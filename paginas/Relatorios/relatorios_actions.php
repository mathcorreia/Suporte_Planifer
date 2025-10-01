<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'generate') {
    $report_type = $_POST['tipo_relatorio'] ?? '';
    $stmt = false;
    $sql = "";
    $params = [];

    switch ($report_type) {
        case 'ativos_completo':
            // Traz TODAS as colunas da tabela de ativos
            $sql = "SELECT * FROM SGM_Ativos ORDER BY Ativo_TAG";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'os_completo':
            // Traz TODAS as colunas da tabela de OS, com filtro de data
            $sql = "SELECT * FROM SGM_OS WHERE 1=1";
            if (!empty($_POST['os_data_inicio'])) { 
                $sql .= " AND Data_Solicitacao >= ?"; 
                $params[] = $_POST['os_data_inicio']; 
            }
            if (!empty($_POST['os_data_fim'])) { 
                $sql .= " AND Data_Solicitacao <= ?"; 
                $params[] = $_POST['os_data_fim'] . ' 23:59:59'; 
            }
            $sql .= " ORDER BY Data_Solicitacao DESC";
            $stmt = sqlsrv_query($conn, $sql, $params);
            break;

        case 'usuarios':
            // Traz todas as informações de usuários
            $sql = "SELECT * FROM SGM_Usuarios ORDER BY nome";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'tarefas':
            // Traz todas as informações de tarefas
            $sql = "SELECT * FROM SGM_Tarefas ORDER BY tarefa_tag";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'setores':
            // Traz todas as informações de setores
            $sql = "SELECT * FROM SGM_Setores ORDER BY Setor_TAG";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        default:
            echo json_encode(['erro' => 'Tipo de relatório inválido.']); 
            exit;
    }

    if ($stmt) {
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            // Formata todas as datas para um formato legível
            foreach($row as &$value) { 
                if ($value instanceof DateTime) { 
                    $value = $value->format('d/m/Y H:i:s'); 
                } 
            }
            $data[] = $row;
        }
        echo json_encode(['dados' => $data]);
    } else {
        echo json_encode(['erro' => 'Erro ao executar a consulta.', 'details' => sqlsrv_errors()]);
    }
} else {
    echo json_encode(["erro" => "Ação inválida para relatórios."]);
}
exit;
?>