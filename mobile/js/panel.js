Pluie1hcrueair/* This file is part of Jeedom.
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

function initVigilancemeteoPanel() {
    displayVigilancemeteo();
    $(window).on("orientationchange", function (event) {
        setTileSize('.eqLogic');
        $('#div_displayEquipementVigilancemeteo').packery({gutter : 4});
    });
}

function displayVigilancemeteo() {
    $.showLoading();
    $.ajax({
        type: 'POST',
        url: 'plugins/vigilancemeteo/core/ajax/vigilancemeteo.ajax.php',
        data: {
            action: 'getVigilancemeteo',
            version: 'mview'
        },
        dataType: 'json',
        error: function (request, status, error) {
            handleAjaxError(request, status, error);
        },
        success: function (data) {
            if (data.state != 'ok') {
                $('#div_alert').showAlert({message: data.result, level: 'danger'});
                return;
            }
            if(data.result.vigilance.length == 0){
                $('#div_vigilance').hide();
            }else{
                $('#div_vigilance').show();
                $('#div_displayEquipementVigilancemeteoVigilance').empty();
                for (var i in data.result.vigilance) {
                    $('#div_displayEquipementVigilancemeteoVigilance').append(data.result.vigilance[i]).trigger('create');
                }
            }
            if(data.result.pluie1h.length == 0){
                $('#div_pluie1h').hide();
            }else{
                $('#div_pluie1h').show();
                $('#div_displayEquipementVigilancemeteoPluie1h').empty();
                for (var i in data.result.pluie1h) {
                    $('#div_displayEquipementVigilancemeteoPluie1h').append(data.result.pluie1h[i]).trigger('create');
                }
            }
            if(data.result.pollen.length == 0){
                $('#div_pollen').hide();
            }else{
                $('#div_pollen').show();
                $('#div_displayEquipementVigilancemeteoPollen').empty();
                for (var i in data.result.pollen) {
                    $('#div_displayEquipementVigilancemeteoPollen').append(data.result.pollen[i]).trigger('create');
                }
            }
            if(data.result.air.length == 0){
                $('#div_air').hide();
            }else{
                $('#div_air').show();
                $('#div_displayEquipementVigilancemeteoAir').empty();
                for (var i in data.result.air) {
                    $('#div_displayEquipementVigilancemeteoAir').append(data.result.air[i]).trigger('create');
                }
            }
            if(data.result.crue.length == 0){
                $('#div_crue').hide();
            }else{
                $('#div_crue').show();
                $('#div_displayEquipementVigilancemeteoCrue').empty();
                for (var i in data.result.crue) {
                    $('#div_displayEquipementVigilancemeteoCrue').append(data.result.crue[i]).trigger('create');
                }
            }
            if(data.result.maree.length == 0){
                $('#div_maree').hide();
            }else{
                $('#div_maree').show();
                $('#div_displayEquipementVigilancemeteoMaree').empty();
                for (var i in data.result.maree) {
                    $('#div_displayEquipementVigilancemeteoMaree').append(data.result.maree[i]).trigger('create');
                }
            }
            if(data.result.plage.length == 0){
                $('#div_plage').hide();
            }else{
                $('#div_plage').show();
                $('#div_displayEquipementVigilancemeteoPlage').empty();
                for (var i in data.result.plage) {
                    $('#div_displayEquipementVigilancemeteoPlage').append(data.result.plage[i]).trigger('create');
                }
            }
            if(data.result.seisme.length == 0){
                $('#div_seisme').hide();
            }else{
                $('#div_seisme').show();
                $('#div_displayEquipementVigilancemeteoSeisme').empty();
                for (var i in data.result.seisme) {
                    $('#div_displayEquipementVigilancemeteoSeisme').append(data.result.seisme[i]).trigger('create');
                }
            }
            if(data.result.surf.length == 0){
                $('#div_surf').hide();
            }else{
                $('#div_surf').show();
                $('#div_displayEquipementVigilancemeteoSurf').empty();
                for (var i in data.result.surf) {
                    $('#div_displayEquipementVigilancemeteoSurf').append(data.result.surf[i]).trigger('create');
                }
            }
            setTileSize('.eqLogic');
            $('#div_displayEquipementVigilancemeteo').packery({gutter : 4});
            $.hideLoading();
        }
    });
}
