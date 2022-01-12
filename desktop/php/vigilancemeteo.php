<?php
if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
// Déclaration des variables obligatoires
$plugin = plugin::byId('vigilancemeteo');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>

<div class="row row-overflow">
	<!-- Page d'accueil du plugin -->
  <div class="col-xs-12 eqLogicThumbnailDisplay">
    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
		<!-- Boutons de gestion du plugin -->
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
          <i class="fas fa-plus-circle"></i>
          <br>
        <span>{{Ajouter}}</span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench"></i>
        <br>
        <span>{{Configuration}}</span>
      </div>
    </div>
    <legend><i class="fas fa-table"></i> {{Mes Equipements}}</legend>
      <?php
		if (count($eqLogics) == 0) {
			echo '<br/><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement Template n\'est paramétré, cliquer sur "Ajouter" pour commencer}}</div>';
		} else {
			// Champ de recherche
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			// Liste des équipements du plugin
			echo '<div class="eqLogicThumbnailContainer">';
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
        echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
        echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
        echo '<br>';
        echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
        echo '</div>';
      }
        echo '</div>';
      }
      ?>
	</div> <!-- /.eqLogicThumbnailDisplay -->

	<!-- Page de présentation de l'équipement -->
	<div class="col-xs-12 eqLogic" style="display: none;">
		<!-- barre de gestion de l'équipement -->
		<div class="input-group pull-right" style="display:inline-flex;">
			<span class="input-group-btn">
				<!-- Les balises <a></a> sont volontairement fermées à la ligne suivante pour éviter les espaces entre les boutons. Ne pas modifier -->
        <a class="btn btn-sm btn-default eqLogicAction roundedLeft" data-action="configure"><i class="fas fa-cogs"></i><span class="hidden-xs"> {{Configuration avancée}}</span>
				</a><a class="btn btn-sm btn-default eqLogicAction" data-action="copy"><i class="fas fa-copy"></i><span class="hidden-xs">  {{Dupliquer}}</span>
        </a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
        </a><a class="btn btn-sm btn-danger eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}
        </a>
			</span>
    </div>
		<!-- Onglets -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
        <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
        <li role="presentation"><a href="#commandtab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-list"></i> {{Commandes}}</a></li>
    </ul>
    <div class="tab-content">
			<!-- Onglet de configuration de l'équipement -->
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<!-- Partie gauche de l'onglet "Equipements" -->
				<!-- Paramètres généraux de l'équipement -->
        <form class="form-horizontal">
          <fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Paramètres généraux}}</legend>
              <div class="form-group">
                  <label class="col-sm-3 control-label">{{Nom}}</label>
                  <div class="col-sm-7">
                      <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;"/>
                      <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement Vigilances Météo}}"/>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                  <div class="col-sm-7">
                      <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                          <option value="">{{Aucun}}</option>
                          <?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
                          }
                          ?>
                      </select>
                  </div>
              </div>
              <div class="form-group">
                  <label class="col-sm-3 control-label">{{Catégorie}}</label>
                  <div class="col-sm-7">
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
                  <label class="col-sm-3 control-label">{{Options}}</label>
                  <div class="col-sm-7">
                      <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                      <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                  </div>
              </div>

							<legend><i class="fas fa-cogs"></i> {{Paramètres spécifiques}}</legend>
              <div class="form-group">
                  <label class="col-sm-3 control-label" >{{Type de Vigilance}}</label>
                  <div class="col-sm-7">
                      <select id="typeEq" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="type">
                          <option value="vigilance">{{Vigilance Météo France}}</option>
                          <option value="pluie1h">{{Pluie à 1h Météo France}}</option>
                          <option value="crue">{{Vigicrues}}</option>
                          <option value="maree">{{Marées}}</option>
                          <option value="surf">{{Surf}}</option>
                          <option value="plage">{{Météo des Plages}}</option>
                          <option value="air">{{Qualité d'Air}}</option>
                          <option value="pollen">{{Index Pollens}}</option>
                          <option value="gdacs">{{Alertes Globales}}</option>
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
                      <a class="btn btn-default" id='btnSearchCity'><i class="fas fa-search"></i> {{Trouver la ville}}</a>
                  </div>
              </div>

              <div id="portEq" class="form-group" style="display:none">
    <div class="form-group">
                  <label class="col-sm-3 control-label">{{Port}}</label>
                  <div class="col-sm-7">
<?php
          $harbors = vigilancemeteo::mareeListHarbors();
          if($harbors !== null) {
            echo "<select type=\"text\" class=\"eqLogicAttr configuration form-control\" data-l1key=\"configuration\" data-l2key=\"port\">";
            foreach($harbors as $harbor) {
              echo "<option value=" . $harbor['id'];
              if(!isset($harbor['latitude']) || !isset($harbor['longitude'])) {
                echo " disabled";
              }
              echo ">" .$harbor['name'] ." " .(isset($harbor['CP'])?$harbor['CP']:'---') ."</option>\n";
            }
            echo "</select>";
          }
          else {
            echo "<input type=\"text\" class=\"eqLogicAttr configuration form-control\" data-l1key=\"configuration\" data-l2key=\"port\" placeholder=\"52 pour Saint-Malo voir le site maree.info\"/>";
          }
?>
      </div>
    </div>
    <div class="form-group">
      <label class="col-sm-3 control-label" ></label>
      <div class="col-sm-7">
        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="useTideTemplate" checked/>{{Utiliser la template du plugin}}</label>
      </div>
                  </div>
              </div>

              <div id="stationEq" class="form-group" style="display:none">
    <div class="form-group">
                  <label class="col-sm-3 control-label">{{Station}}</label>
                  <div class="col-sm-7">
                      <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="station" placeholder="Ex F700000103 pour Paris Austerlitz"/>
                  </div>
              </div>
    <div class="form-group">
      <label class="col-sm-3 control-label" ></label>
      <div class="col-sm-7">
        <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="useFloodTemplate" checked/>{{Utiliser la template du plugin}}</label>
      </div>
    </div>
  </div>

              <div id="geolocEq" class="form-group" style="display:none">
                  <label class="col-sm-3 control-label">{{Localisation à utiliser}}</label>
                  <div class="col-sm-7">
                      <select class="form-control eqLogicAttr configuration" id="geoloc" data-l1key="configuration" data-l2key="geoloc">
                          <?php
                          $none = 0;
                          if (class_exists('geotravCmd')) {
                              foreach (eqLogic::byType('geotrav') as $geoloc) {
                                  if ($geoloc->getConfiguration('type') == 'location') {
                                      $none = 1;
                                      echo '<option value="' . $geoloc->getId() . '">' . $geoloc->getName() . '</option>';
                                  }
                              }
                          } 
                          if ((config::byKey('info::latitude') != '') && (config::byKey('info::longitude') != '') && (config::byKey('info::postalCode') != '') && (config::byKey('info::stateCode') != '')) {
                              echo '<option value="jeedom">Configuration Jeedom</option>';
                              $none = 1;
                          }
                          if ($none == 0) {
                              echo '<option value="">Pas de localisation disponible</option>';
                          }
                          ?>
                      </select>
                  </div>
              </div>

              <div id="pollenEq" class="form-group" style="display:none">
                <label class="col-sm-3 control-label"></label>
                <div class="col-sm-7">
                  <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="configuration" data-l2key="displayNullPollen" checked/>{{Afficher les pollens niveau 0}}</label>
                </div>
              </div>

              <div id="breezeEq" class="form-group" style="display:none">
                  <label class="col-sm-3 control-label">{{Clef}} <a href='http://aqicn.org/api/'>AQICN</a></label>
                  <div class="col-sm-7">
                      <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="aqicn" placeholder="exemple 122"/>
                  </div>
              </div>

              <div id="surfEq" class="form-group" style="display:none">
                  <label class="col-sm-3 control-label">{{ID Spot}}</label>
                  <div class="col-sm-7">
                      <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="surf" placeholder="exemple 1"/>
                  </div>
              </div>

              <div id="mswEq" class="form-group" style="display:none">
                  <label class="col-sm-3 control-label">{{Clef}} <a href='http://magicseaweed.com/developer/api'>Magicseaweed</a></label>
                  <div class="col-sm-7">
                      <input type="text" class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="magicseaweed" placeholder="clef API"/>
                  </div>
              </div>
      </div>
          </fieldset>
        </form>
            <hr>
			</div><!-- /.tabpanel #eqlogictab-->

			<!-- Onglet des commandes de l'équipement -->
            <div role="tabpanel" class="tab-pane" id="commandtab">
                <br/><br/>
				<div class="table-responsive">
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
            </div>
			</div><!-- /.tabpanel #commandtab-->
		</div><!-- /.tab-content -->
	</div><!-- /.eqLogic -->
</div><!-- /.row row-overflow -->

<!-- Inclusion du fichier javascript du plugin (dossier, nom_du_fichier, extension_du_fichier, id_du_plugin) -->
<?php include_file('desktop', 'vigilancemeteo', 'js', 'vigilancemeteo');?>
<!-- Inclusion du fichier javascript du core - NE PAS MODIFIER NI SUPPRIMER -->
<?php include_file('core', 'plugin.template', 'js');?>
