<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ordens de Serviço</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="../../css/style.css">
    <style>
        /* OBSERVAÇÃO: A tag <head> foi removida deste seletor pois não é um elemento visualizável */
        header {
            height: 5.2rem;
        }
        .modal-content {
            max-width: 110rem;
            width: 100%;
            max-height: 90vh;
            display: flex;
            border: 10px;
        }
        .modal-body .bg-light {
            padding: 1.5rem;
            border-radius: 10px;
            border-left: 1px;
            justify-content: right;


        }
        .modal-body {

            margin-top: 1px;
            overflow-y: auto;
            margin-right: 0rem;
            justify-content: right;
           
        }
        form {
        display: table-column;
            gap: 1.5rem;


        }
    </style>
</head>
<body>

<div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNovaOSS"><i class="fas fa-plus-circle me-2"></i>Nova Ordem de Serviço</button>
</div>
<div id="lista-oss" style="gap: 1.5rem; display: flex;"></div>

<div class="modal fade" id="modalNovaOSS" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formNovaOSS">
                <div class="modal-header">
                    <h5 class="modal-title" ><i class="fas fa-plus-circle me-2"></i>Abrir Nova O.S.</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body bg-light" style="width: 50rem; borde-right:1px; justify-content: right; justify-items: right; height: 35rem; overflow-y: auto;">
                    <div class="form-section">
                        <h6 class="form-section-title">Informações Básicas</h6>
                        <div class="row g-3 pt-2">
                            <div class="col-md-6"><label class="form-label">Solicitante</label><select name="solicitante" class="form-select-lg-custom" required></select></div>
                            <div class="col-md-6"><label class="form-label">TAG do Ativo</label><select name="ativo_tag" class="form-select-lg-custom" required></select></div>
                        </div>
                    </div>
                    <div class="form-section">
                        <h6 class="form-section-title">Descrição do Problema</h6>
                        <div class="pt-2">
                            <textarea name="descricao_problema" class="form-control form-control-lg-custom" rows="4" placeholder="Descreva detalhadamente o problema..." required></textarea>
                            <div class="form-check form-switch fs-5 mt-3">
                                <input class="form-check-input" type="checkbox" name="maquina_parada" value="1" id="maquina_parada">
                                <label class="form-check-label" for="maquina_parada">Máquina parada?</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer" style="justify-content: right; top: 10rem">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Salvar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEdicaoOS" tabindex="-1" >
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="formEdicaoOS">
                <div class="modal-header" style="width: 50rem;"><h5 class="modal-title"><i class="fas fa-edit me-2"></i>Seguimento OS <span id="osIdTitulo" class="badge bg-primary"></span></h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body bg-light" id="edicaoOsBody" style="height: 40rem;"></div>
                <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button><button type="button" id="btnConcluirOS" class="btn btn-success">Concluir OS</button><button type="submit" class="btn btn-primary">Salvar Alterações</button></div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
                    const cardHtml = `<div class="card oss-card ${oss.maquina_parada == 1 ? 'parado' : 'normal'}" data-os-id="${oss.os_tag}">
                        <div class="card-header oss-card-header"><div class="oss-card-title">${oss.ativo_tag} - ${oss.nome_ativo || ''}</div><div class="oss-card-os-tag">OS #${oss.os_tag}</div></div>
                        <div class="card-body oss-card-body"><p>${(oss.descricao_problema || '').substring(0,120)}...</p></div>
                        <div class="card-footer oss-card-footer"><div><span class="badge bg-info">${oss.status_atual}</span> ${oss.maquina_parada == 1 ? '<span class="badge bg-danger ms-2"><i class="fas fa-exclamation-triangle me-1"></i>MÁQUINA PARADA</span>' : ''}</div><div class="text-muted"><i class="fas fa-calendar-alt me-1"></i> ${dataCriacao}</div></div>
                        <a href="#" class="stretched-link btn-mostrar-mais" data-os-id="${oss.os_tag}"></a>
                    </div>`;
                    container.append(cardHtml);
                });
            } else { container.html('<div class="col-12"><div class="alert alert-success text-center">Nenhuma Ordem de Serviço em aberto!</div></div>'); }
        }, 'json').fail(function() { $('#lista-oss').html('<div class="col-12"><div class="alert alert-danger text-center">Erro de comunicação ao carregar Ordens de Serviço.</div></div>'); });
    }

    function initSelect2(selector, parent, action, placeholder, formatFunc) {
        $(selector).select2({
            theme: 'bootstrap-5',
            dropdownParent: $(parent),
            placeholder: placeholder,
            ajax: {
                url: actionsUrl, type: "get", dataType: 'json', delay: 250,
                data: function (params) { return { action: action, searchTerm: params.term }; },
                processResults: function (response) { return { results: $.map(response, formatFunc) }; },
                cache: true
            }
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
            if (!details || details.sucesso === false) { modalBody.html(`<div class="alert alert-danger">${details.mensagem || 'Não foi possível carregar os dados.'}</div>`); return; }

            let statusOptions = (details.status_options || []).map(opt => `<option value="${opt}" ${opt === details.Status ? 'selected' : ''}>${opt}</option>`).join('');
            const dataSolicitacao = new Date(details.Data_Solicitacao.replace('T', ' ')).toLocaleString('pt-BR', { dateStyle: 'short', timeStyle: 'short' });

            let historicoHtml = '';
            if (details.historico_status && details.historico_status.length > 0) {
                details.historico_status.forEach(item => {
                    historicoHtml += `<li class="history-log-item"><div class="log-header"><span><i class="fas fa-tag me-2"></i>${item.TAGStatus}</span><small class="text-muted">${item.data_inicio}</small></div><div class="log-body"><small>Por: ${item.abriu_codigo || 'Sistema'}. Desc: ${item.descricao || ''}</small></div></li>`;
                });
            }

            const formHtml = `
                <input type="hidden" name="OS_ID" value="${details.OS_ID}">
                <div class="form-section">
                    <h6 class="form-section-title">Detalhes da Solicitação</h6>
                    <div class="row g-3 pt-2">
                        <div class="col-md-5"><label class="form-label">Ativo</label><input type="text" class="form-control form-control-lg-custom" name="Ativo_TAG" value="${details.Ativo_TAG || ''}" readonly></div>
                        <div class="col-md-3"><label class="form-label">Solicitante</label><input type="text" class="form-control form-control-lg-custom" value="${details.Solicitante || ''}" readonly></div>
                        <div class="col-md-4"><label class="form-label">Data</label><input type="text" class="form-control form-control-lg-custom" value="${dataSolicitacao}" readonly></div>
                        <div class="col-12"><label class="form-label">Problema Original</label><textarea class="form-control form-control-lg-custom" rows="3" readonly>${details.Descricao_Servico || ''}</textarea></div>
                    </div>
                </div>
                <div class="form-section">
                    <h6 class="form-section-title">Novo Atendimento / Mudança de Status</h6>
                     <div class="row g-3 pt-2">
<div class="col-md-4"><label class="form-label">Cód. Técnico</label><input type="number" name="tecnico_codigo" class="form-control form-control-lg-custom" required></div>                        <div class="col-md-4"><label class="form-label">Novo Status</label><select class="form-select form-select-lg-custom" name="Status" id="selectStatus">${statusOptions}</select></div>
                        <div class="col-md-4 d-flex align-items-end pb-2"><div class="form-check form-switch fs-5"><input class="form-check-input" type="checkbox" name="Maquina_Parada" value="1" id="maquina_parada_edit" ${details.Maquina_Parada == 1 ? 'checked' : ''}><label class="form-check-label" for="maquina_parada_edit">Máquina parada?</label></div></div>
                        <div class="col-12"><label class="form-label">Descrição do Atendimento</label><textarea class="form-control form-control-lg-custom" name="servico_realizado_descricao" rows="3" placeholder="Descreva o serviço executado..."></textarea></div>
                    </div>
                </div>
                <div class="form-section">
                     <h6 class="form-section-title">Histórico Auditável de Status</h6>
                     <ul class="history-log pt-2">${historicoHtml}</ul>
                </div>`;
            modalBody.html(formHtml);

            initSelect2('#formEdicaoOS select[name="tecnico_codigo"]', '#modalEdicaoOS', 'get_tecnicos', 'Selecione um técnico...', item => ({ id: item.codigo, text: `${item.nome} (${item.codigo})` }));
        }, 'json');
    });

    function handleFormSubmit(form, button) {
        const action = form.attr('id') === 'formNovaOSS' ? 'create' : 'update';
        const originalButtonText = button.html();
        button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> A Guardar...');

        $.post(actionsUrl, form.serialize() + '&action=' + action)
            .done(function(res) {
                if (res.sucesso) {
                    window.location.reload('index.php');
                    carregarOSS();
                } else {
                    alert("Erro: " + (res.mensagem || "Ocorreu uma falha."));
                }
            })
            .fail(function() { alert("Erro de comunicação."); })
            .always(function() { button.prop('disabled', false).html(originalButtonText); });
    }

    $('#content-area').on('submit', '#formNovaOSS', function(e) {
        e.preventDefault();
        handleFormSubmit($(this), $(this).find('button[type="submit"]'));
    });

    $('#content-area').on('submit', '#formEdicaoOS', function(e) {
        e.preventDefault();
        handleFormSubmit($(this), $('#modalEdicaoOS').find('button[type="submit"]'));
    });

    $('#content-area').on('click', '#btnConcluirOS', function() {
        if (!$('#formEdicaoOS input[name=tecnico_codigo]').val()) {
            alert('Por favor, informe o Técnico Responsável antes de concluir.');
            return;
        }
        if (!confirm("Tem a certeza que deseja concluir esta Ordem de Serviço?")) return;
        const form = $('#formEdicaoOS');
        form.find('#selectStatus').val('Concluída');
        form.trigger('submit');
    });

    $('#modalNovaOSS').on('shown.bs.modal', function () {
         initSelect2('#formNovaOSS select[name="ativo_tag"]', '#modalNovaOSS', 'get_ativos', 'Selecione um Ativo', item => ({ id: item.Ativo_TAG, text: `${item.Ativo_TAG} - ${item.Modelo}` }));
        initSelect2('#formNovaOSS select[name="solicitante"]', '#modalNovaOSS', 'get_usuarios', 'Selecione um Solicitante', item => ({ id: item.codigo, text: `${item.nome} (${item.codigo})` }));   });

    carregarOSS();

})(jQuery);
</script>
</body>
</html>