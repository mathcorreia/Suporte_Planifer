<?php
include 'conexao.php';
$result = sqlsrv_query($conn, "SELECT * FROM SGM_Usuarios ORDER BY Nome");
$usuarios = [];
while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
    $usuarios[] = $row;
}
header('Content-Type: application/json');
echo json_encode($usuarios);