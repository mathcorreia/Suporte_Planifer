<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card">
  <form id="formSetor">
    <input type="hidden" name="setor_tag_original">
    <div class="form-group">
      <label>TAG do Setor</label>
      <input type="text" name="setor_tag" required>
    </div>
    <div class="form-group" style="grid-column: span 2;">
      <label>Descrição do Setor</label>
      <input type="text" name="descricao" required>
    </div>
    <div class="button-container">
      <button type="submit">Salvar</button>
      <button type="button" id="btnNovo" class="add-btn">Novo</button>
    </div>
  </form>
</div>

<div class="card">
  <h2>Setores Cadastrados</h2>
  <table id="setoresTable" style="width:100%">
    <thead>
      <tr><th>TAG</th><th>Descrição</th><th>Ações</th></tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarSetores() {
    $.post('paginas/Setores/setores_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#setoresTable')) { tabela = $('#setoresTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(setor => {
          const acoesHtml = `<button class="btn-editar">✏️</button> <button class="delete-btn btn-apagar" data-tag="${setor.setor_tag}">🗑️</button>`;
          const linha = tabela.row.add([ setor.setor_tag, setor.descricao, acoesHtml ]).draw().node();
          $(linha).data('setor', setor);
        });
      }
    }, 'json');
  }

  $('#formSetor').submit(function(e) { e.preventDefault(); $.ajax({ url: 'paginas/Setores/setores_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarSetores(); limparFormulario(); } else { alert(res.mensagem || res.erro); } } }); });
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
    if (confirm(`Deseja apagar o setor "${tag}"?`)) {
      $.post('paginas/Setores/setores_actions.php', { action: 'delete', setor_tag: tag }, function (res) {
        if(res.sucesso) carregarSetores();
      });
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