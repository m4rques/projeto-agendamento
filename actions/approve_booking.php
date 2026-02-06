<?php
session_start();
require 'database.php';
// --- VERIFICAÇÃO DE LOGIN ---
if (!isset($_SESSION['logados']) || $_SESSION['logados'] !== true) {
    header("Location: index.php");
    exit();
}
// 1. LISTA DE QUEM MANDA (Pode aprovar)
// Se você tiver o perfil 'gerente' no banco, ele entra aqui.
$perfis_autorizados = ['admin', 'gerente', 'ti'];

// 2. VERIFICAÇÃO RIGOROSA
// Se o perfil do usuário logado NÃO estiver na lista acima, para tudo.
if (!isset($_SESSION['perfil']) || !in_array($_SESSION['perfil'], $perfis_autorizados)) {
    die("ACESSO NEGADO: Você não tem permissão para aprovar ou recusar solicitações.");
}

// 3. SEGURANÇA E EXECUÇÃO
if (isset($_GET['id']) && isset($_GET['acao'])) {
    
    // Verifica Token CSRF
    verificar_csrf_token($_GET['csrf_token'] ?? '');

    $status = $_GET['acao']; 
    $id = $_GET['id'];
    
    // Só aceita 'confirmado' ou 'recusado' para evitar injeção de outros status
    if(in_array($status, ['confirmado', 'recusado'])) {
        $stmt = $conn->prepare("UPDATE agendamentos SET status = ? WHERE id = ?");
        $stmt->execute([$status, $id]);
        $_SESSION['msg'] = "Solicitação atualizada para: " . strtoupper($status);
    }
}

header("Location: dashboard.php");
exit;
?>