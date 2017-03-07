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

    public function postUpdate() {
        $depmer = array("06","11","13","14","17","2A","2B","22","29","30","33","34","35","40","44","50","56","59","62","64","66","76","80","83","85");
        if ($this->getConfiguration('type') == 'vigilance') {
            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'vigilance');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Vigilance', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('vigilance');
                $cmdlogic->setConfiguration('data', 'vigilance');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'crue');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Crue', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('crue');
                $cmdlogic->setConfiguration('data', 'crue');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'risque');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Risque', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('risque');
                $cmdlogic->setConfiguration('data', 'risque');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            if (in_array($this->getConfiguration('departement'), $depmer)) {
                $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'mer');
                if (!is_object($cmdlogic)) {
                    $cmdlogic = new vigilancemeteoCmd();
                    $cmdlogic->setName(__('Mer', __FILE__));
                    $cmdlogic->setEqLogic_id($this->getId());
                    $cmdlogic->setLogicalId('mer');
                    $cmdlogic->setConfiguration('data', 'mer');
                }
                $cmdlogic->setType('info');
                $cmdlogic->setSubType('string');
                $cmdlogic->save();
            }
            $this->getVigilance();
        }
        if ($this->getConfiguration('type') == 'maree') {
            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'maree');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Indicateur Marée', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('maree');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pleine');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Pleine Mer', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('pleine');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'basse');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Basse Mer', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('basse');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $this->getMaree();
        }
        if ($this->getConfiguration('type') == 'crue') {
            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'niveau');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Niveau d\'eau', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('niveau');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'dateniveau');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Dernier relevé niveau', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('dateniveau');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'debit');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Débit d\'eau', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('debit');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'datedebit');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Dernier relevé débit', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('datedebit');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $this->getCrue();
        }
        if ($this->getConfiguration('type') == 'air') {
            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'aqi');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Index Qualité Air', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('aqi');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'dominentpol');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Polluant Majoritaire', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('dominentpol');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'color');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Couleur Indice', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('color');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'no2');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('NO2', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('no2');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'o3');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('O3', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('o3');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pm10');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('PM10', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('pm10');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'pm25');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('PM2.5', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('pm25');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'t');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Température', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('t');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'h');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Humidité', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('h');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'p');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Pression', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('p');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $this->getAir();
        }
        if ($this->getConfiguration('type') == 'surf') {
            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'minimum');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Hauteur minimum des Vagues', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('minimum');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'maximum');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Hauteur maximum des Vagues', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('maximum');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'primaryHeight');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Hauteur des Vagues en arrivée', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('primaryHeight');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'primaryPeriod');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Fréquence des Vagues en arrivée', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('primaryPeriod');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'compassDirection');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Direction des Vagues', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('compassDirection');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('string');
            $cmdlogic->save();

            $this->getSurf();
        }
        if ($this->getConfiguration('type') == 'seisme') {
            $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'risk');
            if (!is_object($cmdlogic)) {
                $cmdlogic = new vigilancemeteoCmd();
                $cmdlogic->setName(__('Probabilité de séisme magnitude 5 dans les 3 jours', __FILE__));
                $cmdlogic->setEqLogic_id($this->getId());
                $cmdlogic->setLogicalId('risk');
            }
            $cmdlogic->setType('info');
            $cmdlogic->setSubType('numeric');
            $cmdlogic->save();

            $this->getSeisme();
        }
        if ($this->getConfiguration('type') == 'pluie1h') {
            $vigilancemeteoCmd = $this->getCmd(null, 'prevTexte');
            if (!is_object($vigilancemeteoCmd)) {
                $vigilancemeteoCmd = new vigilancemeteoCmd();
            }
            $vigilancemeteoCmd->setName(__('Previsions Textuelles', __FILE__));
            $vigilancemeteoCmd->setEqLogic_id($this->id);
            $vigilancemeteoCmd->setLogicalId('prevTexte');
            $vigilancemeteoCmd->setType('info');
            $vigilancemeteoCmd->setSubType('other');
            $vigilancemeteoCmd->setEventOnly(1);
            $vigilancemeteoCmd->setIsVisible(1);
            $vigilancemeteoCmd->save();

            $vigilancemeteoCmd = $this->getCmd(null, 'lastUpdate');
            if (!is_object($vigilancemeteoCmd)) {
                $vigilancemeteoCmd = new vigilancemeteoCmd();
            }
            $vigilancemeteoCmd->setName(__('Dernière mise à jour', __FILE__));
            $vigilancemeteoCmd->setEqLogic_id($this->id);
            $vigilancemeteoCmd->setLogicalId('lastUpdate');
            $vigilancemeteoCmd->setType('info');
            $vigilancemeteoCmd->setSubType('other');
            $vigilancemeteoCmd->setEventOnly(1);
            $vigilancemeteoCmd->setIsVisible(1);
            $vigilancemeteoCmd->save();

            $vigilancemeteoCmd = $this->getCmd(null, 'pluieDanslHeure');
            if (!is_object($vigilancemeteoCmd)) {
                $vigilancemeteoCmd = new vigilancemeteoCmd();
            }
            $vigilancemeteoCmd->setName(__('Pluie prévue dans l heure', __FILE__));
            $vigilancemeteoCmd->setEqLogic_id($this->id);
            $vigilancemeteoCmd->setLogicalId('pluieDanslHeure');
            $vigilancemeteoCmd->setType('info');
            $vigilancemeteoCmd->setSubType('other');
            $vigilancemeteoCmd->setEventOnly(1);
            $vigilancemeteoCmd->setIsVisible(1);
            $vigilancemeteoCmd->save();

            for($i=0; $i <= 11; $i++){

                $vigilancemeteoCmd = $this->getCmd(null, 'prev' . $i*5);
                if (!is_object($vigilancemeteoCmd)) {
                    $vigilancemeteoCmd = new vigilancemeteoCmd();
                }
                $vigilancemeteoCmd->setName(__('Prévision à ' . ($i*5) . '-' . ($i*5+5), __FILE__));
                $vigilancemeteoCmd->setEqLogic_id($this->id);
                $vigilancemeteoCmd->setLogicalId('prev' . $i*5);
                $vigilancemeteoCmd->setType('info');
                $vigilancemeteoCmd->setSubType('other');
                $vigilancemeteoCmd->setEventOnly(1);
                $vigilancemeteoCmd->setIsVisible(0);
                $vigilancemeteoCmd->save();
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
            log::add('vigilancemeteo', 'debug', 'Air ' . $json['data']['aqi'] . ' ' . $json['data']['name']);
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
        } else if ($this->getConfiguration('type') == 'crue') {
            $cmd = vigilancemeteoCmd::byEqLogicIdAndLogicalId($this->getId(),'niveau');
            $replace['#crue_history#'] = '';
            $replace['#crue#'] = $cmd->getConfiguration('value');
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
                break;
                case 'yellow':
                $replace['#aqicolor#'] = "#FFFF00";
                break;
                case 'orange':
                $replace['#aqicolor#'] = "#FFA500";
                break;
                case 'red':
                $replace['#aqicolor#'] = "#E50000";
                break;
            }
            $replace['#aqi_history#'] = '';
            $replace['#aqi#'] = $cmd->execCmd();
            $replace['#aqi_id#'] = $cmd->getId();

            $replace['#aqi_collect#'] = $cmd->getCollectDate();
            if ($cmd->getIsHistorized() == 1) {
                $replace['#aqi_history#'] = 'history cursor';
            }

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
