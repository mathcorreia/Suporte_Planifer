<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.css"/>
<link rel="stylesheet" href="../../css/style.css"> 

<style>
    /* --- CORREÇÕES DE ESTILO PARA A TABELA DE RELATÓRIOS --- */
    #resultadoRelatorio {
        overflow-x: auto; /* Garante a barra de rolagem horizontal quando a tabela é larga */
    }
    /* Organiza os controlos da tabela (botões, pesquisa) */
    .dt-container {
        padding-bottom: 1rem;
    }
    .dt-layout-row.dt-layout-table {
        overflow-x: auto;
    }
</style>

<div class="card">
  <form id="formRelatorio">
    <div class="form-group">
      <label for="tipo_relatorio">Selecione o Tipo de Relatório</label>
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
    <div id="opcoes_os_historico" style="display: none; grid-column: span 2; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
       <div class="form-group"><label for="os_data_inicio">Data Início</label><input type="date" id="os_data_inicio" name="os_data_inicio" class="form-control"></div>
       <div class="form-group"><label for="os_data_fim">Data Fim</label><input type="date" id="os_data_fim" name="os_data_fim" class="form-control"></div>
    </div>
    <div class="button-container"><button type="submit" class="btn btn-primary">Gerar Relatório</button></div>
  </form>
</div>
<div class="card">
  <h2>Resultado</h2>
  <div id="resultadoRelatorio">
    <table id="tabelaRelatorio" class="table table-striped table-bordered" style="width:100%"></table>
  </div>
</div>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/v/bs5/dt-1.13.6/b-2.4.2/b-html5-2.4.2/b-print-2.4.2/datatables.min.js"></script>
<script>
// Usar uma função anónima para evitar conflitos com o index.php
(function($) {
    let dataTable;

    $('#tipo_relatorio').on('change', function() {
        const showDateFilter = $(this).val() === 'os_completo' || $(this).val() === 'os_audit';
        $('#opcoes_os_historico').css('display', showDateFilter ? 'grid' : 'none');
    });

    $('#formRelatorio').on('submit', function(e) {
        e.preventDefault();
        const payload = $(this).serialize() + '&action=generate';
        const resultadoDiv = $('#resultadoRelatorio');

        resultadoDiv.html('<div class="text-center p-5"><div class="spinner-border text-primary"></div><p class="mt-2">A gerar relatório...</p></div>');

        $.post('paginas/Relatorios/relatorios_actions.php', payload, function(res) {
            if ($.fn.DataTable.isDataTable('#tabelaRelatorio')) { 
                dataTable.destroy(); 
            }
            resultadoDiv.empty();

            if (res.erro) { 
                resultadoDiv.html(`<div class="alert alert-danger">${res.erro}</div>`);
                return; 
            }

            if (res.dados && res.dados.length > 0) {
                const headers = Object.keys(res.dados[0]);
                const headerHtml = '<thead><tr>' + headers.map(h => `<th>${h.replace(/_/g, ' ').toUpperCase()}</th>`).join('') + '</tr></thead>';
                
                const table = $('<table id="tabelaRelatorio" class="table table-striped table-bordered" style="width:100%"></table>');
                table.html(headerHtml);
                resultadoDiv.append(table);

                dataTable = $('#tabelaRelatorio').DataTable({
                    data: res.dados,
                    columns: headers.map(h => ({ data: h })),
                    // --- DOM ESTRUTURADO CORRETAMENTE PARA BOOTSTRAP 5 ---
                    dom:  "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>" + 
                          "<'row'<'col-sm-12'B>>" + // Botões em uma nova linha no topo
                          "<'row'<'col-sm-12'tr>>" + 
                          "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                    buttons: [ 'csv', { extend: 'excel', text: 'Salvar em XLSX' }, 'pdf', 'print' ],
                    responsive: true,
                    language: {
                        search: "Pesquisar:",
                        lengthMenu: "Mostrar _MENU_ registos",
                        info: "Mostrando de _START_ a _END_ de _TOTAL_ registos",
                        paginate: {
                            first: "Primeiro",
                            last: "Último",
                            next: "Seguinte",
                            previous: "Anterior"
                        }
                    }
                });
            } else {
                resultadoDiv.html('<div class="alert alert-info">Nenhum dado encontrado para os critérios selecionados.</div>');
            }
        }, 'json').fail(function() {
            resultadoDiv.html('<div class="alert alert-danger">Erro de comunicação com o servidor.</div>');
        });
    });
})(jQuery);
</script>