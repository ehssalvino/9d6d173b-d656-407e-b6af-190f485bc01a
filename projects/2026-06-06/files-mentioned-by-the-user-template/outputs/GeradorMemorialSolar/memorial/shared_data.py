from __future__ import annotations

import re
from typing import Any


ALIASES = {
    "CLIENTE_NOME": ("TITULAR_NOME", "NOME_TITULAR", "SOLICITANTE_NOME"),
    "CLIENTE_CPF": ("TITULAR_CPF", "CPF_CNPJ", "CLIENTE_CNPJ"),
    "CLIENTE_EMAIL": ("TITULAR_EMAIL", "SOLICITANTE_EMAIL"),
    "CLIENTE_CELULAR": ("TITULAR_CELULAR", "SOLICITANTE_CELULAR", "CLIENTE_TELEFONE"),
    "CLIENTE_ENDERECO_LOGRADOURO": ("UC_ENDERECO", "ENDERECO_UC", "ENDERECO"),
    "CLIENTE_BAIRRO_MUN_UF_CEP": ("UC_LOCALIDADE", "ENDERECO_COMPLEMENTO"),
    "UC_CONTA_CONTRATO": ("UC_INSTALACAO", "NUMERO_INSTALACAO", "CODIGO_UC"),
    "UC_CODIGO_CLIENTE": ("CODIGO_CLIENTE", "NUMERO_CLIENTE"),
    "COORD_SUL": ("LATITUDE", "COORD_LATITUDE", "UC_LATITUDE"),
    "COORD_OESTE": ("LONGITUDE", "COORD_LONGITUDE", "UC_LONGITUDE"),
    "FV_POT_KWP": ("POTENCIA_GERACAO_KW", "POTENCIA_INSTALADA_GERACAO"),
}


def _text(value: Any) -> str:
    return "" if value is None else str(value).strip()


def _first(data: dict[str, Any], *keys: str) -> Any:
    for key in keys:
        if _text(data.get(key)):
            return data[key]
    return None


def _city_from_locality(value: Any) -> str:
    text = _text(value)
    if not text:
        return ""
    text = re.sub(r"\b\d{5}-?\d{3}\b", "", text).strip(" ,-/")
    parts = [part.strip() for part in text.split(",") if part.strip()]
    if len(parts) >= 2:
        return ", ".join(parts[1:])
    match = re.search(r"([A-Za-zÀ-ÿ ]+)\s*[-/]\s*([A-Z]{2})\b", text)
    return f"{match.group(1).strip()} - {match.group(2)}" if match else ""


def expand_shared_values(values: dict[str, Any]) -> dict[str, Any]:
    """Return one canonical data set shared by memorial and utility forms."""
    data = dict(values)

    for canonical, aliases in ALIASES.items():
        if not _text(data.get(canonical)):
            value = _first(data, *aliases)
            if value is not None:
                data[canonical] = value

    mirrored = {
        "SOLICITANTE_NOME": "CLIENTE_NOME",
        "CARGA_INSTALADA_KW": "FV_POT_KWP",
        "POTENCIA_INSTALADA_GERACAO": "FV_POT_KWP",
        "TITULAR_NOME": "CLIENTE_NOME",
        "TITULAR_CPF": "CLIENTE_CPF",
        "TITULAR_EMAIL": "CLIENTE_EMAIL",
        "TITULAR_CELULAR": "CLIENTE_CELULAR",
        "UC_INSTALACAO": "UC_CONTA_CONTRATO",
        "NUMERO_INSTALACAO": "UC_CONTA_CONTRATO",
        "CODIGO_CLIENTE": "UC_CODIGO_CLIENTE",
        "LATITUDE": "COORD_SUL",
        "LONGITUDE": "COORD_OESTE",
    }
    for target, source in mirrored.items():
        if not _text(data.get(target)) and _text(data.get(source)):
            data[target] = data[source]

    if not _text(data.get("LOCAL_CIDADE_UF")):
        data["LOCAL_CIDADE_UF"] = _city_from_locality(
            data.get("CLIENTE_BAIRRO_MUN_UF_CEP")
        )

    if not _text(data.get("CONCESSIONARIA")):
        data["CONCESSIONARIA"] = "LIGHT"

    return data
