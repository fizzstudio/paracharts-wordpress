var paracharts_helpers = {
	locale: 'en-US'
};

(function( $ ) {
	'use strict';

	// Start things up
	paracharts_helpers.init = function() {
		$( '.paracharts' ).on( 'render_start', function( event ) {
			var chart_object = 'paracharts_' + event.post_id + '_' + event.instance;

			if ( 'undefined' === typeof window[chart_object] ) {
				return;
			}

			paracharts_helpers.locale = window[chart_object].chart_args.options.locale;

			var type = window[chart_object].chart_args.type;

			var value_prefix = window[chart_object].chart_args.value_prefix;
			var value_suffix = window[chart_object].chart_args.value_suffix;
			var labels_pos   = window[chart_object].chart_args.labels_pos;

			if ( 'bubble' == window[chart_object].chart_args.type ) {
				window[chart_object].chart_args.data = paracharts_helpers.preprocess_bubble_data( window[chart_object].chart_args.data );
				window[chart_object].chart_args.options.plugins.tooltip.callbacks = {
					label: (item) => {
	                	return paracharts_helpers.bubble_chart_tooltip_label( item, type, value_prefix, value_suffix, labels_pos );
	                }
				}
			} else if ( 'scatter' == window[chart_object].chart_args.type ) {
				window[chart_object].chart_args.options.plugins.tooltip.callbacks = {
					label: (item) => {
	                	return paracharts_helpers.scatter_chart_tooltip_label( item, type, value_prefix, value_suffix, labels_pos );
	                }
				}
			} else {
				window[chart_object].chart_args.options.plugins.tooltip.callbacks = {
					label: (item) => {
	                	return paracharts_helpers.chart_tooltip_label( item, type, value_prefix, value_suffix, labels_pos );
	                }
				}
			}

			window[chart_object].chart_args.options.plugins.datalabels.formatter = function( label ) {
				// If there's no label we stop here
				if ( null === label ) {
					return label;
				}

				// Handling for Bubble/Scatter Charts
				if ( 'undefined' !== typeof label.label ) {
					label = label.label;
				} else if ( 'undefined' !== typeof label.r ) {
					label = label.r;
				} else if ( 'undefined' !== typeof label.y ) {
					label = label.y;
				}

				if ( $.isNumeric( label ) ) {
					return paracharts_helpers.number_format( label );
				} else {
					return label;
				}
			};
		});
	};

	// Preprocess bubble chart data so bubble size is controlled but still relative to value
	// See https://chartio.com/learn/charts/bubble-chart-complete-guide/#scale-bubble-area-by-value
	paracharts_helpers.preprocess_bubble_data = function( $data ) {
		const value_range = $data.datasets[0].data.reduce((acc, val) => Math.max(acc, val.r), 0);
		const pixel_max   = 31;
		const pixel_min   = 1;
		const pixel_range = pixel_max - pixel_min;

		for ( const ds of $data.datasets ) {
			for ( const d of ds.data ) {
				d.original = d.r;

				const percentage_radius = Math.sqrt( Math.abs(d.r) / value_range );
				d.r = percentage_radius * pixel_range + pixel_min;
			}
		}

		return $data;
	};

	paracharts_helpers.bubble_chart_tooltip_label = function( item, type, prefix, suffix, labels_pos ) {
		var tooltip_label = [
			item.raw.label,
			item.chart.data.labels[0] + ': ' + paracharts_helpers.number_format( item.parsed.x ),
			item.chart.data.labels[1] + ': ' + paracharts_helpers.number_format( item.parsed.y ),
			item.chart.data.labels[2] + ': ' + paracharts_helpers.number_format( item.raw.original ),
		];

		return tooltip_label;
	};

	paracharts_helpers.scatter_chart_tooltip_label = function( item, type, prefix, suffix, labels_pos ) {
		var tooltip_label = [
			item.dataset.label,
			item.chart.data.labels[0] + ': ' + paracharts_helpers.number_format( item.parsed.x ),
			item.chart.data.labels[1] + ': ' + paracharts_helpers.number_format( item.parsed.y ),
		];

		return tooltip_label;
	};

	paracharts_helpers.chart_tooltip_label = function( item, type, prefix, suffix, labels_pos ) {
		var label = item.dataset.label;

		// If raw value is null we don't return anything
		if ( null == item.raw ) {
			return null;
		}

		// Depending on the chart type or data format the label is usually in one of two places
		if ( 'undefined' == typeof label ) {
			label = item.label;
		}

		// Bar tooltips already get the label in the tooltip title
		if ( 'bar' == type ) {
			label = '';
		}

		// Polar charts put the label in a strange place
		if ( 'polarArea' == type ) {
			label = item.chart.data.labels[ item.dataIndex ];
		}

		// Make sure we don't double labels
		if ( 'both' != labels_pos ) {
			label = '';
		}

		// Handle stacked bar/column charts a bit better
		if ( 'undefined' != typeof item.dataset.label && label != item.dataset.label ) {
			label += item.dataset.label;
		}

		if ( '' != label ) {
			label += ': ';
		}

		var tooltip_label = label + prefix + paracharts_helpers.number_format( item.raw ) + suffix;

		return tooltip_label;
	};

	paracharts_helpers.number_format = function( number ) {
		return Chart.helpers.formatNumber( number, paracharts_helpers.locale );
	};

	$( function() {
		paracharts_helpers.init();
	} );
})( jQuery );