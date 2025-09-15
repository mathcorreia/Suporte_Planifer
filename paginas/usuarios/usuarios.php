<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <title>Gestão de Utilizadores</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
  <style>
    tr.selected { background-color: #d0ebff !important; }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <h2 class="mb-4">Cadastro de Utilizadores</h2>

  <div class="card mb-4">
    <div class="card-body">
      <form id="formUsuario" class="row g-3">
        <input type="hidden" name="codigo_original">
        <div class="col-md-3">
          <label class="form-label">Código (Chapa)</label>
          <input type="number" name="codigo" class="form-control" required>
        </div>
        <div class="col-md-9">
          <label class="form-label">Nome Completo</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="col-12">
          <label class="form-label">Perfis</label><br>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="ativo"> <label class="form-check-label">Ativo</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="cliente"> <label class="form-check-label">Cliente</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="tecnico"> <label class="form-check-label">Técnico</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="planejador"> <label class="form-check-label">Planejador</label>
          </div>
          <div class="form-check form-check-inline">
            <input class="form-check-input" type="checkbox" name="admin"> <label class="form-check-label">Administrador</label>
          </div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-primary">Salvar</button>
          <button type="button" class="btn btn-secondary" id="btnNovo">Novo</button>
        </div>
      </form>
    </div>
  </div>

  <h4>Utilizadores Cadastrados</h4>
  <div class="table-responsive">
    <table id="usuariosTable" class="table table-bordered table-hover" style="width:100%">
      <thead class="table-light">
        <tr>
          <th>Código</th>
          <th>Nome</th>
          <th>Perfis</th>
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

  function carregarUsuarios() {
    $.post('usuarios_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#usuariosTable')) {
        tabela = $('#usuariosTable').DataTable({ responsive: true });
      }
      tabela.clear();
      if(Array.isArray(data)) {
        data.forEach(u => {
          const perfis = [];
          if (u.tecnico) perfis.push("Técnico");
          if (u.planejador) perfis.push("Planejador");
          if (u.administrador) perfis.push("Admin");
          if (u.cliente) perfis.push("Cliente");

          const linha = tabela.row.add([
            u.codigo,
            u.nome,
            perfis.join(', '),
            `<button class="btn btn-sm btn-danger btn-apagar" data-codigo="${u.codigo}">🗑️</button>`
          ]).draw().node();
          $(linha).data('usuario', u);
        });
      }
    }, 'json').fail(function() {
        alert("Erro ao carregar a lista de utilizadores. Verifique o console para mais detalhes.");
    });
  }

  $('#formUsuario').submit(function(e) {
    e.preventDefault();
    const dados = $(this).serializeArray();
    const payload = { action: 'save' };

    dados.forEach(item => payload[item.name] = item.value);
    ['ativo', 'cliente', 'tecnico', 'planejador', 'admin'].forEach(perfil => {
        payload[perfil] = $(`input[name="${perfil}"]`).is(':checked') ? 1 : 0;
    });

    $.post('usuarios_actions.php', payload, function(res) {
      alert(res.mensagem || res.erro || "Erro desconhecido");
      if (res.mensagem) {
        carregarUsuarios();
        limparFormulario();
      }
    }, 'json').fail(function() {
        alert("Erro ao salvar o utilizador. Verifique o console para mais detalhes.");
    });
  });

  $('#usuariosTable tbody').on('click', 'tr', function () {
    $('#usuariosTable tbody tr').removeClass('selected');
    $(this).addClass('selected');
    const usuario = $(this).data('usuario');
    if (usuario) {
      $('input[name="codigo_original"]').val(usuario.codigo);
      $('input[name="codigo"]').val(usuario.codigo).prop('readonly', true);
      $('input[name="nome"]').val(usuario.nome);
      $('input[name="ativo"]').prop('checked', usuario.ativo == 1);
      $('input[name="cliente"]').prop('checked', usuario.cliente == 1);
      $('input[name="tecnico"]').prop('checked', usuario.tecnico == 1);
      $('input[name="planejador"]').prop('checked', usuario.planejador == 1);
      $('input[name="admin"]').prop('checked', usuario.administrador == 1);
    }
  });

  $('#usuariosTable tbody').on('click', '.btn-apagar', function (e) {
    e.stopPropagation();
    const codigo = $(this).data('codigo');
    if (confirm(`Deseja realmente apagar o utilizador de código ${codigo}?`)) {
      $.post('usuarios_actions.php', { action: 'delete', codigo: codigo }, function (res) {
        alert(res.mensagem || res.erro || "Erro ao apagar.");
        if(res.mensagem) carregarUsuarios();
      }, 'json');
    }
  });

  $('#btnNovo').on('click', limparFormulario);

  function limparFormulario() {
    $('#formUsuario')[0].reset();
    $('input[name="codigo_original"]').val('');
    $('input[name="codigo"]').prop('readonly', false);
    $('#usuariosTable tbody tr').removeClass('selected');
  }

  carregarUsuarios();
});
</script>

</body>
</html>