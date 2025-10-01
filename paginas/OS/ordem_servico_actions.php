<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'get_open') {
    $sql = "SELECT 
                OS_ID as os_tag, 
                Ativo_TAG as ativo_tag,
                Descricao_Servico as descricao_problema,
                Data_Solicitacao as data_criacao,
                Status as status_atual,
                Maquina_Parada as maquina_parada
            FROM SGM_OS 
            WHERE Data_Fim_Atendimento IS NULL 
            ORDER BY Maquina_Parada DESC, Data_Solicitacao ASC";

    $stmt = sqlsrv_query($conn, $sql);
    $data = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    } else {
        echo json_encode(["erro" => "Falha na consulta SQL.", "details" => sqlsrv_errors()]);
        exit;
    }
    echo json_encode($data);
}
elseif ($action === 'create') {
    $sql_os = "INSERT INTO SGM_OS (Ativo_TAG, Solicitante, Data_Solicitacao, Status, Descricao_Servico, Maquina_Parada) VALUES (?, ?, ?, ?, ?, ?)";
    $params_os = [
        $_POST['ativo_tag'] ?? null, 
        $_POST['solicitante'] ?? null, 
        date('Y-m-d H:i:s'),
        'Aguardando Análise', // Status inicial fixo
        $_POST['descricao_problema'] ?? '',
        isset($_POST['maquina_parada']) ? 1 : 0
    ];
    
    $stmt_os = sqlsrv_query($conn, $sql_os, $params_os);
    
    if ($stmt_os) {
        echo json_encode(["sucesso" => true, "mensagem" => "Ordem de Serviço criada com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Falha ao criar a Ordem de Serviço.", "details" => sqlsrv_errors()]);
    }
}
elseif ($action === 'get_details') {
    $os_id = $_GET['os_id'] ?? 0;
    if (empty($os_id)) {
        echo json_encode(["erro" => "ID da OS não fornecido."]);
        exit;
    }

    $sql = "SELECT * FROM SGM_OS WHERE OS_ID = ?";
    $stmt = sqlsrv_query($conn, $sql, [$os_id]);
    $details = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);

    if ($details) {
        // Formata as datas para o formato esperado pelo input datetime-local
        foreach($details as $key => &$value) {
            if ($value instanceof DateTime) {
                $value = $value->format('Y-m-d\TH:i');
            }
        }
    }

    // Adiciona a lista de status disponíveis para o dropdown de edição
    $sql_status = "SELECT TAGStatus FROM SGM_TAGStatus";
    $stmt_status = sqlsrv_query($conn, $sql_status);
    $status_list = [];
    if($stmt_status){
        while($row = sqlsrv_fetch_array($stmt_status, SQLSRV_FETCH_ASSOC)){
            $status_list[] = $row['TAGStatus'];
        }
    }
    $details['status_options'] = $status_list;
    
    echo json_encode($details);
}
elseif ($action === 'update') {
    $os_id = $_POST['OS_ID'] ?? 0;
    if (empty($os_id)) {
        echo json_encode(["sucesso" => false, "mensagem" => "ID da OS não informado para atualização."]);
        exit;
    }

    $params = [
        'Ativo_TAG' => $_POST['Ativo_TAG'] ?? null,
        'Solicitante' => $_POST['Solicitante'] ?? null,
        'Status' => $_POST['Status'] ?? null,
        'Descricao_Servico' => $_POST['Descricao_Servico'] ?? null,
        'Maquina_Parada' => isset($_POST['Maquina_Parada']) ? 1 : 0,
        'Data_Solicitacao' => empty($_POST['Data_Solicitacao']) ? null : str_replace('T', ' ', $_POST['Data_Solicitacao']),
        'Data_Inicio_Atendimento' => empty($_POST['Data_Inicio_Atendimento']) ? null : str_replace('T', ' ', $_POST['Data_Inicio_Atendimento']),
        'Data_Fim_Atendimento' => empty($_POST['Data_Fim_Atendimento']) ? null : str_replace('T', ' ', $_POST['Data_Fim_Atendimento']),
    ];

    $sql_parts = [];
    foreach($params as $key => $value){
        $sql_parts[] = "$key = ?";
    }
    $sql = "UPDATE SGM_OS SET " . implode(', ', $sql_parts) . " WHERE OS_ID = ?";
    
    $query_params = array_values($params);
    $query_params[] = $os_id;

    $stmt = sqlsrv_query($conn, $sql, $query_params);
    if($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "OS #$os_id atualizada com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar a OS.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida para Ordens de Serviço"]);
}
exit;
?>