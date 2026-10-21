<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<header class="topo">
    <a class="logo" href="/streamlist/index.php">
        <img src="/streamlist/includes/logo.svg" alt="StreamList" class="logo-img">
    </a>
    <nav class="menu">
        <?php if (isset($_SESSION['id'])) { ?>
            <a href="/streamlist/app/read.php">Minha vitrine</a>
            <a class="btn-add" href="/streamlist/app/create.php">+ Adicionar título</a>
            <a href="/streamlist/login/logout.php">Sair</a>
        <?php } else { ?>
            <a href="/streamlist/login/login.php">Entrar</a>
            <a class="btn-add" href="/streamlist/login/cadastrar.php">Cadastre-se</a>
        <?php } ?>
    </nav>
</header>