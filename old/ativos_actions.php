<?php
require_once 'conexao.php'; // Reutiliza sua conexão com o banco
header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com o banco"]);
  exit;
}

$action = $_POST['action'] ?? '';

// Ação para buscar todos os ativos
if ($action === 'get') {
  $ativos = [];
  $sql = "SELECT id, ativo_tag, descricao, modelo, numero_serie, setor_tag, tipo FROM SGM_Ativos";
  $stmt = sqlsrv_query($conn, $sql);

  if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
      $ativos[] = $row;
    }
    echo json_encode($ativos);
  } else {
    error_log("Erro na consulta de ativos: " . print_r(sqlsrv_errors(), true));
    echo json_encode(["erro" => "Erro ao consultar ativos"]);
  }
  exit;
}

// Ação para salvar (inserir ou atualizar) um ativo
if ($action === 'save') {
    $tag = $_POST['ativo_tag'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $numero_serie = $_POST['numero_serie'] ?? '';
    $setor_tag = $_POST['setor_tag'] ?? '';
    $tipo = $_POST['tipo'] ?? '';
    $idOriginal = $_POST['id_original'] ?? ''; // Usado para saber qual registro atualizar

    // Verifica se o ativo já existe para decidir entre UPDATE e INSERT
    $checkSql = "SELECT id FROM SGM_Ativos WHERE id = ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, [$idOriginal]);
    $checkRow = sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC);

    if ($checkRow && $idOriginal) {
        // Atualiza ativo existente
        $updateSql = "UPDATE SGM_Ativos SET 
            ativo_tag = ?, descricao = ?, modelo = ?, numero_serie = ?, setor_tag = ?, tipo = ?
            WHERE id = ?";
        $params = [$tag, $descricao, $modelo, $numero_serie, $setor_tag, $tipo, $idOriginal];
        $stmt = sqlsrv_query($conn, $updateSql, $params);
        echo json_encode(["mensagem" => $stmt ? "Ativo atualizado com sucesso!" : "Erro ao atualizar ativo."]);
    } else {
        // Insere novo ativo
        $insertSql = "INSERT INTO SGM_Ativos (ativo_tag, descricao, modelo, numero_serie, setor_tag, tipo)
            VALUES (?, ?, ?, ?, ?, ?)";
        $params = [$tag, $descricao, $modelo, $numero_serie, $setor_tag, $tipo];
        $stmt = sqlsrv_query($conn, $insertSql, $params);
        echo json_encode(["mensagem" => $stmt ? "Ativo cadastrado com sucesso!" : "Erro ao cadastrar ativo."]);
    }
    exit;
}

// Ação para apagar um ativo
if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (!$id) {
        echo json_encode(["erro" => "ID do ativo não informado"]);
        exit;
    }

    $deleteSql = "DELETE FROM SGM_Ativos WHERE id = ?";
    $stmt = sqlsrv_query($conn, $deleteSql, [$id]);

    echo json_encode([
        "mensagem" => $stmt ? "Ativo apagado com sucesso!" : "Erro ao apagar ativo."
    ]);
    exit;
}

// Ação inválida
echo json_encode(["erro" => "Ação inválida para ativos"]);
exit;