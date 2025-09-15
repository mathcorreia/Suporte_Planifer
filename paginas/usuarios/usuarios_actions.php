<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com a base de dados."]);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get') {
  $sql = "SELECT codigo, nome, ativo, cliente, tecnico, planejador, administrador FROM SGM_Usuarios";
  $stmt = sqlsrv_query($conn, $sql);
  $usuarios = [];
  if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
      $usuarios[] = $row;
    }
    echo json_encode($usuarios);
  } else {
    echo json_encode(["erro" => "Erro ao consultar utilizadores"]);
  }
  exit;
}

if ($action === 'save') {
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
        $stmt = sqlsrv_query($conn, $sql, array_merge(array_values($params), [$codigo_original]));
        if($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Utilizador atualizado com sucesso!"]);
        } else {
            echo json_encode(["erro" => "Erro ao atualizar utilizador.", "details" => sqlsrv_errors()]);
        }
    } else {
        $sql = "INSERT INTO SGM_Usuarios (codigo, nome, ativo, cliente, tecnico, planejador, administrador) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $sql, array_merge([$codigo], array_values($params)));
        if($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => "Utilizador cadastrado com sucesso!"]);
        } else {
            echo json_encode(["erro" => "Erro ao cadastrar utilizador. O código já pode existir.", "details" => sqlsrv_errors()]);
        }
    }
    exit;
}

if ($action === 'delete') {
    $codigo = $_POST['codigo'] ?? '';
    if (empty($codigo)) {
      echo json_encode(["erro" => "Código não informado"]);
      exit;
    }
    $stmt = sqlsrv_query($conn, "DELETE FROM SGM_Usuarios WHERE codigo = ?", [$codigo]);
    if($stmt) {
        echo json_encode(["sucesso" => true, "mensagem" => "Utilizador apagado com sucesso!"]);
    } else {
        echo json_encode(["erro" => "Erro ao apagar utilizador."]);
    }
    exit;
}

echo json_encode(["erro" => "Ação inválida"]);
?>