<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Sistema de Gestão</title>
  <link rel="stylesheet" href="./css/style.css">
</head>
<body>

<header>
  <h1>Sistema de Gestão</h1>
</header>
<nav id="main-nav">
  <a href="#" class="nav-link active" data-page="dashboard" data-title="Dashboard Principal">Dashboard</a>
  <a href="#" class="nav-link" data-page="paginas/OS/ordem_servico" data-title="Ordens de Serviço">Ordens de Serviço</a>
  <a href="#" class="nav-link" data-page="paginas/Relatorios/relatorios" data-title="Relatórios">Relatórios</a>
  <a href="#" class="nav-link" data-page="paginas/Ativos/ativos" data-title="Cadastro de Ativos">Ativos</a>
  <a href="#" class="nav-link" data-page="paginas/Setores/setores" data-title="Cadastro de Setores">Setores</a>
  <a href="#" class="nav-link" data-page="paginas/Usuarios/usuarios" data-title="Cadastro de Usuários">Usuários</a>
  <a href="#" class="nav-link" data-page="paginas/Tarefas/tarefas" data-title="Cadastro de Tarefas">Tarefas</a>
  <a href="#" class="nav-link" data-page="paginas/status/status" data-title="Cadastro de Status de OS">Status de OS</a>
</nav>

<main id="content-area">
  </main>

<footer>
  © 2025 - Todos os direitos reservados.
</footer>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script>
function carregarConteudo(pagina, titulo) {
    const contentArea = $('#content-area');
    contentArea.html(`<h2>${titulo}</h2>`); // Adiciona o título

    $.get(pagina + '.php', function(data) {
        contentArea.append(data); // Adiciona o conteúdo da página
    }).fail(function() {
        contentArea.html(`<h2 style="color: red;">Erro ao carregar a página: ${pagina}.php</h2>`);
    });
}

$(document).ready(function() {
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        $('.nav-link').removeClass('active');
        $(this).addClass('active');
        
        const pagina = $(this).data('page');
        const titulo = $(this).data('title');
        carregarConteudo(pagina, titulo);
    });

    carregarConteudo('dashboard', 'Dashboard Principal');
});
</script>

</body>
</html>