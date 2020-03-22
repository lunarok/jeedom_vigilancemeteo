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
    foreach (eqLogic::byType(__CLASS__, true) as $vigilancemeteo) {
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
    foreach (eqLogic::byType(__CLASS__, true) as $vigilancemeteo) {
      if ($vigilancemeteo->getConfiguration('type') == 'pluie1h') {
        $vigilancemeteo->getPluie();
      }
      $vigilancemeteo->refreshWidget();
    }
  }

  public static function cronHourly() {
    foreach (eqLogic::byType(__CLASS__, true) as $vigilancemeteo) {
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
      if ($this->getConfiguration('type') == 'gdacs') {
        $this->getGDACS();
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
      if ($this->getConfiguration('type') == 'gdacs') {
        $this->getGDACS();
      }
      $this->refreshWidget();
  }

public function loadCmdFromConf($_update = false) {

  if (!is_file(__DIR__ . '/../config/devices/' . $this->getConfiguration('type') . '.json')) {
    return;
  }
  $content = file_get_contents(__DIR__ . '/../config/devices/' . $this->getConfiguration('type') . '.json');
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
    if ($this->getConfiguration('geoloc') == "jeedom") {
    $postal = config::byKey('info::postalCode');
    $departement = $postal[0] . $postal[1];
  } else {
    $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
  }
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
  if ($this->getConfiguration('type') == 'gdacs') {
        $this->getGDACS();
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
  if ($this->getConfiguration('geoloc') == "jeedom") {
    $postal = config::byKey('info::postalCode');
    $departement = $postal[0] . $postal[1];
  } else {
    $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
  }
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
    log::add(__CLASS__, 'error', 'Unable to fetch ' . $url);
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

  log::add(__CLASS__, 'debug', 'Vigilance ' . $lvigilance);
  log::add(__CLASS__, 'debug', 'Crue ' . $lcrue);
  log::add(__CLASS__, 'debug', 'Risque ' . $lrisque);

  $this->checkAndUpdateCmd('vigilance', $lvigilance);
  $this->checkAndUpdateCmd('crue', $lcrue);
  $this->checkAndUpdateCmd('risque', $lrisque);
  $this->checkAndUpdateCmd('mer', $lmer);
  return ;
}
  
  public function getGDACS() {
    $feed = implode(file('http://yourdomains.com/feed.rss'));
$xml = simplexml_load_string($feed);
$json = json_encode($xml);
$array = json_decode($json,TRUE);
    log::add(__CLASS__, 'debug', 'Alertes : ' . print_r($array,true));
    $doc = new DOMDocument();
    $doc->load('https://www.gdacs.org/xml/rss.xml');
  foreach ($doc->getElementsByTagName('channel')->item(0)->getElementsByTagName('item') as key => $item) {
    if (isset($title[$item->getElementsByTagName('gdacs:eventtype')->item(0)->firstChild->nodeValue])) {
      continue;
    }
    $title[$item->getElementsByTagName('gdacs:eventtype')->item(0)->firstChild->nodeValue] = $item->getElementsByTagName('gdacs:title')->item(0)->firstChild->nodeValue;
    $link[$item->getElementsByTagName('gdacs:eventtype')->item(0)->firstChild->nodeValue] = $item->getElementsByTagName('gdacs:link')->item(0)->firstChild->nodeValue;
    $level[$item->getElementsByTagName('gdacs:eventtype')->item(0)->firstChild->nodeValue] = $item->getElementsByTagName('gdacs:alertlevel')->item(0)->firstChild->nodeValue;
    $date[$item->getElementsByTagName('gdacs:eventtype')->item(0)->firstChild->nodeValue] = $item->getElementsByTagName('pubDate')->item(0)->firstChild->nodeValue;
  }
  if (isset($title['EQ'])) {
    $this->checkAndUpdateCmd('EQ::title', $title['EQ']);
    $this->checkAndUpdateCmd('EQ::link', $link['EQ']);
    $this->checkAndUpdateCmd('EQ::level', $level['EQ']);
    $this->checkAndUpdateCmd('EQ::date', $date['EQ']);
  }
  if (isset($title['TC'])) {
    $this->checkAndUpdateCmd('TC::title', $title['TC']);
    $this->checkAndUpdateCmd('TC::link', $link['TC']);
    $this->checkAndUpdateCmd('TC::level', $level['TC']);
    $this->checkAndUpdateCmd('TC::date', $date['TC']);
  }
  if (isset($title['FL'])) {
    $this->checkAndUpdateCmd('FL::title', $title['FL']);
    $this->checkAndUpdateCmd('FL::link', $link['FL']);
    $this->checkAndUpdateCmd('FL::level', $level['FL']);
    $this->checkAndUpdateCmd('FL::date', $date['FL']);
  }
  if (isset($title['VO'])) {
    $this->checkAndUpdateCmd('VO::title', $title['VO']);
    $this->checkAndUpdateCmd('VO::link', $link['VO']);
    $this->checkAndUpdateCmd('VO::level', $level['VO']);
    $this->checkAndUpdateCmd('VO::date', $date['VO']);
  }
  if (isset($title['DR'])) {
    $this->checkAndUpdateCmd('DR::title', $title['DR']);
    $this->checkAndUpdateCmd('DR::link', $link['DR']);
    $this->checkAndUpdateCmd('DR::level', $level['DR']);
    $this->checkAndUpdateCmd('DR::date', $date['DR']);
  }
  return ;
}

public function getMaree() {
  $port = $this->getConfiguration('port');
  if ($port == '') {
    log::add(__CLASS__, 'error', 'Marée : Port non saisi');
    return;
  }
  $url = 'http://horloge.maree.frbateaux.net/ws' . $port . '.js?col=1&c=0';
  $result = file($url);
  if ($result === false) {
    return;
  }

  //log::add(__CLASS__, 'debug', 'Log ' . print_r($result, true));

  $maree = explode('<br>', $result[15]);
  $maree = explode('"', $maree[1]);
  $maree = $maree[0];
  $pleine = explode('PM ', $result[17] );
  $pleine = substr($pleine[1], 0, 5);
  $pleine = str_replace('h', '', $pleine);
  $basse = explode('BM ', $result[17]);
  $basse = substr($basse[1], 0, 5);
  $basse = str_replace('h', '', $basse);

  log::add(__CLASS__, 'debug', 'Marée ' . $maree . ', Pleine ' . $pleine . ', Basse ' . $basse);
  $this->checkAndUpdateCmd('maree', $maree);
  $this->checkAndUpdateCmd('pleine', $pleine);
  $this->checkAndUpdateCmd('basse', $basse);

  return ;
}

public function getPlage() {
  if ($this->getConfiguration('geoloc') == 'jeedom') {
        $city = config::byKey('info::city');
        $postal = config::byKey('info::postalCode');
    } else {
        $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
        if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
            return;
        }
        $postal = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:zip')->execCmd();
        $city = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:city')->execCmd();
    }
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
  $request_http->setNoReportError(true);
  $page = $request_http->exec(8);
  if ($page == '') {
    return;
  }
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
  log::add(__CLASS__, 'debug', 'Plage ' . $poss . ', URL ' . $adresse);
  return ;
}

public function getCrue() {
  $station = $this->getConfiguration('station');
  if ($station == '') {
    log::add(__CLASS__, 'error', 'Station non saisie');
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

  log::add(__CLASS__, 'debug', 'Valeur niveau: ' . $result);
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

  log::add(__CLASS__, 'debug', 'Valeur débit: ' . $result);
  $this->checkAndUpdateCmd('debit', $result);
  $this->checkAndUpdateCmd('datedebit', $date);

  return ;
}

public function getSeisme() {
  log::add(__CLASS__, 'debug', 'API Seisme removed, no result anymore');
  return;
  if ($this->getConfiguration('geoloc', 'none') == 'none') {
    return;
  }
  if ($this->getConfiguration('geoloc') == 'jeedom') {
            $city = config::byKey('info::city');
        } else {
            $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
            if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
                return;
            }
            $city = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:city')->execCmd();
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

  log::add(__CLASS__, 'debug', 'Seisme ' . $result);

  return ;
}

public function getAir() {
  $apikey = $this->getConfiguration('aqicn');
  if ($apikey == '') {
    log::add(__CLASS__, 'error', 'API non saisie');
    return;
  }
  if ($this->getConfiguration('geoloc') == 'jeedom') {
            $latitude = config::byKey('info::latitude');
            $longitude = config::byKey('info::longitude');
        } else {
            $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
            if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
                return;
            }
            $geolocval = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:coordinate')->execCmd();
            $geoloctab = explode(',', trim($geolocval));
            $latitude = trim($geoloctab[0]);
            $longitude = trim($geoloctab[1]);
        }
  $url = 'http://api.waqi.info/feed/geo:' . $latitude . ';' . $longitude . '/?token=' . $apikey;
  log::add(__CLASS__, 'debug', 'AQI URL ' . $url);
  $request_http = new com_http($url);
  $content = $request_http->exec(30);
  //$content = file_get_contents($url);
  if ($content === false) {
    return;
  }
  $json = json_decode($content, true);
  if (!isset($json['data']['aqi'])) {
    log::add(__CLASS__, 'error', 'Error in API call ' . $url);
    return;
  }
  log::add(__CLASS__, 'debug', 'Air ' . $json['data']['aqi'] . ' ' . $json['data']['city']['name']);
  if ($json['data']['aqi'] <= 50) {
    $color = 'green';
  } else if ($json['data']['aqi'] <= 100) {
    $color = 'yellow';
  } else if ($json['data']['aqi'] <= 150) {
    $color = 'orange';
  } else if ($json['data']['aqi'] <= 200) {
    $color = 'red';     // 204 0 51
  } else if ($json['data']['aqi'] <= 300) {
    $color = 'magenta'; // 102 0 53
  } else {
    $color = 'brown';
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
    log::add(__CLASS__, 'error', 'API non saisie');
    return;
  }
  if (null !== ($this->getConfiguration('surf', ''))) {
    $surf = $this->getConfiguration('surf', '');
    $url = 'http://magicseaweed.com/api/' . $apikey . '/forecast/?spot_id=' . $surf;
    $request_http = new com_http($url);
    $request_http->setNoReportError(true);
    $content = $request_http->exec(8);
    if ($content == '') {
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
    $geoloc = $this->getConfiguration('geoloc', 'none');
  if ($geoloc == 'none') {
    log::add(__CLASS__, 'error', 'Pollen geoloc non configuré.');
    return;
  }
  if ($geoloc == "jeedom") {
    $departement = substr(config::byKey('info::postalCode'),0,2);
  } else {
    $geotrav = eqLogic::byId($geoloc);
    if (is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav') {
      $geotravCmd = geotravCmd::byEqLogicIdAndLogicalId($geoloc,'location:department');
      if(is_object($geotravCmd))
        $departement = $geotravCmd->execCmd();
      else {
        log::add(__CLASS__, 'error', 'Pollen geotravCmd object not found');
        return;
      }
    }
    else {
      log::add(__CLASS__, 'error', 'Pollen geotrav object not found');
      return;
    }
  }
  if ($departement == "2A" or $departement == "2B") {$departement = "20";}
  log::add(__CLASS__, 'debug', 'Pollen departement : ' . $departement);
  $url = 'https://www.pollens.fr/risks/thea/counties/' .$departement;
  $pollenData = null;
    $request_http = new com_http($url);
    $request_http->setNoReportError(true);
    $pollenJson = $request_http->exec(8);
    if ($pollenJson == '') {
      log::add(__CLASS__, 'error', 'Impossible d\'obtenir les informations pollens.fr');
      return;
    }
    $pollenData = json_decode($pollenJson, true);
  if ( is_null($pollenData) ) { // Pas de reponse pollens.fr tous les levels a -1
    $this->checkAndUpdateCmd('general', -1);
    for ( $i=1; $i<20; $i++)
      $this->checkAndUpdateCmd('pollen' . $i, -1);
    return;
  }
  else {
    $this->checkAndUpdateCmd('general', $pollenData['riskLevel']);
    foreach ( $pollenData['risks'] as $pollen ) {
      $nomPollen = $pollen['pollenName']; $level = $pollen['level'];
      switch ( $nomPollen ) {
        case "Cyprès" :
          $this->checkAndUpdateCmd('pollen1', $level); break;
        case "Noisetier" :
          $this->checkAndUpdateCmd('pollen2', $level); break;
        case "Aulne" :
          $this->checkAndUpdateCmd('pollen3', $level); break;
        case "Peuplier" :
          $this->checkAndUpdateCmd('pollen4', $level); break;
        case "Saule" :
          $this->checkAndUpdateCmd('pollen5', $level); break;
        case "Frêne" :
          $this->checkAndUpdateCmd('pollen6', $level); break;
        case "Charme" :
          $this->checkAndUpdateCmd('pollen7', $level); break;
        case "Bouleau" :
          $this->checkAndUpdateCmd('pollen8', $level); break;
        case "Platane" :
          $this->checkAndUpdateCmd('pollen9', $level); break;
        case "Chêne" :
          $this->checkAndUpdateCmd('pollen10', $level); break;
        case "Olivier" :
          $this->checkAndUpdateCmd('pollen11', $level); break;
        case "Tilleul" :
          $this->checkAndUpdateCmd('pollen12', $level); break;
        case "Châtaignier" :
          $this->checkAndUpdateCmd('pollen13', $level); break;
        case "Oseille" :
          $this->checkAndUpdateCmd('pollen14', $level); break;
        case "Graminées" :
          $this->checkAndUpdateCmd('pollen15', $level); break;
        case "Plantain" :
          $this->checkAndUpdateCmd('pollen16', $level); break;
        case "Urticacées" :
          $this->checkAndUpdateCmd('pollen17', $level); break;
        case "Armoise" :
          $this->checkAndUpdateCmd('pollen18', $level); break;
        case "Ambroisies" :
          $this->checkAndUpdateCmd('pollen19', $level); break;
      }
    }
  }
  return;
}

  /**
  * Retrieve weather forecast for the next hour
  *
  * @return boolean True if success, false otherwise
  */
  public function getPluie() {
    $ville = $this->getConfiguration('ville');
    if(empty($ville)) {
      log::add(__CLASS__, 'error', __('La ville n\'est pas configurée', __FILE__));
      return false;
    }

    $url = sprintf('http://www.meteofrance.com/mf3-rpc-portlet/rest/pluie/%s', $ville);
    //log::add(__CLASS__, 'debug', __FUNCTION__ .' Ville: ' .$ville);
    $request_http = new com_http($url);
    $request_http->setNoReportError(true);
    $prevPluieJson = $request_http->exec(8);
    if ($prevPluieJson == '') {
      log::add(__CLASS__, 'warning', 'Impossible d\'obtenir les informations Météo France... ');
      for($i=0; $i <= 11; $i++) {
        $cmdName = sprintf('prev%d', $i * 5);
        $this->checkAndUpdateCmd($cmdName, 0);
      }
      $this->checkAndUpdateCmd('prevTexte', 'Données indisponibles. Problème liaison Météo France');
      $this->checkAndUpdateCmd('echeance', date('Hi'));
      return;
    }
    $prevPluieData = json_decode($prevPluieJson, true);

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
    $echeance = substr($prevPluieData['echeance'],-4);
    $this->checkAndUpdateCmd('echeance', $echeance);
    log::add(__CLASS__, 'info', sprintf("%s '%s' %s '%s'",
    __('VigilanceMeteo de type', __FILE__),
    $this->getConfiguration('type'),
    __('mise a jour pour la ville', __FILE__),
    $this->getConfiguration('villeNom')));
    return true;
  }

  public function getLink() {
    if (strpos(network::getNetworkAccess('external'),'https') !== false) {
      $protocole='https://';
    } else {
      $protocole='http://';
    }
    if ($this->getConfiguration('type') == 'maree') {
      $link = $protocole . 'maree.info/' . $this->getConfiguration('port');
    }
    if ($this->getConfiguration('type') == 'air') {
      if ($this->getConfiguration('geoloc') == 'jeedom') {
                $latitude = config::byKey('info::latitude');
                $longitude = config::byKey('info::longitude');
            } else {
                $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
                if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
                    return;
                }
                $geolocval = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:coordinate')->execCmd();
                $geoloctab = explode(',', trim($geolocval));
                $latitude = trim($geoloctab[0]);
                $longitude = trim($geoloctab[1]);
            }
      $link = $protocole . 'waqi.info/#/c/' . $latitude . '/' . $longitude . '/9.2z';
    }
    if ($this->getConfiguration('type') == 'surf') {
      $link = $protocole . 'magicseaweed.com/';
    }
    if ($this->getConfiguration('type') == 'pollen') {
      $link = $protocole . 'pollens.fr';
    }
    if ($this->getConfiguration('type') == 'plage') {
      if ($this->getConfiguration('geoloc') == 'jeedom') {
        $city = config::byKey('info::city');
        $postal = config::byKey('info::postalCode');
      } else {
        $geotrav = eqLogic::byId($this->getConfiguration('geoloc'));
        if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
          return;
        }
        $postal = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:zip')->execCmd();
        $city = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:city')->execCmd();
      }
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
      $link = $protocole . "www.meteofrance.com/previsions-meteo-plages/". $city ."/".$postal;
    }
    if ($this->getConfiguration('type') == 'vigilance') {
      if ($this->getConfiguration('geoloc') == "jeedom") {
        $postal = config::byKey('info::postalCode');
        $departement = $postal[0] . $postal[1];
      } else {
        $departement = geotravCmd::byEqLogicIdAndLogicalId($this->getConfiguration('geoloc'),'location:department')->execCmd();
      }
      $link = $protocole . 'vigilance.meteofrance.com/Bulletin_sans.html?a=dept'.$departement.'&b=2&c=';
    }
    if ($this->getConfiguration('type') == 'crue') {
      $link = $protocole . 'www.vigicrues.gouv.fr/niv3-station.php?CdStationHydro=' . $this->getConfiguration('station') . '&CdEntVigiCru=9&GrdSerie=H&ZoomInitial=3&CdStationsSecondaires=';
    }
    if ($this->getConfiguration('type') == 'pluie1h') {
      $city = $this->getConfiguration('villeNom');
      $explode = explode(' ',$city);
      $city = $explode[0];
      $postal = str_replace('(','',$explode[1]);
      $postal = str_replace(')','',$postal);
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
      $link = $protocole . 'www.meteofrance.com/previsions-meteo-france/previsions-pluie/'. $city . '/' . $postal;
    }
    log::add(__CLASS__, 'debug', 'Link value : ' . $link);
    return $link;
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
    $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'refresh');
    if(is_object($cmd)) { $replace['#refresh#'] = $cmd->getId();}
    if ($this->getConfiguration('type') == 'vigilance') {
      foreach ($this->getCmd('info') as $cmd) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
        $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
        $valeur=ucfirst($cmd->execCmd());
        switch ($valeur) {
          case 'Vert': $valeur = "#00ff1e"; break;
          case 'Jaune': $valeur = "#FFFF00"; break;
          case 'Orange': $valeur = "#FFA500"; break;
          case 'Rouge': $valeur = "#E50000"; break;
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
      $templatename = 'vigilancemeteo';
    } else if ($this->getConfiguration('type') == 'maree') {
      $replace['#portid#'] = $this->getConfiguration('port');

      foreach ($this->getCmd('info') as $cmd) {
        $logicalId = $cmd->getLogicalId();
        $replace['#' . $logicalId . '_history#'] = '';
        $replace['#' . $logicalId . '_id#'] = $cmd->getId();

        if ($logicalId == 'maree') {
          $replace['#' . $logicalId . '#'] = $cmd->execCmd();
        } else {
          $replace['#' . $logicalId . '#'] = substr_replace(str_pad($cmd->execCmd(), 4, '0', STR_PAD_LEFT),':',-2,0);
        }
        $replace['#' . $logicalId . '_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#' . $logicalId . '_history#'] = 'history cursor';
        }
      }

      if (strpos(network::getNetworkAccess('external'),'https') !== false) {
        $replace['#icone#'] = '<a target="_blank" href="http://maree.info/' . $this->getConfiguration('port') . '"><i class="fas fa-info-circle cursor"></i></a>';
      } else {
        $replace['#icone#'] = '<i id="maree' . $this->getId() . '" class="fas fa-info-circle cursor"></i>';
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
      $replace['#background-color#'] = '#262626';
      $chkDisplay0 = $this->getConfiguration('displayNullPollen');
      foreach ($this->getCmd('info') as $cmd) {
        switch ($cmd->execCmd()) {
          case '-1': $color = 'black'; break;
          case '0':  $color = 'black'; break;
          case '1':  $color = 'lime';  break;
          case '2':  $color = 'green'; break;
          case '3':  $color = 'yellow'; break;
          case '4':  $color = 'orange'; break;
          case '5':  $color = 'red';    break;
        }
        if ($cmd->getLogicalId() == 'general') {
          $replace['#general_color#'] = $color;
          if ($replace['#general_color#'] == "yellow" || $replace['#general_color#'] == "lime") {
            $replace['#general_font#'] = "black";
          } else {
            $replace['#general_font#'] = "white";
          }
          $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();
          $replace['#id#'] = $this->getId();
          $general = $cmd->execCmd();
          $replace['#' . $cmd->getLogicalId() . '#'] = (($general==-1)?"?":$general);
          $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
        } else {
          $value =  $cmd->execCmd();
          if($cmd->getIsVisible() &&
            (($version == 'dashboard' && ($value >0 || ($value == 0 && $chkDisplay0 == 1))) ||
            ($version == 'mobile' && $value >0))) {
            $sort[$cmd->getLogicalId()] = $cmd->execCmd();
            $unitreplace['#id#'] = $this->getId();
            $unitreplace['#value#'] = $value;
            $unitreplace['#name#'] = $cmd->getName();
            $unitreplace['#width#'] = $cmd->execCmd() * 20;
            $unitreplace['#color#'] = $color;
            $unitreplace['#background-color#'] = $replace['#background-color#'];
            $slide[$cmd->getLogicalId()] = template_replace($unitreplace, $onetemplate);
          }
        }
      }
      arsort($sort);
      $i=0;
      $repl[0] = $repl[1] = $repl[2] = $repl[3] =$repl[4] = '';
      foreach ($sort as $key => $value) {
        if ($i < 5) $repl[0] .= $slide[$key];
        else if ($i < 10) $repl[1] .= $slide[$key];
        else if ($i < 15) $repl[2] .= $slide[$key];
        else if ($i < 20) $repl[3] .= $slide[$key];
        else $repl[4] .= $slide[$key];
        $i++;
      }
      if ($version == 'dashboard' ) {
        $replace['#slide#'] = '<div class="item active"> ' .$repl[0] .'</div>';
        for($i=1;$i<5;$i++) if($repl[$i] != '')
          $replace['#slide#'] .= '<div class="item"> ' .$repl[$i] .'</div>';
        if (count($sort) <= 5) {
          $replace['#hiding#'] = 'style="display: none;"';
        } else {
          $replace['#hiding#'] = '';
        }
      }
      else {
        $replace['#slide#'] = '';
        for($i=0;$i<5;$i++) if($repl[$i] != '')
          $replace['#slide#'] .= $repl[$i];
      }
      $templatename = 'pollen';
    } else if ($this->getConfiguration('type') == 'crue') {
      $replace['#crue_history#'] = '';
      $station = $this->getConfiguration('station');
      $replace['#station#'] = $station;
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'niveau');
      if(is_object($cmd)) {
        $replace['#crue#'] = $cmd->execCmd();
        $replace['#crue_id#'] = $cmd->getId();
        $replace['#crue_collect#'] = $cmd->getCollectDate();
        if ($cmd->getIsHistorized() == 1) {
          $replace['#crue_history#'] = 'history cursor';
        }
      }
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'dateniveau');
      $replace['#dateniveau#'] = '';
      if(is_object($cmd)) {
        $date = $cmd->execCmd();
        $date = date_create_from_format("Y-m-d\TH:i:sP", $date);
        if ( $date != false ) {
          $date = strftime("%A %e %b %H:%M", $date->getTimestamp());
          $replace['#dateniveau#'] = $date;
        }
      }
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'debit');
      if(is_object($cmd)) {
        if ($cmd->execCmd() == 0) {
          $replace['#debit#'] = '';
        } else {
          $cmddeb = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'datedebit');
          $datedebit = '';
          if(is_object($cmddeb)) {
            $date = $cmddeb->execCmd();
            $date = date_create_from_format("Y-m-d\TH:i:sP", $date);
            if ( $date != false ) {
              $date = strftime("%A %e %b %H:%M", $date->getTimestamp());
              $datedebit = $date;
            }
          }
          $hitsory = '';
          if ($cmddeb->getIsHistorized() == 1) {
            $hitsory = 'history cursor';
          }
          $replace['#debit#'] = '<span style="margin-left: 30px;" class="debit ' . $hitsory . ' data-cmd_id="' . $cmd->getId() . '" title="Débit mesuré le ' . $datedebit . ' (' . $cmd->getCollectDate() . ')">D=' . $cmd->execCmd() . 'm3/s</span>';
        }
      }
      $templatename = 'crue';
    } else if ($this->getConfiguration('type') == 'air') {
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'aqi');
      $cmdcolor = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'color');
      $replace['#aqifont#'] = "white";
      switch ($cmdcolor->execCmd()) {
        case 'green':
        $replace['#aqicolor#'] = "#00ff1e";
        $replace['#aqilevel#'] = "Bon";
        $replace['#aqifont#'] = "black";
        break;
        case 'yellow':
        $replace['#aqicolor#'] = "#FFde33";
        $replace['#aqilevel#'] = "Modéré";
        $replace['#aqifont#'] = "black";
        break;
        case 'orange':
        $replace['#aqicolor#'] = "#FF9933";
        $replace['#aqilevel#'] = "Mauvais pour les groupes sensibles";
        break;
        case 'red':
        $replace['#aqicolor#'] = "#CC0033";
        $replace['#aqilevel#'] = "Mauvais";
        break;
        case 'magenta':
        $replace['#aqicolor#'] = "#660035";
        $replace['#aqilevel#'] = "Trés mauvais";
        break;
        case 'brown':
        $replace['#aqicolor#'] = "#7E0023";
        $replace['#aqilevel#'] = "Dangereux";
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
      if(is_object($cmd)) $replace['#pm25#'] = $cmd->execCmd();
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pm10');
      if(is_object($cmd)) $replace['#pm10#'] = $cmd->execCmd();
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'no2');
      if(is_object($cmd)) $replace['#no2#'] = $cmd->execCmd();
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'o3');
      if(is_object($cmd)) $replace['#o3#'] = $cmd->execCmd();

      $templatename = 'air';
    } else if ($this->getConfiguration('type') == 'seisme') {
      $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'risk');
      $replace['#seisme_history#'] = '';
      if(is_object($cmd)) {
        $replace['#seisme#'] = $cmd->getConfiguration('value');
        $replace['#seisme_id#'] = $cmd->getId();
        $replace['#seisme_collect#'] = $cmd->getCollectDate();
      }
      if ($cmd->getIsHistorized() == 1) {
        $replace['#seisme_history#'] = 'history cursor';
      }

      $templatename = 'seisme';
    } else if ($this->getConfiguration('type') == 'pluie1h') {
      $replace['#ville#'] = $this->getConfiguration('ville');
      $prevTexte = $this->getCmd(null,'prevTexte');
      $replace['#prevTexte#'] = (is_object($prevTexte)) ? nl2br($prevTexte->execCmd()) : '';
      $replace['#prevTexte_display#'] = (is_object($prevTexte) && $prevTexte->getIsVisible()) ? "#prevTexte_display#" : "none";

      $echeance = $this->getCmd(null,'echeance');
      if (is_object($echeance)) {
        $heure = substr_replace($echeance->execCmd(),':',-2,0);
        $replace['#heure#'] = $heure;
        $replace['#h30#'] = date('H:i',strtotime('+ 30 minutes', mktime($heure[0] . $heure[1], $heure[3] . $heure[4])));
        $replace['#h1h#'] = date('H:i',strtotime('+ 1 hour', mktime($heure[0] . $heure[1], $heure[3] . $heure[4])));
      }

      $color = Array();
      $color[0] = '';
      $color[1] = '';
      $color[2] = ' background: #AAE8FF';
      $color[3] = ' background: #48BFEA';
      $color[4] = ' background: #0094CE';

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
    if (file_exists( __DIR__ ."/../template/$_version/".$templatename."_user.html")) {
      return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $templatename."_user", __CLASS__)));
    }
    else {
      return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, $templatename, __CLASS__)));
    }
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
