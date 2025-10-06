<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações de Melhoria - Sistema de Gestão</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="../../css/style.css">

    <style>
        .modal-overlay { 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.6); 
            display: none; 
            align-items: center; 
            justify-content: center; 
            z-index: 1000; 
        }

        .modal-container { 
            background: #fff; 
            border-radius: 15px; 
            box-shadow: 0 5px 25px rgba(0,0,0,0.2); 
            width: 100%; 
            max-width: 800px; 
            max-height: 90vh; 
            display: flex; 
            flex-direction: column; 
            overflow: hidden;
         }

        .modal-container.modal-xl { 
            max-width: 1200px; 
        }

        .modal-header { 
            padding: 1rem 1.5rem; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            border-bottom: 1px solid #eee; 
        }

        .btn-close { background: transparent; border: none; font-size: 1.5rem; cursor: pointer; }
        .modal-body { padding: 1.5rem; overflow-y: auto; background-color: #fbfcfe; }
        .modal-footer { padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 1rem; border-top: 30rem #ffffffff solid ; height: 100px;}
        body.modal-open { overflow: hidden; }
        .loader-container { padding: 5rem; text-align: center; }
        .loader { border: 5px solid #f3f3f3; border-top: 5px solid #2A4687; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 1rem; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        input[readonly], textarea[readonly] {
            background-color: #e9ecef !important;
            cursor: not-allowed;
            opacity: 0.8;
        }

        .badge { padding: 0.35em 0.65em; font-size: .8em; font-weight: 700; border-radius: 8px; text-transform: uppercase; }
        .badge-status-sugerida { background-color: #6c757d; color: white; }
        .badge-status-em_análise { background-color: #ffc107; color: black; }
        .badge-status-aprovada { background-color: #0d6efd; color: white; }
        .badge-status-rejeitada { background-color: #dc3545; color: white; }
        .badge-status-implementada { background-color: #198754; color: white; } 
        
        .oss-card.status-implementada { border-left-color: #198754; }

        .badge-prioridade-baixa { border: 1px solid #6c757d; color: #6c757d; }
        .badge-prioridade-media { border: 1px solid #ffc107; color: #ffc107; }
        .badge-prioridade-alta { border: 1px solid #dc3545; color: #dc3545; }

        /* Ajustes nos botões */
        #content-area .btn { padding: 10px 20px; font-size: 0.95rem; }
        .modal-footer .btn { padding: 8px 18px; font-size: 0.9rem; }
     
    </style>
</head>
<body>
    <header>
        <img src="../../css/logo.png" alt="Logo do Sistema" class="img-logo" style="height: auto; border: 0px; left: 38rem; ">
        <h1>Sistema de Gestão de Manutenção</h1>
</header>
    <nav id="main-nav"><a href="//fserver/intranet"><i class="fas fa-home"></i>Voltar Para intranet</a></nav>

    <main id="content-area" class="container">
            <img src="../../css/logo_diag.png" class="img-fundo" style="heigth: -200rem; align-items: center;">

        <div style="display:flex; justify-content: space-between; align-items: center; ">
            <h2><i class="fas fa-lightbulb" style=" align-items: center; text-align: center;"></i>Solicitações de Melhoria</h2>
            <div>
                <button id="btn-gerar-relatorio" class="btn btn-secondary"><i class="fas fa-file-alt me-2"></i>Gerar Relatório</button>
                <button id="btn-abrir-modal-nova-melhoria" class="btn"><i class="fas fa-plus-circle"></i>Nova Solicitação</button>
            </div>
        </div>
        <div class="card">
             <div id="lista-melhorias"></div>
        </div>
    </main>

    <div class="modal-overlay" id="modalMelhoriaOverlay">
        <div class="modal-container" id="modalMelhoria">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMelhoriaTitle"></h5>
                <button type="button" class="btn-close" data-dismiss-modal>&times;</button>
            </div>
            <form id="formMelhoria">
                <input type="hidden" name="melhoria_id">
                <div class="modal-body">
                    <div class="form-section">
                        <div class="form-gr"><label>Título da Melhoria</label><input type="text" name="titulo" id="titulo" required></div>
                        <div class="form-gr" style="grid-column: 1 / -1;"><label>Descrição Detalhada</label><textarea name="descricao_melhoria" id="descricao_melhoria" rows="5" required></textarea></div>
                        <div class="form-gr"><label>Área / Setor Afetado</label><input type="text" name="area_afetada" id="area_afetada" required></div>
                        <div class="form-gr"><label>Solicitante</label><input type="text" name="solicitante" id="solicitante" required></div>
                        <div class="form-gr"><label>Tipo de Melhoria</label><select name="tipo_melhoria" id="tipo_melhoria" required><option value="" disabled selected>Selecione...</option><option value="Processo">Processo</option><option value="Produto">Produto</option><option value="Segurança">Segurança</option><option value="Qualidade">Qualidade</option><option value="Outro">Outro</option></select></div>
                        <div class="form-group"><label>Prioridade</label><select name="prioridade" id="prioridade" required><option value="Baixa">Baixa</option><option value="Media" selected>Média</option><option value="Alta">Alta</option></select></div>
                        <div class="form-group" id="status-group" style="display: none;"><label for="status">Status</label><select name="status" id="status" required><option value="Sugerida">Sugerida</option><option value="Em Análise">Em Análise</option><option value="Aprovada">Aprovada</option><option value="Rejeitada">Rejeitada</option><option value="Implementada">Implementada</option></select></div>
                    </div>
                </div>
                <div class="modal-footer" id="modalMelhoriaFooter">
                    </div>
            </form>
        </div>
    </div>
    
    <div class="modal-overlay" id="modalRelatorioOverlay">
        <div class="modal-container modal-xl">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-file-alt"></i> Relatório de Melhorias</h5>
                <button type="button" class="btn-close" data-dismiss-modal>&times;</button>
            </div>
            <div class="modal-body">
                <table id="tabelaRelatorio" class="display" style="width:100%">
                    <thead><tr><th>ID</th><th>Título</th><th>Status</th><th>Prioridade</th><th>Solicitante</th><th>Data Criação</th></tr></thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    
    <footer><p>Planifer Ferramentaria e Estamparia Ltda. <br>
        All Rights Reserved © 2025</p></footer>

     <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <script>
    $(document).ready(function() {
        const actionsUrl = 'melhorias_actions.php'; 
        let tabelaRelatorio;

        function openModal(modalId) { $(`#${modalId}Overlay`).css('display', 'flex'); $('body').addClass('modal-open'); }
        function closeModal() { $('.modal-overlay').hide(); $('body').removeClass('modal-open'); }
        $(document).on('click', '[data-dismiss-modal], .modal-overlay', function(e) { if (e.target === this) closeModal(); });
        $(document).on('keydown', function(e) { if (e.key === "Escape") closeModal(); });

        function carregarMelhorias() {
            $('#lista-melhorias').html('<div class="loader-container"><div class="loader"></div></div>');
            $.get(actionsUrl, { action: 'get_open' }, function(res) {
                const container = $('#lista-melhorias');
                container.empty();
                if (Array.isArray(res) && res.length > 0) {
                    res.forEach(item => {
                        const statusClass = (item.status || '').toLowerCase().replace(' ', '_');
                        const cardHtml = `<div class="card oss-card status-${statusClass}" data-id="${item.id}"><div class="oss-card-header"><div class="oss-card-title">${item.titulo}</div><div class="oss-card-os-tag">#${String(item.id).padStart(4, '0')}</div></div><div class="oss-card-body"><span class="badge badge-status-${statusClass}">${item.status}</span> <span class="badge badge-prioridade-${(item.prioridade || '').toLowerCase()}">${item.prioridade}</span></div><div class="oss-card-footer"><span><i class="fas fa-user"></i> ${item.solicitante}</span><span><i class="fas fa-calendar-alt"></i> ${item.data_criacao}</span></div><a href="#" class="stretched-link btn-ver-detalhes" data-id="${item.id}"></a></div>`;
                        container.append(cardHtml);
                    });
                } else {
                    container.html('<p style="text-align:center;">Nenhuma solicitação de melhoria em aberto.</p>');
                }
            }, 'json');
        }

        function setFormReadOnly(isReadOnly) {
            const form = $('#formMelhoria');
            form.find('input[name="titulo"], textarea[name="descricao_melhoria"], input[name="area_afetada"], input[name="solicitante"], select[name="tipo_melhoria"], select[name="prioridade"]').prop('readonly', isReadOnly).prop('disabled', isReadOnly);
        }

        $('#btn-abrir-modal-nova-melhoria').on('click', function() {
            const form = $('#formMelhoria');
            form[0].reset();
            form.find('input[name="melhoria_id"]').val('');
            setFormReadOnly(false); // Garante que os campos estão editáveis
            $('#status-group').hide(); // Esconde o campo de status na criação
            $('#modalMelhoriaTitle').html('<i class="fas fa-plus-circle"></i> Nova Solicitação de Melhoria');
            $('#modalMelhoriaFooter').html('<button type="button" class="btn btn-secondary" data-dismiss-modal>Cancelar</button><button type="submit" class="btn">Salvar Solicitação</button>');
            openModal('modalMelhoria');
        });

        $('#lista-melhorias').on('click', '.btn-ver-detalhes', function(e) {
            e.preventDefault();
            const id = $(this).data('id');
            $.get(actionsUrl, { action: 'get_details', id: id }, function(data) {
                if(data) {
                    const form = $('#formMelhoria');
                    form[0].reset();
                    setFormReadOnly(true); // Bloqueia os campos
                    $('#status-group').show(); // Mostra o campo de status

                    form.find('input[name="melhoria_id"]').val(data.melhoria_id);
                    form.find('input[name="titulo"]').val(data.titulo);
                    form.find('textarea[name="descricao_melhoria"]').val(data.descricao_melhoria);
                    form.find('input[name="area_afetada"]').val(data.area_afetada);
                    form.find('input[name="solicitante"]').val(data.solicitante);
                    form.find('select[name="tipo_melhoria"]').val(data.tipo_melhoria);
                    form.find('select[name="prioridade"]').val(data.prioridade);
                    form.find('select[name="status"]').val(data.status);
                    
                    $('#modalMelhoriaTitle').html('<i class="fas fa-eye"></i> Detalhes da Solicitação #' + String(data.melhoria_id).padStart(4, '0'));
                    $('#modalMelhoriaFooter').html('<button type="button" class="btn btn-secondary" data-dismiss-modal>Fechar</button><button type="button" id="btnConcluirMelhoria" class="btn btn-success">Concluir</button><button type="submit" class="btn">Atualizar Status</button>');
                    openModal('modalMelhoria');
                }
            }, 'json');
        });
        
        // Ação do botão Concluir (usa delegação de evento)
        $(document).on('click', '#btnConcluirMelhoria', function() {
            if (confirm("Tem certeza que deseja marcar esta melhoria como 'Implementada'?")) {
                $('#status').val('Implementada');
                $('#formMelhoria').submit();
            }
        });

        $('#formMelhoria').submit(function(e) {
            e.preventDefault();
            const button = $(this).find('button[type="submit"]');
            const originalButtonText = button.html();
            button.prop('disabled', true).text('Salvando...');
            $.post(actionsUrl, $(this).serialize() + '&action=save')
                .done(function(res) {
                     if (res.sucesso) {
                        alert(res.mensagem);
                        closeModal();
                        carregarMelhorias();
                     } else { alert("Erro: " + (res.mensagem || "Falha.")); }
                })
                .fail(() => alert("Erro de comunicação."))
                .always(() => button.prop('disabled', false).html(originalButtonText));
        });

         $('#btn-gerar-relatorio').on('click', function() {
            openModal('modalRelatorio');
            
            if (!$.fn.DataTable.isDataTable('#tabelaRelatorio')) {
                tabelaRelatorio = $('#tabelaRelatorio').DataTable({
                    responsive: true,
                    dom: 'Bfrtip',
                    buttons: [
                        { extend: 'copy', text: '<i class="fas fa-copy"></i> Copiar' },
                        { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', title: 'Relatorio_Melhorias' },
                        { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', title: 'Relatório de Melhorias' },
                        { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir' }
                    ],
                    language: { 
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json',
                        buttons: {
                            copyTitle: 'Copiado',
                            copySuccess: { _: '%d linhas copiadas', 1: '1 linha copiada' }
                        }
                    }
                });
            }

            $.get(actionsUrl, { action: 'get_all' }, function(res) {
                tabelaRelatorio.clear();
                if (Array.isArray(res) && res.length > 0) {
                    res.forEach(item => {
                        const statusBadge = `<span class="badge badge-status-${(item.status || '').toLowerCase().replace(' ', '_')}">${item.status}</span>`;
                        tabelaRelatorio.row.add([
                            String(item.id).padStart(4, '0'),
                            item.titulo,
                            statusBadge,
                            item.prioridade,
                            item.solicitante,
                            item.data_criacao
                        ]);
                    });
                }
                tabelaRelatorio.draw();
            }, 'json');
        });

        carregarMelhorias();
    });
    </script>
</body>
</html>