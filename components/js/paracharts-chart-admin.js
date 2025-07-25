var chart_admin = {};

(function( $ ) {
	'use strict';

	// Start things up
	chart_admin.init = function() {
		// Only show fields/inputs that are appropriate for the current chart type
		var $chart_type_select = $( document.getElementById( 'paracharts-type' ) );
		$chart_type_select.on( 'load, change', this.handle_chart_type );
		$chart_type_select.trigger( 'change' );

		// Watch for a new chart to be built, then get SVG.
		// $( '.paracharts-container' ).on( 'render_done', this.generate_image_from_chart );
	
		$( '.paracharts-container' ).on( 'chart_args_success', this.refresh_chart );
	};

	// Handle chart type input changes so the settings UI only reflects appropriate options
	chart_admin.handle_chart_type = function( event ) {
		var chart_type        = $( this ).val();
		var $chart_meta_box   = $( document.getElementById( 'paracharts' ) );
		var $spreadsheet_tabs = $( document.getElementById( 'hands-on-table-sheet-tabs' ) );

		// Show everything before hiding the options we don't want
		$chart_meta_box.find( '.row' ).removeClass( 'hide' );
		$spreadsheet_tabs.addClass( 'hide' );

		if (
			   'column' === chart_type
			|| 'bar' === chart_type
		) {
			$chart_meta_box.find( '.row.y-min' ).addClass( 'hide' );
		}

		if (
			   'pie' === chart_type
			|| 'donut' === chart_type
		) {
			$chart_meta_box.find( '.row.vertical-axis, .row.horizontal-axis, .row.y-min' ).addClass( 'hide' );
		}

	};

	// Refresh the chart arguments
	chart_admin.refresh_chart = function( event ) {
		// For Paracharts, a dynamic preview requires us to fetch the manifest with temporary info.
		console.log( 'chart hypothetically refreshes here' );
		let chart = document.querySelector( 'para-chart' );
		let manifest = chart.getAttribute( 'manifest' );
		manifest = new URL( manifest );
		manifest.searchParams.delete( 'dynamic_chart' );
		manifest.searchParams.append( 'dynamic_chart', Math.random() );
		chart.setAttribute( 'manifest', manifest );

		paracharts_admin.form_submission( true );
	};

	$( function() {
		chart_admin.init();
	} );
})( jQuery );