$ = jQuery
$(document).ready(function($) {
	"use strict";

	$('.row-actions .delete a').click( function() {
		var r = confirm( messages.delete_row_confirm_msg );
		if ( r == false ) {
			return false;
		}
	});

});
