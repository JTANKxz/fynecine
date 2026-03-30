# Documentação da API de Configurações (Settings)

Este endpoint fornece as configurações globais necessárias para o funcionamento do aplicativo, como controle de versão, links de suporte, mensagens de sistema e modos de segurança.

## Endpoint

### Obter Configurações do App
`GET /api/settings`

Este endpoint é geralmente chamado na inicialização do aplicativo (*Splash Screen*) para verificar a necessidade de atualizações ou exibir mensagens importantes.

**Exemplo de Resposta:**
```json
{
  "app_name": "Fynecine",
  "api_token_key": "fynecine_app_token",
  "custom_message": "Manutenção programada para as 02:00",
  "custom_message_status": false,
  "force_login": true,
  "show_onboarding": true,
  "update_type": "direct",
  "update_status": false,
  "update_url": "https://fynecine.com/download/app.apk",
  "update_skippable": true,
  "version_code": 10,
  "update_features": "<ul><li>Correção de bugs na busca</li><li>Novos canais de esportes</li></ul>",
  "security_mode": false,
  "instagram_url": "https://instagram.com/fynecine",
  "is_instagram_active": true,
  "telegram_url": "https://t.me/fynecine",
  "is_telegram_active": true,
  "whatsapp_url": "https://wa.me/5511999999999",
  "is_whatsapp_active": true,
  "terms_of_use": "Conteúdo dos termos de uso...",
  "privacy_policy": "Conteúdo da política de privacidade...",
  "comments_status": true,
  "comments_auto_approve": false
}
```

---

## Descrição dos Campos

### Controle de Versão e Updates
- `version_code` (int): A versão atual estável no servidor. O app deve comparar com sua versão local.
- `update_status` (bool): Indica se há uma atualização disponível/recomendada.
- `update_type` (string): Tipo de atualização (`direct` para APK direto, `store` para Play Store).
- `update_url` (string): Link para download da nova versão.
- `update_skippable` (bool): Se `false`, o usuário é obrigado a atualizar para continuar usando o app.
- `update_features` (string): Texto ou HTML com as novidades da versão.

### Comportamento do App
- `force_login` (bool): Se `true`, o app não deve permitir navegação sem que o usuário esteja autenticado.
- `show_onboarding` (bool): Se `true`, o app deve exibir os slides de introdução.
- `security_mode` (bool): Quando ativado (`true`), o aplicativo deve ocultar seções sensíveis como **Canais de TV** e links de player externos.

### Mensagens e Suporte
- `custom_message` (string): Uma mensagem global a ser exibida (ex: aviso de manutenção).
- `custom_message_status` (bool): Se `true`, a mensagem deve ser exibida ao usuário (geralmente em um modal ou banner).
- `instagram_url`, `telegram_url`, `whatsapp_url`: Links para as redes de suporte.
- `is_*_active`: Booleanos que indicam se o respectivo link de rede social deve ser exibido.

### Conteúdo Legal
- `terms_of_use` (string): Texto completo dos termos de uso.
- `privacy_policy` (string): Texto completo da política de privacidade.

### Interação Social
- `comments_status` (bool): Indica se a seção de comentários está habilitada globalmente.
- `comments_auto_approve` (bool): Se os comentários dos usuários aparecem imediatamente ou aguardam moderação.
