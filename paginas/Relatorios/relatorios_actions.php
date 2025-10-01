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
            $sql = "SELECT * FROM SGM_Ativos ORDER BY Ativo_TAG";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'os_completo':
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
            $sql = "SELECT * FROM SGM_Usuarios ORDER BY nome";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'tarefas':
            $sql = "SELECT * FROM SGM_Tarefas ORDER BY tarefa_tag";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'setores':
            $sql = "SELECT * FROM SGM_Setores ORDER BY Setor_TAG";
            $stmt = sqlsrv_query($conn, $sql);
            break;
            
        // NOVO BLOCO ADICIONADO PARA O RELATÓRIO DE AUDITORIA
        case 'os_audit':
            $sql = "SELECT h.os_tag, s.TAGStatus, h.data_inicio, h.abriu_codigo, h.data_fim, h.fechou_codigo, h.descricao
                    FROM SGM_OS_Status h
                    LEFT JOIN SGM_TAGStatus s ON h.tag_status_id = s.TAGStatusID
                    WHERE 1=1";
            if (!empty($_POST['os_data_inicio'])) { 
                $sql .= " AND h.data_inicio >= ?"; 
                $params[] = $_POST['os_data_inicio']; 
            }
            if (!empty($_POST['os_data_fim'])) { 
                $sql .= " AND h.data_inicio <= ?"; 
                $params[] = $_POST['os_data_fim'] . ' 23:59:59'; 
            }
            $sql .= " ORDER BY h.os_tag, h.data_inicio ASC";
            $stmt = sqlsrv_query($conn, $sql, $params);
            break;

        default:
            echo json_encode(['erro' => 'Tipo de relatório inválido.']); 
            exit;
    }

    if ($stmt) {
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
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