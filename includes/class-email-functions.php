<?php
/**
 * Email Functions
 * Handles advanced email functionality for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Email_Functions {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Estes hooks garantem que os e-mails sejam enviados automaticamente quando essas ações ocorrem.
        add_action('vitapro_appointment_created', array($this, 'send_new_appointment_emails_on_creation'), 10, 1);
        add_action('vitapro_appointment_status_changed', array($this, 'send_status_change_emails_on_status_change'), 10, 3);
        
        // Hook para o e-mail de lembrete (o cron job irá chamar um método público desta classe)
        // add_action('vitapro_send_appointment_reminder_hook', array($this, 'send_reminder_email'), 10, 1);

        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        add_action('wp_mail_failed', array($this, 'log_email_error'));
    }
    
    /**
     * Set HTML content type for emails
     */
    public function set_html_content_type() {
        return 'text/html';
    }
    
    /**
     * Wrapper para enviar e-mails de novo agendamento quando a ação 'vitapro_appointment_created' é disparada.
     */
    public function send_new_appointment_emails_on_creation($appointment_id) {
        $this->send_new_appointment_emails($appointment_id);
    }

    /**
     * Wrapper para enviar e-mails de mudança de status quando a ação 'vitapro_appointment_status_changed' é disparada.
     */
    public function send_status_change_emails_on_status_change($appointment_id, $new_status, $old_status) {
        // Evitar envio duplicado se a chamada já for de dentro desta classe
        if (did_action('vitapro_sending_' . $new_status . '_email_for_' . $appointment_id) > 1) {
            return false;
        }
        do_action('vitapro_sending_' . $new_status . '_email_for_' . $appointment_id);

        $this->send_status_change_emails($appointment_id, $new_status, $old_status);
    }

    /**
     * Send new appointment emails (admin and patient)
     */
    public function send_new_appointment_emails($appointment_id) {
        $appointment_data = $this->get_appointment_email_data($appointment_id);
        if (!$appointment_data) return false;

        $options = get_option('vitapro_appointments_settings', array());
        $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        $enable_admin_notification = isset($options['enable_admin_notification']) ? (bool)$options['enable_admin_notification'] : true;
        $enable_patient_confirmation = isset($options['enable_patient_confirmation']) ? (bool)$options['enable_patient_confirmation'] : true;


        if (!$send_emails) return false;

        $results = array();

        // Admin Notification
        if ($enable_admin_notification) {
            $admin_email = isset($options['email_admin_new_booking']) && !empty($options['email_admin_new_booking']) ? $options['email_admin_new_booking'] : get_option('admin_email');
            $subject_admin = sprintf(__('New Appointment Booking - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
            $message_admin = $this->render_email_template('new-booking-admin', $appointment_data);
            $results['admin'] = $this->send_email($admin_email, $subject_admin, $message_admin);
        }

        // Patient Confirmation
        if ($enable_patient_confirmation && !empty($appointment_data['patient_email'])) {
            $subject_patient = sprintf(__('Appointment Confirmation - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
            $message_patient = $this->render_email_template('new-booking-patient', $appointment_data);
            $attachments = array(); // Adicionar anexos .ics se necessário
            $results['patient'] = $this->send_email($appointment_data['patient_email'], $subject_patient, $message_patient, array(), $attachments);
        }
        
        // Não agendar lembrete aqui, o cron job fará isso periodicamente.
        return $results;
    }
    
    /**
     * Send status change emails
     */
    public function send_status_change_emails($appointment_id, $new_status, $old_status = '') {
        $appointment_data = $this->get_appointment_email_data($appointment_id);
        if (!$appointment_data) return false;

        $options = get_option('vitapro_appointments_settings', array());
        $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        if (!$send_emails) return false;

        $results = array();

        switch ($new_status) {
            case 'confirmed':
                // O e-mail de confirmação geralmente é o mesmo de novo agendamento se não for auto-aprovado.
                // Ou um e-mail específico de "Seu agendamento foi confirmado".
                // Por enquanto, vamos assumir que new-booking-patient serve.
                $subject_patient = sprintf(__('Appointment Confirmed - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
                $message_patient = $this->render_email_template('new-booking-patient', $appointment_data); // Pode precisar de um template 'booking-confirmed-patient'
                $results['patient_confirmed'] = $this->send_email($appointment_data['patient_email'], $subject_patient, $message_patient);
                break;
                
            case 'cancelled':
                $cancelled_by = current_user_can('manage_options') ? 'admin' : 'patient'; // Determinar quem cancelou
                $appointment_data['cancelled_by'] = $cancelled_by;
                $appointment_data['old_status'] = $old_status;

                // Admin Notification
                if (isset($options['enable_admin_notification']) && $options['enable_admin_notification']) {
                    $admin_email = isset($options['email_admin_new_booking']) && !empty($options['email_admin_new_booking']) ? $options['email_admin_new_booking'] : get_option('admin_email');
                    $subject_admin = sprintf(__('Appointment Cancelled - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
                    $message_admin = $this->render_email_template('cancellation-admin', $appointment_data);
                    $results['admin_cancelled'] = $this->send_email($admin_email, $subject_admin, $message_admin);
                }

                // Patient Notification
                if (!empty($appointment_data['patient_email'])) {
                    $subject_patient = sprintf(__('Appointment Cancellation Confirmation - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
                    $message_patient = $this->render_email_template('cancellation-patient', $appointment_data);
                    $results['patient_cancelled'] = $this->send_email($appointment_data['patient_email'], $subject_patient, $message_patient);
                }
                break;
                
            case 'completed':
                // Você pode querer enviar um e-mail de agradecimento ou pedido de feedback.
                // if (!empty($appointment_data['patient_email'])) {
                //     $subject_patient = sprintf(__('Appointment Completed - Thank You - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
                //     $message_patient = $this->render_email_template('completion-patient', $appointment_data); // Criar este template
                //     $results['patient_completed'] = $this->send_email($appointment_data['patient_email'], $subject_patient, $message_patient);
                // }
                break;
             default:
                 // Para outros status, pode enviar um e-mail genérico de atualização de status
                if (!empty($appointment_data['patient_email'])) {
                    $status_labels = array(
                        'pending'   => __('Pending', 'vitapro-appointments-fse'),
                        'confirmed' => __('Confirmed', 'vitapro-appointments-fse'),
                        'completed' => __('Completed', 'vitapro-appointments-fse'),
                        'cancelled' => __('Cancelled', 'vitapro-appointments-fse'),
                        'no_show'   => __('No Show', 'vitapro-appointments-fse'),
                    );
                    $appointment_data['new_status_label'] = isset($status_labels[$new_status]) ? $status_labels[$new_status] : ucfirst($new_status);

                    $subject_patient = sprintf(__('Appointment Status Update - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
                    $message_patient = $this->render_email_template('status-update-patient', $appointment_data); // Criar este template genérico
                    $results['patient_status_update'] = $this->send_email($appointment_data['patient_email'], $subject_patient, $message_patient);
                }
                break;
        }
        return $results;
    }
    
    /**
     * Send reminder email (Este será chamado pelo Cron Job)
     */
    public function send_reminder_email($appointment_id) {
        $appointment_data = $this->get_appointment_email_data($appointment_id);
        if (!$appointment_data || $appointment_data['status_raw'] === 'cancelled' || $appointment_data['status_raw'] === 'completed') {
            return false;
        }

        $options = get_option('vitapro_appointments_settings', array());
        $send_emails = isset($options['send_email_notifications']) ? (bool)$options['send_email_notifications'] : true;
        $enable_reminders = isset($options['enable_reminders']) ? (bool)$options['enable_reminders'] : false;

        if (!$send_emails || !$enable_reminders || empty($appointment_data['patient_email'])) {
            return false;
        }
        
        $subject = sprintf(__('Appointment Reminder - %s', 'vitapro-appointments-fse'), $appointment_data['service_name']);
        $message = $this->render_email_template('reminder-patient', $appointment_data);
        
        return $this->send_email($appointment_data['patient_email'], $subject, $message);
    }
    
    /**
     * Get comprehensive appointment data for email templates.
     */
    public function get_appointment_email_data($appointment_id) {
        global $wpdb;
        // Usar a tabela customizada para buscar os dados do agendamento
        $table_name = $wpdb->prefix . 'vpa_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id), ARRAY_A);

        if (!$appointment) {
            // Tentar buscar do post type como fallback se a tabela estiver vazia para um ID de post
            $post = get_post($appointment_id);
            if ($post && $post->post_type === 'vpa_appointment') {
                 $appointment = array(
                    'id' => $post->ID, // Mantém o ID do post
                    'service_id' => get_post_meta($post->ID, '_vpa_appointment_service_id', true),
                    'professional_id' => get_post_meta($post->ID, '_vpa_appointment_professional_id', true),
                    'customer_name' => get_post_meta($post->ID, '_vpa_appointment_patient_name', true),
                    'customer_email' => get_post_meta($post->ID, '_vpa_appointment_patient_email', true),
                    'customer_phone' => get_post_meta($post->ID, '_vpa_appointment_patient_phone', true),
                    'appointment_date' => get_post_meta($post->ID, '_vpa_appointment_date', true),
                    'appointment_time' => get_post_meta($post->ID, '_vpa_appointment_time', true),
                    'duration' => get_post_meta($post->ID, '_vpa_appointment_duration', true), // Pode não existir, pegar do serviço
                    'status' => get_post_meta($post->ID, '_vpa_appointment_status', true),
                    'notes' => $post->post_content, // Se as notas são o conteúdo do post
                    'custom_fields' => get_post_meta($post->ID, '_vpa_appointment_custom_fields_data', true), // Se for meta
                 );
            } else {
                return false;
            }
        }
        
        $service = get_post($appointment['service_id']);
        $professional = $appointment['professional_id'] ? get_post($appointment['professional_id']) : null;

        $options = get_option('vitapro_appointments_settings', array()); // Usando get_option
        $date_format = isset($options['date_format']) ? $options['date_format'] : get_option('date_format');
        $time_format = isset($options['time_format']) ? $options['time_format'] : get_option('time_format');

        $status_raw = $appointment['status'];
        $status_labels = array(
            'pending'   => __('Pending', 'vitapro-appointments-fse'),
            'confirmed' => __('Confirmed', 'vitapro-appointments-fse'),
            'completed' => __('Completed', 'vitapro-appointments-fse'),
            'cancelled' => __('Cancelled', 'vitapro-appointments-fse'),
            'no_show'   => __('No Show', 'vitapro-appointments-fse'),
        );
        $status_display = isset($status_labels[$status_raw]) ? $status_labels[$status_raw] : ucfirst($status_raw);

        $custom_fields_display = array();
        $defined_custom_fields = isset($options['custom_fields']) ? $options['custom_fields'] : array();
        $appointment_custom_fields = !empty($appointment['custom_fields']) ? json_decode($appointment['custom_fields'], true) : array();
        if (is_array($appointment_custom_fields) && !empty($defined_custom_fields)) {
            foreach($defined_custom_fields as $field_id => $field_settings) {
                if (isset($appointment_custom_fields[$field_id])) {
                    $custom_fields_display[$field_id] = array(
                        'label' => $field_settings['label'],
                        'value' => $appointment_custom_fields[$field_id],
                        'type'  => isset($field_settings['type']) ? $field_settings['type'] : 'text',
                    );
                }
            }
        }

        $service_duration = !empty($appointment['duration']) ? $appointment['duration'] : $this->get_service_duration($appointment['service_id']);


        return array(
            'appointment_id'     => $appointment['id'],
            'service_name'       => $service ? $service->post_title : __('Unknown Service', 'vitapro-appointments-fse'),
            'professional_name'  => $professional ? $professional->post_title : __('Any available professional', 'vitapro-appointments-fse'),
            'formatted_date'     => date_i18n($date_format, strtotime($appointment['appointment_date'])),
            'formatted_time'     => date_i18n($time_format, strtotime($appointment['appointment_time'])),
            'patient_name'       => $appointment['customer_name'],
            'patient_email'      => $appointment['customer_email'],
            'patient_phone'      => $appointment['customer_phone'],
            'status'             => $status_display, // Para exibição
            'status_raw'         => $status_raw,     // Para lógica interna
            'site_name'          => get_bloginfo('name'),
            'site_url'           => home_url(),
            'custom_fields'      => $custom_fields_display, // Array formatado de campos customizados
            'appointment_notes'  => $appointment['notes'],
            'duration'           => $service_duration . ' ' . __('minutes', 'vitapro-appointments-fse'),
            // Adicione quaisquer outros dados que seus templates possam precisar
        );
    }
    
    /**
     * Render email template.
     */
    public function render_email_template( $template_name, $args = array() ) {
        // Caminho para os templates dentro do plugin
        $template_path = VITAPRO_APPOINTMENTS_FSE_PATH . 'templates/email/' . $template_name . '.php';
        
        // Permitir que temas sobrescrevam templates
        $theme_template_path = get_stylesheet_directory() . '/vitapro-appointments/email/' . $template_name . '.php';
        if (file_exists($theme_template_path)) {
            $template_path = $theme_template_path;
        }
        
        if (!file_exists($template_path)) {
            // Log de erro ou fallback para um template padrão simples
            error_log("VitaPro Email Template not found: {$template_name}");
            return "Email template {$template_name} not found."; // Mensagem de erro simples
        }

        ob_start();
        // Definir $args como disponível para o template. Os templates usam extract($args).
        // É mais seguro acessar $args['key'] dentro dos templates.
        include $template_path; 
        return ob_get_clean();
    }
    
    /**
     * Send email using WordPress mail function.
     */
    public function send_email( $to, $subject, $message, $headers = array(), $attachments = array() ) {
        $options = get_option('vitapro_appointments_settings', array()); // Usar get_option

        $from_name = isset($options['email_from_name']) && !empty($options['email_from_name']) 
                     ? $options['email_from_name'] 
                     : get_bloginfo('name');
        $from_email = isset($options['email_from_address']) && !empty($options['email_from_address']) 
                      ? $options['email_from_address'] 
                      : get_option('admin_email');

        $default_headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $from_name . ' <' . $from_email . '>',
            'Reply-To: ' . $from_name . ' <' . $from_email . '>', // Adicionar Reply-To
        );

        $final_headers = array_merge($default_headers, (array)$headers); // Garantir que $headers seja um array

        // Integrar com configurações de SMTP se existirem (você precisaria de uma classe/função para isso)
        // add_action('phpmailer_init', array($this, 'configure_smtp'));
        
        $sent = wp_mail($to, $subject, $message, $final_headers, $attachments);
        
        // remove_action('phpmailer_init', array($this, 'configure_smtp')); // Remover após o envio

        if (!$sent) {
            $this->log_email_error_details($to, $subject, $final_headers);
        }
        return $sent;
    }

    /**
     * Log email error details.
     */
    private function log_email_error_details($to, $subject, $headers) {
        global $ts_mail_errors; // Variável global do WP para erros de e-mail
        global $phpmailer; // Objeto PHPMailer

        $error_message = 'VitaPro Appointments Email Error: Failed to send email.';
        if (is_array($ts_mail_errors) && !empty($ts_mail_errors)) {
            $error_message .= " WP Mail Errors: " . implode(", ", $ts_mail_errors);
        }
        if (isset($phpmailer) && is_object($phpmailer) && !empty($phpmailer->ErrorInfo)) {
            $error_message .= " PHPMailer Error: " . $phpmailer->ErrorInfo;
        }
        $error_message .= " | To: {$to} | Subject: {$subject} | Headers: " . implode("\r\n", $headers);
        error_log($error_message);
    }
    
    /**
     * Log email error (hook wp_mail_failed)
     */
    public function log_email_error($wp_error) {
        if ($wp_error instanceof WP_Error) {
            $error_messages = $wp_error->get_error_messages();
            error_log('VitaPro Appointments WP_Mail Failed: ' . implode('; ', $error_messages));
        }
    }

    // public function configure_smtp($phpmailer) {
    //     // Adicionar lógica de configuração SMTP aqui se necessário
    //     // $options = get_option('vitapro_smtp_settings');
    //     // if ($options['enabled']) {
    //     // $phpmailer->isSMTP();
    //     // $phpmailer->Host = $options['host'];
    //     // ...
    //     // }
    // }
}