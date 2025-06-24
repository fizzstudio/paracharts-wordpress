<?php
if ( 1 == $this->instance ) {
	?>
	<script type="text/javascript">
	var paracharts_share = {};

	(function( $ ) {
		'use strict';

		paracharts_share.init = function() {
			$( '.paracharts-share' ).on( 'click', function () {
				$( this ).select();
			});
		};

		$( function() {
			paracharts_share.init();
		} );
	})( jQuery );
	</script>
	<?php
}
?>
<label for="paracharts-share-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>"><?php echo esc_html__( 'Share:', 'paracharts' ); ?></label><textarea rows="3" id="paracharts-share-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="paracharts-share"><?php echo $this->get_chart_iframe( $post_id, $args ); ?></textarea>