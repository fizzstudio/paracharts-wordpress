<table class="<?php echo esc_attr( $classes ); ?>">
	<?php
	$set_name = '';

	if ( $multiple ) {
		$set_name = ': ' . $post_meta['set_names'][ $set ];
	}

	if ( isset( paracharts()->parse()->value_labels['first_row'] ) ) {
		$first_row = paracharts()->parse()->value_labels['first_row'];
		$labels    = paracharts()->parse()->value_labels['first_column'];

		$row_column = false;

		if ( count( $first_row ) == count( paracharts()->parse()->set_data[0] ) ) {
			$row_column = true;
		}
		?>
		<tr><th colspan="<?php echo count( $first_row ) + 1; ?>"><?php echo get_the_title( $post_id ) . $set_name; ?></th></tr>
		<tr>
			<th></th>
			<?php
			foreach ( $first_row as $label ) {
				?>
				<th><?php echo esc_html( $label ); ?></th>
				<?php
			}
			?>
		</tr>
		<?php
		foreach ( $labels as $row => $label ) {
			?>
			<tr>
				<th><?php echo esc_html( $label ); ?></th>
				<?php
				foreach ( $first_row as $column => $label ) {
					if ( $row_column ) {
						$value = paracharts()->parse()->set_data[ $row ][ $column ];
					} else {
						$value = paracharts()->parse()->set_data[ $column ][ $row ];
					}

					if ( is_numeric( $value ) ) {
						$value = number_format_i18n( $value, strlen( substr( strrchr( $value, '.'), 1 ) ) );
						$value = '' != $value ? paracharts()->parse()->data_prefix . $value . paracharts()->parse()->data_suffix : '';
					}
					?>
					<td><?php echo esc_html( $value ); ?></td>
					<?php
				}
				?>
			</tr>
			<?php
		}
	} else {
		$first_row = paracharts()->parse()->value_labels;
		?>
		<tr><th colspan="<?php echo count( $first_row ); ?>"><?php echo get_the_title( $post_id ) . $set_name; ?></th></tr>
		<tr>
			<?php
			foreach ( $first_row as $label ) {
				?>
				<th><?php echo esc_html( $label ); ?></th>
				<?php
			}
			?>
		</tr>
		<tr>
			<?php
			$row_count = 1;
			$total_rows = count( paracharts()->parse()->set_data ) / count( $first_row );

			foreach ( paracharts()->parse()->set_data as $key => $value ) {
				if ( is_numeric( $value ) ) {
					$value = number_format_i18n( $value, strlen( substr( strrchr( $value, '.'), 1 ) ) );
					$value = '' != $value ? paracharts()->parse()->data_prefix . $value . paracharts()->parse()->data_suffix : '';
				}
				?>
				<td><?php echo esc_html( $value ); ?></td>
				<?php

				if ( ( $key + 1 ) / ( count( $first_row ) * $row_count ) == 1 ) {
					$row_count++;

					if ( $row_count <= $total_rows ) {
						?>
						</tr><tr>
						<?php
					}
				}
			}
			?>
		</tr>
	<?php
	}
	?>
</table>