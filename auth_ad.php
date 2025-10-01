<?php
session_start();
require_once __DIR__ . '/config.php';

// Se o utilizador já estiver na sessão, não faz nada
if (isset($_SESSION['usuario'])) {
    return;
}

// 1. OBTER O UTILIZADOR DO WINDOWS
// Formato esperado: DOMINIO\username
if (!isset($_SERVER['REMOTE_USER'])) {
    die("Erro: A Autenticação Integrada do Windows não está ativa ou configurada no servidor web (IIS). O utilizador não foi identificado.");
}
$user_parts = explode('\\', $_SERVER['REMOTE_USER']);
if (count($user_parts) !== 2) {
    die("Erro: Formato de utilizador inválido: " . htmlspecialchars($_SERVER['REMOTE_USER']));
}
$username = $user_parts[1];

// 2. CONECTAR AO ACTIVE DIRECTORY
$ad = ldap_connect($ad_config['domain_controllers'][0]);
if (!$ad) {
    die("Não foi possível conectar ao servidor LDAP.");
}

ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ad, LDAP_OPT_REFERRALS, 0);

// 3. AUTENTICAR (BIND) COM O UTILIZADOR DE SERVIÇO
$bind = @ldap_bind($ad, $ad_config['admin_user'], $ad_config['admin_pass']);
if (!$bind) {
    die("Não foi possível autenticar no AD com o utilizador de serviço. Verifique as credenciais.");
}

// 4. PROCURAR O UTILIZADOR NO AD
$filter = "(sAMAccountName=$username)";
$search = ldap_search($ad, $ad_config['base_dn'], $filter, ['displayname', 'memberof']);
$entries = ldap_get_entries($ad, $search);

if ($entries['count'] == 0) {
    die("O seu utilizador (".htmlspecialchars($username).") foi autenticado pelo Windows, mas não foi encontrado no Active Directory para verificação de grupos.");
}

// 5. EXTRAIR GRUPOS E MAPEÁ-LOS PARA PERMISSÕES
$user_permissions = [];
$user_display_name = $entries[0]['displayname'][0];
$user_groups = $entries[0]['memberof'];

foreach ($user_groups as $group_dn) {
    // Extrai o nome do grupo (CN) do DN completo
    preg_match('/CN=([^,]+)/', $group_dn, $matches);
    if (isset($matches[1])) {
        $group_name = $matches[1];
        // Verifica se o grupo do AD está mapeado nas configurações
        if (isset($ad_config['group_mapping'][$group_name])) {
            $user_permissions[] = $ad_config['group_mapping'][$group_name];
        }
    }
}

ldap_close($ad);

if (empty($user_permissions)) {
    die("Acesso negado. O seu utilizador não pertence a nenhum grupo com permissão para aceder a este sistema.");
}

// 6. GUARDAR INFORMAÇÕES NA SESSÃO
$_SESSION['usuario'] = [
    'username' => $username,
    'nome_completo' => $user_display_name,
    'permissoes' => $user_permissions
];

// Função auxiliar para verificar permissões
function has_permission($role) {
    if (isset($_SESSION['usuario']['permissoes'])) {
        // Administradores têm acesso a tudo
        if (in_array('administrador', $_SESSION['usuario']['permissoes'])) {
            return true;
        }
        return in_array($role, $_SESSION['usuario']['permissoes']);
    }
    return false;
}
?>