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
$departement = $vigilancemeteo->getConfiguration('departement');
$link='http://vigilance.meteofrance.com/Bulletin_sans.html?a=dept'.$departement.'&b=2&c=';
?>

<iframe src="<?php echo $link; ?>" height="100%" width="100%">You need a Frames Capable browser to view this content.</iframe>
