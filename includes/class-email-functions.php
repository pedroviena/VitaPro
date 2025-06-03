<?php
/**
 * Email Functions
 * 
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
        add_action('vitapro_appointment_created', array($this, 'send_new_appointment_emails'));
        add_action('vitapro_appointment_status_changed', array($this, 'send_status_change_emails'), 10, 3);
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
     * Send new appointment emails
     */
    public function send_new_appointment_emails($appointment_id) {
        $appointment = $this->get_appointment_data($appointment_id);
        
        if (!$appointment) {
            return false;
        }
        
        $general_settings = get_option('vitapro_appointments_general_settings', array());
        $send_emails = isset($general_settings['send_email_notifications']) ? $general_settings['send_email_notifications'] : true;
        
        if (!$send_emails) {
            return false;
        }
        
        // Send admin notification
        $this->send_admin_new_booking_email($appointment);
        
        // Send customer confirmation
        $this->send_customer_confirmation_email($appointment);
        
        // Schedule reminder email
        $this->schedule_reminder_email($appointment);
        
        return true;
    }
    
    /**
     * Send status change emails
     */
    public function send_status_change_emails($appointment_id, $new_status, $old_status) {
        $appointment = $this->get_appointment_data($appointment_id);
        
        if (!$appointment) {
            return false;
        }
        
        switch ($new_status) {
            case 'confirmed':
                $this->send_customer_confirmation_email($appointment);
                break;
                
            case 'cancelled':
                $this->send_cancellation_emails($appointment);
                break;
                
            case 'completed':
                $this->send_completion_emails($appointment);
                break;
        }
        
        return true;
    }
    
    /**
     * Send admin new booking email
     */
    private function send_admin_new_booking_email($appointment) {
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        
        $to = isset($email_settings['admin_notification_email']) ? $email_settings['admin_notification_email'] : get_option('admin_email');
        $subject = isset($email_settings['new_booking_admin_subject']) ? $email_settings['new_booking_admin_subject'] : __('New Appointment Booking', 'vitapro-appointments-fse');
        
        $template_data = $this->prepare_email_template_data($appointment);
        $message = $this->load_email_template('new-booking-admin', $template_data);
        
        $headers = $this->get_email_headers();
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Send customer confirmation email
     */
    private function send_customer_confirmation_email($appointment) {
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        
        $to = $appointment['customer_email'];
        $subject = isset($email_settings['new_booking_patient_subject']) ? $email_settings['new_booking_patient_subject'] : __('Appointment Confirmation', 'vitapro-appointments-fse');
        
        $template_data = $this->prepare_email_template_data($appointment);
        $message = $this->load_email_template('new-booking-patient', $template_data);
        
        $headers = $this->get_email_headers();
        
        // Add calendar attachment
        $ics_file = $this->generate_calendar_attachment($appointment);
        $attachments = $ics_file ? array($ics_file) : array();
        
        $result = wp_mail($to, $subject, $message, $headers, $attachments);
        
        // Clean up temporary file
        if ($ics_file && file_exists($ics_file)) {
            unlink($ics_file);
        }
        
        return $result;
    }
    
    /**
     * Send cancellation emails
     */
    private function send_cancellation_emails($appointment) {
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        
        // Send to admin
        $admin_email = isset($email_settings['admin_notification_email']) ? $email_settings['admin_notification_email'] : get_option('admin_email');
        $admin_subject = isset($email_settings['cancellation_admin_subject']) ? $email_settings['cancellation_admin_subject'] : __('Appointment Cancelled', 'vitapro-appointments-fse');
        
        $template_data = $this->prepare_email_template_data($appointment);
        $admin_message = $this->load_email_template('cancellation-admin', $template_data);
        
        $headers = $this->get_email_headers();
        wp_mail($admin_email, $admin_subject, $admin_message, $headers);
        
        // Send to customer
        $customer_subject = isset($email_settings['cancellation_patient_subject']) ? $email_settings['cancellation_patient_subject'] : __('Appointment Cancellation Confirmation', 'vitapro-appointments-fse');
        $customer_message = $this->load_email_template('cancellation-patient', $template_data);
        
        return wp_mail($appointment['customer_email'], $customer_subject, $customer_message, $headers);
    }
    
    /**
     * Send completion emails
     */
    private function send_completion_emails($appointment) {
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        
        $to = $appointment['customer_email'];
        $subject = isset($email_settings['completion_patient_subject']) ? $email_settings['completion_patient_subject'] : __('Appointment Completed - Thank You', 'vitapro-appointments-fse');
        
        $template_data = $this->prepare_email_template_data($appointment);
        $message = $this->load_email_template('completion-patient', $template_data);
        
        $headers = $this->get_email_headers();
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Schedule reminder email
     */
    private function schedule_reminder_email($appointment) {
        $general_settings = get_option('vitapro_appointments_general_settings', array());
        $reminder_hours = isset($general_settings['reminder_hours_before']) ? $general_settings['reminder_hours_before'] : 24;
        
        $appointment_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
        $reminder_time = $appointment_datetime - ($reminder_hours * 3600);
        
        // Only schedule if reminder time is in the future
        if ($reminder_time > current_time('timestamp')) {
            wp_schedule_single_event($reminder_time, 'vitapro_send_appointment_reminder', array($appointment['id']));
        }
    }
    
    /**
     * Send reminder email
     */
    public function send_reminder_email($appointment_id) {
        $appointment = $this->get_appointment_data($appointment_id);
        
        if (!$appointment || $appointment['status'] === 'cancelled') {
            return false;
        }
        
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        
        $to = $appointment['customer_email'];
        $subject = isset($email_settings['reminder_patient_subject']) ? $email_settings['reminder_patient_subject'] : __('Appointment Reminder', 'vitapro-appointments-fse');
        
        $template_data = $this->prepare_email_template_data($appointment);
        $message = $this->load_email_template('reminder-patient', $template_data);
        
        $headers = $this->get_email_headers();
        
        return wp_mail($to, $subject, $message, $headers);
    }
    
    /**
     * Get appointment data
     */
    private function get_appointment_data($appointment_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id), ARRAY_A);
        
        if (!$appointment) {
            return false;
        }
        
        // Add additional data
        $appointment['service_title'] = get_the_title($appointment['service_id']);
        $appointment['professional_title'] = get_the_title($appointment['professional_id']);
        $appointment['service_price'] = get_post_meta($appointment['service_id'], '_vpa_service_price', true);
        $appointment['professional_email'] = get_post_meta($appointment['professional_id'], '_vpa_professional_email', true);
        $appointment['professional_phone'] = get_post_meta($appointment['professional_id'], '_vpa_professional_phone', true);
        
        return $appointment;
    }
    
    /**
     * Prepare email template data
     */
    private function prepare_email_template_data($appointment) {
        $general_settings = get_option('vitapro_appointments_general_settings', array());
        
        $date_format = get_option('date_format');
        $time_format = get_option('time_format');
        
        return array(
            'appointment_id' => $appointment['id'],
            'service_name' => $appointment['service_title'],
            'professional_name' => $appointment['professional_title'],
            'customer_name' => $appointment['customer_name'],
            'customer_email' => $appointment['customer_email'],
            'customer_phone' => $appointment['customer_phone'],
            'appointment_date' => date_i18n($date_format, strtotime($appointment['appointment_date'])),
            'appointment_time' => date_i18n($time_format, strtotime($appointment['appointment_time'])),
            'appointment_status' => ucfirst($appointment['status']),
            'appointment_notes' => $appointment['notes'],
            'appointment_duration' => $appointment['duration'] . ' ' . __('minutes', 'vitapro-appointments-fse'),
            'service_price' => !empty($appointment['service_price']) ? $general_settings['currency_symbol'] . $appointment['service_price'] : '',
            'business_name' => isset($general_settings['business_name']) ? $general_settings['business_name'] : get_bloginfo('name'),
            'business_phone' => isset($general_settings['business_phone']) ? $general_settings['business_phone'] : '',
            'business_address' => isset($general_settings['business_address']) ? $general_settings['business_address'] : '',
            'business_email' => isset($general_settings['business_email']) ? $general_settings['business_email'] : get_option('admin_email'),
            'professional_email' => $appointment['professional_email'],
            'professional_phone' => $appointment['professional_phone'],
            'site_url' => home_url(),
            'admin_url' => admin_url('admin.php?page=vitapro-appointments'),
            'cancel_url' => $this->generate_cancel_url($appointment['id']),
            'reschedule_url' => $this->generate_reschedule_url($appointment['id']),
        );
    }
    
    /**
     * Load email template
     */
    private function load_email_template($template_name, $data) {
        $template_path = VITAPRO_APPOINTMENTS_FSE_PATH . 'templates/email/' . $template_name . '.php';
        
        if (file_exists($template_path)) {
            ob_start();
            extract($data);
            include $template_path;
            $content = ob_get_clean();
        } else {
            // Fallback to default template
            $content = $this->get_default_email_template($template_name, $data);
        }
        
        // Apply email header and footer
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        $header = isset($email_settings['email_template_header']) ? $email_settings['email_template_header'] : '';
        $footer = isset($email_settings['email_template_footer']) ? $email_settings['email_template_footer'] : '';
        
        $full_content = $header . $content . $footer;
        
        // Replace placeholders
        foreach ($data as $key => $value) {
            $full_content = str_replace('{' . $key . '}', $value, $full_content);
        }
        
        return $full_content;
    }
    
    /**
     * Get default email template
     */
    private function get_default_email_template($template_name, $data) {
        switch ($template_name) {
            case 'new-booking-admin':
                return $this->get_admin_new_booking_template($data);
                
            case 'new-booking-patient':
                return $this->get_customer_confirmation_template($data);
                
            case 'reminder-patient':
                return $this->get_reminder_template($data);
                
            case 'cancellation-admin':
                return $this->get_admin_cancellation_template($data);
                
            case 'cancellation-patient':
                return $this->get_customer_cancellation_template($data);
                
            case 'completion-patient':
                return $this->get_completion_template($data);
                
            default:
                return '';
        }
    }
    
    /**
     * Get admin new booking template
     */
    private function get_admin_new_booking_template($data) {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">New Appointment Booking</h2>
            <p>A new appointment has been booked on your website.</p>
            
            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Appointment Details</h3>
                <p><strong>Service:</strong> {service_name}</p>
                <p><strong>Professional:</strong> {professional_name}</p>
                <p><strong>Customer:</strong> {customer_name}</p>
                <p><strong>Email:</strong> {customer_email}</p>
                <p><strong>Phone:</strong> {customer_phone}</p>
                <p><strong>Date:</strong> {appointment_date}</p>
                <p><strong>Time:</strong> {appointment_time}</p>
                <p><strong>Duration:</strong> {appointment_duration}</p>
                <p><strong>Status:</strong> {appointment_status}</p>
                ' . (!empty($data['appointment_notes']) ? '<p><strong>Notes:</strong> {appointment_notes}</p>' : '') . '
            </div>
            
            <p><a href="{admin_url}" style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Manage Appointment</a></p>
        </div>';
    }
    
    /**
     * Get customer confirmation template
     */
    private function get_customer_confirmation_template($data) {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Appointment Confirmation</h2>
            <p>Dear {customer_name},</p>
            <p>Thank you for booking an appointment with {business_name}. Your appointment has been confirmed.</p>
            
            <div style="background: #f9f9f9; padding: 20px; border-radius: 5px; margin: 20px 0;">
                <h3 style="margin-top: 0;">Your Appointment Details</h3>
                <p><strong>Service:</strong> {service_name}</p>
                <p><strong>Professional:</strong> {professional_name}</p>
                <p><strong>Date:</strong> {appointment_date}</p>
                <p><strong>Time:</strong> {appointment_time}</p>
                <p><strong>Duration:</strong> {appointment_duration}</p>
                ' . (!empty($data['service_price']) ? '<p><strong>Price:</strong> {service_price}</p>' : '') . '
                ' . (!empty($data['appointment_notes']) ? '<p><strong>Notes:</strong> {appointment_notes}</p>' : '') . '
            </div>
            
            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="margin-top: 0;">Contact Information</h4>
                <p><strong>{business_name}</strong></p>
                ' . (!empty($data['business_phone']) ? '<p>Phone: {business_phone}</p>' : '') . '
                ' . (!empty($data['business_address']) ? '<p>Address: {business_address}</p>' : '') . '
            </div>
            
            <p>If you need to cancel or reschedule your appointment, please contact us as soon as possible.</p>
            
            <div style="margin: 30px 0;">
                <a href="{cancel_url}" style="background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin-right: 10px;">Cancel Appointment</a>
                <a href="{reschedule_url}" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Reschedule</a>
            </div>
            
            <p>We look forward to seeing you!</p>
            <p>Best regards,<br>{business_name}</p>
        </div>';
    }
    
    /**
     * Get reminder template
     */
    private function get_reminder_template($data) {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #333;">Appointment Reminder</h2>
            <p>Dear {customer_name},</p>
            <p>This is a friendly reminder about your upcoming appointment with {business_name}.</p>
            
            <div style="background: #fff3cd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
                <h3 style="margin-top: 0; color: #856404;">Appointment Tomorrow</h3>
                <p><strong>Service:</strong> {service_name}</p>
                <p><strong>Professional:</strong> {professional_name}</p>
                <p><strong>Date:</strong> {appointment_date}</p>
                <p><strong>Time:</strong> {appointment_time}</p>
                <p><strong>Duration:</strong> {appointment_duration}</p>
            </div>
            
            <p>Please arrive 10 minutes early for your appointment.</p>
            
            <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
                <h4 style="margin-top: 0;">Contact Information</h4>
                <p><strong>{business_name}</strong></p>
                ' . (!empty($data['business_phone']) ? '<p>Phone: {business_phone}</p>' : '') . '
                ' . (!empty($data['business_address']) ? '<p>Address: {business_address}</p>' : '') . '
            </div>
            
            <p>If you need to cancel or reschedule, please contact us immediately.</p>
            
            <p>Thank you,<br>{business_name}</p>
        </div>';
    }
    
    /**
     * Get admin cancellation template
     */
    private function get_admin_cancellation_template($data) {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #dc3545;">Appointment Cancelled</h2>
            <p>An appointment has been cancelled.</p>
            
            <div style="background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                <h3 style="margin-top: 0;">Cancelled Appointment Details</h3>
                <p><strong>Service:</strong> {service_name}</p>
                <p><strong>Professional:</strong> {professional_name}</p>
                <p><strong>Customer:</strong> {customer_name}</p>
                <p><strong>Email:</strong> {customer_email}</p>
                <p><strong>Date:</strong> {appointment_date}</p>
                <p><strong>Time:</strong> {appointment_time}</p>
            </div>
            
            <p><a href="{admin_url}" style="background: #0073aa; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">View All Appointments</a></p>
        </div>';
    }
    
    /**
     * Get customer cancellation template
     */
    private function get_customer_cancellation_template($data) {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #dc3545;">Appointment Cancelled</h2>
            <p>Dear {customer_name},</p>
            <p>Your appointment has been successfully cancelled.</p>
            
            <div style="background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;">
                <h3 style="margin-top: 0;">Cancelled Appointment</h3>
                <p><strong>Service:</strong> {service_name}</p>
                <p><strong>Professional:</strong> {professional_name}</p>
                <p><strong>Date:</strong> {appointment_date}</p>
                <p><strong>Time:</strong> {appointment_time}</p>
            </div>
            
            <p>We\'re sorry to see you go. If you\'d like to book another appointment in the future, please don\'t hesitate to contact us.</p>
            
            <p>Best regards,<br>{business_name}</p>
        </div>';
    }
    
    /**
     * Get completion template
     */
    private function get_completion_template($data) {
        return '
        <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
            <h2 style="color: #28a745;">Thank You for Your Visit</h2>
            <p>Dear {customer_name},</p>
            <p>Thank you for visiting {business_name}. We hope you had a great experience with us.</p>
            
            <div style="background: #d4edda; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #28a745;">
                <h3 style="margin-top: 0;">Completed Appointment</h3>
                <p><strong>Service:</strong> {service_name}</p>
                <p><strong>Professional:</strong> {professional_name}</p>
                <p><strong>Date:</strong> {appointment_date}</p>
                <p><strong>Time:</strong> {appointment_time}</p>
            </div>
            
            <p>We would love to hear about your experience. Please consider leaving us a review or feedback.</p>
            
            <p>If you need to book another appointment, please don\'t hesitate to contact us.</p>
            
            <p>Thank you for choosing {business_name}!</p>
            <p>Best regards,<br>{business_name}</p>
        </div>';
    }
    
    /**
     * Get email headers
     */
    private function get_email_headers() {
        $email_settings = get_option('vitapro_appointments_email_settings', array());
        
        $from_name = isset($email_settings['from_name']) ? $email_settings['from_name'] : get_bloginfo('name');
        $from_email = isset($email_settings['from_email']) ? $email_settings['from_email'] : get_option('admin_email');
        
        $headers = array();
        $headers[] = 'Content-Type: text/html; charset=UTF-8';
        $headers[] = 'From: ' . $from_name . ' <' . $from_email . '>';
        
        return $headers;
    }
    
    /**
     * Generate calendar attachment
     */
    private function generate_calendar_attachment($appointment) {
        $start_datetime = strtotime($appointment['appointment_date'] . ' ' . $appointment['appointment_time']);
        $end_datetime = $start_datetime + ($appointment['duration'] * 60);
        
        $ics_content = "BEGIN:VCALENDAR\r\n";
        $ics_content .= "VERSION:2.0\r\n";
        $ics_content .= "PRODID:-//VitaPro Appointments//EN\r\n";
        $ics_content .= "BEGIN:VEVENT\r\n";
        $ics_content .= "UID:" . $appointment['id'] . "@" . parse_url(home_url(), PHP_URL_HOST) . "\r\n";
        $ics_content .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";
        $ics_content .= "DTSTART:" . gmdate('Ymd\THis\Z', $start_datetime) . "\r\n";
        $ics_content .= "DTEND:" . gmdate('Ymd\THis\Z', $end_datetime) . "\r\n";
        $ics_content .= "SUMMARY:" . $appointment['service_title'] . " - " . $appointment['professional_title'] . "\r\n";
        $ics_content .= "DESCRIPTION:Appointment with " . $appointment['professional_title'] . " for " . $appointment['service_title'] . "\r\n";
        
        $general_settings = get_option('vitapro_appointments_general_settings', array());
        if (!empty($general_settings['business_address'])) {
            $ics_content .= "LOCATION:" . str_replace(array("\r", "\n"), ' ', $general_settings['business_address']) . "\r\n";
        }
        
        $ics_content .= "END:VEVENT\r\n";
        $ics_content .= "END:VCALENDAR\r\n";
        
        $temp_file = wp_upload_dir()['basedir'] . '/appointment-' . $appointment['id'] . '.ics';
        file_put_contents($temp_file, $ics_content);
        
        return $temp_file;
    }
    
    /**
     * Generate cancel URL
     */
    private function generate_cancel_url($appointment_id) {
        return add_query_arg(array(
            'action' => 'cancel_appointment',
            'appointment_id' => $appointment_id,
            'nonce' => wp_create_nonce('cancel_appointment_' . $appointment_id)
        ), home_url());
    }
    
    /**
     * Generate reschedule URL
     */
    private function generate_reschedule_url($appointment_id) {
        return add_query_arg(array(
            'action' => 'reschedule_appointment',
            'appointment_id' => $appointment_id,
            'nonce' => wp_create_nonce('reschedule_appointment_' . $appointment_id)
        ), home_url());
    }
    
    /**
     * Log email error
     */
    public function log_email_error($wp_error) {
        error_log('VitaPro Appointments Email Error: ' . $wp_error->get_error_message());
    }
}