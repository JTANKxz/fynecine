# Documentação da API de Canais (Live TV)

Esta documentação descreve os endpoints disponíveis para a funcionalidade de TV ao Vivo (Canais) da aplicação.

## Endpoints Públicos

### 1. Listar Canais
`GET /api/channels`

Retorna uma lista paginada de canais.

**Parâmetros de Query:**
- `page` (int, opcional): Número da página (padrão: 1).
- `search` (string, opcional): Busca canais pelo nome.
- `category` (string, opcional): Filtra canais pelo slug da categoria.

**Exemplo de Resposta:**
```json
{
  "current_page": 1,
  "data": [
    {
      "id": 1,
      "name": "HBO",
      "slug": "hbo",
      "image_url": "https://exemplo.com/hbo.png",
      "created_at": "2024-03-30T10:00:00.000000Z",
      "updated_at": "2024-03-30T10:00:00.000000Z",
      "categories": [
        {
          "id": 1,
          "name": "Filmes",
          "slug": "filmes",
          "pivot": {
            "tv_channel_id": 1,
            "tv_channel_category_id": 1
          }
        }
      ]
    }
  ],
  "first_page_url": "http://localhost/api/channels?page=1",
  "from": 1,
  "last_page": 5,
  "last_page_url": "http://localhost/api/channels?page=5",
  "next_page_url": "http://localhost/api/channels?page=2",
  "path": "http://localhost/api/channels",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 100
}
```

---

### 2. Listar Categorias de Canais
`GET /api/channels/categories`

Retorna todas as categorias disponíveis e a quantidade de canais em cada uma.

**Exemplo de Resposta:**
```json
[
  {
    "id": 1,
    "name": "Esportes",
    "slug": "esportes",
    "created_at": "2024-03-30T10:00:00.000000Z",
    "updated_at": "2024-03-30T10:00:00.000000Z",
    "channels_count": 15
  },
  {
    "id": 2,
    "name": "Filmes",
    "slug": "filmes",
    "created_at": "2024-03-30T10:00:00.000000Z",
    "updated_at": "2024-03-30T10:00:00.000000Z",
    "channels_count": 22
  }
]
```

---

### 3. Detalhes do Canal
`GET /api/channels/{idOrSlug}`

Retorna os detalhes de um canal específico e seus links de reprodução filtrados por segurança e plano do usuário.

**Lógica de Filtro dos Links:**
1. Se `security_mode` estiver ativado nas configurações globais, a lista `play_links` virá vazia (independente do usuário).
2. Se o usuário estiver logado e possuir um plano ativo (`hasPlan`), todos os links do canal são retornados.
3. Se o usuário NÃO estiver logado ou NÃO possuir plano, apenas os links marcados como `free` (gratuitos) são retornados.

**Exemplo de Resposta (Usuário com Plano):**
```json
{
  "id": 1,
  "name": "HBO",
  "slug": "hbo",
  "image_url": "https://exemplo.com/hbo.png",
  "categories": [
    {
      "id": 1,
      "name": "Filmes",
      "slug": "filmes"
    }
  ],
  "play_links": [
    {
      "id": 10,
      "name": "Player Principal",
      "url": "https://stream.exemplo.com/hbo.m3u8",
      "type": "m3u8"
    },
    {
      "id": 11,
      "name": "Player 4K",
      "url": "https://stream.exemplo.com/hbo-4k.m3u8",
      "type": "m3u8"
    }
  ]
}
```

**Exemplo de Resposta (Usuário sem Plano / Visitante):**
```json
{
  "id": 1,
  "name": "HBO",
  "slug": "hbo",
  "image_url": "https://exemplo.com/hbo.png",
  "categories": [
    {
      "id": 1,
      "name": "Filmes",
      "slug": "filmes"
    }
  ],
  "play_links": [
    {
      "id": 12,
      "name": "Trailer / Amostra",
      "url": "https://stream.exemplo.com/hbo-free.m3u8",
      "type": "m3u8"
    }
  ]
}
```

---

## Modelos de Dados (DB Insights)

### Canais (`tv_channels`)
- `name`: Nome do canal.
- `slug`: Identificador único na URL (gerado automaticamente do nome).
- `image_url`: Link para o logo/poster do canal.

### Links (`tv_channel_links`)
- `tv_channel_id`: FK para o canal.
- `name`: Nome do player/link (ex: "Opção 1", "HD").
- `url`: Endereço do streaming.
- `type`: Tipo de stream (ex: `m3u8`, `embed`, `direct`).
- `player_sub`: Define restrição de acesso (`free` para todos, `premium` ou outros para assinantes).
- `order`: Ordem de exibição dos links.
