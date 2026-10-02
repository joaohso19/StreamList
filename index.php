<?php
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/login/verifica_user.php';

$uid = $_SESSION['id'];

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($_POST['acao'] == "status") {
        mudar_status($conexao, $_POST['id'], $uid, $_POST['status']);
    } elseif ($_POST['acao'] == "favoritar") {
        favoritar($conexao, $_POST['id'], $uid);
    } elseif ($_POST['acao'] == "excluir") {
        excluir($conexao, $_POST['id'], $uid);
    }
    header("Location: " . BASE . "/index.php");
    exit();
}

$status = $_GET['status'] ?? "";
$tipo = $_GET['tipo'] ?? "";
$busca = trim($_GET['q'] ?? "");
$fav = $_GET['fav'] ?? "";
$titulos = listar($conexao, $uid, $status, $tipo, $busca, $fav);

// Se clicou em "O que assistir hoje?", sorteia um título
$sorteio = isset($_GET['sortear']);
$sorteado = $sorteio ? sortear($conexao, $uid) : false;
$stats = estatisticas($conexao, $uid);
$nomes = ["assistido" => "Assistido", "assistindo" => "Assistindo", "quero" => "Quero assistir"];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha lista - StreamList</title>
    <link rel="stylesheet" href="<?= BASE ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    <main>
        <h1>Minha lista</h1>

        <div class="stats">
            <div><b><?= $stats['total'] ?></b><span>Títulos</span></div>
            <div><b><?= $stats['assistidos'] ?></b><span>Assistidos</span></div>
            <div><b><?= $stats['assistindo'] ?></b><span>Assistindo</span></div>
            <div><b><?= $stats['quero'] ?></b><span>Quero assistir</span></div>
            <div><b><?= $stats['media'] ?? '-' ?></b><span>Média das notas</span></div>
        </div>

        <a class="btn-sorteio" href="?sortear=1">🎲 O que assistir hoje?</a>

        <?php if ($sorteio): ?>
            <div class="sorteio">
                <?php if ($sorteado): ?>
                    <?php if ($sorteado['capa']): ?><img src="<?= htmlspecialchars($sorteado['capa']) ?>" alt="Capa"><?php endif; ?>
                    <div>
                        <small>Hoje você pode assistir:</small>
                        <h2><?= htmlspecialchars($sorteado['titulo']) ?></h2>
                        <p><?= $sorteado['tipo'] == 'serie' ? 'Série' : 'Filme' ?><?= $sorteado['ano'] ? ' · ' . $sorteado['ano'] : '' ?></p>
                        <form method="post">
                            <input type="hidden" name="acao" value="status">
                            <input type="hidden" name="id" value="<?= $sorteado['id'] ?>">
                            <input type="hidden" name="status" value="assistindo">
                            <button class="btn">▶ Começar a assistir</button>
                        </form>
                        <a href="?sortear=1">Sortear outro</a>
                    </div>
                <?php else: ?>
                    <p>Você não tem nada em "Quero assistir". <a href="<?= BASE ?>/app/adicionar.php">Adicione um título!</a></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="get" class="filtros">
            <input type="text" name="q" placeholder="Buscar na minha lista..." value="<?= htmlspecialchars($busca) ?>">
            <select name="status">
                <option value="">Todos os status</option>
                <?php foreach ($nomes as $k => $n): ?>
                    <option value="<?= $k ?>" <?= $status == $k ? 'selected' : '' ?>><?= $n ?></option>
                <?php endforeach; ?>
            </select>
            <select name="tipo">
                <option value="">Filmes e séries</option>
                <option value="filme" <?= $tipo == 'filme' ? 'selected' : '' ?>>Filmes</option>
                <option value="serie" <?= $tipo == 'serie' ? 'selected' : '' ?>>Séries</option>
            </select>
            <label class="check"><input type="checkbox" name="fav" value="1" <?= $fav ? 'checked' : '' ?>> ♥ Só favoritos</label>
            <button class="btn">Filtrar</button>
        </form>

        <?php if (count($titulos) == 0): ?>
            <p class="vazio">Nada por aqui. <a href="<?= BASE ?>/app/adicionar.php">Adicione um título!</a></p>
        <?php endif; ?>

        <div class="grade">
            <?php foreach ($titulos as $t): ?>
                <div class="card">
                    <?php if ($t['capa']): ?>
                        <img src="<?= htmlspecialchars($t['capa']) ?>" alt="Capa de <?= htmlspecialchars($t['titulo']) ?>">
                    <?php else: ?>
                        <div class="sem-capa">Sem capa</div>
                    <?php endif; ?>
                    <div class="info">
                        <div class="titulo-linha">
                            <strong><?= htmlspecialchars($t['titulo']) ?></strong>
                            <form method="post">
                                <input type="hidden" name="acao" value="favoritar">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button class="coracao" title="Favoritar"><?= $t['favorito'] ? '♥' : '♡' ?></button>
                            </form>
                        </div>
                        <small><?= $t['tipo'] == 'serie' ? 'Série' : 'Filme' ?><?= $t['ano'] ? ' · ' . $t['ano'] : '' ?></small>
                        <?php if ($t['temporada']): ?>
                            <span class="progresso">T<?= $t['temporada'] ?><?= $t['episodio'] ? ' · Ep. ' . $t['episodio'] : '' ?></span>
                        <?php endif; ?>
                        <?php if ($t['nota']): ?>
                            <span class="estrelas"><?= estrelas($t['nota']) ?></span>
                        <?php endif; ?>
                        <?php if ($t['comentario']): ?>
                            <p class="comentario">“<?= htmlspecialchars($t['comentario']) ?>”</p>
                        <?php endif; ?>
                        <form method="post">
                            <input type="hidden" name="acao" value="status">
                            <input type="hidden" name="id" value="<?= $t['id'] ?>">
                            <select name="status" onchange="this.form.submit()">
                                <?php foreach ($nomes as $k => $n): ?>
                                    <option value="<?= $k ?>" <?= $t['status'] == $k ? 'selected' : '' ?>><?= $n ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <div class="botoes">
                            <a class="editar" href="<?= BASE ?>/app/editar.php?id=<?= $t['id'] ?>">Avaliar / Editar</a>
                            <form method="post" onsubmit="return confirm('Remover este título?')">
                                <input type="hidden" name="acao" value="excluir">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button class="remover">Remover</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
