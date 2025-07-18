<div id="paracharts-settings-page" class="wrap">
	<h1><?php esc_html_e( 'ParaCharts Settings', 'paracharts' ); ?></h1>
	<form method="post">
		<?php wp_nonce_field( paracharts()->slug . '-save-settings', $this->get_field_name( 'nonce' ) ); ?>
		<table class="form-table" role="presentation">
			<tbody>
				<tr>
					<th scope="row"><?php esc_html_e( 'Performance', 'paracharts' ); ?></th>
					<td>
						<fieldset>
							<legend class="screen-reader-text">
								<span><?php esc_html_e( 'Performance', 'paracharts' ); ?></span>
							</legend>
							<ul>
								<li>	
									<input type="radio" id="performance_default" aria-describedby="performance_description" name="<?php echo esc_attr( $this->get_field_name( 'performance' ) ); ?>" value="default"<?php checked( $settings['performance'], 'default' ); ?> />
									<label for="performance_default"><?php esc_html_e( 'Default', 'paracharts' ); ?></label><br />
									<span class="description" id="performance_description"><?php esc_html_e( 'Provides all functionality', 'paracharts' ); ?></span>
								</li>
								<li>
									<input type="radio" id="performance_noimage" aria-describedby="performance_noimage_description" name="<?php echo esc_attr( $this->get_field_name( 'performance' ) ); ?>" value="no-images"<?php checked( $settings['performance'], 'no-images' ); ?> />
									<label for="performance_noimage"><?php esc_html_e( 'No Images', 'paracharts' ); ?></label><br />
									<span class="description" id="performance_noimage_description"><?php esc_html_e( 'No generation of chart images', 'paracharts' ); ?></span>
								</li>
								<li>
									<input type="radio" id="performance_nopreview" aria-describedby="performance_nopreview_description" name="<?php echo esc_attr( $this->get_field_name( 'performance' ) ); ?>" value="no-preview"<?php checked( $settings['performance'], 'no-preview' ); ?> />
									<label for="performance_nopreview"><?php esc_html_e( 'No Instant Preview', 'paracharts' ); ?></label><br />
									<span class="description" id="performance_nopreview_description"><?php esc_html_e( 'No instant preview and no generation of chart images', 'paracharts' ); ?></span>
								</li>
							</ul>
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
							<ul>
								<li>
									<input type="radio" aria-describedby="image_multiplier_description" id="image_multiplier_1" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="1"<?php checked( $settings['image_multiplier'], '1' ); ?> />
									<label for="image_multiplier_1"><?php esc_html_e( '1x', 'paracharts' ); ?></label><br />
								</li>
								<li>
									<input type="radio" aria-describedby="image_multiplier_description" id="image_multiplier_2" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="2"<?php checked( $settings['image_multiplier'], '2' ); ?> />
									<label for="image_multiplier_1"><?php esc_html_e( '2x', 'paracharts' ); ?></label><br />
								</li>
								<li>
									<input type="radio" aria-describedby="image_multiplier_description" id="image_multiplier_3" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="3"<?php checked( $settings['image_multiplier'], '3' ); ?> />
									<label for="image_multiplier_1"><?php esc_html_e( '3x', 'paracharts' ); ?></label><br />
								</li>
								<li>
									<input type="radio" aria-describedby="image_multiplier_description" id="image_multiplier_4" name="<?php echo esc_attr( $this->get_field_name( 'image_multiplier' ) ); ?>" value="4"<?php checked( $settings['image_multiplier'], '4' ); ?> />
									<label for="image_multiplier_1"><?php esc_html_e( '4x', 'paracharts' ); ?></label><br />
									<span class="description" id="image_multiplier_description"><?php esc_html_e( 'The higher the multiplier the better the images will look on high DPI screens', 'paracharts' ); ?></span>
								</li>
							</ul>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="image_width"><?php esc_html_e( 'Image Width', 'paracharts' ); ?></label></th>
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
						
							<input id="embeds" type="checkbox" aria-describedby="embeds_description" name="<?php echo esc_attr( $this->get_field_name( 'embeds' ) ); ?>" value="enabled"<?php checked( $settings['embeds'], 'enabled' ); ?> />
							<label for="embeds"><?php esc_html_e( 'Enable iframe embeds', 'paracharts' ); ?></label><br />
							<span class="description" id="embeds_description"><?php esc_html_e( 'Allow charts to be remotely embedded via iframes', 'paracharts' ); ?></span>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr( $this->get_field_id( 'csv-delimiter' ) ); ?>"><?php esc_html_e( 'Default CSV Delimiter', 'paracharts' ); ?></label></th>
					<td>
						<select aria-describedby="csv_delimiter_description" name="<?php echo esc_attr( $this->get_field_name( 'csv_delimiter' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'csv-delimiter' ) ); ?>">
							<?php
							foreach ( paracharts()->csv_delimiters as $delimiter => $delimiter_name ) {
								?>
								<option value="<?php echo esc_attr( $delimiter ); ?>"<?php selected( $delimiter, $settings['csv_delimiter'] ); ?>>
									<?php esc_html( $delimiter_name ); ?>
								</option>
								<?php
							}
							?>
						</select>
						<span class="description" id="csv_delimiter_description"><?php esc_html_e( 'Default used when importing/exporting CSV files', 'paracharts' ); ?></span>
					</td>
				</tr>
			</tbody>
		</table>
		<p class="submit">
			<input type="submit" name="submit" id="submit" class="button button-primary" value="<?php echo esc_attr_e( 'Save Changes', 'paracharts' ); ?>">
		</p>
	</form>
</div>