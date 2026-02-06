<?php
require 'database.php';

// --- VERIFICAÇÃO DE LOGIN ---
if (!isset($_SESSION['logados']) || $_SESSION['logados'] !== true) {
    header("Location: index.php");
    exit();
}

$nome_usuario = $_SESSION['nome'] ?? 'Usuário';
$perfil = $_SESSION['perfil'] ?? ''; 
$matricula = $_SESSION['matricula'] ?? '---';

// Array para traduzir os meses
$meses_pt = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril', 5 => 'Maio', 6 => 'Junho',
    7 => 'Julho', 8 => 'Agosto', 9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];

// --- LÓGICA DO FILTRO ---
// CORREÇÃO 1: Ano padrão agora é o atual date('Y')
$filtro_mes = $_GET['mes'] ?? date('n'); 
$filtro_ano = $_GET['ano'] ?? date('Y'); 

if (isset($_GET['limpar'])) {
    $filtro_mes = 'todos';
    $filtro_ano = date('Y'); // Reseta para o ano atual ao limpar
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel - Agendamento Direto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .linha-mes { background-color: #e9ecef !important; color: #495057; font-weight: bold; text-transform: uppercase; font-size: 0.9em; letter-spacing: 1px; }
        .text-pequeno { font-size: 0.85em; color: #6c757d; }
    </style>
</head>
<body class="bg-light">
    
    <nav class="navbar navbar-dark bg-primary mb-5">
        <div class="container">
            <span class="navbar-brand">Olá, <strong><?php echo $nome_usuario; ?></strong></span>
            <div class="d-flex align-items-center text-white">
                <span class="me-3 small">Perfil: <?php echo strtoupper($perfil); ?></span>
                <a href="logout.php" class="btn btn-light btn-sm">Sair</a>
            </div>
        </div>
    </nav>

    <div class="container">
        
        <?php 
        if(isset($_SESSION['msg'])) {
            echo "<div class='alert alert-info text-center shadow-sm'>" . $_SESSION['msg'] . "</div>";
            unset($_SESSION['msg']);
        }
        ?>

        <?php if ($perfil == 'ti'): ?>
            <div class="card mb-4 shadow-sm border-primary">
                <div class="card-header bg-primary text-white">Administração - TI</div>
                <div class="card-body text-center">
                    <a href="novo_usuario.php" class="btn btn-light text-primary fw-bold">Cadastrar Novo Usuário</a>
                </div>
            </div>
        <?php endif; ?>

        <?php 
        // Verifica permissão para CRIAR agendamentos
        if ($perfil == 'secretario' || $perfil == 'ti' || $perfil == 'admin'): 
        ?>
            <div class="card mb-5 shadow-sm">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Novo Agendamento</h5></div>
                <div class="card-body">
                    <form action="reservar.php" method="POST">
                        <?php if(function_exists('gerar_csrf_token')): ?>
                            <input type="hidden" name="csrf_token" value="<?php echo gerar_csrf_token(); ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Data</label>
                                <input type="date" name="data" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label">Turno</label>
                                <select name="turno" class="form-select" required>
                                    <option value="">Selecione...</option>
                                    <option value="manha">Manhã: 08:30 às 12:00</option>
                                    <option value="tarde">Tarde: 13:30 às 16:45</option>
                                    <option value="integral">Manhã/Tarde: 08:30 às 12:00 e 13:30 às 16:45</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Secretaria</label>
                                <input type="text" name="secretaria" class="form-control" required placeholder="Ex: Educação">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Responsável</label>
                                <input type="text" name="responsavel" class="form-control" required placeholder="Ex: José Silva">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Telefone</label>
                                <input type="text" name="telefone" class="form-control" required placeholder="(XX) 9999-9999">
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Assunto</label>
                                <input type="text" name="assunto" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Observação</label>
                            <textarea name="descricao" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="text-end"><button type="submit" class="btn btn-success px-4">Confirmar Agendamento</button></div>
                    </form>
                </div>
            </div>
            <div class="alert alert-primary">Observação: A secretaria solicitante fica responsável pelo fornecimento de água e café, bem como pela organização do espaço durante o período de uso.</div>
        <?php endif; ?>
 
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h4 class="text-secondary m-0">Histórico de Agendamentos</h4>
        </div>

        <div class="card bg-light border-0 mb-3">
            <div class="alert alert-dark">Observação: Em caso de cancelamento, gentileza entrar em contato com antecedência com Fabiana (Gapre) pelo telefone 3190-5617 ou pelo ramal 300, para que a sala seja disponibilizada para outros agendamentos.</div>
            <div class="card-body py-3">
                <form method="GET" class="row g-2 align-items-center">
                    <div class="col-auto">
                        <label class="fw-bold text-secondary">Filtrar por:</label>
                    </div>
                    <div class="col-auto">
                        <select name="mes" class="form-select form-select-sm">
                            <option value="todos" <?php echo ($filtro_mes == 'todos') ? 'selected' : ''; ?>>Todos os Meses</option>
                            <?php foreach ($meses_pt as $num => $nome): ?>
                                <option value="<?php echo $num; ?>" <?php echo ($filtro_mes == $num) ? 'selected' : ''; ?>>
                                    <?php echo $nome; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <select name="ano" class="form-select form-select-sm">
                            <?php
                                // Incluímos o ano anterior e os próximos 5 anos
                                $ano_atual = date('Y');
                                $start = $ano_atual - 1; 
                                for ($i = 0; $i <= 6; $i++) {
                                    $ano_opcao = $start + $i;
                                    $selected = ($filtro_ano == $ano_opcao) ? 'selected' : '';
                                    echo "<option value='$ano_opcao' $selected>$ano_opcao</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary btn-sm">Filtrar</button>
                        <?php if($filtro_mes != 'todos' || $filtro_ano != date('Y')): ?>
                            <a href="dashboard.php?limpar=true" class="btn btn-outline-secondary btn-sm">Limpar Filtros</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table mb-0 table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Dia</th>
                                <th>Turno</th>
                                <th>Secretaria</th>
                                <th style="width: 15%;">Assunto</th>
                                <th style="width: 20%;">Observação</th>
                                <th>Responsável</th>
                                <th>Solicitante</th>
                                <th>Telefone</th>
                                <th class="text-end">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql_hist = "SELECT a.*, u.nome FROM agendamentos a 
                                         JOIN usuarios u ON a.usuario_id = u.id";
                            
                            $filtros_where = [];
                            $params = [];

                            // CORREÇÃO 3: Lógica de filtro separada e robusta
                            
                            // 1. Filtro de Ano (Sempre aplicado)
                            if (!empty($filtro_ano)) {
                                $filtros_where[] = "YEAR(a.data_reserva) = :ano";
                                $params[':ano'] = $filtro_ano;
                            }

                            // 2. Filtro de Mês (Só se não for 'todos')
                            if ($filtro_mes != 'todos') {
                                $filtros_where[] = "MONTH(a.data_reserva) = :mes";
                                $params[':mes'] = $filtro_mes;
                            }

                            if (count($filtros_where) > 0) {
                                $sql_hist .= " WHERE " . implode(' AND ', $filtros_where);
                            }

                            $sql_hist .= " ORDER BY a.data_reserva DESC";

                            $stmt_hist = $conn->prepare($sql_hist);
                            $stmt_hist->execute($params);
                            
                            $ultimo_mes_ano = ""; 
                            $total_resultados = $stmt_hist->rowCount();
                            
                            // Variável para controlar se algum item foi exibido (para o caso de permissões ocultarem tudo)
                            $itens_exibidos = 0;

                            if ($total_resultados > 0):
                                foreach($stmt_hist as $row):
                                    // Verificação de permissão movida para cá para contar corretamente
                                    $pode_ver = ($perfil == 'ti' || $perfil == 'admin' || $_SESSION['id'] === $row['usuario_id']);
                                    
                                    if ($pode_ver):
                                        $itens_exibidos++;
                                        
                                        $mes_num = date('n', strtotime($row['data_reserva']));
                                        $ano_reserva = date('Y', strtotime($row['data_reserva']));
                                        $mes_ano_atual = $meses_pt[$mes_num] . " " . $ano_reserva;
                                        
                                        $labelImpressa = "Nenhum turno selecionado";
                                        switch ($row['turno']) {
                                            case 'manha': $labelImpressa = "Manhã: 08:30 às 12:00"; break;
                                            case 'tarde': $labelImpressa = "Tarde: 13:30 às 16:45"; break;
                                            case 'integral': $labelImpressa = "Manhã/Tarde: 08:30 às 12:00 e 13:30 às 16:45"; break;
                                        }

                                        if ($mes_ano_atual !== $ultimo_mes_ano) {
                                            // Colspan ajustado para 9 colunas (total de THs)
                                            echo "<tr class='linha-mes'><td colspan='9' class='ps-4'>📅 $mes_ano_atual</td></tr>";
                                            $ultimo_mes_ano = $mes_ano_atual; 
                                        }
                            ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?php echo date('d', strtotime($row['data_reserva'])); ?></td>
                                        <td><?php echo $labelImpressa; ?></td>
                                        <td><?php echo $row['secretaria']; ?></td>
                                        <td><?php echo $row['assunto']; ?></td>
                                        <td><span class="text-pequeno"><?php echo $row['descricao']; ?></span></td>
                                        <td><?php echo $row['responsavel']; ?></td>
                                        <td><?php echo $row['nome']; ?></td>
                                        <td><?php echo $row['telefone']; ?></td>
                                        
                                        <td class="text-end">
                                            <?php if ( $perfil == 'ti' || $perfil == 'admin' || $_SESSION['id'] === $row['usuario_id'] ): ?>
                                                <a href="cancelamento.php?id=<?php echo $row['id']; ?>" 
                                                class="btn btn-outline-danger btn-sm py-0"
                                                onclick="return confirm('Tem certeza que deseja cancelar este agendamento?');">
                                                    Cancelar
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endif; // fim if pode_ver ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                            <?php if ($total_resultados == 0 || (isset($itens_exibidos) && $itens_exibidos == 0)): ?>
                                <tr><td colspan="9" class="text-center py-5 text-muted">Nenhum agendamento encontrado para este período.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
  
    <br><br>
</body>
</html>