<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

<div class="card">
  <form id="formUsuario">
    <input type="hidden" name="codigo_original">
    <div class="form-group">
      <label>Código (Chapa)</label>
      <input type="number" name="codigo" required>
    </div>
    <div class="form-group" style="grid-column: span 2;">
      <label>Nome Completo</label>
      <input type="text" name="nome" required>
    </div>
    <div style="grid-column: 1 / -1; display:flex; gap: 1.5rem; flex-wrap: wrap; align-items: center; border-top: 1px solid #eee; padding-top: 1rem;">
      <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="ativo" value="1"> Ativo</label>
      <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="cliente" value="1"> Cliente</label>
      <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="tecnico" value="1"> Técnico</label>
      <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="planejador" value="1"> Planejador</label>
      <label style="margin: 0; font-weight: normal;"><input type="checkbox" name="admin" value="1"> Administrador</label>
    </div>
    <div class="button-container">
      <button type="submit">Salvar</button>
      <button type="button" id="btnNovo" class="add-btn">Novo</button>
    </div>
  </form>
</div>
<div class="card">
  <h2>Utilizadores Cadastrados</h2>
  <table id="usuariosTable" style="width:100%">
    <thead>
      <tr><th>Código</th><th>Nome</th><th>Perfis</th><th>Ações</th></tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
  let tabela;
  function carregarUsuarios() {
    $.post('paginas/Usuarios/usuarios_actions.php', { action: 'get' }, function(data) {
      if (!$.fn.DataTable.isDataTable('#usuariosTable')) { tabela = $('#usuariosTable').DataTable({ responsive: true });}
      tabela.clear();
      if(Array.isArray(data)) {
        data.forEach(u => {
          const perfis = [];
          if (u.tecnico == 1) perfis.push("Técnico");
          if (u.planejador == 1) perfis.push("Planejador");
          if (u.administrador == 1) perfis.push("Admin");
          if (u.cliente == 1) perfis.push("Cliente");
          const acoesHtml = `<button class="btn-editar">✏️</button> <button class="delete-btn btn-apagar" data-codigo="${u.codigo}">🗑️</button>`;
          const linha = tabela.row.add([u.codigo, u.nome, perfis.join(', '), acoesHtml]).draw().node();
          $(linha).data('usuario', u);
        });
      }
    }, 'json');
  }

  $('#formUsuario').submit(function(e) { e.preventDefault(); $.ajax({ url: 'paginas/Usuarios/usuarios_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarUsuarios(); limparFormulario(); } else { alert(res.mensagem || res.erro); } } }); });
  $('#usuariosTable tbody').on('click', '.btn-editar', function () {
    const usuario = $(this).closest('tr').data('usuario');
    if (usuario) {
      $('input[name="codigo_original"]').val(usuario.codigo);
      $('input[name="codigo"]').val(usuario.codigo).prop('readonly', true);
      $('input[name="nome"]').val(usuario.nome);
      $('input[name="ativo"]').prop('checked', usuario.ativo == 1);
      $('input[name="cliente"]').prop('checked', usuario.cliente == 1);
      $('input[name="tecnico"]').prop('checked', usuario.tecnico == 1);
      $('input[name="planejador"]').prop('checked', usuario.planejador == 1);
      $('input[name="admin"]').prop('checked', usuario.administrador == 1);
      window.scrollTo(0, 0);
    }
  });
  $('#usuariosTable tbody').on('click', '.btn-apagar', function (e) {
    const codigo = $(this).data('codigo');
    if (confirm(`Deseja apagar o utilizador de código ${codigo}?`)) {
      $.post('paginas/Usuarios/usuarios_actions.php', { action: 'delete', codigo: codigo }, function (res) { if(res.sucesso) carregarUsuarios(); });
    }
  });
  $('#btnNovo').on('click', limparFormulario);
  function limparFormulario() {
    $('#formUsuario')[0].reset();
    $('input[name="codigo_original"]').val('');
    $('input[name="codigo"]').prop('readonly', false);
  }
  carregarUsuarios();
});
</script>