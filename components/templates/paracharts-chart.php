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
<div id="paracharts-container-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="paracharts-container">
	<para-chart manifest="<?php echo esc_url( plugins_url( 'donut-manifest-dark-matter.json', __FILE__ ) ); ?>" id="paracharts-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>"></para-chart>
</div>
