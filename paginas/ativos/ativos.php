<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Ativos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style> tr.selected { background-color: #d0ebff !important; } </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Ativos</h2>
  <div class="card mb-4">
    <div class="card-body">
      <form id="formAtivo" class="row g-3">
        <input type="hidden" name="id_original">
        <div class="col-md-3"><label class="form-label">TAG do Ativo</label><input type="text" name="ativo_tag" class="form-control" required></div>
        <div class="col-md-9"><label class="form-label">Descrição</label><input type="text" name="descricao" class="form-control" required></div>
        <div class="col-md-4"><label class="form-label">Modelo</label><input type="text" name="modelo" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Número de Série</label><input type="text" name="numero_serie" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">TAG do Setor</label><input type="text" name="setor_tag" class="form-control"></div>
        <div class="col-md-4"><label class="form-label">Tipo</label><select name="tipo" class="form-select"><option value="">Selecione...</option><option value="TCNC">TCNC</option><option value="FCNC">FCNC</option><option value="MANUAL">MANUAL</option><option value="INFO">INFO</option><option value="PREDIAL">PREDIAL</option><option value="MAQUINA">MAQUINA</option><option value="OUTRO">OUTRO</option></select></div>
        <div class="col-12"><button type="submit" class="btn btn-primary">Salvar</button><button type="button" class="btn btn-secondary" id="btnNovo">Novo</button></div>
      </form>
    </div>
  </div>
  <h4>Ativos Cadastrados</h4>
  <div class="table-responsive">
    <table id="ativosTable" class="table table-bordered table-hover" style="width:100%"><thead class="table-light"><tr><th>TAG</th><th>Descrição</th><th>Modelo</th><th>Tipo</th><th>Ações</th></tr></thead><tbody></tbody></table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarAtivos() {
    $.post('ativos_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#ativosTable')) { tabela = $('#ativosTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(ativo => {
          const linha = tabela.row.add([ ativo.ativo_tag, ativo.descricao, ativo.modelo, ativo.tipo, `<button class="btn btn-sm btn-danger btn-apagar" data-id="${ativo.id}" data-tag="${ativo.ativo_tag}">🗑️</button>` ]).draw().node();
          $(linha).data('ativo', ativo);
        });
      }
    }, 'json');
  }
  $('#formAtivo').submit(function(e) {
    e.preventDefault();
    $.post('ativos_actions.php', $(this).serialize() + '&action=save', function(res) {
      alert(res.mensagem || res.erro || "Erro desconhecido");
      if(res.mensagem) { carregarAtivos(); limparFormulario(); }
    }, 'json');
  });
  $('#ativosTable tbody').on('click', 'tr', function () {
    $('#ativosTable tbody tr').removeClass('selected');
    $(this).addClass('selected');
    const ativo = $(this).data('ativo');
    if (ativo) {
      $('input[name="id_original"]').val(ativo.id);
      $('input[name="ativo_tag"]').val(ativo.ativo_tag);
      $('input[name="descricao"]').val(ativo.descricao);
      $('input[name="modelo"]').val(ativo.modelo);
      $('input[name="numero_serie"]').val(ativo.numero_serie);
      $('input[name="setor_tag"]').val(ativo.setor_tag);
      $('select[name="tipo"]').val(ativo.tipo);
    }
  });
  $('#ativosTable tbody').on('click', '.btn-apagar', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    const tag = $(this).data('tag');
    if (confirm(`Deseja realmente apagar o ativo "${tag}"?`)) {
      $.post('ativos_actions.php', { action: 'delete', id: id }, function (res) {
        alert(res.mensagem || res.erro || "Erro ao apagar.");
        if(res.mensagem) carregarAtivos();
      }, 'json');
    }
  });
  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
      $('#formAtivo')[0].reset();
      $('input[name="id_original"]').val('');
      $('#ativosTable tbody tr').removeClass('selected');
  }
  carregarAtivos();
});
</script>
</body>
</html>