<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Gestão</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .sidebar { height: 100vh; position: fixed; top: 0; left: 0; width: 240px; background-color: #343a40; color: white; padding-top: 1rem; }
    .sidebar-header { text-align: center; margin-bottom: 1rem; }
    .sidebar a { color: #ccc; text-decoration: none; padding: 10px 20px; display: block; cursor: pointer; }
    .sidebar a:hover, .sidebar a.active { background-color: #495057; color: white; }
    .main-content { margin-left: 240px; padding: 2rem; }
    iframe { border: none; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 100%; height: 90vh; display: none; }
  </style>
</head>
<body>

<div class="sidebar">
  <div class="sidebar-header">
    <h4>Menu Principal</h4>
  </div>
  <a onclick="carregarModulo('Dashboard')" id="menuDashboard">Dashboard</a>
  <a onclick="carregarModulo('OS')" id="menuOS">Ordens de Serviço</a>
  <a onclick="carregarModulo('Relatorios')" id="menuRelatorios">Relatórios</a>
  <hr style="border-color: #6c757d;">
  <small style="padding: 10px 20px; color: #6c757d; text-transform: uppercase;">Cadastros</small>
  <a onclick="carregarModulo('Ativos')" id="menuAtivos">Ativos</a>
  <a onclick="carregarModulo('Setores')" id="menuSetores">Setores</a>
  <a onclick="carregarModulo('Usuarios')" id="menuUsuarios">Usuários</a>
  <a onclick="carregarModulo('Tarefas')" id="menuTarefas">Tarefas</a>
</div>

<div class="main-content">
  <h2 id="tituloModulo" class="mb-4">Bem-vindo</h2>
  <iframe id="iframeModulo"></iframe>
</div>

<script>
function carregarModulo(modulo) {
  const modulosInfo = {
    Dashboard: { titulo: "Dashboard Principal", path: "dashboard.php" },
    OS: { titulo: "Ordens de Serviço de Suporte", path: "paginas/OS/ordem_servico.php" },
    Relatorios: { titulo: "Relatórios do Sistema", path: "paginas/Relatorios/relatorios.php" },
    Ativos: { titulo: "Cadastro de Ativos", path: "paginas/Ativos/ativos.php" },
    Setores: { titulo: "Cadastro de Setores", path: "paginas/Setores/setores.php" },
    Usuarios: { titulo: "Gestão de Usuários", path: "paginas/Usuarios/usuarios.php" },
    Tarefas: { titulo: "Cadastro de Tarefas", path: "paginas/Tarefas/tarefas.php" }
  };

  const info = modulosInfo[modulo];
  if (!info) return;

  document.getElementById('tituloModulo').innerText = info.titulo;
  const iframe = document.getElementById('iframeModulo');
  iframe.src = info.path + "?nocache=" + Date.now();
  iframe.style.display = 'block';

  document.querySelectorAll('.sidebar a').forEach(el => el.classList.remove('active'));
  document.getElementById('menu' + modulo)?.classList.add('active');
}

document.addEventListener("DOMContentLoaded", function() {
    carregarModulo('Dashboard');
});
</script>

</body>
</html>