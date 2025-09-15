<?php
// Configurações do Banco de Dados (existentes)
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
    exit;
}

// --- NOVAS CONFIGURAÇÕES DO ACTIVE DIRECTORY ---
$ad_config = [
    'domain_controllers' => ['SEU_DC.seu_dominio.local'], // Substitua pelo endereço do seu Domain Controller
    'base_dn' => 'DC=seu_dominio,DC=local', // Substitua pelo Base DN do seu domínio
    'admin_user' => 'utilizador_servico@seu_dominio.local', // Utilizador de serviço para a consulta
    'admin_pass' => 'senha_do_utilizador_servico', // Senha do utilizador de serviço
    'group_mapping' => [
        // Mapeia o NOME COMPLETO do grupo no AD para uma permissão interna
        'G_SGM_Administradores' => 'administrador',
        'G_SGM_Planejadores' => 'planejador',
        'G_SGM_Tecnicos' => 'tecnico',
        'G_SGM_Clientes' => 'cliente'
    ]
];
?>