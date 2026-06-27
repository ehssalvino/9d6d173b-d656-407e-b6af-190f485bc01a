# Mapeamento das planilhas

## Dimensionamento residencial e comercial sem demanda

Origem: `TABELA...DIMENSIONAMENTO...xlsx`.

- A base `# BD_CID` contém 5.509 municípios, UF, latitude e longitude.
- O consumo médio é calculado a partir de até 12 meses.
- O custo de disponibilidade é 30 kWh para monofásica, 50 kWh para bifásica e
  100 kWh para trifásica.
- O consumo compensável é o consumo médio menos o custo de disponibilidade.
- A potência é calculada por:

  `kWp = (consumo compensável / 30) / (HSP x (1 - perdas))`

- A geração mensal usa:

  `geração = kWp x HSP x 30 x (1 - perdas)`

- A planilha também calcula geração mensal, saldo acumulado, investimento,
  economia, payback, ROI, TIR e VPL.

## Unidade com demanda contratada

Origem: `PLANILHA...DEMANDA...CONTRATADA.xlsx`.

- Consumo de ponta e fora de ponta são tratados separadamente.
- O fator de ajuste é:

  `FA = TE fora de ponta / TE ponta`

- O consumo de ponta convertido em equivalente fora de ponta é:

  `equivalente = consumo na ponta / FA`

- O dimensionamento usa a soma do consumo fora de ponta e do equivalente.
- Demanda contratada é um parâmetro de validação elétrica e tarifária. Ela não
  representa energia e, isoladamente, não deve ser convertida em kWp.

## Decisões de implementação

- A fórmula de perdas segue a planilha maior. A planilha de demanda possui uma
  célula que multiplica por `(1 - perdas)` e outra que aplica `1,17`; essa
  inconsistência não foi reproduzida.
- HSP permanece editável. A tabela municipal de origem fornece coordenadas, mas
  não contém HSP por município.
- Tarifas e tributos não são congelados no código. Devem ter distribuidora,
  modalidade, vigência e fonte associadas em uma próxima etapa de integração
  com a base aberta da ANEEL.
- O preço é calculado por faixas de potência em R$/Wp e exibido como referência
  central e intervalo. A configuração inicial usa levantamentos de mercado
  Greener e Radar Solfácil publicados em 2025 e 2026. Os valores permanecem
  editáveis porque porte, região, estrutura, logística e padrão elétrico mudam
  o orçamento.
- Todo resultado é preliminar e exige validação de projeto, simultaneidade,
  demanda medida, inversores, conexão e regras atuais da distribuidora.

## Dados automáticos por localização

- O público não informa HSP, perdas, potência do módulo, tarifa ou COSIP.
- HSP, perdas e módulo são parâmetros administrativos.
- Município e distribuidora são relacionados pela base `IndQual Município` e
  pelos atributos de conjuntos elétricos da ANEEL.
- A tarifa regulada usa a linha B1, convencional, residencial e vigente da base
  `Tarifas de aplicação das distribuidoras de energia elétrica`, processada em
  6 de junho de 2026.
- A tarifa ANEEL não inclui automaticamente tributos, bandeiras ou COSIP. O
  plugin aplica um percentual médio administrável e uma COSIP média por UF.
- Municípios com mais de uma distribuidora usam a predominante na base e exibem
  um aviso para confirmação pela conta de energia.
