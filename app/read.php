<?php
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../login/verifica_user.php';

$titulos = relatorio($conexao, $_SESSION['id']);

// Nomes amigáveis para exibir na tela
$nomes = [
    'quero' => 'Quero assistir',
    'assistindo' => 'Assistindo',
    'assistido' => 'Assistidos'
];

// Texto digitado na busca (?busca=naruto)
$busca = '';
if (isset($_GET['busca'])) {
    $busca = trim($_GET['busca']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StreamList - Minha vitrine</title>
    <link rel="stylesheet" href="/streamlist/assets/style.css">
    <link rel="icon" type="image/svg+xml" href="/streamlist/logo-icone.svg">
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>

    <main class="container">

        <!-- Formulário contendo APENAS a busca por título -->
        <form class="busca" action="" method="get">
            <input type="text" name="busca" placeholder="Buscar por título..." value="<?php echo $busca; ?>">
            <input class="btn" type="submit" value="Buscar">
            <?php if ($busca != '') { ?>
                <a class="btn-sec" href="/streamlist/app/read.php">Limpar</a>
            <?php } ?>
        </form>

        <div class="grade">
            <?php
            $mostrou = false;

            foreach ($titulos as $t) {
                // Checa se o título bate com a busca (ou se a busca está vazia)
                if ($busca == '' || stripos($t['titulo'], $busca) !== false) {
                    $mostrou = true;
            ?>
                <article class="card">
                    <div class="poster">
                        <?php if ($t['capa'] != '') { ?>
                            <img src="<?php echo $t['capa']; ?>" alt="Capa de <?php echo $t['titulo']; ?>">
                        <?php } else { ?>
                            <div class="sem-capa">Sem capa</div>
                        <?php } ?>

                        <span class="selo selo-<?php echo $t['status']; ?>"><?php echo $nomes[$t['status']]; ?></span>

                        <?php if ($t['nota'] != null) { ?>
                            <span class="nota">★ <?php echo $t['nota']; ?></span>
                        <?php } ?>
                    </div>
                    <div class="info">
                        <h3><?php echo $t['titulo']; ?></h3>

                        <?php if ($t['tipo'] == 'filme') { ?>
                            <p class="tipo">Filme</p>
                        <?php } else { ?>
                            <p class="tipo">Série</p>
                        <?php } ?>

                        <?php if ($t['review'] != '') { ?>
                            <p class="review">“<?php echo $t['review']; ?>”</p>
                        <?php } ?>

                        <div class="links">
                            <a href="/streamlist/app/update.php?id=<?php echo $t['id']; ?>">Editar</a>
                            <a class="excluir" href="/streamlist/app/delete.php?id=<?php echo $t['id']; ?>">Excluir</a>
                        </div>
                    </div>
                </article>
            <?php
                }
            }
            ?>
        </div>

        <?php if ($mostrou == false) { ?>
            <?php if ($busca != '') { ?>
                <p class="vazio">Nenhum título encontrado para essa busca.</p>
            <?php } else { ?>
                <p class="vazio">Nada por aqui ainda. <a href="/streamlist/app/create.php">Adicione um título</a>.</p>
            <?php } ?>
        <?php } ?>

    </main>

    <?php include __DIR__ . '/../includes/footer.php'; ?>
</body>
</html>