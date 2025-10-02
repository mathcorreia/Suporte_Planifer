<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get_open') {
    $sql = "SELECT 
                ID_Melhoria as id_melhoria, 
                Titulo_Solicitacao as titulo_solicitacao,
                Codigo_Solicitante as codigo_solicitante,
                Data_Criacao as data_criacao,
                Status as status
            FROM SGM_Melhorias 
            WHERE Status = 'Em Aberto' 
            ORDER BY Data_Criacao DESC";
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
elseif ($action === 'save') {
    $id = $_POST['id_melhoria'] ?? '';
    
    $params = [
        'titulo_solicitacao' => $_POST['titulo_solicitacao'] ?? '',
        'descricao_detalhada' => $_POST['descricao_detalhada'] ?? '',
        'codigo_solicitante' => $_POST['codigo_solicitante'] ?? null,
    ];

    if (!empty($id)) {
        // Atualiza solicitação existente
        $sql = "UPDATE SGM_Melhorias SET Titulo_Solicitacao = ?, Descricao_Detalhada = ?, Codigo_Solicitante = ? WHERE ID_Melhoria = ?";
        $query_params = array_values($params);
        $query_params[] = $id;
        $stmt = sqlsrv_query($conn, $sql, $query_params);
        $mensagem_sucesso = "Solicitação de melhoria atualizada com sucesso!";
    } else {
        // Insere nova solicitação
        $sql = "INSERT INTO SGM_Melhorias (Titulo_Solicitacao, Descricao_Detalhada, Codigo_Solicitante, Data_Criacao, Status) VALUES (?, ?, ?, ?, 'Em Aberto')";
        $params[] = date('Y-m-d H:i:s');
        $stmt = sqlsrv_query($conn, $sql, array_values($params));
        $mensagem_sucesso = "Solicitação de melhoria criada com sucesso!";
    }
    
    if ($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => $mensagem_sucesso]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao salvar a solicitação.", "details" => sqlsrv_errors()]);
    }
}
elseif ($action === 'delete') {
    $id = $_POST['id_melhoria'] ?? '';
    if (empty($id)) {
        echo json_encode(["sucesso" => false, "erro" => "ID da solicitação não informado"]);
        exit;
    }
    $sql = "DELETE FROM SGM_Melhorias WHERE ID_Melhoria = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);
    if ($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Solicitação apagada com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao apagar solicitação.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida para solicitações de melhoria"]);
}
exit;
?>