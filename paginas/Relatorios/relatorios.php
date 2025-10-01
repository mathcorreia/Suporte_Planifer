<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Relatórios do Sistema</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.css"/>
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Central de Relatórios</h2>
  <div class="card mb-4">
    <div class="card-header">Filtros e Opções</div>
    <div class="card-body">
      <form id="formRelatorio" class="row g-3">
        <div class="col-md-12">
          <label for="tipo_relatorio" class="form-label">Selecione o Tipo de Relatório</label>
          <select id="tipo_relatorio" name="tipo_relatorio" class="form-select">
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
        <div id="opcoes_os_historico" class="row g-3 mt-2" style="display: none;">
           <div class="col-md-4"><label for="os_data_inicio" class="form-label">Data Início</label><input type="date" id="os_data_inicio" name="os_data_inicio" class="form-control"></div>
           <div class="col-md-4"><label for="os_data_fim" class="form-label">Data Fim</label><input type="date" id="os_data_fim" name="os_data_fim" class="form-control"></div>
        </div>
        <div class="col-12"><button type="submit" class="btn btn-primary">Gerar Relatório</button></div>
      </form>
    </div>
  </div>
  <div class="card">
    <div class="card-header">Resultado</div>
    <div class="card-body"><div id="resultadoRelatorio" class="table-responsive"><table id="tabelaRelatorio" class="table table-striped table-bordered" style="width:100%"></table></div></div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.js"></script>
<script>
$(document).ready(function() {
    // ATUALIZADO: Mostra o filtro de data para ambos os relatórios de OS
    $('#tipo_relatorio').on('change', function() {
        const showDateFilter = $(this).val() === 'os_completo' || $(this).val() === 'os_audit';
        $('#opcoes_os_historico').toggle(showDateFilter);
    });
    let dataTable;
    $('#formRelatorio').on('submit', function(e) {
        e.preventDefault();
        const payload = $(this).serialize() + '&action=generate';
        $.post('relatorios_actions.php', payload, function(res) {
            if ($.fn.DataTable.isDataTable('#tabelaRelatorio')) { dataTable.destroy(); $('#tabelaRelatorio').empty(); }
            
            $('#resultadoRelatorio').html('<table id="tabelaRelatorio" class="table table-striped table-bordered" style="width:100%"></table>');

            if (res.erro) { 
                alert("Erro: " + res.erro); 
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
                $('#resultadoRelatorio').html('<div class="alert alert-warning">Nenhum dado encontrado.</div>');
            }
        }, 'json');
    });
});
</script>
</body>
</html>