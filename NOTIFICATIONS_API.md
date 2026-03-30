# Documentação da API de Notificações In-App

Esta API gerencia as notificações internas exibidas no aplicativo, suportando mensagens globais e individuais com ações de redirecionamento.

## Endpoints (Requer Autenticação)

Todos os endpoints abaixo exigem o header `Authorization: Bearer {token}`.

### 1. Listar Notificações
`GET /api/notifications`

Retorna todas as notificações ativas (não expiradas) destinadas ao usuário ou globais.

**Exemplo de Resposta:**
```json
{
  "unread_count": 2,
  "notifications": [
    {
      "id": 5,
      "title": "Novo Filme Adicionado!",
      "content": "Confira 'Zootopia 2' que acaba de chegar ao catálogo.",
      "image_url": "https://image.tmdb.org/t/p/w500/lgotja3xMoJZbynwHfcQcJAEMWH.jpg",
      "action_type": "movie",
      "action_value": "zootopia-2",
      "created_at": "2024-03-30T10:00:00.000000Z",
      "is_read": false
    },
    {
      "id": 1,
      "title": "Promoção de Assinatura",
      "content": "Assine hoje e ganhe 50% de desconto no primeiro mês.",
      "image_url": null,
      "action_type": "plans",
      "action_value": null,
      "created_at": "2024-03-29T15:30:00.000000Z",
      "is_read": true
    }
  ]
}
```

---

### 2. Marcar como Lida
`POST /api/notifications/{id}/read`

Marca uma notificação específica como lida pelo usuário.

**Resposta:**
```json
{
  "success": true
}
```

---

### 3. Marcar Todas como Lidas
`POST /api/notifications/read-all`

Marca todas as notificações atuais que o usuário ainda não leu como lidas. Ideal para chamar quando o usuário abre a central de notificações.

**Resposta:**
```json
{
  "success": true,
  "marked_count": 2
}
```

---

## Estrutura da Notificação

### Ações (`action_type`)
O campo `action_type` define o que o aplicativo deve fazer ao clicar na notificação:
- `none`: Nenhuma ação, apenas exibe o texto.
- `url`: Abre o link contido em `action_value` no navegador externo.
- `movie`: Navega para a tela de detalhes do filme usando o ID/Slug em `action_value`.
- `series`: Navega para a tela de detalhes da série usando o ID/Slug em `action_value`.
- `plans`: Navega para a tela de planos de assinatura.

### Expiração
A API filtra automaticamente notificações que possuem o campo `expires_at` no passado. Notificações globais que expirarem deixarão de contar no badge de "não lidas" automaticamente.
