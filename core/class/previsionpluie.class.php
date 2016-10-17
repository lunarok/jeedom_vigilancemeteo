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

class previsionpluie extends eqLogic {

    public static function pull() {
		foreach (eqLogic::byType('previsionpluie') as $previsionpluie) {
			$previsionpluie->getInformations();
			$mc = cache::byKey('previsionpluieWidgetmobile' . $previsionpluie->getId());
			$mc->remove();
			$mc = cache::byKey('previsionpluieWidgetdashboard' . $previsionpluie->getId());
			$mc->remove();
			$previsionpluie->toHtml('(1mobile');
			$previsionpluie->toHtml('dashboard');
			$previsionpluie->refreshWidget();
		}
	}

	public function postUpdate() {
		//log::add('previsionpluie', 'debug', 'postUpdate');
		
		$previsionpluieCmd = $this->getCmd(null, 'prevTexte');
		if (!is_object($previsionpluieCmd)) {
			$previsionpluieCmd = new previsionpluieCmd();
		}
		$previsionpluieCmd->setName(__('Previsions Textuelles', __FILE__));
		$previsionpluieCmd->setEqLogic_id($this->id);
		$previsionpluieCmd->setLogicalId('prevTexte');
		$previsionpluieCmd->setType('info');
		$previsionpluieCmd->setSubType('other');
		$previsionpluieCmd->setEventOnly(1);
		$previsionpluieCmd->setIsVisible(1);
		$previsionpluieCmd->save();

		$previsionpluieCmd = $this->getCmd(null, 'lastUpdate');
		if (!is_object($previsionpluieCmd)) {
			$previsionpluieCmd = new previsionpluieCmd();
		}
		$previsionpluieCmd->setName(__('Dernière mise à jour', __FILE__));
		$previsionpluieCmd->setEqLogic_id($this->id);
		$previsionpluieCmd->setLogicalId('lastUpdate');
		$previsionpluieCmd->setType('info');
		$previsionpluieCmd->setSubType('other');
		$previsionpluieCmd->setEventOnly(1);
		$previsionpluieCmd->setIsVisible(1);
		$previsionpluieCmd->save();

		$previsionpluieCmd = $this->getCmd(null, 'pluieDanslHeure');
		if (!is_object($previsionpluieCmd)) {
			$previsionpluieCmd = new previsionpluieCmd();
		}
		$previsionpluieCmd->setName(__('Pluie prévue dans l heure', __FILE__));
		$previsionpluieCmd->setEqLogic_id($this->id);
		$previsionpluieCmd->setLogicalId('pluieDanslHeure');
		$previsionpluieCmd->setType('info');
		$previsionpluieCmd->setSubType('other');
		$previsionpluieCmd->setEventOnly(1);
		$previsionpluieCmd->setIsVisible(1);
		$previsionpluieCmd->save();

		for($i=0; $i <= 11; $i++){

			$previsionpluieCmd = $this->getCmd(null, 'prev' . $i*5);
			if (!is_object($previsionpluieCmd)) {
				$previsionpluieCmd = new previsionpluieCmd();
			}
			$previsionpluieCmd->setName(__('Prévision à ' . ($i*5) . '-' . ($i*5+5), __FILE__));
			$previsionpluieCmd->setEqLogic_id($this->id);
			$previsionpluieCmd->setLogicalId('prev' . $i*5);
			$previsionpluieCmd->setType('info');
			$previsionpluieCmd->setSubType('other');
			$previsionpluieCmd->setEventOnly(1);
			$previsionpluieCmd->setIsVisible(0);
			$previsionpluieCmd->save();
		}
	}
	
	public function postSave() {
		//log::add('previsionpluie', 'debug', 'postSave');
		
		foreach (eqLogic::byType('previsionpluie') as $previsionpluie) {
			$previsionpluie->getInformations();
		}
	}
	
 	public function toHtml($_version = 'dashboard') 
	{
		$_version = jeedom::versionAlias($_version);
        $mc = cache::byKey('previsionpluieWidget' . $_version . $this->getId());
        if ($mc->getValue() != '') {
            return $mc->getValue();
        }
		$replace = array(
			'#id#' => $this->getId(),
			'#name#' => ($this->getIsEnable()) ? $this->getName() : '<del>' . $this->getName() . '</del>',
			'#background_color#' => $this->getBackgroundColor($_version),
			'#eqLink#' => $this->getLinkToConfiguration(),
			'#ville#' => $this->getConfiguration('ville')
		);
		
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
		
		return template_replace($replace, getTemplate('core', $_version, 'previsionpluie','previsionpluie'));
		cache::set('previsionpluieWidget' . $_version . $this->getId(), $html, 0);
        return $html;
	}

    public function getInformations() {
    	//log::add('previsionpluie', 'debug', 'getInformation: go');

    	if($this->getConfiguration('ville') != ''){

    		//log::add('previsionpluie', 'debug', 'getInformation: ' .$this->getConfiguration('ville') );

	    	$prevPluieJson = file_get_contents('http://www.meteofrance.com/mf3-rpc-portlet/rest/pluie/' . $this->getConfiguration('ville')); 
			$prevPluieData = json_decode($prevPluieJson, true); 

			if(count($prevPluieData) == 0){
				log::add('previsionpluie', 'debug', 'Impossible d\'obtenir les informations Météo France... On refait une tentative...');

				sleep(10);
				$prevPluieJson = file_get_contents('http://www.meteofrance.com/mf3-rpc-portlet/rest/pluie/' . $this->getConfiguration('ville')); 
				$prevPluieData = json_decode($prevPluieJson, true); 

				if(count($prevPluieData) == 0){
					log::add('previsionpluie', 'debug', 'Impossible d\'obtenir les informations Météo France... ');
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
}

class previsionpluieCmd extends cmd {

 /*     * *********************Methode d'instance************************* */
	public function execute($_options = null) {

	}
}

?>