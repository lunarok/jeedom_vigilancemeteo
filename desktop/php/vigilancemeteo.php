<?php

if (!isConnect('admin')) {
  throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'vigilancemeteo');
$eqLogics = eqLogic::byType('vigilancemeteo');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-md-3 col-sm-4">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add"><i class="fa fa-plus-circle"></i> {{Ajouter un équipement}}</a>
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-10 col-md-9 col-sm-8 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>{{Mes Vigilances Météo}}
    </legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add" style="background-color : #ffffff; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;" >
        <center>
          <i class="fa fa-plus-circle" style="font-size : 7em;color:#00979c;"></i>
        </center>
        <span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>Ajouter</center></span>
      </div>
      <?php
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        echo '<img src="plugins/vigilancemeteo/doc/images/vigilancemeteo_icon.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>


<div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
 <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
 <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
 <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fa fa-cogs"></i> {{Configuration avancée}}</a>
 <ul class="nav nav-tabs" role="tablist">
  <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fa fa-arrow-circle-left"></i></a></li>
  <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fa fa-tachometer"></i> {{Equipement}}</a></li>
  <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> {{Commandes}}</a></li>
</ul>
<div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
  <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
        <form class="form-horizontal">
          <fieldset>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement Vigilances Météo}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}</label>
              <div class="col-sm-3">
                <select class="form-control eqLogicAttr" data-l1key="object_id">
                  <option value="">{{Aucun}}</option>
                  <?php
                  foreach (object::all() as $object) {
                    echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                  }
                  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}</label>
              <div class="col-sm-8">
                <?php
                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                  echo '<label class="checkbox-inline">';
                  echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                  echo '</label>';
                }
                ?>

              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label"></label>
              <div class="col-sm-8">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
              </div>
            </div>

            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Type de Vigilance}}</label>
              <div class="col-sm-3">
                <select id="typeEq" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="type">
                  <option value="vigilance">{{Vigilance Météo France}}</option>
                  <option value="pluie1h">{{Pluie à 1h Météo France}}</option>
                  <option value="crue">{{Vigicrues}}</option>
                  <option value="maree">{{Marées}}</option>
                  <option value="surf">{{Surf}}</option>
                  <option value="seisme">{{Séisme}}</option>
                  <option value="air">{{Qualité d'Air}}</option>
                </select>
              </div>
            </div>

            <div id="villeEq" class="form-group" style="display:none">
                    <label class="col-sm-3 control-label" >{{Ville}}</label>
                    <div class="col-sm-3">
                        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="villeNom" type="text" placeholder="{{Ville}}" id="mfVilleNom" disabled>
                    </div>
                    <div class="col-sm-3">
                        <input class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ville" type="text" placeholder="{{ID Ville}}" id="mfVilleId" disabled>
                    </div>
                    <div class="col-sm-3">
                       <a class="btn btn-default" id='btnSearchCity'><i class="fa fa-search"></i> {{Trouver la ville}}</a>
                    </div>
                </div>

            <div id="portEq" class="form-group" style="display:none">
              <label class="col-sm-3 control-label">{{Port}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="port" placeholder="exemple 122"/>
              </select>
            </div>
          </div>

          <div id="stationEq" class="form-group" style="display:none">
            <label class="col-sm-3 control-label">{{Station}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="station" placeholder="exemple 122"/>
            </select>
          </div>
        </div>

            <div id="departementEq" class="form-group" style="display:none">
              <label class="col-sm-3 control-label">{{Département}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="departement" placeholder="exemple 75"/>
              </select>
            </div>
          </div>

          <div id="alertEq" class="form-group">
            <label class="col-sm-3 control-label">{{Commande Alerte}}</label>
            <div class="col-sm-3">
              <div class="input-group">
                <input type="text"  class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="alert" />
                <span class="input-group-btn">
                  <a class="btn btn-default cursor" title="Rechercher une commande" id="bt_selectMailCmd"><i class="fa fa-list-alt"></i></a>
                </span>
              </div>
            </div>
          </div>

          <div id="geolocEq" class="form-group" style="display:none">
            <label class="col-sm-3 control-label">{{Geolocolisation à utiliser}}</label>
            <div class="col-sm-3">
              <select class="form-control eqLogicAttr configuration" id="geoloc" data-l1key="configuration" data-l2key="geoloc">
                  <?php
                  if (class_exists('geolocCmd')) {
                    foreach (eqLogic::byType('geoloc') as $geoloc) {
                      foreach (geolocCmd::byEqLogicId($geoloc->getId()) as $geoinfo) {
                        if ($geoinfo->getConfiguration('mode') == 'fixe' || $geoinfo->getConfiguration('mode') == 'dynamic') {
                          echo '<option value="' . $geoinfo->getId() . '">' . $geoinfo->getName() . '</option>';
                        }
                      }
                    }
                  } else {
                    echo '<option value="none">Geoloc absent</option>';
                  }
                  ?>
                </select>
          </div>
        </div>

          <div id="breezeEq" class="form-group" style="display:none">
            <label class="col-sm-3 control-label">{{Clef AQICN}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="aqicn" placeholder="exemple 122"/>
            </select>
          </div>
        </div>

            <div id="seismeEq" class="form-group" style="display:none">
              <label class="col-sm-3 control-label">{{Ville OpenHazards}}</label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="openhazards" placeholder="exemple Paris"/>
              </select>
            </div>
          </div>

          <div id="surfEq" class="form-group" style="display:none">
            <label class="col-sm-3 control-label">{{ID Spot}}</label>
            <div class="col-sm-3">
              <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="surf" placeholder="exemple 1"/>
            </select>
          </div>
        </div>

        <div id="mswEq" class="form-group" style="display:none">
          <label class="col-sm-3 control-label">{{Clef Magicseaweed}}</label>
          <div class="col-sm-3">
            <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="magicseaweed" placeholder="clef API"/>
          </select>
        </div>
      </div>

        </fieldset>
      </form>
    </div>
    <div role="tabpanel" class="tab-pane" id="commandtab">
      <br/>
      <table id="table_cmd" class="table table-bordered table-condensed">
        <thead>
          <tr>
            <th style="width: 100px;">#</th>
            <th style="width: 300px;">{{Nom}}</th>
            <th style="width: 200px;">{{Options}}</th>
            <th style="width: 150px;"></th>
          </tr>
        </thead>
        <tbody>

        </tbody>
      </table>

      <form class="form-horizontal">
        <fieldset>
          <div class="form-actions">
            <a class="btn btn-danger eqLogicAction" data-action="remove"><i class="fa fa-minus-circle"></i> {{Supprimer}}</a>
            <a class="btn btn-success eqLogicAction" data-action="save"><i class="fa fa-check-circle"></i> {{Sauvegarder}}</a>
          </div>
        </fieldset>
      </form>
    </div>
  </div>
</div>
</div>

<?php include_file('desktop', 'vigilancemeteo', 'js', 'vigilancemeteo'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
