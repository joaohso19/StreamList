-- StreamList - estrutura completa do banco (PostgreSQL)
-- 1) Crie o banco (rode no terminal):  psql -U postgres -c "CREATE DATABASE streamlist;"
-- 2) Rode este arquivo:                psql -U postgres -d streamlist -f schema.sql

CREATE TABLE IF NOT EXISTS usuarios (
    id SERIAL PRIMARY KEY,
    email VARCHAR(150) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL              -- guardada com password_hash()
);

CREATE TABLE IF NOT EXISTS titulos (
    id SERIAL PRIMARY KEY,
    usuario_id INT NOT NULL REFERENCES usuarios(id) ON DELETE CASCADE,
    titulo VARCHAR(200) NOT NULL,
    tipo VARCHAR(10) NOT NULL CHECK (tipo IN ('filme', 'serie')),
    status VARCHAR(20) NOT NULL CHECK (status IN ('assistido', 'assistindo', 'quero')),
    capa TEXT,                                -- URL da capa
    ano VARCHAR(4),
    sinopse TEXT,
    nota INT CHECK (nota BETWEEN 1 AND 5),    -- só para status diferente de 'quero'
    comentario TEXT,                          -- só para status diferente de 'quero'
    temporada INT,                            -- progresso da série (só quando 'assistindo')
    episodio INT,
    favorito BOOLEAN NOT NULL DEFAULT FALSE,  -- botão de coração
    duracao INT,                              -- minutos (filme: total | série: por episódio)
    eps_temporadas VARCHAR(300)               -- episódios por temporada, ex.: '10,8,12'
);

CREATE INDEX IF NOT EXISTS idx_titulos_usuario ON titulos(usuario_id);

-- Se você já tinha a tabela titulos de uma versão anterior:
-- DROP TABLE titulos;  e rode este arquivo de novo.