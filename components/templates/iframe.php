<?php
// Make sure we instantiate the library so any library specific filters/setup get run
$this->library( 'paracharts' );
?>
<!doctype html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?php echo esc_html( get_the_title( $post->ID ) ); ?></title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_print_scripts( apply_filters( 'paracharts_iframe_scripts', $scripts, $post->ID ) ); ?>
    </head>
	<!-- overflow: hidden; prevents the iframe from scrolling -->
    <body style="overflow: hidden;">
		<?php echo $this->get_chart( $post->ID, map_deep( $_GET, 'sanitize_text_field' ) ); ?>
    </body>
</html>