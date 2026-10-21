<?php require_once __DIR__ . '/includes/functions.php'; ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamList - Sua vitrine de filmes e séries</title>
    
    <link rel="icon" type="image/svg+xml" href="/streamlist/logo-icone.svg">

    <link rel="stylesheet" href="/streamlist/assets/style.css">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>

    <main class="container">

        <!-- ---------- Destaque ---------- -->
        <section class="home-hero">
            <div class="home-texto">
                <h1>Tudo o que você assiste, <span>organizado</span> em um só lugar.</h1>
                <p>
                    Cadastre filmes e séries, acompanhe o que já viu, o que está vendo
                    e o que pretende ver. Dê uma nota e escreva sua opinião/review.
                </p>
                <div class="acoes">
                    <?php if (isset($_SESSION['id'])) { ?>
                        <a class="btn" href="/streamlist/app/read.php">Ver minha vitrine</a>
                        <a class="btn-sec" href="/streamlist/app/create.php">Adicionar título</a>
                    <?php } else { ?>
                        <a class="btn" href="/streamlist/login/cadastrar.php">Criar conta grátis</a>
                        <a class="btn-sec" href="/streamlist/login/login.php">Já tenho conta</a>
                    <?php } ?>
                </div>
            </div>

            <!-- Exemplo visual da vitrine (só decoração) -->
            <div class="home-mostra">
                <div class="mini-card">
                    <div class="mini-capa mini-capa-1">
                        <span class="selo selo-assistido">Assistido</span>
                        <span class="nota">★ 9</span>
                    </div>
                    <p class="mini-titulo">Filme</p>
                </div>
                <div class="mini-card">
                    <div class="mini-capa mini-capa-2">
                        <span class="selo selo-assistindo">Assistindo</span>
                        <span class="nota">★ 8</span>
                    </div>
                    <p class="mini-titulo">Série</p>
                </div>
                <div class="mini-card">
                    <div class="mini-capa mini-capa-3">
                        <span class="selo selo-quero">Quero assistir</span>
                    </div>
                    <p class="mini-titulo">Filme</p>
                </div>
            </div>
        </section>

        <!-- ---------- Chamada final ---------- -->
        <section class="home-final">
            <h2>Pronto para montar a sua vitrine?</h2>
            <p>Comece agora e nunca mais esqueça o que queria assistir.</p>
            <div class="acoes">
                <?php if (isset($_SESSION['id'])) { ?>
                    <a class="btn" href="/streamlist/app/create.php">Adicionar um título</a>
                <?php } else { ?>
                    <a class="btn" href="/streamlist/login/cadastrar.php">Começar agora</a>
                <?php } ?>
            </div>
        </section>

    </main>

    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>