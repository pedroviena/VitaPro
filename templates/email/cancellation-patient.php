<?php
/**
 * Email Template: Appointment Cancellation Confirmation for Patient
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

extract( $args );
?>

<?php echo vitapro_render_email_template( 'email-header', $args ); ?>

<h2><?php _e( 'Appointment Cancelled', 'vitapro-appointments-fse' ); ?></h2>

<p><?php printf( __( 'Hello %s,', 'vitapro-appointments-fse' ), esc_html( $patient_name ) ); ?></p>

<p><?php _e( 'Your appointment has been successfully cancelled.', 'vitapro-appointments-fse' ); ?></p>

<div class="appointment-details">
    <h3><?php _e( 'Cancelled Appointment Details', 'vitapro-appointments-fse' ); ?></h3>
    <ul>
        <li><strong><?php _e( 'Service:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $service_name ); ?></li>
        <li><strong><?php _e( 'Professional:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $professional_name ); ?></li>
        <li><strong><?php _e( 'Date:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_date ); ?></li>
        <li><strong><?php _e( 'Time:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_time ); ?></li>
        <li><strong><?php _e( 'Reference:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( vitapro_generate_appointment_reference( $appointment_id ) ); ?></li>
    </ul>
</div>

<p><?php _e( 'If you would like to book a new appointment, please visit our website or contact us directly.', 'vitapro-appointments-fse' ); ?></p>

<p><?php _e( 'Thank you for your understanding.', 'vitapro-appointments-fse' ); ?></p>

<?php echo vitapro_render_email_template( 'email-footer', $args ); ?>