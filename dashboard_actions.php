<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Caminho corrigido para a raiz
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$stats = [];

$sql_ativos = "SELECT COUNT(id) as total FROM SGM_Ativos";
$stmt_ativos = sqlsrv_query($conn, $sql_ativos);
if ($stmt_ativos === false) { die(json_encode(["erro" => "Erro na consulta de ativos.", "details" => sqlsrv_errors()])); }
$stats['total_ativos'] = sqlsrv_fetch_array($stmt_ativos, SQLSRV_FETCH_ASSOC)['total'] ?? 0;

$sql_os = "SELECT COUNT(os_tag) as total_os, SUM(CAST(maquina_parada AS INT)) as total_paradas FROM SGM_OS WHERE data_conclusao IS NULL";
$stmt_os = sqlsrv_query($conn, $sql_os);
if($stmt_os === false) { die(json_encode(["erro" => "Erro na consulta de Ordens de Serviço.", "details" => sqlsrv_errors()])); }
$res_os = sqlsrv_fetch_array($stmt_os, SQLSRV_FETCH_ASSOC);
$stats['os_abertas'] = $res_os['total_os'] ?? 0;
$stats['maquinas_paradas'] = $res_os['total_paradas'] ?? 0;

echo json_encode($stats);
exit;
?>