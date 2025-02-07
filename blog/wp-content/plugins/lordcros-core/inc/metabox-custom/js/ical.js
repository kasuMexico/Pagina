jQuery( function ( $ ) {
	'use strict';

	function iCal_import() {
		var $this = $( this );
		var ical_url = $this.prev().val();
		var spinner = $this.next('.spinner');

		if ( ! ical_url ) {
			alert( js_ical_vars.alert_msg1 );
			return;
		}

		if ( ! js_ical_vars.post_id ) {
			alert( js_ical_vars.alert_msg2 );
			return;	
		}

		spinner.addClass('is-active');

		$.ajax({
			url: js_ical_vars.ajax_url,
			type: "POST",
			data: 'ical_id='+$this.prev().attr('id')+'&action=import_ical&post_id='+js_ical_vars.post_id+'&ical_url='+ical_url+'&security='+js_ical_vars.ajax_nonce,
			success: function(response) {
				if (response.success == 1) {
					if ( response.data.price_variation != undefined ) {				
						$.each( response.data.price_variation, function( index, value ) {
							$('#lordcros_room_price_variation option[value="'+value.id+'"]').remove();
							$('#lordcros_room_price_variation').append('<option value="'+value.id+'" selected="selected">'+value.title+'</option>');
						} );
					}

					if ( response.data.date_block != undefined ) {
						$.each( response.data.date_block, function( index, value ) {
							$('#lordcros_room_date_block option[value="'+value.id+'"]').remove();
							$('#lordcros_room_date_block').append('<option value="'+value.id+'" selected="selected">'+value.title+'</option>');
							
						} );
					}
				} else {
					//alert(response.message);
				}
			},
			complete: function() {
				spinner.removeClass('is-active');
			}
		});
	}

	$( document ).on( 'click', '.ical_importer', iCal_import );

} );
