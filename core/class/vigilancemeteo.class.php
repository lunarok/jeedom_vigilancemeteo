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
                $vigilancemeteo->refreshWidget();
            }
            if ($vigilancemeteo->getConfiguration('type') == 'crue') {
                $vigilancemeteo->getCrue();
                $vigilancemeteo->refreshWidget();
            }
        }
        log::add('vigilancemeteo', 'debug', '15mn cron');
    }

    public static function cron5() {
        foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
            if ($vigilancemeteo->getConfiguration('type') == 'pluie1h') {
                $vigilancemeteo->getPluie();
                $vigilancemeteo->refreshWidget();
            }
        }
        log::add('vigilancemeteo', 'debug', '5mn cron');
    }

    public static function cronHourly() {
        foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
            if ($vigilancemeteo->getConfiguration('type') == 'maree') {
                $vigilancemeteo->getMaree();
                $vigilancemeteo->refreshWidget();
            }
            if ($vigilancemeteo->getConfiguration('type') == 'air') {
                $vigilancemeteo->getAir();
                $vigilancemeteo->refreshWidget();
            }
            if ($vigilancemeteo->getConfiguration('type') == 'seisme') {
                $vigilancemeteo->getSeisme();
                $vigilancemeteo->refreshWidget();
            }
            if ($vigilancemeteo->getConfiguration('type') == 'surf') {
                $vigilancemeteo->getSurf();
                $vigilancemeteo->refreshWidget();
            }
            if ($vigilancemeteo->getConfiguration('type') == 'pollen') {
                $vigilancemeteo->getPollen();
                $vigilancemeteo->refreshWidget();
            }
        }
        log::add('vigilancemeteo', 'debug', 'Hourly cron');
    }

    public static function cronDaily() {
        foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
            foreach ($vigilancemeteo->getCmd() as $cmd) {
                $cmd->setConfiguration('alert', '0');
                $cmd->setConfiguration('repeatEventManagement','always');
                $cmd->save();
            }
        }
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
        if (isset($device['name']) && !$_update) {
            $this->setName('[' . $this->getLogicalId() . ']' . $device['name']);
        }
        $this->import($device);
    }

    public function postAjax() {
        $this->loadCmdFromConf();
    }

    public function postUpdate() {
        $depmer = array("06","11","13","14","17","2A","2B","22","29","30","33","34","35","40","44","50","56","59","62","64","66","76","80","83","85");
        if ($this->getConfiguration('type') == 'vigilance') {
            if (in_array($this->getConfiguration('departement'), $depmer)) {
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
        $departement = $this->getConfiguration('departement');
        $alert = str_replace('#','',$this->getConfiguration('alert'));
        if ($departement == '92' || $departement == '93' || $departement == '94') {
            $departement = '75';
        }
        $lvigilance = "vert";
        $lcrue = "vert";
        $lrisque = "RAS";
        $lmer = "vert";

        $doc = new DOMDocument();
        $doc->load('http://vigilance.meteofrance.com/data/NXFR34_LFPW_.xml');

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
                        $lrisque = "vent";
                        break;
                        case 2:
                        $lrisque = "pluie-inondation";
                        break;
                        case 3:
                        $lrisque = "orages";
                        break;
                        case 4:
                        $lrisque = "inondations";
                        break;
                        case 5:
                        $lrisque = "neige-verglas";
                        break;
                        case 6:
                        $lrisque = "canicule";
                        break;
                        case 7:
                        $lrisque = "grand-froid";
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
            $alertmess = "";
            //log::add('vigilancemeteo', 'debug', $cmd->getConfiguration('data'));
            if($cmd->getConfiguration('data')=="vigilance"){
                if ($lvigilance != "vert") {
                    if ($cmd->getConfiguration('alert') == '0' && $alert != '') {
                        $cmd->setConfiguration('alert', '1');
                        $cmdalerte = cmd::byId($alert);
                        if ($alertmess != "") {
                            $alertmess = $alertmess . ", Niveau " . $lcrue . " pour la vigilance";
                        } else {
                            $alertmess = "Niveau " . $lcrue . " pour la vigilance";
                        }
                    }
                } else {
                    $cmd->setConfiguration('alert', '0');
                }
                $cmd->setConfiguration('value', $lvigilance);
                $cmd->save();
                $cmd->event($lvigilance);
            }elseif($cmd->getConfiguration('data')=="crue") {
                log::add('vigilancemeteo', 'debug', $cmd->getConfiguration('data') . ' ' . $lcrue . ' ' . $cmd->getConfiguration('alert'));
                if ($lcrue != "vert") {
                    if ($cmd->getConfiguration('alert') == '0' && $alert != '') {
                        $cmd->setConfiguration('alert', '1');
                        if ($alertmess != "") {
                            $alertmess = $alertmess . ", Niveau " . $lcrue . " pour le risque de crue";
                        } else {
                            $alertmess = "Niveau " . $lcrue . " pour le risque de crue";
                        }
                    }
                } else {
                    $cmd->setConfiguration('alert', '0');
                }
                $cmd->setConfiguration('value', $lcrue);
                $cmd->save();
                $cmd->event($lcrue);
            }elseif($cmd->getConfiguration('data')=="risque"){
                if ($lrisque != "RAS") {
                    if ($cmd->getConfiguration('alert') == '0' && $alert != '') {
                        $cmd->setConfiguration('alert', '1');
                        if ($alertmess != "") {
                            $alertmess = $alertmess . ", Risque " . $lrisque;
                        } else {
                            $alertmess = "Risque " . $lrisque;
                        }
                    }
                } else {
                    $cmd->setConfiguration('alert', '0');
                }
                $cmd->setConfiguration('value', $lrisque);
                $cmd->save();
                $cmd->event($lrisque);
            }elseif($cmd->getConfiguration('data')=="mer"){
                if ($lmer != "vert") {
                    if ($cmd->getConfiguration('alert') == '0' && $alert != '') {
                        $cmd->setConfiguration('alert', '1');
                        if ($alertmess != "") {
                            $alertmess = $alertmess . ", Niveau " . $lmer . " pour le risque bord de mer";
                        } else {
                            $alertmess = "Niveau " . $lmer . " pour le risque bord de mer";
                        }
                    }
                } else {
                    $cmd->setConfiguration('alert', '0');
                }
                $cmd->setConfiguration('value', $lmer);
                $cmd->save();
                $cmd->event($lmer);
            }
            if ($alertmess != "") {
                $cmdalerte = cmd::byId($alert);
                $options['title'] = "Alerte Météo";
                $options['message'] = "Dpt ".$departement." : " . $alertmess;
                $cmdalerte->execCmd($options);
            }
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

        //log::add('maree', 'debug', 'Log ' . print_r($result, true));

        $maree = explode('<br>', $result[14]);
        $maree = explode('"', $maree[1]);
        $maree = $maree[0];
        $pleine = explode('PM ', $result[16] );
        $pleine = substr($pleine[1], 0, 5);
        $pleine = str_replace('h', '', $pleine);
        $basse = explode('BM ', $result[16]);
        $basse = substr($basse[1], 0, 5);
        $basse = str_replace('h', '', $basse);

        log::add('vigilancemeteo', 'debug', 'Marée ' . $maree . ', Pleine ' . $pleine . ', Basse ' . $basse);

        $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'maree');
        $cmdlogic->setConfiguration('value', $maree);
        $cmdlogic->save();
        $cmdlogic->event($maree);

        $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pleine');
        $cmdlogic->setConfiguration('value', $pleine);
        $cmdlogic->save();
        $cmdlogic->event($pleine);

        $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'basse');
        $cmdlogic->setConfiguration('value', $basse);
        $cmdlogic->save();
        $cmdlogic->event($basse);

        return ;
    }

    public function getCrue() {
        $station = $this->getConfiguration('station');
        if ($station == '') {
            log::add('vigilancemeteo', 'error', 'Station non saisie');
            return;
        }
        $url = 'http://www.vigicrues.gouv.fr/services/observations.xml/?CdStationHydro='.$station;
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
        $city = $this->getConfiguration('openhazards');
        if ($city == '') {
            log::add('vigilancemeteo', 'error', 'Ville non saisie');
            return;
        }
        $url = 'http://api.openhazards.com/GetEarthquakeProbability?q=' . $city . '&m=5&r=100&w=3';
        $doc = new DOMDocument();
        $doc->load($url);

        $result = 0;
        foreach($doc->getElementsByTagName('prob') as $data) {
            $result = str_replace("%", "", $data->nodeValue);
        }

        $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'risk');
        $cmdlogic->setConfiguration('value', $result);
        $cmdlogic->save();
        $cmdlogic->event($result);

        log::add('vigilancemeteo', 'debug', 'Seisme ' . $result);

        return ;
    }

    public function getAir() {
        $apikey = $this->getConfiguration('aqicn');
        if ($apikey == '') {
            log::add('vigilancemeteo', 'error', 'API non saisie');
            return;
        }
        if (null !== ($this->getConfiguration('geoloc', '')) && $this->getConfiguration('geoloc', '') != 'none') {
            $geoloc = $this->getConfiguration('geoloc', '');
            $geolocCmd = geolocCmd::byId($geoloc);
            if ($geolocCmd->getConfiguration('mode') == 'fixe') {
                $geolocval = $geolocCmd->getConfiguration('coordinate');
            } else {
                $geolocval = $geolocCmd->execCmd();
            }
            $geoloctab = explode(',', trim($geolocval));
            $latitude = trim($geoloctab[0]);
            $longitude = trim($geoloctab[1]);
            $url = 'http://api.waqi.info/feed/geo:' . $latitude . ';' . $longitude . '/?token=' . $apikey;
            $json = json_decode(file_get_contents($url), true);
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
            $this->checkAndUpdateCmd('o3', $json['data']['iaqi']['o3']['v']);
            $this->checkAndUpdateCmd('pm10', $json['data']['iaqi']['pm10']['v']);
            $this->checkAndUpdateCmd('pm25', $json['data']['iaqi']['pm25']['v']);
            $this->checkAndUpdateCmd('t', $json['data']['iaqi']['t']['v']);
            $this->checkAndUpdateCmd('h', $json['data']['iaqi']['h']['v']);
            $this->checkAndUpdateCmd('p', $json['data']['iaqi']['p']['v']);
        }
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
            $json = json_decode(file_get_contents($url), true);

            $this->checkAndUpdateCmd('minimum', $json[0]['swell']['minBreakingHeight']);
            $this->checkAndUpdateCmd('maximum', $json[0]['swell']['maxBreakingHeight']);
            $this->checkAndUpdateCmd('primaryHeight', $json[0]['swell']['components']['primary']['height']);
            $this->checkAndUpdateCmd('primaryPeriod', $json[0]['swell']['components']['primary']['period']);
            $this->checkAndUpdateCmd('compassDirection', $json[0]['swell']['components']['primary']['compassDirection']);

        }
        return ;
    }

    public function getPollen() {
        $departement = $this->getConfiguration('departement');
        if ($departement == '') {
            log::add('vigilancemeteo', 'error', 'Département non saisi');
            return;
        }
        $im = @imagecreatefrompng("http://www.pollens.fr/docs/Departements_de_France-simple.png");
        $xy = vigilancemeteo::getDep();
        $rgb = @imagecolorat($im, $xy[$departement][0], $xy[$departement][1]);
        $colors = @imagecolorsforindex($im, $rgb);
        $pollen = vigilancemeteo::getPollenLevel($colors['red'],$colors['green'],$colors['blue']);
        //log::add('vigilancemeteo', 'debug', 'Coordonnées ' . $xy[$departement][0] . ' ' . $xy[$departement][1] . ' level : ' . $pollen);
        $this->checkAndUpdateCmd('general', $pollen);//0 green, 1 yellow, 2 orange, 3 red

        $i = 1;
        $im = @imagecreatefromgif("http://www.pollens.fr/docs/d%20".$departement.".gif");
        $x = 116;$y = 45;
        while ($i != 20) {
            $rgb = @imagecolorat($im, $x, $y);
            $colors = @imagecolorsforindex($im, $rgb);
            $pollen = vigilancemeteo::getPollenLevel($colors['red'],$colors['green'],$colors['blue']);
            $this->checkAndUpdateCmd('pollen' . $i, $pollen);
            $y = $y + 20;
            $i++;
        }
        return ;
        }

        public function getPollenLevel($red,$green,$blue) {
            //0 absence, 1 vert clair, 2 vert foncé, 3 jaune, 4 orange, 5 rouge
            $level = 0;
            if ($red == 0 && $green == 255 && $blue == 0) {
                $level = 1;
            } elseif (($red == 0 && $green == 176 && $blue == 80) || ($red == 0 && $green == 128 && $blue == 0)) {
                $level = 2;
            } elseif ($red == 255 && $green == 255 && $blue == 0) {
                $level = 3;
            } elseif (($red == 247 && $green == 150 && $blue == 70) || ($red == 255 && $green == 127 && $blue == 42)) {
                $level = 4;
            } elseif ($red == 255 && $green == 0 && $blue == 0) {
                $level = 5;
            }
            log::add('vigilancemeteo', 'debug', 'Couleur ' . $red . ' ' . $green . ' ' . $blue . ' : ' . $level);
            return $level;
        }

        function getDep() {
            $dep[1] = array(439,316);
            $dep[2] = array(360,91);
            $dep[3] = array(333,305);
            $dep[4] = array(483,447);
            $dep[5] = array(496,411);
            $dep[6] = array(532,454);
            $dep[7] = array(398,419);
            $dep[8] = array(403,93);
            $dep[9] = array(266,531);
            $dep[10] = array(375,180);
            $dep[11] = array(305,520);
            $dep[12] = array(320,439);
            $dep[13] = array(435,490);
            $dep[14] = array(182,124);
            $dep[15] = array(321,389);
            $dep[16] = array(206,346);
            $dep[17] = array(167,341);
            $dep[18] = array(312,275);
            $dep[19] = array(282,372);
            $dep[20] = array(530,557);
            $dep[21] = array(411,229);
            $dep[22] = array(83,167);
            $dep[23] = array(292,396);
            $dep[24] = array(228,389);
            $dep[25] = array(483,251);
            $dep[26] = array(437,411);
            $dep[27] = array(243,124);
            $dep[28] = array(262,175);
            $dep[29] = array(33,177);
            $dep[30] = array(396,456);
            $dep[31] = array(262,495);
            $dep[32] = array(223,480);
            $dep[33] = array(169,402);
            $dep[34] = array(340,490);
            $dep[35] = array(135,177);
            $dep[36] = array(269,275);
            $dep[37] = array(232,244);
            $dep[38] = array(455,370);
            $dep[39] = array(461,281);
            $dep[40] = array(160,462);
            $dep[41] = array(262,221);
            $dep[42] = array(390,354);
            $dep[43] = array(359,383);
            $dep[44] = array(119,240);
            $dep[45] = array(314,206);
            $dep[46] = array(267,419);
            $dep[47] = array(210,438);
            $dep[48] = array(364,434);
            $dep[49] = array(178,236);
            $dep[50] = array(150,130);
            $dep[51] = array(385,136);
            $dep[52] = array(431,184);
            $dep[53] = array(163,192);
            $dep[54] = array(470,156);
            $dep[55] = array(439,149);
            $dep[56] = array(76,203);
            $dep[57] = array(485,126);
            $dep[58] = array(359,259);
            $dep[59] = array(355,54);
            $dep[60] = array(303,115);
            $dep[61] = array(215,160);
            $dep[62] = array(293,39);
            $dep[63] = array(340,341);
            $dep[64] = array(148,505);
            $dep[65] = array(206,521);
            $dep[66] = array(318,549);
            $dep[67] = array(532,143);
            $dep[68] = array(520,197);
            $dep[69] = array(409,339);
            $dep[70] = array(476,221);
            $dep[71] = array(396,288);
            $dep[72] = array(215,197);
            $dep[73] = array(494,367);
            $dep[74] = array(496,322);
            $dep[75] = array(308,147);
            $dep[76] = array(292,65);
            $dep[77] = array(331,158);
            $dep[78] = array(288,149);
            $dep[79] = array(187,287);
            $dep[80] = array(295,74);
            $dep[81] = array(293,469);
            $dep[82] = array(253,452);
            $dep[83] = array(494,497);
            $dep[84] = array(442,460);
            $dep[85] = array(146,285);
            $dep[86] = array(225,290);
            $dep[87] = array(258,341);
            $dep[88] = array(485,186);
            $dep[89] = array(360,208);
            $dep[90] = array(507,219);
            $dep[91] = array(305,164);
            $dep[92] = array(303,149);
            $dep[93] = array(314,143);
            $dep[94] = array(314,152);
            $dep[95] = array(301,132);
            return $dep;
        }

        public function getPluie() {
            //log::add('previsionpluie', 'debug', 'getInformation: go');

            if($this->getConfiguration('ville') != ''){

                //log::add('previsionpluie', 'debug', 'getInformation: ' .$this->getConfiguration('ville') );

                $prevPluieJson = file_get_contents('http://www.meteofrance.com/mf3-rpc-portlet/rest/pluie/' . $this->getConfiguration('ville'));
                $prevPluieData = json_decode($prevPluieJson, true);

                if(count($prevPluieData) == 0){
                    log::add('vigilancemeteo', 'debug', 'Impossible d\'obtenir les informations Météo France... On refait une tentative...');

                    sleep(3);
                    $prevPluieJson = file_get_contents('http://www.meteofrance.com/mf3-rpc-portlet/rest/pluie/' . $this->getConfiguration('ville'));
                    $prevPluieData = json_decode($prevPluieJson, true);

                    if(count($prevPluieData) == 0){
                        log::add('vigilancemeteo', 'debug', 'Impossible d\'obtenir les informations Météo France... ');
                        return false;
                    }
                }

                //log::add('previsionpluie', 'debug', 'getInformation: length ' . count($prevPluieData));

                if(count($prevPluieData) > 0){
                    $prevTexte = "";
                    foreach($prevPluieData['niveauPluieText'] as $prevTexteItem){
                        $prevTexte .= substr_replace($prevTexteItem," ",2,0) . "\n";
                        //log::add('previsionpluie', 'debug', 'prevTexteItem: ' . $prevTexteItem);
                    }
                    $prevTexteCmd = $this->getCmd(null,'prevTexte');
                    if(is_object($prevTexteCmd)){
                        //log::add('previsionpluie', 'debug', 'prevTexte: ' . $prevTexte);
                        $prevTexteCmd->event($prevTexte);
                    }
                    $lastUpdateCmd = $this->getCmd(null,'lastUpdate');
                    if(is_object($lastUpdateCmd)){
                        //log::add('previsionpluie', 'debug', 'lastUpdate: ' . $prevPluieData['lastUpdate']);
                        $lastUpdateCmd->event($prevPluieData['lastUpdate']);
                    }
                    $pluieDanslHeureCount = 0;

                    for($i=0; $i <= 11; $i++){
                        $prevCmd = $this->getCmd(null,'prev' . $i*5);
                        if(is_object($prevCmd)){
                            //log::add('previsionpluie', 'debug', 'prev' . $i*5 . ': ' . $prevPluieData['dataCadran'][$i]['niveauPluie']);
                            if($prevCmd->execCmd() != $prevPluieData['dataCadran'][$i]['niveauPluie']){
                                $prevCmd->event($prevPluieData['dataCadran'][$i]['niveauPluie']);
                            }
                            $pluieDanslHeureCount = $pluieDanslHeureCount + $prevPluieData['dataCadran'][$i]['niveauPluie'];
                        }
                    }

                    $pluieDanslHeure = $this->getCmd(null,'pluieDanslHeure');
                    if(is_object($pluieDanslHeure)){
                        //log::add('previsionpluie', 'debug', 'pluieDanslHeure: ' . $pluieDanslHeureCount);
                        $pluieDanslHeure->event($pluieDanslHeureCount);
                    }
                }
            }
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
                    $replace['#icone#'] = '<a target="_blank" href="http://vigilance.meteofrance.com/Bulletin_sans.html?a=dept' . $this->getConfiguration('departement') . '&b=2&c="><i class="fa fa-info-circle cursor"></i></a>';
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
            } else if ($this->getConfiguration('type') == 'pollen') {
                $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'general');
                $replace['#pollen_history#'] = '';
                $replace['#pollen#'] = $cmd->execCmd();
                $replace['#pollen_id#'] = $cmd->getId();

                $replace['#pollen_collect#'] = $cmd->getCollectDate();
                if ($cmd->getIsHistorized() == 1) {
                    $replace['#pollen_history#'] = 'history cursor';
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
                switch ($cmdcolor->execCmd()) {
                    case 'green':
                    $replace['#aqicolor#'] = "#00ff1e";
                    $replace['#aqilevel#'] = "Good";
                    break;
                    case 'yellow':
                    $replace['#aqicolor#'] = "#FFFF00";
                    $replace['#aqilevel#'] = "Moderate";
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
                $text[0] = '{{Données indisponibles}}';
                $text[1] = '{{Pas de pluie}}';
                $text[2] = '{{Pluie faible}}';
                $text[3] = '{{Pluie modérée}}';
                $text[4] = '{{Pluie forte}}';

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
            return $this->getConfiguration('value');
        }
    }

    ?>
