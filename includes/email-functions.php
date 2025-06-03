<?php
/**
 * Email functions for VitaPro Appointments.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Send new booking emails to admin and patient.
 */
function vitapro_send_new_booking_emails( $appointment_id ) {
    $email_data = vitapro_get_email_data( $appointment_id );
    
    if ( ! $email_data ) {
        return false;
    }

    // Send admin notification
    if ( vitapro_appointments_get_option( 'enable_admin_notification', true ) ) {
        $admin_email = vitapro_appointments_get_option( 'email_admin_new_booking', get_option( 'admin_email' ) );
        if ( $admin_email ) {
            $subject = sprintf( __( 'New Appointment Booking - %s', 'vitapro-appointments-fse' ), $email_data['service_name'] );
            $message = vitapro_render_email_template( 'new-booking-admin', $email_data );
            vitapro_send_email( $admin_email, $subject, $message );
        }
    }

    // Send patient confirmation
    if ( vitapro_appointments_get_option( 'enable_patient_confirmation', true ) ) {
        $subject = sprintf( __( 'Appointment Confirmation - %s', 'vitapro-appointments-fse' ), $email_data['service_name'] );
        $message = vitapro_render_email_template( 'new-booking-patient', $email_data );
        vitapro_send_email( $email_data['patient_email'], $subject, $message );
    }

    return true;
}

/**
 * Get email data for an appointment.
 */
function vitapro_get_email_data( $appointment_id ) {
    $appointment = get_post( $appointment_id );
    if ( ! $appointment || $appointment->post_type !== 'vpa_appointment' ) {
        return false;
    }

    $service_id = get_post_meta( $appointment_id, '_vpa_appointment_service_id', true );
    $professional_id = get_post_meta( $appointment_id, '_vpa_appointment_professional_id', true );
    $appointment_date = get_post_meta( $appointment_id, '_vpa_appointment_date', true );
    $appointment_time = get_post_meta( $appointment_id, '_vpa_appointment_time', true );
    $patient_name = get_post_meta( $appointment_id, '_vpa_appointment_patient_name', true );
    $patient_email = get_post_meta( $appointment_id, '_vpa_appointment_patient_email', true );
    $patient_phone = get_post_meta( $appointment_id, '_vpa_appointment_patient_phone', true );
    $status = get_post_meta( $appointment_id, '_vpa_appointment_status', true );
    $custom_fields_data = get_post_meta( $appointment_id, '_vpa_appointment_custom_fields_data', true );

    $service = get_post( $service_id );
    $professional = get_post( $professional_id );

    $data = array(
        'appointment_id'     => $appointment_id,
        'service_name'       => $service ? $service->post_title : __( 'Unknown Service', 'vitapro-appointments-fse' ),
        'professional_name'  => $professional ? $professional->post_title : __( 'Any available professional', 'vitapro-appointments-fse' ),
        'appointment_date'   => $appointment_date,
        'appointment_time'   => $appointment_time,
        'formatted_date'     => date_i18n( get_option( 'date_format' ), strtotime( $appointment_date ) ),
        'formatted_time'     => date_i18n( get_option( 'time_format' ), strtotime( $appointment_time ) ),
        'patient_name'       => $patient_name,
        'patient_email'      => $patient_email,
        'patient_phone'      => $patient_phone,
        'status'             => $status,
        'site_name'          => get_bloginfo( 'name' ),
        'site_url'           => home_url(),
        'custom_fields'      => array(),
    );

    // Add custom fields data
    if ( ! empty( $custom_fields_data ) ) {
        $defined_custom_fields = vitapro_appointments_get_option( 'custom_fields', array() );
        foreach ( $custom_fields_data as $field_id => $field_value ) {
            if ( isset( $defined_custom_fields[ $field_id ] ) ) {
                $data['custom_fields'][ $field_id ] = array(
                    'label' => $defined_custom_fields[ $field_id ]['label'],
                    'value' => $field_value,
                    'type'  => $defined_custom_fields[ $field_id ]['type'] ?? 'text',
                );
            }
        }
    }

    return $data;
}

/**
 * Send appointment reminder email.
 */
function vitapro_send_appointment_reminder_email( $appointment_id ) {
    $email_data = vitapro_get_email_data( $appointment_id );
    
    if ( ! $email_data ) {
        return false;
    }

    $subject = sprintf( __( 'Appointment Reminder - %s', 'vitapro-appointments-fse' ), $email_data['service_name'] );
    $message = vitapro_render_email_template( 'reminder-patient', $email_data );
    
    return vitapro_send_email( $email_data['patient_email'], $subject, $message );
}

/**
 * Send cancellation emails.
 */
function vitapro_send_cancellation_emails( $appointment_id, $cancelled_by = 'admin', $old_status = '' ) {
    $email_data = vitapro_get_email_data( $appointment_id );
    
    if ( ! $email_data ) {
        return false;
    }

    $email_data['cancelled_by'] = $cancelled_by;
    $email_data['old_status'] = $old_status;

    // Send admin notification
    if ( vitapro_appointments_get_option( 'enable_admin_notification', true ) ) {
        $admin_email = vitapro_appointments_get_option( 'email_admin_new_booking', get_option( 'admin_email' ) );
        if ( $admin_email ) {
            $subject = sprintf( __( 'Appointment Cancelled - %s', 'vitapro-appointments-fse' ), $email_data['service_name'] );
            $message = vitapro_render_email_template( 'cancellation-admin', $email_data );
            vitapro_send_email( $admin_email, $subject, $message );
        }
    }

    // Send patient notification
    $subject = sprintf( __( 'Appointment Cancelled - %s', 'vitapro-appointments-fse' ), $email_data['service_name'] );
    $message = vitapro_render_email_template( 'cancellation-patient', $email_data );
    vitapro_send_email( $email_data['patient_email'], $subject, $message );

    return true;
}

/**
 * Send status change email.
 */
function vitapro_send_status_change_email( $appointment_id, $new_status ) {
    $email_data = vitapro_get_email_data( $appointment_id );
    
    if ( ! $email_data ) {
        return false;
    }

    $status_labels = array(
        'pending'   => __( 'Pending', 'vitapro-appointments-fse' ),
        'confirmed' => __( 'Confirmed', 'vitapro-appointments-fse' ),
        'completed' => __( 'Completed', 'vitapro-appointments-fse' ),
        'cancelled' => __( 'Cancelled', 'vitapro-appointments-fse' ),
        'no_show'   => __( 'No Show', 'vitapro-appointments-fse' ),
    );

    $email_data['new_status'] = $new_status;
    $email_data['new_status_label'] = isset( $status_labels[ $new_status ] ) ? $status_labels[ $new_status ] : $new_status;

    $subject = sprintf( __( 'Appointment Status Update - %s', 'vitapro-appointments-fse' ), $email_data['service_name'] );
    
    // Simple status change email
    $message = vitapro_render_email_template( 'email-header', $email_data );
    $message .= '<h2>' . sprintf( __( 'Appointment Status Updated', 'vitapro-appointments-fse' ) ) . '</h2>';
    $message .= '<p>' . sprintf( __( 'Hello %s,', 'vitapro-appointments-fse' ), $email_data['patient_name'] ) . '</p>';
    $message .= '<p>' . sprintf( __( 'Your appointment status has been updated to: %s', 'vitapro-appointments-fse' ), '<strong>' . $email_data['new_status_label'] . '</strong>' ) . '</p>';
    $message .= '<p><strong>' . __( 'Appointment Details:', 'vitapro-appointments-fse' ) . '</strong></p>';
    $message .= '<ul>';
    $message .= '<li><strong>' . __( 'Service:', 'vitapro-appointments-fse' ) . '</strong> ' . $email_data['service_name'] . '</li>';
    $message .= '<li><strong>' . __( 'Professional:', 'vitapro-appointments-fse' ) . '</strong> ' . $email_data['professional_name'] . '</li>';
    $message .= '<li><strong>' . __( 'Date:', 'vitapro-appointments-fse' ) . '</strong> ' . $email_data['formatted_date'] . '</li>';
    $message .= '<li><strong>' . __( 'Time:', 'vitapro-appointments-fse' ) . '</strong> ' . $email_data['formatted_time'] . '</li>';
    $message .= '</ul>';
    $message .= vitapro_render_email_template( 'email-footer', $email_data );

    return vitapro_send_email( $email_data['patient_email'], $subject, $message );
}

/**
 * Render email template.
 */
function vitapro_render_email_template( $template_name, $args = array() ) {
    $template_path = vitapro_get_email_template_part_path( $template_name );
    
    if ( ! file_exists( $template_path ) ) {
        return '';
    }

    ob_start();
    include $template_path;
    return ob_get_clean();
}

/**
 * Send email using WordPress mail function.
 */
function vitapro_send_email( $to, $subject, $message, $headers = array(), $attachments = array() ) {
    $from_name = vitapro_appointments_get_option( 'email_from_name', get_bloginfo( 'name' ) );
    $from_email = vitapro_appointments_get_option( 'email_from_address', get_option( 'admin_email' ) );

    $default_headers = array(
        'Content-Type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_email . '>',
    );

    $headers = array_merge( $default_headers, $headers );

    return wp_mail( $to, $subject, $message, $headers, $attachments );
}