<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Setores</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <style>
         head, header{
        height: 5.2rem;
    }
        /* Melhora a aparência da paginação padrão do DataTables */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.4em 0.8em;
            margin: 0 3px;
            border-radius: 4px;
            border: 1px solid #ddd;
            background: #fff;
            color: #2A4687 !important; /* !important para sobrescrever estilos padrão */
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: #f0f0f0;
            border-color: #ccc;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current,
        .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
            background: #2A4687 /* Azul primário */
            color: #fff !important;
            border-color: #2A4687;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover {
            background: #fafafa;
            color: #aaa !important;
            border-color: #ddd;
            cursor: default;
        }
        
        /* Seus outros estilos podem ir aqui */
        .card { border: 1px solid #eee; border-radius: 8px; margin-bottom: 2rem; padding: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        label { display: block; margin-bottom: 0.5rem; }
        input[type="text"], input[type="number"] { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .button-container { margin-top: 1rem; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="card">
      <form id="formSetor">
        <input type="hidden" name="setor_tag_original">
        <div class="form-group">
          <label>TAG do Setor</label>
          <input type="text" name="setor_tag" required>
        </div>
        <div class="form-group" style="grid-column: span 2;">
          <label>Descrição do Setor</label>
          <input type="text" name="descricao" required>
        </div>
        <div class="button-container">
          <button type="submit">Salvar</button>
          <button type="button" id="btnNovo" class="add-btn">Novo</button>
        </div>
      </form>
    </div>

    <div class="card">
      <h2>Setores Cadastrados</h2>
      <table id="setoresTable" style="width:100%">
        <thead>
          <tr><th>TAG</th><th>Descrição</th><th>Ações</th></tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function() {
    let tabela;
    function carregarSetores() {
        $.post('paginas/Setores/setores_actions.php', { action: 'get' }, function(data) {
            if (!$.fn.DataTable.isDataTable('#setoresTable')) {
                tabela = $('#setoresTable').DataTable({
                    responsive: true,
                    language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json' }
                });
            }
            tabela.clear();
            if (Array.isArray(data)) {
                data.forEach(setor => {
                    const acoesHtml = `<button class="btn-editar">✏️</button> <button class="delete-btn btn-apagar" data-tag="${setor.setor_tag}">🗑️</button>`;
                    const linha = tabela.row.add([ setor.setor_tag, setor.descricao, acoesHtml ]).draw().node();
                    $(linha).data('setor', setor);
                });
            }
        }, 'json');
    }

    $('#formSetor').submit(function(e) { e.preventDefault(); $.ajax({ url: 'paginas/Setores/setores_actions.php', type: 'POST', data: $(this).serialize() + '&action=save', dataType: 'json', success: function(res) { if (res.sucesso) { alert(res.mensagem); carregarSetores(); limparFormulario(); } else { alert(res.mensagem || res.erro); } } }); });
    $('#setoresTable tbody').on('click', '.btn-editar', function () {
        const setor = $(this).closest('tr').data('setor');
        if (setor) {
            $('input[name="setor_tag_original"]').val(setor.setor_tag);
            $('input[name="setor_tag"]').val(setor.setor_tag).prop('readonly', true);
            $('input[name="descricao"]').val(setor.descricao);
            window.scrollTo(0, 0);
        }
    });
    $('#setoresTable tbody').on('click', '.btn-apagar', function (e) {
        const tag = $(this).data('tag');
        if (confirm(`Deseja apagar o setor "${tag}"?`)) {
            $.post('paginas/Setores/setores_actions.php', { action: 'delete', setor_tag: tag }, function (res) {
                if(res.sucesso) carregarSetores();
            }, 'json');
        }
    });
    $('#btnNovo').on('click', limparFormulario);
    function limparFormulario() {
        $('#formSetor')[0].reset();
        $('input[name="setor_tag_original"]').val('');
        $('input[name="setor_tag"]').prop('readonly', false);
    }
    carregarSetores();
});
</script>

</body>
</html>