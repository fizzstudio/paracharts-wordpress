<?php
$parse_option_names = array(
	'columns' => esc_html__( 'Columns', 'paracharts' ),
	'rows'    => esc_html__( 'Rows', 'paracharts' ),
);

$y_min_disabled = $post_meta['y_min'] ? '' : 'disabled="disabled" ';
?>
<div class="settings">
	<div class="row one">
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>"><?php esc_html_e( 'Type', 'paracharts' ); ?></label><br />
			<select name='<?php echo esc_attr( $this->get_field_name( 'type' ) ); ?>' class='select' id="<?php echo esc_attr( $this->get_field_id( 'type' ) ); ?>">
				<?php
				foreach ( paracharts()->library( 'paracharts' )->type_options as $type ) {
					?>
					<option value="<?php echo esc_attr( $type ); ?>"<?php selected( $type, $post_meta['type'] ); ?>>
						<?php echo esc_html( paracharts()->library( 'paracharts' )->type_option_names[ $type ] ); ?>
					</option>
					<?php
				}
				?>
			</select>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>"><?php esc_html_e( 'Height', 'paracharts' ); ?></label><br />
			<input type="number" name="<?php echo esc_attr( $this->get_field_name( 'height' ) ); ?>" value="<?php echo absint( $post_meta['height'] ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'height' ) ); ?>" min="300" max="1500" />
		</p>
	</div>
	<div class="row two">
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'parse_in' ) ); ?>"><?php esc_html_e( 'Parse data in', 'paracharts' ); ?></label><br />
			<select name='<?php echo esc_attr( $this->get_field_name( 'parse_in' ) ); ?>' class='select' id="<?php echo esc_attr( $this->get_field_id( 'parse_in' ) ); ?>">
				<?php
				foreach ( paracharts()->parse_options as $parse_in ) {
					?>
					<option value="<?php echo esc_attr( $parse_in ); ?>"<?php selected( $parse_in, $post_meta['parse_in'] ); ?>>
						<?php echo esc_html( $parse_option_names[ $parse_in ] ); ?>
					</option>
					<?php
				}
				?>
			</select>
		</p>
		<p class="labels">
			&nbsp;<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'labels' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'labels' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'labels' ) ); ?>" value="1"<?php checked( $post_meta['labels'], true ); ?>/>
				<?php esc_html_e( 'Show labels', 'paracharts' ); ?>
			</label>
		</p>
		<p class="legend">
			&nbsp;<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'legend' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'legend' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'legend' ) ); ?>" value="1"<?php checked( $post_meta['legend'], true ); ?>/>
				<?php esc_html_e( 'Show legend', 'paracharts' ); ?>
			</label>
		</p>
		<p class="shared">&nbsp;<br />
			<label for="<?php echo esc_attr( $this->get_field_id( 'shared' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'shared' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'shared' ) ); ?>" value="1"<?php checked( $post_meta['shared'], true ); ?>/>
				<?php esc_html_e( 'Shared tooltip', 'paracharts' ); ?>
			</label>
		</p>
	</div>
	<div class="row three vertical-axis">
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'y-title' ) ); ?>"><?php esc_html_e( 'Vertical axis title', 'paracharts' ); ?></label><br />
			<input class="input" type="text" name="<?php echo esc_attr( $this->get_field_name( 'y_title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'y-title' ) ); ?>" value="<?php echo esc_attr( $post_meta['y_title'] ); ?>" style="width: 100%;" />
		</p>
		<p class="units">
			<label for="<?php echo esc_attr( $this->get_field_id( 'y-units' ) ); ?>"><?php esc_html_e( 'Units', 'paracharts' ); ?></label><br />
			<select name='<?php echo esc_attr( $this->get_field_name( 'y_units' ) ); ?>' id="<?php echo esc_attr( $this->get_field_id( 'y-units' ) ); ?>" class='select'>
				<option value=""><?php esc_html_e( 'N/A', 'paracharts' ); ?></option>
				<?php
				foreach ( paracharts()->get_unit_terms() as $parent => $units ) {
					?>
					<option value="" disabled="disabled">
						<?php echo esc_html( $parent ); ?>
					</option>
					<?php

					foreach ( $units as $unit ) {
						?>
						<option value="<?php echo esc_attr( $unit->name ); ?>"<?php selected( $unit->name, $post_meta['y_units'] ); ?>>
							<?php echo esc_html( $unit->name ); ?>
						</option>
						<?php
					}
				}
				?>
			</select>
		</p>
	</div>
	<div class="row four y-min">
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'y-min' ) ); ?>">
				<input type="checkbox" name="<?php echo esc_attr( $this->get_field_name( 'y_min' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'y-min' ) ); ?>" value="1"<?php checked( $post_meta['y_min'], true ); ?>/>
				<?php esc_html_e( 'Force vertical axis minimum: ', 'paracharts' ) ?>
			</label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'y_min_value' ) ); ?>" value="<?php echo floatval( $post_meta['y_min_value'] ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'y-min-value' ) ); ?>" size="7" <?php echo esc_html( $y_min_disabled ); ?>/>
		</p>
	</div>
	<div class="row five horizontal-axis">
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'x-title' ) ); ?>"><?php esc_html_e( 'Horizontal axis title', 'paracharts' ); ?></label><br />
			<input class="input" type="text" name="<?php echo esc_attr( $this->get_field_name( 'x_title' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'x-title' ) ); ?>" value="<?php echo esc_attr( $post_meta['x_title'] ); ?>" style="width: 100%;" />
		</p>
		<p class="units">
			<label for="<?php echo esc_attr( $this->get_field_id( 'x-units' ) ); ?>"><?php esc_html_e( 'Units', 'paracharts' ); ?></label><br />
			<select name='<?php echo esc_attr( $this->get_field_name( 'x_units' ) ); ?>' id="<?php echo esc_attr( $this->get_field_id( 'x-units' ) ); ?>" class='select'>
				<option value=""><?php esc_html_e( 'N/A', 'paracharts' ) ?></option>
				<?php
				foreach ( paracharts()->get_unit_terms() as $parent => $units ) {
					?>
					<option value="" disabled="disabled">
						<?php echo esc_html( $parent ); ?>
					</option>
					<?php

					foreach ( $units as $unit ) {
						?>
						<option value="<?php echo esc_attr( $unit->name ); ?>"<?php selected( $unit->name, $post_meta['x_units'] ); ?>>
							<?php echo esc_html( $unit->name ); ?>
						</option>
						<?php
					}
				}
				?>
			</select>
		</p>
	</div>
</div>
<div class="row seven">
	<p>
		<label for="<?php echo esc_attr( $this->get_field_id( 'shortcode' ) ); ?>"><?php esc_html_e( 'Shortcode', 'paracharts' ); ?></label><br />
		<input class="input" type="text" name="<?php echo esc_attr( $this->get_field_name( 'shortcode' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'shortcode' ) ); ?>" value='[chart id="<?php echo absint( $post->ID ); ?>"]' style="width: 100%;" readonly="readonly" />
	</p>
	<p class="image">
		<label for="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>"><?php esc_html_e( 'Image', 'paracharts' ); ?></label><br />
		<?php
		if ( $image ) {
			?>
			<input class="input" type="text" name="<?php echo esc_attr( $this->get_field_name( 'image' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'image' ) ); ?>" value="<?php echo esc_url( $image['url'] ); ?>" style="width: 100%;" readonly="readonly" />
			<a href="<?php echo esc_url( $image['url'] ); ?>" class="button" target="_blank"><?php esc_html_e( 'View', 'paracharts' ); ?></a>
			<?php
		} elseif ( 'default' != $settings['performance'] ) {
			?><em><?php esc_html_e( 'Image generation is disabled', 'paracharts' ); ?></em><?php
		} else {
			?><em><?php esc_html_e( 'Save/Update this post to generate the image version', 'paracharts' ); ?></em><?php
		}
		?>
	</p>
</div>