<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    $sql = "SELECT setor_tag, descricao FROM SGM_Setores";
    $stmt = sqlsrv_query($conn, $sql);
    $setores = [];
    if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $setores[] = $row; } }
    echo json_encode($setores);
}
elseif ($action === 'save') {
    $tag = $_POST['setor_tag'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    
    // Verifica se o setor já existe para evitar erro de chave primária
    $check_sql = "SELECT COUNT(*) as total FROM SGM_Setores WHERE setor_tag = ?";
    $check_stmt = sqlsrv_query($conn, $check_sql, [$tag]);
    $row = sqlsrv_fetch_array($check_stmt, SQLSRV_FETCH_ASSOC);

    if ($row['total'] > 0) {
        // Atualiza
        $sql = "UPDATE SGM_Setores SET descricao = ? WHERE setor_tag = ?";
        $stmt = sqlsrv_query($conn, $sql, [$descricao, $tag]);
    } else {
        // Insere
        $sql = "INSERT INTO SGM_Setores (setor_tag, descricao) VALUES (?, ?)";
        $stmt = sqlsrv_query($conn, $sql, [$tag, $descricao]);
    }
    
    if($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Setor salvo com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao salvar setor.", "details" => sqlsrv_errors()]);
    }
}
elseif ($action === 'delete') {
    $tag = $_POST['setor_tag'] ?? '';
    $sql = "DELETE FROM SGM_Setores WHERE setor_tag = ?";
    $stmt = sqlsrv_query($conn, $sql, [$tag]);
    if ($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Setor apagado com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao apagar setor. Verifique se ele não está em uso por algum ativo.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida"]);
}
exit;
?>