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

if (!isConnect('admin')) {
    throw new Exception('{{401 - AccÃ¨s non autorisÃ©}}');
}

?>

<div class="row" style="text-align: center; margin-top: 10px; margin-bottom: 10px;">
   	<input id='searchCityInput' type="text" class="form-control" placeholder="Entrez la ville (ou le code postal)" style="width: 60%;">
</div>
<div class="row" id='searchCityResults'></div>

<script>
	var pendingRequest = Array();

    $('#searchCityInput').keyup(function () {
    	$.each(pendingRequest, function( index, request ) {
		  	request.abort();
		});

		if($('#searchCityInput').val().length > 1){
	    	request = $.ajax({
	            type: 'POST',
	            url: 'plugins/vigilancemeteo/core/ajax/vigilancemeteo.ajax.php',
	            data: {
	                action: 'searchCity',
	                city : $('#searchCityInput').val()
	            },

	            dataType: 'json',
	            error: function (request, status, error) {
	                console.log("Erreur lors de la requÃªte");
	            },

	            success: function(data, textStatus, jqXHR) {
	            	div = '';

	            	SearchResult = JSON.parse(data.result);

	            	if(SearchResult.length > 0){
		            	div = '<div id="SearchResult" class="list-group">';

		            	$.each(SearchResult, function(index, ville){
		            		disabled = (ville.pluieAvalaible) ? '' : 'disabled';
		            		div += '  <a href="#" data-mfnumber="' + ville.id + '" class="list-group-item" ' + disabled + '>' + ville.nomAffiche + '</a>';
		            	});

						div += '</div>';
					}

	                $('#searchCityResults').html(div);

	                $( "#SearchResult" ).on( "click", "a", function() {
					  	$('#mfVilleNom').val($( this ).text());
					  	$('#mfVilleId').val($( this ).data('mfnumber'));

					  	$('#md_modal').dialog('close');
					});

	            }
	        });
 			pendingRequest.push(request);
    	}
    });
</script>
