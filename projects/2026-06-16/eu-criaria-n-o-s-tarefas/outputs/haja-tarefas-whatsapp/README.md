# Haja Tarefas WhatsApp

Plugin WordPress para um mini-Jira pessoal via WhatsApp, com painel Kanban e integracao com Google Calendar.

## Onde configurar tudo

Depois de ativar o plugin, acesse no WordPress:

`Haja Tarefas > Conexoes`

Essa tela centraliza tudo:

- Google Client ID
- Google Client Secret
- Botao Conectar Google Agenda
- URI de redirecionamento do Google gerada pelo plugin
- WhatsApp Verify Token gerado pelo plugin
- Callback URL do webhook gerada pelo plugin
- WhatsApp Access Token
- Phone Number ID
- WhatsApp Business Account ID
- Status de conexao do Google e WhatsApp

## Observacao importante

O plugin gera e mostra as URLs que devem ser usadas no Google e na Meta. O Google e a Meta ainda exigem que voce crie/copie as credenciais nas plataformas deles, mas toda a configuracao que pertence ao WordPress fica visivel dentro do plugin.

## Fluxo Google Agenda

1. Entre em `Haja Tarefas > Conexoes`.
2. Copie a URI de redirecionamento exibida pelo plugin.
3. Crie um OAuth Client do tipo Web Application no Google Cloud.
4. Cole Client ID e Client Secret no plugin.
5. Salve.
6. Clique em `Conectar Google Agenda`.

## Fluxo WhatsApp Business

1. Entre em `Haja Tarefas > Conexoes`.
2. Copie o `Verify Token` criado pelo plugin.
3. Copie a `Callback URL do webhook` criada pelo plugin.
4. Use esses dois campos na tela de Webhooks da Meta.
5. Cole no plugin o WhatsApp Access Token, Phone Number ID e WhatsApp Business Account ID.

## Endpoint tecnico

O endpoint existe porque o WhatsApp precisa chamar uma URL publica:

`/wp-json/haja-tarefas/v1/whatsapp`

Voce nao precisa editar JSON manualmente para configurar isso.
