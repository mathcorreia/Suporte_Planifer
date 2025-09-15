<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
?>
  <meta charset="UTF-8">
  <title>Ordens de Serviço de Suporte</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .oss-card { border-left: 5px solid #0d6efd; }
    .oss-card.parado { border-left-color: #dc3545; }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Ordens de Serviço em Aberto</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOSS">
      Nova Ordem de Serviço
    </button>
  </div>
  
  <div id="lista-oss" class="row g-3">
    <!-- Cards das OSS serão inseridos aqui via JavaScript -->
  </div>
</div>

<!-- Modal para Nova Ordem de Serviço -->
<div class="modal fade" id="modalNovaOSS" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Abrir Nova O.S.S.</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form id="formNovaOSS" class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Cód. Responsável (chapa)</label>
            <input type="number" name="responsavel_codigo" class="form-control" required>
          </div>
          <div class="col-md-8">
            <label class="form-label">TAG do Ativo</label>
            <input type="text" name="ativo_tag" class="form-control" required>
          </div>
          <div class="col-12">
            <label class="form-label">Descreva o problema</label>
            <textarea name="descricao_problema" class="form-control" rows="3" required></textarea>
          </div>
          <div class="col-md-6 d-flex align-items-end">
             <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" name="maquina_parada" id="maquina_parada">
              <label class="form-check-label" for="maquina_parada">Máquina parada?</label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-primary" id="btnSalvarOSS">Salvar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
  function carregarOSS() {
      $.post('ordem_servico_actions.php', { action: 'get_open' }, function(res) {
          const container = $('#lista-oss');
          container.empty();
          if (res.erro) {
            container.html(`<div class="col-12"><div class="alert alert-danger">Erro ao carregar Ordens de Serviço: ${res.erro}</div></div>`);
            return;
          }
          if (Array.isArray(res.dados) && res.dados.length > 0) {
              res.dados.forEach(oss => {
                  const cardClass = oss.maquina_parada == 1 ? 'parado' : '';
                  const cardHtml = `
                      <div class="col-md-6 col-lg-4">
                          <div class="card oss-card ${cardClass}">
                              <div class="card-body">
                                  <h5 class="card-title">${oss.ativo_tag} <span class="badge bg-info float-end">${oss.status_atual}</span></h5>
                                  <h6 class="card-subtitle mb-2 text-muted">OS: ${oss.os_tag}</h6>
                                  <p class="card-text">${(oss.descricao_problema || '').substring(0, 100)}...</p>
                                  <p class="card-text"><small class="text-muted">Aberto em: ${oss.data_criacao}</small></p>
                                  <a href="#" class="card-link">Mostrar mais...</a>
                              </div>
                          </div>
                      </div>
                  `;
                  container.append(cardHtml);
              });
          } else {
              container.html('<div class="col-12"><div class="alert alert-secondary">Nenhuma Ordem de Serviço em aberto.</div></div>');
          }
      }, 'json');
  }

  $('#btnSalvarOSS').on('click', function() {
    // Para o modal, é mais fiável acionar o submit do formulário
    $('#formNovaOSS').submit();
  });

  $('#formNovaOSS').submit(function(e) {
      e.preventDefault();
      const payload = $(this).serialize() + '&action=create';

      $.post('ordem_servico_actions.php', payload, function(res) {
          alert(res.mensagem || res.erro);
          if(res.mensagem) {
              $('#modalNovaOSS').modal('hide');
              $('#formNovaOSS')[0].reset();
              carregarOSS();
          }
      }, 'json');
  });

  carregarOSS();
});
</script>

</body>
</html>