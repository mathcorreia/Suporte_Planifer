<?php
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config.php';

header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com a base de dados."]);
  exit;
}

$action = $_POST['action'] ?? '';

if ($action === 'get_open') {
    $sql = "
        WITH LatestStatus AS (
            SELECT os_tag, tag_status_id, data_inicio,
                   ROW_NUMBER() OVER(PARTITION BY os_tag ORDER BY data_inicio DESC) as rn
            FROM SGM_OS_Status
        )
        SELECT os.os_tag, os.ativo_tag, os.maquina_parada, os.descricao_problema, os.data_criacao, st.TAGStatus as status_atual
        FROM SGM_OS os
        JOIN LatestStatus ls ON os.os_tag = ls.os_tag AND ls.rn = 1
        JOIN SGM_TAGStatus st ON ls.tag_status_id = st.TAGStatusID
        WHERE os.data_conclusao IS NULL
        ORDER BY os.maquina_parada DESC, os.data_criacao ASC";

    $stmt = sqlsrv_query($conn, $sql);
    $data = [];
    if ($stmt) {
        while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $data[] = $row;
        }
    }
    echo json_encode($data);
    exit;
}

if ($action === 'create') {
    $params = [
        'ativo_tag' => $_POST['ativo_tag'] ?? '',
        'solicitante' => $_POST['solicitante'] ?? '',
        'descricao_problema' => $_POST['descricao_problema'] ?? '',
        'maquina_parada' => isset($_POST['maquina_parada']) ? 1 : 0,
    ];

    if (empty($params['ativo_tag']) || empty($params['solicitante']) || empty($params['descricao_problema'])) {
        echo json_encode(["erro" => "Todos os campos são obrigatórios."]);
        exit;
    }

    $os_tag = strtoupper($params['ativo_tag']) . '-' . date('YmdHis');
    $dataCriacao = date('Y-m-d H:i:s');

    sqlsrv_begin_transaction($conn);

    $sql_os = "INSERT INTO SGM_OS (os_tag, ativo_tag, maquina_parada, os_tipo, data_criacao, solicitante, descricao_problema) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_os = sqlsrv_query($conn, $sql_os, [$os_tag, $params['ativo_tag'], $params['maquina_parada'], 'Corretiva', $dataCriacao, $params['solicitante'], $params['descricao_problema']]);

    $sql_status = "INSERT INTO SGM_OS_Status (os_tag, tag_status_id, data_inicio, abriu_codigo, descricao) VALUES (?, (SELECT TAGStatusID FROM SGM_TAGStatus WHERE TAGStatus = 'Aguardando Análise'), ?, ?, ?)";
    $stmt_status = sqlsrv_query($conn, $sql_status, [$os_tag, $dataCriacao, $params['solicitante'], 'Abertura da OSS']);

    if ($stmt_os && $stmt_status) {
        sqlsrv_commit($conn);
        echo json_encode(["sucesso" => true, "mensagem" => "Ordem de Serviço " . $os_tag . " criada com sucesso!"]);
    } else {
        sqlsrv_rollback($conn);
        echo json_encode(["erro" => "Falha ao criar a Ordem de Serviço.", "details" => sqlsrv_errors()]);
    }
    exit;
}

if ($action === 'get_details') {
    $os_tag = $_POST['os_tag'] ?? '';
    if (empty($os_tag)) { echo json_encode(["erro" => "OS Tag não informada."]); exit; }

    $response = [];
    $sql_os = "SELECT os.*, u.nome as solicitante_nome FROM SGM_OS os LEFT JOIN SGM_Usuarios u ON os.solicitante = u.codigo WHERE os.os_tag = ?";
    $stmt_os = sqlsrv_query($conn, $sql_os, [$os_tag]);
    $response['os'] = sqlsrv_fetch_array($stmt_os, SQLSRV_FETCH_ASSOC);

    $sql_hist = "SELECT h.*, s.TAGStatus FROM SGM_OS_Status h JOIN SGM_TAGStatus s ON h.tag_status_id = s.TAGStatusID WHERE h.os_tag = ? ORDER BY h.data_inicio DESC";
    $stmt_hist = sqlsrv_query($conn, $sql_hist, [$os_tag]);
    $response['historico'] = [];
    while($row = sqlsrv_fetch_array($stmt_hist, SQLSRV_FETCH_ASSOC)) {
        $response['historico'][] = $row;
    }
    
    $response['pode_atender'] = ($response['historico'][0]['TAGStatus'] === 'Aguardando Análise');

    echo json_encode($response);
    exit;
}

if ($action === 'attend_os') {
    $os_tag = $_POST['os_tag'] ?? '';
    $tecnico_codigo = $_POST['tecnico_codigo'] ?? ($_SESSION['usuario_codigo'] ?? 0);

    if (empty($os_tag) || empty($tecnico_codigo)) {
        echo json_encode(["erro" => "Dados insuficientes para atender a OS."]);
        exit;
    }
    
    sqlsrv_begin_transaction($conn);

    $dataAtendimento = date('Y-m-d H:i:s');
    
    // Fecha o status 'Aguardando Análise'
    $sql_close = "UPDATE SGM_OS_Status SET data_fim = ?, fechou_codigo = ?, descricao = 'Atendido pelo técnico' WHERE os_tag = ? AND data_fim IS NULL AND tag_status_id = (SELECT TAGStatusID FROM SGM_TAGStatus WHERE TAGStatus = 'Aguardando Análise')";
    $stmt_close = sqlsrv_query($conn, $sql_close, [$dataAtendimento, $tecnico_codigo, $os_tag]);

    // Abre o status 'Em Atendimento'
    $sql_open = "INSERT INTO SGM_OS_Status (os_tag, tag_status_id, data_inicio, abriu_codigo, descricao) VALUES (?, (SELECT TAGStatusID FROM SGM_TAGStatus WHERE TAGStatus = 'Em Atendimento'), ?, ?, ?)";
    $stmt_open = sqlsrv_query($conn, $sql_open, [$os_tag, $dataAtendimento, $tecnico_codigo, 'Início do atendimento técnico']);

    if ($stmt_close && $stmt_open) {
        sqlsrv_commit($conn);
        echo json_encode(["sucesso" => true, "mensagem" => "Atendimento da OS " . $os_tag . " iniciado!"]);
    } else {
        sqlsrv_rollback($conn);
        echo json_encode(["erro" => "Falha ao iniciar atendimento.", "details" => sqlsrv_errors()]);
    }
    exit;
}


echo json_encode(["erro" => "Ação inválida."]);
?>