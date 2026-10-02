# StreamList — Documentação

Mini sistema para organizar filmes e séries: **Assistido**, **Assistindo** e **Quero assistir**.
Tecnologias: PHP + PDO + PostgreSQL + HTML/CSS + um pouco de JavaScript (busca com sugestões).

## 1. Pré-requisitos
- PHP 8+ com a extensão `pdo_pgsql` ativada
- PostgreSQL
- Internet (a busca de capas usa APIs abertas, **sem cadastro e sem chave**)

## 2. Estrutura
```
streamlist/
├── schema.sql            -> estrutura completa do banco
├── config.php           -> BASE (pasta) e chave da API
├── index.php            -> minha lista (ler, atualizar status, excluir)
├── css/style.css        -> estilo
├── database/connect.php -> conexão PDO
├── includes/            -> functions.php, header.php, footer.php
├── login/               -> login, cadastrar, logout, verifica_user
└── app/adicionar.php    -> busca com sugestões + cadastro do título
```

## 3. Passo a passo
**Passo 1 — Banco.** Crie o banco e as tabelas:
```
psql -U postgres -c "CREATE DATABASE db_streamlist;"
psql -U postgres -d streamlist -f schema.sql
```
**Passo 2 — Conexão.** Em `database/connect.php` ajuste `$host`, `$user` e `$pass`.

**Passo 3 — Nada a configurar para as capas.** A busca usa TVMaze (séries) e iTunes Search (filmes), que não exigem chave.

**Passo 4 — Rodar.** Dentro da pasta `streamlist`:
```
php -S localhost:8000
```
Abra http://localhost:8000/login/cadastrar.php, crie sua conta e faça login.
(Se rodar dentro do htdocs/www de um servidor, coloque em `config.php`: `define('BASE','/streamlist');`)

## 4. Como usar
1. **+ Adicionar** > digite o título > clique numa sugestão (aparece a capa) > escolha o status.
2. Na **Minha lista**, use os filtros, troque o status pelo seletor ou remova o título.

## 5. Como funciona a busca (resumo)
O JavaScript espera você parar de digitar (400 ms), chama o TVMaze (séries) e o iTunes Search (filmes), mostra até 6 sugestões com capa
e, ao clicar, guarda título, tipo e URL da capa em campos escondidos do formulário. O PHP só salva no banco.

## 6. Problemas comuns
- *could not find driver*: ative `pdo_pgsql` no `php.ini`.
- *Sem sugestões*: confira sua internet e o console do navegador (F12). Filmes muito novos ou raros podem não existir no iTunes.
- *Redirecionamento errado*: ajuste o `BASE` em `config.php`.

---
## Atualização v2
O banco inteiro agora está em `schema.sql` (não existe mais `migracao.sql`). Se já tinha a tabela `titulos` antiga, rode `DROP TABLE titulos;` e depois o `schema.sql`.

Novidades:
- **Capa à direita** ao digitar: aparece a capa, o ano, o tipo e a sinopse do 1º resultado; passar o mouse
  sobre outra sugestão troca a prévia; clicar escolhe o título.
- **Nota (1 a 5) e comentário** ao adicionar ou em *Avaliar / Editar*.
  Regra: se o status for **Quero assistir**, nota e comentário ficam bloqueados (a tela desabilita os campos
  e o PHP também ignora/apaga esses dados em `aplicar_regras()`).
- **Painel de estatísticas** no topo (totais por status e média das notas).
- **Busca dentro da lista** (por nome) e filtros por status e por tipo (filme/série).
- Novo arquivo `app/editar.php`.

## Atualização v3
Removido o TMDB: agora as capas vêm de **TVMaze** (séries) e **iTunes Search** (filmes), sem cadastro nem chave.

## Atualização v4
- Corrigido o painel da capa (o atributo `hidden` era anulado pelo CSS).
- `banco.sql` e `migracao.sql` unificados em `schema.sql`.

## Atualização v5
- Busca em 3 fontes abertas (iTunes, TVMaze e IMDb), com filtro de relevância: resultados sem relação com o que foi digitado são descartados.
- Resultados atrasados são ignorados (antes a capa de uma busca antiga podia aparecer).
- Opção **"Não achou? Adicionar manualmente"**: escolhe o tipo e, se quiser, cola a URL de uma capa.
- Dica: as bases são em inglês; se um título em português não aparecer, tente o nome original (ex.: "Spider-Man").

## Atualização v6 — Sorteio, Progresso da série e Favoritos
Rode no banco (se já tem a tabela): 
`ALTER TABLE titulos ADD COLUMN temporada INT, ADD COLUMN episodio INT, ADD COLUMN favorito BOOLEAN NOT NULL DEFAULT FALSE;`

1. **🎲 O que assistir hoje?** (`index.php` + função `sortear()`): faz `SELECT ... ORDER BY RANDOM() LIMIT 1`
   entre os títulos "Quero assistir". Dá para começar a assistir direto (muda o status para "Assistindo").
2. **Progresso da série** (`app/editar.php`): campos temporada/episódio, que só aparecem para séries "Assistindo".
   O card mostra, por exemplo, `T2 · Ep. 5`.
3. **♥ Favoritos** (função `favoritar()`): botão de coração no card (`SET favorito = NOT favorito`) e filtro "Só favoritos".

### Roteiro curto para apresentar
- Cadastro/login com sessão e senha criptografada -> `login/`
- CRUD: adicionar (Create), lista (Read), avaliar/editar (Update), remover (Delete)
- Regra de negócio: "Quero assistir" não pode ter nota nem comentário (`aplicar_regras()`)
- Busca com sugestões: JavaScript + APIs abertas -> `app/adicionar.php`
- Extras: estatísticas, filtros, sorteio, progresso e favoritos

## Atualização v7
- Removida a sinopse/elenco do painel e do cadastro.
- Se o cadastro de um título falhar, a tela mostra o motivo do erro do banco.
- Se o usuário da sessão não existir mais no banco (ex.: tabelas recriadas), o sistema volta para o login.
