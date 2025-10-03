<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* As suas regras de estilo para os cards permanecem */
        .card.oss-card { border-left-width: 5px; margin-bottom: 0; }
        .card.parado { border-left-color: #dc3545; }
        .card.normal { border-left-color: #2A4687; }
        .oss-card { transition: transform 0.2s, box-shadow 0.2s; height: 100%; }
        .oss-card:hover { transform: translateY(-3px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .oss-card-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(0,0,0,0.125); }
        .oss-card-title { font-weight: bold; font-size: 1.1rem; }
        .oss-card-os-tag { font-size: 0.9rem; color: #6c757d; }
        .oss-card-body { flex-grow: 1; }
        .oss-card-footer { border-top: 1px solid rgba(0,0,0,0.125); display: flex; justify-content: space-between; align-items: center; }

        /* --- MELHORIAS DE ESTILO PARA OS MODAIS --- */
        .form-section {
            padding: 1.25rem;
            border: 1px solid #dee2e6;
        }
        .form-section-title {
            color: #0d6efd;
            margin-bottom: 1rem !important;
            font-weight: 600; /* Corrigido de 60 para 600 */
        }
        .form-control-lg-custom, .form-select-lg-custom {
            padding: 0.8rem 1.1rem;
            font-size: 1.1rem;
            line-height: 1.5;
            border-radius: 8px;
        }
        .form-switch.fs-5 .form-check-input {
            height: 1.5rem;
            width: calc(2.5rem + 0.75rem);
        }
    </style>
</head>
<body>
    <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOSS">
            <i class="fas fa-plus-circle me-2"></i>Nova Ordem de Serviço
        </button>
    </div>

    <div id="lista-oss" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;"></div>

    <div class="modal fade" id="modalNovaOSS" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formNovaOSS">
                    <div class="modal-header"><h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Abrir Nova O.S.</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                    <div class="modal-body">
                        <div class="form-section">
                            <h6 class="form-section-title">Informações Básicas</h6>
                            <div class="row g-3">
                                <div class="col-md-6"><label class="form-label">Cód. Solicitante</label><input type="number" name="solicitante" class="form-control form-control-lg-custom" required></div>
                                <div class="col-md-6"><label class="form-label">TAG do Ativo</label><input type="text" name="ativo_tag" class="form-control form-control-lg-custom" required></div>
                            </div>
                        </div>
                        <div class="form-section">
                            <h6 class="form-section-title">Descrição do Problema</h6>
                            <textarea name="descricao_problema" class="form-control form-control-lg-custom" rows="4" placeholder="Descreva detalhadamente o problema..." required></textarea>
                            <div class="form-check form-switch fs-5 mt-3"><input class="form-check-input" type="checkbox" name="maquina_parada" value="1" id="maquina_parada"><label class="form-check-label" for="maquina_parada">Máquina parada?</label></div>
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
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Seguimento OS
                            <span id="osIdTitulo" class="badge bg-primary"></span>
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-3" id="edicaoOsBody"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="button" id="btnConcluirOS" class="btn btn-success">Concluir OS</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    (function($) { 
        const actionsUrl = 'paginas/OS/ordem_servico_actions.php'; 

        function carregarOSS() {
            $('#lista-oss').html('<div class="col-12 text-center p-5"><div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div><p class="mt-2">A carregar...</p></div>');
            
            $.get(actionsUrl, { action: 'get_open' }, function(res) {
                const container = $('#lista-oss');
                container.empty();
                if (Array.isArray(res) && res.length > 0) {
                    res.forEach(oss => {
                        const dataCriacao = oss.data_criacao && oss.data_criacao.date ? new Date(oss.data_criacao.date).toLocaleDateString('pt-BR') : 'N/A';
                        const cardClass = oss.maquina_parada == 1 ? 'parado' : 'normal';
                        const maquinaParadaBadge = oss.maquina_parada == 1 ? '<span class="badge bg-danger ms-2"><i class="fas fa-exclamation-triangle me-1"></i>MÁQUINA PARADA</span>' : '';
                        
                        const cardHtml = `
                        <div class="card oss-card ${cardClass}" data-os-id="${oss.os_tag}">
                            <div class="card-header oss-card-header"><div class="oss-card-title">${oss.ativo_tag}</div><div class="oss-card-os-tag">OS #${oss.os_tag}</div></div>
                            <div class="card-body oss-card-body"><p>${(oss.descricao_problema || '')}</p></div>
                            <div class="card-footer oss-card-footer"><div><span class="badge bg-info">${oss.status_atual}</span>${maquinaParadaBadge}</div><div class="text-muted"><i class="fas fa-calendar-alt me-1"></i> ${dataCriacao}</div></div>
                            <a href="#" class="stretched-link btn-mostrar-mais" data-os-id="${oss.os_tag}"></a>
                        </div>`;
                        container.append(cardHtml);
                    });
                } else {
                    container.html('<div class="col-12"><div class="alert alert-success text-center">Nenhuma Ordem de Serviço em aberto!</div></div>');
                }
            }, 'json').fail(function() {
                $('#lista-oss').html('<div class="col-12"><div class="alert alert-danger text-center">Erro de comunicação ao carregar Ordens de Serviço.</div></div>');
            });
        }

        $('#content-area').on('click', '.btn-mostrar-mais', function(e) {
            e.preventDefault();
            const osId = $(this).data('os-id');
            const modalBody = $('#edicaoOsBody');
            const edicaoModal = new bootstrap.Modal(document.getElementById('modalEdicaoOS'));
            $('#osIdTitulo').text(`#${osId}`);
            modalBody.html('<div class="text-center p-5"><div class="spinner-border"></div></div>');
            edicaoModal.show();

            $.get(actionsUrl, { action: 'get_details', os_id: osId }, function(details) {
                if (!details || details.sucesso === false) {
                    modalBody.html(`<div class="alert alert-danger">${details.mensagem || 'Não foi possível carregar os dados.'}</div>`);
                    return;
                }
                let statusOptions = (details.status_options || []).map(opt => `<option value="${opt}" ${opt === details.Status ? 'selected' : ''}>${opt}</option>`).join('');
                const dataSolicitacao = new Date(details.Data_Solicitacao.replace('T', ' ')).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });
                
                const formHtml = `
                    <input type="hidden" name="OS_ID" value="${details.OS_ID}">
                    <div class="form-section">
                        <h6 class="form-section-title">Detalhes da Solicitação</h6>
                        <div class="row g-3">
                            <div class="col-md-5"><label class="form-label">Ativo</label><input type="text" class="form-control form-control-lg-custom" name="Ativo_TAG" value="${details.Ativo_TAG || ''}"></div>
                            <div class="col-md-3"><label class="form-label">Solicitante</label><input type="text" class="form-control form-control-lg-custom" value="${details.Solicitante || ''}" readonly></div>
                            <div class="col-md-4"><label class="form-label">Data</label><input type="text" class="form-control form-control-lg-custom" value="${dataSolicitacao}" readonly></div>
                            <div class="col-12"><label class="form-label">Histórico do Problema</label><textarea class="form-control form-control-lg-custom" rows="4" readonly>${details.Descricao_Servico || ''}</textarea></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title">Atendimento</h6>
                       <div class="row g-3">
                            <div class="col-md-8"><label class="form-label">Status</label><select class="form-select form-select-lg-custom" name="Status" id="selectStatus">${statusOptions}</select></div>
                            <div class="col-md-4 d-flex align-items-end pb-2"><div class="form-check form-switch fs-5"><input class="form-check-input" type="checkbox" name="Maquina_Parada" value="1" id="maquina_parada_edit" ${details.Maquina_Parada == 1 ? 'checked' : ''}><label class="form-check-label" for="maquina_parada_edit">Máquina parada?</label></div></div>
                            <div class="col-12"><label class="form-label">Adicionar Novo Serviço ao Histórico</label><textarea class="form-control form-control-lg-custom" name="servico_realizado_descricao" rows="3" placeholder="Descreva o serviço executado..."></textarea></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title">Controle de Tempo</h6>
                       <div class="row g-3">
                            <div class="col-md-6"><label class="form-label">Início do Atendimento</label><input type="datetime-local" class="form-control form-control-lg-custom" name="Data_Inicio_Atendimento" value="${details.Data_Inicio_Atendimento || ''}"></div>
                            <div class="col-md-6"><label class="form-label">Fim do Atendimento</label><input type="datetime-local" class="form-control form-control-lg-custom" name="Data_Fim_Atendimento" id="dataFimAtendimento" value="${details.Data_Fim_Atendimento || ''}"></div>
                        </div>
                    </div>`;
                modalBody.html(formHtml);
            }, 'json');
        });

        function handleFormSubmit(form, button, action) {
            const originalButtonText = button.html();
            button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> A Guardar...');
            
            $.post(actionsUrl, form.serialize() + '&action=' + action)
                .done(function(res) {
                    alert(res.mensagem || "Ocorreu um erro.");
                    if (res.sucesso) {
                        form[0].reset();
                        carregarOSS();
                    }
                })
                .fail(function() {
                    alert("Erro de comunicação. Tente novamente.");
                })
                .always(function() {
                    window.location.reload();
                     $('.modal').modal('hide');
                     button.prop('disabled', false).html(originalButtonText);
                });
        }

        $('#content-area').on('submit', '#formNovaOSS', function(e) {
            e.preventDefault();
            handleFormSubmit($(this), $(this).find('button[type="submit"]'), 'create');
        });

        $('#content-area').on('submit', '#formEdicaoOS', function(e) {
            e.preventDefault();
            handleFormSubmit($(this), $('#modalEdicaoOS').find('button[type="submit"]'), 'update');
        });
        
        $('#content-area').on('click', '#btnConcluirOS', function(e) {
            e.preventDefault();
            if (!confirm("Tem a certeza que deseja concluir esta Ordem de Serviço?")) return;
            
            const form = $('#formEdicaoOS');
            
            form.find('#selectStatus').val('Concluída');
            const dataFimInput = form.find('#dataFimAtendimento');
            if (!dataFimInput.val()) {
                const now = new Date();
                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                dataFimInput.val(now.toISOString().slice(0,16));
            }
            
            handleFormSubmit(form, $(this), 'update');
        });

        carregarOSS();

    })(jQuery);
    </script>
</body>
</html>