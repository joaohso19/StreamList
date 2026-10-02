<header class="topo">
    <a class="logo" href="<?= BASE ?>/index.php">Stream<span>List</span></a>
    <nav>
        <?php if (isset($_SESSION['id'])): ?>
            <a href="<?= BASE ?>/index.php">Minha lista</a>
            <a href="<?= BASE ?>/app/adicionar.php">+ Adicionar</a>
            <a href="<?= BASE ?>/login/logout.php">Sair</a>
        <?php else: ?>
            <a href="<?= BASE ?>/login/login.php">Entrar</a>
            <a href="<?= BASE ?>/login/cadastrar.php">Cadastre-se</a>
        <?php endif; ?>
    </nav>
</header>
