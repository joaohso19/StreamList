<?php
// Verifica se o usuário está logado (mesmo padrão da aula)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['id'])) {
    header("Location: /streamlist/login/login.php");
    exit();
}
?>