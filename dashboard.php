<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div id="maquinaParadaAlert" class="blinking-alert" style="padding: 1rem; margin-bottom: 2rem; background-color: #f8d7da; color: #721c24; border-radius: 8px;"></div>
<div class="summary-grid">
    <div class="summary-card"><div class="summary-icon icon-total"><i class="fas fa-box"></i></div><div class="summary-content"><h3 id="totalAtivos">--</h3><p>Ativos Totais</p></div></div>
    <div class="summary-card"><div class="summary-icon icon-externo" style="background-color: #1cc88a"><i class="fas fa-sitemap"></i></div><div class="summary-content"><h3 id="totalSetores">--</h3><p>Setores</p></div></div>
    <div class="summary-card"><div class="summary-icon icon-manutencao" style="background-color: #f6c23e"><i class="fas fa-file-alt"></i></div><div class="summary-content"><h3 id="osAbertas">--</h3><p>OS em Aberto</p></div></div>
    <div class="summary-card"><div class="summary-icon icon-manutencao"><i class="fas fa-tools"></i></div><div class="summary-content"><h3 id="maquinasParadas">--</h3><p>Máquinas Paradas</p></div></div>
</div>
<div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 2rem;">
    <div class="card"><h2>OS por Status</h2><canvas id="statusChart"></canvas></div>
    <div class="card"><h2>Últimas OS Abertas</h2><table><thead><tr><th>OS</th><th>Ativo</th><th>Descrição</th><th>Data</th></tr></thead><tbody id="recentOsTable"></tbody></table></div>
</div>

<script>
$(document).ready(function() {
    $.post('dashboard_actions.php', function(data) {
        if (!data || data.erro) { return; }
        if (data.stats) {
            $('#totalAtivos').text(data.stats.total_ativos || 0);
            $('#totalSetores').text(data.stats.total_setores || 0);
            $('#osAbertas').text(data.stats.os_abertas || 0);
            $('#maquinasParadas').text(data.stats.maquinas_paradas || 0);
            if (parseInt(data.stats.maquinas_paradas) > 0) {
                const plural = data.stats.maquinas_paradas > 1 ? 's' : '';
                $('#maquinaParadaAlert').text(`AVISO: Existem ${data.stats.maquinas_paradas} máquina${plural} parada${plural}!`).show();
            }
        }
        if (data.os_status_breakdown && data.os_status_breakdown.length > 0) {
            const ctx = document.getElementById('statusChart').getContext('2d');
            const labels = data.os_status_breakdown.map(item => item.TAGStatus);
            const values = data.os_status_breakdown.map(item => item.total);
            new Chart(ctx, { type: 'doughnut', data: { labels: labels, datasets: [{ data: values, backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'] }] } });
        }
        const tableBody = $('#recentOsTable');
        tableBody.empty();
        if (data.recent_os && data.recent_os.length > 0) {
            data.recent_os.forEach(os => {
                const row = `<tr><td>${os.os_tag}</td><td>${os.ativo_tag}</td><td>${(os.descricao_problema || '').substring(0, 40)}...</td><td>${os.data_criacao}</td></tr>`;
                tableBody.append(row);
            });
        }
    }, 'json');
});
</script>