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

if (init('id') == '') {
  throw new Exception('{{L\'id de l\'équipement ne peut etre vide : }}' . init('op_id'));
}

$id = init('id');
$vigilancemeteo = vigilancemeteo::byId($id);
if (!is_object($vigilancemeteo)) {

  throw new Exception(__('Aucun equipement ne  correspond : Il faut (re)-enregistrer l\'équipement ', __FILE__) . init('action'));
}

if (strpos(network::getNetworkAccess('external'),'https') !== false) {
  $protocole='https:/';
} else {
  $protocole='http://';
}
if ($vigilancemeteo->getConfiguration('type') == 'maree') {
  $link = $protocole . 'maree.info/' . $vigilancemeteo->getConfiguration('port');
}
if ($vigilancemeteo->getConfiguration('type') == 'air') {
  if ($vigilancemeteo->getConfiguration('geoloc') == 'jeedom') {
            $latitude = config::byKey('info::latitude');
            $longitude = config::byKey('info::longitude');
        } else {
            $geotrav = eqLogic::byId($vigilancemeteo->getConfiguration('geoloc'));
            if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
                return;
            }
            $geolocval = geotravCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getConfiguration('geoloc'),'location:coordinate')->execCmd();
            $geoloctab = explode(',', trim($geolocval));
            $latitude = trim($geoloctab[0]);
            $longitude = trim($geoloctab[1]);
        }
  $link = $protocole . 'waqi.info/#/c/' . $latitude . '/' . $longitude . '/9.2z';
}
if ($vigilancemeteo->getConfiguration('type') == 'surf') {
  $link = $protocole . 'magicseaweed.com/';
}
if ($vigilancemeteo->getConfiguration('type') == 'pollen') {
  $link = $protocole . 'pollens.fr';
}
if ($vigilancemeteo->getConfiguration('type') == 'plage') {
  if ($vigilancemeteo->getConfiguration('geoloc') == 'jeedom') {
    $city = config::byKey('info::city');
    $postal = config::byKey('info::postalCode');
  } else {
    $geotrav = eqLogic::byId($vigilancemeteo->getConfiguration('geoloc'));
    if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
      return;
    }
    $postal = geotravCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getConfiguration('geoloc'),'location:zip')->execCmd();
    $city = geotravCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getConfiguration('geoloc'),'location:city')->execCmd();
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
if ($vigilancemeteo->getConfiguration('type') == 'vigilance') {
  if ($vigilancemeteo->getConfiguration('geoloc') == "jeedom") {
    $postal = config::byKey('info::postalCode');
    $departement = $postal[0] . $postal[1];
  } else {
    $departement = geotravCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getConfiguration('geoloc'),'location:department')->execCmd();
  }
  $link = $protocole . 'vigilance.meteofrance.com/Bulletin_sans.html?a=dept'.$departement.'&b=2&c=';
}
if ($vigilancemeteo->getConfiguration('type') == 'crue') {
  $link = $protocole . 'www.vigicrues.gouv.fr/niv3-station.php?CdStationHydro=' . $vigilancemeteo->getConfiguration('station') . '&CdEntVigiCru=9&GrdSerie=H&ZoomInitial=3&CdStationsSecondaires=';
}
if ($vigilancemeteo->getConfiguration('type') == 'pluie1h') {
  if ($vigilancemeteo->getConfiguration('geoloc') == 'jeedom') {
    $city = config::byKey('info::city');
    $postal = config::byKey('info::postalCode');
  } else {
    $geotrav = eqLogic::byId($vigilancemeteo->getConfiguration('geoloc'));
    if (!(is_object($geotrav) && $geotrav->getEqType_name() == 'geotrav')) {
      return;
    }
    $postal = geotravCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getConfiguration('geoloc'),'location:zip')->execCmd();
    $city = geotravCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getConfiguration('geoloc'),'location:city')->execCmd();
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
  $link = $protocole . "www.meteofrance.com/previsions-meteo-france/previsions-pluie/". $city ."/".$postal;
}


?>

<iframe src="<?php echo $link; ?>" height="100%" width="100%">You need a Frames Capable browser to view this content.</iframe>
