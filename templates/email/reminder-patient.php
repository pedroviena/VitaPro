<?php
/**
 * Email Template: Appointment Reminder for Patient
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

extract( $args );
?>

<?php echo vitapro_render_email_template( 'email-header', $args ); ?>

<h2><?php _e( 'Appointment Reminder', 'vitapro-appointments-fse' ); ?></h2>

<p><?php printf( __( 'Hello %s,', 'vitapro-appointments-fse' ), esc_html( $patient_name ) ); ?></p>

<p><?php _e( 'This is a friendly reminder about your upcoming appointment:', 'vitapro-appointments-fse' ); ?></p>

<div class="appointment-details">
    <h3><?php _e( 'Appointment Details', 'vitapro-appointments-fse' ); ?></h3>
    <ul>
        <li><strong><?php _e( 'Service:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $service_name ); ?></li>
        <li><strong><?php _e( 'Professional:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $professional_name ); ?></li>
        <li><strong><?php _e( 'Date:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_date ); ?></li>
        <li><strong><?php _e( 'Time:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_time ); ?></li>
        <li><strong><?php _e( 'Reference:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( vitapro_generate_appointment_reference( $appointment_id ) ); ?></li>
    </ul>
</div>

<h3><?php _e( 'Important Reminders', 'vitapro-appointments-fse' ); ?></h3>
<ul>
    <li><?php _e( 'Please arrive 10-15 minutes before your scheduled time', 'vitapro-appointments-fse' ); ?></li>
    <li><?php _e( 'Bring any required documents or identification', 'vitapro-appointments-fse' ); ?></li>
    <li><?php _e( 'If you need to cancel or reschedule, please contact us as soon as possible', 'vitapro-appointments-fse' ); ?></li>
</ul>

<?php if ( vitapro_can_patient_cancel_appointment( $appointment_id ) ) : ?>
    <p>
        <a href="<?php echo esc_url( vitapro_get_appointment_cancellation_url( $appointment_id ) ); ?>" 
           style="background-color: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 4px; display: inline-block; font-size: 14px;">
            <?php _e( 'Cancel Appointment', 'vitapro-appointments-fse' ); ?>
        </a>
    </p>
<?php endif; ?>

<p><?php _e( 'We look forward to seeing you!', 'vitapro-appointments-fse' ); ?></p>

<?php echo vitapro_render_email_template( 'email-footer', $args ); ?>