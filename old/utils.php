<?php
/**
 * Converte uma data no formato ISO 8601 com 'T' (ex: 2025-08-25T22:15)
 * para o formato DATETIME do SQL Server (ex: 2025-08-25 22:15:00)
 *
 * @param string $dataIso Data no formato ISO 8601 com 'T'
 * @return string Data formatada para SQL Server
 */
function formatarDataParaSQL($dataIso) {
    // Substitui o 'T' por espaço
    $dataFormatada = str_replace('T', ' ', $dataIso);

    // Adiciona segundos se estiverem ausentes
    if (strlen($dataFormatada) === 16) {
        $dataFormatada .= ':00';
    }

    return $dataFormatada;
}
?>