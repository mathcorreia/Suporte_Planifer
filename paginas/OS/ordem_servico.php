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
          <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="maquina_parada" id="maquina_parada"><label class="form-check-label" for="maquina_parada">Máquina parada?</label></div></div>
        </form>
      </div>
      <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" form="formNovaOSS" class="btn btn-primary">Salvar</button></div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalDetalhesOSS" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalhes_os_tag"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6>Informações Gerais</h6>
                <p id="detalhes_info"></p>
                <hr>
                <h6>Histórico de Status</h6>
                <div id="detalhes_status_historico"></div>
            </div>
            <div class="modal-footer" id="detalhes_footer"></div>
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
          if (Array.isArray(res) && res.length > 0) {
              res.forEach(oss => {
                  const cardClass = oss.maquina_parada == 1 ? 'parado' : '';
                  const cardHtml = `
                      <div class="col-md-6 col-lg-4">
                          <div class="card oss-card ${cardClass}">
                              <div class="card-body">
                                  <h5 class="card-title">${oss.ativo_tag} <span class="badge bg-info float-end">${oss.status_atual}</span></h5>
                                  <h6 class="card-subtitle mb-2 text-muted">OS: ${oss.os_tag}</h6>
                                  <p class="card-text">${(oss.descricao_problema || '').substring(0, 100)}...</p>
                                  <p class="card-text"><small class="text-muted">Aberto em: ${new Date(oss.data_criacao.date).toLocaleString('pt-BR')}</small></p>
                                  <a href="#" class="card-link btn-mostrar-mais" data-ostag="${oss.os_tag}">Mostrar mais...</a>
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
      const payload = $(this).serialize() + '&action=create';
      $.post('ordem_servico_actions.php', payload, function(res) {
          alert(res.mensagem || res.erro);
          if(res.sucesso) {
              $('#modalNovaOSS').modal('hide');
              $('#formNovaOSS')[0].reset();
              carregarOSS();
          }
      }, 'json');
  });

  $('#lista-oss').on('click', '.btn-mostrar-mais', function(e) {
    e.preventDefault();
    const os_tag = $(this).data('ostag');
    $.post('ordem_servico_actions.php', {action: 'get_details', os_tag: os_tag}, function(res) {
        if(res.os) {
            $('#detalhes_os_tag').text('Detalhes da OS: ' + res.os.os_tag);
            let info = `<strong>Ativo:</strong> ${res.os.ativo_tag}<br>
                        <strong>Problema:</strong> ${res.os.descricao_problema}<br>
                        <strong>Solicitante:</strong> ${res.os.solicitante_nome} (${res.os.solicitante})<br>
                        <strong>Abertura:</strong> ${new Date(res.os.data_criacao.date).toLocaleString('pt-BR')}`;
            $('#detalhes_info').html(info);

            let historicoHtml = '<ul class="list-group">';
            res.historico.forEach(h => {
                historicoHtml += `<li class="list-group-item">
                    <strong>${h.TAGStatus}</strong> - Início: ${new Date(h.data_inicio.date).toLocaleString('pt-BR')}
                    <br><small>${h.descricao}</small></li>`;
            });
            historicoHtml += '</ul>';
            $('#detalhes_status_historico').html(historicoHtml);

            let footerHtml = '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>';
            if(res.pode_atender) {
                footerHtml += `<button type="button" class="btn btn-success btn-atender" data-ostag="${os_tag}">Atender</button>`;
            }
            $('#detalhes_footer').html(footerHtml);

            $('#modalDetalhesOSS').modal('show');
        }
    }, 'json');
  });

  $('#modalDetalhesOSS').on('click', '.btn-atender', function() {
      const os_tag = $(this).data('ostag');
      const tecnico_codigo = prompt("Informe seu código de técnico para iniciar o atendimento:");
      if (tecnico_codigo) {
          $.post('ordem_servico_actions.php', {action: 'attend_os', os_tag: os_tag, tecnico_codigo: tecnico_codigo}, function(res) {
              alert(res.mensagem || res.erro);
              if (res.sucesso) {
                  $('#modalDetalhesOSS').modal('hide');
                  carregarOSS();
              }
          }, 'json');
      }
  });

  carregarOSS();
});
</script>

</body>
</html>