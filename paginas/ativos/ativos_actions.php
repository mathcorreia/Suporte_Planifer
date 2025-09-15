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
  $sql = "SELECT id, ativo_tag, descricao, modelo, numero_serie, setor_tag, tipo FROM SISTEMAS_SUPORTE.SGM_Ativos";
  $stmt = sqlsrv_query($conn, $sql);
  $ativos = [];
  if ($stmt) {
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
      $ativos[] = $row;
    }
    echo json_encode($ativos);
  } else {
    echo json_encode(["erro" => "Erro ao consultar ativos"]);
  }
  exit;
}

if ($action === 'save') {
    $idOriginal = $_POST['id_original'] ?? '';
    $params = [
        $_POST['ativo_tag'] ?? '',
        $_POST['descricao'] ?? '',
        $_POST['modelo'] ?? '',
        $_POST['numero_serie'] ?? '',
        $_POST['setor_tag'] ?? '',
        $_POST['tipo'] ?? '',
    ];

    if (!empty($idOriginal)) {
        $sql = "UPDATE SISTEMAS_SUPORTE.SGM_Ativos SET ativo_tag = ?, descricao = ?, modelo = ?, numero_serie = ?, setor_tag = ?, tipo = ? WHERE id = ?";
        $params[] = $idOriginal;
        $stmt = sqlsrv_query($conn, $sql, $params);
        echo json_encode(["mensagem" => $stmt ? "Ativo atualizado com sucesso!" : "Erro ao atualizar ativo."]);
    } else {
        $sql = "INSERT INTO SISTEMAS_SUPORTE.SGM_Ativos (ativo_tag, descricao, modelo, numero_serie, setor_tag, tipo) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = sqlsrv_query($conn, $sql, $params);
        echo json_encode(["mensagem" => $stmt ? "Ativo cadastrado com sucesso!" : "Erro ao cadastrar ativo."]);
    }
    exit;
}

if ($action === 'delete') {
    $id = $_POST['id'] ?? '';
    if (empty($id)) {
      echo json_encode(["erro" => "ID do ativo não informado"]);
      exit;
    }
    $stmt = sqlsrv_query($conn, "DELETE FROM SISTEMAS_SUPORTE.SGM_Ativos WHERE id = ?", [$id]);
    echo json_encode(["mensagem" => $stmt ? "Ativo apagado com sucesso!" : "Erro ao apagar ativo."]);
    exit;
}

echo json_encode(["erro" => "Ação inválida para ativos"]);
?>