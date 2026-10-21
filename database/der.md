```mermaid
erDiagram
    USUARIOS ||--o{ TITULOS : "cadastra / possui"

    USUARIOS {
        SERIAL id PK
        VARCHAR_150 email UK
        VARCHAR_255 senha
    }

    TITULOS {
        SERIAL id PK
        INT usuario_id FK
        VARCHAR_200 titulo
        VARCHAR_10 tipo
        VARCHAR_10 status
        INT nota
        TEXT review
        TEXT capa
    }
```