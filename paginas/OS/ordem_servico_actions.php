<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../database/config.php';

if ($conn === false) {
    echo json_encode(["sucesso" => false, "mensagem" => "Falha na conexão com o banco de dados.", "detalhes" => sqlsrv_errors()]);
    exit;
}
    
$action = $_REQUEST['action'];

switch ($action) {
    case 'get_open':
        $sql = "SELECT OS_ID as os_tag, Ativo_TAG as ativo_tag, Descricao_Servico as descricao_problema, Data_Solicitacao as data_criacao, Status as status_atual, Maquina_Parada as maquina_parada FROM SGM_OS WHERE Data_Fim_Atendimento IS NULL ORDER BY Maquina_Parada DESC, Data_Solicitacao ASC";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'A consulta SQL para buscar OS em aberto falhou.', 'detalhes' => sqlsrv_errors()]);
            break;
        }
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    case 'create':
        $sql = "INSERT INTO SGM_OS (Ativo_TAG, Solicitante, Data_Solicitacao, Status, Descricao_Servico, Maquina_Parada) VALUES (?, ?, GETDATE(), ?, ?, ?)";
        $params = [
            $_POST['ativo_tag'] ?? null, 
            $_POST['solicitante'] ?? null, 
            'Aguardando Análise',
            $_POST['descricao_problema'] ?? '',
            isset($_POST['maquina_parada']) ? 1 : 0
        ];
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ordem de Serviço criada com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Falha ao criar a Ordem de Serviço.", "details" => sqlsrv_errors()]);
        }
        break;

    case 'get_details':
        $os_id = $_GET['os_id'] ?? 0;
        if (empty($os_id)) {
            echo json_encode(["sucesso" => false, "mensagem" => "ID da OS não fornecido."]);
            break;
        }
        $sql = "SELECT * FROM SGM_OS WHERE OS_ID = ?";
        $stmt = sqlsrv_query($conn, $sql, [$os_id]);
        if ($stmt === false) {
            echo json_encode(['sucesso' => false, 'mensagem' => 'A consulta SQL para buscar detalhes falhou.', 'detalhes' => sqlsrv_errors()]);
            break;
        }
        $details = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        if ($details) {
            foreach($details as &$value) {
                if ($value instanceof DateTime) {
                    $value = $value->format('Y-m-d\TH:i');
                }
            }
            $sql_status = "SELECT TAGStatus FROM SGM_TAGStatus ORDER BY TAGStatus";
            $stmt_status = sqlsrv_query($conn, $sql_status);
            $status_list = [];
            if($stmt_status){
                while($row = sqlsrv_fetch_array($stmt_status, SQLSRV_FETCH_ASSOC)){
                    $status_list[] = $row['TAGStatus'];
                }
            }
            $details['status_options'] = $status_list;
            echo json_encode($details);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Nenhuma OS encontrada com o ID: $os_id"]);
        }
        break;
        
    case 'update':
        $os_id = $_POST['OS_ID'] ?? 0;
        if (empty($os_id)) {
            echo json_encode(["sucesso" => false, "mensagem" => "ID da OS não informado."]);
            break;
        }

        $sql_get_desc = "SELECT Descricao_Servico FROM SGM_OS WHERE OS_ID = ?";
        $stmt_get_desc = sqlsrv_query($conn, $sql_get_desc, [$os_id]);
        $current_data = sqlsrv_fetch_array($stmt_get_desc, SQLSRV_FETCH_ASSOC);
        $historico_completo = $current_data['Descricao_Servico'] ?? '';

        $novo_servico_descricao = $_POST['servico_realizado_descricao'] ?? '';
        
        if (!empty($novo_servico_descricao)) {
            $data_servico = date('d/m/Y H:i');
            $historico_completo = "--- SERVIÇO REALIZADO EM {$data_servico} ---\n" . $novo_servico_descricao . "\n\n" . $historico_completo;
        }

        $params = [
            'Ativo_TAG' => $_POST['Ativo_TAG'] ?? null,
            'Status' => $_POST['Status'] ?? null,
            'Maquina_Parada' => isset($_POST['Maquina_Parada']) ? 1 : 0,
            'Data_Inicio_Atendimento' => empty($_POST['Data_Inicio_Atendimento']) ? null : str_replace('T', ' ', $_POST['Data_Inicio_Atendimento']),
            'Data_Fim_Atendimento' => empty($_POST['Data_Fim_Atendimento']) ? null : str_replace('T', ' ', $_POST['Data_Fim_Atendimento']),
            'Descricao_Servico' => $historico_completo
        ];

        $sql_parts = [];
        foreach($params as $key => $value){ $sql_parts[] = "[$key] = ?"; }
        $sql = "UPDATE SGM_OS SET " . implode(', ', $sql_parts) . " WHERE OS_ID = ?";
        
        $query_params = array_values($params);
        $query_params[] = $os_id;
        
        $stmt = sqlsrv_query($conn, $sql, $query_params);
        
        if($stmt) {
            $is_concluido = (isset($_POST['Status']) && $_POST['Status'] === 'Concluída');
            echo json_encode(["sucesso" => true, "mensagem" => "OS #$os_id atualizada com sucesso!", "concluido" => $is_concluido]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar a OS.", "details" => sqlsrv_errors()]);
        }
        break;

    default:
        echo json_encode(["sucesso" => false, "mensagem" => "Ação desconhecida: " . htmlspecialchars($action)]);
        break;
}
    
exit;
?>