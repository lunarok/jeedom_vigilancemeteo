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

try {
    require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
    include_file('core', 'authentification', 'php');

    if (!isConnect('admin')) {
        throw new Exception(__('401 - Accès non autorisé', __FILE__));
    }

    if (init('action') == 'searchCity') {
    	$city = init('city');
    	log::add('vigilancemeteo', 'debug', 'Ajax: searchCity - city = ' . $city);
    	$res = file_get_contents("http://www.meteofrance.com/mf3-rpc-portlet/rest/lieu/facet/pluie/search/" . $city);
		if($res){
			ajax::success($res);
		}else{
			throw new Exception("Impossible d'obtenir le résultat de la recherche");
		}
    }

    if (init('action') == 'getVigilanceMeteo') {
		$return['location'] = array();
		$return['travel'] = array();
		$return['geofence'] = array();
        $return['station'] = array();
		foreach (eqLogic::byType('geotrav') as $eqLogic) {
			if ($eqLogic->getIsEnable() == 0 || $eqLogic->getIsVisible() == 0) {
				continue;
			}
			if ($eqLogic->getConfiguration('type') == 'location') {
				$return['location'][] = $eqLogic->toHtml(init('version'));
			}
			if ($eqLogic->getConfiguration('type') == 'travel') {
				$return['travel'][] = $eqLogic->toHtml(init('version'));
			}
			if ($eqLogic->getConfiguration('type') == 'geofence') {
				$return['geofence'][] = $eqLogic->toHtml(init('version'));
			}
            if ($eqLogic->getConfiguration('type') == 'station') {
				$return['station'][] = $eqLogic->toHtml(init('version'));
			}
		}
		ajax::success($return);
	}

    throw new Exception(__('Aucune methode correspondante à : ', __FILE__) . init('action'));
    /*     * *********Catch exeption*************** */
} catch (Exception $e) {
    ajax::error(displayExeption($e), $e->getCode());
}
?>
