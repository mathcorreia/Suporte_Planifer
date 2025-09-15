<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <title>Relatórios do Sistema</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.css"/>
  <style>
    .filter-options { display: none; }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Central de Relatórios</h2>

  <div class="card mb-4">
    <div class="card-header">Filtros e Opções de Relatório</div>
    <div class="card-body">
      <form id="formRelatorio" class="row g-3">
        <div class="col-md-12">
          <label for="tipo_relatorio" class="form-label">Selecione o Tipo de Relatório</label>
          <select id="tipo_relatorio" name="tipo_relatorio" class="form-select">
            <option value="">Selecione...</option>
            <option value="ativos">Lista de Ativos</option>
            <option value="os_historico">Histórico de Ordem de Serviço</option>
          </select>
        </div>

        <div id="opcoes_ativos" class="filter-options row g-3 mt-2">
          <div class="col-12">
            <h6>Selecione as colunas para o relatório de ativos:</h6>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="colunas[]" value="ativo_tag" id="col_ativo_tag" checked>
              <label class="form-check-label" for="col_ativo_tag">TAG Ativo</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="checkbox" name="colunas[]" value="descricao" id="col_descricao" checked>
              <label class="form-check-label" for="col_descricao">Descrição</label>
            </div>
          </div>
        </div>

        <div id="opcoes_os_historico" class="filter-options row g-3 mt-2">
           <div class="col-md-4">
             <label for="os_data_inicio" class="form-label">Data Início</label>
             <input type="date" id="os_data_inicio" name="os_data_inicio" class="form-control">
           </div>
           <div class="col-md-4">
             <label for="os_data_fim" class="form-label">Data Fim</label>
             <input type="date" id="os_data_fim" name="os_data_fim" class="form-control">
           </div>
        </div>
        
        <div class="col-12">
          <button type="submit" class="btn btn-primary" id="btnGerarRelatorio">Gerar Relatório</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-header">Resultado</div>
    <div class="card-body">
       <div id="resultadoRelatorio" class="table-responsive">
          <table id="tabelaRelatorio" class="table table-striped table-bordered" style="width:100%"></table>
       </div>
    </div>
  </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.js"></script>

<script>
$(document).ready(function() {
    $('#tipo_relatorio').on('change', function() {
        $('.filter-options').hide();
        const selectedOption = $(this).val();
        if (selectedOption) {
            $('#opcoes_' + selectedOption).show();
        }
    });

    let dataTable;

    $('#formRelatorio').on('submit', function(e) {
        e.preventDefault();
        const payload = $(this).serialize() + '&action=generate';
        
        $.post('relatorios_actions.php', payload, function(res) {
            if ($.fn.DataTable.isDataTable('#tabelaRelatorio')) {
                dataTable.destroy();
                $('#tabelaRelatorio').empty();
            }

            if (res.erro) {
                alert("Erro ao gerar relatório: " + res.erro);
                return;
            }

            if (res.dados && res.dados.length > 0) {
                const headers = Object.keys(res.dados[0]);
                let headerHtml = '<thead><tr>';
                headers.forEach(h => headerHtml += `<th>${h.replace(/_/g, ' ').toUpperCase()}</th>`);
                headerHtml += '</tr></thead>';
                
                const dataSet = res.dados.map(item => Object.values(item));

                $('#tabelaRelatorio').html(headerHtml);
                
                dataTable = $('#tabelaRelatorio').DataTable({
                    data: dataSet,
                    responsive: true,
                    dom: 'Bfrtip',
                    buttons: ['csv', 'excel', 'pdf', 'print'],
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' }
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