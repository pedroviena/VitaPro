<?php
/**
 * Frontend Actions
 * Handles frontend actions for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Frontend_Actions
 *
 * Handles frontend actions for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Frontend_Actions {
    
    private $email_functions_instance; // Adicionado

    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_frontend_appointment_actions'));
        add_action('wp', array($this, 'maybe_display_frontend_messages'));

        if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) { // Instanciar Email Functions
            $this->email_functions_instance = new VitaPro_Appointments_FSE_Email_Functions();
        }
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

        if (!wp_verify_nonce($nonce, 'cancel_appointment_' . $appointment_id)) {
            wp_die(__('Security check failed.', 'vitapro-appointments-fse'));
        }

        $appointment = $this->get_appointment_from_custom_table($appointment_id);
        if (!$appointment) {
            wp_die(__('Appointment not found.', 'vitapro-appointments-fse'));
        }
        $patient_email_meta = $appointment->customer_email;
        $old_status_meta = $appointment->status;


        // Idealmente, vitapro_can_patient_cancel_appointment seria um método de Availability_Logic ou desta classe.
        if (!function_exists('vitapro_can_patient_cancel_appointment') || !vitapro_can_patient_cancel_appointment($appointment_id)) {
             wp_die(__('This appointment cannot be cancelled at this time.', 'vitapro-appointments-fse'));
        }

        $current_user = wp_get_current_user();
        $has_permission = false;
        if (is_user_logged_in() && $current_user->user_email === $patient_email_meta) {
            $has_permission = true;
        }
        // Adicionar lógica para link de cancelamento anônimo se necessário (com token seguro)

        if (!$has_permission) {
            wp_die(__('You do not have permission to cancel this appointment.', 'vitapro-appointments-fse'));
        }

        // Atualizar na tabela customizada
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        $wpdb->update(
            $table_name,
            array('status' => 'cancelled', 'updated_at' => current_time('mysql', 1)),
            array('id' => $appointment_id) // Assumindo que o ID do post é o ID da tabela
        );


        // A classe VitaPro_Appointments_FSE_Email_Functions já escuta a ação 'vitapro_appointment_status_changed'.
        // O do_action abaixo deve ser suficiente para disparar os e-mails de cancelamento.
        do_action('vitapro_appointment_status_changed', $appointment_id, 'cancelled', $old_status_meta);
        
        // Remover a chamada direta de e-mail, se houver, para evitar duplicidade:
        // $options = get_option('vitapro_appointments_settings', array());
        // $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        // if ($send_emails && $this->email_functions_instance) {
        //    $this->email_functions_instance->send_status_change_emails($appointment_id, 'cancelled', $old_status_meta);
        // }

        $redirect_url = add_query_arg(array(
            'vpa_message' => 'appointment_cancelled',
            'appointment_id' => $appointment_id,
        ), wp_get_referer() ? wp_get_referer() : home_url());

        wp_redirect($redirect_url);
        exit;
    }
    
    // Função auxiliar para buscar da tabela customizada, se necessário
    private function get_appointment_from_custom_table($appointment_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        return $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id));
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
                $message = '<div class="vpa-message vpa-message-success">';
                $message .= '<p>' . __('Your appointment has been successfully cancelled. You will receive a confirmation email shortly.', 'vitapro-appointments-fse') . '</p>';
                $message .= '</div>';
                break;
        }

        return $message . $content;
    }
}