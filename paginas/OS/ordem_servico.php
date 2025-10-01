<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    /* Estilos para garantir a compatibilidade entre os dois CSS */
    .card.oss-card { border-left-width: 5px; margin-bottom: 0; padding: 1.5rem; }
    .card.parado { border-left-color: #dc3545; }
    .card.normal { border-left-color: #2A4687; }
</style>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <button data-bs-toggle="modal" data-bs-target="#modalNovaOSS">Nova Ordem de Serviço</button>
</div>
<div id="lista-oss" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
    </div>

<div class="modal fade" id="modalNovaOSS" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form id="formNovaOSS">
        <div class="modal-header"><h5 class="modal-title">Abrir Nova O.S.</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
            <div class="row g-3">
              <div class="col-md-4"><label class="form-label">Cód. Solicitante</label><input type="number" name="solicitante" class="form-control" required></div>
              <div class="col-md-8"><label class="form-label">TAG do Ativo</label><input type="text" name="ativo_tag" class="form-control" required></div>
              <div class="col-12"><label class="form-label">Descrição do Problema</label><textarea name="descricao_problema" class="form-control" rows="3" required></textarea></div>
              <div class="col-12"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="maquina_parada" value="1" id="maquina_parada"><label class="form-check-label" for="maquina_parada">Máquina parada?</label></div></div>
            </div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary">Salvar</button></div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEdicaoOS" tabindex="-1">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <form id="formEdicaoOS">
        <div class="modal-header">
          <h5 class="modal-title">Seguimento da Ordem de Serviço <span id="osIdTitulo"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="edicaoOsBody">
          </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Salvar Alterações</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
  function carregarOSS() {
      // O caminho agora é relativo ao index.php
      $.post('paginas/OS/ordem_servico_actions.php', { action: 'get_open' }, function(res) {
          const container = $('#lista-oss');
          container.empty();
          if (Array.isArray(res) && res.length > 0) {
              res.forEach(oss => {
                  const dataCriacao = oss.data_criacao && oss.data_criacao.date ? new Date(oss.data_criacao.date).toLocaleString('pt-BR') : 'Data indisponível';
                  const cardClass = oss.maquina_parada == 1 ? 'parado' : 'normal';
                  const maquinaParadaBadge = oss.maquina_parada == 1 ? '<span class="badge bg-danger ms-2">MÁQUINA PARADA</span>' : '';
                  
                  const cardHtml = `<div class="card oss-card ${cardClass}">
                      <div class="card-body">
                          <h5 style="border:none; margin-bottom:0.5rem">${oss.ativo_tag}</h5>
                          <h6><span class="badge bg-info">${oss.status_atual}</span>${maquinaParadaBadge}</h6>
                          <h6 class="text-muted" style="font-family: 'Segoe UI', sans-serif;">OS: ${oss.os_tag}</h6>
                          <p style="font-family: 'Segoe UI', sans-serif;">${(oss.descricao_problema || '').substring(0, 100)}...</p>
                          <p style="margin-top: 1rem;"><small class="text-muted" style="font-family: 'Segoe UI', sans-serif;">Aberto em: ${dataCriacao}</small></p>
                          <a href="#" style="text-decoration:none; font-weight:bold;" class="card-link btn-mostrar-mais" data-os-id="${oss.os_tag}">Mostrar mais...</a>
                      </div>
                  </div>`;
                  container.append(cardHtml);
              });
          } else {
              container.html('<div class="alert alert-secondary">Nenhuma Ordem de Serviço em aberto.</div>');
          }
      }, 'json');
  }

  // Evento para o botão "Mostrar mais..." (agora escutando a partir do #content-area)
  $('#content-area').on('click', '.btn-mostrar-mais', function(e) {
      e.preventDefault();
      const osId = $(this).data('os-id');
      const modalBody = $('#edicaoOsBody');
      const edicaoModal = new bootstrap.Modal(document.getElementById('modalEdicaoOS'));

      $('#osIdTitulo').text(`#${osId}`);
      modalBody.html('<p class="text-center">A carregar dados da OS...</p>');
      edicaoModal.show();

      $.get(`paginas/OS/ordem_servico_actions.php?action=get_details&os_id=${osId}`, function(details) {
          if (details.erro) {
              modalBody.html(`<div class="alert alert-danger">${details.erro}</div>`);
              return;
          }
          let statusOptions = '';
          if(details.status_options){
              details.status_options.forEach(opt => {
                  statusOptions += `<option value="${opt}" ${opt === details.Status ? 'selected' : ''}>${opt}</option>`;
              });
          }
          const dataSolicitacaoFormatada = details.Data_Solicitacao ? new Date(details.Data_Solicitacao.replace('T', ' ')).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short'}) : 'N/A';
          const formHtml = `<input type="hidden" name="OS_ID" value="${details.OS_ID}"><div class="row g-3">
                <div class="col-md-4"><label class="form-label">Ativo TAG</label><input type="text" class="form-control" name="Ativo_TAG" value="${details.Ativo_TAG || ''}"></div>
                <div class="col-md-4"><label class="form-label">Solicitante</label><input type="number" class="form-control" name="Solicitante" value="${details.Solicitante || ''}"></div>
                <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="Status">${statusOptions}</select></div>
                <div class="col-md-4"><label class="form-label">Data Solicitação (Fixo)</label><input type="text" class="form-control" value="${dataSolicitacaoFormatada}" readonly></div>
                <div class="col-md-4"><label class="form-label">Início Atendimento</label><input type="datetime-local" class="form-control" name="Data_Inicio_Atendimento" value="${details.Data_Inicio_Atendimento || ''}"></div>
                <div class="col-md-4"><label class="form-label">Fim Atendimento</label><input type="datetime-local" class="form-control" name="Data_Fim_Atendimento" value="${details.Data_Fim_Atendimento || ''}"></div>
                <div class="col-md-12"><label class="form-label">Descrição do Serviço</label><textarea class="form-control" name="Descricao_Servico" rows="3">${details.Descricao_Servico || ''}</textarea></div>
                <div class="col-md-6 d-flex align-items-center pt-3">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="Maquina_Parada" value="1" ${details.Maquina_Parada == 1 ? 'checked' : ''}><label class="form-check-label">Máquina parada?</label></div>
                </div>
            </div>`;
          modalBody.html(formHtml);
      }, 'json');
  });

  // Evento para submeter o formulário de EDIÇÃO (agora escutando a partir do #content-area)
  $('#content-area').on('submit', '#formEdicaoOS', function(e) {
      e.preventDefault();
      $.ajax({
          url: 'paginas/OS/ordem_servico_actions.php', type: 'POST', data: $(this).serialize() + '&action=update', dataType: 'json',
          success: function(res) {
              if (res.sucesso) {
                  alert(res.mensagem);
                  $('#modalEdicaoOS').modal('hide');
                  carregarOSS();
              } else {
                  alert("Erro: " + (res.mensagem || "Ocorreu uma falha."));
              }
          }
      });
  });

  // Evento para submeter o formulário de CRIAÇÃO (agora escutando a partir do #content-area)
  $('#content-area').on('submit', '#formNovaOSS', function(e) {
      e.preventDefault();
      $.ajax({ url: 'paginas/OS/ordem_servico_actions.php', type: 'POST', data: $(this).serialize() + '&action=create', dataType: 'json',
          success: function(res) {
              if (res.sucesso) {
                  alert(res.mensagem);
                  $('#modalNovaOSS').modal('hide');
                  $('#formNovaOSS')[0].reset();
                  carregarOSS();
              } else {
                  alert(res.mensagem || res.erro || "Ocorreu um erro desconhecido.");
              }
          }
      });
  });

  carregarOSS();
});
</script>