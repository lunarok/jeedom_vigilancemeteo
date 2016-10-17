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

class crues extends eqLogic {

  public static function cronHourly() {
    foreach (eqLogic::byType('crues', true) as $crues) {
        $crues->getInformations();
    }
    log::add('crues', 'debug', 'pull cron');
  }

  public function postUpdate() {
      $cmdlogic = cruesCmd::byEqLogicIdAndLogicalId($this->getId(),'niveau');
      if (!is_object($cmdlogic)) {
        $cmdlogic = new cruesCmd();
        $cmdlogic->setName(__('Niveau d\'eau', __FILE__));
        $cmdlogic->setEqLogic_id($this->getId());
        $cmdlogic->setLogicalId('niveau');
      }
      $cmdlogic->setType('info');
      $cmdlogic->setSubType('numeric');
      $cmdlogic->save();

      $this->getInformations();
  }

  public function getInformations() {
    $station = $this->getConfiguration('station');
    if ($station == '') {
      log::add('crues', 'error', 'Station non saisie');
      return;
    }
    $url = "http://www.vigicrues.gouv.fr/niveau3.php?CdStationHydro=".$station."&typegraphe=h&AffProfondeur=24&nbrstations=2&ong=2&Submit=Refaire+le+tableau+-+Valider+la+s%C3%A9lection";
    //r�cup�ration des donn�es
    $html = file_get_contents($url);

    // from Hervé http://www.abavala.com
    // nom de la station de relev� et type d'information r�cup�r�e
        $info = explode("<p class='titre_cadre'>", $html,2);
        $station = explode(" - ", $info[1],2);
        $info = explode("</p>", $station[1],2);

    //r�cup�ration du tableau de donn�es
        $tableau = explode("<table  class='liste'>", $html,2);
        $tableau = explode("</table>", $tableau[1],2);

    //lecture de la prem�re date du tableau
        $data = explode("<td>",$tableau[0],2);
        $datadate = explode("</td>",$data[1],2);

    //lecture du relev� associ�
        $data = explode("<td align='right'>",$tableau[0],2);
        $datareleve = explode("</td>",$data[1],2);

    log::add('crues', 'debug', 'Valeur ' . $datareleve[0]);

    $cmdlogic = cruesCmd::byEqLogicIdAndLogicalId($this->getId(),'niveau');
    $cmdlogic->setConfiguration('value', $datareleve[0]);
    $cmdlogic->save();
    $cmdlogic->event($datareleve[0]);

    return ;
  }

}

class cruesCmd extends cmd {
  /*     * *************************Attributs****************************** */



  /*     * ***********************Methode static*************************** */

  /*     * *********************Methode d'instance************************* */
  public function execute($_options = null) {
              return $this->getConfiguration('value');
    }

}

?>
