<?php require_once __DIR__ . '/../includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamList - Login</title>
    <link rel="stylesheet" href="/streamlist/assets/style.css">
    <link rel="icon" type="image/svg+xml" href="/streamlist/logo-icone.svg">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <main class="container estreito">
        <h1>Faça seu Login</h1>
        <form class="card-form" action="" method="post">
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" placeholder="Digite seu e-mail" required>
            <label for="senha">Senha:</label>
            <input type="password" name="senha" id="senha" placeholder="Digite sua senha" required>
            <div class="acoes">
                <input class="btn" type="submit" value="Entrar">
                <input class="btn-sec" type="reset" value="Limpar">
            </div>
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $usuario = consulta_user($conexao, $_POST['email']);

            if ($usuario && password_verify($_POST['senha'], $usuario['senha'])) {
                session_start();
                $_SESSION['id'] = $usuario['id'];
                header("Location: /streamlist/app/read.php");
                exit();
            } else {
                echo "<p class='erro'>E-mail ou senha inválido(s).</p>";
            }
        }
        ?>
    </main>
    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>