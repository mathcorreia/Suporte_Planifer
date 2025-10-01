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
    body {
      background-color: #f8f9fa;
    }
    .sidebar {
      height: 100vh;
      position: fixed;
      top: 0;
      left: 0;
      width: 240px;
      background-color: #343a40;
      color: white;
      padding-top: 1rem;
    }
    .sidebar h4 {
      text-align: center;
      margin-bottom: 1rem;
    }
    .sidebar a {
      color: #ccc;
      text-decoration: none;
      padding: 10px 20px;
      display: block;
      cursor: pointer;
    }
    .sidebar a:hover,
    .sidebar a.active {
      background-color: #495057;
      color: white;
    }
    .main-content {
      margin-left: 240px;
      padding: 2rem;
    }
    iframe {
      border: none;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      width: 100%;
      height: 900px;
      display: none;
    }
    @media (max-width: 768px) {
      .sidebar {
        position: relative;
        width: 100%;
        height: auto;
      }
      .main-content {
        margin-left: 0;
      }
    }
  </style>
</head>
<body>

<!-- Menu lateral -->
<div class="sidebar">
  <h4>Menu</h4>
  <a onclick="carregarModulo('usuarios')" id="menuUsuarios">Usuários</a>
  <a onclick="carregarModulo('ativos')" id="menuAtivos">Ativos</a>
  <a onclick="carregarModulo('clientes')" id="menuClientes">Clientes</a>
  <a onclick="carregarModulo('relatorios')" id="menuRelatorios">Relatórios</a>
</div>

<!-- Conteúdo principal -->
<div class="main-content">
  <h2 id="tituloModulo" class="mb-4">Selecione um módulo</h2>
  <iframe id="iframeModulo"></iframe>
</div>

<script>
function carregarModulo(modulo) {
  const titulos = {
    usuarios: "Gestão de Usuários",
    ativos: "Cadastro de Ativos",
    clientes: "Gestão de Clientes",
    relatorios: "Relatórios do Sistema"
  };

  document.getElementById('tituloModulo').innerText = titulos[modulo] || "Módulo";

  const iframe = document.getElementById('iframeModulo');
  iframe.src = modulo + ".php?nocache=" + Date.now();
  iframe.style.display = 'block';

  document.querySelectorAll('.sidebar a').forEach(el => el.classList.remove('active'));
  document.getElementById('menu' + capitalize(modulo)).classList.add('active');
}

function capitalize(str) {
  return str.charAt(0).toUpperCase() + str.slice(1);
}
</script>

</body>
</html>