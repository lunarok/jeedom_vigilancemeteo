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

?>

<style>
	#pollen_carousel#id# {
	  position: relative;
	  width: 186px;
	  margin: 0 auto;
	  display: table-cell;
	  top: -12px;
	}
	#pollen_slides#id# {
	  overflow: hidden;
	  position: relative;
	  width: 186px;
	  height: 76px;
	}
	#pollen_slides#id# ul {
	  list-style: none;
	  width: 186px;
	  height: 76px;
	  margin: 0;
	  padding: 0;
	  position: relative;
	}
	#pollen_slides#id# li {
	  width: 186px;
	  height: 76px;
	  float: left;
	  text-align: center;
	  position: relative;
	}
	.pollen_btn-bar#id# {
	  position: relative;
	  position: relative;
	  display: table-cell;
	  height: 76px;
	  top: -11px;
	}
	.pollen_button_L#id# {
	  padding: 0 8px 0 6px;
	  display: table-cell;
	}
	.pollen_button_R#id# {
	  padding: 0 6px 0 8px;
	  display: table-cell;
	}
	#pollen_buttons#id# a {
	  vertical-align: middle;
	  float: left;
	  outline: 0;
	  text-decoration: none;
	  width: 15px;
	  height: 76px;
	  padding-top: 28px;
	  opacity: 0.8;
	}
	#pollen_prev#id# {
	  border-right: solid;
	  border-right-width: 1px;
	  border-right-color: gray;
	}
	#pollen_next#id# {
	  border-left: solid;
	  border-left-width: 1px;
	  border-left-color: gray;
	}
	a#pollen_prev#id#:hover,
	a#pollen_next#id#:hover {
	  border-right-color: white;
	  border-left-color: white;
	}
	.pollen_quote-phrase#id# {
	  text-align: left;
	  display: table-cell;
	  height: 76px;
	  font-size: 12px;
	  color: #FFF;
	  text-shadow: .5px 0px #b14943;
	}
	.pollen_quoteContainer#id# {
	  display: table;
	  width: 100%;
	}
	.pollen_pollen#id# {
	  padding: 1px 0 1px;
	}
	.pollen_label#id# {
	  display: table-cell;
	  font-size: 11px;
	  width: 74px;
	  height: 16px;
	}
	.pollen_value#id# {
	  display: table-cell;
	  width: 15px;
	  text-align: center;
	  font-style: normal;
	}
	.pollen_graph#id# {
	  display: table-cell;
	  height: 16px;
	}
	.pollen_general#id# {
	  display: table-cell;
	  vertical-align: middle;
	  align: center;
	  cursor:default;
	  font-size: 1.5em;
	  font-weight: bold;
	  border-style: solid;
	  border-width: 1px;
	  border-color: #ffffff;
	  border-radius:19px;
	  width:38px;
	  height:38px;
	}
</style>

<?php

$id = init('id');
$eqLogic = vigilancemeteo::byId($id);
$onetemplate = getTemplate('core', $version, '1pollen', 'vigilancemeteo');
$replace = $this->preToHtml($_version);

foreach ($eqLogic->getCmd('info') as $cmd) {
  switch ($cmd->execCmd()) {
    case '-1': $color = 'black'; break;
    case '0':  $color = 'black'; break;
    case '1':  $color = 'lime';  break;
    case '2':  $color = 'green'; break;
    case '3':  $color = 'yellow'; break;
    case '4':  $color = 'orange'; break;
    case '5':  $color = 'red';    break;
  }
  if ($cmd->getLogicalId() != 'general') {
    $sort[$cmd->getLogicalId()] = $cmd->execCmd();
    $unitreplace['#id#'] = $cmd->getId();
    $unitreplace['#value#'] = $cmd->execCmd();
    $unitreplace['#name#'] = $cmd->getName();
    $unitreplace['#width#'] = $cmd->execCmd() * 20;
    $unitreplace['#color#'] = $color;
    $unitreplace['#background-color#'] = $replace['#background-color#'];
    $slide[$cmd->getLogicalId()] = template_replace($unitreplace, $onetemplate);
  }
}
arsort($sort);
foreach ($sort as $key => $value) {
  echo $slide[$key];
}

?>
