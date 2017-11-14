
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

$('#btnSearchCity').on('click', function () {
    $('#md_modal').dialog({title: "{{Trouver la ville}}"});
    $('#md_modal').load('index.php?v=d&plugin=vigilancemeteo&modal=searchCity').dialog('open');
});

$('#typeEq').change(function(){
  var text = $("#typeEq").val();
  if ((text == 'vigilance') || (text == 'seisme') || (text == 'pollen') || (text == 'plage')) {
    $('#portEq').hide();
    $('#villeEq').hide();
    $('#stationEq').hide();
    $('#geolocEq').show();
    $('#surfEq').hide();
    $('#mswEq').hide();
    $('#breezeEq').hide(); 
  }
    if (text == 'air') {
    $('#portEq').hide();
    $('#villeEq').hide();
    $('#stationEq').hide();
    $('#geolocEq').show();
    $('#surfEq').hide();
    $('#mswEq').hide();
    $('#breezeEq').show(); 
  }
  if (text == 'maree') {
    $('#portEq').show();
    $('#villeEq').hide();
    $('#stationEq').hide();
    $('#geolocEq').hide();
    $('#surfEq').hide();
    $('#mswEq').hide();
      $('#breezeEq').hide();
  }
  if (text == 'surf') {
      $('#portEq').hide();
      $('#villeEq').hide();
      $('#stationEq').hide();
      $('#geolocEq').hide();
      $('#surfEq').show();
      $('#mswEq').show();
      $('#breezeEq').hide();
  }
  if (text == 'crue') {
      $('#portEq').hide();
      $('#villeEq').hide();
      $('#stationEq').show();
      $('#geolocEq').hide();
      $('#surfEq').hide();
      $('#mswEq').hide();
      $('#breezeEq').hide();
  }
  if (text == 'pluie1h') {
      $('#portEq').hide();
      $('#villeEq').show();
      $('#stationEq').hide();
      $('#geolocEq').hide();
      $('#surfEq').hide();
      $('#mswEq').hide();
      $('#breezeEq').hide();
  }
});

function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
      var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
      tr += '<td>';
      tr += '<span class="cmdAttr" data-l1key="id"></span>';
      tr += '</td><td>';
      tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom de la commande}}"></td>';
      tr += '</td><td>';
      if (_cmd.subType == 'numeric' || _cmd.subType == 'binary') {
        tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span>';
      }
      tr += '</td><td>';
      if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i>{{Tester}}</a>';
      }
      tr += '</td>';
      tr += '</tr>';
        $('#table_cmd tbody').append(tr);
        $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');

}
