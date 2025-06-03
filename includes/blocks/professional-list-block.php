<?php
/**
 * Registers the Professional List Gutenberg block.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Professional List block.
 */
function vitapro_appointments_register_professional_list_block() {
    register_block_type( 'vitapro-appointments/professional-list', array(
        'attributes' => array(
            'layout' => array(
                'type'    => 'string',
                'default' => 'grid',
            ),
            'columns' => array(
                'type'    => 'number',
                'default' => 3,
            ),
            'showBio' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showServices' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showBookingButton' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'bookingPageUrl' => array(
                'type'    => 'string',
                'default' => '',
            ),
        ),
        'render_callback' => 'vitapro_appointments_render_professional_list_block',
    ) );
}
add_action( 'init', 'vitapro_appointments_register_professional_list_block' );

/**
 * Render the Professional List block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function vitapro_appointments_render_professional_list_block( $attributes, $content ) {
    $professionals = get_posts( array(
        'post_type'      => 'vpa_professional',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    if ( empty( $professionals ) ) {
        return '<p>' . esc_html__( 'No professionals available at the moment.', 'vitapro-appointments-fse' ) . '</p>';
    }

    $layout_class = $attributes['layout'] === 'list' ? 'vpa-professional-list-layout' : 'vpa-professional-grid-layout';
    $columns_class = 'vpa-columns-' . $attributes['columns'];

    ob_start();
    ?>
    <div class="vitapro-professional-list-wrapper <?php echo esc_attr( $layout_class ); ?> <?php echo esc_attr( $columns_class ); ?>">
        <?php foreach ( $professionals as $professional ) : ?>
            <?php
            $assigned_services = get_post_meta( $professional->ID, '_vpa_professional_services', true );
            if ( ! is_array( $assigned_services ) ) {
                $assigned_services = array();
            }
            ?>
            <div class="vpa-professional-item">
                <?php if ( has_post_thumbnail( $professional->ID ) ) : ?>
                    <div class="vpa-professional-image">
                        <?php echo get_the_post_thumbnail( $professional->ID, 'medium' ); ?>
                    </div>
                <?php endif; ?>

                <div class="vpa-professional-content">
                    <h3 class="vpa-professional-name"><?php echo esc_html( $professional->post_title ); ?></h3>

                    <?php if ( $attributes['showBio'] && ! empty( $professional->post_content ) ) : ?>
                        <div class="vpa-professional-bio">
                            <?php echo wp_kses_post( wp_trim_words( $professional->post_content, 30 ) ); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ( $attributes['showServices'] && ! empty( $assigned_services ) ) : ?>
                        <div class="vpa-professional-services">
                            <strong><?php esc_html_e( 'Services:', 'vitapro-appointments-fse' ); ?></strong>
                            <ul>
                                <?php foreach ( $assigned_services as $service_id ) : ?>
                                    <?php
                                    $service = get_post( $service_id );
                                    if ( $service ) :
                                    ?>
                                        <li><?php echo esc_html( $service->post_title ); ?></li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ( $attributes['showBookingButton'] ) : ?>
                        <div class="vpa-professional-actions">
                            <?php
                            $booking_url = ! empty( $attributes['bookingPageUrl'] ) 
                                ? add_query_arg( 'professional_id', $professional->ID, $attributes['bookingPageUrl'] )
                                : '#';
                            ?>
                            <a href="<?php echo esc_url( $booking_url ); ?>" class="vpa-book-professional-button">
                                <?php esc_html_e( 'Book with', 'vitapro-appointments-fse' ); ?> <?php echo esc_html( $professional->post_title ); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}