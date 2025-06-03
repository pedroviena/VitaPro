<?php
/**
 * Helper functions.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Get plugin option with default value.
 */
function vitapro_appointments_get_option( $option_name, $default_value = '' ) {
    $options = get_option( 'vitapro_appointments_settings', array() );
    return isset( $options[ $option_name ] ) ? $options[ $option_name ] : $default_value;
}

/**
 * Get email template part path.
 */
function vitapro_get_email_template_part_path( $template_name ) {
    return VITAPRO_APPOINTMENTS_PLUGIN_DIR . 'templates/email/' . $template_name . '.php';
}

/**
 * Get localized text for JavaScript.
 */
function vitapro_appointments_get_localized_text( $key ) {
    $texts = array(
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
    );

    return isset( $texts[ $key ] ) ? $texts[ $key ] : '';
}

/**
 * Format appointment status for display.
 */
function vitapro_format_appointment_status( $status ) {
    $statuses = array(
        'pending'   => __( 'Pending', 'vitapro-appointments-fse' ),
        'confirmed' => __( 'Confirmed', 'vitapro-appointments-fse' ),
        'completed' => __( 'Completed', 'vitapro-appointments-fse' ),
        'cancelled' => __( 'Cancelled', 'vitapro-appointments-fse' ),
        'no_show'   => __( 'No Show', 'vitapro-appointments-fse' ),
    );

    return isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
}

/**
 * Get appointment cancellation URL.
 */
function vitapro_get_appointment_cancellation_url( $appointment_id ) {
    return add_query_arg( array(
        'action'         => 'cancel_appointment',
        'appointment_id' => $appointment_id,
        'nonce'          => wp_create_nonce( 'cancel_appointment_' . $appointment_id ),
    ), home_url() );
}

/**
 * Check if appointment can be cancelled by patient.
 */
function vitapro_can_patient_cancel_appointment( $appointment_id ) {
    $cancellation_allowed = vitapro_appointments_get_option( 'allow_patient_cancellation', false );
    
    if ( ! $cancellation_allowed ) {
        return false;
    }

    $status = get_post_meta( $appointment_id, '_vpa_appointment_status', true );
    if ( ! in_array( $status, array( 'pending', 'confirmed' ) ) ) {
        return false;
    }

    $appointment_date = get_post_meta( $appointment_id, '_vpa_appointment_date', true );
    $appointment_time = get_post_meta( $appointment_id, '_vpa_appointment_time', true );
    $buffer_hours = vitapro_appointments_get_option( 'patient_cancellation_buffer_hours', 48 );

    $appointment_datetime = strtotime( $appointment_date . ' ' . $appointment_time );
    $buffer_time = $buffer_hours * 3600; // Convert hours to seconds

    return ( time() + $buffer_time ) < $appointment_datetime;
}

/**
 * Sanitize appointment status.
 */
function vitapro_sanitize_appointment_status( $status ) {
    $valid_statuses = array( 'pending', 'confirmed', 'completed', 'cancelled', 'no_show' );
    return in_array( $status, $valid_statuses ) ? $status : 'pending';
}

/**
 * Get service duration with fallback.
 */
function vitapro_get_service_duration( $service_id ) {
    $duration = get_post_meta( $service_id, '_vpa_service_duration', true );
    return $duration ? absint( $duration ) : 30; // Default 30 minutes
}

/**
 * Get service buffer time.
 */
function vitapro_get_service_buffer_time( $service_id ) {
    $buffer = get_post_meta( $service_id, '_vpa_service_buffer_time', true );
    return $buffer ? absint( $buffer ) : 0;
}

/**
 * Check if date is within booking window.
 */
function vitapro_is_date_bookable( $date_str ) {
    $min_advance_hours = vitapro_appointments_get_option( 'min_advance_notice', 2 );
    $max_advance_days = vitapro_appointments_get_option( 'max_advance_notice', 90 );

    $date = strtotime( $date_str );
    $min_date = strtotime( '+' . $min_advance_hours . ' hours' );
    $max_date = strtotime( '+' . $max_advance_days . ' days' );

    return $date >= $min_date && $date <= $max_date;
}

/**
 * Generate unique appointment reference.
 */
function vitapro_generate_appointment_reference( $appointment_id ) {
    return 'VPA-' . str_pad( $appointment_id, 6, '0', STR_PAD_LEFT );
}