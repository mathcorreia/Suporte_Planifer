<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    $sql = "SELECT codigo, nome, ativo, cliente, tecnico, planejador, administrador FROM SGM_Usuarios";
    $stmt = sqlsrv_query($conn, $sql);
    $usuarios = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $usuarios[] = $row;
        }
    }
    echo json_encode($usuarios);
}
elseif ($action === 'save') {
    $codigo_original = $_POST['codigo_original'] ?? '';
    $codigo = $_POST['codigo'] ?? '';
    
    $params = [
        'nome' => $_POST['nome'] ?? '',
        'ativo' => isset($_POST['ativo']) ? 1 : 0,
        'cliente' => isset($_POST['cliente']) ? 1 : 0,
        'tecnico' => isset($_POST['tecnico']) ? 1 : 0,
        'planejador' => isset($_POST['planejador']) ? 1 : 0,
        'admin' => isset($_POST['admin']) ? 1 : 0,
    ];

    if (!empty($codigo_original)) {
        $sql = "UPDATE SGM_Usuarios SET nome = ?, ativo = ?, cliente = ?, tecnico = ?, planejador = ?, administrador = ? WHERE codigo = ?";
        $query_params = array_values($params);
        $query_params[] = $codigo_original;
        
        $stmt = sqlsrv_query($conn, $sql, $query_params);
        
        if($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Utilizador atualizado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar utilizador.", "details" => sqlsrv_errors()]);
        }
    } else {
        $sql = "INSERT INTO SGM_Usuarios (codigo, nome, ativo, cliente, tecnico, planejador, administrador) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $query_params = array_merge([$codigo], array_values($params));

        $stmt = sqlsrv_query($conn, $sql, $query_params);
        
        if($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Utilizador cadastrado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao cadastrar utilizador.", "details" => sqlsrv_errors()]);
        }
    }
}
elseif ($action === 'delete') {
    $codigo = $_POST['codigo'] ?? '';
    if (empty($codigo)) {
        echo json_encode(["sucesso" => false, "erro" => "Código não informado"]);
        exit;
    }
    $stmt = sqlsrv_query($conn, "DELETE FROM SGM_Usuarios WHERE codigo = ?", [$codigo]);
    if ($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Utilizador apagado com sucesso!"]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao apagar utilizador.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida"]);
}
exit;
?>