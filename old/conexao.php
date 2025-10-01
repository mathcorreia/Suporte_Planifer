<?php
$serverName = "172.20.1.7";
$connectionOptions = [
    "Database" => "Sistemas_Auxiliares",
    "Uid" => "sgq",
    "PWD" => "sgq01-2025",
    "Encrypt" => true,
    "TrustServerCertificate" => true
];

$conn = sqlsrv_connect($serverName, $connectionOptions);

if (!$conn) {
    die("❌ Erro na conexão:<br>" . print_r(sqlsrv_errors(), true));
}

?>