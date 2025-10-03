<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

if ($conn === false) {
    echo json_encode(["sucesso" => false, "mensagem" => "Falha na conexão com o banco de dados.", "detalhes" => sqlsrv_errors()]);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'get_ativos':
        $searchTerm = $_GET['searchTerm'] ?? '';
        $sql = "SELECT Ativo_TAG FROM SGM_Ativos WHERE Ativo_TAG LIKE ? ORDER BY Ativo_TAG ASC";
        $params = ['%' . $searchTerm . '%'];
        $stmt = sqlsrv_query($conn, $sql, $params);
        $ativos = [];
        if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $ativos[] = $row; } }
        echo json_encode($ativos);
        break;

    case 'get_usuarios':
        $searchTerm = $_GET['searchTerm'] ?? '';
        $sql = "SELECT codigo, nome FROM SGM_Usuarios WHERE CAST(codigo AS VARCHAR) LIKE ? OR nome LIKE ? ORDER BY nome ASC";
        $params = ['%' . $searchTerm . '%', '%' . $searchTerm . '%'];
        $stmt = sqlsrv_query($conn, $sql, $params);
        $usuarios = [];
        if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $usuarios[] = $row; } }
        echo json_encode($usuarios);
        break;

    case 'get_open':
        $sql = "SELECT OS_ID as os_tag, Ativo_TAG as ativo_tag, Descricao_Servico as descricao_problema, Data_Solicitacao as data_criacao, Status as status_atual, Maquina_Parada as maquina_parada FROM SGM_OS WHERE Data_Fim_Atendimento IS NULL ORDER BY Maquina_Parada DESC, Data_Solicitacao ASC";
        $stmt = sqlsrv_query($conn, $sql);
        if ($stmt === false) { echo json_encode(['sucesso' => false, 'mensagem' => 'Falha ao buscar OS abertas.', 'detalhes' => sqlsrv_errors()]); break; }
        $data = [];
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $data[] = $row; }
        echo json_encode($data);
        break;

    case 'create':
        sqlsrv_begin_transaction($conn);
        $sql_os = "INSERT INTO SGM_OS (Ativo_TAG, Solicitante, Data_Solicitacao, Status, Descricao_Servico, Maquina_Parada) OUTPUT INSERTED.OS_ID VALUES (?, ?, GETDATE(), ?, ?, ?)";
        $params_os = [$_POST['ativo_tag'] ?? null, $_POST['solicitante'] ?? null, 'Aguardando Análise', $_POST['descricao_problema'] ?? '', isset($_POST['maquina_parada']) ? 1 : 0];
        $stmt_os = sqlsrv_query($conn, $sql_os, $params_os);

        if ($stmt_os && sqlsrv_fetch($stmt_os) && ($os_id = sqlsrv_get_field($stmt_os, 0))) {
            $sql_status = "INSERT INTO SGM_OS_Status (os_tag, tag_status_id, data_inicio, abriu_codigo, descricao) VALUES (?, (SELECT TAGStatusID FROM SGM_TAGStatus WHERE TAGStatus = 'Aguardando Análise'), GETDATE(), ?, ?)";
            $params_status = [$os_id, $_POST['solicitante'] ?? null, 'Abertura da Ordem de Serviço'];
            $stmt_status = sqlsrv_query($conn, $sql_status, $params_status);

            if ($stmt_status) {
                sqlsrv_commit($conn);
                echo json_encode(["sucesso" => true, "mensagem" => "Ordem de Serviço criada com sucesso!"]);
            } else {
                sqlsrv_rollback($conn);
                echo json_encode(["sucesso" => false, "mensagem" => "Falha ao criar o histórico de status.", "details" => sqlsrv_errors()]);
            }
        } else {
            sqlsrv_rollback($conn);
            echo json_encode(["sucesso" => false, "mensagem" => "Falha ao criar a Ordem de Serviço.", "details" => sqlsrv_errors()]);
        }
        break;

    case 'get_details':
        $os_id = $_GET['os_id'] ?? 0;
        if (empty($os_id)) { echo json_encode(["sucesso" => false, "mensagem" => "ID da OS não fornecido."]); break; }
        
        $sql = "SELECT * FROM SGM_OS WHERE OS_ID = ?";
        $stmt = sqlsrv_query($conn, $sql, [$os_id]);
        $details = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

        if ($details) {
            $sql_historico = "SELECT s.TAGStatus, h.data_inicio, h.data_fim, h.abriu_codigo, h.fechou_codigo, h.descricao 
                              FROM SGM_OS_Status h 
                              JOIN SGM_TAGStatus s ON h.tag_status_id = s.TAGStatusID 
                              WHERE h.os_tag = ? ORDER BY h.data_inicio DESC";
            $stmt_historico = sqlsrv_query($conn, $sql_historico, [$os_id]);
            $historico = [];
            if ($stmt_historico) {
                while($row = sqlsrv_fetch_array($stmt_historico, SQLSRV_FETCH_ASSOC)) {
                    if ($row['data_inicio'] instanceof DateTime) { $row['data_inicio'] = $row['data_inicio']->format('d/m/Y H:i'); }
                    if ($row['data_fim'] instanceof DateTime) { $row['data_fim'] = $row['data_fim']->format('d/m/Y H:i'); }
                    $historico[] = $row;
                }
            }
            $details['historico_status'] = $historico;

            foreach($details as &$value) { if ($value instanceof DateTime) { $value = $value->format('Y-m-d\TH:i'); } }
            
            $sql_status_options = "SELECT TAGStatus FROM SGM_TAGStatus ORDER BY TAGStatus";
            $stmt_status_options = sqlsrv_query($conn, $sql_status_options);
            $status_list = [];
            if($stmt_status_options){ while($row = sqlsrv_fetch_array($stmt_status_options, SQLSRV_FETCH_ASSOC)){ $status_list[] = $row['TAGStatus']; } }
            $details['status_options'] = $status_list;
            
            echo json_encode($details);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Nenhuma OS encontrada com o ID: $os_id"]);
        }
        break;
        
    case 'update':
        $os_id = $_POST['OS_ID'] ?? 0;
        $novo_status_nome = $_POST['Status'] ?? null;
        $tecnico_codigo = $_POST['tecnico_codigo'] ?? null;

        if (empty($os_id)) { echo json_encode(["sucesso" => false, "mensagem" => "ID da OS não informado."]); break; }
        if (empty($tecnico_codigo)) { echo json_encode(["sucesso" => false, "mensagem" => "Código do técnico é obrigatório."]); break; }

        sqlsrv_begin_transaction($conn);

        $data_fim = empty($_POST['Data_Fim_Atendimento']) ? null : str_replace('T', ' ', $_POST['Data_Fim_Atendimento']);
        if ($novo_status_nome === 'Concluída' && $data_fim === null) {
            $data_fim = date('Y-m-d H:i:s');
        }
        $params_os_update = [
            'Ativo_TAG' => $_POST['Ativo_TAG'] ?? null,
            'Status' => $novo_status_nome,
            'Maquina_Parada' => isset($_POST['Maquina_Parada']) ? 1 : 0,
            'Data_Inicio_Atendimento' => empty($_POST['Data_Inicio_Atendimento']) ? null : str_replace('T', ' ', $_POST['Data_Inicio_Atendimento']),
            'Data_Fim_Atendimento' => $data_fim
        ];
        $sql_parts = [];
        foreach($params_os_update as $key => $value){ $sql_parts[] = "[$key] = ?"; }
        $sql_os_update = "UPDATE SGM_OS SET " . implode(', ', $sql_parts) . " WHERE OS_ID = ?";
        $query_params = array_values($params_os_update);
        $query_params[] = $os_id;
        $stmt_os_update = sqlsrv_query($conn, $sql_os_update, $query_params);

        $sql_close_status = "UPDATE SGM_OS_Status SET data_fim = GETDATE(), fechou_codigo = ? WHERE os_tag = ? AND data_fim IS NULL";
        $stmt_close_status = sqlsrv_query($conn, $sql_close_status, [$tecnico_codigo, $os_id]);

        $is_concluido = ($novo_status_nome === 'Concluída');
        $stmt_open_status = true; 
        if (!$is_concluido) {
            $descricao_atendimento = $_POST['servico_realizado_descricao'] ?? 'Status alterado.';
            $sql_open_status = "INSERT INTO SGM_OS_Status (os_tag, tag_status_id, data_inicio, abriu_codigo, descricao) VALUES (?, (SELECT TAGStatusID FROM SGM_TAGStatus WHERE TAGStatus = ?), GETDATE(), ?, ?)";
            $stmt_open_status = sqlsrv_query($conn, $sql_open_status, [$os_id, $novo_status_nome, $tecnico_codigo, $descricao_atendimento]);
        }
        
        $stmt_atendimento = true; 
        if(!empty($_POST['servico_realizado_descricao'])){
            $sql_atendimento = "INSERT INTO SGM_OS_Atendimentos (OS_ID, Tecnico, Data_Hora_Inicio, Descricao_Atendimento) VALUES (?, ?, GETDATE(), ?)";
            $params_atendimento = [$os_id, $tecnico_codigo, $_POST['servico_realizado_descricao']];
            $stmt_atendimento = sqlsrv_query($conn, $sql_atendimento, $params_atendimento);
        }

        if ($stmt_os_update && $stmt_close_status && $stmt_open_status && $stmt_atendimento) {
            sqlsrv_commit($conn);
            echo json_encode(["sucesso" => true, "mensagem" => "OS #$os_id atualizada com sucesso!", "concluido" => $is_concluido]);
        } else {
            sqlsrv_rollback($conn);
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar o histórico da OS. Nenhuma alteração foi salva.", "details" => sqlsrv_errors()]);
        }
        break;

    default:
        echo json_encode(["sucesso" => false, "mensagem" => "Ação desconhecida: " . htmlspecialchars($action)]);
        break;
}
    
exit;
?>