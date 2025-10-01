<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gestão de Ativos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Ativos</h2>
  <div class="card mb-4">
    <div class="card-body">
      <form id="formAtivo" class="row g-3">
        <input type="hidden" name="ativo_tag_original">
        
        <div class="col-md-3"><label class="form-label">TAG do Ativo</label><input type="text" name="Ativo_TAG" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label">Descrição</label><input type="text" name="Descricao" class="form-control" required></div>
        <div class="col-md-3"><label class="form-label">Setor</label><select name="Setor_TAG" class="form-select" required></select></div>

        <div class="col-md-3"><label class="form-label">Modelo</label><input type="text" name="Modelo" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Número de Série</label><input type="text" name="Numero_Serie" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Tipo</label><input type="text" name="Tipo" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Data de Instalação</label><input type="date" name="Instalacao" class="form-control"></div>

        <div class="col-md-3"><label class="form-label">Sensor</label><input type="text" name="Sensor" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Comando</label><input type="text" name="Comando" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Rede Elétrica TAG</label><input type="text" name="Rede_Eletrica_TAG" class="form-control"></div>
        <div class="col-md-3"><label class="form-label">Corrente</label><input type="number" name="Corrente" class="form-control"></div>
        
        <div class="col-12"><hr></div>
        <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="Ferramenta" value="1"><label class="form-check-label">Ferramenta</label></div></div>
        <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="Maquina" value="1"><label class="form-check-label">Máquina</label></div></div>
        <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="Controle" value="1"><label class="form-check-label">Controle</label></div></div>
        <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="Turno1" value="1"><label class="form-check-label">Turno 1</label></div></div>
        <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="Turno2" value="1"><label class="form-check-label">Turno 2</label></div></div>
        <div class="col-md-2"><div class="form-check"><input class="form-check-input" type="checkbox" name="Turno3" value="1"><label class="form-check-label">Turno 3</label></div></div>

        <div class="col-12 mt-4"><button type="submit" class="btn btn-primary">Salvar</button><button type="button" class="btn btn-secondary" id="btnNovo">Novo</button></div>
      </form>
    </div>
  </div>
  <h4>Ativos Cadastrados</h4>
  <div class="table-responsive">
    <table id="ativosTable" class="table table-bordered table-hover" style="width:100%"><thead class="table-light"><tr><th>TAG</th><th>Descrição</th><th>Setor</th><th>Tipo</th><th>Ações</th></tr></thead><tbody></tbody></table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;

  function carregarSetoresDropdown() {
      $.post('../Setores/setores_actions.php', { action: 'get' }, function(data) {
          const select = $('select[name="Setor_TAG"]');
          select.empty().append('<option value="">Selecione um setor...</option>');
          if(Array.isArray(data)) {
              data.forEach(setor => {
                  select.append(`<option value="${setor.setor_tag}">${setor.setor_tag} - ${setor.descricao}</option>`);
              });
          }
      }, 'json');
  }

  function carregarAtivos() {
    $.post('ativos_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#ativosTable')) { tabela = $('#ativosTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(ativo => {
          const acoesHtml = `<button class="btn btn-sm btn-primary btn-editar">✏️</button> <button class="btn btn-sm btn-danger btn-apagar" data-tag="${ativo.Ativo_TAG}">🗑️</button>`;
          const linha = tabela.row.add([ ativo.Ativo_TAG, ativo.Descricao, ativo.Setor_TAG, ativo.Tipo, acoesHtml ]).draw().node();
          $(linha).data('ativo', ativo);
        });
      }
    }, 'json');
  }

  $('#formAtivo').submit(function(e) { e.preventDefault(); $.ajax({ url: 'ativos_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarAtivos(); limparFormulario(); } else { alert(res.mensagem || res.erro); } }, error: function(jqXHR) { alert("Falha grave: " + jqXHR.responseText); } }); });

  $('#ativosTable tbody').on('click', '.btn-editar', function () {
    const ativo = $(this).closest('tr').data('ativo');
    if (ativo) {
      $('input[name="ativo_tag_original"]').val(ativo.Ativo_TAG);
      $('input[name="Ativo_TAG"]').val(ativo.Ativo_TAG).prop('readonly', true);
      $('input[name="Descricao"]').val(ativo.Descricao);
      $('select[name="Setor_TAG"]').val(ativo.Setor_TAG);
      $('input[name="Modelo"]').val(ativo.Modelo);
      $('input[name="Numero_Serie"]').val(ativo.Numero_Serie);
      $('input[name="Tipo"]').val(ativo.Tipo);
      $('input[name="Instalacao"]').val(ativo.Instalacao);
      $('input[name="Sensor"]').val(ativo.Sensor);
      $('input[name="Comando"]').val(ativo.Comando);
      $('input[name="Rede_Eletrica_TAG"]').val(ativo.Rede_Eletrica_TAG);
      $('input[name="Corrente"]').val(ativo.Corrente);
      $('input[name="Ferramenta"]').prop('checked', ativo.Ferramenta == 1);
      $('input[name="Maquina"]').prop('checked', ativo.Maquina == 1);
      $('input[name="Controle"]').prop('checked', ativo.Controle == 1);
      $('input[name="Turno1"]').prop('checked', ativo.Turno1 == 1);
      $('input[name="Turno2"]').prop('checked', ativo.Turno2 == 1);
      $('input[name="Turno3"]').prop('checked', ativo.Turno3 == 1);
      window.scrollTo(0, 0);
    }
  });

  $('#ativosTable tbody').on('click', '.btn-apagar', function () {
    const tag = $(this).data('tag');
    if (confirm(`Deseja realmente apagar o ativo ${tag}?`)) {
      $.post('ativos_actions.php', { action: 'delete', Ativo_TAG: tag }, function (res) {
        alert(res.mensagem || res.erro);
        if(res.sucesso) carregarAtivos();
      }, 'json');
    }
  });
  
  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
      $('#formAtivo')[0].reset();
      $('input[name="ativo_tag_original"]').val('');
      $('input[name="Ativo_TAG"]').prop('readonly', false);
  }

  carregarSetoresDropdown();
  carregarAtivos();
});
</script>
</body>
</html>