=== Solar Integradores ===
Contributors: haja-geracao-solar
Tags: energia solar, fotovoltaica, integradores, calculadora
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.7.0
License: GPLv2 or later

Calculadora solar pública e área de integradores para WordPress.

== Instalação ==

1. Envie a pasta `solar-integradores` para `wp-content/plugins`.
2. Ative o plugin no painel do WordPress.
3. Ajuste HSP, perdas, módulo e tarifa em Configurações > Solar Integradores.
4. Crie páginas com os shortcodes:
   * `[si_solar_calculator]`
   * `[si_integrator_registration]`
   * `[si_integrator_dashboard]`

== Metodologia ==

O dimensionamento usa:

`kWp = (consumo compensável / 30) / (HSP x (1 - perdas))`

Para unidades do Grupo A, o consumo na ponta é convertido em equivalente fora de
ponta pelo fator `TE fora de ponta / TE ponta`, conforme o modelo fornecido.

O plugin instala uma base com 5.509 municípios, UF, latitude e longitude extraída
da planilha de dimensionamento fornecida. A HSP permanece configurável porque não
há uma HSP por município nessa tabela de origem.

A demanda contratada é mantida separada do consumo de energia. Ela serve para a
validação elétrica e tarifária e não deve ser usada isoladamente para calcular kWp.

Os resultados são estimativas preliminares. Projeto, homologação, simultaneidade,
demanda medida, modalidade tarifária, impostos e regras da distribuidora precisam
ser validados por profissional habilitado e por fontes oficiais vigentes.

O investimento é estimado automaticamente por faixas de preço instalado em R$/Wp.
Os valores podem ser atualizados pelo administrador e são exibidos ao cliente como
referência central e faixa, não como orçamento definitivo.

A interface pública não solicita HSP, perdas, potência do módulo, tarifa ou COSIP.
Os parâmetros técnicos ficam no painel. A distribuidora e a tarifa B1 convencional
são identificadas pela localização usando bases oficiais da ANEEL processadas em
junho de 2026. A tarifa final inclui um acréscimo médio configurável; tributos,
bandeiras e COSIP reais variam conforme a conta e o município.

Após o cálculo, o plugin oferece a geração de orçamento, coleta nome, WhatsApp,
e-mail, preferência de contato e consentimento, registra o lead e cria um link
privado com o resumo. O envio automático de mensagens pelo WhatsApp depende de
credenciais da API oficial do WhatsApp Business; sem essa integração, o plugin
gera um botão de compartilhamento.

== Proposta comercial ==

A versão 0.5.0 gera uma proposta completa em sete páginas com dimensionamento,
equipamentos, consumo x geração, análise financeira, fluxo de caixa de 25 anos,
formas de pagamento e aceite.

As taxas do Mercado Pago são editáveis no painel. O catálogo de fornecedores usa
uma linha por cotação no formato:

`Fornecedor|kWp mínimo|kWp máximo|Módulo|Inversor|Custo do kit`

O sistema escolhe o kit compatível de menor custo, mas mantém o preço final da
proposta igual ao preço-base calculado e apresentado no site.

Na versão 0.6.0, a proposta é gerada como arquivo PDF pelo próprio WordPress,
anexada ao e-mail e compartilhada por um link protegido pelo token do orçamento.

Na versão 0.7.0, o sistema calcula a potência DC real pela quantidade de módulos
e seleciona automaticamente a quantidade e a potência comercial dos inversores,
respeitando até 30% de overload DC/AC. O preço-base permanece inalterado.
