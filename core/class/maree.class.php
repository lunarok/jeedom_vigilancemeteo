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

class maree extends eqLogic {

  public static $_widgetPossibility = array('custom' => true);

  public static function cronHourly() {
    foreach (eqLogic::byType('maree', true) as $maree) {
        $maree->getInformations();
    }
    log::add('maree', 'debug', 'pull cron');
  }

  public function postUpdate() {
      $cmdlogic = mareeCmd::byEqLogicIdAndLogicalId($this->getId(),'maree');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new mareeCmd();
        $cmdlogic->setName(__('Indicateur Marée', __FILE__));
        $cmdlogic->setEqLogic_id($this->getId());
        $cmdlogic->setLogicalId('maree');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('numeric');
      $cmdlogic->save();

      $cmdlogic = mareeCmd::byEqLogicIdAndLogicalId($this->getId(),'pleine');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new mareeCmd();
        $cmdlogic->setName(__('Pleine Mer', __FILE__));
        $cmdlogic->setEqLogic_id($this->getId());
        $cmdlogic->setLogicalId('pleine');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('numeric');
      $cmdlogic->save();

      $cmdlogic = mareeCmd::byEqLogicIdAndLogicalId($this->getId(),'basse');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new mareeCmd();
        $cmdlogic->setName(__('Basse Mer', __FILE__));
        $cmdlogic->setEqLogic_id($this->getId());
        $cmdlogic->setLogicalId('basse');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('numeric');
      $cmdlogic->save();

      $this->getInformations();
  }

  public function getInformations() {
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

    log::add('maree', 'debug', 'Marée ' . $maree . ', Pleine ' . $pleine . ', Basse ' . $basse);

    $cmdlogic = mareeCmd::byEqLogicIdAndLogicalId($this->getId(),'maree');
    $cmdlogic->setConfiguration('value', $maree);
    $cmdlogic->save();
    $cmdlogic->event($maree);

    $cmdlogic = mareeCmd::byEqLogicIdAndLogicalId($this->getId(),'pleine');
    $cmdlogic->setConfiguration('value', $pleine);
    $cmdlogic->save();
    $cmdlogic->event($pleine);

    $cmdlogic = mareeCmd::byEqLogicIdAndLogicalId($this->getId(),'basse');
    $cmdlogic->setConfiguration('value', $basse);
    $cmdlogic->save();
    $cmdlogic->event($basse);

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

    $replace['#portid#'] = $this->getConfiguration('port');

    foreach ($this->getCmd('info') as $cmd) {
      $replace['#' . $cmd->getLogicalId() . '_history#'] = '';
      $replace['#' . $cmd->getLogicalId() . '_id#'] = $cmd->getId();

      if ($cmd->getLogicalId() == 'maree') {
        $replace['#' . $cmd->getLogicalId() . '#'] = $cmd->execCmd();
      } else {
        $replace['#' . $cmd->getLogicalId() . '#'] = substr_replace($cmd->execCmd(),':',-2,0);
      }
      $replace['#' . $cmd->getLogicalId() . '_collect#'] = $cmd->getCollectDate();
      if ($cmd->getIsHistorized() == 1) {
        $replace['#' . $cmd->getLogicalId() . '_history#'] = 'history cursor';
      }

    }

    return $this->postToHtml($_version, template_replace($replace, getTemplate('core', $version, 'maree', 'maree')));
  }

}

class mareeCmd extends cmd {
  public function execute($_options = null) {
              return $this->getConfiguration('value');
    }
}

?>
