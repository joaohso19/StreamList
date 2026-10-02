<?php
require_once __DIR__ . '/../includes/functions.php';

// Precisa estar logado e o usuário precisa existir no banco
if (!isset($_SESSION['id']) || !usuario_existe($conexao, $_SESSION['id'])) {
    $_SESSION = array();
    session_destroy();
    header("Location: " . BASE . "/login/login.php");
    exit();
}
