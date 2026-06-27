from __future__ import annotations

import re
from pathlib import Path

from docx import Document
from pypdf import PdfReader


def extract_text(path: str | Path) -> str:
    source = Path(path)
    suffix = source.suffix.lower()
    if suffix == ".pdf":
        return "\n".join(page.extract_text() or "" for page in PdfReader(source).pages)
    if suffix == ".docx":
        document = Document(source)
        return "\n".join(paragraph.text for paragraph in document.paragraphs)
    if suffix in {".txt", ".csv"}:
        return source.read_text(encoding="utf-8", errors="ignore")
    return ""


def _first(patterns: list[str], text: str) -> str:
    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE | re.MULTILINE)
        if match:
            return " ".join(match.group(1).strip().split())
    return ""


def extract_consumption_kwh(text: str) -> list[int]:
    month_names = (
        "jan|fev|mar|abr|mai|jun|jul|ago|set|out|nov|dez|janeiro|fevereiro|março|marco|abril|maio|junho|julho|"
        "agosto|setembro|outubro|novembro|dezembro"
    )
    values: list[int] = []
    for pattern in [
        rf"(?:{month_names})[^\n\r]{{0,35}}?(\d{{2,5}})\s*kwh",
        rf"(\d{{2,5}})\s*kwh[^\n\r]{{0,35}}?(?:{month_names})",
        r"consumo[^\n\r]{0,40}?(\d{2,5})\s*kwh",
    ]:
        for match in re.finditer(pattern, text, re.IGNORECASE):
            value = int(match.group(1))
            if 20 <= value <= 5000:
                values.append(value)
    if len(values) < 12:
        # Algumas contas trazem uma tabela sem o sufixo kWh, com 12 ou 13 números
        # próximos do título de histórico de consumo.
        hist = re.search(r"(hist[oó]rico de consumo|consumo mensal)(.{0,1500})", text, re.IGNORECASE | re.DOTALL)
        if hist:
            for value in re.findall(r"\b(\d{2,5})\b", hist.group(2)):
                number = int(value)
                if 20 <= number <= 5000:
                    values.append(number)
    deduped: list[int] = []
    for value in values:
        if len(deduped) >= 12:
            break
        deduped.append(value)
    return deduped


def suggest_fields(paths: list[str | Path]) -> tuple[dict[str, str], list[str], list[int]]:
    text_parts: list[str] = []
    unsupported: list[str] = []
    for path in paths:
        content = extract_text(path)
        if content:
            text_parts.append(content)
        else:
            unsupported.append(Path(path).name)
    text = "\n".join(text_parts)

    suggestions = {
        "CLIENTE_NOME": _first(
            [r"(?:nome do cliente|nome|titular)\s*[:\-]\s*([^\n]{4,100})"], text
        ),
        "UC_CONTA_CONTRATO": _first(
            [
                r"(?:conta contrato|n[úu]mero da instala[çc][ãa]o|unidade consumidora|uc)\s*[:\-]?\s*([0-9.\-/]{5,30})"
            ],
            text,
        ),
        "CLIENTE_ENDERECO_LOGRADOURO": _first(
            [r"(?:endere[çc]o|logradouro)\s*[:\-]\s*([^\n]{6,150})"], text
        ),
        "MOD_MODELO": _first(
            [r"(?:module model|modelo do m[óo]dulo|model)\s*[:\-]\s*([A-Z0-9._\-/ ]{3,60})"],
            text,
        ),
        "MOD_WP": _first(
            [r"(?:maximum power|pot[êe]ncia m[áa]xima|pmax)\s*[:\-]?\s*(\d{2,4}(?:[.,]\d+)?)\s*W"],
            text,
        ),
        "MOD_VOC": _first([r"(?:Voc|open circuit voltage)\s*[:\-]?\s*(\d+(?:[.,]\d+)?)"], text),
        "MOD_ISC": _first([r"(?:Isc|short circuit current)\s*[:\-]?\s*(\d+(?:[.,]\d+)?)"], text),
        "MOD_VMP": _first([r"(?:Vmp|Vmpp)\s*[:\-]?\s*(\d+(?:[.,]\d+)?)"], text),
        "MOD_IMP": _first([r"(?:Imp|Impp)\s*[:\-]?\s*(\d+(?:[.,]\d+)?)"], text),
        "INV_MODELO": _first(
            [r"(?:inverter model|modelo do inversor)\s*[:\-]\s*([A-Z0-9._\-/ ]{3,60})"],
            text,
        ),
        "INV_VCC_MAX": _first(
            [r"(?:max(?:imum)? dc voltage|tens[ãa]o cc m[áa]xima)\s*[:\-]?\s*(\d+(?:[.,]\d+)?)"],
            text,
        ),
        "INV_PN_KW": _first(
            [r"(?:rated power|pot[êe]ncia nominal)\s*[:\-]?\s*(\d+(?:[.,]\d+)?)\s*kW"],
            text,
        ),
    }
    return {key: value for key, value in suggestions.items() if value}, unsupported, extract_consumption_kwh(text)
