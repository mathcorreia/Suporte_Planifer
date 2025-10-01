<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
header("Expires: Tue, 01 Jan 2000 00:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <title>Gestão de Usuários</title>
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
  <h2 class="mb-4">Cadastro de Usuários</h2>

  <div class="card mb-4">
    <div class="card-body">
      <form id="formUsuario" class="row g-3">
        <div class="col-md-2">
          <label class="form-label">Código</label>
          <input type="number" name="codigo" class="form-control" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Nome</label>
          <input type="text" name="nome" class="form-control" required>
        </div>
        <div class="col-md-4">
          <label class="form-label">Senha</label>
          <input type="password" name="senha" class="form-control">
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
        </div>
      </form>
    </div>
  </div>

  <h4>Usuários Cadastrados</h4>
  <div class="table-responsive">
    <table id="usuariosTable" class="table table-bordered table-hover display nowrap" style="width:100%">
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
let tabela;

function carregarUsuarios() {
  $.post('actions.php', { action: 'get' }, function(data) {
    if (Array.isArray(data)) {
      if ($.fn.DataTable.isDataTable('#usuariosTable')) {
        tabela.clear().draw();
      } else {
        tabela = $('#usuariosTable').DataTable({
          autoWidth: true,
          responsive: true
        });
      }

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
    } else {
      console.error("Erro ao carregar usuários:", data);
    }
  }, 'json');
}

$('#formUsuario').submit(function(e) {
  e.preventDefault();
  const dados = $(this).serializeArray();
  const payload = { action: 'save' };

  dados.forEach(item => {
    payload[item.name] = item.value || '';
  });

  payload['ativo'] = $('input[name="ativo"]').is(':checked') ? 1 : 0;
  payload['cliente'] = $('input[name="cliente"]').is(':checked') ? 1 : 0;
  payload['tecnico'] = $('input[name="tecnico"]').is(':checked') ? 1 : 0;
  payload['planejador'] = $('input[name="planejador"]').is(':checked') ? 1 : 0;
  payload['admin'] = $('input[name="admin"]').is(':checked') ? 1 : 0;

  $.post('actions.php', payload, function(res) {
    alert(res.mensagem || res.erro || "Erro desconhecido");
    carregarUsuarios();
    $('#formUsuario')[0].reset();
  }, 'json');
});

$('#usuariosTable tbody').on('click', 'tr', function () {
  $('#usuariosTable tbody tr').removeClass('selected');
  $(this).addClass('selected');

  const usuario = $(this).data('usuario');
  if (!usuario) return;

  $('input[name="codigo"]').val(usuario.codigo);
  $('input[name="nome"]').val(usuario.nome);
  $('input[name="senha"]').val(usuario.senha || '');

  $('input[name="ativo"]').prop('checked', usuario.ativo == 1);
  $('input[name="cliente"]').prop('checked', usuario.cliente == 1);
  $('input[name="tecnico"]').prop('checked', usuario.tecnico == 1);
  $('input[name="planejador"]').prop('checked', usuario.planejador == 1);
  $('input[name="admin"]').prop('checked', usuario.administrador == 1);
});

$('#usuariosTable tbody').on('click', '.btn-apagar', function (e) {
  e.stopPropagation();
  const codigo = $(this).data('codigo');
  if (!confirm(`Deseja realmente apagar o usuário de código ${codigo}?`)) return;

  $.post('actions.php', { action: 'delete', codigo }, function (res) {
    alert(res.mensagem || res.erro || "Erro ao apagar.");
    carregarUsuarios();
  }, 'json');
});

$(document).ready(carregarUsuarios);
</script>

</body>
</html>