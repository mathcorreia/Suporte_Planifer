<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    $sql = "SELECT Ativo_TAG as ativo_tag, Descricao as descricao, Modelo as modelo, Numero_Serie as numero_serie, Setor_TAG as setor_tag, Tipo as tipo FROM SGM_Ativos";
    $stmt = sqlsrv_query($conn, $sql);
    $ativos = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $ativos[] = $row;
        }
    }
    echo json_encode($ativos);
}
elseif ($action === 'save') {
    $tagOriginal = $_POST['ativo_tag_original'] ?? '';
    $params = [
        'ativo_tag' => $_POST['ativo_tag'] ?? '',
        'descricao' => $_POST['descricao'] ?? '',
        'modelo' => $_POST['modelo'] ?? '',
        'numero_serie' => $_POST['numero_serie'] ?? '',
        'setor_tag' => $_POST['setor_tag'] ?? '',
        'tipo' => $_POST['tipo'] ?? ''
    ];

    if (!empty($tagOriginal)) {
        // Lógica de ATUALIZAÇÃO (EDIÇÃO)
        $sql = "UPDATE SGM_Ativos SET Ativo_TAG = ?, Descricao = ?, Modelo = ?, Numero_Serie = ?, Setor_TAG = ?, Tipo = ? WHERE Ativo_TAG = ?";
        $query_params = array_values($params);
        $query_params[] = $tagOriginal;
        
        $stmt = sqlsrv_query($conn, $sql, $query_params);
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ativo atualizado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar ativo.", "details" => sqlsrv_errors()]);
        }
    } else {
        // Lógica de CRIAÇÃO (NOVO)
        $sql = "INSERT INTO SGM_Ativos (Ativo_TAG, Descricao, Modelo, Numero_Serie, Setor_TAG, Tipo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $sql, array_values($params));
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ativo cadastrado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao cadastrar ativo.", "details" => sqlsrv_errors()]);
        }
    }
}
elseif ($action === 'delete') {
    $tag = $_POST['ativo_tag'] ?? '';
    if (empty($tag)) {
        echo json_encode(["sucesso" => false, "erro" => "TAG do ativo não informada"]);
        exit;
    }
    $stmt = sqlsrv_query($conn, "DELETE FROM SGM_Ativos WHERE Ativo_TAG = ?", [$tag]);
    if ($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Ativo apagado com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao apagar ativo.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida para ativos"]);
}
exit;
?>