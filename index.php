<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Gestão</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .sidebar { height: 100vh; position: fixed; top: 0; left: 0; width: 240px; background-color: #343a40; color: white; padding-top: 1rem; }
    .sidebar h4 { text-align: center; margin-bottom: 1rem; }
    .sidebar a { color: #ccc; text-decoration: none; padding: 10px 20px; display: block; cursor: pointer; }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
    .main-content { margin-left: 240px; padding: 2rem; }
    iframe { border: none; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; height: 900px; display: none; }
    @media (max-width: 768px) {
      .sidebar { position: relative; width: 100%; height: auto; }
      .main-content { margin-left: 0; }
    }
  </style>
</head>
<body>

<!-- Menu lateral com os links para os módulos -->
<div class="sidebar">
  <h4>Menu</h4>
  <!-- Módulos principais -->
  <a onclick="carregarModulo('OS')" id="menuOS">Ordens de Serviço</a>
  <a onclick="carregarModulo('Relatorios')" id="menuRelatorios">Relatórios</a>
  <hr style="border-color: #6c757d;">
  <!-- Seção de Cadastros -->
  <small style="padding: 10px 20px; color: #6c757d; text-transform: uppercase;">Cadastros</small>
  <a onclick="carregarModulo('Usuarios')" id="menuUsuarios">Usuários</a>
  <a onclick="carregarModulo('Ativos')" id="menuAtivos">Ativos</a>
  <a onclick="carregarModulo('Tarefas')" id="menuTarefas">Tarefas</a>
</div>

<!-- Área principal onde o conteúdo dos módulos será exibido -->
<div class="main-content">
  <h2 id="tituloModulo" class="mb-4">Bem-vindo</h2>
  <iframe id="iframeModulo"></iframe>
</div>

<script>
function carregarModulo(modulo) {
  // Mapeamento dos módulos para seus respectivos títulos e caminhos
  const modulosInfo = {
    Usuarios: { titulo: "Gestão de Usuários", path: "paginas/Usuarios/usuarios.php" },
    Ativos: { titulo: "Cadastro de Ativos", path: "paginas/Ativos/ativos.php" },
    Tarefas: { titulo: "Cadastro de Tarefas", path: "paginas/Tarefas/tarefas.php" },
    OS: { titulo: "Ordens de Serviço de Suporte", path: "paginas/OS/ordem_servico.php" },
    Relatorios: { titulo: "Relatórios do Sistema", path: "paginas/Relatorios/relatorios.php" }
  };

  const info = modulosInfo[modulo];
  if (!info) {
    console.error("Módulo desconhecido:", modulo);
    return;
  }

  // Atualiza o título da página principal
  document.getElementById('tituloModulo').innerText = info.titulo;

  // Define o caminho do arquivo a ser carregado no iframe
  const iframe = document.getElementById('iframeModulo');
  iframe.src = info.path + "?nocache=" + Date.now();
  iframe.style.display = 'block';

  // Atualiza a classe 'active' no menu para destacar o item selecionado
  document.querySelectorAll('.sidebar a').forEach(el => el.classList.remove('active'));
  const menuId = 'menu' + modulo;
  if (document.getElementById(menuId)) {
    document.getElementById(menuId).classList.add('active');
  }
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}

// Carrega um módulo padrão (Ordens de Serviço) ao iniciar a página
document.addEventListener("DOMContentLoaded", function() {
    carregarModulo('OS');
});
</script>

</body>
</html>

