<?php
$title    = get_the_title( $post_id ); 
$subtitle = paracharts()->get_post_meta( $post_id, 'subtitle' );

if ( '' != $subtitle ) {
	$title = $title . ': ' . $subtitle;
}
$dataset = paracharts()->paracharts_library_class()->get_chart_args( $post_id, array() );

echo '<script id="paracharts-data-' . absint( $post_id ) . '-' . absint( $this->instance ) . '" type="application/json">' . json_encode( $dataset, JSON_PRETTY_PRINT ) . '</script>';

?>
<div id="paracharts-container-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>" class="paracharts-container">
	<para-chart manifest="<?php echo esc_url( plugins_url( 'donut-manifest-dark-matter.json', __FILE__ ) ); ?>" id="paracharts-<?php echo absint( $post_id ); ?>-<?php echo absint( $this->instance ); ?>"></para-chart>
</div>
