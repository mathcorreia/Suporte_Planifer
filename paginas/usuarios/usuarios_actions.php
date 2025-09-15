<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../config.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'get') {
  $sql = "SELECT codigo, nome, ativo, cliente, tecnico, planejador, administrador FROM SGM_Usuarios";
  $stmt = sqlsrv_query($conn, $sql);
  $usuarios = [];
  if ($stmt) { while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { $usuarios[] = $row; } }
  echo json_encode($usuarios);
}
elseif ($action === 'save') {
    $codigo_original = $_POST['codigo_original'] ?? '';
    $params = [$_POST['nome'] ?? '', $_POST['ativo'] ?? 0, $_POST['cliente'] ?? 0, $_POST['tecnico'] ?? 0, $_POST['planejador'] ?? 0, $_POST['admin'] ?? 0];
    if (!empty($codigo_original)) {
        $sql = "UPDATE SGM_Usuarios SET nome = ?, ativo = ?, cliente = ?, tecnico = ?, planejador = ?, administrador = ? WHERE codigo = ?";
        $params[] = $codigo_original;
        $stmt = sqlsrv_query($conn, $sql, $params);
        echo json_encode(["sucesso" => $stmt ? true : false, "mensagem" => $stmt ? "Utilizador atualizado com sucesso!" : "Erro ao atualizar utilizador."]);
    } else {
        $sql = "INSERT INTO SGM_Usuarios (codigo, nome, ativo, cliente, tecnico, planejador, administrador) VALUES (?, ?, ?, ?, ?, ?, ?)";
        array_unshift($params, $_POST['codigo'] ?? '');
        $stmt = sqlsrv_query($conn, $sql, $params);
        echo json_encode(["sucesso" => $stmt ? true : false, "mensagem" => $stmt ? "Utilizador cadastrado com sucesso!" : "Erro ao cadastrar utilizador."]);
    }
}
elseif ($action === 'delete') {
    $codigo = $_POST['codigo'] ?? '';
    if (empty($codigo)) { echo json_encode(["erro" => "Código não informado"]); exit; }
    $stmt = sqlsrv_query($conn, "DELETE FROM SGM_Usuarios WHERE codigo = ?", [$codigo]);
    echo json_encode(["sucesso" => $stmt ? true : false, "mensagem" => $stmt ? "Utilizador apagado com sucesso!" : "Erro ao apagar utilizador."]);
}
else {
    echo json_encode(["erro" => "Ação inválida"]);
}
exit;
?>