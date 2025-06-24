(function( $ ) {
	'use strict';

	// Start things up
	paracharts_admin.init = function() {

		this.post_id = $( document.getElementById( 'post_ID' ) ).attr( 'value' );
		this.nonce   = $( 'input[name="paracharts[nonce]"]' ).attr( 'value' );

		// Store the setting inputs and title input for use later
		this.$setting_inputs = $( document.getElementById( 'paracharts' ) ).find( '.settings input, .settings select' );
		this.$title_input    = $( document.getElementById( 'titlewrap' ) ).find( 'input' );
		this.$subtitle_input = $( document.getElementById( 'titlediv' ) ).find( '#paracharts-subtitle' );
		this.$y_min_value    = $( document.getElementById( 'paracharts-y-min-value' ) );

		// Store these for later
		this.$form_buttons = $( '#save-post, #wp-preview, #post-preview, #publish' );

		// Build the spreadsheets
		this.build_spreadsheets();

		// Handle the spreadsheet controls
		this.handle_sheet_controls();

		// Set the form encoding type to multipart/form-data so that the CSV import will work
		var $form = $( 'form#post' );
		$form.attr( 'enctype', 'multipart/form-data' );

		// Watch form submissions and stop them if necessary or update data value
		$form.on( 'submit', function( event ) {
			if ( false === paracharts_admin.allow_form_submission ) {
				event.preventDefault();
			} else {
				$( document.getElementById( 'paracharts-spreadsheet' ) ).find( '.data' ).val(
					JSON.stringify( paracharts_admin.get_data() )
				);

				$( '.hands-on-table-sheet-tab-input' ).attr( 'disabled', false );
			}
		});

		// Watch for clicks on the y min toggle
		$( document.getElementById( 'paracharts-y-min' ) ).on( 'click', function () {
			if ( $( this ).is( ':checked' ) ) {
				paracharts_admin.$y_min_value.attr( 'disabled', false ).focus();
			} else {
				paracharts_admin.$y_min_value.attr( 'disabled', true );
			}
		});

		// Watch for clicks on the shortcode input
		$( document.getElementById( 'paracharts-shortcode' ) ).on( 'click', function () {
			$( this ).select();
		});

		// Watch for clicks on the image input
		$( document.getElementById( 'paracharts-image' ) ).on( 'click', function () {
			$( this ).select();
		});

		// Watch for clicks on the CSV tools
		this.handle_csv_import();
		this.handle_csv_export();

		// Do instant preview unless it's been turned off
		if ( 'no-preview' !== this.performance ) {
			this.watch_for_chart_changes();
		}
	};

	// Get data from the spreadsheets
	paracharts_admin.get_data = function() {
		var $data = [];

		var spreadsheet = 0;

		$.each( this.$spreadsheets, function( i ) {
			$data[ spreadsheet ] = paracharts_admin.$spreadsheets[ i ].getData();
			spreadsheet++;
		});

		return $data;
	}

	// Instantiate the spreadsheets
	paracharts_admin.build_spreadsheets = function() {
		this.$spreadsheet_divs  = $( document.getElementById( 'hands-on-table-sheets' ) );
		this.$spreadsheet_tabs  = $( document.getElementById( 'hands-on-table-sheet-tabs' ) );
		this.sheet_div_template = Handlebars.compile( $( document.getElementById( 'paracharts-sheet-div' ) ).html() );
		this.sheet_tab_template = Handlebars.compile( $( document.getElementById( 'paracharts-sheet-tab' ) ).html() );

		this.$spreadsheets = {};
		console.log( 'whatsup' );
		// hands_on_table_data is an array of data sets so we cycle through them and build a spreadsheet object for each one
		$.each( hands_on_table_data, function( i, data ) {
			var instance = Number( i ) + 1;

			paracharts_admin.create_spreadsheet( instance, data );
		});
	}

	// Instantiate a spreedsheet
	paracharts_admin.create_spreadsheet = function( i, data ) {
		this.$spreadsheet_divs.append( this.sheet_div_template( { post_id: this.post_id, instance: i } ) );
		// Note we're purposely not getting a jQuery version of this object because handsontable likes it that way
		var $spreadsheet_div = document.getElementById( 'hands-on-table-sheet-' + this.post_id + '-' + i );

		// New charts won't actually have data so we'll pass something handsontable understands
		if ( '' == data ) {
			data = [[]];
		}

		this.$spreadsheets[i] = new Handsontable( $spreadsheet_div, {
			data:         data,
			colHeaders:   true,
			rowHeaders:   true,
			height:       350,
			minRows:      17,
			minCols:      37,
			minSpareRows: 1,
			minSpareCols: 1,
			contextMenu:  true,
			stretchH:     'all'
		});

		// Built tab for sheet this sheet (it's only visible if the user selects an appropriate chart type but we build it now anyway)
		var $template_vars = {
			post_id: paracharts_admin.post_id,
			instance: i
		};

		if ( i > 0 ) {
			$( $spreadsheet_div ).addClass( 'hide' );
			$template_vars.class = 'nav-tab';
		} else {
			this.active_set = i;
			$template_vars.class = 'nav-tab nav-tab-active';
		}

		if ( 'undefined' !== typeof this.set_names[ i - 1 ] ) {
			$template_vars.value = this.set_names[ i - 1 ];
		} else {
			$template_vars.value = 'Sheet ' + i;
		}

		this.$spreadsheet_tabs.append( this.sheet_tab_template( $template_vars ) );

		// Set the tab input width
		var $tab_input = $( '#hands-on-table-sheet-tab-' + this.post_id + '-' + i + ' input' );
		paracharts_admin.resize_input( $tab_input );

		// Add change event so we update on spreadsheet changes
		paracharts_admin.$spreadsheets[ i ].addHook( 'afterChange', function() {
			paracharts_admin.refresh_chart();
		});

		this.last_set = i;
	}

	// Handle spreadsheet functionality
	paracharts_admin.handle_sheet_controls = function() {
		// Add a spreedsheet
		this.$spreadsheet_tabs.find( '.add-sheet' ).on( 'click', function( event ) {
			event.preventDefault();
			paracharts_admin.create_spreadsheet( paracharts_admin.last_set + 1, '' );
			var new_tab = document.getElementById( 'hands-on-table-sheet-tab-' + paracharts_admin.post_id + '-' + paracharts_admin.last_set );

			// Check tab count
			paracharts_admin.check_tab_count();

			$( new_tab ).click().find( 'input' ).trigger( 'dblclick' );
			paracharts_admin.refresh_chart();
		});

		// Handle regular clicks on the tabs
		this.$spreadsheet_tabs.on( 'click', '.nav-tab', function( event ) {
			event.preventDefault();

			if ( $( this ).hasClass( 'nav-tab-active' ) ) {
				return;
			}

			paracharts_admin.$spreadsheet_divs.find( '.hands-on-table-sheet' ).addClass( 'hide' );
			$( document.getElementById( 'hands-on-table-sheet-' + paracharts_admin.post_id + '-' + $(this).data( 'instance' ) ) ).removeClass( 'hide' );

			paracharts_admin.$spreadsheet_tabs.find( '.nav-tab' ).removeClass( 'nav-tab-active' );
			$( this ).addClass( 'nav-tab-active' );
			paracharts_admin.active_set = $(this).data( 'instance' );
		});

		// On the initial load of the interface we should select the initial tab
		this.$spreadsheet_tabs.find( '.nav-tab' ).first().click();

		// Handle double clicks and long presses on the tabs
		this.$spreadsheet_tabs.on( 'dblclick taphold', '.nav-tab', function( event ) {
			event.preventDefault();

			var $input = $( this ).find( 'input' );
			$input.attr( 'disabled', false ).focus();
		});

		// Set input back to disabled on blur
		this.$spreadsheet_tabs.on( 'blur', 'input', function( event ) {
			$( this ).attr( 'disabled', true );
			paracharts_admin.refresh_chart();
		});

		// Set input back to disabled on return/enter
		this.$spreadsheet_tabs.on( 'keydown', 'input', function( event ) {
			if ( 13 === event.keyCode ) {
				event.preventDefault();
				$( this ).attr( 'disabled', true );
				paracharts_admin.refresh_chart();
			}
		});

		// Resize the input based on it's value
		this.$spreadsheet_tabs.on( 'keydown keyup input propertychange change', 'input', function( event ) {
			paracharts_admin.resize_input( $( this ) );
		});

		// Remove a tab/spreadsheet
		this.$spreadsheet_tabs.on( 'click', '.dashicons-dismiss', function( event ) {
			if ( ! confirm( paracharts_admin.delete_comfirm ) ) {
				return;
			}

			var $tab = $( this ).closest( '.nav-tab' );

			// Select the tab we're working with if necessary
			if ( ! $tab.hasClass( 'nav-tab-active' ) ) {
				$tab.click();
			}

			var instance = $( this ).closest( '.nav-tab' ).data( 'instance' );

			// Delete the spreedsheet
			delete paracharts_admin.$spreadsheets[ instance ];

			// Remove the tab
			$tab.remove();

			// Remove the spreedsheet div
			$( document.getElementById( 'hands-on-table-sheet-' + paracharts_admin.post_id + '-' + instance ) ).remove();

			// Check tab count
			paracharts_admin.check_tab_count();

			// Select the first tab and refresh the chart to reflect the changes
			paracharts_admin.$spreadsheet_tabs.find( '.nav-tab' ).first().click();
			paracharts_admin.refresh_chart();
		});
	};

	// Set last tab as do-not-delete if tab count is 1
	paracharts_admin.check_tab_count = function() {
		if ( 1 == this.$spreadsheet_tabs.find( '.nav-tab' ).length ) {
			this.$spreadsheet_tabs.find( '.nav-tab' ).addClass( 'do-not-delete' );
		} else {
			this.$spreadsheet_tabs.find( '.nav-tab' ).removeClass( 'do-not-delete' );
		}
	};

	// Resize an input based on it's value
	paracharts_admin.resize_input = function( $input ) {
		// Get what we need to calculate the value width
		var font = window.getComputedStyle( document.getElementById( $input.attr( 'id' ) ) ).font;
		var text = $input.val();

		// Get what we need to properly size the input with the value width
		var border_width  = window.getComputedStyle( document.getElementById( $input.attr( 'id' ) ) ).getPropertyValue( 'border-width' ).replace( 'px', '' );
		var padding_left  = window.getComputedStyle( document.getElementById( $input.attr( 'id' ) ) ).getPropertyValue( 'padding-left' ).replace( 'px', '' );
		var padding_right = window.getComputedStyle( document.getElementById( $input.attr( 'id' ) ) ).getPropertyValue( 'padding-right' ).replace( 'px', '' );

		// Calculate width of the input value
		var input_canvas  = document.createElement( 'canvas' );
	    var input_context = input_canvas.getContext( '2d' );

		input_context.font = font;

		var metrics = input_context.measureText( text );
		var width   = Math.ceil( metrics.width );

		$input.css( 'width', ( border_width * 2 ) + parseInt( padding_left ) + width + parseInt( padding_right ) + 'px' );
	};

	// Handle CSV import functionality
	paracharts_admin.handle_csv_import = function() {
		var $csv_container = $( document.getElementById( 'paracharts-csv' ) );
		var $select        = $csv_container.find( '.import .select.button' );
		var $confirmation  = $csv_container.find( '.import .confirmation' );
		var $import_form   = $( document.getElementById( 'paracharts-csv-import-form' ) );
		var $file_input    = $import_form.find( 'input[type=file]' );
		var $file_info     = $csv_container.find( '.file-info' );
		var $file_error    = $csv_container.find( '.file.error' );
		var $file_import   = $csv_container.find( '.import.in-progress' );
		var $import_error  = $csv_container.find( '.import.error' );
		var $cancel        = $csv_container.find( '.dashicons-dismiss' );

		// Watch for clicks on the select button
		$select.on( 'click', function( event ) {
			event.preventDefault();
			$file_error.addClass( 'hide' );
			$import_error.addClass( 'hide' );
			$file_input.trigger( 'click' );
		});

		// Watch for changes to the file input
		$file_input.on( 'change', function( event ) {
			var file_name = $( this ).val().replace( /C:\\fakepath\\/i, '' );

			if ( -1 === file_name.search( /.+(\.csv)$/ ) ) {
				$file_error.removeClass( 'hide' );
				return;
			}

			$file_info.find( '.file-name' ).text( file_name );

			$select.addClass( 'hide' );
			$confirmation.removeClass( 'hide' );
			$file_info.removeClass( 'hide' );
		});

		// Watch for clicks on the cancel button
		$cancel.on( 'click', function( event ) {
			event.preventDefault();
			$file_info.addClass( 'hide' );
			$file_input.val( '' );
			$select.removeClass( 'hide' );
			$confirmation.addClass( 'hide' );
		});

		// Watch for clicks on import button
		$confirmation.find( '.button' ).on( 'click', function( event ) {
			event.preventDefault();

			$file_info.addClass( 'hide' );
			$confirmation.addClass( 'hide' );
			$import_error.addClass( 'hide' );
			$file_import.removeClass( 'hide' );

			$import_form.trigger( 'submit' );
		});

		// Watch for CSV import form submission
		$import_form.on( 'submit', function( event ) {
			event.preventDefault();

			var $form_data = new FormData( this );

			$form_data.append( 'post_id', paracharts_admin.post_id );
			$form_data.append( 'csv_delimiter', $confirmation.find( 'select' ).val() );
			$form_data.append( 'nonce', paracharts_admin.nonce );

			var request = $.ajax({
				url: 'admin-ajax.php?action=paracharts_import_csv',
				type: 'POST',
				data: $form_data,
				cache: false,
				dataType: 'json',
				// Don't process the files
				processData: false,
				// Set content type to false as jQuery will tell the server its a query string request
				contentType: false
			});

			request.done( function( response ) {
				if ( false == response.success ) {
					$import_error.text( response.data );
					$import_error.removeClass( 'hide' );

					$file_input.val( '' );
					$select.removeClass( 'hide' );
					$file_import.addClass( 'hide' );

					return false;
				}

				// Update the spreadsheet with the new data
				paracharts_admin.$spreadsheets[ paracharts_admin.active_set ].loadData( response.data );

				$file_input.val( '' );
				$select.removeClass( 'hide' );
				$file_import.addClass( 'hide' );
			});
		});
	};

	// Handle CSV export functionality
	paracharts_admin.handle_csv_export = function() {
		$( document.getElementById( 'paracharts-csv' ) ).find( '.export a' ).on( 'click', function( event ) {
			event.preventDefault();

			var $form = $( document.getElementById( 'paracharts-csv-export-form' ) );
			var $data = paracharts_admin.$spreadsheets[ paracharts_admin.active_set ].getData();

			var set_name = paracharts_admin.$spreadsheet_tabs.find( '.nav-tab-active input' ).val();

			$( document.getElementById( 'paracharts-csv-post-id' ) ).val( paracharts_admin.post_id );
			$( document.getElementById( 'paracharts-csv-data' ) ).val( JSON.stringify( $data ) );
			$( document.getElementById( 'paracharts-csv-title' ) ).val( paracharts_admin.$title_input.val() );
			$( document.getElementById( 'paracharts-csv-set-name' ) ).val( set_name );

			$form.trigger( 'submit' );
		});
	};

	// Watch for changes to the chart settings or title
	paracharts_admin.watch_for_chart_changes = function() {
		this.$setting_inputs.on( 'change', function() {
			paracharts_admin.refresh_chart();
		});

		this.$title_input.on( 'change', function() {
			paracharts_admin.refresh_chart();
		});

		this.$subtitle_input.on( 'change', function() {
			paracharts_admin.refresh_chart();
		});
	};

	// Refresh chart
	paracharts_admin.refresh_chart = function() {
		if ( 'no-preview' === this.performance || 'yes' !== this.instant_preview_support ) {
			return false;
		}

		// Stop any existing requests so we don't just pile them up
		if ( this.request ) {
			this.request.abort();
		}

		// Stop form submission while we wait for the chart to refresh and a new image to generate
		paracharts_admin.form_submission( false );

		// Build an object with all fo the post_meta values
		var $post_meta = {};

		$.each( this.$setting_inputs, function() {
			// Don't record unselected/unchecked radio/checkboxes
			if (
				   'radio' !== $( this ).attr( 'type' )
				&& 'checkbox' !== $( this ).attr( 'type' )
				|| true === $( this ).is( ':checked' )
			 ) {
				$post_meta[ $( this ).attr( 'name' ).replace( /^paracharts\[|\]$/g , '' ) ] = $( this ).val();
			}
		});

		$post_meta[ 'subtitle' ] = this.$subtitle_input.val();

		$post_meta.data = JSON.stringify( paracharts_admin.get_data() );

		$post_meta['set_names'] = [];

		$.each( this.$spreadsheet_tabs.find( '.nav-tab' ), function( i ) {
			$post_meta['set_names'][ i ] = $( this ).find( 'input' ).val();
		});

		// Request a new chart_args object so we can rerender the chart with the changes
		this.request = $.ajax({
			url: 'admin-ajax.php?action=paracharts_get_chart_args',
			type: 'POST',
			data: {
				post_id:   paracharts_admin.post_id,
				nonce:     paracharts_admin.nonce,
				title:     this.$title_input.val(),
				post_meta: $post_meta
			},
			cache: false,
			dataType: 'json'
		});

		this.request.done( function( response ) {
			if ( true !== response.success ) {
				return false;
			}

			$( '.paracharts' ).trigger({
				type:     'chart_args_success',
				response: response
			});
		});
	};

	paracharts_admin.form_submission = function( enable ) {
		paracharts_admin.allow_form_submission = enable;

		if ( false === enable ) {
			paracharts_admin.$form_buttons.addClass( 'disabled' );
		} else {
			paracharts_admin.$form_buttons.removeClass( 'disabled' );
		}
	};

	$( function() {
		paracharts_admin.init();
	} );
})( jQuery );