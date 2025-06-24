<?php
// Show what the graph looks like
echo $chart;
?>
<canvas id="paracharts-canvas-render-<?php echo absint( $post->ID ); ?>" class="hide"></canvas>
<textarea name="<?php echo esc_attr( $this->get_field_name( 'img' ) ); ?>" rows="8" cols="40" id="<?php echo esc_attr( $this->get_field_id( 'img' ) ); ?>" class="hide"></textarea>
<?php

// Load the various settings and controls this chart's library
require apply_filters( 'paracharts_settings_template', __DIR__ . '/chart-settings.php' );