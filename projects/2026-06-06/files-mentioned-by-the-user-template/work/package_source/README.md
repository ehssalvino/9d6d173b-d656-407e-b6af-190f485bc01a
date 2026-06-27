# Gerador de Memorial Solar

Aplicativo Windows para preencher e gerar o memorial técnico a partir do template Word e da planilha de dados.

## Decisão técnica

Python foi mantido porque oferece a melhor relação entre velocidade de desenvolvimento e suporte a Word, Excel, PDF, imagens, OCR futuro e empacotamento para Windows. A interface usa Tkinter e funciona localmente; documentos pessoais não são enviados para serviços externos.

## O que foi corrigido

- substituição de tags partidas em vários trechos internos do Word;
- substituição também em cabeçalhos, rodapés e caixas de texto;
- preservação da formatação do trecho onde a tag começa;
- cálculo interno dos totais e grandezas técnicas, sem depender do cache de fórmulas do Excel;
- validação e relatório de tags não resolvidas;
- imagem de localização sem dependência obrigatória de `qrcode`;
- interface para revisar dados antes da geração;
- formulário organizado em abas para dados gerais, datasheets, consumo mensal, levantamento de carga, proteção, aterramento e cabos;
- campos destacados em amarelo no memorial foram transformados em variáveis editáveis ou calculadas;
- leitura inicial de PDFs textuais e DOCX para sugerir alguns campos.
- extração dos 12 consumos mensais quando a conta estiver em PDF legível.

## Uso pelo código-fonte

1. Instale Python 3.11 ou superior.
2. Execute:

```powershell
py -3 -m venv .venv
.\.venv\Scripts\python.exe -m pip install -r requirements.txt
.\.venv\Scripts\python.exe app.py
```

Também é possível gerar pela linha de comando:

```powershell
.\.venv\Scripts\python.exe generate_cli.py `
  --excel assets\Mapa_Dados_Memorial_LIGHT_100_PARAM.xlsx `
  --template assets\TEMPLATE_MEMORIAL_LIGHT_100_PARAM_TAGUEADO.docx `
  --out Memorial_Gerado.docx
```

## Criar executável e instalador

Execute `build_exe.ps1` para criar o `.exe`. Para criar o instalador, instale o Inno Setup 6 e execute `build_installer.ps1`.

## Limites que precisam de revisão técnica

- O template recebido contém somente 12 meses de consumo e 12 itens de carga visíveis, embora a planilha tenha 24.
- CPF e identidade ainda não têm tags no template fornecido.
- PDFs digitalizados como imagem e fotos exigem um módulo OCR adicional.
- A conta de energia precisa estar em PDF legível, com texto selecionável. Conta escaneada ou foto não será lida automaticamente nesta versão.
- A extração de datasheets é assistida: o usuário deve confirmar marca, modelo e parâmetros elétricos.
- Todo memorial deve ser conferido e assinado pelo responsável técnico antes de ser protocolado.
