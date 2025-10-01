<?php
require_once 'conexao.php';
header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com o banco"]);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get') {
  $usuarios = [];
  $sql = "SELECT codigo, nome, senha, ativo, cliente, tecnico, planejador, administrador FROM SGM_Usuarios";
  $stmt = sqlsrv_query($conn, $sql);

  if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
      $usuarios[] = $row;
    }
    echo json_encode($usuarios);
  } else {
    error_log("Erro na consulta: " . print_r(sqlsrv_errors(), true));
    echo json_encode(["erro" => "Erro ao consultar usuários"]);
  }
  exit;
}if ($action === 'save') {
    $codigo = $_POST['codigo'] ?? '';
    $nome = $_POST['nome'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $ativo = $_POST['ativo'] ?? 0;
    $cliente = $_POST['cliente'] ?? 0;
    $tecnico = $_POST['tecnico'] ?? 0;
    $planejador = $_POST['planejador'] ?? 0;
    $admin = $_POST['admin'] ?? 0;

    // Verifica se o usuário já existe
    $checkSql = "SELECT COUNT(*) AS total FROM SGM_Usuarios WHERE codigo = ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, [$codigo]);
    $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($checkRow && $checkRow['total'] > 0) {
        // Atualiza usuário existente
        $updateSql = "UPDATE SGM_Usuarios SET 
            nome = ?, senha = ?, ativo = ?, cliente = ?, tecnico = ?, planejador = ?, administrador = ?
            WHERE codigo = ?";
        $params = [$nome, $senha, $ativo, $cliente, $tecnico, $planejador, $admin, $codigo];
        $stmt = sqlsrv_query($conn, $updateSql, $params);
        echo json_encode(["mensagem" => $stmt ? "Usuário atualizado com sucesso!" : "Erro ao atualizar usuário."]);
    } else {
        // Insere novo usuário
        $insertSql = "INSERT INTO SGM_Usuarios (codigo, nome, senha, ativo, cliente, tecnico, planejador, administrador)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $params = [$codigo, $nome, $senha, $ativo, $cliente, $tecnico, $planejador, $admin];
        $stmt = sqlsrv_query($conn, $insertSql, $params);
        echo json_encode(["mensagem" => $stmt ? "Usuário cadastrado com sucesso!" : "Erro ao cadastrar usuário."]);
    }

    exit;
}

if ($action === 'delete') {
    $codigo = $_POST['codigo'] ?? '';
    if (!$codigo) {
        echo json_encode(["erro" => "Código não informado"]);
        exit;
    }

    $deleteSql = "DELETE FROM SGM_Usuarios WHERE codigo = ?";
    $stmt = sqlsrv_query($conn, $deleteSql, [$codigo]);

    echo json_encode([
        "mensagem" => $stmt ? "Usuário apagado com sucesso!" : "Erro ao apagar usuário."
    ]);
    exit;
}

// Ação inválida
echo json_encode(["erro" => "Ação inválida"]);
exit;