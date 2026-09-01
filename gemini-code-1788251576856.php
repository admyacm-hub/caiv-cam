<?php
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Método não permitido.']);
    exit;
}

$host = 'localhost';
$db   = 'nome_da_base';
$user = 'utilizador';
$pass = 'palavra_passe';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro de conexão com a base de dados.']);
    exit;
}

$tipo        = filter_input(INPUT_POST, 'tipo_denuncia', FILTER_SANITIZE_SPECIAL_CHARS);
$descricao   = filter_input(INPUT_POST, 'descricao', FILTER_SANITIZE_SPECIAL_CHARS);
$contacto    = filter_input(INPUT_POST, 'contacto', FILTER_SANITIZE_SPECIAL_CHARS);
$anonimo     = filter_input(INPUT_POST, 'anonimo', FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

if (empty($descricao)) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'A descrição da denúncia é obrigatória.']);
    exit;
}

$contactoFinal = null;
if (!$anonimo && !empty($contacto)) {
    $numeroLimpo = preg_replace('/^\+?244|\D/', '', $contacto);
    if (!preg_match('/^9[1-9][0-9]{7}$/', $numeroLimpo)) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Número de telemóvel angolano inválido. Formato esperado: 9XXXXXXXX.']);
        exit;
    }
    $contactoFinal = '+244 ' . $numeroLimpo;
}

$protocolo = 'DEN-' . strtoupper(bin2hex(random_bytes(4)));

try {
    $sql = "INSERT INTO denuncias (protocolo, tipo, descricao, contacto, anonimo, data_criacao) VALUES (:protocolo, :tipo, :descricao, :contacto, :anonimo, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'protocolo' => $protocolo,
        'tipo'      => $tipo ?: 'Geral',
        'descricao' => $descricao,
        'contacto'  => $contactoFinal,
        'anonimo'   => $anonimo
    ]);

    echo json_encode([
        'sucesso'   => true,
        'mensagem'  => 'Denúncia registada com sucesso.',
        'protocolo' => $protocolo
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao processar o registo da denúncia.']);
}
?>