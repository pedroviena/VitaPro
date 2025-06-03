<?php
/**
 * Email Template: New Booking Confirmation for Patient
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

extract( $args );
?>

<?php echo vitapro_render_email_template( 'email-header', $args ); ?>

<h2><?php _e( 'Appointment Confirmation', 'vitapro-appointments-fse' ); ?></h2>

<p><?php printf( __( 'Hello %s,', 'vitapro-appointments-fse' ), esc_html( $patient_name ) ); ?></p>

<?php if ( $status === 'confirmed' ) : ?>
    <p><?php _e( 'Your appointment has been confirmed! Here are the details:', 'vitapro-appointments-fse' ); ?></p>
<?php else : ?>
    <p><?php _e( 'Thank you for booking an appointment with us. Your booking is currently pending approval. We will contact you shortly to confirm your appointment.', 'vitapro-appointments-fse' ); ?></p>
<?php endif; ?>

<div class="appointment-details">
    <h3><?php _e( 'Appointment Details', 'vitapro-appointments-fse' ); ?></h3>
    <ul>
        <li><strong><?php _e( 'Service:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $service_name ); ?></li>
        <li><strong><?php _e( 'Professional:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $professional_name ); ?></li>
        <li><strong><?php _e( 'Date:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_date ); ?></li>
        <li><strong><?php _e( 'Time:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_time ); ?></li>
        <li><strong><?php _e( 'Status:', 'vitapro-appointments-fse' ); ?></strong> 
            <span class="status-badge status-<?php echo esc_attr( $status ); ?>">
                <?php echo esc_html( vitapro_format_appointment_status( $status ) ); ?>
            </span>
        </li>
        <li><strong><?php _e( 'Reference:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( vitapro_generate_appointment_reference( $appointment_id ) ); ?></li>
    </ul>
</div>

<?php if ( ! empty( $custom_fields ) ) : ?>
<div class="custom-fields">
    <h3><?php _e( 'Your Information', 'vitapro-appointments-fse' ); ?></h3>
    <?php foreach ( $custom_fields as $field_id => $field_data ) : ?>
        <div class="custom-field">
            <strong><?php echo esc_html( $field_data['label'] ); ?>:</strong>
            <?php if ( $field_data['type'] === 'textarea' ) : ?>
                <br><?php echo nl2br( esc_html( $field_data['value'] ) ); ?>
            <?php else : ?>
                <?php echo esc_html( $field_data['value'] ); ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ( $status === 'confirmed' ) : ?>
    <h3><?php _e( 'What to Expect', 'vitapro-appointments-fse' ); ?></h3>
    <p><?php _e( 'Please arrive 10-15 minutes before your scheduled appointment time. If you need to reschedule or cancel, please contact us as soon as possible.', 'vitapro-appointments-fse' ); ?></p>
    
    <?php if ( vitapro_appointments_get_option( 'enable_reminders', false ) ) : ?>
        <p><?php printf( __( 'You will receive a reminder email %d hours before your appointment.', 'vitapro-appointments-fse' ), vitapro_appointments_get_option( 'reminder_lead_time_hours', 24 ) ); ?></p>
    <?php endif; ?>
<?php endif; ?>

<p><?php _e( 'If you have any questions or need to make changes to your appointment, please contact us.', 'vitapro-appointments-fse' ); ?></p>

<p><?php _e( 'Thank you for choosing our services!', 'vitapro-appointments-fse' ); ?></p>

<?php echo vitapro_render_email_template( 'email-footer', $args ); ?>