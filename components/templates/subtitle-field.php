<div class="subtitlewrap">
	<label for="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" class="screen-reader-text">Add Chart Subtitle</label>
	<input class="input" type="text" name="<?php echo esc_attr( $this->get_field_name( 'subtitle' ) ); ?>" id="<?php echo esc_attr( $this->get_field_id( 'subtitle' ) ); ?>" value="<?php echo esc_attr( $post_meta['subtitle'] ); ?>" placeholder="<?php esc_attr_e( 'Add subtitle', 'paracharts' ); ?>" style="width: 100%;" />
</div>