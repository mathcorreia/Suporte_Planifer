<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    
    .card.oss-card { border-left-width: 5px; margin-bottom: 0; padding: 1.5rem; width: auto; }
    .card.parado { border-left-color: #dc3545; }
    .card.normal { border-left-color: #2A4687; }

    .oss-card {
        transition: transform 0.2s, box-shadow 0.2s;
        height: 100%;
        border-left: 4px solid #0d6efd;
    }
    
    .oss-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .oss-card.parado {
        border-left-color: #dc3545;
    }
    
    .oss-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        border-bottom: 1px solid rgba(0,0,0,0.125);
    }
    
    .oss-card-title {
        font-weight: bold;
        font-size: 1.1rem;
    }
    
    .oss-card-os-tag {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .oss-card-body {
        padding: 1rem;
        flex-grow: 1;
    }
    
    .oss-card-footer {
        padding: 1rem;
        border-top: 1px solid rgba(0,0,0,0.125);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .form-section {
        margin-bottom: 1.5rem;
        padding: 1rem;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        border-left: 3px solid #0d6efd;
    }
    
    .form-section-title {
        color: #0d6efd;
        margin-bottom: 0.5rem;
        font-weight: 60;
        font-size: 0.95rem;
        display: flex;
        align-items: center;
    }
    
    .form-section-title i {
        font-size: 0.5rem;
        width: 20px;
    }
    
    .form-label {
        font-weight: 500;
        margin-bottom: 0.4rem;
        font-size: 0.9rem;
    }
    
    .form-control, .form-select {
        font-size: 0.9rem;
        padding: 0.5rem 0.75rem;
    }
    
    .form-control-lg {
        min-height: 80px;
        font-size: 0.9rem;
    }
    
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
    
    .compact-form .row {
        margin-bottom: 0.5rem;
    }
    
    .compact-form .col-md-6,
    .compact-form .col-md-4,
    .compact-form .col-md-3 {
        margin-bottom: 0.5rem;
    }
    
    .modal-body {
        max-height: 75vh;
        overflow-y: auto;
    }
    
    .modal-header {
        padding: 1rem 1.5rem;
    }
    
    .modal-footer {
        padding: 1rem 1.5rem;
    }
    
    .compact-checkbox {
        margin: 0.5rem 0;
    }
    
    .compact-checkbox .form-check-input {
        margin-right: 0.5rem;
    }
</style>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOSS">
        <i class="fas fa-plus-circle me-2"></i>Nova Ordem de Serviço
    </button>
</div>

<div id="lista-oss" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 0.5rem;"></div>

<div class="modal fade" id="modalNovaOSS" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formNovaOSS">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Abrir Nova O.S.</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-section compact-form">
                        <h6 class="form-section-title"><i class="fas fa-info-circle"></i>Informações Básicas</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Cód. Solicitante</label>
                                <input type="number" name="solicitante" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">TAG do Ativo</label>
                                <input type="text" name="ativo_tag" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section compact-form">
                        <h6 class="form-section-title"><i class="fas fa-exclamation-triangle"></i>Descrição do Problema</h6>
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label">Problema Reportado</label>
                                <textarea name="descricao_problema" class="form-control form-control-lg" rows="3" placeholder="Descreva detalhadamente o problema reportado..." required></textarea>
                            </div>
                            <div class="col-12 compact-checkbox">
                                <div class="form-check form-switch d-flex align-items-center">
                                    <input class="form-check-input me-2" type="checkbox" name="maquina_parada" value="1" id="maquina_parada">
                                    <label class="form-check-label" for="maquina_parada">Máquina parada?</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoOS" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="formEdicaoOS">
                <div class="modal-header py-2">
                    <h5 class="modal-title mb-0">
                        <i class="fas fa-edit me-2"></i>Seguimento OS <span id="osIdTitulo"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3" id="edicaoOsBody"></div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btnConcluirOS" class="btn btn-success">Concluir OS</button>
                    <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    function carregarOSS() {
        $.post('paginas/OS/ordem_servico_actions.php', { action: 'get_open' }, function(res) {
            const container = $('#lista-oss');
            container.empty();
            if (Array.isArray(res) && res.length > 0) {
                res.forEach(oss => {
                    const dataCriacao = oss.data_criacao && oss.data_criacao.date ? new Date(oss.data_criacao.date).toLocaleDateString('pt-BR') : 'N/A';
                    const cardClass = oss.maquina_parada == 1 ? 'parado' : 'normal';
                    const maquinaParadaBadge = oss.maquina_parada == 1 ? '<span class="badge bg-danger ms-2"><i class="fas fa-exclamation-triangle me-1"></i>MÁQUINA PARADA</span>' : '';
                    
                    const cardHtml = `
                    <div class="card oss-card ${cardClass}" data-os-id="${oss.os_tag}">
                        <div class="oss-card-header">
                            <div class="oss-card-title">${oss.ativo_tag}</div>
                            <div class="oss-card-os-tag">OS #${oss.os_tag}</div>
                        </div>
                        <div class="oss-card-body">
                            <p>${(oss.descricao_problema || '')}</p>
                        </div>
                        <div class="oss-card-footer">
                            <div><span class="badge bg-info">${oss.status_atual}</span>${maquinaParadaBadge}</div>
                            <div class="text-muted"><i class="fas fa-calendar-alt me-1"></i> ${dataCriacao}</div>
                        </div>
                        <a href="#" class="stretched-link btn-mostrar-mais" data-os-id="${oss.os_tag}"></a>
                    </div>`;
                    container.append(cardHtml);
                });
            } else if (res && res.erro) {
                // Se o backend retornar um erro específico
                let errorMessage = 'Erro ao carregar Ordens de Serviço: ' + res.erro;
                if (res.details) {
                    errorMessage += '<br><pre>' + JSON.stringify(res.details, null, 2) + '</pre>';
                }
                container.html('<div class="alert alert-danger text-center">' + errorMessage + '</div>');
            } else {
                container.html('<div class="alert alert-success text-center">Nenhuma Ordem de Serviço em aberto!</div>');
            }
        }, 'json')
        .fail(function(jqXHR, textStatus, errorThrown) {
            $('#lista-oss').html('<div class="alert alert-danger text-center">Erro de comunicação com o servidor: ' + textStatus + ' - ' + errorThrown + '</div>');
        });
    }

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
            
            const dataSolicitacaoFormatada = details.Data_Solicitacao ? 
                new Date(details.Data_Solicitacao.replace('T', ' ')).toLocaleString('pt-BR', { 
                    dateStyle: 'short', 
                    timeStyle: 'short'
                }) : 'N/A';
            
          
            const formHtml = `
                <input type="hidden" name="OS_ID" value="${details.OS_ID}">
                
                <div class="form-section compact-form">
                    <h6 class="form-section-title"><i class="fas fa-info-circle"></i>Detalhes da Solicitação</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Ativo TAG</label>
                            <input type="text" class="form-control" name="Ativo_TAG" value="${details.Ativo_TAG || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Cód. Solicitante</label>
                            <input type="text" class="form-control" value="${details.Solicitante || ''}" readonly>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data da Solicitação</label>
                            <input type="text" class="form-control" value="${dataSolicitacaoFormatada}" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Problema Reportado</label>
                            <textarea class="form-control" rows="2" readonly>${details.Descricao_Servico || ''}</textarea>
                        </div>
                    </div>
                </div>

                <div class="form-section compact-form">
                    <h6 class="form-section-title"><i class="fas fa-user-cog"></i>Identificação do Técnico</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Cód. do Técnico</label>
                            <input type="number" class="form-control" name="tecnico_codigo" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Alterar Status para</label>
                            <select class="form-select" name="Status" id="selectStatus">${statusOptions}</select>
                        </div>
                        <div class="col-12 compact-checkbox">
                            <div class="form-check form-switch d-flex align-items-center">
                                <input class="form-check-input me-2" type="checkbox" name="Maquina_Parada" value="1" id="maquina_parada_edit" ${details.Maquina_Parada == 1 ? 'checked' : ''}>
                                <label class="form-check-label" for="maquina_parada_edit">Máquina parada?</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-section compact-form">
                    <h6 class="form-section-title"><i class="fas fa-clock"></i>Controle de Tempo</h6>
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Início do Atendimento</label>
                            <input type="datetime-local" class="form-control" name="Data_Inicio_Atendimento" id="dataInicioAtendimento" value="${details.Data_Inicio_Atendimento || ''}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fim do Atendimento</label>
                            <input type="datetime-local" class="form-control" name="Data_Fim_Atendimento" id="dataFimAtendimento" value="${details.Data_Fim_Atendimento || ''}">
                        </div>
                    </div>
                </div>

                <div class="form-section compact-form">
                    <h6 class="form-section-title"><i class="fas fa-tasks"></i>Serviço Realizado</h6>
                    <div class="row g-2">
                        <div class="col-12">
                            <label class="form-label">Descrição do Serviço Realizado</label>
                            <textarea class="form-control form-control-lg" name="servico_realizado_descricao" rows="3" placeholder="Descreva detalhadamente o serviço que foi executado..." required></textarea>
                        </div>
                    </div>
                </div>
            `;
            modalBody.html(formHtml);
        }, 'json');
    });

    $('#content-area').on('click', '#btnConcluirOS', function() {
        if (!confirm("Tem a certeza que deseja concluir esta Ordem de Serviço?")) { return; }
        
        const form = $('#formEdicaoOS');
        form.find('#selectStatus').val('Concluída');
        
        const dataFimInput = form.find('#dataFimAtendimento');
        if (!dataFimInput.val()) {
            const now = new Date();
            now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
            dataFimInput.val(now.toISOString().slice(0,16));
        }
        
        form.trigger('submit'); 
    });

    $('#content-area').on('submit', '#formEdicaoOS', function(e) {
        e.preventDefault();
        const form = $(this);
        const osId = form.find('input[name="OS_ID"]').val();

        $.ajax({
            url: 'paginas/OS/ordem_servico_actions.php',
            type: 'POST',
            data: form.serialize() + '&action=update',
            dataType: 'json',
            success: function(res) {
                if (res.sucesso) {
                    alert(res.mensagem);
                    $('#modalEdicaoOS').modal('hide'); 

                    if (res.concluido) {
                        $(`.oss-card[data-os-id="${osId}"]`).fadeOut(500, function() {
                            $(this).remove();
                            if ($('#lista-oss').children().length === 0) {
                                $('#lista-oss').html('<div class="alert alert-success text-center">Nenhuma Ordem de Serviço em aberto!</div>');
                            }
                        });
                    } else {
                        carregarOSS(); 
                    }
                } else {
                    alert("Erro: " + (res.mensagem || "Ocorreu uma falha."));
                }
            }
        });
    });

    $('#content-area').on('submit', '#formNovaOSS', function(e) {
        e.preventDefault();
        $.ajax({ 
            url: 'paginas/OS/ordem_servico_actions.php', 
            type: 'POST', 
            data: $(this).serialize() + '&action=create', 
            dataType: 'json',
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