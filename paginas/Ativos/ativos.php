<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../../css/style.css">
   <style>
    head, header{
   
          font-family: 'StaraBlack', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    line-height: 1.6;

        height: 5.2rem;
    }
</style>

<div class="card">
  <form id="formAtivo">
    <input type="hidden" name="ativo_tag_original">
    <div class="form-group"><label>TAG do Ativo</label><input type="text" name="Ativo_TAG" required></div>
    <div class="form-group" style="grid-column: span 2;"><label>Descrição</label><input type="text" name="Descricao" required></div>
    <div class="form-group"><label>Setor</label><select name="Setor_TAG" required></select></div>
    <div class="form-group"><label>Modelo</label><input type="text" name="Modelo"></div>
    <div class="form-group"><label>Número de Série</label><input type="text" name="Numero_Serie"></div>
    <div class="form-group"><label>Tipo</label><input type="text" name="Tipo"></div>
    <div class="form-group"><label>Data de Instalação</label><input type="date" name="Instalacao"></div>
    <div class="form-group"><label>Sensor</label><input type="text" name="Sensor"></div>
    <div class="form-group"><label>Comando</label><input type="text" name="Comando"></div>
    <div class="form-group"><label>Rede Elétrica TAG</label><input type="text" name="Rede_Eletrica_TAG"></div>
    <div class="form-group"><label>Corrente</label><input type="number" name="Corrente"></div>
    <div style="grid-column: 1 / -1; display:flex; gap: 1.5rem; flex-wrap: wrap; align-items: center; border-top: 1px solid #eee; padding-top: 1rem;">
        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="Ferramenta" value="1"> Ferramenta</label>
        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="Maquina" value="1"> Máquina</label>
        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="Controle" value="1"> Controle</label>
        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="Turno1" value="1"> Turno 1</label>
        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="Turno2" value="1"> Turno 2</label>
        <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="Turno3" value="1"> Turno 3</label>
    </div>
    <div class="button-container"><button type="submit">Salvar</button><button type="button" id="btnNovo" class="add-btn">Novo</button></div>
  </form>
</div>
<div class="card">
  <h2>Ativos Cadastrados</h2>
  <table id="ativosTable" style="width:100%"><thead><tr><th>TAG</th><th>Descrição</th><th>Setor</th><th>Tipo</th><th>Ações</th></tr></thead><tbody></tbody></table>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarSetoresDropdown() {
      $.post('paginas/Setores/setores_actions.php', { action: 'get' }, function(data) {
          const select = $('select[name="Setor_TAG"]');
          select.empty().append('<option value="">Selecione...</option>');
          if(Array.isArray(data)) { data.forEach(setor => { select.append(`<option value="${setor.setor_tag}">${setor.setor_tag} - ${setor.descricao}</option>`); }); }
      }, 'json');
  }
  function carregarAtivos() {
    $.post('paginas/Ativos/ativos_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#ativosTable')) { tabela = $('#ativosTable').DataTable({ responsive: true }); }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(ativo => {
          const acoesHtml = `<button class="btn-editar">✏️</button> <button class="delete-btn btn-apagar" data-tag="${ativo.Ativo_TAG}">🗑️</button>`;
          const linha = tabela.row.add([ ativo.Ativo_TAG, ativo.Descricao, ativo.Setor_TAG, ativo.Tipo, acoesHtml ]).draw().node();
          $(linha).data('ativo', ativo);
        });
      }
    }, 'json');
  }
  $('#formAtivo').submit(function(e) { e.preventDefault(); $.ajax({ url: 'paginas/Ativos/ativos_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarAtivos(); limparFormulario(); } else { alert(res.mensagem || res.erro); } } }); });
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
    if (confirm(`Deseja apagar o ativo ${tag}?`)) {
      $.post('paginas/Ativos/ativos_actions.php', { action: 'delete', Ativo_TAG: tag }, function (res) { if(res.sucesso) carregarAtivos(); });
    }
  });
  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
      $('#formAtivo')[0].reset();
      $('input[name="ativo_tag_original"]').val('');
      $('input[name="Ativo_TAG"]').val(ativo.Ativo_TAG);

     // $('input[name="Ativo_TAG"]').prop('readonly', false);
  }
  carregarSetoresDropdown();
  carregarAtivos();
});
</script>