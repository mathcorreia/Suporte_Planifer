<?php
session_start();
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if (!$conn) {
    echo json_encode(['erro' => 'Falha na conexão com a base de dados.']);
    exit;
}

$codigo = $_POST['codigo'] ?? 0;
$senha = $_POST['senha'] ?? '';

if (empty($codigo) || empty($senha)) {
    echo json_encode(['erro' => 'Código e senha são obrigatórios.']);
    exit;
}

$sql = "SELECT codigo, nome, senha, administrador FROM SGM_Usuarios WHERE codigo = ? AND ativo = 1";
$params = [$codigo];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($stmt && sqlsrv_has_rows($stmt)) {
    $usuario = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
    // A função password_verify precisa ser implementada ou a senha guardada em texto plano (não recomendado)
    // Para este exemplo, vamos comparar a senha diretamente
    if ($senha === $usuario['senha']) { // Em um ambiente de produção, use password_verify($senha, $usuario['senha'])
        $_SESSION['usuario_codigo'] = $usuario['codigo'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['is_admin'] = (bool)$usuario['administrador'];
        echo json_encode(['sucesso' => true]);
    } else {
        echo json_encode(['erro' => 'Código ou senha inválidos.']);
    }
} else {
    echo json_encode(['erro' => 'Código ou senha inválidos.']);
}
?>