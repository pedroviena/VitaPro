<?php
/**
 * Registers the My Appointments Gutenberg block.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the My Appointments block.
 */
function vitapro_appointments_register_my_appointments_block() {
    register_block_type( 'vitapro-appointments/my-appointments', array(
        'attributes' => array(
            'showUpcoming' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'showPast' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
            'allowCancellation' => array(
                'type'    => 'boolean',
                'default' => true,
            ),
        ),
        'render_callback' => 'vitapro_appointments_render_my_appointments_block',
    ) );
}
add_action( 'init', 'vitapro_appointments_register_my_appointments_block' );

/**
 * Render the My Appointments block.
 *
 * @param array $attributes Block attributes.
 * @return string HTML output.
 */
function vitapro_appointments_render_my_appointments_block( $attributes, $content ) {
    if ( ! is_user_logged_in() ) {
        return '<p>' . esc_html__( 'Please log in to view your appointments.', 'vitapro-appointments-fse' ) . '</p>';
    }

    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;

    // Get user's appointments
    $appointments = get_posts( array(
        'post_type'      => 'vpa_appointment',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'     => '_vpa_appointment_patient_email',
                'value'   => $user_email,
                'compare' => '=',
            ),
        ),
        'meta_key'       => '_vpa_appointment_date',
        'orderby'        => 'meta_value',
        'order'          => 'ASC',
    ) );

    if ( empty( $appointments ) ) {
        return '<p>' . esc_html__( 'You have no appointments scheduled.', 'vitapro-appointments-fse' ) . '</p>';
    }

    $today = date( 'Y-m-d' );
    $upcoming_appointments = array();
    $past_appointments = array();

    foreach ( $appointments as $appointment ) {
        $appointment_date = get_post_meta( $appointment->ID, '_vpa_appointment_date', true );
        if ( $appointment_date >= $today ) {
            $upcoming_appointments[] = $appointment;
        } else {
            $past_appointments[] = $appointment;
        }
    }

    ob_start();
    ?>
    <div class="vitapro-my-appointments-wrapper">
        <?php if ( $attributes['showUpcoming'] && ! empty( $upcoming_appointments ) ) : ?>
            <div class="vpa-upcoming-appointments">
                <h3><?php esc_html_e( 'Upcoming Appointments', 'vitapro-appointments-fse' ); ?></h3>
                <div class="vpa-appointments-list">
                    <?php foreach ( $upcoming_appointments as $appointment ) : ?>
                        <?php echo vitapro_render_appointment_item( $appointment, $attributes['allowCancellation'] ); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $attributes['showPast'] && ! empty( $past_appointments ) ) : ?>
            <div class="vpa-past-appointments">
                <h3><?php esc_html_e( 'Past Appointments', 'vitapro-appointments-fse' ); ?></h3>
                <div class="vpa-appointments-list">
                    <?php foreach ( $past_appointments as $appointment ) : ?>
                        <?php echo vitapro_render_appointment_item( $appointment, false ); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Render individual appointment item.
 */
function vitapro_render_appointment_item( $appointment, $allow_cancellation = false ) {
    $service_id = get_post_meta( $appointment->ID, '_vpa_appointment_service_id', true );
    $professional_id = get_post_meta( $appointment->ID, '_vpa_appointment_professional_id', true );
    $appointment_date = get_post_meta( $appointment->ID, '_vpa_appointment_date', true );
    $appointment_time = get_post_meta( $appointment->ID, '_vpa_appointment_time', true );
    $status = get_post_meta( $appointment->ID, '_vpa_appointment_status', true );

    $service = get_post( $service_id );
    $professional = get_post( $professional_id );

    $status_labels = array(
        'pending'   => __( 'Pending', 'vitapro-appointments-fse' ),
        'confirmed' => __( 'Confirmed', 'vitapro-appointments-fse' ),
        'completed' => __( 'Completed', 'vitapro-appointments-fse' ),
        'cancelled' => __( 'Cancelled', 'vitapro-appointments-fse' ),
        'no_show'   => __( 'No Show', 'vitapro-appointments-fse' ),
    );

    $status_label = isset( $status_labels[ $status ] ) ? $status_labels[ $status ] : $status;

    ob_start();
    ?>
    <div class="vpa-appointment-item vpa-status-<?php echo esc_attr( $status ); ?>">
        <div class="vpa-appointment-info">
            <h4 class="vpa-appointment-service">
                <?php echo $service ? esc_html( $service->post_title ) : esc_html__( 'Unknown Service', 'vitapro-appointments-fse' ); ?>
            </h4>
            
            <div class="vpa-appointment-details">
                <span class="vpa-appointment-professional">
                    <strong><?php esc_html_e( 'Professional:', 'vitapro-appointments-fse' ); ?></strong>
                    <?php echo $professional ? esc_html( $professional->post_title ) : esc_html__( 'Unknown Professional', 'vitapro-appointments-fse' ); ?>
                </span>
                
                <span class="vpa-appointment-datetime">
                    <strong><?php esc_html_e( 'Date & Time:', 'vitapro-appointments-fse' ); ?></strong>
                    <?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $appointment_date ) ) ); ?>
                    <?php esc_html_e( 'at', 'vitapro-appointments-fse' ); ?>
                    <?php echo esc_html( date_i18n( get_option( 'time_format' ), strtotime( $appointment_time ) ) ); ?>
                </span>
                
                <span class="vpa-appointment-status">
                    <strong><?php esc_html_e( 'Status:', 'vitapro-appointments-fse' ); ?></strong>
                    <span class="vpa-status-badge vpa-status-<?php echo esc_attr( $status ); ?>">
                        <?php echo esc_html( $status_label ); ?>
                    </span>
                </span>
            </div>
        </div>

        <?php if ( $allow_cancellation && in_array( $status, array( 'pending', 'confirmed' ) ) ) : ?>
            <?php
            $cancellation_allowed = vitapro_appointments_get_option( 'allow_patient_cancellation', false );
            $buffer_hours = vitapro_appointments_get_option( 'patient_cancellation_buffer_hours', 48 );
            $appointment_datetime = strtotime( $appointment_date . ' ' . $appointment_time );
            $buffer_time = $buffer_hours * 3600; // Convert hours to seconds
            $can_cancel = $cancellation_allowed && ( time() + $buffer_time ) < $appointment_datetime;
            ?>
            
            <?php if ( $can_cancel ) : ?>
                <div class="vpa-appointment-actions">
                    <a href="<?php echo esc_url( add_query_arg( array( 'action' => 'cancel_appointment', 'appointment_id' => $appointment->ID, 'nonce' => wp_create_nonce( 'cancel_appointment_' . $appointment->ID ) ) ) ); ?>" 
                       class="vpa-cancel-button" 
                       onclick="return confirm('<?php esc_attr_e( 'Are you sure you want to cancel this appointment?', 'vitapro-appointments-fse' ); ?>')">
                        <?php esc_html_e( 'Cancel Appointment', 'vitapro-appointments-fse' ); ?>
                    </a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}

function render_user_appointments_custom_table($user_email, $type = 'upcoming', $limit = 10, $allow_cancellation = true) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'vpa_appointments';
    $current_date = current_time('Y-m-d');
    $current_time = current_time('H:i:s');

    if ($type === 'upcoming') {
        $where_clause = "WHERE customer_email = %s AND (appointment_date > %s OR (appointment_date = %s AND appointment_time > %s)) AND status != 'cancelled'";
        $order_clause = "ORDER BY appointment_date ASC, appointment_time ASC";
        $prepare_values = array($user_email, $current_date, $current_date, $current_time);
    } else {
        $where_clause = "WHERE customer_email = %s AND (appointment_date < %s OR (appointment_date = %s AND appointment_time <= %s))";
        $order_clause = "ORDER BY appointment_date DESC, appointment_time DESC";
        $prepare_values = array($user_email, $current_date, $current_date, $current_time);
    }

    $sql = "SELECT * FROM {$table_name} {$where_clause} {$order_clause} LIMIT %d";
    $prepare_values[] = $limit;

    $appointments = $wpdb->get_results($wpdb->prepare($sql, $prepare_values));

    // ...existing code...
}