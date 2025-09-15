<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if (!$conn) {
  echo json_encode(["erro" => "Falha na conexão com a base de dados."]);
  exit;
}

$stats = [];

// Total de Ativos
$sql_ativos = "SELECT COUNT(id) as total FROM SISTEMAS_SUPORTE.SGM_Ativos";
$stmt_ativos = sqlsrv_query($conn, $sql_ativos);
$stats['total_ativos'] = ($stmt_ativos) ? sqlsrv_fetch_array($stmt_ativos, SQLSRV_FETCH_ASSOC)['total'] : 0;

// OS Abertas e Máquinas Paradas
$sql_os = "SELECT COUNT(os_tag) as total_os, SUM(CAST(maquina_parada AS INT)) as total_paradas FROM SISTEMAS_SUPORTE.SGM_OS WHERE data_conclusao IS NULL";
$stmt_os = sqlsrv_query($conn, $sql_os);
if($stmt_os) {
    $res_os = sqlsrv_fetch_array($stmt_os, SQLSRV_FETCH_ASSOC);
    $stats['os_abertas'] = $res_os['total_os'] ?? 0;
    $stats['maquinas_paradas'] = $res_os['total_paradas'] ?? 0;
} else {
    $stats['os_abertas'] = 0;
    $stats['maquinas_paradas'] = 0;
}

echo json_encode($stats);
exit;
?>