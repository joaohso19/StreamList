<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../login/verifica_user.php';

$uid = $_SESSION['id'];
$id = $_GET['id'] ?? ($_POST['id'] ?? 0);
$t = consultar($conexao, $id, $uid);
if (!$t) {
    header("Location: " . BASE . "/index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    atualizar($conexao, $id, $uid, $_POST['status'], $_POST['nota'] ?? 0, $_POST['comentario'] ?? "",
              $_POST['temporada'] ?? 0, $_POST['episodio'] ?? 0);
    header("Location: " . BASE . "/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar - StreamList</title>
    <link rel="stylesheet" href="<?= BASE ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="largo">
        <h1>Avaliar / Editar</h1>
        <div class="duas">
            <form action="" method="post">
                <input type="hidden" name="id" value="<?= $t['id'] ?>">

                <label for="status">Status:</label>
                <select name="status" id="status">
                    <option value="quero" <?= $t['status'] == 'quero' ? 'selected' : '' ?>>Quero assistir</option>
                    <option value="assistindo" <?= $t['status'] == 'assistindo' ? 'selected' : '' ?>>Assistindo</option>
                    <option value="assistido" <?= $t['status'] == 'assistido' ? 'selected' : '' ?>>Assistido</option>
                </select>

                <label for="nota">Nota:</label>
                <select name="nota" id="nota">
                    <option value="0">Sem nota</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= $t['nota'] == $i ? 'selected' : '' ?>><?= estrelas($i) ?> (<?= $i ?>)</option>
                    <?php endfor; ?>
                </select>

                <label for="comentario">Comentário:</label>
                <textarea name="comentario" id="comentario" rows="5" placeholder="O que você achou?"><?= htmlspecialchars($t['comentario'] ?? '') ?></textarea>
                <small id="aviso" class="aviso"></small>

                <?php if ($t['tipo'] == 'serie'): ?>
                    <div id="progresso" class="manual">
                        <strong>Onde você parou?</strong>
                        <label for="temporada">Temporada:</label>
                        <input type="number" name="temporada" id="temporada" min="1" value="<?= $t['temporada'] ?>">
                        <label for="episodio">Episódio:</label>
                        <input type="number" name="episodio" id="episodio" min="1" value="<?= $t['episodio'] ?>">
                    </div>
                <?php endif; ?>

                <input type="submit" value="Salvar" class="btn">
                <a href="<?= BASE ?>/index.php" class="cancelar">Cancelar</a>
            </form>

            <aside class="painel">
                <?php if ($t['capa']): ?>
                    <img src="<?= htmlspecialchars($t['capa']) ?>" alt="Capa">
                <?php else: ?>
                    <div class="capa-vazia">Sem capa</div>
                <?php endif; ?>
                <h2><?= htmlspecialchars($t['titulo']) ?></h2>
                <small><?= $t['tipo'] == 'serie' ? 'Série' : 'Filme' ?><?= $t['ano'] ? ' · ' . $t['ano'] : '' ?></small>
            </aside>
        </div>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
    <script>
        const status = document.getElementById("status");
        function controlar() {
            const bloqueado = status.value == "quero";
            document.getElementById("nota").disabled = bloqueado;
            document.getElementById("comentario").disabled = bloqueado;
            const prog = document.getElementById("progresso");
            if (prog) { prog.hidden = status.value != "assistindo"; }   // progresso só para "assistindo"
            document.getElementById("aviso").textContent = bloqueado ? "Avaliação liberada só para o que você está assistindo ou já assistiu." : "";
        }
        status.addEventListener("change", controlar);
        controlar();
    </script>
</body>
</html>
