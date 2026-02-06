<<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '10.198.0.2';
$usuario = ''; 
$senha = '';   
$banco = 'gapre_agendamento';

try {
    $conn = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erro de conexão: " . $e->getMessage());
}

function gerar_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verificar_csrf_token($token_recebido) {
    if (!isset($_SESSION['csrf_token']) || $token_recebido !== $_SESSION['csrf_token']) {
        die("ERRO DE SEGURANÇA: Token inválido.");
    }
}
?>