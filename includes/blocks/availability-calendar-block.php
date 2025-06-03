<?php
/**
 * Registers the Availability Calendar Gutenberg block.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Availability Calendar block.
 */
function vitapro_appointments_register_availability_calendar_block() {
    register_block_type( 'vitapro-appointments/availability-calendar', array(
        'attributes' => array(
            'serviceId' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'professionalId' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'showLegend' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
        ),
        'render_callback' => 'vitapro_appointments_render_availability_calendar_block',
    ) );
}
add_action( 'init', 'vitapro_appointments_register_availability_calendar_block' );

/**
 * Render the Availability Calendar block.
 */
function vitapro_appointments_render_availability_calendar_block( $attributes, $content ) {
    $block_id = 'vitapro-availability-calendar-' . wp_generate_uuid4();
    
    ob_start();
    ?>
    <div id="<?php echo esc_attr( $block_id ); ?>" class="vitapro-availability-calendar-wrapper">
        <div class="vpa-calendar-container">
            <div class="vpa-calendar-header">
                <button type="button" class="vpa-calendar-prev">&lt;</button>
                <span class="vpa-calendar-month-year"></span>
                <button type="button" class="vpa-calendar-next">&gt;</button>
            </div>
            <div class="vpa-calendar-grid">
                <div class="vpa-calendar-weekdays">
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Sun', 'vitapro-appointments-fse' ); ?></div>
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Mon', 'vitapro-appointments-fse' ); ?></div>
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Tue', 'vitapro-appointments-fse' ); ?></div>
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Wed', 'vitapro-appointments-fse' ); ?></div>
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Thu', 'vitapro-appointments-fse' ); ?></div>
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Fri', 'vitapro-appointments-fse' ); ?></div>
                    <div class="vpa-calendar-weekday"><?php esc_html_e( 'Sat', 'vitapro-appointments-fse' ); ?></div>
                </div>
                <div class="vpa-calendar-days"></div>
            </div>
        </div>

        <?php if ( $attributes['showLegend'] ) : ?>
        <div class="vpa-calendar-legend">
            <div class="vpa-legend-item">
                <span class="vpa-legend-color vpa-available"></span>
                <span class="vpa-legend-text"><?php esc_html_e( 'Available', 'vitapro-appointments-fse' ); ?></span>
            </div>
            <div class="vpa-legend-item">
                <span class="vpa-legend-color vpa-busy"></span>
                <span class="vpa-legend-text"><?php esc_html_e( 'Busy', 'vitapro-appointments-fse' ); ?></span>
            </div>
            <div class="vpa-legend-item">
                <span class="vpa-legend-color vpa-unavailable"></span>
                <span class="vpa-legend-text"><?php esc_html_e( 'Unavailable', 'vitapro-appointments-fse' ); ?></span>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}