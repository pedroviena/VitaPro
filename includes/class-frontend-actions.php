<?php
/**
 * Frontend Actions
 * * Handles frontend actions for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Frontend_Actions {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_frontend_appointment_actions'));
        add_action('wp', array($this, 'maybe_display_frontend_messages'));
    }

    /**
     * Handle frontend appointment actions (like cancellation).
     */
    public function handle_frontend_appointment_actions() {
        if (!isset($_GET['action'])) {
            return;
        }

        $action = sanitize_text_field($_GET['action']);

        switch ($action) {
            case 'cancel_appointment':
                $this->process_patient_cancellation();
                break;
        }
    }

    /**
     * Process patient appointment cancellation.
     */
    private function process_patient_cancellation() {
        if (!isset($_GET['appointment_id']) || !isset($_GET['nonce'])) {
            return;
        }

        $appointment_id = absint($_GET['appointment_id']);
        $nonce = sanitize_text_field($_GET['nonce']);

        // Verify nonce
        if (!wp_verify_nonce($nonce, 'cancel_appointment_' . $appointment_id)) {
            wp_die(__('Security check failed.', 'vitapro-appointments-fse'));
        }

        // Check if appointment exists
        $appointment = get_post($appointment_id);
        if (!$appointment || $appointment->post_type !== 'vpa_appointment') {
            wp_die(__('Appointment not found.', 'vitapro-appointments-fse'));
        }

        // Check if cancellation is allowed
        // Supondo que vitapro_can_patient_cancel_appointment é uma função helper global ou de outra classe
        // Se for desta classe, seria $this->can_patient_cancel_appointment()
        if (!function_exists('vitapro_can_patient_cancel_appointment') || !vitapro_can_patient_cancel_appointment($appointment_id)) {
             // Se a função estiver em 'includes/common/helpers.php', ela deve estar carregada
            // Se não, você precisará garantir que ela esteja acessível ou movê-la para esta classe.
            // Por enquanto, vamos assumir que ela está disponível globalmente.
             wp_die(__('This appointment cannot be cancelled.', 'vitapro-appointments-fse'));
        }


        // Check if user has permission
        $patient_email = get_post_meta($appointment_id, '_vpa_appointment_patient_email', true);
        $current_user = wp_get_current_user();
        
        $has_permission = false;
        if (is_user_logged_in() && $current_user->user_email === $patient_email) {
            $has_permission = true;
        }

        if (!$has_permission) {
            wp_die(__('You do not have permission to cancel this appointment.', 'vitapro-appointments-fse'));
        }

        $old_status = get_post_meta($appointment_id, '_vpa_appointment_status', true);
        update_post_meta($appointment_id, '_vpa_appointment_status', 'cancelled');

        // Send cancellation emails
        // Supondo que vitapro_send_cancellation_emails é uma função helper global ou de outra classe (provavelmente email-functions)
        if (function_exists('vitapro_send_cancellation_emails')) {
            vitapro_send_cancellation_emails($appointment_id, 'patient', $old_status);
        }


        // Set success message
        $redirect_url = add_query_arg(array(
            'vpa_message' => 'appointment_cancelled',
            'appointment_id' => $appointment_id,
        ), wp_get_referer() ? wp_get_referer() : home_url());

        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Maybe display frontend messages.
     */
    public function maybe_display_frontend_messages() {
        if (isset($_GET['vpa_message'])) {
            add_filter('the_content', array($this, 'display_cancellation_message_in_content'));
        }
    }

    /**
     * Display cancellation message in content.
     */
    public function display_cancellation_message_in_content($content) {
        if (!isset($_GET['vpa_message'])) {
            return $content;
        }

        $message_type = sanitize_text_field($_GET['vpa_message']);
        $message = '';

        switch ($message_type) {
            case 'appointment_cancelled':
                $message = '<div class="vpa-message vpa-message-success">'; // Garanta que estas classes CSS existem em frontend.css
                $message .= '<p>' . __('Your appointment has been successfully cancelled. You will receive a confirmation email shortly.', 'vitapro-appointments-fse') . '</p>';
                $message .= '</div>';
                break;
        }

        return $message . $content;
    }
}