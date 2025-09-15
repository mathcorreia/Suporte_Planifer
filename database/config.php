<?php


$serverName = "172.20.1.7"; 
$connectionOptions = [
    "Database" => "manutencao", 
    "Uid" => "root",                     
    "PWD" => "",              
    "Encrypt" => true,
    "TrustServerCertificate" => true
];


$conn = sqlsrv_connect($serverName, $connectionOptions);


if (!$conn) {
   
    header('Content-Type: application/json');
    echo json_encode(["erro" => "Falha na conexão com o banco de dados.", "details" => sqlsrv_errors()]);
    exit; // Impede que o resto do script seja executado
}
?>