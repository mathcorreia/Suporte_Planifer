<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    $sql = "SELECT TAGStatusID, TAGStatus FROM SGM_TAGStatus ORDER BY TAGStatus";
    $stmt = sqlsrv_query($conn, $sql);
    $lista = [];
    if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $lista[] = $row; } }
    echo json_encode($lista);
}
elseif ($action === 'save') {
    $id = $_POST['TAGStatusID'] ?? '';
    $descricao = $_POST['TAGStatus'] ?? '';

    if (empty($descricao)) {
        echo json_encode(["sucesso" => false, "mensagem" => "A descrição do status é obrigatória."]);
        exit;
    }

    if (!empty($id)) {
        $sql = "UPDATE SGM_TAGStatus SET TAGStatus = ? WHERE TAGStatusID = ?";
        $params = [$descricao, $id];
        $mensagem_sucesso = "Status atualizado com sucesso!";
    } else {
        $sql = "INSERT INTO SGM_TAGStatus (TAGStatus) VALUES (?)";
        $params = [$descricao];
        $mensagem_sucesso = "Status cadastrado com sucesso!";
    }
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    if($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => $mensagem_sucesso]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao salvar status.", "details" => sqlsrv_errors()]);
    }
}
elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        echo json_encode(["sucesso" => false, "mensagem" => "ID do status não informado."]);
        exit;
    }
    $sql = "DELETE FROM SGM_TAGStatus WHERE TAGStatusID = ?";
    $stmt = sqlsrv_query($conn, $sql, [$id]);
    if ($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Status apagado com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao apagar status. Verifique se ele não está em uso.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida"]);
}
exit;
?>