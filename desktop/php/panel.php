<?php
if (!isConnect()) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
$vigilance = array();
$pluie1h = array();
$pollen = array();
$air = array();
$crue = array();
$maree = array();
$plage = array();
$seisme = array();
$surf = array();
foreach (eqLogic::byType('vigilancemeteo') as $eqLogic) {
    if ($eqLogic->getIsEnable() == 0 || $eqLogic->getIsVisible() == 0) {
        continue;
    }
    if ($eqLogic->getConfiguration('type') == 'vigilance') {
        $vigilance[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'pluie1') {
        $pluie1h[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'pollen') {
        $pollen[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'air') {
        $air[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'crue') {
        $crue[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'maree') {
        $maree[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'plage') {
        $plage[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'seisme') {
        $seisme[] = $eqLogic;
    }
    if ($eqLogic->getConfiguration('type') == 'surf') {
        $surf[] = $eqLogic;
    }
}
?>
<i class="fa fa-pencil pull-right cursor reportModeHidden" id="bt_editDashboardWidgetOrder" data-mode="0" style="margin-right : 10px;"></i>
<?php if (count($vigilance) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Vigilance Météo France}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($vigilance as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($pluie1h) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Pluie 1h Météo France}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($pluie1h as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($pollen) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Pollen}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($pollen as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($air) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Qualité d'Air}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($air as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($crue) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Crue}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($crue as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($maree) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Marée}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($maree as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($plage) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Plage}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($plage as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($seisme) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Séisme}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($seisme as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php if (count($surf) > 0) {
    ?>
    <legend><i class="icon nature-planet5"></i> {{Surf}}</legend>
    <div class="div_displayEquipement">
        <?php
        foreach ($surf as $eqLogic) {
            echo $eqLogic->toHtml('dview');
        }
        ?>
    </div>
<?php }?>
<?php include_file('desktop', 'panel', 'js', 'geotrav');?>
