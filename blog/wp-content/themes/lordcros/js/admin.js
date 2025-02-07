/**
 * Admin JS
 */

jQuery(document).ready(function($) {
	'use strict';

	/* Redux framework checkbox */
	$('.color-transparency-check').append($('<span class="switch-checkbox rounded"></span>'));

	/* LordCros Rooms widget fields */
	$('body').on('change', '#widgets-right .type-field.room-widget-field', function(){
		if ( $(this).val() == 'selected' ) {
			$(this).closest('form').find('p.count-field-wrapper').hide();
			$(this).closest('form').find('p.room-ids-field-wrapper').show();
		} else {
			$(this).closest('form').find('p.count-field-wrapper').show();
			$(this).closest('form').find('p.room-ids-field-wrapper').hide();
		}
	});
	$('#widgets-right .type-field.room-widget-field').trigger('change');
	$(document).ajaxSuccess(function(e, xhr, settings) {
		var widget_id_base = 'lordcros-room-widget';
		
		if(settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) {
			$('#widgets-right .type-field.room-widget-field').each(function(){
				if ( $(this).val() == 'selected' ) {
					$(this).closest('form').find('p.count-field-wrapper').hide();
					$(this).closest('form').find('p.room-ids-field-wrapper').show();
				} else {
					$(this).closest('form').find('p.count-field-wrapper').show();
					$(this).closest('form').find('p.room-ids-field-wrapper').hide();
				}
			});
			

		}
	});

	/* LordCros Recent Posts widget fields */
	$('body').on('change', '#widgets-right .thumb-field.recent-posts-widget-field', function(){
		if ( $(this).is(':checked') ) {
			$(this).closest('form').find('p.size-field-wrapper').show();
		} else {
			$(this).closest('form').find('p.size-field-wrapper').hide();
		}
	});
	$('#widgets-right .thumb-field.recent-posts-widget-field').trigger('change');
	$(document).ajaxSuccess(function(e, xhr, settings) {
		var widget_id_base = 'lordcros-recent-posts-widget';
		
		if(settings.data.search('action=save-widget') != -1 && settings.data.search('id_base=' + widget_id_base) != -1) {
			$('#widgets-right .thumb-field.recent-posts-widget-field').each(function(){
				if ( $(this).is(':checked') ) {
					$(this).closest('form').find('p.size-field-wrapper').show();
				} else {
					$(this).closest('form').find('p.size-field-wrapper').hide();
				}
			});
			

		}
	});

});

jQuery(function($) {
	"use strict";

	function alertLeavePage(e) {
		var dialogText = "Are you sure you want to leave?";
		e.returnValue = dialogText;
		return dialogText;
	}

	function addAlertLeavePage() {
		$('.import-demo-area .lordcros-install-demo-button').attr('disabled', 'disabled');
		$(window).bind('beforeunload', alertLeavePage);
	}

	function removeAlertLeavePage() {
		$('.import-demo-area .lordcros-install-demo-button').removeAttr('disabled');
		$(window).unbind('beforeunload', alertLeavePage);
		setTimeout(function() {
			$('.lordcros-demo-import #import-status').slideUp().html('');
		}, 3000);
	}

	function showImportMessage(selected_demo, message, count, index) {
		var html = '',
			percent = 0;
		
		if (selected_demo) {
			html += '<h3>Installing ' + $('#' + selected_demo).html() + '</h3>';
		}
		if (message) {
			html += '<strong>' + message + '</strong>';
		}
		if (count && index) {
			percent = parseInt( index / count * 100 );
			if (percent > 100) {
				percent = 100;
			}

			html += '<div class="import-progress-bar" data-progress="' + percent + '"><div style="width:' + percent + '%;"></div></div>';
		}
		$('.lordcros-demo-import #import-status').stop().show().html(html);
	}

	// install demo
	$('.lordcros-install-demo-button').on( 'click', function(e) {
		e.preventDefault();

		var $this = $(this),
			selected_demo = $this.data('demo-id'),
			disabled = $this.attr('disabled');

		if ( disabled ) {
			return;
		}

		addAlertLeavePage();

		$('#lordcros-install-demo-type').val(selected_demo);
		$('#lordcros-install-options').slideDown();
		$('.import-success.importer-notice').slideUp();

		$('html, body').stop().animate({
			scrollTop: $('#lordcros-install-options').offset().top - 50
		}, 600);
	} );

	$('.lordcros-install-demo-button[disabled="disabled"]').on('click', function(e) {
		e.preventDefault();

		return;
	});

	// cancel import button
	$('#lordcros-import-no').click(function() {
		$('#lordcros-install-options').slideUp();
		removeAlertLeavePage();
	});

	// import
	$('#lordcros-import-yes').click(function() {
		var demo = $('#lordcros-install-demo-type').val(),
			options = {
				demo: demo,
				reset_menus: $('#lordcros-reset-menus').is(':checked'),
				reset_widgets: $('#lordcros-reset-widgets').is(':checked'),
				import_dummy: $('#lordcros-import-dummy').is(':checked'),
				import_widgets: $('#lordcros-import-widgets').is(':checked'),
				import_options: $('#lordcros-import-options').is(':checked'),
			};

		if (options.demo) {
			showImportMessage(demo, '');
			lordcros_import_options(options);
		}
		$('#lordcros-install-options').slideUp();
	});

	// import options
	function lordcros_import_options(options) {
		if ( ! options.demo ) {
			removeAlertLeavePage();
			return;
		}
		if ( options.import_options ) {
			var demo = options.demo,
				data = {'action': 'lordcros_import_options', 'demo': demo};

			showImportMessage(demo, 'Importing theme options');
			
			$.post(ajaxurl, data, function(response) {
				if ( response ) {
					showImportMessage(demo, response);
				}
				lordcros_reset_menus(options);
			})
			.fail(function(response) {
				lordcros_reset_menus(options);
			});
		} else {
			lordcros_reset_menus(options);
		}
	}

	// reset_menus
	function lordcros_reset_menus(options) {
		if ( ! options.demo ) {
			removeAlertLeavePage();
			return;
		}
		if ( options.reset_menus ) {
			var demo = options.demo,
				data = {'action': 'lordcros_reset_menus'};

			$.post(ajaxurl, data, function(response) {
				if (response) showImportMessage(demo, response);
				lordcros_reset_widgets(options);
			}).fail(function(response) {
				lordcros_reset_widgets(options);
			});
		} else {
			lordcros_reset_widgets(options);
		}
	}

	// reset widgets
	function lordcros_reset_widgets(options) {
		if ( ! options.demo ) {
			removeAlertLeavePage();
			return;
		}
		if ( options.reset_widgets ) {
			var demo = options.demo,
				data = {'action': 'lordcros_reset_widgets'};

			$.post(ajaxurl, data, function(response) {
				if (response) showImportMessage(demo, response);
				lordcros_import_dummy(options);
			}).fail(function(response) {
				lordcros_import_dummy(options);
			});
		} else {
			lordcros_import_dummy(options);
		}
	}

	// import dummy content
	var dummy_index = 0, dummy_count = 0, dummy_process = 'import_start';
	function lordcros_import_dummy(options) {
		if ( ! options.demo ) {
			removeAlertLeavePage();
			return;
		}
		if ( options.import_dummy ) {
			var demo = options.demo,
				data = {'action': 'lordcros_import_dummy', 'process':'import_start', 'demo': demo};

			dummy_index = 0;
			dummy_count = 0;
			dummy_process = 'import_start';
			lordcros_import_dummy_process(options, data);
		} else {
			lordcros_import_widgets(options);
		}
	}

	// import dummy content process
	function lordcros_import_dummy_process(options, args) {
		var demo = options.demo;
		$.post(ajaxurl, args, function(response) {
			if ( response && /^[\],:{}\s]*$/.test(response.replace(/\\["\\\/bfnrtu]/g, '@' ).
				replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g, ']').
				replace(/(?:^|:|,)(?:\s*\[)+/g, ''))) {
				response = $.parseJSON(response);
				if (response.process != 'complete') {
					var requests = {'action': 'lordcros_import_dummy'};
					if (response.process) requests.process = response.process;
					if (response.index) requests.index = response.index;

					requests.demo = demo;
					lordcros_import_dummy_process(options, requests);

					dummy_index = response.index;
					dummy_count = response.count;
					dummy_process = response.process;

					showImportMessage(demo, response.message, dummy_count, dummy_index);
				} else {
					showImportMessage(demo, response.message);
					lordcros_import_widgets(options);
				}
			} else {
				showImportMessage(demo, 'Failed importing! Please check the "System Status" tab to ensure your server meets all requirements for a successful import. Settings that need attention will be listed in red.');
				lordcros_import_widgets(options);
			}
		}).fail(function(response) {
			if ( dummy_index < dummy_count ) {
				var requests = {'action': 'lordcros_import_dummy'};
				requests.process = dummy_process;
				requests.index = ++dummy_index;
				requests.demo = demo;

				lordcros_import_dummy_process(options, requests);
			} else {
				var requests = {'action': 'lordcros_import_dummy'};
				requests.process = dummy_process;
				requests.demo = demo;

				lordcros_import_dummy_process(options, requests);
			}
		});
	}

	// import widgets
	function lordcros_import_widgets(options) {
		if ( ! options.demo ) {
			removeAlertLeavePage();
			return;
		}
		if ( options.import_widgets ) {
			var demo = options.demo,
				data = {'action': 'lordcros_import_widgets', 'demo': demo};

			showImportMessage(demo, 'Importing widgets');

			$.post(ajaxurl, data, function(response) {
				if (response) showImportMessage(demo, response);
				lordcros_import_finished(options);
			}).fail(function(response) {
				lordcros_import_finished(options);
			});
		} else {
			lordcros_import_finished(options);
		}
	}

	// import finished
	function lordcros_import_finished(options) {
		if ( ! options.demo ) {
			removeAlertLeavePage();
			return;
		}

		var demo = options.demo;

		showImportMessage(demo, 'Import Finished!');
		setTimeout(removeAlertLeavePage, 600);

		$('.import-success.importer-notice').slideDown();
	}
});