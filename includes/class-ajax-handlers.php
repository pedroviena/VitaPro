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
        if (
            !isset($_POST['nonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'vpa_book_appointment_nonce')
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';

        $data = array(
            'service_id'      => intval($_POST['service_id']),
            'professional_id' => intval($_POST['professional_id']),
            'customer_name'   => sanitize_text_field($_POST['customer_name']),
            'customer_email'  => sanitize_email($_POST['customer_email']),
            'customer_phone'  => sanitize_text_field($_POST['customer_phone']),
            'appointment_date'=> sanitize_text_field($_POST['appointment_date']),
            'appointment_time'=> sanitize_text_field($_POST['appointment_time']),
            'status'          => 'pending',
            'notes'           => sanitize_textarea_field($_POST['appointment_notes']),
            'created_at'      => current_time('mysql', 1),
            'updated_at'      => current_time('mysql', 1),
            // ...custom_fields...
        );

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
            'message' => __('Appointment booked successfully!', 'vitapro-appointments-fse'),
            'appointment_id' => $appointment_id,
            'appointment_details' => $details_html,
            'status' => 'pending',
        ));
    }
    
    public function cancel_appointment() {
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        if (
            !isset($_POST['nonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'vpa_cancel_appointment_nonce')
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $appointment_id = intval($_POST['appointment_id']);

        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $appointment_id));
        if (!$appointment) {
            wp_send_json_error(__('Appointment not found.', 'vitapro-appointments-fse'));
        }
        $old_status = $appointment->status;

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
        if (
            !isset($_POST['nonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'vpa_update_appointment_status_nonce')
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $appointment_id = intval($_POST['appointment_id']);
        $new_status = sanitize_text_field($_POST['new_status']);

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