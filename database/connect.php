<?php
$host = "192.168.10.51";
$dbname = "db_streamlist";
$user = "joao";
$pass = "jotinha";

try {
    $conexao = new PDO("pgsql:host=$host;dbname=$dbname", $user, $pass);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro: " . $e->getMessage());
}
