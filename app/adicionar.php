<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../login/verifica_user.php';

$erro = "";
if ($_SERVER['REQUEST_METHOD'] == "POST" && $_POST['titulo'] != "") {
    $erro = adicionar($conexao, $_SESSION['id'], $_POST['titulo'], $_POST['tipo'], $_POST['status'],
                      $_POST['capa'], $_POST['ano'], "", $_POST['nota'] ?? 0, $_POST['comentario'] ?? "");
    if ($erro == "") {
        header("Location: " . BASE . "/index.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar - StreamList</title>
    <link rel="stylesheet" href="<?= BASE ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="largo">
        <h1>Adicionar título</h1>
        <?php if ($erro): ?>
            <p class="erro">Não foi possível salvar: <?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>
        <div class="duas">
            <form action="" method="post" autocomplete="off" id="form">
                <label for="busca">Buscar filme ou série:</label>
                <div class="busca-wrap">
                    <input type="text" id="busca" placeholder="Comece a digitar o título...">
                    <ul id="sugestoes"></ul>
                </div>

                <div id="manual" class="manual" hidden>
                    <label for="m-tipo">Tipo:</label>
                    <select id="m-tipo">
                        <option value="filme">Filme</option>
                        <option value="serie">Série</option>
                    </select>
                    <label for="m-capa">URL da capa (opcional):</label>
                    <input type="text" id="m-capa" placeholder="https://...">
                </div>

                <input type="hidden" name="titulo" id="titulo">
                <input type="hidden" name="tipo" id="tipo">
                <input type="hidden" name="capa" id="capa">
                <input type="hidden" name="ano" id="ano">

                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="quero">Quero assistir</option>
                    <option value="assistindo">Assistindo</option>
                    <option value="assistido">Assistido</option>
                </select>

                <label for="nota">Nota:</label>
                <select name="nota" id="nota">
                    <option value="0">Sem nota</option>
                    <option value="1">★☆☆☆☆ (1)</option>
                    <option value="2">★★☆☆☆ (2)</option>
                    <option value="3">★★★☆☆ (3)</option>
                    <option value="4">★★★★☆ (4)</option>
                    <option value="5">★★★★★ (5)</option>
                </select>

                <label for="comentario">Comentário:</label>
                <textarea name="comentario" id="comentario" rows="4" placeholder="O que você achou?"></textarea>
                <small id="aviso" class="aviso"></small>

                <input type="submit" value="Adicionar à lista" class="btn">
            </form>

            <aside class="painel" id="painel">
                <div class="capa-vazia" id="capa-vazia">A capa aparece aqui</div>
                <img id="previa-img" alt="Capa" hidden>
                <h2 id="previa-titulo"></h2>
                <small id="previa-info"></small>
            </aside>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script>
        const busca = document.getElementById("busca");
        const lista = document.getElementById("sugestoes");
        const status = document.getElementById("status");
        let espera;

        // ---- Busca com sugestões (sem cadastro e sem chave) ----
        busca.addEventListener("input", () => {
            clearTimeout(espera);
            const texto = busca.value.trim();
            if (texto.length < 2) { lista.innerHTML = ""; return; }
            espera = setTimeout(() => buscar(texto), 400);
        });

        let contador = 0;

        // Tira acentos, pontuação e maiúsculas: "Homem-Aranha: Um Novo Dia" -> "homem aranha um novo dia"
        const normaliza = t => (t || "").normalize("NFD").replace(/[\u0300-\u036f]/g, "")
            .toLowerCase().replace(/[^a-z0-9 ]/g, " ").replace(/\s+/g, " ").trim();

        // 0 = igual, 1 = contém tudo junto, 2 = tem todas as palavras, 3 = só algumas, 4 = não tem relação
        function pontos(nome, texto) {
            const n = normaliza(nome), t = normaliza(texto);
            if (n == t) return 0;
            if (n.includes(t)) return 1;
            const palavras = t.split(" ");
            const acertos = palavras.filter(w => n.includes(w)).length;
            if (acertos == palavras.length) return 2;
            return acertos > 0 ? 3 : 4;
        }

        const pegar = (url, vazio) => fetch(url).then(r => r.json()).catch(() => vazio);

        async function buscar(texto) {
            const minha = ++contador;
            const q = encodeURIComponent(texto);
            const letra = /^[a-z]/i.test(texto) ? texto[0].toLowerCase() : "x";

            // Três fontes abertas, sem chave: iTunes (filmes em PT), TVMaze (séries) e IMDb (filmes e séries)
            const [itunes, tvmaze, imdb] = await Promise.all([
                pegar(`https://itunes.apple.com/search?term=${q}&media=movie&entity=movie&country=BR&limit=8`, { results: [] }),
                pegar(`https://api.tvmaze.com/search/shows?q=${q}`, []),
                pegar(`https://v2.sg.media-imdb.com/suggestion/${letra}/${encodeURIComponent(texto.toLowerCase())}.json`, { d: [] })
            ]);
            if (minha != contador) return;   // chegou atrasada: o usuário já digitou outra coisa

            let todos = [];

            (itunes.results || []).forEach(f => todos.push({
                nome: f.trackName, ano: (f.releaseDate || "").slice(0, 4), tipo: "filme",
                capa: (f.artworkUrl100 || "").replace("100x100bb", "600x600bb"),
                sinopse: f.longDescription || f.shortDescription || ""
            }));

            tvmaze.forEach(x => todos.push({
                nome: x.show.name, ano: (x.show.premiered || "").slice(0, 4), tipo: "serie",
                capa: x.show.image ? (x.show.image.original || x.show.image.medium).replace("http://", "https://") : "",
                sinopse: (x.show.summary || "").replace(/<[^>]+>/g, "")
            }));

            (imdb.d || []).filter(i => ["movie", "tvMovie", "tvSeries", "tvMiniSeries"].includes(i.qid)).forEach(i => todos.push({
                nome: i.l, ano: i.y ? String(i.y) : "",
                tipo: (i.qid == "tvSeries" || i.qid == "tvMiniSeries") ? "serie" : "filme",
                capa: i.i ? i.i.imageUrl.replace("._V1_.jpg", "._V1_UX500.jpg") : "",
                sinopse: i.s ? "Elenco: " + i.s : ""
            }));

            // Remove o que não tem relação com o texto, ordena por relevância (e com capa primeiro) e tira repetidos
            todos = todos.filter(d => pontos(d.nome, texto) < 4);
            todos.sort((x, y) => (pontos(x.nome, texto) - pontos(y.nome, texto)) || ((y.capa ? 1 : 0) - (x.capa ? 1 : 0)));
            const vistos = new Set();
            const final = todos.filter(d => {
                const chave = d.tipo + normaliza(d.nome) + d.ano;
                if (vistos.has(chave)) return false;
                vistos.add(chave);
                return true;
            }).slice(0, 8);

            mostrar(final, texto);
            previa(final.length > 0 ? final[0] : { nome: "", ano: "", tipo: "", capa: "", sinopse: "" });
        }

        function mostrar(resultados, texto) {
            lista.innerHTML = "";
            resultados.forEach(d => {
                const li = document.createElement("li");
                li.innerHTML = d.capa ? `<img src="${d.capa}">` : `<span class="mini"></span>`;
                const span = document.createElement("span");
                span.textContent = `${d.nome} ${d.ano ? "(" + d.ano + ")" : ""} · ${d.tipo == "serie" ? "Série" : "Filme"}`;
                li.appendChild(span);
                li.onmouseenter = () => previa(d);   // passar o mouse mostra a capa à direita
                li.onclick = () => escolher(d);       // clicar escolhe de verdade
                lista.appendChild(li);
            });
            // Última opção: adicionar do jeito que digitou
            const m = document.createElement("li");
            m.className = "manual-item";
            m.textContent = `✏️ Não achou? Adicionar "${texto}" manualmente`;
            m.onclick = () => manual(texto);
            lista.appendChild(m);
        }

        // Painel da direita
        function previa(d) {
            const img = document.getElementById("previa-img");
            const vazia = document.getElementById("capa-vazia");
            img.onerror = () => { img.hidden = true; vazia.hidden = false; };
            img.onload = () => { img.hidden = false; vazia.hidden = true; };
            if (d.capa) { img.src = d.capa; } else { img.hidden = true; vazia.hidden = false; }
            document.getElementById("previa-titulo").textContent = d.nome;
            document.getElementById("previa-info").textContent = (d.tipo == "serie" ? "Série" : "Filme") + (d.ano ? " · " + d.ano : "");
        }

        function preencher(d) {
            document.getElementById("titulo").value = d.nome;
            document.getElementById("tipo").value = d.tipo;
            document.getElementById("capa").value = d.capa;
            document.getElementById("ano").value = d.ano;
            previa(d);
        }

        function escolher(d) {
            document.getElementById("manual").hidden = true;
            preencher(d);
            busca.value = d.nome;
            lista.innerHTML = "";
        }

        // Modo manual: o usuário define o tipo e (se quiser) cola a URL de uma capa
        let tituloManual = "";
        function manual(texto) {
            tituloManual = texto;
            document.getElementById("manual").hidden = false;
            lista.innerHTML = "";
            atualizarManual();
        }
        function atualizarManual() {
            preencher({
                nome: tituloManual, ano: "", sinopse: "",
                tipo: document.getElementById("m-tipo").value,
                capa: document.getElementById("m-capa").value.trim()
            });
        }
        document.getElementById("m-tipo").addEventListener("change", atualizarManual);
        document.getElementById("m-capa").addEventListener("input", atualizarManual);

        document.getElementById("form").addEventListener("submit", e => {
            if (document.getElementById("titulo").value == "") {
                e.preventDefault();
                alert("Escolha um título na lista de sugestões.");
            }
        });

        // ---- Quem só "quer assistir" não pode avaliar ----
        function controlar() {
            const bloqueado = status.value == "quero";
            document.getElementById("nota").disabled = bloqueado;
            document.getElementById("comentario").disabled = bloqueado;
            document.getElementById("aviso").textContent = bloqueado ? "Avaliação liberada só para o que você está assistindo ou já assistiu." : "";
        }
        status.addEventListener("change", controlar);
        controlar();
    </script>
</body>
</html>
