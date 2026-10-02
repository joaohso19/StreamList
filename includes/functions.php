<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../database/connect.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

function cadastrar_user($conexao, $email, $senha) {
    $sql = "INSERT INTO usuarios (email, senha) VALUES (:email, :senha)";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":email", $email);
        $hash = password_hash($senha, PASSWORD_DEFAULT); // senha criptografada
        $stmt->bindParam(":senha", $hash);
        $stmt->execute();
        return true;
    } catch (PDOException $e) {
        return false; // e-mail já existe
    }
}

function consulta_user($conexao, $email) {
    $sql = "SELECT id, email, senha FROM usuarios WHERE email = :email";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Regra: quem "quer assistir" ainda não pode ter nota nem comentário
function aplicar_regras($status, $nota, $comentario) {
    $nota = (int)$nota;
    if ($status == "quero" || $nota < 1 || $nota > 5) {
        $nota = null;
    }
    $comentario = trim($comentario);
    if ($status == "quero" || $comentario == "") {
        $comentario = null;
    }
    return [$nota, $comentario];
}

function adicionar($conexao, $uid, $titulo, $tipo, $status, $capa, $ano, $sinopse, $nota, $comentario) {
    [$nota, $comentario] = aplicar_regras($status, $nota, $comentario);
    $sql = "INSERT INTO titulos (usuario_id, titulo, tipo, status, capa, ano, sinopse, nota, comentario)
            VALUES (:uid, :titulo, :tipo, :status, :capa, :ano, :sinopse, :nota, :comentario)";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":uid", $uid);
        $stmt->bindParam(":titulo", $titulo);
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":capa", $capa);
        $stmt->bindParam(":ano", $ano);
        $stmt->bindParam(":sinopse", $sinopse);
        $stmt->bindParam(":nota", $nota, PDO::PARAM_INT);
        $stmt->bindParam(":comentario", $comentario);
        $stmt->execute();
        return "";                     // "" = salvou sem erro
    } catch (PDOException $e) {
        return $e->getMessage();       // devolve o motivo do erro para a tela mostrar
    }
}

function listar($conexao, $uid, $status = "", $tipo = "", $busca = "", $fav = "") {
    $sql = "SELECT * FROM titulos WHERE usuario_id = :uid";
    if ($status != "") { $sql .= " AND status = :status"; }
    if ($tipo != "")   { $sql .= " AND tipo = :tipo"; }
    if ($busca != "")  { $sql .= " AND titulo ILIKE :busca"; }
    if ($fav != "")    { $sql .= " AND favorito = TRUE"; }
    $sql .= " ORDER BY id DESC";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":uid", $uid);
        if ($status != "") { $stmt->bindParam(":status", $status); }
        if ($tipo != "")   { $stmt->bindParam(":tipo", $tipo); }
        if ($busca != "")  { $like = "%" . $busca . "%"; $stmt->bindParam(":busca", $like); }
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
        return [];
    }
}

function consultar($conexao, $id, $uid) {
    $sql = "SELECT * FROM titulos WHERE id = :id AND usuario_id = :uid";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Edição completa: status + nota + comentário + progresso da série
function atualizar($conexao, $id, $uid, $status, $nota, $comentario, $temporada, $episodio) {
    [$nota, $comentario] = aplicar_regras($status, $nota, $comentario);

    // Progresso (temporada/episódio) só existe para quem está "assistindo"
    $temporada = (int)$temporada;
    $episodio = (int)$episodio;
    if ($status != "assistindo" || $temporada < 1) { $temporada = null; }
    if ($status != "assistindo" || $episodio < 1)  { $episodio = null; }

    $sql = "UPDATE titulos SET status = :status, nota = :nota, comentario = :comentario,
                   temporada = :temporada, episodio = :episodio
            WHERE id = :id AND usuario_id = :uid";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":status", $status);
        $stmt->bindParam(":nota", $nota, PDO::PARAM_INT);
        $stmt->bindParam(":comentario", $comentario);
        $stmt->bindParam(":temporada", $temporada, PDO::PARAM_INT);
        $stmt->bindParam(":episodio", $episodio, PDO::PARAM_INT);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Troca rápida de status (pelo seletor do card)
function mudar_status($conexao, $id, $uid, $status) {
    $t = consultar($conexao, $id, $uid);
    if ($t) {
        atualizar($conexao, $id, $uid, $status, $t['nota'], $t['comentario'], $t['temporada'], $t['episodio']);
    }
}

function excluir($conexao, $id, $uid) {
    $sql = "DELETE FROM titulos WHERE id = :id AND usuario_id = :uid";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

function estatisticas($conexao, $uid) {
    $sql = "SELECT COUNT(*) AS total,
                   COUNT(*) FILTER (WHERE status = 'assistido') AS assistidos,
                   COUNT(*) FILTER (WHERE status = 'assistindo') AS assistindo,
                   COUNT(*) FILTER (WHERE status = 'quero') AS quero,
                   ROUND(AVG(nota), 1) AS media
            FROM titulos WHERE usuario_id = :uid";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

function estrelas($nota) {
    return str_repeat("★", $nota) . str_repeat("☆", 5 - $nota);
}

// FAVORITO: liga/desliga o coração (NOT inverte true <-> false)
function favoritar($conexao, $id, $uid) {
    $sql = "UPDATE titulos SET favorito = NOT favorito WHERE id = :id AND usuario_id = :uid";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// SORTEIO: pega 1 título aleatório entre os "quero assistir"
function sortear($conexao, $uid) {
    $sql = "SELECT * FROM titulos WHERE usuario_id = :uid AND status = 'quero' ORDER BY RANDOM() LIMIT 1";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":uid", $uid);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC); // false se não tiver nenhum
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}

// Confere se o usuário da sessão ainda existe no banco
function usuario_existe($conexao, $id) {
    $sql = "SELECT 1 FROM usuarios WHERE id = :id";
    try {
        $stmt = $conexao->prepare($sql);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch() ? true : false;
    } catch (PDOException $e) {
        return false;
    }
}
