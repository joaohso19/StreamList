<?php
require_once __DIR__ . '/../database/connect.php';

// ---------- USUÁRIOS ----------

function cadastrar_user($conexao, $email, $senha){
    $sql = "INSERT INTO usuarios (email, senha) VALUES (:email, :senha)";
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":senha", $senha_hash);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return false;
    }
}

function consulta_user($conexao, $email){
    $sql = "SELECT id, email, senha FROM usuarios WHERE email = :email";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
        return $usuario;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// ---------- FILMES E SÉRIES ----------

function cadastrar($conexao, $usuario_id, $titulo, $tipo, $status, $nota, $review, $capa){
    $sql = "INSERT INTO titulos (usuario_id, titulo, tipo, status, nota, review, capa)
            VALUES (:usuario_id, :titulo, :tipo, :status, :nota, :review, :capa)";

    // nota só vale para "assistindo" ou "assistido"
    if ($status == "quero" || $nota === "" || $nota === null) {
        $nota = null;
    }

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":nota", $nota);
        $stmt->bindParam(":review", $review);
        $stmt->bindParam(":capa", $capa);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return false;
    }
}

// Verifica se o usuário já tem esse título (mesmo nome e mesmo tipo)
function titulo_existe($conexao, $usuario_id, $titulo, $tipo){
    $sql = "SELECT id FROM titulos
            WHERE usuario_id = :usuario_id AND LOWER(titulo) = LOWER(:titulo) AND tipo = :tipo";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->execute();

        $existe = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existe) {
            return true;
        }
        return false;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return false;
    }
}

// Edita status, nota, review e capa
function atualizar($conexao, $id, $usuario_id, $status, $nota, $review, $capa){
    $sql = "UPDATE titulos SET status = :status, nota = :nota, review = :review, capa = :capa
            WHERE id = :id AND usuario_id = :usuario_id";

    if ($status == "quero" || $nota === "" || $nota === null) {
        $nota = null;
    }

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":nota", $nota);
        $stmt->bindParam(":review", $review);
        $stmt->bindParam(":capa", $capa);

        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return false;
    }
}

function excluir($conexao, $id, $usuario_id){
    $sql = "DELETE FROM titulos WHERE id = :id AND usuario_id = :usuario_id";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return false;
    }
}

function consultar($conexao, $id, $usuario_id){
    $sql = "SELECT id, titulo, tipo, status, nota, review, capa
            FROM titulos WHERE id = :id AND usuario_id = :usuario_id";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        $titulo = $stmt->fetch(PDO::FETCH_ASSOC);
        return $titulo;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Lista tudo que o usuário adicionou (vitrine)
function relatorio($conexao, $usuario_id){
    $sql = "SELECT * FROM titulos WHERE usuario_id = :usuario_id ORDER BY id DESC";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":usuario_id", $usuario_id);
        $stmt->execute();

        $titulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $titulos;
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return [];
    }
}
?>