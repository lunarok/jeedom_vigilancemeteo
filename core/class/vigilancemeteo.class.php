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

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class vigilancemeteo extends eqLogic {

  public static $_widgetPossibility = array('custom' => true);

  public static function cron15() {
    foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
        $vigilancemeteo->getInformations();
    }
    log::add('vigilancemeteo', 'debug', 'pull cron');
  }

  public static function cronDaily() {
    foreach (eqLogic::byType('vigilancemeteo', true) as $vigilancemeteo) {
      foreach ($vigilancemeteo->getCmd() as $cmd) {
        $cmd->setConfiguration('alert', '0');
        $cmd->save();
      }
    }
  }

  public function postUpdate() {
    $depmer = array("06","11","13","14","17","2A","2B","22","29","30","33","34","35","40","44","50","56","59","62","64","66","76","80","83","85");
    foreach (eqLogic::byType('vigilancemeteo') as $vigilancemeteo) {
      $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getId(),'vigilance');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new vigilancemeteoCmd();
        $cmdlogic->setName(__('Vigilance', __FILE__));
        $cmdlogic->setEqLogic_id($vigilancemeteo->id);
        $cmdlogic->setLogicalId('vigilance');
        $cmdlogic->setConfiguration('data', 'vigilance');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('string');
      $cmdlogic->save();

      $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getId(),'crue');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new vigilancemeteoCmd();
        $cmdlogic->setName(__('Crue', __FILE__));
        $cmdlogic->setEqLogic_id($vigilancemeteo->id);
        $cmdlogic->setLogicalId('crue');
        $cmdlogic->setConfiguration('data', 'crue');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('string');
      $cmdlogic->save();

      $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getId(),'risque');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new vigilancemeteoCmd();
        $cmdlogic->setName(__('Risque', __FILE__));
        $cmdlogic->setEqLogic_id($vigilancemeteo->id);
        $cmdlogic->setLogicalId('risque');
        $cmdlogic->setConfiguration('data', 'risque');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('string');
      $cmdlogic->save();

      if (in_array($vigilancemeteo->getConfiguration('departement'), $depmer)) {
        $cmdlogic = vigilancemeteoCmd::byEqLogicIdAndLogicalId($vigilancemeteo->getId(),'mer');
        if (!is_object($cmdlogic)) {
          $cmdlogic = new vigilancemeteoCmd();
          $cmdlogic->setName(__('Mer', __FILE__));
          $cmdlogic->setEqLogic_id($vigilancemeteo->id);
          $cmdlogic->setLogicalId('mer');
          $cmdlogic->setConfiguration('data', 'mer');
        }
        $cmdlogic->setType('info');
        $cmdlogic->setSubType('string');
        $cmdlogic->save();
      }
      $vigilancemeteo->getInformations();
    }
  }

  public function getInformations() {
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
    $doc2 = new DOMDocument();
    $doc2->load('http://vigilance.meteofrance.com/data/NXFR33_LFPW_.xml');

    foreach($doc->getElementsByTagName('datavigilance') as $data) {
      if ($data->getAttribute('dep') == $departement) {
        // On récupère le niveau général
        switch ($data->getAttribute('couleur')) {
          case 0:
          $lvigilance = "vert";
          break;
          case 1:
          $lvigilance = "vert";
          break;
          case 2:
          $lvigilance = "jaune";
          break;
          case 3:
          $lvigilance = "orange";
          break;
          case 4:
          $lvigilance = "rouge";
          break;
        }
        // On cherche les alertes "crue"
        foreach($data->getElementsByTagName('crue') as $crue) {
          switch ($crue->getAttribute('valeur')) {
            case 0:
            $lcrue = "vert";
            break;
            case 1:
            $lcrue = "vert";
            break;
            case 2:
            $lcrue = "jaune";
            break;
            case 3:
            $lcrue = "orange";
            break;
            case 4:
            $lcrue = "rouge";
            break;
          }
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
        foreach($data->getElementsByTagName('risque') as $risque) {
          switch ($risque->getAttribute('val')) {
            case 1:
            if ($lrisque == "RAS") {
              $lrisque = "vent";
            } else {
              $lrisque = $lrisque . ", vent";
            }
            break;
            case 2:
            if ($lrisque == "RAS") {
              $lrisque = "pluie-inondation";
            } else {
              $lrisque = $lrisque . ", pluie-inondation";
            }
            break;
            case 3:
            if ($lrisque == "RAS") {
              $lrisque = "orages";
            } else {
              $lrisque = $lrisque . ", orages";
            }
            break;
            case 4:
            if ($lrisque == "RAS") {
              $lrisque = "inondations";
            } else {
              $lrisque = $lrisque . ", inondations";
            }
            break;
            case 5:
            if ($lrisque == "RAS") {
              $lrisque = "neige-verglas";
            } else {
              $lrisque = $lrisque . ", neige-verglas";
            }
            break;
            case 6:
            if ($lrisque == "RAS") {
              $lrisque = "canicule";
            } else {
              $lrisque = $lrisque . ", canicule";
            }
            break;
            case 7:
            if ($lrisque == "RAS") {
              $lrisque = "grand-froid";
            } else {
              $lrisque = $lrisque . ", grand-froid";
            }
            break;
            case 8:
            if ($lrisque == "RAS") {
              $lrisque = "avalanches";
            } else {
              $lrisque = $lrisque . ", avalanches";
            }
            break;
            case 9:
            if ($lrisque == "RAS") {
              $lrisque = "vagues-submersion";
            } else {
              $lrisque = $lrisque . ", vagues-submersion";
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
      //log::add('vigilancemeteo', 'debug', $cmd->getConfiguration('data'));
      if($cmd->getConfiguration('data')=="vigilance"){
        if ($lvigilance != "vert") {
          if ($cmd->getConfiguration('alert') == '0' && $alert != '') {
            $cmd->setConfiguration('alert', '1');
            $cmdalerte = cmd::byId($alert);
            $options['title'] = "Alerte Météo";
            $options['message'] = "Niveau " . $lvigilance . " pour la vigilance";
            $cmdalerte->execCmd($options);
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
            $cmdalerte = cmd::byId($alert);
            $options['title'] = "Alerte Météo";
            $options['message'] = "Niveau " . $lcrue . " pour le risque de crue";
            $cmdalerte->execCmd($options);
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
            $cmdalerte = cmd::byId($alert);
            $options['title'] = "Alerte Météo";
            $options['message'] = "Risque " . $lrisque;
            $cmdalerte->execCmd($options);
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
            $cmdalerte = cmd::byId($alert);
            $options['title'] = "Alerte Météo";
            $options['message'] = "Niveau " . $lmer . " pour le risque bord de mer";
            $cmdalerte->execCmd($options);
          }
        } else {
          $cmd->setConfiguration('alert', '0');
        }
        $cmd->setConfiguration('value', $lmer);
        $cmd->save();
        $cmd->event($lmer);
      }
    }
    return ;
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

    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'vigilancemeteo', 'vigilancemeteo')));
  }

}

class vigilancemeteoCmd extends cmd {
  public function execute($_options = null) {
              return $this->getConfiguration('value');
    }
}

?>
