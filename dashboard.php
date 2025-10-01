<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .card-metric {
        border-left-width: 4px;
        border-radius: .35rem;
    }
    .border-left-primary { border-left-color: #4e73df !important; }
    .border-left-success { border-left-color: #1cc88a !important; }
    .border-left-warning { border-left-color: #f6c23e !important; }
    .border-left-danger  { border-left-color: #e74a3b !important; }
  </style>
</head>
<body class="bg-light">
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-metric border-left-primary shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Ativos Totais</div>
                    <div class="h5 mb-0 fw-bold text-gray-800" id="totalAtivos">--</div>
                </div></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-metric border-left-success shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Setores</div>
                    <div class="h5 mb-0 fw-bold text-gray-800" id="totalSetores">--</div>
                </div></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-metric border-left-warning shadow h-100 py-2">
                <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">OS em Aberto</div>
                    <div class="h5 mb-0 fw-bold text-gray-800" id="osAbertas">--</div>
                </div></div></div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card card-metric border-left-danger shadow h-100 py-2">
                 <div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2">
                    <div class="text-xs fw-bold text-danger text-uppercase mb-1">Máquinas Paradas</div>
                    <div class="h5 mb-0 fw-bold text-gray-800" id="maquinasParadas">--</div>
                </div></div></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-5 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Ordens de Serviço por Status</h6></div>
                <div class="card-body"><canvas id="statusChart"></canvas></div>
            </div>
        </div>
        <div class="col-xl-7 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3"><h6 class="m-0 fw-bold text-primary">Últimas OS Abertas</h6></div>
                <div class="card-body"><div class="table-responsive">
                    <table class="table table-hover">
                        <thead><tr><th>OS</th><th>Ativo</th><th>Descrição</th><th>Data</th></tr></thead>
                        <tbody id="recentOsTable"><tr><td colspan="4" class="text-center">A carregar...</td></tr></tbody>
                    </table>
                </div></div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script>
$(document).ready(function() {
    $.post('dashboard_actions.php', function(data) {
        // --- INÍCIO DA DEPURAÇÃO ---
        console.log("Dados recebidos do servidor:", data);
        // --- FIM DA DEPURAÇÃO ---

        if (data && data.stats) {
            // Preenche as métricas principais
            $('#totalAtivos').text(data.stats.total_ativos || 0);
            $('#totalSetores').text(data.stats.total_setores || 0);
            $('#osAbertas').text(data.stats.os_abertas || 0);
            $('#maquinasParadas').text(data.stats.maquinas_paradas || 0);
        } else {
             console.error("Objeto 'stats' não encontrado na resposta.");
        }

        // Cria o gráfico de status
        if (data && data.os_status_breakdown && data.os_status_breakdown.length > 0) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            const labels = data.os_status_breakdown.map(item => item.Status);
            const values = data.os_status_breakdown.map(item => item.total);
            new Chart(ctx, {
                type: 'doughnut',
                data: { labels: labels, datasets: [{ data: values, backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'] }] },
                options: { responsive: true, maintainAspectRatio: false }
            });
        } else {
            console.log("Não há dados para o gráfico de status.");
        }
        
        // Preenche a tabela de OS recentes
        const tableBody = $('#recentOsTable');
        tableBody.empty();
        if (data && data.recent_os && data.recent_os.length > 0) {
            data.recent_os.forEach(os => {
                const row = `<tr><td>${os.OS_ID}</td><td>${os.Ativo_TAG}</td><td>${(os.Descricao_Servico || '').substring(0, 40)}...</td><td>${os.Data_Solicitacao}</td></tr>`;
                tableBody.append(row);
            });
        } else {
            tableBody.append('<tr><td colspan="4" class="text-center">Nenhuma OS recente encontrada.</td></tr>');
        }

    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error("Falha na requisição AJAX:", textStatus, errorThrown);
        $('body').prepend(`<div class="alert alert-danger">Falha grave na comunicação com o servidor. Verifique o console do navegador para mais detalhes.</div>`);
    });
});
</script>
</body>
</html>