<?php
/**
 * AJAX Handlers
 * Handles AJAX requests for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Ajax_Handlers {
    
    private $availability_logic_instance;
    private $email_functions_instance; // Adicionado

    /**
     * Constructor
     */
    public function __construct() {
        // ... (hooks AJAX existentes) ...
        add_action('wp_ajax_vpa_book_appointment', array($this, 'book_appointment'));
        add_action('wp_ajax_nopriv_vpa_book_appointment', array($this, 'book_appointment'));
        add_action('wp_ajax_vpa_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_nopriv_vpa_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_vitapro_get_professionals_for_service', array($this, 'get_professionals_for_service'));
        add_action('wp_ajax_nopriv_vitapro_get_professionals_for_service', array($this, 'get_professionals_for_service'));
        add_action('wp_ajax_vpa_cancel_appointment', array($this, 'cancel_appointment'));
        add_action('wp_ajax_nopriv_vpa_cancel_appointment', array($this, 'cancel_appointment'));
        add_action('wp_ajax_vpa_update_appointment_status', array($this, 'update_appointment_status'));


        if (class_exists('VitaPro_Appointments_FSE_Availability_Logic')) {
            $this->availability_logic_instance = new VitaPro_Appointments_FSE_Availability_Logic();
        }
        if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) { // Instanciar Email Functions
            $this->email_functions_instance = new VitaPro_Appointments_FSE_Email_Functions();
        }
    }
    
    public function book_appointment() {
        // ... (início do método book_appointment) ...
        
        $appointment_id = $wpdb->insert_id;
        
        $options = get_option('vitapro_appointments_settings', array()); // Obter opções aqui
        $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        
        if ($send_emails && $this->email_functions_instance) { // Usar a instância da classe de e-mail
            $this->email_functions_instance->send_new_appointment_emails($appointment_id);
        }
        
        do_action('vitapro_appointment_created', $appointment_id); // Este hook já é usado pela classe de Email
        
        // ... (resto do método book_appointment com a geração do $details_html) ...

        wp_send_json_success(array(
            'message' => __('Appointment booked successfully!', 'vitapro-appointments-fse'),
            'appointment_id' => $appointment_id,
            'appointment_details' => $details_html, // Certifique-se que $details_html é gerado antes
            'status' => $status,
        ));
    }
    
    public function cancel_appointment() {
        // ... (início do método cancel_appointment) ...
        
        $old_status = $appointment->status; // Guardar status antigo
        // ... (lógica de atualização do status para 'cancelled') ...
        
        // A classe VitaPro_Appointments_FSE_Email_Functions já escuta a ação 'vitapro_appointment_status_changed'.
        // Então, o do_action abaixo deve ser suficiente para disparar os e-mails de cancelamento.
        do_action('vitapro_appointment_status_changed', $appointment_id, 'cancelled', $old_status);
        
        // Remover chamadas diretas de e-mail daqui, se existirem, para evitar duplicidade.
        // $options = get_option('vitapro_appointments_settings', array());
        // $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        // if ($send_emails && $this->email_functions_instance) {
        //     $this->email_functions_instance->send_status_change_emails($appointment_id, 'cancelled', $old_status);
        // }
        
        wp_send_json_success(array('message' => __('Appointment cancelled successfully.', 'vitapro-appointments-fse')));
    }
    
    public function update_appointment_status() {
        // ... (início do método update_appointment_status) ...
        
        // A classe VitaPro_Appointments_FSE_Email_Functions já escuta a ação 'vitapro_appointment_status_changed'.
        do_action('vitapro_appointment_status_changed', $appointment_id, $new_status, $old_status);
        
        // Remover chamadas diretas de e-mail daqui para e-mails de 'confirmed' ou 'cancelled',
        // pois o hook acima já deve cuidar disso através da classe de e-mail.
        // $options = get_option('vitapro_appointments_settings', array());
        // $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        // if ($send_emails && $this->email_functions_instance && $new_status !== $old_status && ($new_status === 'confirmed' || $new_status === 'cancelled')) {
        //      $this->email_functions_instance->send_status_change_emails($appointment_id, $new_status, $old_status);
        // }
        
        wp_send_json_success(array('message' => __('Appointment status updated successfully.', 'vitapro-appointments-fse')));
    }

    // Os outros métodos (get_available_times, get_professionals_for_service) não enviam e-mails diretamente.
    // ...
}