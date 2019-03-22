<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class vigilancemeteo extends eqLogic {
  const LEVEL = array('vert', 'vert', 'jaune', 'orange', 'rouge');

  public static $_widgetPossibility = array('custom' => true);

  public static function cron15() {
    foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
      if ($vigilancemeteo->getConfiguration('type') == 'vigilance') {
        $vigilancemeteo->getVigilance();
      }
      if ($vigilancemeteo->getConfiguration('type') == 'crue') {
        $vigilancemeteo->getCrue();
      }
      $vigilancemeteo->refreshWidget();
    }
  }

  public static function cron5() {
    foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
      if ($vigilancemeteo->getConfiguration('type') == 'pluie1h') {
        $vigilancemeteo->getPluie();
      }
      $vigilancemeteo->refreshWidget();
    }
  }

  public static function cronHourly() {
    foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
      if ($vigilancemeteo->getConfiguration('type') == 'maree') {
        $vigilancemeteo->getMaree();
      }
      if ($vigilancemeteo->getConfiguration('type') == 'air') {
        $vigilancemeteo->getAir();
      }
      if ($vigilancemeteo->getConfiguration('type') == 'seisme') {
        $vigilancemeteo->getSeisme();
      }
      if ($vigilancemeteo->getConfiguration('type') == 'surf') {
        $vigilancemeteo->getSurf();
      }
      if ($vigilancemeteo->getConfiguration('type') == 'pollen') {
        $vigilancemeteo->getPollen();
      }
      if ($vigilancemeteo->getConfiguration('type') == 'plage') {
        $vigilancemeteo->getPlage();
      }
      $vigilancemeteo->refreshWidget();
    }
  }

  public function getInformations() {
      if ($this->getConfiguration('type') == 'maree') {
        $this->getMaree();
      }
      if ($this->getConfiguration('type') == 'air') {
        $this->getAir();
      }
      if ($this->getConfiguration('type') == 'seisme') {
        $this->getSeisme();
      }
      if ($this->getConfiguration('type') == 'surf') {
        $this->getSurf();
      }
      if ($this->getConfiguration('type') == 'pollen') {
        $this->getPollen();
      }
      if ($this->getConfiguration('type') == 'plage') {
        $this->getPlage();
      }
      if ($this->getConfiguration('type') == 'pluie1h') {
        $this->getPluie();
      }
      if ($this->getConfiguration('type') == 'vigilance') {
        $this->getVigilance();
      }
      if ($this->getConfiguration('type') == 'crue') {
        $this->getCrue();
      }
      $this->refreshWidget();
  }

public function loadCmdFromConf($_update = false) {

  if (!is_file(dirname(__FILE__) . '/../config/devices/' . $this->getConfiguration('type') . '.json')) {
    return;
  }
  $content = file_get_contents(dirname(__FILE__) . '/../config/devices/' . $this->getConfiguration('type') . '.json');
  if (!is_json($content)) {
    return;
  }
  $device = json_decode($content, true);
  if (!is_array($device) || !isset($device['commands'])) {
    return true;
  }
  //$this->import($device);
  foreach ($device['commands'] as $command) {
    $cmd = null;
    foreach ($this->getCmd() as $liste_cmd) {
      if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
      || (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
        $cmd = $liste_cmd;
        break;
      }
    }
    try {
      if ($cmd == null || !is_object($cmd)) {
        $cmd = new vigilancemeteoCmd();
        $cmd->setEqLogic_id($this->getId());
      } else {
        $command['name'] = $cmd->getName();
        if (isset($command['display'])) {
          unset($command['display']);
        }
      }
      utils::a2o($cmd, $command);
      $cmd->setConfiguration('logicalId', $cmd->getLogicalId());
      $cmd->save();
      if (isset($command['value'])) {
        $link_cmds[$cmd->getId()] = $command['value'];
      }
      if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
        $link_actions[$cmd->getId()] = $command['configuration']['updateCmdId'];
      }
    } catch (Exception $exc) {

    }
  }
}

public function postAjax() {
  $this->loadCmdFromConf();
}

public function postUpdate() {
  $depmer = array("06","11","13","14","17","2A","2B","22","29","30","33","34","35","40","44","50","56","59","62","64","66","76","80","83","85");
  if ($this->getConfiguration('type') == 'vigilance') {
    if ($this->getConfiguration('geoloc', 'none') == 'none') {
      return;
    }
    $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
    if (in_array($departement, $depmer)) {
      $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'mer');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new vigilancemeteoCmd();
        $cmdlogic->setName(__('Mer', __FILE__));
        $cmdlogic->setEqLogic_id($this->getId());
        $cmdlogic->setLogicalId('mer');
        $cmdlogic->setConfiguration('data', 'mer');
        $cmdlogic->setType('info');
        $cmdlogic->setSubType('string');
        $cmdlogic->save();
      }
    }
    $this->getVigilance();
  }
  if ($this->getConfiguration('type') == 'maree') {
    $this->getMaree();
  }
  if ($this->getConfiguration('type') == 'crue') {
    $this->getCrue();
  }
  if ($this->getConfiguration('type') == 'air') {
    $this->getAir();
  }
  if ($this->getConfiguration('type') == 'pollen') {
    $this->getPollen();
  }
  if ($this->getConfiguration('type') == 'surf') {
    $this->getSurf();
  }
  if ($this->getConfiguration('type') == 'seisme') {
    $this->getSeisme();
  }
  if ($this->getConfiguration('type') == 'plage') {
    $this->getPlage();
  }
  if ($this->getConfiguration('type') == 'pluie1h') {
    for($i=0; $i <= 11; $i++){
      $vigilancemeteoCmd = $this->getCmd(null, 'prev' . $i*5);
      if (!is_object($vigilancemeteoCmd)) {
        $vigilancemeteoCmd = new vigilancemeteoCmd();
        $vigilancemeteoCmd->setName(__('Prévision à ' . ($i*5) . '-' . ($i*5+5), __FILE__));
        $vigilancemeteoCmd->setEqLogic_id($this->id);
        $vigilancemeteoCmd->setLogicalId('prev' . $i*5);
        $vigilancemeteoCmd->setType('info');
        $vigilancemeteoCmd->setSubType('numeric');
        $vigilancemeteoCmd->save();
      }
    }
    $this->getPluie();
  }
}

public function getVigilance() {
  if ($this->getConfiguration('geoloc', 'none') == 'none') {
    return;
  }
  $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
  if ($departement == '92' || $departement == '93' || $departement == '94') {
    $departement = '75';
  }
  $lvigilance = "vert";
  $lcrue = "vert";
  $lrisque = array();
  $lmer = "vert";

  $url = 'http://vigilance.meteofrance.com/data/NXFR34_LFPW_.xml';
  $result = file($url);
  if ($result === false) {
    return;
  }
  $doc = new DOMDocument();
  $doc->load($url);

  /* exemple extrait du fichier, il y a différents niveaux possibles pour les risques
  <DV dep="33" coul="1"/>
  <DV dep="3310" coul="1"/>
  <DV dep="34" coul="3">
  <risque val="4"/>
  <risque val="3"/>
  <risque val="2"/>
  </DV>
  <DV dep="34" coul="2">
  <risque val="1"/>
  </DV>
  <DV dep="3410" coul="2">
  <risque val="9"/>
  </DV>
  <DV dep="35" coul="1"/>
  <DV dep="3510" coul="1"/>
  */
  $doc2 = new DOMDocument();
  $doc2->load('http://vigilance.meteofrance.com/data/NXFR33_LFPW_.xml');

  foreach($doc->getElementsByTagName('datavigilance') as $data) {
    if ($data->getAttribute('dep') == $departement) {
      // On récupère le niveau général
      $lvigilance = self::LEVEL[$data->getAttribute('couleur')];

      // On cherche les alertes "crue"
      foreach($data->getElementsByTagName('crue') as $crue) {
        $lcrue = self::LEVEL[$crue->getAttribute('valeur')];
      }
      foreach($data->getElementsByTagName('risque') as $risque) {
        switch ($risque->getAttribute('valeur')) {
          case 1:
          $lrisque[] = "vent";
          break;
          case 2:
          $lrisque[] = "pluie-inondation";
          break;
          case 3:
          $lrisque[] = "orages";
          break;
          case 4:
          $lrisque[] = "inondations";
          break;
          case 5:
          $lrisque[] = "neige-verglas";
          break;
          case 6:
          $lrisque[] = "canicule";
          break;
          case 7:
          $lrisque[] = "grand-froid";
          break;
        }
      }
    }
    if ($data->getAttribute('dep') == $departement.'10') {
      //alerte mer
      switch ($data->getAttribute('couleur')) {
        case 0:
        $lmer = "vert";
        break;
        case 1:
        $lmer = "vert";
        break;
        case 2:
        $lmer = "jaune";
        break;
        case 3:
        $lmer = "orange";
        break;
        case 4:
        $lmer = "rouge";
        break;
      }

    }
  }

  if (array_key_exists(0, $lrisque)) {
    $lrisque = implode(", ", $lrisque);
  } else {
    $lrisque = 'RAS';
  }

  foreach($doc2->getElementsByTagName('DV') as $data) {
    if ($data->getAttribute('dep') == $departement) {
      $couleur = self::LEVEL[$data->getAttribute('coul')];
      foreach($data->getElementsByTagName('risque') as $risque) {
        switch ($risque->getAttribute('val')) {
          case 1:
          if ($lrisque == "RAS") {
            $lrisque = "vent ".$couleur;
          } else {
            $lrisque = $lrisque . ", vent ".$couleur;
          }
          break;
          case 2:
          if ($lrisque == "RAS") {
            $lrisque = "pluie-inondation ".$couleur;
          } else {
            $lrisque = $lrisque . ", pluie-inondation ".$couleur;
          }
          break;
          case 3:
          if ($lrisque == "RAS") {
            $lrisque = "orages ".$couleur;
          } else {
            $lrisque = $lrisque . ", orages ".$couleur;
          }
          break;
          case 4:
          if ($lrisque == "RAS") {
            $lrisque = "inondations ".$couleur;
          } else {
            $lrisque = $lrisque . ", inondations ".$couleur;
          }
          break;
          case 5:
          if ($lrisque == "RAS") {
            $lrisque = "neige-verglas ".$couleur;
          } else {
            $lrisque = $lrisque . ", neige-verglas ".$couleur;
          }
          break;
          case 6:
          if ($lrisque == "RAS") {
            $lrisque = "canicule ".$couleur;
          } else {
            $lrisque = $lrisque . ", canicule ".$couleur;
          }
          break;
          case 7:
          if ($lrisque == "RAS") {
            $lrisque = "grand-froid ".$couleur;
          } else {
            $lrisque = $lrisque . ", grand-froid ".$couleur;
          }
          break;
          case 8:
          if ($lrisque == "RAS") {
            $lrisque = "avalanches ".$couleur;
          } else {
            $lrisque = $lrisque . ", avalanches ".$couleur;
          }
          break;
          case 9:
          if ($lrisque == "RAS") {
            $lrisque = "vagues-submersion ".$couleur;
          } else {
            $lrisque = $lrisque . ", vagues-submersion ".$couleur;
          }
          break;
        }
      }
    }
  }

  log::add('vigilancemeteo', 'debug', 'Vigilance ' . $lvigilance);
  log::add('vigilancemeteo', 'debug', 'Crue ' . $lcrue);
  log::add('vigilancemeteo', 'debug', 'Risque ' . $lrisque);

  foreach ($this->getCmd() as $cmd) {
    $this->checkAndUpdateCmd('vigilance', $lvigilance);
    $this->checkAndUpdateCmd('crue', $lcrue);
    $this->checkAndUpdateCmd('risque', $lrisque);
    $this->checkAndUpdateCmd('mer', $lmer);
  }
  return ;
}

public function getMaree() {
  $port = $this->getConfiguration('port');
  if ($port == '') {
    log::add('crues', 'error', 'Port non saisi');
    return;
  }
  $url = 'http://horloge.maree.frbateaux.net/ws' . $port . '.js?col=1&c=0';
  $result = file($url);
  if ($result === false) {
    return;
  }

  //log::add('maree', 'debug', 'Log ' . print_r($result, true));

  $maree = explode('<br>', $result[15]);
  $maree = explode('"', $maree[1]);
  $maree = $maree[0];
  $pleine = explode('PM ', $result[17] );
  $pleine = substr($pleine[1], 0, 5);
  $pleine = str_replace('h', '', $pleine);
  $basse = explode('BM ', $result[17]);
  $basse = substr($basse[1], 0, 5);
  $basse = str_replace('h', '', $basse);

  log::add('vigilancemeteo', 'debug', 'Marée ' . $maree . ', Pleine ' . $pleine . ', Basse ' . $basse);
  $this->checkAndUpdateCmd('maree', $maree);
  $this->checkAndUpdateCmd('pleine', $pleine);
  $this->checkAndUpdateCmd('basse', $basse);

  return ;
}

public function getPlage() {
  $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
       if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
           return;
       }
  $postal = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:zip')->execCmd();
  $city = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:city')->execCmd();
  $city = str_replace(' ','_',strtolower($city));
  $city = preg_replace('#Ç#', 'C', $city);
    $city = preg_replace('#ç#', 'c', $city);
    $city = preg_replace('#è|é|ê|ë#', 'e', $city);
    $city = preg_replace('#à|á|â|ã|ä|å#', 'a', $city);
    $city = preg_replace('#ì|í|î|ï#', 'i', $city);
    $city = preg_replace('#ð|ò|ó|ô|õ|ö#', 'o', $city);
    $city = preg_replace('#ù|ú|û|ü#', 'u', $city);
    $city = preg_replace('#ý|ÿ#', 'y', $city);
    $city = preg_replace('#Ý#', 'Y', $city);
  $city = str_replace('_', '-', $city);
  $city = str_replace('\'', '', $city);
  $adresse = "http://www.meteofrance.com/previsions-meteo-plages/". $city ."/".$postal;
  $request_http = new com_http($adresse);
  $page = $request_http->exec(30);
  //$page = file_get_contents($adresse);
  //Temperature de la mer
  $findeau   = 'Eau';
  $pos = strstr($page, $findeau);
  $Temperatureeau = strstr($pos, "</li>", true);
  $poss =  substr($Temperatureeau, -5, 5);
  $this->checkAndUpdateCmd('tempWater', $poss);
  $findEtat   = 'Etat';
  $positionEtat = strstr($page, $findEtat);
  $Meretat = strstr($positionEtat, "</li>", true);
  $find2point   = ": ";
  $possEtat = strstr($Meretat, $find2point);
  $resultatEtat = explode(':',$possEtat, 2)[1];
  $this->checkAndUpdateCmd('seaState', $resultatEtat);
  $findcondition   = "day-summary-label";
  $positionCondition = strstr($page, $findcondition);
  $Condition = strstr($positionCondition, "</li>", true);
  $findespace   = " ";
  $possCondition = strstr($Condition, $findespace);
  $this->checkAndUpdateCmd('condition', $possCondition);
  $findindice   = "day-summary-uv";
  $positionindice = strstr($page, $findindice);
  $Indice = strstr($positionindice, "</li>", true);
  $possIndice =  substr($Indice, -2, 2);
  $this->checkAndUpdateCmd('UVI', $possIndice);
  $findresenti   = "day-summary-tress-start";
  $posistionresenti = strstr($page, $findresenti);
  $Resenti = strstr($posistionresenti, "</li>", true);
  $possResenti =  substr($Resenti, -5, 5);
  $this->checkAndUpdateCmd('tempFeel', $possResenti);
  $findeTexterior  = "day-summary-temperature";
  $positionTescterior = strstr($page, $findeTexterior);
  $Texterior = strstr($positionTescterior, "</li>", true);
  $possTexterior =  substr($Texterior, -5, 5);
  $this->checkAndUpdateCmd('tempAir', $possTexterior);
  log::add('vigilancemeteo', 'debug', 'Plage ' . $poss . ', URL ' . $adresse);
  return ;
}

public function getCrue() {
  $station = $this->getConfiguration('station');
  if ($station == '') {
    log::add('vigilancemeteo', 'error', 'Station non saisie');
    return;
  }
  $url = 'http://www.vigicrues.gouv.fr/services/observations.xml/?CdStationHydro='.$station;
  $result = file($url);
  if ($result === false) {
    return;
  }
  $doc = new DOMDocument();
  $doc->load($url);

  $result = 0;
  foreach($doc->getElementsByTagName('ResObsHydro') as $data) {
    $result = $data->nodeValue;
  }

  $date = 0;
  foreach($doc->getElementsByTagName('DtObsHydro') as $data) {
    $date = $data->nodeValue;
  }

  log::add('vigilancemeteo', 'debug', 'Valeur ' . $result);
  $this->checkAndUpdateCmd('niveau', $result);
  $this->checkAndUpdateCmd('dateniveau', $date);

  $url = 'http://www.vigicrues.gouv.fr/services/observations.xml/?CdStationHydro='.$station.'&GrdSerie=Q';
  $doc = new DOMDocument();
  $doc->load($url);

  $result = 0;
  foreach($doc->getElementsByTagName('ResObsHydro') as $data) {
    $result = $data->nodeValue;
  }

  $date = 0;
  foreach($doc->getElementsByTagName('DtObsHydro') as $data) {
    $date = $data->nodeValue;
  }

  log::add('vigilancemeteo', 'debug', 'Valeur ' . $result);
  $this->checkAndUpdateCmd('debit', $result);
  $this->checkAndUpdateCmd('datedebit', $date);

  return ;
}

public function getSeisme() {
  if ($this->getConfiguration('geoloc', 'none') == 'none') {
    return;
  }
  $city = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:city')->execCmd();
  $url = 'http://api.openhazards.com/GetEarthquakeProbability?q=' . $city . '&m=5&r=100&w=3';
  $result = file($url);
  if ($result === false) {
    return;
  }
  $doc = new DOMDocument();
  $doc->load($url);

  $result = 0;
  foreach($doc->getElementsByTagName('prob') as $data) {
    $result = str_replace("%", "", $data->nodeValue);
  }

  $this->checkAndUpdateCmd('risk', $result);

  log::add('vigilancemeteo', 'debug', 'Seisme ' . $result);

  return ;
}

public function getAir() {
  $apikey = $this->getConfiguration('aqicn');
  if ($apikey == '') {
    log::add('vigilancemeteo', 'error', 'API non saisie');
    return;
  }
  $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
       if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
           return;
       }
  $geolocval = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:coordinate')->execCmd();
  $geoloctab = explode(',', trim($geolocval));
  $latitude = trim($geoloctab[0]);
  $longitude = trim($geoloctab[1]);
  $url = 'http://api.waqi.info/feed/geo:' . $latitude . ';' . $longitude . '/?token=' . $apikey;
  log::add('vigilancemeteo', 'debug', 'AQI URL ' . $url);
  $request_http = new com_http($url);
  $content = $request_http->exec(30);
  //$content = file_get_contents($url);
  if ($content === false) {
    return;
  }
  $json = json_decode($content, true);
  if (!isset($json['data']['aqi'])) {
    log::add('vigilancemeteo', 'error', 'Error in API call ' . $url);
    return;
  }
  log::add('vigilancemeteo', 'debug', 'Air ' . $json['data']['aqi'] . ' ' . $json['data']['city']['name']);
  if ($json['data']['aqi'] <= 50) {
    $color = 'green';
  } else if ($json['data']['aqi'] <= 100) {
    $color = 'yellow';
  } else if ($json['data']['aqi'] <= 150) {
    $color = 'orange';
  } else {
    $color = 'red';
  }
  $this->checkAndUpdateCmd('color', $color);
  $this->checkAndUpdateCmd('aqi', $json['data']['aqi']);
  $this->checkAndUpdateCmd('dominentpol', $json['data']['dominentpol']);
  $this->checkAndUpdateCmd('no2', $json['data']['iaqi']['no2']['v']);
  if (isset($json['data']['iaqi']['o3']['v'])) {
    $this->checkAndUpdateCmd('o3', $json['data']['iaqi']['o3']['v']);
  }
  if (isset($json['data']['iaqi']['pm10']['v'])) {
    $this->checkAndUpdateCmd('pm10', $json['data']['iaqi']['pm10']['v']);
  }
  $this->checkAndUpdateCmd('pm25', $json['data']['iaqi']['pm25']['v']);
  $this->checkAndUpdateCmd('t', $json['data']['iaqi']['t']['v']);
  $this->checkAndUpdateCmd('h', $json['data']['iaqi']['h']['v']);
  $this->checkAndUpdateCmd('p', $json['data']['iaqi']['p']['v']);
  return ;
}

public function getSurf() {
  $apikey = $this->getConfiguration('magicseaweed');
  if ($apikey == '') {
    log::add('vigilancemeteo', 'error', 'API non saisie');
    return;
  }
  if (null !== ($this->getConfiguration('surf', ''))) {
    $surf = $this->getConfiguration('surf', '');
    $url = 'http://magicseaweed.com/api/' . $apikey . '/forecast/?spot_id=' . $surf;
    $request_http = new com_http($url);
    $content = $request_http->exec(30);
    //$content = file_get_contents($url);
    if ($content === false) {
      return;
    }
    $json = json_decode($content, true);

    $this->checkAndUpdateCmd('minimum', $json[0]['swell']['minBreakingHeight']);
    $this->checkAndUpdateCmd('maximum', $json[0]['swell']['maxBreakingHeight']);
    $this->checkAndUpdateCmd('primaryHeight', $json[0]['swell']['components']['primary']['height']);
    $this->checkAndUpdateCmd('primaryPeriod', $json[0]['swell']['components']['primary']['period']);
    $this->checkAndUpdateCmd('compassDirection', $json[0]['swell']['components']['primary']['compassDirection']);

  }
  return ;
}

public function getPollen() {
  $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
  if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
    return;
  }
  $colorsPollen = array();
  $colorsPollen[] = array( 255, 255, 255, 0); // Blanc
  $colorsPollen[] = array( 116, 228, 108, 1); // VertClair
  $colorsPollen[] = array(   4, 128,   0, 2); // VertFonce
  $colorsPollen[] = array( 242, 234,  26, 3); // Jaune
  $colorsPollen[] = array( 255, 127,  41, 4); // Orange
  $colorsPollen[] = array( 255,   1,   0, 5); // Rouge
  $imgPollen = @imagecreate(10, 10);
  if ( $imgPollen !== false ) {
    foreach ( $colorsPollen as $color )
      $col = imagecolorallocate($imgPollen, $color[0], $color[1], $color[2]);
  }
  else {
    log::add('vigilancemeteo' ,'debug' ,__FUNCTION__ .'Cannot Initialize new GD imgPollen stream');
  }
  
  $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
  log::add('vigilancemeteo', 'debug', 'Pollen departement : ' . $departement);
  $im = @imagecreatefrompng("http://www.pollens.fr/generated/vigilance_map.png");
  if ($im === false) {
    log::add('vigilancemeteo', 'debug', 'Pollens.fr Image not found ');
    $pollen = -1;
  } else {
    $xy = vigilancemeteo::getDep();
    $dep0 = ltrim($departement, '0');
    $rgb = @imagecolorat($im, $xy[$dep0][0], $xy[$dep0][1]);
    $colors = @imagecolorsforindex($im, $rgb);
    $pollen = vigilancemeteo::getPollenLevel($colors['red'],$colors['green'],$colors['blue'],$colorsPollen,$imgPollen);
    //log::add('vigilancemeteo', 'debug', 'Coordonnées ' . $xy[$departement][0] . ' ' . $xy[$departement][1] . ' level : ' . $pollen);
  }
  $this->checkAndUpdateCmd('general', $pollen);

  if ( strlen ($departement) == 1) $departement = "0".$departement;
    // Use internal libxml errors -- turn on in production, off for debugging
  libxml_use_internal_errors(true);
  $dom = new DomDocument;
    // Load the HTML
  $ret = $dom->loadHTMLFile("https://www.pollens.fr/risks/thea/counties/$departement");
  if ( $ret === false ) {
    log::add('vigilancemeteo', 'debug', __FUNCTION__ .' Unable to load data for county : '.$departement);
    for ( $i=1; $i<20; $i++) {
      $this->checkAndUpdateCmd('pollen' . $i, -1);
    }
    return;
  }
  $xpath = new DomXPath($dom);
    // Query all nodes containing specified class name
  $texts = $xpath->query("/html/body/div/svg/g[3]//text");
  $rects = $xpath->query("/html/body/div/svg/g[1]//rect");
    // idxPollen parce que la liste des pollens n'arrive plus dans le même ordre qu'avant
    // et qu'il faut avoir le meme index
  foreach ($texts as $i => $text) {
    $nomPollen = trim($text->nodeValue);
    $nomPollen = preg_replace('#'.chr(131).chr(194).'#', '', $nomPollen);
    switch ( $nomPollen ) {
      case "Cyprès" : $nomPollen="Cupressacées"; $idxPollen = 1; break;
      case "Noisetier" : $idxPollen = 2; break;
      case "Aulne" : $idxPollen = 3; break;
      case "Peuplier" : $idxPollen = 4; break;
      case "Saule" : $idxPollen = 5; break;
      case "Frêne" : $idxPollen = 6; break;
      case "Charme" : $idxPollen = 7; break;
      case "Bouleau" : $idxPollen = 8; break;
      case "Platane" : $idxPollen = 9; break;
      case "Chêne" : $idxPollen = 10; break;
      case "Olivier" : $idxPollen = 11; break;
      case "Tilleul" : $idxPollen = 12; break;
      case "Châtaignier" : $idxPollen = 13; break;
      case "Oseille" : $nomPollen = "Rumex"; $idxPollen = 14; break;
      case "Graminées" : $idxPollen = 15; break;
      case "Plantain" : $idxPollen = 16; break;
      case "Urticacées" : $idxPollen = 17; break;
      case "Armoise" : $idxPollen = 18; break;
      case "Ambroisies" : $idxPollen = 19; break;
      default : $idxPollen = 0;
        log::add('vigilancemeteo', 'debug', "Pollen: [$nomPollen] not processed in pollens.fr data.");
    }
    foreach ($rects as $j => $rect) {
      if( $i == $j) {
        $attr = trim($rect->getAttribute("style"));
        if ( ( $pos = strpos($attr,"fill: #")) === false) {
          $pollenLevel = -1;
          log::add('vigilancemeteo', 'debug', "Fill color not found in rect for: $nomPollen");
        } else {
          $color = substr($attr,$pos+7,6);
          $red = hexdec(substr($color,0,2));
          $green = hexdec(substr($color,2,2));
          $blue = hexdec(substr($color,4,2));
          $pollenLevel = self::getPollenLevel($red,$green,$blue,$colorsPollen,$imgPollen);
        }
          // Envoi résultat à Jeedom
        $this->checkAndUpdateCmd('pollen'.$idxPollen, $pollenLevel);
        break;
      }
    }
  }
  return ;
}

  public function getPollenLevel($red,$green,$blue,$colorsPollen,$imgPollen) {
    if ( $imgPollen ) {
      $col = imagecolorclosest ( $imgPollen , $red , $green , $blue );
      $col = imagecolorsforindex($imgPollen, $col);
      $nred = $col['red']; $ngreen = $col['green']; $nblue = $col['blue'];
      foreach ( $colorsPollen as $color ) {
        if($nred == $color[0] && $ngreen == $color[1] && $nblue == $color[2] ) {
          log::add('vigilancemeteo', 'debug', 'Img Couleur ' . $nred . ' ' . $ngreen . ' ' . $nblue . ' : ' . $color[3]);
          return( $color[3] );
        }
      }
    }
      //0 absence, 1 vert clair, 2 vert foncé, 3 jaune, 4 orange, 5 rouge
    $level = 0;
    if ($red == 116 && $green == 228 && $blue == 108) { // vert clair
      $level = 1;
    } elseif ($red == 4 && $green == 128 && $blue == 0) { // vert fonce
      $level = 2;
    } elseif ($red == 242 && $green == 234 && $blue == 26) { // jaune
      $level = 3;
    } elseif ($red == 255 && $green == 127 && $blue == 41) { // orange
      $level = 4;
    } elseif ($red == 255 && ( $green == 1 || $green == 2 ) && $blue == 0) { // rouge
      $level = 5;
    }
    log::add('vigilancemeteo', 'debug', 'Couleur ' . $red . ' ' . $green . ' ' . $blue . ' : ' . $level);
    return $level;
  }

  function getDep() {
    $dep[1] = array(1100,840);
    $dep[2] = array(940,360);
    $dep[3] = array(900,800);
    $dep[4] = array(1200,1100);
    $dep[5] = array(1200,1020);
    $dep[6] = array(1270,1120);
    $dep[7] = array(1010,1010);
    $dep[8] = array(1030,360);
    $dep[9] = array(760,1240);
    $dep[10] = array(1000,540);
    $dep[11] = array(850,1220);
    $dep[12] = array(870,1070);
    $dep[13] = array(1100,1160);
    $dep[14] = array(600,430);
    $dep[15] = array(860,970);
    $dep[16] = array(640,880);
    $dep[17] = array(550,870);
    $dep[18] = array(840,700);
    $dep[19] = array(780,930);
    $dep[20] = array(1450,1310);
    $dep[21] = array(1040,660);
    $dep[22] = array(350,530);
    $dep[23] = array(800,840);
    $dep[24] = array(680,960);
    $dep[25] = array(1190,700);
    $dep[26] = array(1080,1020);
    $dep[27] = array(700,440);
    $dep[28] = array(740,530);
    $dep[29] = array(260,540);
    $dep[30] = array(1000,1110);
    $dep[31] = array(740,1170);
    $dep[32] = array(660,1150);
    $dep[33] = array(560,1000);
    $dep[34] = array(920,1160);
    $dep[35] = array(470,560);
    $dep[36] = array(750,750);
    $dep[37] = array(680,680);
    $dep[38] = array(1120,940);
    $dep[39] = array(1140,750);
    $dep[40] = array(540,1110);
    $dep[41] = array(740,630);
    $dep[42] = array(990,890);
    $dep[43] = array(960,960);
    $dep[44] = array(460,660);
    $dep[45] = array(830,600);
    $dep[46] = array(760,1030);
    $dep[47] = array(660,1060);
    $dep[48] = array(930,1050);
    $dep[49] = array(560,670);
    $dep[50] = array(500,440);
    $dep[51] = array(1000,450);
    $dep[52] = array(1090,570);
    $dep[53] = array(560,560);
    $dep[54] = array(1170,490);
    $dep[55] = array(1100,450);
    $dep[56] = array(350,600);
    $dep[57] = array(1210,440);
    $dep[58] = array(930,700);
    $dep[59] = array(930,270);
    $dep[60] = array(840,390);
    $dep[61] = array(620,490);
    $dep[62] = array(820,240);
    $dep[63] = array(900,880);
    $dep[64] = array(550,1200);
    $dep[65] = array(630,1230);
    $dep[66] = array(860,1280);
    $dep[67] = array(1300,500);
    $dep[68] = array(1270,600);
    $dep[69] = array(1030,870);
    $dep[70] = array(1170,630);
    $dep[71] = array(1020,770);
    $dep[72] = array(630,580);
    $dep[73] = array(1210,920);
    $dep[74] = array(1200,840);
    $dep[75] = array(1220,120);
    $dep[76] = array(710,350);
    $dep[77] = array(880,500);
    $dep[78] = array(780,470);
    $dep[79] = array(580,780);
    $dep[80] = array(820,310);
    $dep[81] = array(810,1130);
    $dep[82] = array(720,1100);
    $dep[83] = array(1180,1180);
    $dep[84] = array(1080,1100);
    $dep[85] = array(500,760);
    $dep[86] = array(650,780);
    $dep[87] = array(720,870);
    $dep[88] = array(1200,560);
    $dep[89] = array(940,600);
    $dep[90] = array(1240,630);
    $dep[91] = array(820,510);
    $dep[92] = array(1160,140);
    $dep[93] = array(1290,80);
    $dep[94] = array(1280,180);
    $dep[95] = array(820,440);
    $dep[99] = array(755,1290);
    return $dep;
  }

  /**
  * Retrieve weather forecast for the next hour
  *
  * @return boolean True if success, false otherwise
  */
  public function getPluie() {
    //log::add('previsionpluie', 'debug', 'getInformation: go');
    $ville = $this->getConfiguration('ville');
    if(empty($ville)) {
      log::add('vigilancemeteo', 'error', __('La ville n\'est pas configurée', __FILE__));
      return false;
    }

    $url = sprintf('http://www.meteofrance.com/mf3-rpc-portlet/rest/pluie/%s', $ville);
    //log::add('previsionpluie', 'debug', 'getInformation: ' .$this->getConfiguration('ville') );
    $prevPluieData = null;
    for ($attempt = 1; $attempt <= 3 && is_null($prevPluieData); $attempt++) {
      //$prevPluieJson = file_get_contents($url);
      $request_http = new com_http($url);
      $prevPluieJson = $request_http->exec(30);
      $prevPluieData = json_decode($prevPluieJson, true);

      # If it's not the first attempt
      if ($attempt > 1) {
        log::add('vigilancemeteo', 'info', 'Impossible d\'obtenir les informations Météo France... On refait une tentative...');
        sleep(3);
      }
    }

    // unable to fetch the url more than max times
    if(is_null($prevPluieData)){
      log::add('vigilancemeteo', 'warning', 'Impossible d\'obtenir les informations Météo France... ');
      return false;
    }

    //log::add('previsionpluie', 'debug', 'getInformation: length ' . count($prevPluieData));
    $prevTexte = "";
    # Loop over each rain level description
    foreach($prevPluieData['niveauPluieText'] as $prevTexteItem){
      $prevTexte .= substr_replace($prevTexteItem," ",2,0) . "\n";
      //log::add('previsionpluie', 'debug', 'prevTexteItem: ' . $prevTexteItem);
    }
    $this->checkAndUpdateCmd('prevTexte', $prevTexte);
    $this->checkAndUpdateCmd('lastUpdate', $prevPluieData['lastUpdate']);

    # compute the rain summary for the next hour
    $minutesAvantPluie = null;
    $pluieDanslHeureCount = 0;
    for($i=0; $i <= 11; $i++) {
      $cmdName = sprintf('prev%d', $i * 5);
      $prevCmd = $this->getCmd(null, $cmdName);
      if(is_object($prevCmd)){
        //log::add('previsionpluie', 'debug', 'prev' . $i*5 . ': ' . $prevPluieData['dataCadran'][$i]['niveauPluie']);
        $niveau = intval($prevPluieData['dataCadran'][$i]['niveauPluie']);
        $this->checkAndUpdateCmd($cmdName, $niveau);
        $pluieDanslHeureCount += $niveau;
        if ($niveau > 1 && is_null($minutesAvantPluie)) {
          $minutesAvantPluie = $i * 5;
        }
      }
    }
    $this->checkAndUpdateCmd('minutesAvantPluie', $minutesAvantPluie);
    $this->checkAndUpdateCmd('pluieDanslHeure', $pluieDanslHeureCount);
    log::add('vigilancemeteo', 'info', sprintf("%s '%s' %s '%s'",
    __('VigilanceMeteo de type', __FILE__),
    $this->getConfiguration('type'),
    __('mise a jour pour la ville', __FILE__),
    $this->getConfiguration('villeNom')));
    return true;
  }

  public function toHtml($_version = 'dashboard') {
    $replace = $this->preToHtml($_version);
    if (!is_array($replace)) {
      return $replace;
    }
    $version = jeedom::versionAlias($_version);
    if ($this->getDisplay('hideOn' . $version) == 1) {
      return '';
    }

    if ($this->getConfiguration('type') == 'vigilance') {
      foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        $valeur=ucfirst($cmd->execCmd());
        switch ($valeur) {
          case 'Vert':
          $valeur = "#00ff1e";
          break;
          case 'Jaune':
          $valeur = "#FFFF00";
          break;
          case 'Orange':
          $valeur = "#FFA500";
          break;
          case 'Rouge':
          $valeur = "#E50000";
          break;
        }

        $replace['#' . $cmd->getLogicalId() . '#'] = $valeur;
        $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
        }

      }
      $parameters = $this->getDisplay('parameters');
      if (is_array($parameters)) {
        foreach ($parameters as $key => $value) {
          $replace['#' . $key . '#'] = $value;
        }
      }
      if (strpos(network::getNetworkAccess('external'),'https') !== false) {
        $department = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
        $replace['#icone#'] = '<a target="_blank" href="http://vigilance.meteofrance.com/Bulletin_sans.html?a=dept' . $department . '&b=2&c="><i class="fa fa-info-circle cursor"></i></a>';      
      } else {
        $replace['#icone#'] = '<i id="yourvigilance' . $this->getId() . ' class="fa fa-info-circle cursor"></i>';
      }

      $templatename = 'vigilancemeteo';
    } else if ($this->getConfiguration('type') == 'maree') {
      $replace['#portid#'] = $this->getConfiguration('port');

      foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();

        if ($cmd->getLogicalId() == 'maree') {
          $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
        } else {
          $replace['#' . $cmd->getLogicalId() . '#'] = substr_replace(str_pad($cmd->execCmd(), 4, '0', STR_PAD_LEFT),':',-2,0);
        }
        $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
        }

      }

      if (strpos(network::getNetworkAccess('external'),'https') !== false) {
        $replace['#icone#'] = '<a target="_blank" href="http://maree.info/' . $this->getConfiguration('port') . '"><i class="fa fa-info-circle cursor"></i></a>';
      } else {
        $replace['#icone#'] = '<i id="maree' . $this->getId() . '" class="fa fa-info-circle cursor"></i>';
      }

      $templatename = 'maree';
    } else if ($this->getConfiguration('type') == 'surf') {
      foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
        $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
        }

      }

      $templatename = 'surf';
    } else if ($this->getConfiguration('type') == 'plage') {
      foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
        $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
        }

      }

      $templatename = 'plage';
    } else if ($this->getConfiguration('type') == 'uvi') {
      foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
        $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
        }

      }

      $templatename = 'uvi';
    } else if ($this->getConfiguration('type') == 'pollen') {
      $onetemplate = getTemplate('core', $version, '1pollen', 'vigilancemeteo');
      foreach ($this->getCmd('info') as $cmd) {
        switch ($cmd->execCmd()) {
          case '0':
          $color = 'black';
          break;
          case '1':
          $color = 'lime';
          break;
          case '2':
          $color = 'green';
          break;
          case '3':
          $color = 'yellow';
          break;
          case '4':
          $color = 'orange';
          break;
          case '5':
          $color = 'red';
          break;
        }
        if ($cmd->getLogicalId() == 'general') {
          $replace['#' . $cmd->getLogicalId() . '_color#'] = $color;
          if ($replace['#general_color#'] == "yellow" || $replace['#general_color#'] == "lime") {
            $replace['#general_font#'] = "black";
          } else {
            $replace['#general_font#'] = "white";
          }
          $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
          $replace['#id#'] = $this->getId();
          $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
          $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        } else {
          $sort[$cmd->getLogicalId()] = $cmd->execCmd();
          $unitreplace['#id#'] = $this->getId();
          $unitreplace['#value#'] = $cmd->execCmd();
          $unitreplace['#name#'] = $cmd->getName();
          $unitreplace['#width#'] = $cmd->execCmd() * 20;
          $unitreplace['#color#'] = $color;
          $unitreplace['#background-color#'] = $replace['#background-color#'];
          $slide[$cmd->getLogicalId()] = template_replace($unitreplace, $onetemplate);
        }
      }
      arsort($sort);
      $i=0;
      $replace['#slide1#'] = $replace['#slide2#'] = $replace['#slide3#'] = $replace['#slide4#'] =$replace['#slide5#'] = '';
      foreach ($sort as $key => $value) {
        if ($i<4) {
          $replace['#slide1#'] .= $slide[$key];
        } else if ($i<8) {
          $replace['#slide2#'] .= $slide[$key];
        } else if ($i<12) {
          $replace['#slide3#'] .= $slide[$key];
        } else if ($i<16) {
          $replace['#slide4#'] .= $slide[$key];
        } else {
          $replace['#slide5#'] .= $slide[$key];
        }
        $i++;
      }
      $templatename = 'pollen';
    } else if ($this->getConfiguration('type') == 'crue') {
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'niveau');
      $replace['#crue_history#'] = '';
      $replace['#crue#'] = $cmd->execCmd();
      $replace['#crue_id#'] = $cmd->getId();

      $replace['#crue_collect#'] = $cmd->getCollectDate();
      if ($cmd->getIsHistorized() == 1) {
        $replace['#crue_history#'] = 'history cursor';
      }

      $templatename = 'crue';
    } else if ($this->getConfiguration('type') == 'air') {
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'aqi');
      $cmdcolor = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'color');
      $replace['#aqifont#'] = "white";
      switch ($cmdcolor->execCmd()) {
        case 'green':
        $replace['#aqicolor#'] = "#00ff1e";
        $replace['#aqilevel#'] = "Good";
        $replace['#aqifont#'] = "black";
        break;
        case 'yellow':
        $replace['#aqicolor#'] = "#FFFF00";
        $replace['#aqilevel#'] = "Moderate";
        $replace['#aqifont#'] = "black";
        break;
        case 'orange':
        $replace['#aqicolor#'] = "#FFA500";
        $replace['#aqilevel#'] = "Unhealthy Sensitive";
        break;
        case 'red':
        $replace['#aqicolor#'] = "#E50000";
        $replace['#aqilevel#'] = "Unhealthy";
        break;
      }

      $replace['#aqi_history#'] = '';
      $replace['#aqi#'] = $cmd->execCmd();
      $replace['#aqi_id#'] = $cmd->getId();

      $replace['#aqi_collect#'] = $cmd->getCollectDate();
      if ($cmd->getIsHistorized() == 1) {
        $replace['#aqi_history#'] = 'history cursor';
      }

      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pm25');
      $replace['#pm25#'] = $cmd->execCmd();
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pm10');
      $replace['#pm10#'] = $cmd->execCmd();
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'no2');
      $replace['#no2#'] = $cmd->execCmd();
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'o3');
      $replace['#o3#'] = $cmd->execCmd();

      $templatename = 'air';
    } else if ($this->getConfiguration('type') == 'seisme') {
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'risk');
      $replace['#seisme_history#'] = '';
      $replace['#seisme#'] = $cmd->getConfiguration('value');
      $replace['#seisme_id#'] = $cmd->getId();

      $replace['#seisme_collect#'] = $cmd->getCollectDate();
      if ($cmd->getIsHistorized() == 1) {
        $replace['#seisme_history#'] = 'history cursor';
      }

      $templatename = 'seisme';
    } else if ($this->getConfiguration('type') == 'pluie1h') {
      $replace['#ville#'] = $this->getConfiguration('ville');
      $prevTexte = $this->getCmd(null,'prevTexte');
      $replace['#prevTexte#'] = (is_object($prevTexte)) ? nl2br($prevTexte->execCmd()) : '';
      $replace['#prevTexte_display#'] = (is_object($prevTexte) && $prevTexte->getIsVisible()) ? "#prevTexte_display#" : "none";

      $lastUpdate = $this->getCmd(null,'lastUpdate');
      $replace['#lastUpdate#'] = (is_object($lastUpdate)) ? $lastUpdate->execCmd() : '';

      $colors = Array();
      $color[0] = '#D6D7D7';
      $color[1] = '#FFFFFF';
      $color[2] = '#AAE8FF';
      $color[3] = '#48BFEA';
      $color[4] = '#0094CE';

      $text = Array();
      $text[0] = 'Données indisponibles';
      $text[1] = 'Pas de pluie';
      $text[2] = 'Pluie faible';
      $text[3] = 'Pluie modérée';
      $text[4] = 'Pluie forte';

      for($i=0; $i <= 11; $i++){
        $prev = $this->getCmd(null,'prev' . $i*5);
        if(is_object($prev)){
          $prevision = $prev->execCmd();
          $replace['#prev' . ($i*5) . '#'] = $prevision;
          $replace['#prev' . ($i*5) . 'Color#'] = $color[$prevision];
          $replace['#prev' . ($i*5) . 'Text#'] = $text[$prevision];
        }
      }
      $parameters = $this->getDisplay('parameters');
      if (is_array($parameters)) {
        foreach ($parameters as $key => $value) {
          $replace['#' . $key . '#'] = $value;
        }
      }
      $templatename = 'previsionpluie';
    }
    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $templatename, 'vigilancemeteo')));
  }

}

class vigilancemeteoCmd extends cmd {
  public function execute($_options = null) {
    if ($this->getLogicalId() == 'refresh') {
      $this->getEqLogic()->getInformations();
    }
  }
}

?>
