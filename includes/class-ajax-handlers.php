<?php
/**
 * Class VitaPro_Appointments_FSE_Ajax_Handlers
 *
 * Handles AJAX requests for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
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
            if (method_exists('VitaPro_Appointments_FSE_Email_Functions', 'get_instance')) {
                $this->email_functions_instance = VitaPro_Appointments_FSE_Email_Functions::get_instance();
            } else {
                // Fallback: instancia diretamente (não recomendado se houver hooks no construtor)
                $this->email_functions_instance = new VitaPro_Appointments_FSE_Email_Functions();
            }
        }
    }
    
    public function book_appointment() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (
            ! $nonce ||
            !( wp_verify_nonce($nonce, VITAPRO_FRONTEND_NONCE) || wp_verify_nonce($nonce, VITAPRO_ADMIN_NONCE) )
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';

        // Validação de data e hora
        $appointment_date = isset($_POST['appointment_date']) ? sanitize_text_field($_POST['appointment_date']) : '';
        $appointment_time = isset($_POST['appointment_time']) ? sanitize_text_field($_POST['appointment_time']) : '';
        $date_valid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $appointment_date) && strtotime($appointment_date);
        $time_valid = preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $appointment_time);

        if (!$date_valid || !$time_valid) {
            wp_send_json_error(__('Invalid date or time format. Please select a valid date and time.', 'vitapro-appointments-fse'));
        }

        // Sanitização e validação de campos personalizados
        $custom_fields = array();
        $defined_custom_fields = get_option('vitapro_appointments_settings', array());
        $defined_custom_fields = isset($defined_custom_fields['custom_fields']) ? $defined_custom_fields['custom_fields'] : array();
        foreach ($defined_custom_fields as $field) {
            $field_key = 'custom_field_' . sanitize_key($field['name']);
            if (isset($_POST[$field_key])) {
                $value = $_POST[$field_key];
                switch ($field['type']) {
                    case 'email':
                        $value = sanitize_email($value);
                        if (!is_email($value)) $value = '';
                        break;
                    case 'tel':
                        $value = preg_replace('/[^0-9+\s\-]/', '', $value);
                        break;
                    case 'textarea':
                        $value = sanitize_textarea_field($value);
                        break;
                    case 'select':
                    case 'radio':
                        $value = sanitize_text_field($value);
                        break;
                    case 'checkbox':
                        $value = $value ? 1 : 0;
                        break;
                    default:
                        $value = sanitize_text_field($value);
                }
                // Validação obrigatória
                if (!empty($field['required']) && empty($value)) {
                    wp_send_json_error(sprintf(__('Field "%s" is required. Please fill in this field.', 'vitapro-appointments-fse'), $field['label']));
                }
                $custom_fields[$field['name']] = $value;
            } elseif (!empty($field['required'])) {
                wp_send_json_error(sprintf(__('Field "%s" is required.', 'vitapro-appointments-fse'), $field['label']));
            }
        }

        $data = array(
            'service_id'      => isset($_POST['service_id']) ? intval($_POST['service_id']) : 0,
            'professional_id' => isset($_POST['professional_id']) ? intval($_POST['professional_id']) : 0,
            'customer_name'   => isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '',
            'customer_email'  => isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '',
            'customer_phone'  => isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '',
            'appointment_date'=> $appointment_date,
            'appointment_time'=> $appointment_time,
            'status'          => 'pending',
            'notes'           => isset($_POST['appointment_notes']) ? sanitize_textarea_field($_POST['appointment_notes']) : '',
            'created_at'      => current_time('mysql', 1),
            'updated_at'      => current_time('mysql', 1),
            'custom_fields'   => !empty($custom_fields) ? wp_json_encode($custom_fields) : null,
        );

        // Validação extra de e-mail
        if (empty($data['customer_email']) || !is_email($data['customer_email'])) {
            wp_send_json_error(__('A valid email address is required. Please enter your email.', 'vitapro-appointments-fse'));
        }

        $wpdb->insert($table, $data);
        $appointment_id = $wpdb->insert_id;

        // Crie o CPT apenas como ponte/admin
        $cpt_id = wp_insert_post(array(
            'post_type'   => 'vpa_appointment',
            'post_status' => 'publish',
            'post_title'  => $data['customer_name'] . ' - ' . get_the_title($data['service_id']) . ' - ' . $data['appointment_date'],
            'meta_input'  => array('_vpa_custom_table_id' => $appointment_id),
        ));

        do_action('vitapro_appointment_created', $appointment_id);

        // ...existing code...
        wp_send_json_success(array(
            'message' => __('Appointment booked successfully! You will receive a confirmation email soon.', 'vitapro-appointments-fse'),
            'appointment_id' => $appointment_id,
            'appointment_details' => $details_html,
            'status' => 'pending',
        ));
    }
    
    public function cancel_appointment() {
        // Permitir cancelamento tanto para admin quanto para o paciente autenticado
        if (!is_user_logged_in() && !isset($_POST['frontend'])) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (
            ! $nonce ||
            !( wp_verify_nonce($nonce, VITAPRO_FRONTEND_NONCE) || wp_verify_nonce($nonce, VITAPRO_ADMIN_NONCE) )
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;

        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $appointment_id));
        if (!$appointment) {
            wp_send_json_error(__('Appointment not found.', 'vitapro-appointments-fse'));
        }
        $old_status = $appointment->status;

        // Se for frontend, verifique se o usuário pode cancelar
        if (isset($_POST['frontend']) && $_POST['frontend'] == '1') {
            $current_user = wp_get_current_user();
            if (!$current_user || $current_user->user_email !== $appointment->customer_email) {
                wp_send_json_error(__('You do not have permission to cancel this appointment.', 'vitapro-appointments-fse'));
            }
            if (!function_exists('vitapro_can_patient_cancel_appointment') || !vitapro_can_patient_cancel_appointment($appointment_id)) {
                wp_send_json_error(__('This appointment cannot be cancelled at this time.', 'vitapro-appointments-fse'));
            }
        }

        $wpdb->update($table, array(
            'status' => 'cancelled',
            'updated_at' => current_time('mysql', 1),
        ), array('id' => $appointment_id));

        do_action('vitapro_appointment_status_changed', $appointment_id, 'cancelled', $old_status);

        wp_send_json_success(array('message' => __('Appointment cancelled successfully.', 'vitapro-appointments-fse')));
    }
    
    public function update_appointment_status() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (
            ! $nonce ||
            !( wp_verify_nonce($nonce, VITAPRO_FRONTEND_NONCE) || wp_verify_nonce($nonce, VITAPRO_ADMIN_NONCE) )
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
        $new_status = isset($_POST['new_status']) ? sanitize_text_field($_POST['new_status']) : '';

        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $appointment_id));
        if (!$appointment) {
            wp_send_json_error(__('Appointment not found.', 'vitapro-appointments-fse'));
        }
        $old_status = $appointment->status;

        $wpdb->update($table, array(
            'status' => $new_status,
            'updated_at' => current_time('mysql', 1),
        ), array('id' => $appointment_id));

        do_action('vitapro_appointment_status_changed', $appointment_id, $new_status, $old_status);

        wp_send_json_success(array('message' => __('Appointment status updated successfully.', 'vitapro-appointments-fse')));
    }

    // Os outros métodos (get_available_times, get_professionals_for_service) não enviam e-mails diretamente.
    // ...
}