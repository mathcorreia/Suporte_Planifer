<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <title>Gestão de Tarefas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    tr.selected { background-color: #d0ebff !important; }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Tarefas de Manutenção</h2>

  <div class="card mb-4">
    <div class="card-body">
      <form id="formTarefa" class="row g-3">
        <input type="hidden" name="tarefa_codigo">
        <div class="col-md-4">
          <label class="form-label">TAG da Tarefa</label>
          <input type="text" name="tarefa_tag" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">TAG do Ativo</label>
          <input type="text" name="ativo_tag" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Última Execução</label>
          <input type="date" name="ultima_execucao" class="form-control">
        </div>
        <div class="col-md-12">
          <label class="form-label">Descrição da Tarefa</label>
          <textarea name="tarefa_descricao" class="form-control" rows="2" required></textarea>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" class="btn btn-secondary" id="btnNovo">Novo</button>
        </div>
      </form>
    </div>
  </div>

  <h4>Tarefas Cadastradas</h4>
  <div class="table-responsive">
    <table id="tarefasTable" class="table table-bordered table-hover" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>TAG Tarefa</th>
          <th>Descrição</th>
          <th>Ativo</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script>
$(document).ready(function() {
  let tabela;

  function carregarTarefas() {
    // A chamada é relativa a este ficheiro, pelo que "tarefas_actions.php" é o correto
    $.post('tarefas_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#tarefasTable')) {
        tabela = $('#tarefasTable').DataTable({ responsive: true });
      }
      tabela.clear();
      if (Array.isArray(data)) {
        data.forEach(item => {
          const linha = tabela.row.add([
            item.tarefa_tag,
            item.tarefa_descricao,
            item.ativo_tag,
            `<button class="btn btn-sm btn-danger btn-apagar" data-id="${item.tarefa_codigo}" data-tag="${item.tarefa_tag}">🗑️</button>`
          ]).draw().node();
          $(linha).data('tarefa', item);
        });
      }
    }, 'json');
  }

  $('#formTarefa').submit(function(e) {
    e.preventDefault();
    $.post('tarefas_actions.php', $(this).serialize() + '&action=save', function(res) {
      alert(res.mensagem || res.erro || "Erro desconhecido");
      if(res.mensagem) {
          carregarTarefas();
          limparFormulario();
      }
    }, 'json');
  });

  $('#tarefasTable tbody').on('click', 'tr', function () {
    $('#tarefasTable tbody tr').removeClass('selected');
    $(this).addClass('selected');
    const tarefa = $(this).data('tarefa');
    if (tarefa) {
      $('input[name="tarefa_codigo"]').val(tarefa.tarefa_codigo);
      $('input[name="tarefa_tag"]').val(tarefa.tarefa_tag);
      $('input[name="ativo_tag"]').val(tarefa.ativo_tag);
      $('textarea[name="tarefa_descricao"]').val(tarefa.tarefa_descricao);
      if (tarefa.ultima_execucao) {
        $('input[name="ultima_execucao"]').val(tarefa.ultima_execucao.split(' ')[0]);
      } else {
        $('input[name="ultima_execucao"]').val('');
      }
    }
  });
  
  $('#tarefasTable tbody').on('click', '.btn-apagar', function (e) {
    e.stopPropagation();
    const id = $(this).data('id');
    const tag = $(this).data('tag');
    if (confirm(`Deseja realmente apagar a tarefa "${tag}"?`)) {
      $.post('tarefas_actions.php', { action: 'delete', id: id }, function (res) {
        alert(res.mensagem || res.erro || "Erro ao apagar.");
        if(res.mensagem) carregarTarefas();
      }, 'json');
    }
  });

  $('#btnNovo').on('click', limparFormulario);

  function limparFormulario() {
    $('#formTarefa')[0].reset();
    $('input[name="tarefa_codigo"]').val('');
    $('#tarefasTable tbody tr').removeClass('selected');
  }

  carregarTarefas();
});
</script>

</body>
</html>
