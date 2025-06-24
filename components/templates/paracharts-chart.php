<?php
$title  = get_the_title( $post_id ); 
$height = paracharts()->get_post_meta( $post_id, 'height' );
$width  = '';

$subtitle = paracharts()->get_post_meta( $post_id, 'subtitle' );

if ( '' != $subtitle ) {
	$title = $title . ': ' . $subtitle;
}

if ( '' != $args['width'] && 'responsive' != $args['width'] ) {
	$width = ' width="' . absint( $args['width'] ) . '"';
}
?>
<div id="paracharts-container-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="paracharts-container chartjs">
	<canvas id="paracharts-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="paracharts" height="<?php echo absint( $height ); ?>"<?php echo $width; ?> aria-label="<?php echo esc_attr( $title ); ?>" role="img" style="height: <?php echo esc_attr( $height ); ?>px;"></canvas>
</div>
<script type="text/javascript">
	var paracharts_container_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>_canvas = document.getElementById( 'paracharts-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>' ).getContext('2d');

	var paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?> = {
		chart_args: <?php echo $this->unicode_aware_stripslashes( json_encode( $this->library( 'chartjs' )->get_chart_args( $post_id, $args ), JSON_HEX_QUOT ) ); ?>,
		post_id: <?php echo absint( $post_id ); ?>,
		instance: <?php echo absint( $this->instance ); ?>,
		render_1: true
	};

	<?php do_action( 'paracharts_after_chart_args', $post_id, $args, $this->instance ); ?>

	(function( $ ) {
		paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>.render_chart = function() {
			$( '.paracharts' ).trigger({
				type:     'render_start',
				post_id:  this.post_id,
				instance: this.instance
			});
			
			var target = this.chart_args.options.animation;
            
			var source = {
				onComplete: function() {
					// This deals with an issue in Chart.js 3.1.0 where onComplete can run too many times
					// We only want to trigger on the first render anyway so we'll just check
					if ( false === paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>.render_1 ) {
						return;
					}

					paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>.render_1 = false;

					$( '.paracharts' ).trigger({
						type:     'render_done',
						post_id:  paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>.post_id,
						instance: paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>.instance
					});
				}
			}
           
			if ( ! target ) {
				source = {animation: source};
				target = this.chart_args.options;
			}
			
			Object.assign( target, source );

			Chart.register( ChartDataLabels );

			this.chart = new Chart(
				paracharts_container_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>_canvas,
				this.chart_args
			);
		};

		$( function() {
			$.when( paracharts_helpers.init() ).done(function() {
				paracharts_<?php echo absint( $post_id ); ?>_<?php echo absint( $this->instance ); ?>.render_chart();
			});
		} );
	})( jQuery );
</script>
