<?php

class Paracharts_Admin {
	private $safe_settings = array(
		'performance' => array(
			'default',
			'no-images',
			'no-preview',
		),
		'csv_delimiter' => array(
			',',
			"\t",
			' ',
			';',
		),
	);
	private $plugin_url;

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->plugin_url = paracharts()->plugin_url();

		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'current_screen', array( $this, 'current_screen' ) );
		add_action( 'admin_footer', array( $this, 'admin_footer' ) );
		add_action( 'wp_ajax_paracharts_export_csv', array( $this, 'ajax_export_csv' ) );
		add_action( 'wp_ajax_paracharts_get_chart_args', array( $this, 'ajax_get_chart_args' ) );
		add_action( 'wp_ajax_paracharts_import_csv', array( $this, 'ajax_import_csv' ) );
		add_action( 'edit_form_before_permalink', array( $this, 'edit_form_before_permalink' ) );
		add_action( 'manage_' . paracharts()->slug . '_posts_custom_column', array( $this, 'manage_posts_custom_column' ), 10, 2 );

		add_filter( 'manage_' . paracharts()->slug . '_posts_columns', array( $this, 'manage_posts_columns' ) );
	}

	/**
	 * Add settings admin page
	 */
	public function admin_menu() {
		add_submenu_page(
			'edit.php?post_type=' . paracharts()->slug,
			esc_html__( 'ParaCharts Settings', 'paracharts' ),
			esc_html__( 'Settings', 'paracharts' ),
			'manage_options',
			'paracharts-settings',
			array( $this, 'paracharts_settings' )
		);
	}

	/**
	 * Display the ParaCharts settings admin page
	 */
	public function paracharts_settings() {
		$settings = paracharts()->get_settings();
		require_once __DIR__ . '/templates/paracharts-settings.php';
	}

	/**
	 * Check for and save ParaCharts settings
	 */
	public function save_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Check the nonce
		if (
			   ! isset( $_POST[ paracharts()->slug ] )
			|| ! wp_verify_nonce( $_POST[ paracharts()->slug ]['nonce'], paracharts()->slug . '-save-settings' )
		) {
			return;
		}

		$validated_settings = array();
		$submitted_settings = $_POST[ paracharts()->slug ];

		/**
		 * Filter ParaChart settings.
		 *
		 * @hook paracharts_default_settings
		 *
		 * @param {array} $settings Array of settings.
		 */
		$default_settings = apply_filters( 'paracharts_default_settings', paracharts()->settings );

		foreach ( $default_settings as $setting => $default ) {
			if ( ! isset( $submitted_settings[ $setting ] ) ) {
				$validated_settings[ $setting ] = $default;
				continue;
			}

			if ( isset( $this->safe_settings[ $setting ] ) ) {
				// If we've got an array of valid values lets check against that
				$safe_setting = $this->safe_settings[ $setting ];

				if ( in_array( $submitted_settings[ $setting ], $safe_setting, true ) ) {
					$validated_settings[ $setting ] = $submitted_settings[ $setting ];
				} else {
					$validated_settings[ $setting ] = $default;
				}
			} else {
				// Make sure the value is safe before attempting to save it
				if ( preg_match( '#^[a-zA-Z0-9-_]+$#', $submitted_settings[ $setting ] ) ) {
					$validated_settings[ $setting ] = $submitted_settings[ $setting ];
				} else {
					$validated_settings[ $setting ] = $default;
				}
			}
		}

		// Allow third party libraries to further validate the settings
		/**
		 * Filter the settings after validation.
		 *
		 * @hook paracharts_validated_settings
		 *
		 * @param {array} $validated_settings Array of settings after validation.
		 * @param {array} $submitted_settings Array of settings before validation.
		 */
		$validated_settings = apply_filters( 'paracharts_validated_settings', $validated_settings, $submitted_settings );

		update_option( paracharts()->slug, $validated_settings );

		// Make sure the embed endpoint makes it into the rewrite rules
		flush_rewrite_rules();

		add_action( 'admin_notices', array( $this, 'save_success' ) );
	}

	/**
	 * Display an admin notice that the settings have been saved
	 */
	public function save_success() {
		?>
<div class="updated notice notice-success">
	<p><?php esc_html_e( 'Settings saved', 'paracharts' ); ?></p>
</div>
		<?php
	}

	/**
	 * Load CSS/Javascript necessary for the interface
	 *
	 * @param object the current screen object as passed by the current_screen action hook
	 */
	public function current_screen( $screen ) {
		if ( paracharts()->slug != $screen->post_type ) {
			return;
		}

		$version = ( SCRIPT_DEBUG ) ? paracharts()->version . '-' . wp_rand( 1000, 9999 ) : paracharts()->version;
		// Only load these if we are on a post page
		if ( 'post' == $screen->base ) {
			// Handsontable
			wp_enqueue_style(
				'handsontable',
				$this->plugin_url . '/components/external/handsontable/handsontable.css',
				array(),
				$version
			);

			wp_enqueue_script(
				'handsontable',
				$this->plugin_url . '/components/external/handsontable/handsontable.js',
				array( 'jquery' ),
				$version
			);

			// Handlebars
			wp_enqueue_script(
				'handlebars',
				$this->plugin_url . '/components/external/handlebars/handlebars.js',
				array(),
				$version
			);

			// canvg is useful for SVG -> Canvas conversions
			wp_enqueue_script(
				'canvg',
				$this->plugin_url . '/components/external/canvg/umd.js',
				array(),
				$version
			);

			// Admin panel JS
			wp_enqueue_script(
				'paracharts-admin',
				$this->plugin_url . '/components/js/paracharts-admin.js',
				array( 'jquery', 'handsontable', 'handlebars' ),
				$version
			);

			// We need the post ID for some bunch of stuff below
			$post_id = isset( $_GET['post'] ) ? (int) $_GET['post'] : '';

			// Only load this if we are on an appropriate post page
			if ( 'post' == $screen->base && 'paracharts' === $screen->id ) {
				wp_enqueue_script(
					'paracharts-chart-admin',
					$this->plugin_url . '/components/js/paracharts-chart-admin.js',
					array( 'paracharts-admin', 'jquery', 'wpa11y' ),
					$version
				);
			}

			wp_localize_script(
				'paracharts-admin',
				'paracharts_admin',
				array(
					'refresh_counter'         => 0,
					'allow_form_submission'   => false,
					'request'                 => false,
					'performance'             => paracharts()->get_settings( 'performance' ),
					'image_support'           => apply_filters( 'paracharts_image_support', 'yes' ),
					'instant_preview_support' => apply_filters( 'paracharts_instant_preview_support', 'yes' ),
					'image_multiplier'        => paracharts()->get_settings( 'image_multiplier' ),
					'image_width'             => paracharts()->get_settings( 'image_width' ),
					'set_names'               => paracharts()->get_post_meta( $post_id, 'set_names' ),
					'delete_comfirm'          => esc_attr__( 'Are you sure you want to delete this spreadsheet?', 'paracharts' ),
				)
			);

			do_action( 'paracharts_admin_scripts', $post_id );
		}

		// Admin panel CSS
		wp_enqueue_style(
			'paracharts-admin',
			$this->plugin_url . '/components/css/paracharts-admin.css',
			array(),
			$version
		);
	}

	/**
	 * Add all of the metaboxes needed for the data and chart editing interface
	 */
	public function meta_boxes() {
		global $wp_meta_boxes;

		// Remove excerpt from it's normal spot in the meta_boxes array so we can put it back in after the spreadsheet
		// Users can move metaboxes, but this helps put things in a reasonable place on the first visit
		$excerpt = $wp_meta_boxes[ paracharts()->slug ]['normal']['core']['postexcerpt'];
		unset( $wp_meta_boxes[ paracharts()->slug ]['normal']['core']['postexcerpt'] );

		add_meta_box(
			paracharts()->slug . '-spreadsheet',
			esc_html__( 'Data', 'paracharts' ),
			array( $this, 'spreadsheet_meta_box' ),
			paracharts()->slug,
			'normal',
			'high'
		);

		add_meta_box(
			paracharts()->slug,
			esc_html__( 'Chart', 'paracharts' ),
			array( $this, 'chart_meta_box' ),
			paracharts()->slug,
			'normal',
			'high'
		);

		$wp_meta_boxes[ paracharts()->slug ]['normal']['high']['postexcerpt'] = $excerpt;

		// We are using our own interface for the units so we can remove the units taxonomy metabox
		remove_meta_box( paracharts()->slug . '-unitsdiv', paracharts()->slug, 'side' );
	}

	/**
	 * Displays the spread sheet meta box
	 *
	 * @param object the WP post object as returned by the metabox API
	 */
	public function spreadsheet_meta_box( $post ) {
		$post_meta = paracharts()->get_post_meta( $post->ID );

		// Setup default empty sheet data if needed
		$sheet_data = empty( $post_meta['data'] ) ? array( array( '' ) ) : $post_meta['data']['sets'];

		require_once __DIR__ . '/templates/spreadsheet-meta-box.php';
	}

	/**
	 * Displays the chart meta box
	 *
	 * @param object the WP post object as returned by the metabox API
	 */
	public function chart_meta_box( $post ) {
		// Force an instance of 1 since we NEVER show more than one chart at a time inside the admin panel
		paracharts()->instance = 1;

		$chart     = paracharts()->get_chart( $post->ID );
		$post_meta = paracharts()->get_post_meta( $post->ID );
		$image     = paracharts()->get_chart_image( $post->ID );
		$settings  = paracharts()->get_settings();

		require_once __DIR__ . '/templates/chart-meta-box.php';
	}

	/**
	 * Insert CSV Import and Export forms into the footer when editing charts
	 */
	public function admin_footer() {
		$screen = get_current_screen();

		if ( 'post' != $screen->base || paracharts()->slug != $screen->post_type ) {
			return;
		}
		?>
<form id="<?php echo esc_attr( $this->get_field_id( 'csv-import-form' ) ); ?>" style="display: none;">
	<input type="file" name="import_csv_file" id="<?php echo esc_attr( $this->get_field_id( 'csv-file' ) ); ?>"
		class="hide" />
</form>
<form action="<?php echo esc_url( admin_url( 'admin-ajax.php?action=paracharts_export_csv' ) ); ?>"
	id="<?php echo esc_attr( $this->get_field_id( 'csv-export-form' ) ); ?>" style="display: none;" method="post">
	<input type="hidden" name="post_id" value="" id="<?php echo esc_attr( $this->get_field_id( 'csv-post-id' ) ); ?>" />
	<input type="hidden" name="data" value="" id="<?php echo esc_attr( $this->get_field_id( 'csv-data' ) ); ?>" />
	<input type="hidden" name="title" value="" id="<?php echo esc_attr( $this->get_field_id( 'csv-title' ) ); ?>" />
	<input type="hidden" name="set_name" value=""
		id="<?php echo esc_attr( $this->get_field_id( 'csv-set-name' ) ); ?>" />
</form>
<script type="text/javascript">
		<?php do_action( 'paracharts_admin_footer_javascript' ); ?>
</script>
		<?php
	}

	/**
	 * Inserts a subtitle field under the title field on the chart edit form and includes the handlebars templates we'll need
	 *
	 * @param object the WP post object as returned by the metabox API
	 */
	public function edit_form_before_permalink( $post ) {
		if ( paracharts()->slug != $post->post_type ) {
			return;
		}

		$post_meta = paracharts()->get_post_meta( $post->ID );

		require_once __DIR__ . '/templates/subtitle-field.php';
		require_once __DIR__ . '/templates/handlebars.php';
	}

	/**
	 * Display some additional information about a chart
	 *
	 * @param string the name of the custom column being displayed
	 * @param string the $post_id of the post being displayed in this row
	 */
	public function manage_posts_custom_column( $column, $post_id ) {
		if ( paracharts()->slug . '-type' != $column && paracharts()->slug . '-library' != $column ) {
			return;
		}

		if ( paracharts()->slug . '-type' == $column ) {
			$type      = paracharts()->get_post_meta( $post_id, 'type' );
			$type_name = paracharts()->library( 'paracharts' )->type_option_names[ $type ];
			?>
<span class="type-name">
	<span class="type <?php echo esc_attr( $type ); ?>" aria-hidden="true"></span>
	<span class="type-label"><?php echo esc_html( $type_name ); ?></span>
</span>
			<?php
		}
	}

	/**
	 * Add our custom column to the array of columns for charts
	 *
	 * @param array the array of columns
	 *
	 * @return array array of columns with the custom column added
	 */
	public function manage_posts_columns( $columns ) {
		$new_columns = array();

		foreach ( $columns as $column => $name ) {
			$new_columns[ $column ] = $name;

			if ( 'author' == $column || 'coauthors' == $column ) {
				$new_columns[ paracharts()->slug . '-type' ] = 'Type';
			}
		}

		return $new_columns;
	}

	/**
	 * Hook to save_post action and save chart related post meta
	 *
	 * @param int the WP post ID of the post being saved
	 */
	public function save_post( $post_id ) {
		$post = get_post( $post_id );

		// Check that this isn't an autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check post type
		if ( ! isset( $post->post_type ) || paracharts()->slug != $post->post_type ) {
			return;
		}

		// Don't run on post revisions (almost always happens just before the real post is saved)
		if ( wp_is_post_revision( $post->ID ) ) {
			return;
		}

		// Make sure we've got some actual ParaCharts related data in the $_POST array
		if ( ! isset( $_POST[ paracharts()->slug ] ) ) {
			return;
		}

		// Check the nonce
		if ( ! wp_verify_nonce( $_POST[ paracharts()->slug ]['nonce'], paracharts()->slug . '-save-post' ) ) {
			return;
		}

		unset( $_POST[ paracharts()->slug ]['nonce'] );

		// Check the permissions
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		// If there's an image being passed attach it to the chart post
		$this->attach_image();

		// Load the library in question in case there's a filter/action we'll need
		paracharts()->library( 'paracharts' );

		// update_post_meta passes the $_POST values directly to validate_post_meta
		// validate_post_meta returns only valid post meta values and does data validation on each item
		paracharts()->update_post_meta( $post->ID, $_POST[ paracharts()->slug ] );
	}

	/**
	 * Attach a given image to a chart post
	 *
	 * @param int the WP post ID of the post being saved
	 * @param string a base64 encoded string of the image we want to attach
	 */
	public function attach_image() {
		$settings = paracharts()->get_settings();

		// If the performance setting isn't turned to default we don't do this
		if ( 'default' != $settings['performance'] ) {
			return;
		}

		if ( ! is_numeric( $_POST['post_ID'] ) ) {
			return;
		}

		$post_id = absint( $_POST['post_ID'] );

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		if ( ! $post = get_post( $post_id ) ) {
			return false;
		}

		if ( '' == $_POST[ paracharts()->slug ]['img'] ) {
			return false;
		}

		// Decode the image so we can save it
		$decoded_img = base64_decode( str_replace( 'data:image/png;base64,', '', $_POST[ paracharts()->slug ]['img'] ) );

		if ( '' == $decoded_img ) {
			return false;
		}

		// Check for an existing attached image
		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => 1,
				'post_parent'    => $post->ID,
				'meta_key'       => paracharts()->slug . '-image',
			)
		);

		// If an existing image was found delete it
		foreach ( $attachments as $attachment ) {
			wp_delete_attachment( $attachment->ID, true );
		}

		// Upload image to WP
		$file = wp_upload_bits( sanitize_title( $post->post_title . '-' . $post->ID ) . '.png', null, $decoded_img );

		// START acting like media_sideload_image
		preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $file['file'], $matches );

		$file_array['name']     = basename( $matches[0] );
		$file_array['tmp_name'] = $file['file'];

		if ( is_wp_error( $file ) ) {
			@unlink( $file_array['tmp_name'] );
			$file_array['tmp_name'] = '';
		}

		$img_id = media_handle_sideload( $file_array, $post->ID, $post->post_title );

		if ( is_wp_error( $img_id ) ) {
			@unlink( $file_array['tmp_name'] );
			return $img_id;
		}
		// STOP acting like media_sideload_image

		// Set some meta on the attachment so we know it came from paracharts
		add_post_meta( $img_id, paracharts()->slug . '-image', $post->ID );

		// Set the attachment as the chart's thumbnail
		update_post_meta( $post->ID, '_thumbnail_id', $img_id );
	}

	/**
	 * Parses an incoming CSV file and compiles it into an array
	 *
	 * @return array an array fo the data from the imported CSV file ready for use in the chart meta
	 */
	public function ajax_import_csv() {
		$post = get_post( absint( $_POST['post_id'] ) );

		// Check post type
		if ( ! isset( $post->post_type ) || paracharts()->slug != $post->post_type ) {
			wp_send_json_error( esc_html__( 'Wrong post type', 'paracharts' ) );
		}

		// Check the nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], paracharts()->slug . '-save-post' ) ) {
			wp_send_json_error( esc_html__( 'Invalid nonce', 'paracharts' ) );
		}

		// Check the permissions
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			wp_send_json_error( esc_html__( 'Wrong post type', 'paracharts' ) );
		}

		// Make sure there's a CSV file
		if ( empty( $_FILES ) || ! isset( $_FILES['import_csv_file']['name'] ) ) {
			wp_send_json_error( esc_html__( 'No file to import', 'paracharts' ) );
		}

		// Make sure the file is a CSV file
		$file_ext = strtolower( pathinfo( $_FILES['import_csv_file']['name'], PATHINFO_EXTENSION ) );

		if ( 'csv' != $file_ext ) {
			wp_send_json_error( esc_html__( 'Only CSV files can be imported ', 'paracharts' ) );
		}

		// Do some validation on the CSV file (mirroring what WP does for this sort of thing)
		$csv_file = realpath( $_FILES['import_csv_file']['tmp_name'] );

		if ( ! $csv_file ) {
			wp_send_json_error( esc_html__( 'File path not found', 'paracharts' ) );
		}

		$csv_data = file_get_contents( $csv_file );

		if ( '' == $csv_data ) {
			wp_send_json_error( esc_html__( 'CSV file was empty', 'paracharts' ) );
		}

		// Get parseCSV library so we can use it to convert the CSV to a nice array
		// Yes, PHP does this natively now but I've run into trouble with malformed CSV that parsCSV handles fine
		require_once __DIR__ . '/external/parsecsv/parsecsv.lib.php';

		$parse_csv = new parseCSV();

		// The "\n" before and after is to deal with CSV files that don't have line breaks above and below the data
		// Which then seems to confuse parseCSV occasionally
		$csv_data = "\n" . trim( $csv_data ) . "\n";

		// Set delimiter
		$parse_csv->delimiter = isset( $_POST['csv_delimiter'] ) ? $_POST['csv_delimiter'] : paracharts()->get_settings( 'csv_delimiter' );
		
		// Parse the CSV 
		$parse_csv->parse( $csv_data );

		// This deals with Google Doc's crappy CSV exports which don't include columns at the end of a row if they are empty
		$data_array = $this->fix_csv_data_array( $parse_csv->data );

		wp_send_json_success( $data_array );
	}

	/**
	 * Helper function makes sure that the data array has matching numbers of array elements for each row
	 * CSV from some sources (Google Docs) doesn't include columns that are empty when they are at the end of a row (Why Google? WHY?)
	 *
	 * @param array an array of data as returned from the parseCSV class
	 *
	 * @param array the array of data with matching array value counts
	 */
	public function fix_csv_data_array( $data_array ) {
		$count = 0;

		// Get largest row count
		foreach ( $data_array as $data ) {
			$temp_count = count( $data );

			$count = ( $temp_count > $count ) ? $temp_count : $count;
		}

		// Fix arrays so value counts match
		foreach ( $data_array as $key => $data ) {
			$temp_count = count( $data );

			if ( $temp_count < $count ) {
				$difference = $count - $temp_count;

				for ( $i = 0; $i < $difference; $i++ ) {
					$data_array[ $key ][] = '';
				}
			}
		}

		return $data_array;
	}

	/**
	 * Converts data array into CSV and outputs it to the browser
	 */
	public function ajax_export_csv() {
		// Purposely using $_REQUEST here since this method can work via a GET and POST request
		// POST requests are used when passing the data value since it's too big to pass via GET
		if ( ! is_numeric( $_REQUEST['post_id'] ) || ! current_user_can( 'edit_post', absint( $_REQUEST['post_id'] ) ) ) {
			wp_die( 'Unauthorized access', 'You do not have permission to do that', array( 'response' => 401 ) );
		}

		$post = get_post( absint( $_REQUEST['post_id'] ) );

		// If the user passed a data value in their request we'll use it after validation
		if ( isset( $_POST['data'] ) && isset( $_POST['title'] ) ) {
			$data      = paracharts()->validate_data( json_decode( stripslashes( $_POST['data'] ) ) );
			$file_name = sanitize_title( $_POST['title'] );
		} else {
			$data      = paracharts()->get_post_meta( $post->ID, 'data' );
			$file_name = sanitize_title( get_the_title( $post->ID ) );
		}

		$set_name = sanitize_title( $_REQUEST['set_name'] );

		if ( empty( $data ) ) {
			return;
		}

		require_once __DIR__ . '/external/parsecsv/parsecsv.lib.php';
		$parse_csv = new parseCSV();

		// Set delimiter
		$parse_csv->output_delimiter = paracharts()->get_settings( 'csv_delimiter' );

		$parse_csv->output( $file_name . '-' . $set_name . '.csv', $data );
		die;
	}

	/**
	 * Returns JSON encoded chart args from $_POST values sent from the admin panel
	 *
	 * @return string a JSON encoded string containing all of the chart args needed to update an active chart
	 */
	public function ajax_get_chart_args() {
		// Check the nonce
		if ( ! wp_verify_nonce( $_POST['nonce'], paracharts()->slug . '-save-post' ) ) {
			wp_send_json_error( esc_html__( 'Invalid nonce', 'paracharts' ) );
		}

		// Does the post exist?
		if ( ! $post = get_post( absint( $_POST['post_id'] ) ) ) {
			wp_send_json_error( esc_html__( 'Invalid post', 'paracharts' ) );
		}

		// Can the user edit this post?
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			wp_send_json_error( esc_html__( 'Permission error', 'paracharts' ) );
		}

		/**
		 * Filter the characteristics of the ParaCharts class.
		 *
		 * @hook paracharts_library_class
		 *
		 * @param {Paracharts} $library_class Paracharts object characteristics.
		 */
		$library = apply_filters( 'paracharts_library_class', paracharts()->library_class );

		// Set these values so that get_chart_args has them already available before we call it
		$library->args             = paracharts()->get_chart_default_args;
		$library->post             = $post;
		$library->post->post_title = sanitize_text_field( $_POST['title'] );

		// validate_post_meta returns only valid post meta values and does data validation on each item
		$library->post_meta = paracharts()->validate_post_meta( $_POST['post_meta'] );

		wp_send_json_success( $library->get_chart_args( $library->post->ID, $library->args, true, false ) );
	}

	/**
	 * Return a name spaced field name
	 *
	 * @param string the field name we want to name space
	 *
	 * @param string a name spaced field name
	 */
	public function get_field_name( $field_name, $parent_field_name = '' ) {
		if ( '' != $parent_field_name ) {
			return paracharts()->slug . '[' . $parent_field_name . ']' . '[' . $field_name . ']';
		}

		return paracharts()->slug . '[' . $field_name . ']';
	}

	/**
	 * Return a name spaced field id
	 *
	 * @param string the field id we want to name space
	 *
	 * @param string a name spaced field id
	 */
	public function get_field_id( $field_name ) {
		return paracharts()->slug . '-' . $field_name;
	}
}
