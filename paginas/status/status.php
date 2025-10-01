<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card">
    <form id="formStatus">
        <input type="hidden" name="TAGStatusID">
        <div class="form-group" style="grid-column: 1 / -1;">
            <label>Descrição do Status</label>
            <input type="text" name="TAGStatus" required>
        </div>
        <div class="button-container">
            <button type="submit">Salvar</button>
            <button type="button" class="add-btn" id="btnNovo">Novo</button>
        </div>
    </form>
</div>
<div class="card">
    <h2>Status Cadastrados</h2>
    <table id="statusTable" style="width:100%">
        <thead><tr><th>ID</th><th>Descrição</th><th>Ações</th></tr></thead>
        <tbody></tbody>
    </table>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarStatus() {
    $.post('paginas/status/status_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#statusTable')) { tabela = $('#statusTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(item => {
          const acoesHtml = `<button class="btn-editar">✏️</button> <button class="delete-btn btn-apagar" data-id="${item.TAGStatusID}">🗑️</button>`;
          const linha = tabela.row.add([ item.TAGStatusID, item.TAGStatus, acoesHtml ]).draw().node();
          $(linha).data('status', item);
        });
      }
    }, 'json');
  }

  $('#formStatus').submit(function(e) { e.preventDefault(); $.ajax({ url: 'paginas/status/status_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarStatus(); limparFormulario(); } else { alert(res.mensagem || res.erro); } } }); });
  $('#statusTable tbody').on('click', '.btn-editar', function () {
    const status = $(this).closest('tr').data('status');
    if (status) {
      $('input[name="TAGStatusID"]').val(status.TAGStatusID);
      $('input[name="TAGStatus"]').val(status.TAGStatus);
      window.scrollTo(0, 0);
    }
  });
  $('#statusTable tbody').on('click', '.btn-apagar', function (e) {
    const id = $(this).data('id');
    if (confirm(`Deseja apagar este status?`)) {
      $.post('paginas/status/status_actions.php', { action: 'delete', id: id }, function (res) { if(res.sucesso) carregarStatus(); });
    }
  });
  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
      $('#formStatus')[0].reset();
      $('input[name="TAGStatusID"]').val('');
  }
  carregarStatus();
});
</script>