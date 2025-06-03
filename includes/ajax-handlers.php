<?php
/**
 * AJAX Handlers
 * * Handles AJAX requests for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Ajax_Handlers {
    
    private $email_handler; // Para a classe de e-mail
    private $availability_logic; // Para a classe de lógica de disponibilidade

    /**
     * Constructor
     */
    public function __construct() {
        // Public AJAX actions
        add_action('wp_ajax_vpa_book_appointment', array($this, 'book_appointment'));
        add_action('wp_ajax_nopriv_vpa_book_appointment', array($this, 'book_appointment'));
        
        add_action('wp_ajax_vpa_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_nopriv_vpa_get_available_times', array($this, 'get_available_times'));
        
        // Adicionando o handler que faltava na classe
        add_action('wp_ajax_vitapro_get_professionals_for_service', array($this, 'get_professionals_for_service'));
        add_action('wp_ajax_nopriv_vitapro_get_professionals_for_service', array($this, 'get_professionals_for_service'));

        add_action('wp_ajax_vpa_cancel_appointment', array($this, 'cancel_appointment'));
        add_action('wp_ajax_nopriv_vpa_cancel_appointment', array($this, 'cancel_appointment'));
        
        // Admin AJAX actions
        add_action('wp_ajax_vpa_update_appointment_status', array($this, 'update_appointment_status'));

        // Instanciar handlers/logic se eles forem classes e não estáticos/singleton
        // Isso depende de como você estruturou as outras classes.
        // Exemplo, se VitaPro_Appointments_FSE_Email_Functions for um singleton:
        // if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) {
        //     $this->email_handler = VitaPro_Appointments_FSE_Email_Functions::get_instance(); 
        // }
        // Exemplo, se VitaPro_Appointments_FSE_Availability_Logic for um singleton:
        // if (class_exists('VitaPro_Appointments_FSE_Availability_Logic')) {
        //     $this->availability_logic = VitaPro_Appointments_FSE_Availability_Logic::get_instance();
        // }
    }
    
    /**
     * Book appointment
     */
    public function book_appointment() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $professional_id = isset($_POST['professional_id']) ? intval($_POST['professional_id']) : 0;
        $appointment_date = isset($_POST['appointment_date']) ? sanitize_text_field($_POST['appointment_date']) : '';
        $appointment_time = isset($_POST['appointment_time']) ? sanitize_text_field($_POST['appointment_time']) : '';
        $customer_name = isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '';
        $customer_email = isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '';
        $customer_phone = isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '';
        $appointment_notes = isset($_POST['appointment_notes']) ? sanitize_textarea_field($_POST['appointment_notes']) : '';
        
        if (empty($service_id) || empty($appointment_date) || empty($appointment_time) || empty($customer_name) || !is_email($customer_email)) {
            wp_send_json_error(array('message' => __('Please fill in all required fields and provide a valid email.', 'vitapro-appointments-fse')));
            return;
        }
        
        $service = get_post($service_id);
        if (!$service || $service->post_type !== 'vpa_service') {
            wp_send_json_error(array('message' => __('Invalid service selected.', 'vitapro-appointments-fse')));
            return;
        }

        if ($professional_id) {
            $professional = get_post($professional_id);
            if (!$professional || $professional->post_type !== 'vpa_professional') {
                wp_send_json_error(array('message' => __('Invalid professional selected.', 'vitapro-appointments-fse')));
                return;
            }
        }
        
        $duration = get_post_meta($service_id, '_vpa_service_duration', true);
        if (empty($duration)) {
            $options = get_option('vitapro_appointments_settings', array()); // Usando get_option diretamente
            $duration = isset($options['default_appointment_duration']) ? (int)$options['default_appointment_duration'] : 60;
        } else {
            $duration = (int)$duration;
        }

        // Em vez de $this->is_time_slot_available, usar a classe de lógica de disponibilidade
        $is_available = true; // Placeholder
        if (class_exists('VitaPro_Appointments_FSE_Availability_Logic') && method_exists('VitaPro_Appointments_FSE_Availability_Logic', 'is_time_slot_available_static')) {
            // Se você criar um método estático na classe de lógica
            // $is_available = VitaPro_Appointments_FSE_Availability_Logic::is_time_slot_available_static($service_id, $professional_id, $appointment_date, $appointment_time, $duration);
        } elseif (function_exists('vitapro_is_slot_booked')) { // Usando a função global do availability-logic.php
             $is_available = !vitapro_is_slot_booked( $professional_id, $appointment_date, $appointment_time, $duration );
        }


        if (!$is_available) {
            wp_send_json_error(array('message' => __('Sorry, this time slot is no longer available.', 'vitapro-appointments-fse')));
            return;
        }
        
        $options = get_option('vitapro_appointments_settings', array());
        $auto_confirm = isset($options['auto_confirm_appointments']) ? (bool)$options['auto_confirm_appointments'] : false;
        $status = $auto_confirm ? 'confirmed' : 'pending';
        
        $custom_fields_data = array();
        $defined_custom_fields = isset($options['custom_fields']) ? $options['custom_fields'] : array();

        if (!empty($defined_custom_fields) && isset($_POST['vpa_custom_fields']) && is_array($_POST['vpa_custom_fields'])) {
            foreach ($defined_custom_fields as $field_id => $field_settings) {
                if (isset($_POST['vpa_custom_fields'][$field_id])) {
                    $value = wp_unslash($_POST['vpa_custom_fields'][$field_id]);
                    // Adicionar sanitização mais específica se necessário
                    $value = ($field_settings['type'] === 'textarea') ? sanitize_textarea_field($value) : sanitize_text_field($value);
                    
                    if (!empty($field_settings['required']) && empty($value)) {
                        wp_send_json_error(array('message' => sprintf(__('The field "%s" is required.', 'vitapro-appointments-fse'), esc_html($field_settings['label']))));
                        return;
                    }
                    $custom_fields_data[$field_id] = $value;
                } elseif (!empty($field_settings['required'])) {
                     wp_send_json_error(array('message' => sprintf(__('The field "%s" is required.', 'vitapro-appointments-fse'), esc_html($field_settings['label']))));
                     return;
                }
            }
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'service_id' => $service_id,
                'professional_id' => $professional_id,
                'customer_name' => $customer_name,
                'customer_email' => $customer_email,
                'customer_phone' => $customer_phone,
                'appointment_date' => $appointment_date,
                'appointment_time' => $appointment_time,
                'duration' => $duration,
                'status' => $status,
                'notes' => $appointment_notes,
                'custom_fields' => !empty($custom_fields_data) ? json_encode($custom_fields_data) : null,
                'created_at' => current_time('mysql', 1), // GMT
            )
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to book appointment. Please try again. DB Error: ', 'vitapro-appointments-fse') . $wpdb->last_error));
            return;
        }
        
        $appointment_id = $wpdb->insert_id;
        
        // Usar a classe de e-mail para enviar notificações
        $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        if ($send_emails) {
            if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) {
                // Supondo que você tenha um singleton ou métodos estáticos para enviar e-mails
                // Exemplo com função global adaptada (como no seu email-functions.php)
                if (function_exists('vitapro_send_new_booking_emails')) {
                    vitapro_send_new_booking_emails($appointment_id);
                }
            }
        }
        
        do_action('vitapro_appointment_created', $appointment_id);
        
        $professional_post = $professional_id ? get_post($professional_id) : null;
        $professional_name = $professional_post ? $professional_post->post_title : __('Any available professional', 'vitapro-appointments-fse');

        $details_html = sprintf(
            '<p><strong>%s:</strong> %s</p><p><strong>%s:</strong> %s</p><p><strong>%s:</strong> %s</p><p><strong>%s:</strong> %s %s %s</p><p><strong>%s:</strong> %s</p>',
            __('Service', 'vitapro-appointments-fse'), esc_html($service->post_title),
            __('Professional', 'vitapro-appointments-fse'), esc_html($professional_name),
            __('Patient', 'vitapro-appointments-fse'), esc_html($customer_name),
            __('Date & Time', 'vitapro-appointments-fse'), date_i18n(get_option('date_format'), strtotime($appointment_date)), __('at', 'vitapro-appointments-fse'), date_i18n(get_option('time_format'), strtotime($appointment_time)),
            __('Status', 'vitapro-appointments-fse'), ($status === 'pending' ? __('Pending Approval', 'vitapro-appointments-fse') : __('Confirmed', 'vitapro-appointments-fse'))
        );
        if (!empty($custom_fields_data) && !empty($defined_custom_fields)) {
            foreach ($defined_custom_fields as $field_id => $field_settings) {
                if (isset($custom_fields_data[$field_id]) && !empty($custom_fields_data[$field_id])) {
                    $display_value = ($field_settings['type'] === 'textarea') ? nl2br(esc_html($custom_fields_data[$field_id])) : esc_html($custom_fields_data[$field_id]);
                    $details_html .= sprintf('<p><strong>%s:</strong> %s</p>', esc_html($field_settings['label']), $display_value);
                }
            }
        }


        wp_send_json_success(array(
            'message' => __('Appointment booked successfully!', 'vitapro-appointments-fse'),
            'appointment_id' => $appointment_id,
            'appointment_details' => $details_html,
            'status' => $status,
        ));
    }
    
    /**
     * Get available times
     */
    public function get_available_times() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) { // No seu frontend.js o nonce é 'vitapro_appointments_nonce'
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $professional_id = isset($_POST['professional_id']) ? intval($_POST['professional_id']) : 0; // Pode ser 0 se "qualquer profissional"
        $date = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        
        if (empty($service_id) || empty($date)) {
            wp_send_json_error(array('message' => __('Missing required parameters (service or date).', 'vitapro-appointments-fse')));
            return;
        }

        $service_duration = get_post_meta($service_id, '_vpa_service_duration', true);
        if (empty($service_duration)) {
            $options = get_option('vitapro_appointments_settings', array());
            $service_duration = isset($options['default_appointment_duration']) ? (int)$options['default_appointment_duration'] : 60;
        } else {
            $service_duration = (int)$service_duration;
        }
        
        $available_slots = array();
        // Usar a classe de lógica de disponibilidade ou a função global
        if (function_exists('vitapro_calculate_available_slots')) { // Do availability-logic.php
            $time_strings = vitapro_calculate_available_slots($date, $service_id, $professional_id, $service_duration);
            foreach($time_strings as $time_str) {
                $available_slots[] = array(
                    'value' => $time_str,
                    'label' => date_i18n(get_option('time_format'), strtotime($time_str))
                );
            }
        } else {
             wp_send_json_error(array('message' => __('Availability calculation function not found.', 'vitapro-appointments-fse')));
            return;
        }
        
        wp_send_json_success(array('slots' => $available_slots));
    }

    /**
     * Handle AJAX request to get professionals for a service.
     */
    public function get_professionals_for_service() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) { // Usando o mesmo nonce do frontend
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }

        $service_id = isset($_POST['service_id']) ? absint($_POST['service_id']) : 0;

        if (!$service_id) {
            wp_send_json_error(array('message' => __('Invalid service ID.', 'vitapro-appointments-fse')));
            return;
        }

        $professionals = get_posts(array(
            'post_type'      => 'vpa_professional',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_vpa_professional_services', // Certifique-se que este é o meta key correto
                    'value'   => '"' . $service_id . '"', // Se for um array serializado, precisa ser buscado assim
                    'compare' => 'LIKE',
                ),
            ),
        ));

        $professionals_data = array();
        if (!empty($professionals)) {
            foreach ($professionals as $professional) {
                // Verificação adicional se a meta é um array
                $assigned_services = get_post_meta($professional->ID, '_vpa_professional_services', true);
                if (is_array($assigned_services) && in_array((string)$service_id, $assigned_services)) {
                    $professionals_data[] = array(
                        'id'   => $professional->ID,
                        'name' => $professional->post_title,
                        // Adicionar mais dados se necessário, como imagem, título, etc.
                    );
                }
            }
        }
        // Adicionar opção "Qualquer profissional" se aplicável
        // $professionals_data[] = array('id' => 0, 'name' => __('Any Available Professional', 'vitapro-appointments-fse'));


        wp_send_json_success(array('professionals' => $professionals_data));
    }
    
    /**
     * Cancel appointment
     */
    public function cancel_appointment() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
        
        if (empty($appointment_id)) {
            wp_send_json_error(array('message' => __('Invalid appointment ID.', 'vitapro-appointments-fse')));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id));
        
        if (!$appointment) {
            wp_send_json_error(array('message' => __('Appointment not found.', 'vitapro-appointments-fse')));
            return;
        }
        
        $can_cancel = false;
        if (is_user_logged_in()) {
            $current_user = wp_get_current_user();
            if ($current_user->user_email === $appointment->customer_email || current_user_can('manage_options')) {
                $can_cancel = true;
            }
        }
        
        if (!$can_cancel) {
            wp_send_json_error(array('message' => __('You do not have permission to cancel this appointment.', 'vitapro-appointments-fse')));
            return;
        }
        
        $options = get_option('vitapro_appointments_settings', array());
        $cancellation_limit = isset($options['cancellation_time_limit']) ? (int)$options['cancellation_time_limit'] : 24;
        
        $appointment_datetime = strtotime($appointment->appointment_date . ' ' . $appointment->appointment_time);
        $current_time_wp = current_time('timestamp'); // Usar current_time para respeitar o fuso do WP
        
        if (($appointment_datetime - $current_time_wp) < ($cancellation_limit * 3600) && !current_user_can('manage_options')) {
            wp_send_json_error(array('message' => sprintf(__('Appointments can only be cancelled at least %d hours in advance.', 'vitapro-appointments-fse'), $cancellation_limit)));
            return;
        }
        
        $old_status = $appointment->status;
        $result = $wpdb->update(
            $table_name,
            array('status' => 'cancelled', 'updated_at' => current_time('mysql', 1)), // GMT
            array('id' => $appointment_id)
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to cancel appointment. Please try again.', 'vitapro-appointments-fse')));
            return;
        }
        
        $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        if ($send_emails) {
            if (function_exists('vitapro_send_cancellation_emails')) { // Do email-functions.php
                vitapro_send_cancellation_emails($appointment_id, (current_user_can('manage_options') ? 'admin' : 'patient'), $old_status);
            }
        }
        
        do_action('vitapro_appointment_status_changed', $appointment_id, 'cancelled', $old_status);
        wp_send_json_success(array('message' => __('Appointment cancelled successfully.', 'vitapro-appointments-fse')));
    }
    
    /**
     * Update appointment status (admin only)
     */
    public function update_appointment_status() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('You do not have permission to perform this action.', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        // O nonce para admin pode ser diferente, ex: 'vitapro_appointments_admin_nonce'
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_admin_nonce')) { 
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $appointment_id = isset($_POST['appointment_id']) ? intval($_POST['appointment_id']) : 0;
        $new_status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
        
        if (empty($appointment_id) || empty($new_status)) {
            wp_send_json_error(array('message' => __('Missing required parameters.', 'vitapro-appointments-fse')));
            return;
        }
        
        $valid_statuses = array('pending', 'confirmed', 'cancelled', 'completed', 'no-show'); // Adicionado no-show
        if (!in_array($new_status, $valid_statuses)) {
            wp_send_json_error(array('message' => __('Invalid status.', 'vitapro-appointments-fse')));
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id));
        
        if (!$appointment) {
            wp_send_json_error(array('message' => __('Appointment not found.', 'vitapro-appointments-fse')));
            return;
        }
        
        $old_status = $appointment->status;
        
        $result = $wpdb->update(
            $table_name,
            array('status' => $new_status, 'updated_at' => current_time('mysql', 1)), // GMT
            array('id' => $appointment_id)
        );
        
        if ($result === false) {
            wp_send_json_error(array('message' => __('Failed to update appointment status.', 'vitapro-appointments-fse')));
            return;
        }
        
        // Enviar e-mail de mudança de status se relevante (ex: confirmado, cancelado)
        if ($new_status !== $old_status && ($new_status === 'confirmed' || $new_status === 'cancelled')) {
             if (function_exists('vitapro_send_status_change_email')) { // Do email-functions.php
                vitapro_send_status_change_email($appointment_id, $new_status);
             }
        }

        do_action('vitapro_appointment_status_changed', $appointment_id, $new_status, $old_status);
        wp_send_json_success(array('message' => __('Appointment status updated successfully.', 'vitapro-appointments-fse')));
    }
    
    // Os métodos privados da classe original (is_time_slot_available, get_available_time_slots, send_new_booking_admin_email, etc.)
    // foram removidos pois suas lógicas serão idealmente chamadas de outras classes dedicadas (Availability_Logic, Email_Functions).
    // As chamadas para essas lógicas foram ajustadas acima para refletir isso, usando verificações function_exists como placeholders.
}