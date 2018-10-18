<?php

include_once __DIR__.'/../../core.php';
include_once __DIR__.'/../../../crore.php';

$show_costi = true;
// Limitazione delle azioni dei tecnici
if ($user['gruppo'] == 'Tecnici') {
    $show_costi = !empty($user['idanagrafica']) && setting('Mostra i prezzi al tecnico');
}

$sessione = $dbo->fetchOne('SELECT in_interventi_tecnici.*, an_anagrafiche.ragione_sociale, an_anagrafiche.deleted_at FROM in_interventi_tecnici INNER JOIN an_anagrafiche ON in_interventi_tecnici.idtecnico = an_anagrafiche.idanagrafica WHERE in_interventi_tecnici.id = '.prepare(get('id_sessione')));

if (empty($sessione)) {
    $op = 'add_sessione';
    $button = '<i class="fa fa-plus"></i> '.tr('Aggiungi');
} else {
    $op = 'edit_sessione';
    $button = '<i class="fa fa-edit"></i> '.tr('Modifica');
}

echo '
<form id="add_form" action="'.$rootdir.'/editor.php?id_module='.$id_module.'&id_record='.$id_record.'" method="post">
    <input type="hidden" name="op" value="'.$op.'">
    <input type="hidden" name="id_sessione" value="'.$sessione['id'].'">';

// Tecnico
echo '
<p>'.tr('Tecnico').': '.$sessione['ragione_sociale'].' '.(!empty($sessione['deleted_at']) ? '<small class="text-danger"><em>('.tr('Eliminato').')</em></small>' : '').'</p>';

// Orari
echo '
    <div class="row">
        <div class="col-md-6">
            {[ "type": "timestamp", "label": "'.tr('Inizio attività').'", "name": "orario_inizio", "required": 1, "value": "'.$sessione['orario_inizio'].'" ]}
        </div>

        <div class="col-md-6">
            {[ "type": "timestamp", "label": "'.tr('Fine attività').'", "name": "orario_fine", "required": 1, "value": "'.$sessione['orario_fine'].'" ]}
        </div>
    </div>';

if ($show_costi) {
    echo '
    <div class="row">';

    // Km
    echo '
        <div class="col-md-4">
            {[ "type": "number", "label": "'.tr('Km').'", "name": "km", "value": "'.$sessione['km'].'"]}
        </div>';

    // Sconto ore
    echo '
        <div class="col-md-4">
            {[ "type": "number", "label": "'.tr('Sconto ore').'", "name": "sconto", "value": "'.$sessione['sconto_unitario'].'", "icon-after": "choice|untprc|'.$sessione['tipo_sconto'].'"]}
        </div>';

    // Sconto km
    echo '
        <div class="col-md-4">
            {[ "type": "number", "label": "'.tr('Sconto km').'", "name": "sconto_km", "value": "'.$sessione['scontokm_unitario'].'", "icon-after": "choice|untprc|'.$sessione['tipo_scontokm'].'"]}
        </div>';

    echo'
    </div>';
}

echo '

    <!-- PULSANTI -->
	<div class="row">
		<div class="col-md-12 text-right">
			<button type="submit" class="btn btn-primary">'.$button.'</button>
		</div>
    </div>
</form>';

echo '
<script src="'.$rootdir.'/lib/init.js"></script>';

echo '
<script>
$(document).ready(function () {
    // Quando modifico orario inizio, allineo anche l\'orario fine
    $("#orario_inizio").on("dp.change", function (e) {
        $("#orario_fine").data("DateTimePicker").minDate(e.date).format(globals.timestampFormat);
    });
});
</script>';