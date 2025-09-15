<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Caminho corrigido
require_once __DIR__ . '/../../../database/config.php';

header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com a base de dados."]);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'generate') {
    $report_type = $_POST['tipo_relatorio'] ?? '';

    switch ($report_type) {
        case 'ativos':
            $allowed_columns = ['ativo_tag', 'descricao', 'setor_tag', 'modelo', 'numero_serie', 'tipo', 'instalacao'];
            $selected_columns = $_POST['colunas'] ?? [];
            $safe_columns = array_intersect($selected_columns, $allowed_columns);

            if (empty($safe_columns)) {
                echo json_encode(['erro' => 'Nenhuma coluna válida foi selecionada.']);
                exit;
            }

            $sql = "SELECT " . implode(', ', $safe_columns) . " FROM SGM_Ativos";
            $stmt = sqlsrv_query($conn, $sql);
            break;

        case 'os_historico':
            $sql = "SELECT os.os_tag, os.ativo_tag, os.data_criacao, os.data_conclusao, os.descricao_problema, u.nome as solicitante
                    FROM SGM_OS os
                    LEFT JOIN SGM_Usuarios u ON os.solicitante = u.codigo
                    WHERE 1=1";

            $params = [];
            if (!empty($_POST['os_data_inicio'])) {
                $sql .= " AND os.data_criacao >= ?";
                $params[] = $_POST['os_data_inicio'];
            }
            if (!empty($_POST['os_data_fim'])) {
                $sql .= " AND os.data_criacao <= ?";
                $params[] = $_POST['os_data_fim'] . ' 23:59:59';
            }
            if (!empty($_POST['os_ativo_tag'])) {
                $sql .= " AND os.ativo_tag = ?";
                $params[] = $_POST['os_ativo_tag'];
            }

            $sql .= " ORDER BY os.data_criacao DESC";
            $stmt = sqlsrv_query($conn, $sql, $params);
            break;

        default:
            echo json_encode(['erro' => 'Tipo de relatório inválido.']);
            exit;
    }

    if ($stmt) {
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            foreach($row as $key => &$value) {
                if ($value instanceof DateTime) {
                    $value = $value->format('d/m/Y H:i');
                }
            }
            $data[] = $row;
        }
        echo json_encode(['data' => $data]);
    } else {
        echo json_encode(['erro' => 'Erro ao executar a consulta.', 'details' => sqlsrv_errors()]);
    }
    exit;
}

echo json_encode(["erro" => "Ação inválida para relatórios."]);
?>