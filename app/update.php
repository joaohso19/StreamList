<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../login/verifica_user.php';

// Se enviou o formulário, atualiza antes de mostrar a página
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $ok = atualizar(
        $conexao,
        $_POST['id'],
        $_SESSION['id'],
        $_POST['status'],
        $_POST['nota'],
        $_POST['review'],
        $_POST['capa']
    );
    if ($ok) {
        header("Location: /streamlist/app/read.php");
        exit();
    }
}

// pega o id que veio pelo link (?id=)
$id = 0;
if (isset($_GET['id'])) {
    $id = $_GET['id'];
}

$t = consultar($conexao, $id, $_SESSION['id']);

// se não achou o título, volta para a vitrine
if (!$t) {
    header("Location: /streamlist/app/read.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamList - Editar título</title>
    <link rel="stylesheet" href="/streamlist/assets/style.css">
    <link rel="icon" type="image/svg+xml" href="/streamlist/logo-icone.svg">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="container">
        <h1>Editar: <?php echo htmlspecialchars($t['titulo']); ?></h1>

        <form class="card-form form-grande" action="" method="post">
            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">

            <div class="preview">
                <?php if ($t['capa']) { ?>
                    <img id="preview-capa" src="<?php echo htmlspecialchars($t['capa']); ?>" alt="Capa">
                    <div id="sem-capa" class="sem-capa" style="display:none">Sem capa</div>
                <?php } else { ?>
                    <img id="preview-capa" src="" alt="Capa" style="display:none">
                    <div id="sem-capa" class="sem-capa">Sem capa</div>
                <?php } ?>
            </div>

            <div class="campos">
                <div class="linha">
                    <div>
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="quero" <?php if ($t['status'] == 'quero') { echo 'selected'; } ?>>Quero assistir</option>
                            <option value="assistindo" <?php if ($t['status'] == 'assistindo') { echo 'selected'; } ?>>Assistindo</option>
                            <option value="assistido" <?php if ($t['status'] == 'assistido') { echo 'selected'; } ?>>Já assisti</option>
                        </select>
                    </div>
                    <div id="campo-nota" style="display:none">
                        <label for="nota">Nota (1–10)</label>
                        <input type="number" name="nota" id="nota" min="1" max="10"
                               value="<?php echo $t['nota']; ?>" placeholder="Ex: 9">
                    </div>
                </div>

                <label for="review">Comentário / review</label>
                <textarea name="review" id="review" rows="3" placeholder="O que você achou?"><?php echo htmlspecialchars((string)$t['review']); ?></textarea>


                <input type="hidden" name="capa" id="capa" value="<?php echo htmlspecialchars((string)$t['capa']); ?>">

                <div class="acoes">
                    <input class="btn" type="submit" value="Atualizar">
                    <a class="btn-sec" href="/streamlist/app/read.php">Cancelar</a>
                </div>
            </div>
        </form>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>

    <script src="/streamlist/assets/capas.js"></script>
    <script>
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

    // ---------- busca de capas pelo título já cadastrado ----------
    const nomeTitulo = <?php echo json_encode($t['titulo']); ?>;
    const tipoTitulo = <?php echo json_encode($t['tipo']); ?>;

    async function buscaCapas() {
        let capas = [];
        try {
            if (tipoTitulo === 'filme') {
                capas = await buscaFilmes(nomeTitulo);
                if (capas.length === 0) capas = await buscaSeries(nomeTitulo);
            } else {
                capas = await buscaSeries(nomeTitulo);
                if (capas.length === 0) capas = await buscaFilmes(nomeTitulo);
            }
        } catch (erro) {
            capasDiv.innerHTML = '<span class="dica">Não foi possível buscar as capas agora.</span>';
            return;
        }

        if (capas.length === 0) {
            capasDiv.innerHTML = '<span class="dica">Nenhuma capa encontrada para este nome.</span>';
            return;
        }

        capas.forEach(function (url) {
            const img = document.createElement('img');
            img.src = url;
            img.className = 'capa-opcao';
            img.addEventListener('click', function () {
                escolheCapa(url);
            });
            capasDiv.appendChild(img);
        });
    }

    buscaCapas();
    </script>
</body>
</html>