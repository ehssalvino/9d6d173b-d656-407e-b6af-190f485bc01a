# Delfos Propostas

Aplicativo Windows para cadastrar clientes, manter o histórico e gerar propostas comerciais de:

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

1. Clique em **Nova proposta** e selecione a área técnica.
2. Preencha os dados do cliente e os dados técnicos.
3. Edite a relação de itens, escopo, pagamento e prazo.
4. Clique em **Salvar no banco** ou gere o DOCX/PDF.
5. Use **Clientes e histórico** para buscar pelo cliente e abrir propostas antigas.

A geração de PDF usa o Microsoft Word instalado no Windows.

## Banco de dados

O banco SQLite é criado automaticamente em:

```text
%LOCALAPPDATA%\DelfosPropostas\delfos_propostas.db
```

Ele permanece no computador mesmo quando o executável é atualizado. Faça backup
periódico desse arquivo para proteger o cadastro e o histórico.
