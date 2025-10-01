<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    $sql = "SELECT * FROM SGM_Ativos";
    $stmt = sqlsrv_query($conn, $sql);
    $ativos = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            if ($row['Instalacao'] instanceof DateTime) {
                $row['Instalacao'] = $row['Instalacao']->format('Y-m-d');
            }
            $ativos[] = $row;
        }
    }
    echo json_encode($ativos);
}
elseif ($action === 'save') {
    $tagOriginal = $_POST['ativo_tag_original'] ?? '';
    
    $params = [
        'Ativo_TAG' => $_POST['Ativo_TAG'] ?? '',
        'Setor_TAG' => $_POST['Setor_TAG'] ?? null,
        'Descricao' => $_POST['Descricao'] ?? null,
        'Modelo' => $_POST['Modelo'] ?? null,
        'Numero_Serie' => $_POST['Numero_Serie'] ?? null,
        'Ferramenta' => isset($_POST['Ferramenta']) ? 1 : 0,
        'Maquina' => isset($_POST['Maquina']) ? 1 : 0,
        'Tipo' => $_POST['Tipo'] ?? null,
        'Sensor' => $_POST['Sensor'] ?? null,
        'Comando' => $_POST['Comando'] ?? null,
        'Rede_Eletrica_TAG' => $_POST['Rede_Eletrica_TAG'] ?? null,
        'Instalacao' => empty($_POST['Instalacao']) ? null : $_POST['Instalacao'],
        'Corrente' => empty($_POST['Corrente']) ? null : (int)$_POST['Corrente'],
        'Turno1' => isset($_POST['Turno1']) ? 1 : 0,
        'Turno2' => isset($_POST['Turno2']) ? 1 : 0,
        'Turno3' => isset($_POST['Turno3']) ? 1 : 0,
        'Controle' => isset($_POST['Controle']) ? 1 : 0,
    ];

    if (!empty($tagOriginal)) {
        $sql_parts = [];
        foreach ($params as $key => $value) {
            $sql_parts[] = "$key = ?";
        }
        $sql = "UPDATE SGM_Ativos SET " . implode(', ', $sql_parts) . " WHERE Ativo_TAG = ?";
        
        $query_params = array_values($params);
        $query_params[] = $tagOriginal;
        
        $stmt = sqlsrv_query($conn, $sql, $query_params);
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ativo atualizado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao atualizar ativo.", "details" => sqlsrv_errors()]);
        }
    } else {
        $columns = implode(', ', array_keys($params));
        $placeholders = implode(', ', array_fill(0, count($params), '?'));
        $sql = "INSERT INTO SGM_Ativos ($columns) VALUES ($placeholders)";
        
        $stmt = sqlsrv_query($conn, $sql, array_values($params));
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Ativo cadastrado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Erro ao cadastrar ativo.", "details" => sqlsrv_errors()]);
        }
    }
}
elseif ($action === 'delete') {
    $tag = $_POST['Ativo_TAG'] ?? '';
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