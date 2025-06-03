<?php
/**
 * Registers the Service List Gutenberg block.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Service List block.
 */
function vitapro_appointments_register_service_list_block() {
    register_block_type( 'vitapro-appointments/service-list', array(
        'attributes' => array(
            'layout' => array(
                'type'    => 'string',
                'default' => 'grid',
            ),
            'columns' => array(
                'type'    => 'number',
                'default' => 3,
            ),
            'showPrice' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showDescription' => array(
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
        'render_callback' => 'vitapro_appointments_render_service_list_block',
    ) );
}
add_action( 'init', 'vitapro_appointments_register_service_list_block' );

/**
 * Render the Service List block.
 */
function vitapro_appointments_render_service_list_block( $attributes, $content ) {
    $services = get_posts( array(
        'post_type'      => 'vpa_service',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    if ( empty( $services ) ) {
        return '<p>' . esc_html__( 'No services available at the moment.', 'vitapro-appointments-fse' ) . '</p>';
    }

    $layout_class = $attributes['layout'] === 'list' ? 'vpa-service-list-layout' : 'vpa-service-grid-layout';
    $columns_class = 'vpa-columns-' . $attributes['columns'];

    ob_start();
    ?>
    <div class="vitapro-service-list-wrapper <?php echo esc_attr( $layout_class ); ?> <?php echo esc_attr( $columns_class ); ?>">
        <?php foreach ( $services as $service ) : ?>
            <?php
            $price = get_post_meta( $service->ID, '_vpa_service_price', true );
            $duration = get_post_meta( $service->ID, '_vpa_service_duration', true );
            ?>
            <div class="vpa-service-item">
                <?php if ( has_post_thumbnail( $service->ID ) ) : ?>
                    <div class="vpa-service-image">
                        <?php echo get_the_post_thumbnail( $service->ID, 'medium' ); ?>
                    </div>
                <?php endif; ?>

                <div class="vpa-service-content">
                    <h3 class="vpa-service-title"><?php echo esc_html( $service->post_title ); ?></h3>

                    <?php if ( $attributes['showDescription'] && ! empty( $service->post_content ) ) : ?>
                        <div class="vpa-service-description">
                            <?php echo wp_kses_post( wp_trim_words( $service->post_content, 20 ) ); ?>
                        </div>
                    <?php endif; ?>

                    <div class="vpa-service-meta">
                        <?php if ( $duration ) : ?>
                            <span class="vpa-service-duration">
                                <strong><?php esc_html_e( 'Duration:', 'vitapro-appointments-fse' ); ?></strong>
                                <?php echo esc_html( $duration ); ?> <?php esc_html_e( 'minutes', 'vitapro-appointments-fse' ); ?>
                            </span>
                        <?php endif; ?>

                        <?php if ( $attributes['showPrice'] && $price ) : ?>
                            <span class="vpa-service-price">
                                <strong><?php esc_html_e( 'Price:', 'vitapro-appointments-fse' ); ?></strong>
                                $<?php echo esc_html( number_format( $price, 2 ) ); ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ( $attributes['showBookingButton'] ) : ?>
                        <div class="vpa-service-actions">
                            <?php
                            $booking_url = ! empty( $attributes['bookingPageUrl'] ) 
                                ? add_query_arg( 'service_id', $service->ID, $attributes['bookingPageUrl'] )
                                : '#';
                            ?>
                            <a href="<?php echo esc_url( $booking_url ); ?>" class="vpa-book-service-button">
                                <?php esc_html_e( 'Book Now', 'vitapro-appointments-fse' ); ?>
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