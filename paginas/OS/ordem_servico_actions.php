<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Caminho corrigido
require_once __DIR__ . '/../../../database/config.php';

header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com a base de dados."]);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get_open') {
    $oss_abertas = [];
    $sql = "
        WITH LatestStatus AS (
            SELECT
                os_tag,
                tag_status_id,
                ROW_NUMBER() OVER(PARTITION BY os_tag ORDER BY data_inicio DESC) as rn
            FROM SGM_OS_Status
        )
        SELECT
            os.os_tag, os.ativo_tag, os.maquina_parada,
            os.descricao_problema, os.data_criacao, st.TAGStatus as status_atual
        FROM SGM_OS os
        JOIN LatestStatus ls ON os.os_tag = ls.os_tag AND ls.rn = 1
        JOIN SGM_TAGStatus st ON ls.tag_status_id = st.TAGStatusID
        WHERE st.TAGStatus <> 'Concluída'
        ORDER BY os.maquina_parada DESC, os.data_criacao ASC";

    $stmt = sqlsrv_query($conn, $sql);
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $oss_abertas[] = $row;
        }
        echo json_encode($oss_abertas);
    } else {
        echo json_encode(["erro" => "Erro ao consultar Ordens de Serviço", "details" => sqlsrv_errors()]);
    }
    exit;
}

if ($action === 'create') {
    if (empty($_POST['ativo_tag']) || empty($_POST['responsavel_codigo']) || empty($_POST['descricao_problema'])) {
        echo json_encode(["erro" => "Campos obrigatórios não preenchidos."]);
        exit;
    }

    sqlsrv_begin_transaction($conn);

    $dataCriacao = date('Y-m-d H:i:s');
    $os_tag = strtoupper($_POST['ativo_tag']) . '-' . date('YmdHis');

    $sql_os = "INSERT INTO SGM_OS (os_tag, ativo_tag, maquina_parada, os_tipo, data_criacao, solicitante, descricao_problema) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $params_os = [
        $os_tag, $_POST['ativo_tag'], $_POST['maquina_parada'] ?? 0,
        'Corretiva', $dataCriacao, $_POST['responsavel_codigo'], $_POST['descricao_problema']
    ];
    $stmt_os = sqlsrv_query($conn, $sql_os, $params_os);

    $sql_status = "INSERT INTO SGM_OS_Status (os_tag, tag_status_id, data_inicio, abriu_codigo, descricao) VALUES (?, (SELECT TAGStatusID FROM SGM_TAGStatus WHERE TAGStatus = 'Aguardando Análise'), ?, ?, ?)";
    $params_status = [$os_tag, $dataCriacao, $_POST['responsavel_codigo'], 'Abertura da OSS'];
    $stmt_status = sqlsrv_query($conn, $sql_status, $params_status);

    if ($stmt_os && $stmt_status) {
        sqlsrv_commit($conn);
        echo json_encode(["mensagem" => "Ordem de Serviço " . $os_tag . " criada com sucesso!"]);
    } else {
        sqlsrv_rollback($conn);
        echo json_encode(["erro" => "Falha ao criar a Ordem de Serviço.", "details" => sqlsrv_errors()]);
    }
    exit;
}

echo json_encode(["erro" => "Ação inválida para Ordens de Serviço"]);
?>