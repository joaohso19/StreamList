<?php
// Conexão com o banco PostgreSQL (mesmo estilo da aula)
// Troque os dados abaixo pelos do seu banco
$host = "192.168.10.51";
$dbname = "final_teste";
$user = "joao";
$pass = "jotinha";

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