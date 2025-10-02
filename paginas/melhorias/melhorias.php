<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    .card {
        border-left-width: 5px;
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        width: auto;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .button-container {
        display: flex;
        gap: 10px;
    }
    .col-md-4, .col-md-8, . .form-group> [class*="col-"] {
      
       flex-direction: row;
       gap: 10px;
    }
    .col-12 {
         flex-direction: column;
         gap: 10px;
         width: ;
    }
   
  
    </style>
<div class="card">
    <h2 class="mb-4">Solicitação de Melhorias</h2>
    <form id="formMelhoria">
        <input type="hidden" name="id_melhoria" value="">
        <div class="row g-3">
            <div class="col-md-4">
                <div class="form-group">
                    <label class="form-label">Cód. Solicitante</label>
                    <input type="number" name="codigo_solicitante" class="form-control" required>
                </div>
            </div>
            <div class="col-md-8">
                <div class="form-group">
                    <label class="form-label">Título da Solicitação</label>
                    <input type="text" name="titulo_solicitacao" class="form-control" required>
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label class="form-label">Descrição Detalhada</label>
                    <textarea name="descricao_detalhada" class="form-control" rows="5" required></textarea>
                </div>
            </div>
        </div>
        <div class="button-container mt-3">
            <button type="submit" class="btn btn-primary">Salvar Solicitação</button>
            <button type="button" id="btnNovo" class="btn btn-secondary">Novo</button>
        </div>
    </form>
</div>

<div class="card">
    <h2>Melhorias em Aberto</h2>
    <table id="melhoriasTable" class="table table-bordered table-hover" style="width:100%">
        <thead>
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Solicitante</th>
                <th>Data</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    let tabelaMelhorias;

    function carregarMelhorias() {
        $.post('paginas/Melhorias/melhorias_actions.php', { action: 'get_open' }, function(data) {
            if (!$.fn.DataTable.isDataTable('#melhoriasTable')) {
                tabelaMelhorias = $('#melhoriasTable').DataTable({ responsive: true });
            }
            tabelaMelhorias.clear();
            if (Array.isArray(data)) {
                data.forEach(item => {
                    const dataFormatada = item.data_criacao ? new Date(item.data_criacao.date).toLocaleDateString('pt-BR') : 'N/A';
                    const acoesHtml = `<button class="btn-editar btn btn-sm btn-primary" data-id="${item.id_melhoria}">✏️</button> <button class="btn-apagar btn btn-sm btn-danger" data-id="${item.id_melhoria}">🗑️</button>`;
                    const linha = tabelaMelhorias.row.add([
                        item.id_melhoria,
                        item.titulo_solicitacao,
                        item.codigo_solicitante,
                        dataFormatada,
                        acoesHtml
                    ]).draw().node();
                    $(linha).data('melhoria', item);
                });
            }
        }, 'json');
    }

    $('#formMelhoria').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize() + '&action=save';
        $.ajax({
            url: 'paginas/Melhorias/melhorias_actions.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(res) {
                if (res.sucesso) {
                    alert(res.mensagem);
                    carregarMelhorias();
                    limparFormulario();
                } else {
                    alert(res.mensagem || res.erro);
                }
            }
        });
    });

    $('#melhoriasTable tbody').on('click', '.btn-editar', function() {
        const id = $(this).data('id');
        const melhoria = tabelaMelhorias.rows().data().filter(row => row[0] == id)[0];
        if (melhoria) {
            $('input[name="id_melhoria"]').val(id);
            $('input[name="codigo_solicitante"]').val(melhoria[2]);
            $('input[name="titulo_solicitacao"]').val(melhoria[1]);
            // Nota: a descrição detalhada não é mostrada na tabela, seria necessário um 'get_details' para preenchê-la
            window.scrollTo(0, 0);
        }
    });

    $('#melhoriasTable tbody').on('click', '.btn-apagar', function(e) {
        e.stopPropagation();
        const id = $(this).data('id');
        if (confirm(`Tem certeza que deseja apagar a solicitação #${id}?`)) {
            $.post('paginas/Melhorias/melhorias_actions.php', { action: 'delete', id_melhoria: id }, function(res) {
                if(res.sucesso) {
                    alert(res.mensagem);
                    carregarMelhorias();
                } else {
                    alert(res.mensagem || res.erro);
                }
            }, 'json');
        }
    });

    $('#btnNovo').on('click', limparFormulario);

    function limparFormulario() {
        $('#formMelhoria')[0].reset();
        $('input[name="id_melhoria"]').val('');
    }

    carregarMelhorias();
});
</script>