<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Ordens de Serviço</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .oss-card.parado { border-left: 5px solid #dc3545; }
    .oss-card { border-left: 5px solid #0d6efd; }
  </style>
</head>
<body class="bg-light">

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Ordens de Serviço em Aberto</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOSS">Nova Ordem de Serviço</button>
  </div>
  <div id="lista-oss" class="row g-3"></div>
</div>

<div class="modal fade" id="modalNovaOSS" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header"><h5 class="modal-title">Abrir Nova O.S.</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <form id="formNovaOSS" class="row g-3">
          <div class="col-md-4"><label class="form-label">Cód. Solicitante</label><input type="number" name="solicitante" class="form-control" required></div>
          <div class="col-md-8"><label class="form-label">TAG do Ativo</label><input type="text" name="ativo_tag" class="form-control" required></div>
          <div class="col-12"><label class="form-label">Descrição do Problema</label><textarea name="descricao_problema" class="form-control" rows="3" required></textarea></div>
          <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="maquina_parada" value="1" id="maquina_parada"><label class="form-check-label" for="maquina_parada">Máquina parada?</label></div></div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="formNovaOSS" class="btn btn-primary">Salvar</button></div>
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
          if(res.erro){
              container.html(`<div class="col-12"><div class="alert alert-danger">Erro ao carregar OS: ${res.erro}</div></div>`);
              return;
          }
          if (Array.isArray(res) && res.length > 0) {
              res.forEach(oss => {
                  const dataCriacao = oss.data_criacao && oss.data_criacao.date 
                      ? new Date(oss.data_criacao.date).toLocaleString('pt-BR') 
                      : 'Data indisponível';
                  const cardClass = oss.maquina_parada == 1 ? 'parado' : '';
                  const cardHtml = `
                      <div class="col-md-6 col-lg-4">
                          <div class="card oss-card ${cardClass}">
                              <div class="card-body">
                                  <h5 class="card-title">${oss.ativo_tag} <span class="badge bg-info float-end">${oss.status_atual}</span></h5>
                                  <h6 class="card-subtitle mb-2 text-muted">OS: ${oss.os_tag}</h6>
                                  <p class="card-text">${(oss.descricao_problema || '').substring(0, 100)}...</p>
                                  <p class="card-text"><small class="text-muted">Aberto em: ${dataCriacao}</small></p>
                                  <a href="#" class="card-link">Mostrar mais...</a>
                              </div>
                          </div>
                      </div>`;
                  container.append(cardHtml);
              });
          } else {
              container.html('<div class="col-12"><div class="alert alert-secondary">Nenhuma Ordem de Serviço em aberto.</div></div>');
          }
      }, 'json');
  }

  $('#formNovaOSS').submit(function(e) {
      e.preventDefault();
      $.ajax({
          url: 'ordem_servico_actions.php', type: 'POST', data: $(this).serialize() + '&action=create', dataType: 'json',
          success: function(res) {
              if (res.sucesso) {
                  alert(res.mensagem);
                  $('#modalNovaOSS').modal('hide');
                  $('#formNovaOSS')[0].reset();
                  carregarOSS();
              } else {
                  let errorMsg = res.mensagem || res.erro || "Ocorreu um erro desconhecido.";
                  if (res.details && res.details[0]) { errorMsg += "\nDetalhes: " + res.details[0].message; }
                  alert(errorMsg);
              }
          },
          error: function(jqXHR) { alert("Falha grave: " + jqXHR.responseText); }
      });
  });

  carregarOSS();
});
</script>
</body>
</html>