<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/database/config.php';
header('Content-Type: application/json');

$response = [];

// 1. Estatísticas principais (Cards)
$sql_stats = "SELECT 
    (SELECT COUNT(Ativo_TAG) FROM SGM_Ativos) as total_ativos,
    (SELECT COUNT(Setor_TAG) FROM SGM_Setores) as total_setores,
    (SELECT COUNT(OS_ID) FROM SGM_OS WHERE Data_Fim_Atendimento IS NULL) as os_abertas,
    (SELECT COUNT(OS_ID) FROM SGM_OS WHERE Data_Fim_Atendimento IS NULL AND Maquina_Parada = 1) as maquinas_paradas";

$stmt_stats = sqlsrv_query($conn, $sql_stats);
if ($stmt_stats) {
    $response['stats'] = sqlsrv_fetch_array($stmt_stats, SQLSRV_FETCH_ASSOC);
} else {
    die(json_encode(["erro" => "Erro ao buscar estatísticas.", "details" => sqlsrv_errors()]));
}

// 2. Dados para o Gráfico de Status das OS
$sql_status = "SELECT Status, COUNT(OS_ID) as total 
               FROM SGM_OS 
               WHERE Data_Fim_Atendimento IS NULL AND Status IS NOT NULL
               GROUP BY Status 
               ORDER BY total DESC";
$stmt_status = sqlsrv_query($conn, $sql_status);
$status_data = [];
if ($stmt_status) {
    while($row = sqlsrv_fetch_array($stmt_status, SQLSRV_FETCH_ASSOC)){
        // Renomeia 'Status' para 'TAGStatus' para ser compatível com o gráfico
        $status_data[] = ['TAGStatus' => $row['Status'], 'total' => $row['total']];
    }
    $response['os_status_breakdown'] = $status_data;
} else {
     die(json_encode(["erro" => "Erro ao buscar status das OS.", "details" => sqlsrv_errors()]));
}

// 3. Últimas 5 OS Abertas - CORRIGIDO
// Usa os nomes de coluna corretos (OS_ID, Ativo_TAG, etc.) e cria apelidos (aliases) para o frontend
$sql_recent = "SELECT TOP 5 
                    OS_ID as os_tag, 
                    Ativo_TAG as ativo_tag, 
                    Descricao_Servico as descricao_problema, 
                    Data_Solicitacao as data_criacao 
               FROM SGM_OS 
               WHERE Data_Fim_Atendimento IS NULL 
               ORDER BY Data_Solicitacao DESC";
$stmt_recent = sqlsrv_query($conn, $sql_recent);
$recent_os = [];
if($stmt_recent){
    while($row = sqlsrv_fetch_array($stmt_recent, SQLSRV_FETCH_ASSOC)){
        if ($row['data_criacao'] instanceof DateTime) {
            $row['data_criacao'] = $row['data_criacao']->format('d/m/Y H:i');
        }
        $recent_os[] = $row;
    }
    $response['recent_os'] = $recent_os;
} else {
    die(json_encode(["erro" => "Erro ao buscar OS recentes.", "details" => sqlsrv_errors()]));
}

echo json_encode($response);
exit;
?>