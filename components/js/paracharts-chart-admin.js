var chart_admin = {};

(function( $ ) {
	'use strict';

	// Start things up
	chart_admin.init = function() {
		// Only show fields/inputs that are appropriate for the current chart type
		var $chart_type_select = $( document.getElementById( 'paracharts-type' ) );
		$chart_type_select.on( 'load, change', this.handle_chart_type );
		$chart_type_select.trigger( 'change' );

		// Watch for a new chart to be built
		if ( 'default' === chart_admin.performance && 'yes' === chart_admin.image_support ) {
			$( '.paracharts-container' ).on( 'render_done', this.generate_image_from_chart );
		}

		$( '.paracharts-container' ).on( 'chart_args_success', this.refresh_chart );
	};

	// Handle chart type input changes so the settings UI only reflects appropriate options
	chart_admin.handle_chart_type = function( event ) {
		var chart_type        = $( this ).val();
		var $chart_meta_box   = $( document.getElementById( 'paracharts' ) );
		var $spreadsheet_tabs = $( document.getElementById( 'hands-on-table-sheet-tabs' ) );

		// Show everything before hiding the options we don't want
		$chart_meta_box.find( '.row' ).removeClass( 'hide' );

		if (
			   'area' === chart_type
			|| 'column' === chart_type
			|| 'stacked-column' === chart_type
			|| 'bar' === chart_type
			|| 'stacked-bar' === chart_type
		) {
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if (
			   'column' === chart_type
			|| 'stacked-column' === chart_type
			|| 'bar' === chart_type
			|| 'stacked-bar' === chart_type
		) {
			$chart_meta_box.find( '.row.y-min' ).addClass( 'hide' );
		}

		if (
			   'pie' === chart_type
			|| 'doughnut' === chart_type
			|| 'polar' === chart_type
		) {
			$chart_meta_box.find( '.row.vertical-axis, .row.horizontal-axis, .row.y-min' ).addClass( 'hide' );
		}

		if (
			   'scatter' === chart_type
			|| 'bubble' === chart_type
		) {
			$chart_meta_box.find( '.row.y-min' ).addClass( 'hide' );
			$spreadsheet_tabs.removeClass( 'hide' );
		}

		if ( 'polar' === chart_type ) {
			$spreadsheet_tabs.addClass( 'hide' );
		}

		if (
			   'radar' === chart_type
			|| 'radar-area' === chart_type
		) {
			$chart_meta_box.find( '.row.vertical-axis, .row.horizontal-axis, .row.y-min' ).addClass( 'hide' );
			$spreadsheet_tabs.removeClass( 'hide' );
		}
	};

	// Generate a PNG image out of a rendered chart
	chart_admin.generate_image_from_chart = function( event ) {
		chart_admin.form_submission(false);

		var $canvas_source = document.getElementById( 'paracharts-' + event.post_id + '-' + event.instance );
		var $target_canvas = $( '#paracharts-canvas-render-' + event.post_id );
		var target_context = document.getElementById( 'paracharts-canvas-render-' + event.post_id ).getContext('2d');

		var chart_width  = chart_admin.image_width;
		var chart_height = $( document.getElementById( 'paracharts-height' ) ).val();

		var image_width  = chart_width * chart_admin.image_multiplier;
		var image_height = chart_height * chart_admin.image_multiplier;

		// Set some constraints on the chart to get it into the right size for image generation
		$( '.paracharts-container' ).attr( 'width', chart_width ).css( 'width', chart_width + 'px' ).css( 'height', chart_height + 'px' );

		// Resize the chart
		window[ 'paracharts_' + event.post_id + '_1' ].chart.resize();

		// Set the background to a solid white color (we don't need to reset this explicitly as it's undone by the later chart.resize() call)
		var $chart_context = window[ 'paracharts_' + event.post_id + '_1' ].chart.canvas.getContext( '2d' );

		$chart_context.save();
		$chart_context.globalCompositeOperation = 'destination-over';
		$chart_context.fillStyle = 'white';
		$chart_context.fillRect(0, 0, window[ 'paracharts_' + event.post_id + '_1' ].chart.width, window[ 'paracharts_' + event.post_id + '_1' ].chart.height );
		$chart_context.restore();

		// Get a PNG of the chart
		var img = window[ 'paracharts_' + event.post_id + '_1' ].chart.toBase64Image( 'image/png', 1 );

		// Remove the restraints
		$( '.paracharts-container' ).removeAttr( 'width' ).css( 'width', '' ).css( 'height', '' );

		// Put the chart back into it's normal state
		window[ 'paracharts_' + event.post_id + '_1' ].chart.resize();

		// Save the image string to the text area so we can save it on update/publish
		$( document.getElementById( 'paracharts-img' ) ).val( img );

		// Allow form submission now that we've got a valid img value set
		chart_admin.form_submission( true );
	};


	// Refresh the chart arguments
	chart_admin.refresh_chart = function( event ) {
		// For Paracharts, a dynamic preview requires us to fetch the manifest with temporary info.
		// Not sure how that will work.
		console.log( 'chart hypothetically refreshes here' );
	};

	$( function() {
		chart_admin.init();
	} );
})( jQuery );