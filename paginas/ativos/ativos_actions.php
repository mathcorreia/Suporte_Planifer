<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
// Caminho corrigido para subir três níveis até à raiz
require_once __DIR__ . '/../../../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    $sql = "SELECT id, ativo_tag, descricao, modelo, numero_serie, setor_tag, tipo FROM SGM_Ativos";
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
    $idOriginal = $_POST['id_original'] ?? '';
    $params = [
        $_POST['ativo_tag'] ?? '',
        $_POST['descricao'] ?? '',
        $_POST['modelo'] ?? '',
        $_POST['numero_serie'] ?? '',
        $_POST['setor_tag'] ?? '',
        $_POST['tipo'] ?? ''
    ];

    if (!empty($idOriginal)) {
        $sql = "UPDATE SGM_Ativos SET ativo_tag = ?, descricao = ?, modelo = ?, numero_serie = ?, setor_tag = ?, tipo = ? WHERE id = ?";
        $params[] = $idOriginal;
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ativo atualizado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar ativo.", "details" => sqlsrv_errors()]);
        }
    } else {
        $sql = "INSERT INTO SGM_Ativos (ativo_tag, descricao, modelo, numero_serie, setor_tag, tipo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $sql, $params);
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ativo cadastrado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao cadastrar ativo.", "details" => sqlsrv_errors()]);
        }
    }
}
elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
        echo json_encode(["sucesso" => false, "erro" => "ID do ativo não informado"]);
        exit;
    }
    $stmt = sqlsrv_query($conn, "DELETE FROM SGM_Ativos WHERE id = ?", [$id]);
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