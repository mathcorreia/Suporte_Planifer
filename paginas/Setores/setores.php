<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Setores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Setores</h2>
  <div class="card mb-4">
    <div class="card-body">
      <form id="formSetor" class="row g-3">
        <input type="hidden" name="setor_tag_original">
        <div class="col-md-4"><label class="form-label">TAG do Setor</label><input type="text" name="setor_tag" class="form-control" required></div>
        <div class="col-md-8"><label class="form-label">Descrição do Setor</label><input type="text" name="descricao" class="form-control" required></div>
        <div class="col-12"><button type="submit" class="btn btn-primary">Salvar</button><button type="button" class="btn btn-secondary" id="btnNovo">Novo</button></div>
      </form>
    </div>
  </div>
  <h4>Setores Cadastrados</h4>
  <div class="table-responsive">
    <table id="setoresTable" class="table table-bordered table-hover" style="width:100%"><thead class="table-light"><tr><th>TAG</th><th>Descrição</th><th>Ações</th></tr></thead><tbody></tbody></table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarSetores() {
    $.post('setores_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#setoresTable')) { tabela = $('#setoresTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(setor => {
          const acoesHtml = `<button class="btn btn-sm btn-primary btn-editar">✏️</button> <button class="btn btn-sm btn-danger btn-apagar" data-tag="${setor.setor_tag}">🗑️</button>`;
          const linha = tabela.row.add([ setor.setor_tag, setor.descricao, acoesHtml ]).draw().node();
          $(linha).data('setor', setor);
        });
      }
    }, 'json');
  }

  $('#formSetor').submit(function(e) { e.preventDefault(); $.ajax({ url: 'setores_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarSetores(); limparFormulario(); } else { alert(res.mensagem || res.erro); } }, error: function(jqXHR) { alert("Falha grave: " + jqXHR.responseText); } }); });

  $('#setoresTable tbody').on('click', '.btn-editar', function () {
    const setor = $(this).closest('tr').data('setor');
    if (setor) {
      $('input[name="setor_tag_original"]').val(setor.setor_tag);
      $('input[name="setor_tag"]').val(setor.setor_tag).prop('readonly', true);
      $('input[name="descricao"]').val(setor.descricao);
      window.scrollTo(0, 0);
    }
  });

  $('#setoresTable tbody').on('click', '.btn-apagar', function (e) {
    const tag = $(this).data('tag');
    if (confirm(`Deseja realmente apagar o setor "${tag}"?`)) {
      $.post('setores_actions.php', { action: 'delete', setor_tag: tag }, function (res) {
        alert(res.mensagem || res.erro);
        if(res.sucesso) carregarSetores();
      }, 'json');
    }
  });

  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
      $('#formSetor')[0].reset();
      $('input[name="setor_tag_original"]').val('');
      $('input[name="setor_tag"]').prop('readonly', false);
  }
  carregarSetores();
});
</script>
</body>
</html>