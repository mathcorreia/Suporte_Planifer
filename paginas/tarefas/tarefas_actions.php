<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
  $sql = "SELECT tarefa_codigo, tarefa_tag, ativo_tag, tarefa_descricao, ultima_execucao FROM SGM_Tarefas";
  $stmt = sqlsrv_query($conn, $sql);
  $tarefas = [];
  if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        if ($row['ultima_execucao'] instanceof DateTime) { $row['ultima_execucao'] = $row['ultima_execucao']->format('Y-m-d'); }
        $tarefas[] = $row;
    }
  }
  echo json_encode($tarefas);
}
elseif ($action === 'save') {
    $id = $_POST['tarefa_codigo'] ?? '';
    $params = [$_POST['tarefa_tag'] ?? null, $_POST['ativo_tag'] ?? null, $_POST['tarefa_descricao'] ?? null, empty($_POST['ultima_execucao']) ? null : $_POST['ultima_execucao']];
    if (!empty($id)) {
        $sql = "UPDATE SGM_Tarefas SET tarefa_tag = ?, ativo_tag = ?, tarefa_descricao = ?, ultima_execucao = ? WHERE tarefa_codigo = ?";
        $params[] = $id;
        $stmt = sqlsrv_query($conn, $sql, $params);
        echo json_encode(["mensagem" => $stmt ? "Tarefa atualizada com sucesso!" : "Erro ao atualizar tarefa."]);
    } else {
        $sql = "INSERT INTO SGM_Tarefas (tarefa_tag, ativo_tag, tarefa_descricao, ultima_execucao) VALUES (?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $sql, $params);
        echo json_encode(["mensagem" => $stmt ? "Tarefa cadastrada com sucesso!" : "Erro ao cadastrar tarefa."]);
    }
}
elseif ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (empty($id)) { echo json_encode(["erro" => "ID da tarefa não informado"]); exit; }
    $stmt = sqlsrv_query($conn, "DELETE FROM SGM_Tarefas WHERE tarefa_codigo = ?", [$id]);
    echo json_encode(["mensagem" => $stmt ? "Tarefa apagada com sucesso!" : "Erro ao apagar tarefa."]);
}
else {
    echo json_encode(["erro" => "Ação inválida para tarefas"]);
}
exit;
?>