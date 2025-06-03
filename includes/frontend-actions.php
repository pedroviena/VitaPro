<?php
/**
 * Frontend actions for VitaPro Appointments.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Handle frontend appointment actions
add_action( 'template_redirect', 'vitapro_handle_frontend_appointment_actions' );

/**
 * Handle frontend appointment actions (like cancellation).
 */
function vitapro_handle_frontend_appointment_actions() {
    if ( ! isset( $_GET['action'] ) ) {
        return;
    }

    $action = sanitize_text_field( $_GET['action'] );

    switch ( $action ) {
        case 'cancel_appointment':
            vitapro_process_patient_cancellation();
            break;
    }
}

/**
 * Process patient appointment cancellation.
 */
function vitapro_process_patient_cancellation() {
    if ( ! isset( $_GET['appointment_id'] ) || ! isset( $_GET['nonce'] ) ) {
        return;
    }

    $appointment_id = absint( $_GET['appointment_id'] );
    $nonce = sanitize_text_field( $_GET['nonce'] );

    // Verify nonce
    if ( ! wp_verify_nonce( $nonce, 'cancel_appointment_' . $appointment_id ) ) {
        wp_die( __( 'Security check failed.', 'vitapro-appointments-fse' ) );
    }

    // Check if appointment exists
    $appointment = get_post( $appointment_id );
    if ( ! $appointment || $appointment->post_type !== 'vpa_appointment' ) {
        wp_die( __( 'Appointment not found.', 'vitapro-appointments-fse' ) );
    }

    // Check if cancellation is allowed
    if ( ! vitapro_can_patient_cancel_appointment( $appointment_id ) ) {
        wp_die( __( 'This appointment cannot be cancelled.', 'vitapro-appointments-fse' ) );
    }

    // Check if user has permission (either logged in as the patient or has the email)
    $patient_email = get_post_meta( $appointment_id, '_vpa_appointment_patient_email', true );
    $current_user = wp_get_current_user();
    
    $has_permission = false;
    if ( is_user_logged_in() && $current_user->user_email === $patient_email ) {
        $has_permission = true;
    }

    if ( ! $has_permission ) {
        wp_die( __( 'You do not have permission to cancel this appointment.', 'vitapro-appointments-fse' ) );
    }

    // Get old status for email
    $old_status = get_post_meta( $appointment_id, '_vpa_appointment_status', true );

    // Update appointment status
    update_post_meta( $appointment_id, '_vpa_appointment_status', 'cancelled' );

    // Send cancellation emails
    vitapro_send_cancellation_emails( $appointment_id, 'patient', $old_status );

    // Set success message
    $redirect_url = add_query_arg( array(
        'vpa_message' => 'appointment_cancelled',
        'appointment_id' => $appointment_id,
    ), wp_get_referer() ? wp_get_referer() : home_url() );

    wp_redirect( $redirect_url );
    exit;
}

// Display frontend messages
add_action( 'wp', 'vitapro_maybe_display_frontend_messages' );

/**
 * Maybe display frontend messages.
 */
function vitapro_maybe_display_frontend_messages() {
    if ( isset( $_GET['vpa_message'] ) ) {
        add_filter( 'the_content', 'vitapro_display_cancellation_message_in_content' );
    }
}

/**
 * Display cancellation message in content.
 */
function vitapro_display_cancellation_message_in_content( $content ) {
    if ( ! isset( $_GET['vpa_message'] ) ) {
        return $content;
    }

    $message_type = sanitize_text_field( $_GET['vpa_message'] );
    $message = '';

    switch ( $message_type ) {
        case 'appointment_cancelled':
            $message = '<div class="vpa-message vpa-message-success">';
            $message .= '<p>' . __( 'Your appointment has been successfully cancelled. You will receive a confirmation email shortly.', 'vitapro-appointments-fse' ) . '</p>';
            $message .= '</div>';
            break;
    }

    return $message . $content;
}