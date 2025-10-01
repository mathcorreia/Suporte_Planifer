<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
// Headers para evitar cache
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <title>Gestão de Ativos</title>
  <link rel="stylesheet" href="css/estilo.css">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
  <style>
    tr.selected {
      background-color: #d0ebff !important;
    }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Ativos</h2>

  <div class="card mb-4">
    <div class="card-body">
      <form id="formAtivo" class="row g-3">
        <input type="hidden" name="id_original"> <div class="col-md-3">
          <label class="form-label">TAG do Ativo</label>
          <input type="text" name="ativo_tag" class="form-control" required>
        </div>
        <div class="col-md-9">
          <label class="form-label">Descrição</label>
          <input type="text" name="descricao" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Modelo</label>
          <input type="text" name="modelo" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">Número de Série</label>
          <input type="text" name="numero_serie" class="form-control">
        </div>
        <div class="col-md-4">
          <label class="form-label">TAG do Setor</label>
          <input type="text" name="setor_tag" class="form-control">
        </div>
         <div class="col-md-4">
          <label class="form-label">Tipo</label>
          <select name="tipo" class="form-select">
            <option value="">Selecione...</option>
            <option value="TCNC">TCNC</option>
            <option value="FCNC">FCNC</option>
            <option value="MANUAL">MANUAL</option>
            <option value="INFO">INFO</option>
            <option value="PREDIAL">PREDIAL</option>
            <option value="MAQUINA">MAQUINA</option>
            <option value="OUTRO">OUTRO</option>
          </select>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" class="btn btn-secondary" onclick="$('#formAtivo')[0].reset();">Novo</button>
        </div>
      </form>
    </div>
  </div>

  <h4>Ativos Cadastrados</h4>
  <div class="table-responsive">
    <table id="ativosTable" class="table table-bordered table-hover display nowrap" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>TAG</th>
          <th>Descrição</th>
          <th>Modelo</th>
          <th>Tipo</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let tabela;

function carregarAtivos() {
  $.post('ativos_actions.php', { action: 'get' }, function(data) {
    if (Array.isArray(data)) {
      if ($.fn.DataTable.isDataTable('#ativosTable')) {
        tabela.clear().draw();
      } else {
        tabela = $('#ativosTable').DataTable({
          autoWidth: true,
          responsive: true
        });
      }

      data.forEach(ativo => {
        const linha = tabela.row.add([
          ativo.ativo_tag,
          ativo.descricao,
          ativo.modelo,
          ativo.tipo,
          `<button class="btn btn-sm btn-danger btn-apagar" data-id="${ativo.id}" data-tag="${ativo.ativo_tag}">🗑️</button>`
        ]).draw().node();

        $(linha).data('ativo', ativo); // Guarda o objeto completo do ativo na linha
      });
    } else {
      console.error("Erro ao carregar ativos:", data);
      alert("Não foi possível carregar os ativos.");
    }
  }, 'json');
}

$('#formAtivo').submit(function(e) {
  e.preventDefault();
  const payload = $(this).serialize() + '&action=save'; // Adiciona a ação ao payload

  $.post('ativos_actions.php', payload, function(res) {
    alert(res.mensagem || res.erro || "Erro desconhecido");
    if(res.mensagem) {
        carregarAtivos();
        $('#formAtivo')[0].reset();
    }
  }, 'json');
});

$('#ativosTable tbody').on('click', 'tr', function () {
  $('#ativosTable tbody tr').removeClass('selected');
  $(this).addClass('selected');

  const ativo = $(this).data('ativo');
  if (!ativo) return;

  // Preenche o formulário com os dados do ativo selecionado
  $('input[name="id_original"]').val(ativo.id);
  $('input[name="ativo_tag"]').val(ativo.ativo_tag);
  $('input[name="descricao"]').val(ativo.descricao);
  $('input[name="modelo"]').val(ativo.modelo);
  $('input[name="numero_serie"]').val(ativo.numero_serie);
  $('input[name="setor_tag"]').val(ativo.setor_tag);
  $('select[name="tipo"]').val(ativo.tipo);
});

$('#ativosTable tbody').on('click', '.btn-apagar', function (e) {
  e.stopPropagation(); // Impede que o clique na linha seja acionado
  const id = $(this).data('id');
  const tag = $(this).data('tag');
  if (!confirm(`Deseja realmente apagar o ativo de TAG "${tag}"?`)) return;

  $.post('ativos_actions.php', { action: 'delete', id: id }, function (res) {
    alert(res.mensagem || res.erro || "Erro ao apagar.");
    if(res.mensagem) {
        carregarAtivos();
    }
  }, 'json');
});

// Carrega os dados iniciais quando a página estiver pronta
$(document).ready(carregarAtivos);
</script>

</body>
</html>