<?php
session_start();
require 'database.php';

// 1. VERIFICAÇÃO DE LOGIN MAIS ROBUSTA
if (!isset($_SESSION['logados']) || $_SESSION['logados'] !== true) {
    header("Location: index.php");
    exit();
}

// 2. TENTATIVA DE RECUPERAR O ID (CORREÇÃO DO ERRO)
// Tenta encontrar o ID em várias variáveis comuns de sessão
$usuario_id = $_SESSION['id'] ?? $_SESSION['user_id'] ?? $_SESSION['usuario_id'] ?? null;

// Se mesmo assim não encontrar, para tudo e avisa
if (!$usuario_id) {
    die("Erro Crítico: O ID do usuário não foi encontrado na sessão. Por favor, faça logout e entre novamente.");
}

// Verifica CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Erro de validação de segurança (CSRF Inválido).");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $data = $_POST['data'];
    $turno = $_POST['turno'];
    $secretaria = trim($_POST['secretaria']);
    $telefone = trim($_POST['telefone']);
    $assunto = trim($_POST['assunto']);
    $descricao = trim($_POST['descricao']);
    $responsavel = trim($_POST['responsavel']);


    // 3. VERIFICAR DISPONIBILIDADE
    $check = $conn->prepare("SELECT id FROM agendamentos WHERE data_reserva = :data AND turno = :turno");
    $check->execute([':data' => $data, ':turno' => $turno]);

    if ($check->rowCount() > 0) {
        $_SESSION['msg'] = "❌ Erro: Já existe um agendamento confirmado para esta Data e Turno.";
    } else {
        // 4. INSERIR NO BANCO
        // A coluna 'status' foi removida, então inserimos direto
        $sql = "INSERT INTO agendamentos (usuario_id, data_reserva, turno, secretaria, telefone, assunto, descricao, responsavel) 
                VALUES (:uid, :data, :turno, :sec, :tel, :assunto, :desc, :responsavel)";
        
        $stmt = $conn->prepare($sql);
        
        try {
            $stmt->execute([
                ':uid' => $usuario_id, // Agora garantimos que essa variável tem valor
                ':data' => $data,
                ':turno' => $turno,
                ':sec' => $secretaria,
                ':tel' => $telefone,
                ':assunto' => $assunto,
                ':desc' => $descricao,
                ':responsavel' => $responsavel
            ]);
            $_SESSION['msg'] = "✅ Agendamento realizado com sucesso!";
        } catch(PDOException $e) {
            // Se der erro, mostra qual foi
            $_SESSION['msg'] = "Erro ao salvar no banco: " . $e->getMessage();
        }
    }

    header("Location: dashboard.php");
    exit();
}
?>