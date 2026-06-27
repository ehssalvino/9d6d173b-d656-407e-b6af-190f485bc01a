# Delfos Propostas

Protótipo Windows para gerar propostas comerciais de:

- Energia Solar
- CFTV
- Elétrica
- Rede

## Executar o código

Requer Python 3 e as dependências de `requirements.txt`.

```powershell
python app.py
```

## Gerar o executável

No PowerShell:

```powershell
.\build_exe.ps1
```

O arquivo final será criado em `dist\DelfosPropostas.exe`.

## Fluxo

1. Selecione a área técnica.
2. Preencha os dados do cliente e os dados técnicos.
3. Edite a relação de itens no formato indicado.
4. Revise escopo, exclusões, pagamento e prazo.
5. Gere o DOCX ou DOCX + PDF.

A geração de PDF usa o Microsoft Word instalado no Windows.
