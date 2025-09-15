<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Ativos Totais</h5>
                    <p class="card-text fs-3" id="totalAtivos">--</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card text-white bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Máquinas Paradas</h5>
                    <p class="card-text fs-3" id="maquinasParadas">--</p>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">OS em Aberto</h5>
                    <p class="card-text fs-3" id="osAbertas">--</p>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script>
$(document).ready(function() {
    $.post('dashboard_actions.php', function(data) {
        if (!data.erro) {
            $('#totalAtivos').text(data.total_ativos);
            $('#maquinasParadas').text(data.maquinas_paradas);
            $('#osAbertas').text(data.os_abertas);
        }
    }, 'json');
});
</script>
</body>
</html>