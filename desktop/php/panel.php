<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
?>

<i class="fa fa-pencil pull-right cursor reportModeHidden" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;"></i>
    <div class="div_displayEquipement">
        <?php
        foreach (eqLogic::byType('vigilancemeteo', true) as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>

<?php include_file('desktop', 'panel', 'js', 'vigilancemeteo');?>
