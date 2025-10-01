<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.css"/>

<div class="card">
  <form id="formRelatorio">
    <div class="form-group">
      <label for="tipo_relatorio">Selecione o Tipo de Relatório</label>
      <select id="tipo_relatorio" name="tipo_relatorio">
        <option value="">Selecione...</option>
        <optgroup label="Cadastros Gerais">
            <option value="ativos_completo">Lista Completa de Ativos</option>
            <option value="usuarios">Lista de Usuários</option>
            <option value="setores">Lista de Setores</option>
            <option value="tarefas">Lista de Tarefas</option>
        </optgroup>
        <optgroup label="Movimentações">
             <option value="os_completo">Histórico Completo de OS</option>
             <option value="os_audit">Auditoria de Alterações de Status (OS)</option>
        </optgroup>
      </select>
    </div>
    <div id="opcoes_os_historico" style="display: none; grid-column: span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
       <div class="form-group"><label for="os_data_inicio">Data Início</label><input type="date" id="os_data_inicio" name="os_data_inicio"></div>
       <div class="form-group"><label for="os_data_fim">Data Fim</label><input type="date" id="os_data_fim" name="os_data_fim"></div>
    </div>
    <div class="button-container"><button type="submit">Gerar Relatório</button></div>
  </form>
</div>
<div class="card">
  <h2>Resultado</h2>
  <div id="resultadoRelatorio">
    <table id="tabelaRelatorio" style="width:100%"></table>
  </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.js"></script>
<script>
$(document).ready(function() {
    $('#tipo_relatorio').on('change', function() {
        const showDateFilter = $(this).val() === 'os_completo' || $(this).val() === 'os_audit';
        $('#opcoes_os_historico').css('display', showDateFilter ? 'grid' : 'none');
    });
    let dataTable;
    $('#formRelatorio').on('submit', function(e) {
        e.preventDefault();
        const payload = $(this).serialize() + '&action=generate';
        $.post('paginas/Relatorios/relatorios_actions.php', payload, function(res) {
            if ($.fn.DataTable.isDataTable('#tabelaRelatorio')) { dataTable.destroy(); }
            $('#resultadoRelatorio').html('<table id="tabelaRelatorio" style="width:100%"></table>');

            if (res.erro) { 
                $('#resultadoRelatorio').html(`<div style="color:red; padding: 1rem;">Erro: ${res.erro}</div>`);
                return; 
            }
            if (res.dados && res.dados.length > 0) {
                const headers = Object.keys(res.dados[0]);
                let headerHtml = '<thead><tr>' + headers.map(h => `<th>${h.replace(/_/g, ' ').toUpperCase()}</th>`).join('') + '</tr></thead>';
                $('#tabelaRelatorio').html(headerHtml);

                dataTable = $('#tabelaRelatorio').DataTable({
                    data: res.dados.map(item => Object.values(item)),
                    dom: 'Bfrtip', 
                    buttons: [ 'csv', { extend: 'excel', text: 'Salvar em XLSX' }, 'pdf', 'print' ]
                });
            } else {
                $('#resultadoRelatorio').html('<div style="padding: 1rem;">Nenhum dado encontrado.</div>');
            }
        }, 'json');
    });
});
</script>