<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card">
    <form id="formTarefa">
        <input type="hidden" name="tarefa_codigo">
        <div class="form-group"><label>TAG da Tarefa</label><input type="text" name="tarefa_tag" required></div>
        <div class="form-group"><label>TAG do Ativo</label><input type="text" name="ativo_tag" required></div>
        <div class="form-group"><label>Última Execução</label><input type="date" name="ultima_execucao"></div>
        <div class="form-group" style="grid-column: 1 / -1;"><label>Descrição da Tarefa</label><textarea name="tarefa_descricao" required></textarea></div>
        <div style="grid-column: 1 / -1; border-top: 1px solid #eee; padding-top: 1rem;"><strong>Periodicidade</strong></div>
        <div class="form-group"><label>Horas</label><input type="number" name="Horas"></div>
        <div style="grid-column: span 3; display:flex; gap: 1.5rem; flex-wrap: wrap; align-items: center;">
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Semanal" value="1"> Semanal</label>
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Mensal" value="1"> Mensal</label>
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Bimestral" value="1"> Bimestral</label>
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Trimestral" value="1"> Trimestral</label>
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Semestral" value="1"> Semestral</label>
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Anual" value="1"> Anual</label>
            <label style="margin:0; font-weight: normal;"><input type="checkbox" name="Bianual" value="1"> Bianual</label>
        </div>
        <div class="button-container"><button type="submit">Salvar</button><button type="button" id="btnNovo" class="add-btn">Novo</button></div>
    </form>
</div>
<div class="card">
    <h2>Tarefas Cadastradas</h2>
    <table id="tarefasTable" style="width:100%"><thead><tr><th>TAG Tarefa</th><th>Descrição</th><th>Ativo</th><th>Ações</th></tr></thead><tbody></tbody></table>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarTarefas() {
    $.post('paginas/Tarefas/tarefas_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#tarefasTable')) { tabela = $('#tarefasTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(item => {
          const acoesHtml = `<button class="btn-editar">✏️</button> <button class="delete-btn btn-apagar" data-id="${item.tarefa_codigo}">🗑️</button>`;
          const linha = tabela.row.add([ item.tarefa_tag, item.tarefa_descricao, item.ativo_tag, acoesHtml]).draw().node();
          $(linha).data('tarefa', item);
        });
      }
    }, 'json');
  }

  $('#formTarefa').submit(function(e) { e.preventDefault(); $.ajax({ url: 'paginas/Tarefas/tarefas_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarTarefas(); limparFormulario(); } else { alert(res.mensagem || res.erro); } } }); });
  $('#tarefasTable tbody').on('click', '.btn-editar', function () {
    const tarefa = $(this).closest('tr').data('tarefa');
    if (tarefa) {
      $('input[name="tarefa_codigo"]').val(tarefa.tarefa_codigo);
      $('input[name="tarefa_tag"]').val(tarefa.tarefa_tag);
      $('input[name="ativo_tag"]').val(tarefa.ativo_tag);
      $('textarea[name="tarefa_descricao"]').val(tarefa.tarefa_descricao);
      $('input[name="ultima_execucao"]').val(tarefa.ultima_execucao ? tarefa.ultima_execucao.split(' ')[0] : '');
      $('input[name="Horas"]').val(tarefa.Horas);
      $('input[name="Semanal"]').prop('checked', tarefa.Semanal == 1);
      $('input[name="Mensal"]').prop('checked', tarefa.Mensal == 1);
      $('input[name="Bimestral"]').prop('checked', tarefa.Bimestral == 1);
      $('input[name="Trimestral"]').prop('checked', tarefa.Trimestral == 1);
      $('input[name="Semestral"]').prop('checked', tarefa.Semestral == 1);
      $('input[name="Anual"]').prop('checked', tarefa.Anual == 1);
      $('input[name="Bianual"]').prop('checked', tarefa.Bianual == 1);
      window.scrollTo(0, 0);
    }
  });
  $('#tarefasTable tbody').on('click', '.btn-apagar', function (e) {
    const id = $(this).data('id');
    if (confirm(`Deseja apagar esta tarefa?`)) {
      $.post('paginas/Tarefas/tarefas_actions.php', { action: 'delete', id: id }, function (res) { if(res.sucesso) carregarTarefas(); });
    }
  });
  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
    $('#formTarefa')[0].reset();
    $('input[name="tarefa_codigo"]').val('');
  }
  carregarTarefas();
});
</script>