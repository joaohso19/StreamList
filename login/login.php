<?php
require_once __DIR__ . '/../includes/functions.php';
$erro = "";
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $usuario = consulta_user($conexao, $_POST['email']);
    if ($usuario && password_verify($_POST['senha'], $usuario['senha'])) {
        $_SESSION['id'] = $usuario['id'];
        header("Location: " . BASE . "/index.php");
        exit();
    } else {
        $erro = "E-mail ou senha inválido(s).";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - StreamList</title>
    <link rel="stylesheet" href="<?= BASE ?>/css/style.css">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="caixa">
        <h1>Faça seu Login</h1>
        <form action="" method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Digite seu e-mail" required>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" placeholder="Digite sua senha" required>
            <?php if ($erro) echo "<p class='erro'>$erro</p>"; ?>
            <input type="submit" value="Entrar" class="btn">
        </form>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>
