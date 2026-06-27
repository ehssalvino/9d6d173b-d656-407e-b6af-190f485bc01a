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
- leitura de PDFs textuais, PDFs escaneados, DOCX e imagens JPG/PNG;
- OCR local em português, sem enviar documentos pessoais para a internet;
- correção automática de rotação para fotos de documentos;
- extração de nome, CPF, RG, nascimento e naturalidade para revisão;
- extração dos 12 consumos mensais da tabela da conta de energia;
- detecção de todos os modelos presentes em datasheets com tabelas;
- seletores de módulo e inversor para aplicar somente a coluna escolhida.
- separação entre documentos técnicos e documentos do cliente, evitando que
  contatos do fabricante sejam usados como dados do titular;
- leitura de tabelas multipágina RENEPV e de datasheets digitalizados da série
  SAJ R6-S3, com seleção automática do modelo citado no nome do arquivo;
- leitor genérico por rótulos elétricos para datasheets simples de outras marcas;
- captura automática do Google Maps em modo satélite pelo endereço do cliente;
- uma linha de circuito CC para cada string, com tensão e queda calculadas separadamente;
- remoção do gráfico antigo do template, mantendo somente o gráfico gerado pelo programa;
- preenchimento dos limites de tensão CA e demais dados da tabela do inversor.
- seleção de concessionária entre LIGHT e Enel-RJ;
- geração automática dos formulários LIGHT fornecidos, preservando o PDF original;
- aba de rateio com leitura das contas adicionais e prioridade automática;
- unidade geradora fixada como prioridade 0 e participantes numerados a partir de 1;
- opção separada para rateio percentual, quando necessário;
- pacote de dados em Word para apoiar o preenchimento do protocolo da Enel-RJ.
- reaproveitamento automático dos dados comuns entre memorial, conta de energia,
  formulário de conexão, registro ANEEL e formulários de rateio;
- replicação de titular, CPF/CNPJ, instalação, código do cliente, endereço,
  município, telefone, e-mail, potência, latitude e longitude;
- compatibilidade com nomes alternativos de campos usados em planilhas antigas.
- geração automática do diagrama unifilar em PDF A3 e DXF editável;
- dimensionamento assistido das strings conforme Voc, Vmp, faixa MPPT,
  tensão máxima, corrente e potência CC do inversor;
- preenchimento do padrão de entrada, proteção geral, proteção do inversor,
  cabos CA/CC, equipamentos, dados técnicos, cliente, endereço, UC e autor;
- inclusão da imagem de localização no quadro de situação do diagrama;
- alerta quando a potência fotovoltaica excede a potência CC informada para o
  inversor e bloqueio quando tensão, corrente ou quantidade de entradas não
  permitem um arranjo válido.

## Formulários das concessionárias

Na aba `Concessionária e rateio`, selecione a distribuidora, a modalidade de
compensação e os documentos desejados. Para rateio, use `Adicionar outra conta`
e selecione a conta de energia de cada unidade participante. O programa tenta
ler instalação, código do cliente, titular e endereço; todos os dados continuam
editáveis para conferência.

Os modelos oficiais fornecidos neste projeto são da LIGHT. Para a Enel-RJ, o
programa gera uma ficha completa de apoio ao protocolo, identificada claramente
como documento auxiliar e não como formulário oficial.

O diagrama unifilar utiliza como base a prancha CAD fornecida pelo usuário.
O PDF é gerado pronto para revisão e o DXF pode ser aberto no AutoCAD, LibreCAD
ou outro programa compatível. O responsável técnico deve confirmar os
dispositivos, as bitolas, a coordenação das proteções e as exigências vigentes da
concessionária antes do protocolo.

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
- CPF e identidade podem ser lidos e revisados no formulário, mas ainda não têm tags no template fornecido.
- Fotos desfocadas, com reflexo forte ou parte do documento cortada podem exigir correção manual.
- A extração de datasheets é assistida: o usuário deve confirmar marca, modelo e parâmetros elétricos.
- Todo memorial deve ser conferido e assinado pelo responsável técnico antes de ser protocolado.
- Formulários e exigências das concessionárias podem mudar; confirme a versão
  vigente antes do envio.
