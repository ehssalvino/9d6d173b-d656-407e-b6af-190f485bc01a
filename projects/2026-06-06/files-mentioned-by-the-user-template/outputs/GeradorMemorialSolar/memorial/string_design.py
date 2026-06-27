from __future__ import annotations

import math
import re
from collections import Counter, defaultdict
from dataclasses import dataclass
from typing import Any


@dataclass(frozen=True)
class StringConfig:
    mppt: int
    input_index: int
    modules: int

    @property
    def label(self) -> str:
        return f"{self.mppt}/{self.input_index}"


@dataclass(frozen=True)
class StringDesign:
    strings: list[StringConfig]
    warnings: list[str]

    @property
    def modules_by_string(self) -> list[int]:
        return [item.modules for item in self.strings]

    @property
    def string_count(self) -> int:
        return len(self.strings)

    @property
    def max_parallel_strings(self) -> int:
        groups: dict[int, int] = defaultdict(int)
        for item in self.strings:
            groups[item.mppt] += 1
        return max(groups.values(), default=0)


def number(value: Any) -> float | None:
    if value in (None, ""):
        return None
    if isinstance(value, (int, float)):
        return float(value)
    text = str(value).strip().replace(" ", "")
    match = re.search(r"-?\d+(?:[.,]\d+)?", text)
    if not match:
        return None
    try:
        return float(match.group(0).replace(",", "."))
    except ValueError:
        return None


def _number_list(value: Any) -> list[float]:
    if value in (None, ""):
        return []
    if isinstance(value, (int, float)):
        return [float(value)]
    return [float(item.replace(",", ".")) for item in re.findall(r"\d+(?:[.,]\d+)?", str(value))]


def _fmt(value: float | int | None, decimals: int = 2) -> str:
    if value is None:
        return "-"
    if float(value).is_integer():
        return str(int(value))
    return f"{float(value):.{decimals}f}".replace(".", ",")


def _limits(data: dict[str, Any]) -> dict[str, float | int | None]:
    module_qty = int(number(data.get("MOD_QTD")) or 0)
    mppts = int(number(data.get("INV_MPPTS")) or 1)
    total_inputs = int(number(data.get("INV_STRINGS")) or mppts or 1)
    if total_inputs < mppts:
        total_inputs = mppts
    return {
        "module_qty": module_qty,
        "mppts": max(1, mppts),
        "total_inputs": max(1, total_inputs),
        "voc": number(data.get("MOD_VOC")),
        "vmp": number(data.get("MOD_VMP")),
        "isc": number(data.get("MOD_ISC")),
        "module_power": number(data.get("MOD_WP")),
        "max_voltage": number(data.get("INV_VCC_MAX")),
        "mppt_max": number(data.get("INV_VMPPT_MAX")) or number(data.get("INV_VCC_MAX")),
        "mppt_min": number(data.get("INV_VMPPT_MIN")),
        "start_voltage": number(data.get("INV_VSTART")),
        "max_current": max(_number_list(data.get("INV_ICC_MAX")) or [0]) or None,
        "max_power_kw": number(data.get("INV_PMAXCC_KW")),
    }


def _group_strings(modules_by_string: list[int], mppts: int, total_inputs: int) -> list[StringConfig] | None:
    per_mppt = max(1, math.ceil(total_inputs / max(1, mppts)))
    counters = Counter(modules_by_string)
    ordered_groups = sorted(counters.items(), key=lambda item: (-item[1], -item[0]))
    result: list[StringConfig] = []
    current_mppt = 1
    for modules, count in ordered_groups:
        remaining = count
        while remaining > 0:
            if current_mppt > mppts:
                return None
            take = min(per_mppt, remaining)
            for input_index in range(1, take + 1):
                result.append(StringConfig(current_mppt, input_index, modules))
            current_mppt += 1
            remaining -= take
    if len(result) > total_inputs:
        return None
    return result


def _valid_modules(modules: int, min_modules: int, max_modules: int) -> bool:
    return min_modules <= modules <= max_modules


def _candidate_module_counts(qty: int, total_inputs: int, min_modules: int, max_modules: int) -> list[list[int]]:
    candidates: list[list[int]] = []
    for count in range(total_inputs, 0, -1):
        if qty % count == 0:
            modules = qty // count
            if _valid_modules(modules, min_modules, max_modules):
                candidates.append([modules] * count)
            continue
        if count <= 1:
            continue
        for main_modules in range(max_modules, min_modules - 1, -1):
            remainder = qty - (main_modules * (count - 1))
            if remainder == main_modules:
                continue
            if _valid_modules(remainder, min_modules, max_modules):
                candidates.append([main_modules] * (count - 1) + [remainder])
                break
    return candidates


def design_string_arrangement(data: dict[str, Any], *, strict: bool = True) -> StringDesign:
    limits = _limits(data)
    qty = int(limits["module_qty"] or 0)
    if qty <= 0:
        raise ValueError("Informe a quantidade de módulos para calcular as strings.")

    missing = [
        label
        for label, value in (
            ("Voc do módulo", limits["voc"]),
            ("Vmp do módulo", limits["vmp"]),
            ("potência do módulo", limits["module_power"]),
            ("tensão CC máxima do inversor", limits["max_voltage"]),
            ("tensão MPPT máxima", limits["mppt_max"]),
            ("tensão MPPT mínima", limits["mppt_min"]),
        )
        if value is None
    ]
    if missing:
        raise ValueError("Dados ausentes para dimensionar strings: " + ", ".join(missing))

    voc = float(limits["voc"] or 0)
    vmp = float(limits["vmp"] or 0)
    isc = float(limits["isc"] or 0)
    max_voltage = float(limits["max_voltage"] or 0)
    mppt_max = float(limits["mppt_max"] or 0)
    min_voltage = max(float(limits["mppt_min"] or 0), float(limits["start_voltage"] or 0))
    total_inputs = int(limits["total_inputs"] or 1)
    mppts = int(limits["mppts"] or 1)

    max_modules = max(1, min(math.floor(max_voltage / voc), math.floor(mppt_max / vmp)))
    min_modules = max(1, math.ceil(min_voltage / vmp))
    candidates = _candidate_module_counts(qty, total_inputs, min_modules, max_modules)
    strings: list[StringConfig] | None = None
    modules_by_string: list[int] = []
    for candidate in candidates:
        grouped = _group_strings(candidate, mppts, total_inputs)
        if grouped:
            modules_by_string = candidate
            strings = grouped
            break
    if not modules_by_string or not strings:
        raise ValueError(
            f"N?o foi poss?vel distribuir {qty} m?dulos em at? {total_inputs} string(s) "
            f"respeitando {min_modules} a {max_modules} m?dulos por string, "
            f"agrupadas em {mppts} MPPT(s) sem paralelismo desigual."
        )

    warnings: list[str] = []
    max_power_kw = limits["max_power_kw"]
    module_power = float(limits["module_power"] or 0)
    total_power_kw = qty * module_power / 1000
    if max_power_kw and total_power_kw > float(max_power_kw):
        warnings.append(
            f"A potência dos módulos ({_fmt(total_power_kw)} kWp) excede a potência CC máxima "
            f"informada para o inversor ({_fmt(float(max_power_kw))} kW)."
        )

    by_mppt: dict[int, list[StringConfig]] = defaultdict(list)
    for item in strings:
        by_mppt[item.mppt].append(item)
        if item.modules * voc > max_voltage:
            raise ValueError(f"A string {item.label} excede a tensão CC máxima do inversor.")
        if item.modules * vmp > mppt_max:
            raise ValueError(f"A string {item.label} excede a faixa MPPT máxima do inversor.")
        if item.modules * vmp < min_voltage:
            raise ValueError(f"A string {item.label} não atinge a tensão mínima de MPPT/partida.")

    max_current = limits["max_current"]
    if isc and max_current:
        for mppt, items in by_mppt.items():
            mppt_current = len(items) * isc
            if mppt_current > float(max_current):
                message = (
                    f"O MPPT {mppt} teria {_fmt(mppt_current)} A ({len(items)} string(s) em paralelo), "
                    f"acima do limite informado de {_fmt(float(max_current))} A."
                )
                if strict:
                    raise ValueError(message)
                warnings.append(message)

    if len(set(item.modules for item in strings)) > 1:
        warnings.append(
            "Há uma string com quantidade diferente de módulos; ela foi isolada em MPPT separado para evitar paralelismo desigual."
        )
    return StringDesign(strings=strings, warnings=warnings)


def fallback_string_design(data: dict[str, Any]) -> StringDesign:
    qty = int(number(data.get("MOD_QTD")) or 0)
    if qty <= 0:
        return StringDesign([], [])
    count = 2 if qty >= 10 else 1
    base, remainder = divmod(qty, count)
    strings = [StringConfig(index + 1, 1, base + (1 if index < remainder else 0)) for index in range(count)]
    return StringDesign(strings=strings, warnings=[])
