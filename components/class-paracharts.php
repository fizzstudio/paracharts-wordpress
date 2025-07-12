<?php

class Paracharts {
	public $version           = '0.1.0';
	public $slug              = 'paracharts';
	public $plugin_name       = 'Chart';
	public $chart_meta_fields = array(
		'library'      => 'paracharts',
		'type'         => 'line',
		'parse_in'     => 'rows',
		'shared'       => false,
		'subtitle'     => '',
		'y_title'      => '',
		'y_units'      => '',
		'y_min'        => false,
		'controlpanel' => true,
		'y_min_value'  => 0,
		'x_title'      => '',
		'x_units'      => '',
		'aspect'       => 1,
		'source'       => '',
		'source_url'   => '',
		'data'         => array(),
		'set_names'    => array(),
	);
	public $get_chart_default_args = array(
		'show'  => 'chart',
		'width' => 'responsive',
		'share' => '',
	);
	public $parse_options = array(
		'columns',
		'rows',
	);
	public $options_set;
	public $plugin_url;
	public $is_iframe    = false;
	public $instance     = 1;
	public $settings     = array(
		'library'          => 'paracharts',
		'performance'      => 'default',
		'image_multiplier' => '2',
		'image_width'      => '600',
		'embeds'           => '',
		'default_theme'    => '_default',
		'locale'           => 'en-US',
		'csv_delimiter'    => ',',
		'lang_settings'    => array(
			'decimalPoint'   => '.',
			'thousandsSep'   => ',',
			'numericSymbols' => array(
				'K', // Thousands
				'M', // Millions
				'B', // Billions
				'T', // Trillions
				'P', // Quadrillions
				'E', // Quintillions
			),
			'numericSymbolMagnitude' => 1000,
		),
	);
	public $csv_delimiters = array(
		','  => 'Comma',
		"\t" => 'Tab',
		' '  => 'Space',
		';'  => 'Semicolon',
	);
	public $library_class;

	private $admin;
	private $parse;
	private $block;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_url = $this->plugin_url();

		add_action( 'init', array( $this, 'init' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
		add_action( 'shortcode_ui_before_do_shortcode', array( $this, 'shortcode_ui_before_do_shortcode' ) );
		// Doing this early as possible because it sets is_iframe which we might need to use for other things
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 0 );
		add_action( 'paracharts_update_post_meta', array( $this, 'paracharts_update_post_meta' ), 10, 2 );

		// Doing this before the default so it's already done before anything else
		add_filter( 'paracharts_get_chart_image_tag', array( $this, 'paracharts_get_chart_image_tag' ), 9, 3 );
		add_filter( 'the_content', array( $this, 'the_content' ) );
		add_filter( 'paracharts_image_support', array( $this, 'paracharts_image_support' ), 10, 2 );
		add_filter( 'paracharts_instant_preview_support', array( $this, 'paracharts_instant_preview_support' ), 10, 2 );
		add_filter( 'paracharts_library_class', array( $this, 'paracharts_library_class' ), 10, 2 );

		add_shortcode( 'chart', array( $this, 'chart_shortcode' ) );
		add_shortcode( 'chart-share', array( $this, 'share_shortcode' ) );

		// Initiate the block class
		$this->block();
	}

	/**
	 * Admin object accessor
	 */
	public function admin() {
		if ( ! $this->admin ) {
			require_once __DIR__ . '/class-paracharts-admin.php';
			$this->admin = new Paracharts_Admin();
		}

		return $this->admin;
	}
	
	/**
	 * Block object accessor
	 */
	public function block() {
		if ( ! $this->block ) {
			require_once __DIR__ . '/class-paracharts-block.php';
			$this->block = new Paracharts_Block();
		}

		return $this->block;
	}

	/**
	 * Library object accessor
	 */
	public function library() {
		/**
		 * Filter the characteristics of the ParaCharts class.
		 *
		 * @hook paracharts_library_class
		 *
		 * @param {Paracharts} $library_class Paracharts object characteristics.
		 */
		return apply_filters( 'paracharts_library_class', $this->library_class );
	}

	/**
	 * Parse object accessor
	 */
	public function parse() {
		if ( ! $this->parse ) {
			require_once __DIR__ . '/class-paracharts-parse.php';
			$this->parse = new Paracharts_Parse();
		}

		return $this->parse;
	}

	/**
	 * Do init stuff
	 */
	public function init() {
		// Register the units taxonomy
		register_taxonomy(
			$this->slug . '-units',
			array( $this->slug ),
			array(
				'hierarchical' => true,
				'labels'       => array(
					'name'              => esc_html__( 'Chart Units', 'paracharts' ),
					'singular_name'     => esc_html__( 'Chart Unit', 'paracharts' ),
					'search_items'      => esc_html__( 'Search Chart Units', 'paracharts' ),
					'all_items'         => esc_html__( 'All Chart Units', 'paracharts' ),
					'parent_item'       => esc_html__( 'Parent Chart Unit', 'paracharts' ),
					'parent_item_colon' => esc_html__( 'Parent Chart Unit:', 'paracharts' ),
					'edit_item'         => esc_html__( 'Edit Chart Unit', 'paracharts' ),
					'update_item'       => esc_html__( 'Update Chart Unit', 'paracharts' ),
					'add_new_item'      => esc_html__( 'Add New Chart Unit', 'paracharts' ),
					'new_item_name'     => esc_html__( 'Chart Unit Name', 'paracharts' ),
					'menu_name'         => esc_html__( 'Chart Units', 'paracharts' ),
				),
				'show_ui'   => true,
				'query_var' => true,
				'rewrite'   => array(
					'slug' => $this->slug . '-units',
				),
			)
		);

		// Register the library taxonomy
		register_taxonomy(
			$this->slug . '-library',
			array( $this->slug ),
			array(
				'hierarchical' => false,
				'show_ui'      => false,
				'query_var'    => true,
				'rewrite'      => array(
					'slug' => $this->slug . '-library',
				),
			)
		);

		// Register the charts custom post type
		register_post_type(
			$this->slug,
			array(
				'labels' => array(
					'name'               => esc_html__( 'Charts', 'paracharts' ),
					'singular_name'      => esc_html__( 'Chart', 'paracharts' ),
					'add_new'            => esc_html__( 'Add Chart', 'paracharts' ),
					'add_new_item'       => esc_html__( 'Add Chart', 'paracharts' ),
					'edit'               => esc_html__( 'Edit', 'paracharts' ),
					'edit_item'          => esc_html__( 'Edit Chart', 'paracharts' ),
					'new_item'           => esc_html__( 'New Chart', 'paracharts' ),
					'view'               => esc_html__( 'View', 'paracharts' ),
					'view_item'          => esc_html__( 'View Chart', 'paracharts' ),
					'search_items'       => esc_html__( 'Search Charts', 'paracharts' ),
					'not_found'          => esc_html__( 'No Charts found', 'paracharts' ),
					'not_found_in_trash' => esc_html__( 'No Charts found in the Trash', 'paracharts' ),
				),
				'register_meta_box_cb' => is_admin() ? array( $this->admin(), 'meta_boxes' ) : null,
				'public'               => true,
				'show_in_rest'         => true,
				'hierarchical'         => false,
				'exclude_from_search'  => false,
				'menu_position'        => 9,
				'menu_icon'            => 'dashicons-chart-pie',
				'query_var'            => true,
				'can_export'           => true,
				'has_archive'          => 'charts',
				'description'          => esc_html__( 'Manage data sets and display them as charts in WordPress.', 'paracharts' ),
				'rewrite'              => array(
					'slug' => 'chart',
				),
				'supports' => array(
					'author',
					'title',
					'excerpt',
					'comments',
				),
				'taxonomies' => array(
					'category',
					'post_tag',
					$this->slug . '-units',
				),
			)
		);

		// Register the ParaCharts module.
		wp_register_script_module(
			'paracharts',
			$this->plugin_url . '/components/js/paracharts.min.js',
			array(),
			$this->version
		);

		wp_enqueue_script_module( 'paracharts' );

		// Add endpoint needed for iframe embed support
		add_rewrite_endpoint( 'embed', EP_PERMALINK );
	}

	/**
	 * Do plugins loaded stuff
	 */
	public function plugins_loaded() {
		load_plugin_textdomain( 'paracharts', false, plugin_basename( dirname( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * VIP's CDN was breaking Highcharts ability to handle embedded SVGs so this should circumvent that
	 * If you wanted to say, watermark your charts, SVGs suddenly become very important
	 *
	 * @param string $path option additional path to be used (e.g. components)
	 *
	 * @return string URL to the plugin directory with path if parameter was passed
	 */
	public function plugin_url( $path = '' ) {
		if ( is_admin() ) {
			$url_base = parse_url( admin_url() );
		} else {
			$url_base = parse_url( home_url() );
		}

		$url_path = parse_url( plugins_url( $path, __DIR__ ) );

		// Check for a port value if one exists we make sure it's honored
		$port = '';

		if ( isset( $url_base['port'] ) && 80 != $url_base['port'] ) {
			$port = ':' . $url_base['port'];
		}

		return $url_base['scheme'] . '://' . $url_base['host'] . $port . preg_replace( '#/$#', '', $url_path['path'] ) . ( empty( $url_path['query'] ) ? '' : '?' . $url_path['query'] );
	}

	/**
	 * Get chart post meta
	 *
	 * @param int $post_id WP post ID of the post you want post meta from
	 * @param string $field optional field to be returned instead of all post meta
	 *
	 * @return string|array Array of chart post meta or single value.
	 */
	public function get_post_meta( $post_id, $field = false ) {
		$raw_post_meta = get_post_meta( $post_id, $this->slug, true );
		$post_meta     = $raw_post_meta;
		$defaults      = $this->chart_meta_fields;
		$post_meta     = wp_parse_args( $post_meta, $defaults );

		// Theme default is based off of an option so we'll handle that here
		if ( ! isset( $post_meta['theme'] ) ) {
			$settings           = $this->get_settings();
			$post_meta['theme'] = $settings['default_theme'];
		}

		// If there's no subtitle set we'll set an empty one
		if ( ! isset( $post_meta['subtitle'] ) ) {
			$post_meta['subtitle'] = '';
		}

		// If there's no y min value set we'll set it to 0
		if ( ! isset( $post_meta['y_min_value'] ) ) {
			$post_meta['y_min_value'] = 0;
		}

		// If there's no set_names value set we'll set it to an empty array
		if ( ! isset( $post_meta['set_names'] ) ) {
			$post_meta['set_names'] = array();
		}
		/**
		 * Filter the post meta data for a chart.
		 *
		 * @hook paracharts_get_post_meta
		 *
		 * @param {array} $post_meta Array of all post meta data with default values filled.
		 * @param {array} $raw_post_meta Array of all raw post meta data.
		 * @param {int}   $post_id Post ID.
		 */
		$post_meta = apply_filters( 'paracharts_get_post_meta', $post_meta, $raw_post_meta, $post_id );

		if ( $field && isset( $post_meta[ $field ] ) ) {
			return $post_meta[ $field ];
		} elseif ( $field ) {
			return null;
		}

		return $post_meta;
	}

	/**
	 * Update the post meta based and set unit terms if appropriate
	 *
	 * @param int $post_id WP post ID of the post you want to attach post meta to
	 * @param array $meta an array of the post meta you want to attach to the post
	 */
	public function update_post_meta( $post_id, $meta ) {
		// Make sure the meta is formatted correctly and validated
		$parsed_meta = $this->validate_post_meta( $meta );

		// Set unit terms
		$terms = array();

		if ( '' != $parsed_meta['y_units'] ) {
			$terms[] = $parsed_meta['y_units'];
		}

		if ( '' != $parsed_meta['x_units'] ) {
			$terms[] = $parsed_meta['x_units'];
		}

		wp_set_object_terms( $post_id, $terms, $this->slug . '-units' );

		// Save meta to the post
		update_post_meta( $post_id, $this->slug, $parsed_meta );
		do_action( 'paracharts_update_post_meta', $post_id, $parsed_meta, $meta );
	}

	/**
	 * Parses a $meta array and returns a cleaned and validated array
	 *
	 * @param array $meta an array of the post meta you want to attach to the post
	 *
	 * @return array of cleaned/validated post meta
	 */
	public function validate_post_meta( $meta ) {
		// Need to set checkboxes before checking or they can't be deselected
		$chart_meta['controlpanel'] = true;
		$chart_meta['y_min']        = false;

		// Filter values so we know the data is clean
		foreach ( $this->chart_meta_fields as $field => $default ) {
			if ( isset( $meta[ $field ] ) ) {
				if ( 'source_url' == $field && '' != $meta[ $field ] ) {
					$chart_meta[ $field ] = esc_url_raw( $meta[ $field ] );
				} elseif ( 'data' == $field ) {
					$chart_meta[ $field ]['sets'] = $meta[ $field ];
				} elseif ( 'set_names' == $field ) {
					$chart_meta[ $field ] = array_values( $meta[ $field ] );
				} elseif ( in_array( $field, array( 'controlpanel', 'y_min' ) ) ) {
					$chart_meta[ $field ] = (bool) $meta[ $field ];
				} elseif ( 'aspect' == $field ) {
					$chart_meta[ $field ] = ( is_numeric( $meta[ $field ] ) ) ? (float) $meta[ $field ] : 1;
				} elseif ( 'y_min_value' == $field ) {
					$chart_meta[ $field ] = floatval( $meta[ $field ] );
				} else {
					$chart_meta[ $field ] = wp_filter_nohtml_kses( $meta[ $field ] );
				}
			} elseif ( ! isset( $chart_meta[ $field ] ) ) {
				// Fall back on the default value if there wasn't one in the given meta
				$chart_meta[ $field ] = $default;
			}
		}

		// The theme meta it isn't included in the chart_meta_fields class var so we handle it here
		if ( isset( $meta['theme'] ) && preg_match( '#^[a-zA-Z0-9-_]+$#', $meta['theme'] ) ) {
			$chart_meta['theme'] = $meta['theme'];
		}

		// If the data value is not an array we asume it is JSON encoded (i.e. from Handsontable)
		if ( ! is_array( $chart_meta['data']['sets'] ) && '' != $chart_meta['data']['sets'] ) {
			$chart_meta['data']['sets'] = json_decode( stripslashes( $chart_meta['data']['sets'] ) );
		}

		// Validate the data array
		foreach ( $chart_meta['data']['sets'] as $key => $data ) {
			$chart_meta['data'][ $key ] = $this->validate_data( $data );
		}

		/**
		 * Filter post meta after validation.
		 *
		 * @hook paracharts_validate_post_meta
		 *
		 * @param {array} $chart_meta Validated chart meta data.
		 * @param {array} $meta Unvalidated meta data.
		 */
		$chart_meta = apply_filters( 'paracharts_validate_post_meta', $chart_meta, $meta );

		return $chart_meta;
	}

	/**
	 * Runs all of the raw data array values through wp_filter_nohtml_kses
	 *
	 * @param array $data an array of data as passed by the user
	 *
	 * @return array of validated data
	 */
	function validate_data( $data ) {
		if ( ! is_array( $data ) ) {
			return wp_filter_nohtml_kses( $data );
		}

		foreach ( $data as $key => $value ) {
			if ( is_array( $value ) ) {
				$data[ $key ] = $this->validate_data( $value );
			} else {
				$value        = $value ?? '';
				$data[ $key ] = wp_filter_nohtml_kses( $value );
			}
		}

		return $data;
	}


	/**
	 * Hook to save_post action and save chart related post meta
	 *
	 * @param int $post_id WP post ID of the post
	 */
	public function save_post( $post_id ) {
		// We do this in the main class because otherwise it won't get hooked soon enough
		$this->admin()->save_post( $post_id );
	}

	/**
	 * Returns a chart
	 *
	 * @param int $post_id WP post ID of the chart you want
	 * @param array $args an array of args
	 *
	 * @return string HTML and Javascript needed to display a chart (or if appropriate an HTML image tag)
	 */
	public function get_chart( $post_id = null, $args = array() ) {
		if ( ! $post_id ) {
			$post_id = get_the_ID();
		}

		do_action( 'paracharts_get_chart_start', $post_id, $args );

		// Normalize historic usage of 'img' argument
		if ( isset( $args['img'] ) && 'yes' == $args['img'] ) {
			$args['show'] = 'image';
			unset( $args['img'] );
		}

		$args = wp_parse_args( $args, $this->get_chart_default_args );

		// Make sure we isntantiate the library so any library specific filters/setup get run
		$this->library( 'paracharts' );

		// If they want the table of data we'll return that
		if ( 'table' == $args['show'] ) {
			return $this->build_table( $post_id );
		}

		// If they want the image version or the request is happening from a feed we return the image tag
		if ( 'image' == $args['show']
			|| is_feed()
			|| $this->is_amp_endpoint()
			|| apply_filters( 'paracharts_show_image', false, $post_id, $args )
		) {
			$image = $this->get_chart_image( $post_id );

			// Default behavior is to return the full size image but with the width/height values halved
			// This should result in an image that looks nice on retina or better screens
			$image['width']  = $image['width'] / 2;
			$image['height'] = $image['height'] / 2;

			$image = apply_filters( 'paracharts_get_chart_image_tag', $image, $post_id, $args );

			$classes = $this->slug . ' ' . $this->slug . '-' . $post_id;

			if ( $this->is_amp_endpoint() ) {
				ob_start();
				?><amp-img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['name'] ); ?>"
	width="<?php echo absint( $image['width'] ); ?>" height="<?php echo absint( $image['height'] ); ?>"
	class="<?php echo esc_attr( $classes ); ?>"></amp-img>
				<?php
				return ob_get_clean();
			} else {
				ob_start();
				?>
<img src="<?php echo esc_url( $image['url'] ); ?>" alt="<?php echo esc_attr( $image['name'] ); ?>"
	width="<?php echo absint( $image['width'] ); ?>" height="<?php echo absint( $image['height'] ); ?>"
	alt="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" class="<?php echo esc_attr( $classes ); ?>" />
				<?php
				return ob_get_clean();
			}
		}

		$settings = $this->get_settings();

		if (
			   ! is_admin()
			&& 'enabled' == $settings['embeds']
			&& ! $this->is_iframe
		) {
			return $this->get_chart_iframe( $post_id, $args );
		}

		wp_enqueue_script( 'paracharts' );

		$template = __DIR__ . '/templates/paracharts-chart.php';

		ob_start();
		/**
		 * Execute action before displaying a chart.
		 *
		 * @hook paracharts_get_chart_begin
		 *
		 * @param {int} $post_id Post ID.
		 * @param {array} $args Array of display arguments.
		 */
		do_action( 'paracharts_get_chart_begin', $post_id, $args );
		/**
		 * Filter the ParaCharts chart template file.
		 *
		 * @hook paracharts_chart_template
		 *
		 * @param {string} $template Path to display template.
		 */
		require apply_filters( 'paracharts_chart_template', $template, $post_id );
		/**
		 * Execute action after displaying a chart.
		 *
		 * @hook paracharts_get_chart_end
		 *
		 * @param {int} $post_id Post ID.
		 * @param {array} $args Array of display arguments.
		 */
		do_action( 'paracharts_get_chart_end', $post_id, $args );
		$this->instance++;
		return ob_get_clean();
	}

	/**
	 * Returns a charts data as an HTML table
	 *
	 * @param int $post_id WP post ID of the chart you want
	 *
	 * @return string HTML table
	 */
	public function build_table( $post_id ) {
		$post      = get_post( $post_id );
		$post_meta = $this->get_post_meta( $post_id );

		$table = '';

		foreach ( $post_meta['data']['sets'] as $set => $data ) {

			paracharts()->parse()->parse_data( $data, $post_meta['parse_in'] );

			$template = __DIR__ . '/templates/table.php';

			ob_start();
			/**
			 * Filter the template used for ParaChart table output.
			 *
			 * @hook paracharts_table_template
			 *
			 * @param {string} $template Path to template.
			 * @param {int}    $post_ID Post ID.
			 */
			require apply_filters( 'paracharts_table_template', $template, $post->ID );
			$table .= ob_get_clean();
		}

		return $table;
	}

	/**
	 * Return an array of image values for a chart
	 *
	 * @param int $post_id WP post ID of the chart you want an image for
	 *
	 * @return array an array of image values url, width, height, etc...
	 */
	public function get_chart_image( $post_id ) {
		$settings = $this->get_settings();

		// If we aren't generating images we'll return false
		if ( 'default' != $settings['performance'] ) {
			return false;
		}

		if ( ! $thumbnail_id = get_post_meta( $post_id, '_thumbnail_id', true ) ) {
			return false;
		}

		if ( ! $thumbnail = wp_get_attachment_image_src( $thumbnail_id, 'full' ) ) {
			return false;
		}

		return array(
			'url'    => $thumbnail[0],
			'file'   => basename( $thumbnail[0] ),
			'width'  => $thumbnail[1],
			'height' => $thumbnail[2],
			'name'   => get_the_title( $thumbnail_id ),
		);
	}

	/**
	 * Filter the paracharts_get_chart_image_tag hook and return a plaecholder if appropriate
	 *
	 * @param array|bool an array of image values or false if no image could be found
	 * @param int $post_id WP post ID of the chart you want an image for
	 *
	 * @return array an array of image values url, width, height, etc...
	 */
	public function paracharts_get_chart_image_tag( $img, $post_id ) {
		if ( $img ) {
			return $img;
		}

		$url = $this->plugin_url . '/components/images/chart-placeholder.png';

		return array(
			'url'    => $url,
			'file'   => basename( $url ),
			'width'  => 640,
			'height' => 480,
			'name'   => get_the_title( $post_id ),
		);
	}

	/**
	 * Filter the the_content hook and return chart code if this is a chart
	 *
	 * @param string $content content from the current post
	 *
	 * @return string original content or chart code
	 */
	public function the_content( $content ) {
		// Make sure we're dealing with a chart
		if ( get_post_type() != $this->slug ) {
			// We aren't dealing with a chart so we'll just stop here
			return $content;
		}

		// Call the get_chart method and let it do it's thing
		return $this->get_chart();
	}

	/**
	 * Hook to the paracharts_image_support filter and indicate that Chart.js supports images
	 *
	 * @param string $supports_images yes/no whether the library supports image generation
	 *
	 * @return string yes/no whether the library supports images
	 */
	public function paracharts_image_support( $supports_images ) {
		return $supports_images;
	}

	/**
	 * Hook to the paracharts_instant_preview_support filter and indicate that Chart.js supports instant previews
	 *
	 * @param string $supports_images yes/no whether the library supports instant previews
	 *
	 * @return string yes/no whether the library supports instant previews
	 */
	public function paracharts_instant_preview_support( $supports_instant_preview ) {
		return $supports_instant_preview;
	}

	/**
	 * Hook to the paracharts_library_class filter and return the library class if appropriate
	 *
	 * @return class the library class for this library
	 */
	public function paracharts_library_class() {

		if ( ! $this->library_class instanceof ParachartsJs ) {
			require_once __DIR__ . '/class-parachartsjs.php';
			$this->library_class = new ParachartsJs();
		}

		return $this->library_class;
	}

	/**
	 * Return an iframe for a given chart
	 *
	 * @param int $post_id WP post ID of the chart you want
	 * @param array $args an array of args
	 *
	 * @return string HTML needed to display a chart via an iframe
	 */
	public function get_chart_iframe( $post_id, $args = array() ) {
		$src_url = add_query_arg( $args, get_permalink( $post_id ) . 'embed/' );

		ob_start();
		?>
<iframe title="<?php echo esc_attr( get_the_title( $post_id ) ); ?>" id="paracharts-container-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="paracharts-iframe" width="100%" height="600" src="<?php echo esc_url_raw( $src_url ); ?>" frameborder="0"></iframe>
		<?php
		if ( 'show' == $args['share'] ) {
			unset( $args['share'] );
			/**
			 * Filter template used to display chart share info.
			 *
			 * @hook paracharts_share_template
			 *
			 * @param {int} $template Path to share template.
			 */
			require apply_filters( 'paracharts_share_template', __DIR__ . '/templates/share.php' );
		}
		$this->instance++;
		return ob_get_clean();
	}

	/**
	 * Return a chart via the [chart id="x"] short code
	 *
	 * @param array $args an array of arguments passed by the WP short code API
	 *
	 * @return string the chart requested in Javascript or HTML form
	 */
	public function chart_shortcode( $args ) {
		$default_args       = $this->get_chart_default_args;
		$default_args['id'] = '';

		$args = shortcode_atts( $default_args, $args );

		$post_id = $args['id'];
		unset( $args['id'] );

		// Did we get a chart ID?
		if ( ! is_numeric( $post_id ) ) {
			return;
		}

		// Make sure the chart actually exists and that it's a chart so we don't fatal later
		if ( $this->slug != get_post_type( $post_id ) ) {
			return;
		}

		// Is it published?
		if ( 'publish' != get_post_status( $post_id ) ) {
			return;
		}

		return $this->get_chart( $post_id, $args );
	}

	/**
	 * Helper function that prevents issues with stripslashes and unicode characters
	 * stripslashes will strip off the slash before unicode characters which is sucky, this prevents that
	 *
	 * @param string $string a string that may have unicode characters as well as unecessary escaping
	 *
	 * @return string a string with any unecessary escaping removed
	 */
	public function unicode_aware_stripslashes( $string ) {
		return stripslashes( preg_replace( '#\\\u[a-fA-F0-9]{4}#', '\\\\$0', $string ) );
	}

	/**
	 * Helper function that returns the chart unit terms
	 *
	 * @return array an array of generated and/or compiled unit terms
	 */
	public function get_unit_terms() {
		$terms = get_terms( $this->slug . '-units', array( 'hide_empty' => false ) );

		if ( empty( $terms ) ) {
			$terms = $this->generate_unit_terms();
		}

		return $this->compile_unit_terms( $terms );
	}

	/**
	 * Helper function that returns the chart unit terms
	 *
	 * @param array an array of unit terms
	 *
	 * @return array an array of compiled unit terms
	 */
	public function compile_unit_terms( $terms ) {
		$compiled_terms = array();
		$parents        = array();

		foreach ( $terms as $unit ) {
			if ( 0 == $unit->parent ) {
				$parents[ $unit->term_id ] = $unit->name;
			}
		}

		foreach ( $terms as $unit ) {
			if ( 0 != $unit->parent && isset( $parents[ $unit->parent ] ) ) {
				$compiled_terms[ $parents[ $unit->parent ] ][] = $unit;
			}
		}

		ksort( $compiled_terms );

		return $compiled_terms;
	}

	/**
	 * Helper function that populates the unit terms taxonomy with a default set of terms
	 *
	 * @return array an array of the newly generated unit terms
	 */
	public function generate_unit_terms() {
		// Load the default terms array
		$default_terms = require __DIR__ . '/array-default-unit-terms.php';

		$terms = array();

		foreach ( $default_terms as $parent_term => $child_terms ) {
			$parent  = (object) wp_insert_term( $parent_term, $this->slug . '-units' );
			$terms[] = get_term( $parent->term_id, $this->slug . '-units' );

			foreach ( $child_terms as $child_term ) {
				$term    = (object) wp_insert_term( $child_term, $this->slug . '-units', array( 'parent' => $parent->term_id ) );
				$terms[] = get_term( $term->term_id, $this->slug . '-units' );
			}
		}

		return $terms;
	}

	/**
	 * Looks for the embed endpoint and serves up the requested chart if appropriate
	 */
	public function template_redirect() {
		global $wp_query;

		// Make sure this is a chart with the embed endpoint in the URL
		if ( ! isset( $wp_query->query['post_type'] ) || ! isset( $wp_query->query['embed'] ) || 'paracharts' != $wp_query->query['post_type'] ) {
			return;
		}

		$post = get_post();

		if ( ! $post ) {
			wp_die(
				esc_html__( 'The chart could not be found', 'paracharts' ),
				esc_html__( 'Chart not found', 'paracharts' ),
				array( 'response' => 404 )
			);
		}

		$settings = $this->get_settings();

		if ( 'enabled' != $settings['embeds'] ) {
			wp_die(
				esc_html__( 'Embeds of this type are not enabled', 'paracharts' ),
				esc_html__( 'Embeds disabled', 'paracharts' ),
				array( 'response' => 403 )
			);
			exit;
		}

		$this->is_iframe = true;

		unset( $_GET['action'], $_GET['share'] );

		// This prevents issues when embedding with outside sites
		header_remove( 'X-Frame-Options' );

		status_header( 200 );

		require __DIR__ . '/templates/iframe.php';
		exit;
	}

	/**
	 * Hook to the paracharts_update_post_meta action and call the required library specific function
	 *
	 * @param int $post_id WP post ID of the post you want chart args for
	 * @param array $parsed_meta the parsed chart meta passed by the action hook
	 */
	public function paracharts_update_post_meta( $post_id, $parsed_meta ) {
		$this->library( 'paracharts' )->paracharts_update_post_meta( $post_id, $parsed_meta );
	}

	/**
	 * If current page is an AMP page returns true
	 *
	 * @return bool
	 */
	public function is_amp_endpoint() {
		if ( function_exists( 'is_amp_endpoint' ) && is_amp_endpoint() ) {
			return true;
		}

		return false;
	}

	/**
	 * Return the ParaCharts settings
	 *
	 * @return array current settings
	 */
	public function get_settings( $setting = false ) {
		// Allow third party libraries to modify the default settings
		/**
		 * Filter ParaCharts default settings.
		 *
		 * @hook paracharts_default_settings
		 *
		 * @param {array} $settings Array of all default settings.
		 */
		$default_settings = apply_filters( 'paracharts_default_settings', $this->settings );

		$settings = (array) get_option( $this->slug, $default_settings );
		$settings = wp_parse_args( $settings, $default_settings );

		// Make sure the lang_settings aren't missing anything we'll be expecting later on
		$settings['lang_settings'] = wp_parse_args( $settings['lang_settings'], $this->settings['lang_settings'] );

		/**
		 * Filter ParaCharts settings.
		 *
		 * @hook paracharts_get_settings
		 *
		 * @param {array} $settings Array of saved ParaCharts settings.
		 */
		$settings = apply_filters( 'paracharts_get_settings', $settings );

		if ( $setting && isset( $settings[ $setting ] ) ) {
			return $settings[ $setting ];
		} elseif ( $setting ) {
			return null;
		}

		return $settings;
	}

	/**
	 * Return the locale array
	 *
	 * @return array locales as used by Intl.NumberFormat
	 */
	public function get_locales() {
		return require __DIR__ . '/array-locale-codes.php';
	}

	/**
	 * Return a recursively merged array out of the two given
	 *
	 * @param array a multi dimensional array that will be merged into
	 * @param array a multi dimensional array that will be merged recursively into the first one
	 *
	 * @return array the merged array
	 */
	public function array_merge_recursive( &$a, $b ) {
		foreach ( $b as $child => $value ) {
			if ( isset( $a[ $child ] ) ) {
				// New value exists so we'll need to move a level down
				if ( is_array( $a[ $child ] ) && is_array( $value ) ) {
					$this->array_merge_recursive( $a[ $child ], $value );
				} else {
					// New value is not an array so we override the old value with the new one
					$a[ $child ] = $value;
				}
			} else {
				// New value doesn't exist so we can just add it
				$a[ $child ] = $value;
			}
		}

		return $a;
	}
}

/**
 * Plugin object accessor
 */
function paracharts() {
	global $paracharts;

	if ( ! $paracharts instanceof Paracharts ) {
		$paracharts = new Paracharts();
	}

	return $paracharts;
}
