<?php
session_start();
if (isset($_SESSION['usuario_codigo'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - Sistema de Gestão</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: #f8f9fa; }
    .login-card { width: 100%; max-width: 400px; }
  </style>
</head>
<body>
<div class="card login-card">
  <div class="card-body">
    <h3 class="card-title text-center mb-4">Acesso ao Sistema</h3>
    <form id="formLogin">
      <div class="mb-3">
        <label for="codigo" class="form-label">Código (Chapa)</label>
        <input type="number" class="form-control" id="codigo" name="codigo" required>
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" class="form-control" id="senha" name="senha" required>
      </div>
      <div class="d-grid">
        <button type="submit" class="btn btn-primary">Entrar</button>
      </div>
    </form>
    <div id="mensagemErro" class="alert alert-danger mt-3" style="display: none;"></div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script>
$(document).ready(function() {
    $('#formLogin').submit(function(e) {
        e.preventDefault();
        $.post('login_actions.php', $(this).serialize(), function(res) {
            if (res.sucesso) {
                window.location.href = 'index.php';
            } else {
                $('#mensagemErro').text(res.erro).show();
            }
        }, 'json');
    });
});
</script>
</body>
</html>