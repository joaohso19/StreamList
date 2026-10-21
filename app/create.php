<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../login/verifica_user.php';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamList - Adicionar título</title>
    <link rel="stylesheet" href="/streamlist/assets/style.css">
    <link rel="icon" type="image/svg+xml" href="/streamlist/logo-icone.svg">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">
        <h1>Adicionar filme ou série</h1>
        
        <?php
        // Processamento do formulário no estilo simplificado das imagens
        $erro = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $titulo_novo = trim(isset($_POST['titulo']) ? $_POST['titulo'] : '');
            $tipo_novo = isset($_POST['tipo']) ? $_POST['tipo'] : 'filme';
            $status = isset($_POST['status']) ? $_POST['status'] : 'quero';
            $nota = isset($_POST['nota']) ? $_POST['nota'] : null;
            $review = isset($_POST['review']) ? $_POST['review'] : '';
            $capa = isset($_POST['capa']) ? $_POST['capa'] : '';

            if (titulo_existe($conexao, $_SESSION['id'], $titulo_novo, $tipo_novo)) {
                $erro = 'Você já adicionou "' . $titulo_novo . '" na sua vitrine.';
            } else {
                $ok = cadastrar($conexao, $_SESSION['id'], $titulo_novo, $tipo_novo, $status, $nota, $review, $capa);

                if ($ok) {
                    header("Location: /streamlist/app/read.php");
                    exit();
                }
            }
        }

        if ($erro != '') {
            echo '<p class="erro">' . $erro . '</p>';
        }
        ?>

        <form class="card-form form-grande" action="" method="post">
            <div class="preview">
                <img id="preview-capa" src="" alt="Capa" style="display:none">
                <div id="sem-capa" class="sem-capa">Sem capa</div>
            </div>

            <div class="campos">
                <label for="titulo">Título do filme ou série</label>
                <input type="text" name="titulo" id="titulo" placeholder="Ex: Naruto" required>

                <div class="linha">
                    <div>
                        <label for="tipo">Tipo</label>
                        <select name="tipo" id="tipo">
                            <option value="filme">Filme</option>
                            <option value="serie">Série</option>
                        </select>
                    </div>
                    <div>
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="quero">Quero assistir</option>
                            <option value="assistindo">Assistindo</option>
                            <option value="assistido">Já assisti</option>
                        </select>
                    </div>
                    <div id="campo-nota" style="display:none">
                        <label for="nota">Nota (1–10)</label>
                        <input type="number" name="nota" id="nota" min="1" max="10" placeholder="Ex: 9">
                    </div>
                </div>

                <label for="review">Comentário / review</label>
                <textarea name="review" id="review" rows="3" placeholder="O que você achou?"></textarea>

                <p class="dica">Capas encontradas pela API (clique para escolher):</p>
                <div id="capas" class="capas"></div>

                <input type="hidden" name="capa" id="capa">

                <div class="acoes">
                    <input class="btn" type="submit" value="Salvar na Vitrine">
                    <a class="btn-sec" href="/streamlist/app/read.php">Cancelar</a>
                </div>
            </div>
        </form>
    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="/streamlist/assets/capas.js"></script>
    <script>
        
// Mostra o campo de nota só quando o status é "assistindo" ou "assistido"
const status = document.getElementById('status');
const campoNota = document.getElementById('campo-nota');
const nota = document.getElementById('nota');

function atualizaNota() {
    if (status.value === 'quero') {
        campoNota.style.display = 'none';
        nota.value = '';
    } else {
        campoNota.style.display = 'flex';
    }
}
status.addEventListener('change', atualizaNota);
atualizaNota();

// ---------- Elementos da capa e da busca ----------
const capasDiv = document.getElementById('capas');
const inputCapa = document.getElementById('capa');
const previewCapa = document.getElementById('preview-capa');
const semCapa = document.getElementById('sem-capa');
const titulo = document.getElementById('titulo');
const tipo = document.getElementById('tipo');
let timer = null;
let numeroBusca = 0;

// ---------- Seleção e preview da capa ----------
function escolheCapa(url, nome) {
    if (!url) return;
    inputCapa.value = url;
    previewCapa.src = url;
    previewCapa.style.display = 'block';
    if (semCapa) semCapa.style.display = 'none';

    if (nome) titulo.value = nome;

    document.querySelectorAll('.capa-opcao').forEach(img => {
        img.classList.toggle('selecionada', img.src === url);
    });
}

function limpaCapa() {
    inputCapa.value = '';
    previewCapa.src = '';
    previewCapa.style.display = 'none';
    if (semCapa) semCapa.style.display = 'flex';
}

// ---------- Busca de capas pelo título digitado ----------
async function buscaCapas() {
    const nome = titulo.value.trim();
    capasDiv.innerHTML = '';
    limpaCapa();
    if (nome.length < 2) return;

    numeroBusca++;
    const minhaBusca = numeroBusca;

    capasDiv.innerHTML = '<span class="dica">Buscando...</span>';
    let capas = [];
    try {
        if (tipo.value === 'filme') {
            capas = await buscaFilmes(nome);
        } else {
            capas = await buscaSeries(nome);
        }
    } catch (erro) {
        capasDiv.innerHTML = '<span class="dica">Não foi possível buscar as capas agora.</span>';
        return;
    }

    if (minhaBusca != numeroBusca) return;

    capasDiv.innerHTML = '';
    if (capas.length === 0) {
        capasDiv.innerHTML = '<span class="dica">Nenhuma capa encontrada para este nome.</span>';
        return;
    }

    capas.forEach(function (item) {
        const img = document.createElement('img');
        img.src = item.capa;
        img.title = item.titulo;
        img.className = 'capa-opcao';
        img.addEventListener('click', function () {
            escolheCapa(item.capa, item.titulo);
        });
        capasDiv.appendChild(img);
    });
}

titulo.addEventListener('input', function () {
    clearTimeout(timer);
    timer = setTimeout(buscaCapas, 400);
});

tipo.addEventListener('change', buscaCapas);
    </script>
</body>
</html>