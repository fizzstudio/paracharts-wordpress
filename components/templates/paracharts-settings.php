<div id="paracharts-settings-page" class="wrap">
	<h1><?php esc_html_e( 'ParaCharts Settings', 'paracharts' ); ?></h1>
	<form method="post">
		<?php wp_nonce_field( paracharts()->slug . '-save-settings', $this->get_field_name( 'nonce' ) ); ?>
		<h2><?php esc_html_e( 'General Settings', 'paracharts' ); ?></h2>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'show_library' ) ); ?>" value="yes"<?php checked( $settings['show_library'], 'yes' ); ?> />
							<span><?php esc_html_e( 'Show Library in Edit Posts Screen', 'paracharts' ); ?></span><br />
							<span class="description"><?php esc_html_e( 'Displays an icon indicating the library used for a chart in Edit Posts Screen of the WP Admin', 'paracharts' ); ?></span>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Performance', 'paracharts' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php esc_html_e( 'Performance', 'paracharts' ); ?></span>
							</legend>
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'performance' ) ); ?>" value="default"<?php checked( $settings['performance'], 'default' ); ?> />
								<span><?php esc_html_e( 'Default', 'paracharts' ); ?></span><br />
								<span class="description"><?php esc_html_e( 'Provides all functionality', 'paracharts' ); ?></span>
							</label><br />
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'performance' ) ); ?>" value="no-images"<?php checked( $settings['performance'], 'no-images' ); ?> />
								<span><?php esc_html_e( 'No Images', 'paracharts' ); ?></span><br />
								<span class="description"><?php esc_html_e( 'No generation of chart images', 'paracharts' ); ?></span>
							</label><br />
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'performance' ) ); ?>" value="no-preview"<?php checked( $settings['performance'], 'no-preview' ); ?> />
								<span><?php esc_html_e( 'No Instant Preview', 'paracharts' ); ?></span><br />
								<span class="description"><?php esc_html_e( 'No instant preview and no generation of chart images', 'paracharts' ); ?></span>
							</label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Image Multiplier', 'paracharts' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php esc_html_e( 'Image Multiplier', 'paracharts' ); ?></span>
							</legend>
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="1"<?php checked( $settings['image_multiplier'], '1' ); ?> />
								<span><?php esc_html_e( '1x', 'paracharts' ); ?></span><br />
							</label><br />
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="2"<?php checked( $settings['image_multiplier'], '2' ); ?> />
								<span><?php esc_html_e( '2x', 'paracharts' ); ?></span><br />
							</label><br />
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="3"<?php checked( $settings['image_multiplier'], '3' ); ?> />
								<span><?php esc_html_e( '3x', 'paracharts' ); ?></span><br />
							</label><br />
							<label>
								<input type="radio" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="4"<?php checked( $settings['image_multiplier'], '4' ); ?> />
								<span><?php esc_html_e( '4x', 'paracharts' ); ?></span><br />
							</label><br />
							<span class="description"><?php esc_html_e( 'The higher the multiplier the better the images will look on high DPI screens', 'paracharts' ); ?></span>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><lable for="image_width"><?php esc_html_e( 'Image Width', 'paracharts' ); ?></label></th>
					<td>
						<input type="number" id="image_width" aria-describedby="image_width_description" name="<?php echo esc_attr( paracharts()->admin()->get_field_name( 'image_width' ) ); ?>" value="<?php echo absint( $settings['image_width'] ); ?>" />
						<p class="description" id="image_width_description">
							<?php esc_html_e( 'The width of the image generated from your chart', 'paracharts' ); ?>
						</p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Embeds', 'paracharts' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'embeds' ) ); ?>" value="enabled"<?php checked( $settings['embeds'], 'enabled' ); ?> />
							<span><?php esc_html_e( 'Enable iframe embeds', 'paracharts' ); ?></span><br />
							<span class="description"><?php esc_html_e( 'Allow charts to be remotely embedded via iframes', 'paracharts' ); ?></span>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Default CSV Delimiter', 'paracharts' ); ?></th>
					<td>
						<select name="<?php echo esc_attr( $this->get_field_name( 'csv_delimiter' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'csv-delimiter' ) ); ?>">
							<?php
							foreach ( paracharts()->csv_delimiters as $delimiter => $delimiter_name ) {
								?>
								<option value="<?php echo esc_attr( $delimiter ); ?>"<?php selected( $delimiter, $settings['csv_delimiter'] ); ?>>
									<?php esc_html_e( $delimiter_name, 'paracharts' ); ?>
								</option>
								<?php
							}
							?>
						</select>
						<span class="description"><?php esc_html_e( 'Default used when importing/exporting CSV files', 'paracharts' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr_e( 'Save Changes', 'paracharts' ); ?>">
		</p>
	</form>
</div>