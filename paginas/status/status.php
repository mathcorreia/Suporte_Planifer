<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Status de OS</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Status de OS</h2>
  <div class="card mb-4">
    <div class="card-body">
      <form id="formStatus" class="row g-3">
        <input type="hidden" name="TAGStatusID">
        <div class="col-md-12"><label class="form-label">Descrição do Status</label><input type="text" name="TAGStatus" class="form-control" required></div>
        <div class="col-12"><button type="submit" class="btn btn-primary">Salvar</button><button type="button" class="btn btn-secondary" id="btnNovo">Novo</button></div>
      </form>
    </div>
  </div>
  <h4>Status Cadastrados</h4>
  <div class="table-responsive">
    <table id="statusTable" class="table table-bordered table-hover" style="width:100%"><thead class="table-light"><tr><th>ID</th><th>Descrição</th><th>Ações</th></tr></thead><tbody></tbody></table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarStatus() {
    $.post('status_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#statusTable')) { tabela = $('#statusTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(item => {
          const acoesHtml = `<button class="btn btn-sm btn-primary btn-editar">✏️</button> <button class="btn btn-sm btn-danger btn-apagar" data-id="${item.TAGStatusID}">🗑️</button>`;
          const linha = tabela.row.add([ item.TAGStatusID, item.TAGStatus, acoesHtml ]).draw().node();
          $(linha).data('status', item);
        });
      }
    }, 'json');
  }

  $('#formStatus').submit(function(e) { e.preventDefault(); $.ajax({ url: 'status_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarStatus(); limparFormulario(); } else { alert(res.mensagem || res.erro); } }, error: function(jqXHR) { alert("Falha grave: " + jqXHR.responseText); } }); });

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
    if (confirm(`Deseja realmente apagar este status?`)) {
      $.post('status_actions.php', { action: 'delete', id: id }, function (res) {
        alert(res.mensagem || res.erro);
        if(res.sucesso) carregarStatus();
      }, 'json');
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
</body>
</html>