<?php

use Modules\Contratti\Contratto;
use Modules\Fatture\Fattura;

$documento_finale = Fattura::find($id_record);
$dir = $documento_finale->direzione;

$id_documento = get('id_documento');
if (!empty($id_documento)) {
    $documento = Contratto::find($id_documento);

    $options = [
        'op' => 'add_documento',
        'type' => 'contratto',
        'button' => tr('Aggiungi'),
        'documento' => $documento,
        'documento_finale' => $documento_finale,
    ];

    echo App::load('importa.php', [], $options, true);

    return;
}

$id_anagrafica = $documento_finale->idanagrafica;

$_SESSION['superselect']['idanagrafica'] = $id_anagrafica;
$_SESSION['superselect']['stato'] = 'is_fatturabile';

echo '
<div class="row">
    <div class="col-md-12">
        {[ "type": "select", "label": "'.tr('Contratto').'", "name": "id_documento", "ajax-source": "contratti" ]}
    </div>
</div>

<div id="righe_documento">

</div>

<div class="alert alert-info" id="card-loading">
    <i class="fa fa-spinner fa-spin"></i> '.tr('Caricamento in corso').'...
</div>';

$file = basename(__FILE__);
echo '
<script>$(document).ready(init)</script>

<script>
    var content = $("#righe_documento");
    var loader = $("#card-loading");

    $(document).ready(function(){
        loader.hide();
    });

    $("#id_documento").on("change", function(){
        loader.show();

        var id = $(this).selectData() ? $(this).selectData().id  : "";

        content.html("");
        content.load("'.$structure->fileurl($file).'?id_module='.$id_module.'&id_record='.$id_record.'&id_documento=" + id, function() {
            loader.hide();
        });
    });
</script>';
