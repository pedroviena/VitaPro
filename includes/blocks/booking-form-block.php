<?php
/**
 * Registers the Booking Form Gutenberg block.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Booking Form block.
 */
function vitapro_appointments_register_booking_form_block() {
    wp_register_script(
        'vitapro-booking-form-block-script',
        VITAPRO_APPOINTMENTS_PLUGIN_URL . 'assets/js/vitapro-booking-form-block.js',
        array( 'jquery' ),
        VITAPRO_APPOINTMENTS_VERSION,
        true
    );

    register_block_type( 'vitapro-appointments/booking-form', array(
        'attributes' => array(
            'defaultServiceId' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'defaultProfessionalId' => array(
                'type'    => 'number',
                'default' => 0,
            ),
            'showServiceStep' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showProfessionalStep' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showCalendarStep' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showBookingFormStep' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
        ),
        'render_callback' => 'vitapro_appointments_render_booking_form_block',
        'script'          => 'vitapro-booking-form-block-script',
    ) );
}
add_action( 'init', 'vitapro_appointments_register_booking_form_block' );

/**
 * Render the Booking Form block.
 *
 * @param array $attributes Block attributes.
 * @param string $content Block content.
 * @return string HTML output.
 */
function vitapro_appointments_render_booking_form_block( $attributes, $content ) {
    $block_id = 'vitapro-booking-form-' . wp_generate_uuid4();
    
    // Get services and professionals
    $services = get_posts( array(
        'post_type'      => 'vpa_service',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    $professionals = get_posts( array(
        'post_type'      => 'vpa_professional',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    // Get custom fields
    $defined_custom_fields = vitapro_appointments_get_option( 'custom_fields', array() );

    // Prepare data for JavaScript
    $js_data = array(
        'ajax_url'    => admin_url( 'admin-ajax.php' ),
        'nonce'       => wp_create_nonce( 'vitapro_appointments_frontend_nonce' ),
        'attributes'  => $attributes,
        'text'        => array(
            'processing'           => __( 'Processing...', 'vitapro-appointments-fse' ),
            'select_service'       => __( 'Please select a service', 'vitapro-appointments-fse' ),
            'select_professional'  => __( 'Please select a professional', 'vitapro-appointments-fse' ),
            'select_date'          => __( 'Please select a date', 'vitapro-appointments-fse' ),
            'select_time'          => __( 'Please select a time slot', 'vitapro-appointments-fse' ),
            'fill_required_fields' => __( 'Please fill in all required fields', 'vitapro-appointments-fse' ),
            'booking_success'      => __( 'Booking submitted successfully!', 'vitapro-appointments-fse' ),
            'booking_error'        => __( 'There was an error processing your booking. Please try again.', 'vitapro-appointments-fse' ),
            'no_slots_available'   => __( 'No time slots available for the selected date.', 'vitapro-appointments-fse' ),
            'choose_option'        => __( 'Choose an option', 'vitapro-appointments-fse' ),
        ),
        'min_booking_date' => date( 'Y-m-d', strtotime( '+' . vitapro_appointments_get_option( 'min_advance_notice', 2 ) . ' hours' ) ),
        'max_booking_date' => date( 'Y-m-d', strtotime( '+' . vitapro_appointments_get_option( 'max_advance_notice', 90 ) . ' days' ) ),
        'defined_custom_fields' => $defined_custom_fields,
    );

    // Enqueue the script with localized data
    wp_enqueue_script( 'vitapro-booking-form-block-script' );
    wp_localize_script( 'vitapro-booking-form-block-script', 'vitaproBlockData_' . str_replace( '-', '_', $block_id ), $js_data );

    ob_start();
    ?>
    <div id="<?php echo esc_attr( $block_id ); ?>" class="vitapro-booking-form-wrapper">
        <div class="vpa-loading-indicator" style="display: none;">
            <span class="vpa-loading-indicator-text"><?php esc_html_e( 'Loading...', 'vitapro-appointments-fse' ); ?></span>
        </div>

        <div class="vpa-error-message-area" style="display: none;"></div>

        <!-- Step 1: Service Selection -->
        <?php if ( $attributes['showServiceStep'] && ( ! $attributes['defaultServiceId'] || $attributes['defaultServiceId'] === 0 ) ) : ?>
        <div class="vpa-step vpa-step-service" data-step="1">
            <h3><?php esc_html_e( 'Select Service', 'vitapro-appointments-fse' ); ?></h3>
            <select class="vpa-service-select vpa-input">
                <option value=""><?php esc_html_e( 'Choose a service...', 'vitapro-appointments-fse' ); ?></option>
                <?php foreach ( $services as $service ) : ?>
                    <option value="<?php echo esc_attr( $service->ID ); ?>" data-duration="<?php echo esc_attr( get_post_meta( $service->ID, '_vpa_service_duration', true ) ); ?>">
                        <?php echo esc_html( $service->post_title ); ?>
                        <?php
                        $price = get_post_meta( $service->ID, '_vpa_service_price', true );
                        if ( $price ) {
                            echo ' - $' . esc_html( number_format( $price, 2 ) );
                        }
                        ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- Step 2: Professional Selection -->
        <?php if ( $attributes['showProfessionalStep'] && ( ! $attributes['defaultProfessionalId'] || $attributes['defaultProfessionalId'] === 0 ) ) : ?>
        <div class="vpa-step vpa-step-professional" data-step="2" style="display: none;">
            <h3><?php esc_html_e( 'Select Professional', 'vitapro-appointments-fse' ); ?></h3>
            <div class="vpa-loading-professionals" style="display: none;">
                <?php esc_html_e( 'Loading professionals...', 'vitapro-appointments-fse' ); ?>
            </div>
            <select class="vpa-professional-select vpa-input">
                <option value=""><?php esc_html_e( 'Choose a professional...', 'vitapro-appointments-fse' ); ?></option>
            </select>
        </div>
        <?php endif; ?>

        <!-- Step 3: Date & Time Selection -->
        <?php if ( $attributes['showCalendarStep'] ) : ?>
        <div class="vpa-step vpa-step-datetime" data-step="3" style="display: none;">
            <h3><?php esc_html_e( 'Select Date & Time', 'vitapro-appointments-fse' ); ?></h3>
            
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

            <input type="hidden" class="vpa-date-select-hidden" />

            <div class="vpa-time-slots-container">
                <h4><?php esc_html_e( 'Available Times', 'vitapro-appointments-fse' ); ?></h4>
                <div class="vpa-time-slots-placeholder"><?php esc_html_e( 'Please select a date to see available times.', 'vitapro-appointments-fse' ); ?></div>
                <div class="vpa-time-slots-list"></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Step 4: Booking Form -->
        <?php if ( $attributes['showBookingFormStep'] ) : ?>
        <div class="vpa-step vpa-step-booking" data-step="4" style="display: none;">
            <h3><?php esc_html_e( 'Your Information', 'vitapro-appointments-fse' ); ?></h3>
            
            <form class="vpa-booking-actual-form">
                <input type="hidden" class="vpa-selected-time-input" name="vpa_selected_time" />
                
                <div class="vpa-form-row">
                    <label for="<?php echo esc_attr( $block_id ); ?>-patient-name">
                        <?php esc_html_e( 'Full Name', 'vitapro-appointments-fse' ); ?> *
                    </label>
                    <input type="text" id="<?php echo esc_attr( $block_id ); ?>-patient-name" name="vpa_patient_name" class="vpa-input" required />
                </div>

                <div class="vpa-form-row">
                    <label for="<?php echo esc_attr( $block_id ); ?>-patient-email">
                        <?php esc_html_e( 'Email Address', 'vitapro-appointments-fse' ); ?> *
                    </label>
                    <input type="email" id="<?php echo esc_attr( $block_id ); ?>-patient-email" name="vpa_patient_email" class="vpa-input" required />
                </div>

                <div class="vpa-form-row">
                    <label for="<?php echo esc_attr( $block_id ); ?>-patient-phone">
                        <?php esc_html_e( 'Phone Number', 'vitapro-appointments-fse' ); ?>
                    </label>
                    <input type="tel" id="<?php echo esc_attr( $block_id ); ?>-patient-phone" name="vpa_patient_phone" class="vpa-input" />
                </div>

                <!-- Custom Fields Container -->
                <div class="vpa-custom-fields-render-area"></div>

                <div class="vpa-form-actions">
                    <button type="submit" class="vpa-submit-button"><?php esc_html_e( 'Book Appointment', 'vitapro-appointments-fse' ); ?></button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Success Message -->
        <div class="vpa-step vpa-step-success" data-step="5" style="display: none;">
            <h3><?php esc_html_e( 'Booking Confirmed!', 'vitapro-appointments-fse' ); ?></h3>
            <div class="vpa-success-message">
                <p><?php esc_html_e( 'Thank you for your booking. We will contact you shortly to confirm your appointment.', 'vitapro-appointments-fse' ); ?></p>
                <div class="vpa-appointment-details"></div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Register block category.
 */
function vitapro_appointments_register_block_category( $categories, $block_editor_context ) {
    return array_merge(
        $categories,
        array(
            array(
                'slug'  => 'vitapro-appointments',
                'title' => __( 'VitaPro Appointments', 'vitapro-appointments-fse' ),
                'icon'  => 'calendar-alt',
            ),
        )
    );
}
add_filter( 'block_categories_all', 'vitapro_appointments_register_block_category', 10, 2 );

/**
 * Register block patterns.
 */
function vitapro_appointments_register_block_patterns() {
    register_block_pattern(
        'vitapro-appointments/full-booking-process',
        array(
            'title'       => __( 'Full Booking Process', 'vitapro-appointments-fse' ),
            'description' => __( 'Complete appointment booking form with all steps', 'vitapro-appointments-fse' ),
            'content'     => '<!-- wp:vitapro-appointments/booking-form /-->',
            'categories'  => array( 'vitapro-appointments' ),
        )
    );

    register_block_pattern(
        'vitapro-appointments/quick-booking',
        array(
            'title'       => __( 'Quick Booking Form', 'vitapro-appointments-fse' ),
            'description' => __( 'Simplified booking form with pre-selected service', 'vitapro-appointments-fse' ),
            'content'     => '<!-- wp:vitapro-appointments/booking-form {"showServiceStep":false,"defaultServiceId":1} /-->',
            'categories'  => array( 'vitapro-appointments' ),
        )
    );
}
add_action( 'init', 'vitapro_appointments_register_block_patterns' );

// Exemplo de inclusão de template:
$template_path = VITAPRO_APPOINTMENTS_FSE_PATH . 'templates/booking-form.php';
if (file_exists($template_path)) {
    include $template_path;
} else {
    error_log('VitaPro Error: Template file not found: ' . $template_path);
    // Opcional: echo '<div>Booking form template not found.</div>';
}