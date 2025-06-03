<?php
/**
 * Email Template: Appointment Cancellation Notification for Admin
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

<p><?php printf( __( 'An appointment has been cancelled %s.', 'vitapro-appointments-fse' ), $cancelled_by === 'patient' ? __( 'by the patient', 'vitapro-appointments-fse' ) : __( 'by admin', 'vitapro-appointments-fse' ) ); ?></p>

<div class="appointment-details">
    <h3><?php _e( 'Cancelled Appointment Details', 'vitapro-appointments-fse' ); ?></h3>
    <ul>
        <li><strong><?php _e( 'Service:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $service_name ); ?></li>
        <li><strong><?php _e( 'Professional:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $professional_name ); ?></li>
        <li><strong><?php _e( 'Date:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_date ); ?></li>
        <li><strong><?php _e( 'Time:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $formatted_time ); ?></li>
        <li><strong><?php _e( 'Patient:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( $patient_name ); ?> (<?php echo esc_html( $patient_email ); ?>)</li>
        <li><strong><?php _e( 'Reference:', 'vitapro-appointments-fse' ); ?></strong> <?php echo esc_html( vitapro_generate_appointment_reference( $appointment_id ) ); ?></li>
    </ul>
</div>

<?php if ( ! empty( $custom_fields ) ) : ?>
<div class="custom-fields">
    <h3><?php _e( 'Patient Information', 'vitapro-appointments-fse' ); ?></h3>
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

<p>
    <a href="<?php echo esc_url( admin_url( 'post.php?post=' . $appointment_id . '&action=edit' ) ); ?>" 
       style="background-color: var(--wp--preset--color--primary, #0073aa); color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;">
        <?php _e( 'View Appointment in Admin', 'vitapro-appointments-fse' ); ?>
    </a>
</p>

<?php echo vitapro_render_email_template( 'email-footer', $args ); ?>