<?php
require_once __DIR__ . '/../login/verifica_user.php';
ini_set('display_errors', '0');

$chave = '828e49d8c87a7054ab3513d1c25991d2';
$tipo = ($_GET['tipo'] ?? '') === 'tv' ? 'tv' : 'movie';
$q = trim($_GET['q'] ?? '');

header('Content-Type: application/json; charset=utf-8');

if (strlen($q) < 2) {
    echo '[]';
    exit;
}

$url = "https://api.themoviedb.org/3/search/$tipo?api_key=$chave&language=pt-BR&include_adult=false&query=" . urlencode($q);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0');
$resposta = curl_exec($ch);

if ($resposta === false) {
    http_response_code(502);
    echo '[]';
    exit;
}

$dados = json_decode($resposta, true);
$capas = [];
$resultados = [];
foreach (($dados['results'] ?? []) as $item) {
    if (!empty($item['poster_path'])) {
        $nome = $item['title'] ?? $item['name'] ?? '';
        $resultados[] = [
            'titulo' => $nome,
            'capa' => 'https://image.tmdb.org/t/p/w500' . $item['poster_path']
        ];
    }
    if (count($resultados) >= 6) break;
}

echo json_encode($resultados);