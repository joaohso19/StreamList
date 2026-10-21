<?php
// Conexão com o banco PostgreSQL (mesmo estilo da aula)
// Troque os dados abaixo pelos do seu banco
$host = "localhost";
$dbname = "streamlist";
$user = "streamlist_user";
$pass = "stream";

try {
    $conexao = new PDO(
        "pgsql:host=$host;dbname=$dbname",
        $user,
        $pass
    );
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage();
    exit();
}
?>
