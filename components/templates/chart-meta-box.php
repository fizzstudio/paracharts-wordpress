<?php
// Show what the graph looks like
echo $chart;
?>
<textarea name="<?php echo esc_attr( $this->get_field_name( 'img' ) ); ?>" rows="8" cols="40" id="<?php echo esc_attr( $this->get_field_id( 'img' ) ); ?>" class="hide"></textarea>
<?php

/**
 * Filter chart settings template.
 *
 * @hook paracharts_settings_template
 *
 * @param {string} $template Path to settings template.
 */
require apply_filters( 'paracharts_settings_template', __DIR__ . '/chart-settings.php' );