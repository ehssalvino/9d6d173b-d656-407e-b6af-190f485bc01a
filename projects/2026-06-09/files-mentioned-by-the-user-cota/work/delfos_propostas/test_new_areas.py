from pathlib import Path
from tempfile import TemporaryDirectory

from app import AREA_CONFIG, ProposalData, generate_docx


def proposal(area: str) -> ProposalData:
    fields = {
        key: default or "A definir"
        for key, _label, default in AREA_CONFIG[area]["fields"]
    }
    return ProposalData(
        area=area,
        proposal_number="20260610-001",
        client="Cliente de Teste",
        document="",
        contact="",
        email="",
        address="Local da obra",
        city="Rio de Janeiro/RJ",
        issue_date="10/06/2026",
        validity_days=5,
        responsible="Paulo Arruda",
        responsible_phone="",
        responsible_email="",
        technical=fields,
        items=[
            {
                "qty": 1,
                "description": f"Materiais e serviços de {area}",
                "unit": 1000,
                "note": "Teste",
            }
        ],
        service_value=500,
        payment_terms="À vista",
        deadline="30 dias",
        included=AREA_CONFIG[area]["included"],
        excluded=AREA_CONFIG[area]["excluded"],
    )


with TemporaryDirectory() as temporary:
    output = Path(temporary)
    for area_name in ("Drywall", "Serralheria e Portões"):
        target = output / f"{area_name.replace(' ', '_')}.docx"
        generate_docx(proposal(area_name), target)
        assert target.exists() and target.stat().st_size > 20_000

print("new-area-tests-ok")
