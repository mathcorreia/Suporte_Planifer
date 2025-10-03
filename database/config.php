<?php
// Configurações do Banco de Dados 
$serverName = "172.20.1.7";
$connectionOptions = [
    "Database" => "SISTEMAS_AUXILIARES",
    "Uid" => "sgq",
    "PWD" => "sgq01-2025",
        "Encrypt" => false,
        "TrustServerCertificate" => true 
];

$conn = sqlsrv_connect($serverName, $connectionOptions);


if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode([
        "erro" => "CRÍTICO: Falha na conexão com o banco de dados 'Sistemas_Auxiliares'. Verifique as configurações.",
        "details" => sqlsrv_errors()
    ]);
    exit;
}
?>