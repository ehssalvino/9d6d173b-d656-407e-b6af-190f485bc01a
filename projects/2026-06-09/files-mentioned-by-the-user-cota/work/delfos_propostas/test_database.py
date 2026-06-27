from pathlib import Path
from tempfile import TemporaryDirectory

from app import Database, ProposalData


def sample(client: str = "Cliente Teste") -> ProposalData:
    return ProposalData(
        area="CFTV",
        proposal_number="20260609-999",
        client=client,
        document="123.456.789-00",
        contact="(21) 99999-9999",
        email="cliente@example.com",
        address="Rua de Teste, 10",
        city="Rio de Janeiro/RJ",
        issue_date="09/06/2026",
        validity_days=5,
        responsible="Paulo Arruda",
        responsible_phone="(85) 98690-9945",
        responsible_email="alfasolar2@gmail.com",
        technical={"cameras": "16", "resolucao": "Full HD"},
        items=[
            {"qty": 16, "description": "Câmera Full HD", "unit": 109.0, "note": ""}
        ],
        service_value=1200,
        payment_terms="À vista",
        deadline="10 dias",
        included=["Instalação e configuração"],
        excluded=["Obras civis"],
    )


with TemporaryDirectory() as temporary:
    database = Database(Path(temporary) / "test.db")
    proposal = sample()
    proposal_id = database.save_proposal(proposal)
    assert proposal_id > 0
    assert len(database.list_proposals("Cliente")) == 1
    assert database.get_proposal(proposal_id)["client"] == "Cliente Teste"

    proposal.contact = "(21) 98888-7777"
    updated_id = database.save_proposal(proposal, proposal_id=proposal_id)
    assert updated_id == proposal_id
    assert database.list_proposals("98888")[0]["client"] == "Cliente Teste"

    database.delete_proposal(proposal_id)
    assert database.list_proposals() == []
    database.close()

print("database-tests-ok")
