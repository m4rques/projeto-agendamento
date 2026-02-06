<?php
// index.php - TELA DE LOGIN
require 'database.php';

// 1. VERIFICAÇÃO INICIAL (EVITA LOOP INFINITO)
// Se o usuário já estiver logado, não deixa ele ver a tela de login, manda pro painel.
if (isset($_SESSION['logados']) && $_SESSION['logados'] === true) {
    header("Location: dashboard.php");
    exit;
}

$erro = '';

// 2. PROCESSAMENTO DO FORMULÁRIO
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // A. Verifica Token de Segurança (Anti-CSRF)
    try {
        verificar_csrf_token($_POST['csrf_token'] ?? '');
    } catch (Exception $e) {
        $erro = "Erro de segurança: Token inválido. Atualize a página.";
    }

    // B. Se o token passou, verifica usuário e senha
    if (empty($erro)) {
        $matricula = trim($_POST['matricula']);
        $senha = $_POST['senha'];

        // Busca o usuário no banco
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE matricula = :mat");
        $stmt->bindValue(':mat', $matricula);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verifica a senha (Hash)
        if ($user && password_verify($senha, $user['senha'])) {
            
            // SUCESSO! Salva os dados na sessão
            $_SESSION['logados'] = true; 
            $_SESSION['id'] = $user['id'];
            $_SESSION['nome'] = $user['nome'];
            $_SESSION['matricula'] = $user['matricula'];
            $_SESSION['perfil'] = $user['perfil'];
            
            // Redireciona para o painel  
            header("Location: dashboard.php");
            exit;
        } else {
            $erro = "Matrícula ou senha incorretos!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sala de Reunião</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            background-color: #e9ecef; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            height: 100vh; 
            margin: 0;
        }
        .login-box { 
            width: 100%; 
            max-width: 400px; 
            background: white; 
            padding: 40px; 
            border-radius: 10px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        }
        .brand-logo {
            font-size: 2rem;
            font-weight: bold;
            color: #0d6efd;
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    
    <div class="login-box">
        <div class="brand-logo">Agendamento</div>
        <h5 class="text-center text-secondary mb-4">Acesso ao Sistema</h5>
        
        <?php if($erro): ?>
            <div class='alert alert-danger text-center p-2'><?php echo $erro; ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

            <div class="mb-3">
                <label class="form-label">Matrícula</label>
                <input type="text" name="matricula" class="form-control form-control-lg" required placeholder="Ex: 1000" autofocus>
            </div>
            
            <div class="mb-4">
                <label class="form-label">Senha</label>
                <input type="password" name="senha" class="form-control form-control-lg" required placeholder="******">
            </div>
            
            <button type="submit" class="btn btn-primary w-100 btn-lg">Entrar</button>
        </form>
        
        <div class="text-center mt-3">
            <small class="text-muted">Esqueceu a senha? Contate a TI.</small>
        </div>
    </div>

</body>
</html>