<?php
session_start();
require 'database.php';
// --- VERIFICAÇÃO DE LOGIN ---
if (!isset($_SESSION['logados']) || $_SESSION['logados'] !== true) {
    header("Location: index.php");
    exit();
}
// SEGURANÇA MÁXIMA: Só perfil 'ti' entra aqui. Admin comum é expulso.
if (!isset($_SESSION['perfil']) || $_SESSION['perfil'] !== 'ti') {
    header("Location: dashboard.php");
    exit;
}

$msg = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // VERIFICAÇÃO DE SEGURANÇA CSRF
    verificar_csrf_token($_POST['csrf_token'] ?? '');

    $nome = $_POST['nome'];
    $matricula = $_POST['matricula'];
    $senha = $_POST['senha'];
    $perfil_escolhido = $_POST['perfil']; 

    $check = $conn->prepare("SELECT id FROM usuarios WHERE matricula = ?");
    $check->execute([$matricula]);

    if ($check->rowCount() > 0) {
        $msg = "<div class='alert alert-danger'>Matrícula já existe!</div>";
    } else {
        $hash = password_hash($senha, PASSWORD_DEFAULT);
        $sql = "INSERT INTO usuarios (nome, matricula, senha, perfil) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt->execute([$nome, $matricula, $hash, $perfil_escolhido])) {
            $msg = "<div class='alert alert-success'>Usuário criado com sucesso!</div>";
        } else {
            $msg = "<div class='alert alert-danger'>Erro ao cadastrar.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light p-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header bg-success text-white">Cadastrar Novo Usuário</div>
                    <div class="card-body">
                        
                        <?php echo $msg; ?>

                        <form method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">

                            <div class="mb-3">
                                <label>Nome Completo</label>
                                <input type="text" name="nome" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Matrícula</label>
                                <input type="text" name="matricula" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Senha Provisória</label>
                                <input type="text" name="senha" class="form-control" required>
                            </div>
                            
                            <div class="mb-3">
                                <label>Função do Usuário</label>
                                <select name="perfil" class="form-select">
                                    <option value="secretario">Secretário (Apenas solicita)</option>
                                    <option value="admin">Administrador (Apenas Aprova)</option>
                                    </select>
                            </div>

                            <button type="submit" class="btn btn-success w-100">Cadastrar</button>
                        </form>
                    </div>
                    <div class="card-footer text-center">
                        <a href="dashboard.php" class="btn btn-outline-secondary btn-sm">Voltar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>