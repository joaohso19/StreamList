<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../login/verifica_user.php';

// Se confirmou a exclusão (POST), remove e volta para a vitrine
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if (excluir($conexao, $_POST['id'], $_SESSION['id'])) {
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
    <title>StreamList - Exclusão</title>
    <link rel="stylesheet" href="/streamlist/assets/style.css">
    <link rel="icon" type="image/svg+xml" href="/streamlist/logo-icone.svg">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="container estreito">
        <h1>Excluir título</h1>
        <form class="card-form" action="" method="post">
            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
            <p>Tem certeza que deseja excluir <strong><?php echo htmlspecialchars($t['titulo']); ?></strong> da sua vitrine?</p>
            <div class="acoes">
                <input class="btn" type="submit" value="Excluir">
                <a class="btn-sec" href="/streamlist/app/read.php">Cancelar</a>
            </div>
        </form>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>