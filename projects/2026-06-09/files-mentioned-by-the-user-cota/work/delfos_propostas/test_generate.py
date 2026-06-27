from pathlib import Path

from app import ProposalData, convert_to_pdf, generate_docx


OUTPUT = Path(__file__).resolve().parents[2] / "work" / "test-output"
OUTPUT.mkdir(parents=True, exist_ok=True)

data = ProposalData(
    area="Energia Solar",
    proposal_number="20260609-001",
    client="Luiz Sérgio",
    document="",
    contact="",
    email="",
    address="Fazenda",
    city="Rio de Janeiro/RJ",
    issue_date="09/06/2026",
    validity_days=5,
    responsible="Paulo Arruda",
    responsible_phone="(85) 98690-9945",
    responsible_email="alfasolar2@gmail.com",
    technical={
        "consumo_mensal": "4100",
        "tarifa": "0,93",
        "tipo_rede": "Trifásica",
        "potencia_sistema": "37,18",
        "geracao_mensal": "4823,55",
        "modulos": "52 x 715 W",
        "inversores": "2 x 10 kW",
    },
    items=[
        {
            "qty": 52,
            "description": "Módulo bifacial HJT 715 W",
            "unit": 715.00,
            "note": "Conforme kit cotado",
        },
        {
            "qty": 2,
            "description": "Inversor monofásico 2 MPPT 220 V 10 kW",
            "unit": 3900.00,
            "note": "Com monitoramento",
        },
        {
            "qty": 1,
            "description": "Estrutura, proteções, conectores e cabos solares",
            "unit": 3462.86,
            "note": "Kit completo",
        },
    ],
    service_value=14532.86,
    discount=0,
    payment_terms="Entrada de 30% e saldo conforme cronograma",
    deadline="30 a 45 dias após aceite e disponibilidade dos equipamentos",
    included=[
        "Vistoria técnica e levantamento das condições de instalação",
        "Projeto elétrico e documentação técnica aplicável",
        "Fornecimento e instalação dos equipamentos descritos",
        "Configuração do monitoramento e comissionamento",
    ],
    excluded=[
        "Reforço estrutural, alvenaria e adequações civis não descritas",
        "Alterações exigidas pela concessionária após vistoria",
    ],
    notes="Valores sujeitos à confirmação após vistoria técnica.",
)

docx = OUTPUT / "Proposta_Delfos_Teste.docx"
generate_docx(data, docx)
pdf = convert_to_pdf(docx)
print(docx)
print(pdf)
