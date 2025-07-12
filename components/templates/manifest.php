<?php
// Make sure we instantiate the library so any library specific filters/setup get run
$this->library( 'paracharts' );
$post_id = get_the_ID();
$dataset = paracharts()->paracharts_library_class()->get_chart_args( $post_id, array() );

echo wp_json_encode( $dataset );