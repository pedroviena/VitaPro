<?php
/**
 * Notifications
 *
 * Handles notification creation, management, and delivery for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Notifications
 *
 * Provides methods to create, read, update, and dismiss notifications for users and admins.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Notifications {

    /**
     * Constructor.
     *
     * Registers hooks for notification events.
     *
     * @since 1.0.0
     * @uses add_action()
     */
    public function __construct() {
        add_action('init', array($this, 'init_notifications'));
        add_action('wp_ajax_vpa_get_notifications', array($this, 'get_notifications'));
        add_action('wp_ajax_vpa_mark_notification_read', array($this, 'mark_notification_read'));
        add_action('wp_ajax_vpa_dismiss_notification', array($this, 'dismiss_notification'));
        
        // Notification triggers
        add_action('vitapro_appointment_created', array($this, 'notify_new_appointment'));
        add_action('vitapro_appointment_status_changed', array($this, 'notify_status_change'), 10, 3);
        add_action('vitapro_appointment_reminder_due', array($this, 'notify_reminder_due'));
        add_action('vitapro_security_alert', array($this, 'notify_security_alert'), 10, 2);
        
        // WebSocket support for real-time updates
        add_action('wp_enqueue_scripts', array($this, 'enqueue_notification_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_notification_scripts'));
        
        // Push notifications
        add_action('wp_ajax_vpa_register_push_subscription', array($this, 'register_push_subscription'));
        add_action('wp_ajax_vpa_send_push_notification', array($this, 'send_push_notification'));
    }

    /**
     * Initialize notifications system
     */
    public function init_notifications() {
        $this->create_notifications_table();
        $this->schedule_notification_cleanup();
    }
    
    /**
     * Create notifications table
     */
    public function create_notifications_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vpa_notifications';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            type varchar(50) NOT NULL,
            title varchar(255) NOT NULL,
            message text NOT NULL,
            data longtext,
            is_read tinyint(1) DEFAULT 0,
            is_dismissed tinyint(1) DEFAULT 0,
            priority enum('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
            expires_at datetime,
            created_at datetime NOT NULL,
            read_at datetime,
            PRIMARY KEY (id),
            KEY user_id (user_id),
            KEY type (type),
            KEY is_read (is_read),
            KEY priority (priority),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Enqueue notification scripts
     */
    public function enqueue_notification_scripts() {
        wp_enqueue_script(
            'vpa-notifications',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/notifications.js',
            array('jquery'),
            VITAPRO_APPOINTMENTS_FSE_VERSION,
            true
        );
        
        wp_enqueue_style(
            'vpa-notifications',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/notifications.css',
            array(),
            VITAPRO_APPOINTMENTS_FSE_VERSION
        );
        
        wp_localize_script('vpa-notifications', 'vpaNotifications', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vpa_notifications_nonce'),
            'user_id' => get_current_user_id(),
            'websocket_url' => $this->get_websocket_url(),
            'push_public_key' => $this->get_push_public_key(),
            'strings' => array(
                'new_notification' => __('New notification', 'vitapro-appointments-fse'),
                'mark_all_read' => __('Mark all as read', 'vitapro-appointments-fse'),
                'no_notifications' => __('No notifications', 'vitapro-appointments-fse'),
            )
        ));
    }
    
    /**
     * Get notifications for current user
     */
    public function get_notifications() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_notifications_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $user_id = get_current_user_id();
        $limit = isset($_POST['limit']) ? intval($_POST['limit']) : 20;
        $offset = isset($_POST['offset']) ? intval($_POST['offset']) : 0;
        $unread_only = isset($_POST['unread_only']) ? (bool)$_POST['unread_only'] : false;
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_notifications';
        
        $where_conditions = array("user_id = %d", "is_dismissed = 0");
        $where_values = array($user_id);
        
        if ($unread_only) {
            $where_conditions[] = "is_read = 0";
        }
        
        // Add expiration check
        $where_conditions[] = "(expires_at IS NULL OR expires_at > NOW())";
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        $notifications = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_name} 
             {$where_clause}
             ORDER BY priority DESC, created_at DESC 
             LIMIT %d OFFSET %d",
            array_merge($where_values, array($limit, $offset))
        ));
        
        // Get unread count
        $unread_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} 
             WHERE user_id = %d AND is_read = 0 AND is_dismissed = 0 
             AND (expires_at IS NULL OR expires_at > NOW())",
            $user_id
        ));
        
        // Format notifications
        $formatted_notifications = array();
        foreach ($notifications as $notification) {
            $formatted_notifications[] = array(
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'data' => json_decode($notification->data, true),
                'is_read' => (bool)$notification->is_read,
                'priority' => $notification->priority,
                'created_at' => $notification->created_at,
                'time_ago' => $this->time_ago($notification->created_at),
                'icon' => $this->get_notification_icon($notification->type),
                'color' => $this->get_notification_color($notification->priority)
            );
        }
        
        wp_send_json_success(array(
            'notifications' => $formatted_notifications,
            'unread_count' => intval($unread_count),
            'has_more' => count($notifications) === $limit
        ));
    }
    
    /**
     * Mark notification as read
     */
    public function mark_notification_read() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_notifications_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $notification_id = intval($_POST['notification_id']);
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_notifications';
        
        $result = $wpdb->update(
            $table_name,
            array(
                'is_read' => 1,
                'read_at' => current_time('mysql')
            ),
            array(
                'id' => $notification_id,
                'user_id' => $user_id
            ),
            array('%d', '%s'),
            array('%d', '%d')
        );
        
        if ($result === false) {
            error_log('VitaPro DB Error: ' . $wpdb->last_error . ' on query: ' . $wpdb->last_query);
            wp_send_json_error(__('Failed to mark notification as read', 'vitapro-appointments-fse'));
        } else {
            wp_send_json_success();
        }
    }
    
    /**
     * Dismiss notification
     */
    public function dismiss_notification() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_notifications_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $notification_id = intval($_POST['notification_id']);
        $user_id = get_current_user_id();
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_notifications';
        
        $result = $wpdb->update(
            $table_name,
            array('is_dismissed' => 1),
            array(
                'id' => $notification_id,
                'user_id' => $user_id
            ),
            array('%d'),
            array('%d', '%d')
        );
        
        if ($result === false) {
            error_log('VitaPro DB Error: ' . $wpdb->last_error . ' on query: ' . $wpdb->last_query);
            wp_send_json_error(__('Failed to dismiss notification', 'vitapro-appointments-fse'));
        } else {
            wp_send_json_success();
        }
    }
    
    /**
     * Create a new notification for a user.
     *
     * @param int $user_id User ID.
     * @param string $type Notification type.
     * @param string $title Notification title.
     * @param string $message Notification message.
     * @param array $data Optional. Additional data for the notification.
     * @param string $priority Optional. Notification priority (low, medium, high, urgent). Default is medium.
     * @param string $expires_at Optional. Expiration date/time for the notification in Y-m-d H:i:s format. Default is null.
     * @return int|false Notification ID on success, false on failure.
     * @since 1.0.0
     * @uses $wpdb
     */
    public function create_notification($user_id, $type, $title, $message, $data = array(), $priority = 'medium', $expires_at = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_notifications';
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'user_id' => $user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => json_encode($data),
                'priority' => $priority,
                'expires_at' => $expires_at,
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
        
        if ($result === false) {
            error_log('VitaPro DB Error: ' . $wpdb->last_error . ' on query: ' . $wpdb->last_query);
            return false;
        }
        
        $notification_id = $wpdb->insert_id;
        
        // Send real-time notification
        $this->send_realtime_notification($user_id, array(
            'id' => $notification_id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'priority' => $priority
        ));
        
        // Send push notification if enabled
        $this->maybe_send_push_notification($user_id, $title, $message, $data);
        
        return $notification_id;
    }
    
    /**
     * Notify new appointment
     */
    public function notify_new_appointment($appointment_id) {
        $appointment = $this->get_appointment_data($appointment_id);
        if (!$appointment) return;
        
        // Notify admin users
        $admin_users = get_users(array('role' => 'administrator'));
        foreach ($admin_users as $user) {
            $this->create_notification(
                $user->ID,
                'new_appointment',
                __('New Appointment Booked', 'vitapro-appointments-fse'),
                sprintf(
                    __('New appointment booked by %s for %s on %s at %s', 'vitapro-appointments-fse'),
                    $appointment['customer_name'],
                    $appointment['service_title'],
                    date_i18n(get_option('date_format'), strtotime($appointment['appointment_date'])),
                    date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))
                ),
                array('appointment_id' => $appointment_id),
                'high'
            );
        }
        
        // Notify professional
        $professional_user_id = get_post_meta($appointment['professional_id'], '_vpa_professional_user_id', true);
        if ($professional_user_id) {
            $this->create_notification(
                $professional_user_id,
                'new_appointment',
                __('New Appointment Assigned', 'vitapro-appointments-fse'),
                sprintf(
                    __('You have a new appointment with %s on %s at %s', 'vitapro-appointments-fse'),
                    $appointment['customer_name'],
                    date_i18n(get_option('date_format'), strtotime($appointment['appointment_date'])),
                    date_i18n(get_option('time_format'), strtotime($appointment['appointment_time']))
                ),
                array('appointment_id' => $appointment_id),
                'high'
            );
        }
    }
    
    /**
     * Notify status change
     */
    public function notify_status_change($appointment_id, $new_status, $old_status) {
        $appointment = $this->get_appointment_data($appointment_id);
        if (!$appointment) return;
        
        $status_messages = array(
            'confirmed' => __('Your appointment has been confirmed', 'vitapro-appointments-fse'),
            'cancelled' => __('Your appointment has been cancelled', 'vitapro-appointments-fse'),
            'completed' => __('Your appointment has been completed', 'vitapro-appointments-fse'),
            'rescheduled' => __('Your appointment has been rescheduled', 'vitapro-appointments-fse')
        );
        
        if (isset($status_messages[$new_status])) {
            // Create notification for customer (if they have an account)
            $customer_user = get_user_by('email', $appointment['customer_email']);
            if ($customer_user) {
                $this->create_notification(
                    $customer_user->ID,
                    'appointment_status_change',
                    __('Appointment Status Update', 'vitapro-appointments-fse'),
                    $status_messages[$new_status],
                    array(
                        'appointment_id' => $appointment_id,
                        'new_status' => $new_status,
                        'old_status' => $old_status
                    ),
                    'medium'
                );
            }
        }
    }
    
    /**
     * Notify security alert
     */
    public function notify_security_alert($alert_type, $details) {
        $admin_users = get_users(array('role' => 'administrator'));
        
        $alert_messages = array(
            'failed_login_attempts' => __('Multiple failed login attempts detected', 'vitapro-appointments-fse'),
            'suspicious_activity' => __('Suspicious activity detected', 'vitapro-appointments-fse'),
            'sql_injection_attempt' => __('SQL injection attempt blocked', 'vitapro-appointments-fse'),
            'xss_attempt' => __('XSS attack attempt blocked', 'vitapro-appointments-fse')
        );
        
        $message = isset($alert_messages[$alert_type]) ? $alert_messages[$alert_type] : __('Security alert', 'vitapro-appointments-fse');
        
        foreach ($admin_users as $user) {
            $this->create_notification(
                $user->ID,
                'security_alert',
                __('Security Alert', 'vitapro-appointments-fse'),
                $message,
                array(
                    'alert_type' => $alert_type,
                    'details' => $details
                ),
                'urgent',
                date('Y-m-d H:i:s', strtotime('+24 hours')) // Expire in 24 hours
            );
        }
    }
    
    /**
     * Send real-time notification via WebSocket
     */
    private function send_realtime_notification($user_id, $notification_data) {
        // This would integrate with a WebSocket server
        // For now, we'll use a simple AJAX polling mechanism
        
        $transient_key = 'vpa_realtime_notifications_' . $user_id;
        $existing_notifications = get_transient($transient_key) ?: array();
        
        $existing_notifications[] = $notification_data;
        
        // Keep only last 10 notifications in transient
        if (count($existing_notifications) > 10) {
            $existing_notifications = array_slice($existing_notifications, -10);
        }
        
        set_transient($transient_key, $existing_notifications, 300); // 5 minutes
    }
    
    /**
     * Register push subscription
     */
    public function register_push_subscription() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_notifications_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $user_id = get_current_user_id();
        $subscription = json_decode(stripslashes($_POST['subscription']), true);
        
        if ($subscription && isset($subscription['endpoint'])) {
            update_user_meta($user_id, 'vpa_push_subscription', $subscription);
            wp_send_json_success();
        } else {
            wp_send_json_error(__('Invalid subscription data', 'vitapro-appointments-fse'));
        }
    }
    
    /**
     * Maybe send push notification
     */
    private function maybe_send_push_notification($user_id, $title, $message, $data = array()) {
        $subscription = get_user_meta($user_id, 'vpa_push_subscription', true);
        
        if (!$subscription || !isset($subscription['endpoint'])) {
            return false;
        }
        
        // Check user preferences
        $push_enabled = get_user_meta($user_id, 'vpa_push_notifications_enabled', true);
        if ($push_enabled === '0') {
            return false;
        }
        
        $payload = json_encode(array(
            'title' => $title,
            'body' => $message,
            'icon' => VITAPRO_APPOINTMENTS_FSE_URL . 'assets/images/notification-icon.png',
            'badge' => VITAPRO_APPOINTMENTS_FSE_URL . 'assets/images/notification-badge.png',
            'data' => $data,
            'actions' => array(
                array(
                    'action' => 'view',
                    'title' => __('View', 'vitapro-appointments-fse')
                ),
                array(
                    'action' => 'dismiss',
                    'title' => __('Dismiss', 'vitapro-appointments-fse')
                )
            )
        ));
        
        return $this->send_web_push($subscription, $payload);
    }
    
    /**
     * Send web push notification
     */
    private function send_web_push($subscription, $payload) {
        // This would use a library like web-push-php
        // For demonstration, we'll just log it
        error_log('VitaPro Push Notification: ' . $payload);
        return true;
    }
    
    /**
     * Get notification icon
     */
    private function get_notification_icon($type) {
        $icons = array(
            'new_appointment' => 'calendar-alt',
            'appointment_status_change' => 'info',
            'reminder' => 'clock',
            'security_alert' => 'warning',
            'system_update' => 'update',
            'payment_received' => 'money-alt'
        );
        
        return isset($icons[$type]) ? $icons[$type] : 'bell';
    }
    
    /**
     * Get notification color
     */
    private function get_notification_color($priority) {
        $colors = array(
            'low' => '#72aee6',
            'medium' => '#00a32a',
            'high' => '#ff8c00',
            'urgent' => '#d63638'
        );
        
        return isset($colors[$priority]) ? $colors[$priority] : '#72aee6';
    }
    
    /**
     * Time ago helper
     */
    private function time_ago($datetime) {
        $time = time() - strtotime($datetime);
        
        if ($time < 60) return __('just now', 'vitapro-appointments-fse');
        if ($time < 3600) return sprintf(__('%d minutes ago', 'vitapro-appointments-fse'), floor($time/60));
        if ($time < 86400) return sprintf(__('%d hours ago', 'vitapro-appointments-fse'), floor($time/3600));
        if ($time < 2592000) return sprintf(__('%d days ago', 'vitapro-appointments-fse'), floor($time/86400));
        
        return date_i18n(get_option('date_format'), strtotime($datetime));
    }
    
    /**
     * Get appointment data
     */
    private function get_appointment_data($appointment_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id), ARRAY_A);
        
        if ($appointment) {
            $appointment['service_title'] = get_the_title($appointment['service_id']);
            $appointment['professional_title'] = get_the_title($appointment['professional_id']);
        }
        
        return $appointment;
    }
    
    /**
     * Schedule notification cleanup
     */
    private function schedule_notification_cleanup() {
        if (!wp_next_scheduled('vpa_cleanup_notifications')) {
            wp_schedule_event(time(), 'daily', 'vpa_cleanup_notifications');
        }
        
        add_action('vpa_cleanup_notifications', array($this, 'cleanup_old_notifications'));
    }
    
    /**
     * Cleanup old notifications
     */
    public function cleanup_old_notifications() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_notifications';
        
        // Delete notifications older than 30 days
        $wpdb->query("DELETE FROM {$table_name} WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)");
        
        // Delete expired notifications
        $wpdb->query("DELETE FROM {$table_name} WHERE expires_at IS NOT NULL AND expires_at < NOW()");
    }
    
    /**
     * Get WebSocket URL
     */
    private function get_websocket_url() {
        // This would return the WebSocket server URL
        return '';
    }
    
    /**
     * Get push notification public key
     */
    private function get_push_public_key() {
        // This would return the VAPID public key for push notifications
        return '';
    }
}