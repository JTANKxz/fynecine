# API de Eventos Ao Vivo

Esta API permite listar e obter links de reprodução para eventos em tempo real (esportes, shows, etc.).

## 1. Listar Eventos Ativos
Retorna eventos que estão "Ao Vivo" ou que começarão nos próximos 30 minutos (fuso `America/Sao_Paulo`).

- **URL:** `/api/events`
- **Método:** `GET`
- **Resposta de Sucesso:**
```json
[
  {
    "id": 1,
    "title": "Final Champions League",
    "home_team": "Real Madrid",
    "away_team": "B. Dortmund",
    "image_url": "https://...",
    "start_time": "2026-06-01T16:00:00-03:00",
    "end_time": "2026-06-01T18:30:00-03:00",
    "status": "Ao Vivo"
  }
]
```
> **Nota sobre Status:** Os valores possíveis são `Ao Vivo`, `Em Breve` (começa em < 30min) e `Encerrado`.

---

## 2. Detalhes e Links do Evento
Retorna as informações do evento e a lista de links disponíveis para o plano do usuário.

- **URL:** `/api/events/{id}`
- **Método:** `GET`
- **Resposta de Sucesso:**
```json
{
  "id": 1,
  "title": "Final Champions League",
  "home_team": "Real Madrid",
  "away_team": "B. Dortmund",
  "image_url": "https://...",
  "description": "A grande final europeia ao vivo.",
  "status": "Ao Vivo",
  "play_links": [
    {
      "id": 5,
      "name": "Opção HD",
      "url": "https://...",
      "type": "direct"
    }
  ]
}
```
> **Planos:** Links marcados como `premium` no admin só serão retornados se o usuário tiver um plano ativo (VIP). Links `free` aparecem para todos.

---

## 3. Integração com a Home
Para exibir eventos na Página Inicial:
1. No Painel Admin, crie uma nova **Seção da Home**.
2. No campo **Tipo**, selecione `events`.
3. A API `/api/home` passará a retornar os eventos ativos dentro desta seção.
