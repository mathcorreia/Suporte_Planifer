<?php
// Habilita a exibição de erros para depuração
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json');

// Inclui o arquivo de configuração e conexão com o banco de dados
require_once __DIR__ . '/../../database/config.php';

// Verifica se a conexão foi bem-sucedida
if ($conn === false) {
    echo json_encode(["sucesso" => false, "mensagem" => "Falha na conexão com o banco de dados."]);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch ($action) {

    case 'get_open':
        $sql = "SELECT melhoria_id AS id, titulo, status, prioridade, solicitante, data_criacao 
                FROM SGM_Melhorias 
                WHERE status NOT IN ('Implementada', 'Rejeitada') 
                ORDER BY CASE prioridade WHEN 'Alta' THEN 1 WHEN 'Media' THEN 2 ELSE 3 END, data_criacao ASC";
        
        $stmt = sqlsrv_query($conn, $sql);
        $data = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if ($row['data_criacao'] instanceof DateTime) {
                    $row['data_criacao'] = $row['data_criacao']->format('d/m/Y');
                }
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'get_all':
        $sql = "SELECT melhoria_id AS id, titulo, status, prioridade, solicitante, data_criacao 
                FROM SGM_Melhorias ORDER BY data_criacao DESC";
        $stmt = sqlsrv_query($conn, $sql);
        $data = [];
        if ($stmt) {
            while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
                if ($row['data_criacao'] instanceof DateTime) {
                    $row['data_criacao'] = $row['data_criacao']->format('d/m/Y');
                }
                $data[] = $row;
            }
        }
        echo json_encode($data);
        break;

    case 'get_details':
        $id = $_GET['id'] ?? 0;
        $sql = "SELECT * FROM SGM_Melhorias WHERE melhoria_id = ?";
        $stmt = sqlsrv_query($conn, $sql, [$id]);
        $data = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
        echo json_encode($data);
        break;

    case 'save':
        $id = $_POST['melhoria_id'] ?? null;
        
        // Se um ID foi enviado, a ação é de ATUALIZAÇÃO (APENAS STATUS)
        if (!empty($id)) {
            $status = $_POST['status'] ?? null;
            if (empty($status)) {
                echo json_encode(["sucesso" => false, "mensagem" => "O status é obrigatório para atualização."]);
                exit;
            }
            $sql = "UPDATE SGM_Melhorias SET 
                        status = ?, 
                        data_atualizacao = GETDATE() 
                    WHERE melhoria_id = ?";
            $params = [$status, $id];
            $mensagem = "Status da solicitação atualizado com sucesso!";
        } 
        // Se não há ID, a ação é de CRIAÇÃO (salva todos os campos)
        else {
            $titulo = $_POST['titulo'] ?? null;
            $descricao = $_POST['descricao_melhoria'] ?? null;
            $area = $_POST['area_afetada'] ?? null;
            $solicitante = $_POST['solicitante'] ?? null;
            $tipo = $_POST['tipo_melhoria'] ?? null;
            $prioridade = $_POST['prioridade'] ?? null;

            $sql = "INSERT INTO SGM_Melhorias 
                        (titulo, descricao_melhoria, area_afetada, solicitante, tipo_melhoria, prioridade) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $params = [$titulo, $descricao, $area, $solicitante, $tipo, $prioridade];
            $mensagem = "Solicitação de melhoria criada com sucesso!";
        }
        
        $stmt = sqlsrv_query($conn, $sql, $params);
        
        if ($stmt) {
            echo json_encode(["sucesso" => true, "mensagem" => $mensagem]);
        } else {
            echo json_encode(["sucesso" => false, "mensagem" => "Falha ao salvar a solicitação.", "detalhes" => sqlsrv_errors()]);
        }
        break;

    default:
        echo json_encode(["sucesso" => false, "mensagem" => "Ação desconhecida."]);
        break;
}

sqlsrv_close($conn);
?>