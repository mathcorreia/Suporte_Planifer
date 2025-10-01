<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../database/config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
    // Corrigido: Usa 'Nome_Setor' e dá o apelido de 'descricao' para compatibilidade
    $sql = "SELECT Setor_TAG as setor_tag, Nome_Setor as descricao FROM SGM_Setores";
    $stmt = sqlsrv_query($conn, $sql);
    $setores = [];
    if ($stmt) { 
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { 
            $setores[] = $row; 
        } 
    } else {
        echo json_encode(["erro" => "Falha ao buscar setores.", "details" => sqlsrv_errors()]);
        exit;
    }
    echo json_encode($setores);
}
elseif ($action === 'save') {
    $tag_original = $_POST['setor_tag_original'] ?? '';
    $tag_nova = $_POST['setor_tag'] ?? null;
    $descricao_do_formulario = $_POST['descricao'] ?? '';

    if ($tag_nova === null || trim($tag_nova) === '' || trim($descricao_do_formulario) === '') {
        echo json_encode(["sucesso" => false, "mensagem" => "A TAG e a Descrição do setor são obrigatórias."]);
        exit;
    }

    if (!empty($tag_original)) {
        // Corrigido: Atualiza a coluna 'Nome_Setor'
        $sql = "UPDATE SGM_Setores SET Setor_TAG = ?, Nome_Setor = ? WHERE Setor_TAG = ?";
        $params = [$tag_nova, $descricao_do_formulario, $tag_original];
        $mensagem_sucesso = "Setor atualizado com sucesso!";
        $mensagem_erro = "Erro ao atualizar setor.";
    } else {
        // Corrigido: Insere na coluna 'Nome_Setor'
        $sql = "INSERT INTO SGM_Setores (Setor_TAG, Nome_Setor) VALUES (?, ?)";
        $params = [$tag_nova, $descricao_do_formulario];
        $mensagem_sucesso = "Setor cadastrado com sucesso!";
        $mensagem_erro = "Erro ao cadastrar setor. A TAG já pode existir.";
    }
    
    $stmt = sqlsrv_query($conn, $sql, $params);
    
    if($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => $mensagem_sucesso]);
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => $mensagem_erro, "details" => sqlsrv_errors()]);
    }
}
elseif ($action === 'delete') {
    $tag = $_POST['setor_tag'] ?? null;
    if ($tag === null || $tag === '') {
        echo json_encode(["sucesso" => false, "mensagem" => "TAG do setor não informada."]);
        exit;
    }
    $sql = "DELETE FROM SGM_Setores WHERE Setor_TAG = ?";
    $stmt = sqlsrv_query($conn, $sql, [$tag]);
    if ($stmt) {
        $rows_affected = sqlsrv_rows_affected($stmt);
        if ($rows_affected > 0) {
            echo json_encode(["sucesso" => true, "mensagem" => "Setor apagado com sucesso!"]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Nenhum setor encontrado com essa TAG."]);
        }
    } else {
        echo json_encode(["sucesso" => false, "mensagem" => "Erro ao apagar setor. Verifique se ele não está em uso por algum ativo.", "details" => sqlsrv_errors()]);
    }
}
else {
    echo json_encode(["erro" => "Ação inválida"]);
}
exit;
?>