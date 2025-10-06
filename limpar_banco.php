<?php
/**
 * SCRIPT DE MANUTENÇÃO - LIMPEZA COMPLETA DO BANCO DE DADOS
 * ATENÇÃO: Este script apaga TODOS os dados das tabelas de registo. Use com cuidado.
 */

ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

// Conecta-se à base de dados
require_once __DIR__ . '/database/config.php';

if ($conn === false) {
    echo json_encode(["sucesso" => false, "mensagem" => "CRÍTICO: Não foi possível conectar ao banco de dados para a limpeza.", "detalhes" => sqlsrv_errors()]);
    exit;
}

// Lista de tabelas que contêm dados de utilizador e podem ser limpas com segurança.
// A tabela SGM_TAGStatus é excluída propositadamente para manter os status básicos do sistema.
$tabelas_para_limpar = [
    "SGM_Ativos",
    "SGM_OS_Atendimentos",
    "SGM_OS_Pecas",
    "SGM_OS_Status",
    "SGM_OS",
    "SGM_Melhorias",
    
  
];

$resultados = [];
$erros = 0;

// Desativa temporariamente as restrições de chave estrangeira para permitir o TRUNCATE
// (Nota: Esta abordagem pode variar dependendo das permissões. Se falhar, DELETE FROM é a alternativa)

foreach ($tabelas_para_limpar as $tabela) {
    // TRUNCATE TABLE é mais rápido que DELETE e reinicia os contadores de identidade (ID).
    $sql = "TRUNCATE TABLE dbo.{$tabela}";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt) {
        $resultados[] = "Tabela '{$tabela}' foi limpa com sucesso.";
    } else {
        // Se o TRUNCATE falhar (por exemplo, devido a permissões ou foreign keys ativas), tenta o DELETE.
        $sql_delete = "DELETE FROM dbo.{$tabela}";
        $stmt_delete = sqlsrv_query($conn, $sql_delete);
        if ($stmt_delete) {
            $resultados[] = "Tabela '{$tabela}' foi limpa com sucesso (usando DELETE).";
        } else {
            $resultados[] = "FALHA ao limpar a tabela '{$tabela}'. Detalhes do erro: " . print_r(sqlsrv_errors(), true);
            $erros++;
        }
    }
}

sqlsrv_close($conn);

if ($erros > 0) {
    echo json_encode([
        "sucesso" => false,
        "mensagem" => "O processo de limpeza terminou com $erros erros.",
        "log" => $resultados
    ]);
} else {
    echo json_encode([
        "sucesso" => true,
        "mensagem" => "Limpeza completa! Todas as tabelas de dados foram esvaziadas com sucesso. Pode apagar este ficheiro.",
        "log" => $resultados
    ]);
}

exit;
?>