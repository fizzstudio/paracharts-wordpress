<?php

class ParachartsJs {
	public $library            = 'paracharts';
	public $library_name       = 'ParaCharts';
	public $value_labels_limit = 15;
	public $value_labels_div   = 10;
	public $original_labels;
	public $post;
	public $post_meta;
	public $args;
	public $type_options       = array(
		'line',
		'stepline',
		'column',
		'bar',
		'pie',
		'donut',
		//'heatmap',
		//'scatter',
		//'histogram',
		//'lollipop',
	);
	public $type_option_names  = array();

	public $chart_types        = array(
		'line'      => 'line',
		'stepline'  => 'stepline',
		'column'    => 'column',
		'bar'       => 'bar',
		'pie'       => 'pie',
		'donut'     => 'donut',
		'heatmap'   => 'heatmap',
		'scatter'   => 'scatter',
		'histogram' => 'histogram',
		'lollipop'  => 'lollipop',
	);

	/**
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'paracharts_image_support', array( $this, 'paracharts_image_support' ), 10, 2 );
		add_filter( 'paracharts_iframe_scripts', array( $this, 'paracharts_iframe_scripts' ), 10, 2 );

		$this->type_option_names = array(
			'line'      => __( 'Line', 'paracharts' ),
			'stepline'  => __( 'Stepline', 'paracharts' ),
			'column'    => __( 'Column', 'paracharts' ),
			'bar'       => __( 'Bar', 'paracharts' ),
			'pie'       => __( 'Pie', 'paracharts' ),
			'donut'     => __( 'Donut', 'paracharts' ),
			'heatmap'   => __( 'Heatmap', 'paracharts' ),
			'scatter'   => __( 'Scatter', 'paracharts' ),
			'histogram' => __( 'Histogram', 'paracharts' ),
			'lollipop'  => __( 'Lollipop', 'paracharts' ),
		);
	}

	/**
	 * Get the necessary chart data for a given chart and assign it the right class vars
	 *
	 * @param int $post_id the WP ID for the chart post
	 * @param array $args any args we want to override the defaults for
	 */
	public function get_chart_data( $post_id, $args ) {
		$this->args = wp_parse_args( $args, paracharts()->get_chart_default_args );
		$this->post = get_post( $post_id );

		// If the post wasn't valid might as well stop here
		if ( ! $this->post ) {
			return;
		}

		$this->post_meta = paracharts()->get_post_meta( $this->post->ID );
	}

	/**
	 * Returns an arary of all settings and data needed to build a chart
	 *
	 * @param int $post_id WP post ID of the post you want chart args for
	 * @param array $args any args we want to override the defaults for
	 * @param bool $force optional param to force a rebuild of the data even if a cache was found
	 * @param bool $cache optional param to override the default behavior to cache the results
	 *
	 * @return string URL to the plugin directory with path if parameter was passed
	 */
	public function get_chart_args( $post_id, $args, $force = false, $cache = true ) {
		// There's a ton of work that goes into generating the chart args so we cache them
		$cache_key = $post_id . '-chart-args';

		if ( ! $force && $chart_args = wp_cache_get( $cache_key, paracharts()->slug ) ) {

			return $chart_args;
		}

		if ( ! $this->args || ! $this->post || ! $this->post_meta ) {
			$this->get_chart_data( $post_id, $args );
		}

		if ( isset( $this->post_meta['data']['sets'] ) ) {
			// Run the parse class on the data
			paracharts()->parse()->parse_data( $this->post_meta['data']['sets'][0], $this->post_meta['parse_in'] );
		} else {
			paracharts()->parse()->parse_data( [], $this->post_meta['parse_in'] );
		}

		$type         = $this->post_meta['type'];
		$description  = $this->post_meta['subtitle'];
		$x_units      = $this->post_meta['x_units'];
		$x_axis       = $this->post_meta['x_title'];
		$y_units      = $this->post_meta['y_units'];
		$y_axis       = $this->post_meta['y_title'];
		$controlpanel = $this->post_meta['controlpanel'];
		$y_min        = $this->post_meta['y_min'];

		switch ( $type ) {
			case 'column': // only works with singular data points.
			case 'line':
			case 'pie':
			case 'stepline': // What's the difference between line and stepline?
			case 'heatmap': // hypothesizing; not working.
			case 'scatter': // hypothesizing; not working.
			case 'histogram': // hypothesizing; not working.
				$x_display_type = array(
					'type'        => 'axis',
					'orientation' => 'horizontal',
				);
				$y_display_type = array(
					'type'        => 'axis',
					'orientation' => 'vertical',
				);
				break;
			case 'donut':
			case 'lollipop': // hypothesizing; not working.
				$x_display_type = array(
					'type' => 'marking',
				);
				$y_display_type = array(
					'type' => 'angle',
				);
				break;
			case 'bar': // only works with singular data points
				$x_display_type = array(
					'type'        => 'axis',
					'orientation' => 'vertical',
				);
				$y_display_type = array(
					'type'        => 'axis',
					'orientation' => 'horizontal',
				);
				break;
		}
		if ( $y_min ) {
			$y_display_type['minDisplayed'] = $y_min;
		}

		// Generate the manifest data for the chart.
		$x_facet = (object) array(
			'label'        => $x_axis,
			'variableType' => 'independent',
			'measure'      => 'nominal',
			'datatype'     => 'string',
			'units'        => $x_units,
			'displayType'  => (object) $x_display_type,
		);

		$y_facet = (object) array(
			'label'        => $y_axis,
			'variableType' => 'dependent',
			'measure'      => 'ratio',
			'datatype'     => 'number',
			'units'        => $y_units,
			'multiplier'   => 0.01,
			'displayType'  => (object) $y_display_type,
		);

		// Not sure how to use this yet.
		$labels_array = $this->get_value_labels_array();
		$records      = $this->get_data_sets( $labels_array );

		// For multiline graphs, the column headers become keys, and the row headers are record labels.
		$series = array(
			(object) array(
				'key'     => $this->esc_title( $description ),
				'theme'   => (object) array(
					'baseQuantity' => 'energy',
					'baseKind'     => 'proportion',
					'entity'       => 'the Universe',
					'aggregate'    => 'total',
				),
				'records' => $records,
			),
		);

		$data = (object) array(
			'source' => 'inline',
		);

		$settings = (object) array(
			'controlPanel.isControlPanelDefaultOpen' => $controlpanel,
		);

		$chart_args = (object) array(
			'datasets' => array(
				(object) array(
					'type'     => $this->chart_types[ $type ],
					'title'    => $this->esc_title( apply_filters( 'the_title', $this->post->post_title, $this->post->ID ) ),
					'facets'   => (object) array(
						'x' => $x_facet,
						'y' => $y_facet,
					),
					'series'   => $series,
					'data'     => $data,
					'settings' => $settings,
				),
			),
		);
	
		// Forcing a minimum value of 0 prevents the built in fudging which sometimes looks weird
		if (
			$this->post_meta['y_min']
			&& (
				   'line' == $type
				|| 'spline' == $type
				|| 'area' == $type
			)
		) {
			// Need to figure out how this correlates in manifest.
			// $chart_args['options']['scales']['y']['min'] = $this->post_meta['y_min_value'];
		}

		/**
		 * Filter a chart's display arguments.
		 *
		 * @hook paracharts_chart_args
		 *
		 * @param {array}  $chart_args Chart display arguments.
		 * @param {object} $post WP_Post object.
		 * @param {array}  $post_meta Post meta data.
		 * @param {array}  $args Raw display arguments.
		 */
		$chart_args = apply_filters( 'paracharts_chart_args', $chart_args, $this->post, $this->post_meta, $this->args );

		// Set the cache, we'll regenerate this when someone updates the post
		if ( $cache ) {
			wp_cache_set( $cache_key, $chart_args, paracharts()->slug );
		}

		// Clear out all of the class vars so the next chart instance starts fresh
		$this->args      = null;
		$this->post      = null;
		$this->post_meta = null;

		return $chart_args;
	}

	/**
	 * Hook to the paracharts_update_post_meta action and refresh the chart args cache
	 *
	 * @param int $post_id WP post ID of the post you want chart args for
	 * @param array $parsed_meta the parsed chart meta passed by the action hook
	 */
	public function paracharts_update_post_meta( $post_id, $parsed_meta ) {
		// Refresh arg cache
		$this->args = paracharts()->get_chart_default_args;
		$this->post = get_post( $post_id );

		$this->post_meta = $parsed_meta;

		$this->get_chart_args( $post_id, array(), true );
	}

	/**
	 * Hook to the paracharts_image_support filter and indicate that ParaCharts supports images
	 *
	 * @param string $supports_images yes/no whether the library supports image generation
	 * @param string $library the library in question
	 *
	 * @return string $supports_images yes/no whether the library supports image generation
	 */
	public function paracharts_image_support( $supports_images, $library ) {
		if ( $library != $this->library ) {
			return $supports_images;
		}

		return 'yes';
	}

	/**
	 * Returns the value labels array
	 *
	 * @return array an array of the value labels need for the active chart
	 */
	public function get_value_labels_array() {
		$value_labels = paracharts()->parse()->value_labels;

		if ( 'both' === $this->post_meta['parse_in'] ) {
			return $value_labels;
		}

		if ( isset( $value_labels['first_column'] ) ) {
			$label_key = 'rows' == $this->post_meta['parse_in'] ? 'first_row' : 'first_column';

			return $value_labels[ $label_key ];
		}

		return $value_labels;
	}

	/**
	 * Handle adding data sets to the chart args
	 * 
	 * @param array $labels Array of labels for data points.
	 *
	 * @return array the chart args array with data sets added to it
	 */
	public function get_data_sets( $labels ) {
		$records = array();
		if ( ! empty( $labels ) ) {
			$data_array  = array_map( array( $this, 'fix_null_values' ), paracharts()->parse()->set_data );
			$count       = count( $labels );
			$data_arrays = array_chunk( $data_array, $count, false );
			foreach ( $data_arrays as $array ) {
				foreach ( $array as $key => $point ) {
					$data_point = (object) array(
						'x' => $labels[ $key % $count ],
						'y' => $point,
					);
					$records[] = $data_point;
				}
			}
		}

		return $records;
	}

	/**
	 * Hook to the paracharts_iframe_scripts filter and add additional scripts if needed
	 *
	 * @param array $scripts an array of scripts needed for the iframe to render the chart
	 * @param int $post_id WP post ID of the chart being displayed
	 *
	 * @return array $scripts an array of scripts needed for the iframe to render the chart
	 */
	public function paracharts_iframe_scripts( $scripts, $post_id ) {

		$type = paracharts()->get_post_meta( $post_id, 'type' );

		// Return the scripts
		return $scripts;
	}

	/**
	 * Helper function escapes and modifies text/title values
	 *
	 * @param string a string you want to use in a chart.
	 *
	 * @return string the escaped and modified string
	 */
	public function esc_title( $string ) {
		$string = html_entity_decode( $string, ENT_QUOTES );

		$find = array(
			"\n",
			"\r",
			'<br><br>',
			'—',
			'–',
		);

		$replace = array(
			'<br />',
			'<br />',
			'<br />',
			'-',
			'-',
		);

		$string = str_replace( $find, $replace, $string );

		// @TODO: See if this addslashes/stripslashes is still necessary (need to remember why I did it first...)
		return addslashes( stripslashes( $string ) );
	}

	/**
	 * Helper function sets empty values to NULL so that Chart.js handles them correctly
	 *
	 * @param string/int a data value
	 *
	 * @return int/null the integer value or NULL if the value was not numeric
	 */
	public function fix_null_values( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( $this, 'fix_null_values' ), $value );
		}

		if ( ! is_numeric( $value ) ) {
			return null;
		}

		return $value;
	}
}
