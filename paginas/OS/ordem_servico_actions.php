<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get_open') {
    // Corrigido: Totalmente reescrito para ser compatível com a sua tabela dbo.SGM_OS
    $sql = "SELECT 
                OS_ID as os_tag, 
                Ativo_TAG as ativo_tag,
                Descricao_Servico as descricao_problema,
                Data_Solicitacao as data_criacao,
                Status as status_atual
            FROM SGM_OS 
            WHERE Data_Fim_Atendimento IS NULL 
            ORDER BY Data_Solicitacao ASC";

    $stmt = sqlsrv_query($conn, $sql);
    $data = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row['maquina_parada'] = 0; 
            $data[] = $row;
        }
    } else {
        echo json_encode(["erro" => "Falha na consulta SQL.", "details" => sqlsrv_errors()]);
        exit;
    }
    echo json_encode($data);
}
elseif ($action === 'create') {
    // Corrigido: Lógica de inserção compatível com a sua tabela dbo.SGM_OS
    $ativo_tag = $_POST['ativo_tag'] ?? '';
    if (empty($ativo_tag)) {
        echo json_encode(["sucesso" => false, "mensagem" => "A TAG do Ativo é obrigatória."]);
        exit;
    }
    
    $dataCriacao = date('Y-m-d H:i:s');
    $statusInicial = 'Aguardando Análise';
    
    $sql_os = "INSERT INTO SGM_OS (Ativo_TAG, Solicitante, Data_Solicitacao, Status, Descricao_Servico) VALUES (?, ?, ?, ?, ?)";
    $params_os = [
        $ativo_tag, 
        $_POST['solicitante'] ?? null, 
        $dataCriacao,
        $statusInicial,
        $_POST['descricao_problema'] ?? ''
    ];
    
    $stmt_os = sqlsrv_query($conn, $sql_os, $params_os);
    
    if ($stmt_os) {
        echo json_encode(["sucesso" => true, "mensagem" => "Ordem de Serviço criada com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Falha ao criar a Ordem de Serviço.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida para Ordens de Serviço"]);
}
exit;
?>