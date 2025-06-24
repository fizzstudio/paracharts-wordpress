<script type="text/javascript" charset="utf-8">
	var hands_on_table_data = <?php echo json_encode( $sheet_data ); ?>;
</script>
<nav id="hands-on-table-sheet-tabs" class="nav-tab-wrapper hide">
	<a href="#add-sheet" class="add-sheet" title="<?php esc_html_e( 'Add Sheet', 'paracharts' ); ?>"><span class="dashicons dashicons-plus-alt"></span></a>
</nav>
<div id="hands-on-table-sheets"></div>
<div id="paracharts-csv">
	<div class="export">
		<br />
		<a href="#export-csv" title="<?php esc_attr_e( 'Export CSV', 'paracharts' ); ?>" class="button"><?php esc_html_e( 'Export', 'paracharts' ); ?></a>
	</div>
	<div class="import">
		<?php esc_html_e( 'CSV Import/Export', 'paracharts' ); ?><br />
		<div class="controls">
			<a href="#select-csv" title="<?php esc_attr_e( 'Select CSV File', 'paracharts' ); ?>" class="button select"><?php esc_html_e( 'Select File', 'paracharts' ); ?></a>
			<div class="confirmation hide">
				<a href="#import-csv" title="<?php esc_attr_e( 'Import', 'paracharts' ); ?>" class="button"><?php esc_html_e( 'Import', 'paracharts' ); ?></a>
				<select name="<?php echo esc_attr( $this->get_field_name( 'csv_delimiter' ) ); ?>">
					<?php
					$csv_delimiter = paracharts()->get_settings( 'csv_delimiter' );
				
					foreach ( paracharts()->csv_delimiters as $delimiter => $delimiter_name ) {
						?>
						<option value="<?php echo esc_attr( $delimiter ); ?>"<?php selected( $delimiter, $csv_delimiter ); ?>>
							<?php esc_html_e( $delimiter_name . ' Delimited', 'paracharts' ); ?>
						</option>
						<?php
					}
					?>
				</select>
			</div>
			<p class="file error hide"><?php esc_html_e( 'You can only import CSV files', 'paracharts' ); ?></p>
			<p class="import error hide"></p>
			<p class="import in-progress hide"><?php esc_html_e( 'Importing file', 'paracharts' ); ?></p>
			<div class="file-info hide">
				<a href="#cancel" title="<?php esc_attr_e( 'Cancel Import', 'paracharts' ); ?>" class="dashicons dashicons-dismiss"></a>
				File: <span class="file-name"></span><br />
				<span class="warning"><?php esc_html_e( 'Importing this file will replace all existing data in this sheet', 'paracharts' ); ?></span>
			</div>
		</div>
	</div>
</div>
<textarea name="<?php echo esc_attr( $this->get_field_name( 'data' ) ); ?>" rows="8" cols="40" class="data hide"></textarea>
<?php
wp_nonce_field( paracharts()->slug . '-save-post', $this->get_field_name( 'nonce' ) );