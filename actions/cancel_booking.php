<?php
session_start();
require 'database.php';

// 1. Verifica se está logado
if (!isset($_SESSION['logados']) || $_SESSION['logados'] !== true) {
    header("Location: index.php");
    exit();
}

// 2. Verifica permissão (apenas perfis autorizados podem excluir)
$perfil = $_SESSION['perfil'] ?? '';
if ($perfil != 'secretario' && $perfil != 'ti' && $perfil != 'admin') {
    $_SESSION['msg'] = "Você não tem permissão para realizar esta ação.";
    header("Location: dashboard.php");
    exit();
}

// 3. Processa a exclusão
if (isset($_GET['id'])) {
    // Filtra o ID para garantir que é um número
    $id = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

    if ($id) {
        try {
            $stmt = $conn->prepare("DELETE FROM agendamentos WHERE id = :id");
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                $_SESSION['msg'] = "Agendamento excluído com sucesso!";
            } else {
                $_SESSION['msg'] = "Erro ao tentar excluir o agendamento.";
            }
        } catch (PDOException $e) {
            $_SESSION['msg'] = "Erro no banco de dados: " . $e->getMessage();
        }
    } else {
        $_SESSION['msg'] = "ID de agendamento inválido.";
    }
}

// 4. Retorna ao painel
header("Location: dashboard.php");
exit();
?>