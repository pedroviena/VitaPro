<?php
/**
 * Security
 * 
 * Handles advanced security features for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Security {
    
    private $failed_attempts = array();
    private $blocked_ips = array();
    private $max_attempts = 5;
    private $lockout_duration = 900; // 15 minutes
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init_security'));
        add_action('wp_ajax_vpa_book_appointment', array($this, 'check_rate_limit'), 1);
        add_action('wp_ajax_nopriv_vpa_book_appointment', array($this, 'check_rate_limit'), 1);
        
        // CSRF Protection
        add_action('wp_ajax_vpa_verify_booking_token', array($this, 'verify_booking_token'));
        add_action('wp_ajax_nopriv_vpa_verify_booking_token', array($this, 'verify_booking_token'));
        
        // Input Sanitization
        add_filter('vitapro_sanitize_booking_data', array($this, 'advanced_sanitization'));
        
        // SQL Injection Prevention
        add_filter('vitapro_prepare_query', array($this, 'secure_query_preparation'));
        
        // XSS Prevention
        add_filter('vitapro_output_data', array($this, 'prevent_xss'));
        
        // Honeypot Protection
        add_action('vitapro_booking_form_fields', array($this, 'add_honeypot_field'));
        add_filter('vitapro_validate_booking', array($this, 'check_honeypot'));
        
        // Security Headers
        add_action('send_headers', array($this, 'add_security_headers'));
        
        // Audit Logging
        add_action('vitapro_appointment_created', array($this, 'log_appointment_creation'));
        add_action('vitapro_appointment_status_changed', array($this, 'log_status_change'), 10, 3);
        
        // Backup and Recovery
        add_action('vitapro_daily_backup', array($this, 'create_daily_backup'));
        
        // Two-Factor Authentication for Admin
        add_action('wp_login', array($this, 'check_2fa_requirement'), 10, 2);
    }
    
    /**
     * Initialize security features
     */
    public function init_security() {
        $this->load_blocked_ips();
        $this->check_ip_blocking();
        $this->setup_security_tables();
    }
    
    /**
     * Check rate limiting
     */
    public function check_rate_limit() {
        $ip = $this->get_client_ip();
        $current_time = current_time('timestamp');
        
        // Check if IP is blocked
        if ($this->is_ip_blocked($ip)) {
            wp_die(__('Access denied. Too many failed attempts.', 'vitapro-appointments-fse'), 'Rate Limited', array('response' => 429));
        }
        
        // Check rate limiting (max 10 requests per minute)
        $rate_limit_key = 'vpa_rate_limit_' . md5($ip);
        $requests = get_transient($rate_limit_key);
        
        if ($requests === false) {
            set_transient($rate_limit_key, 1, 60);
        } else {
            if ($requests >= 10) {
                $this->log_security_event('rate_limit_exceeded', $ip);
                wp_die(__('Rate limit exceeded. Please try again later.', 'vitapro-appointments-fse'), 'Rate Limited', array('response' => 429));
            }
            set_transient($rate_limit_key, $requests + 1, 60);
        }
    }
    
    /**
     * Verify booking token (CSRF protection)
     */
    public function verify_booking_token() {
        if (!wp_verify_nonce($_POST['booking_token'], 'vitapro_booking_' . session_id())) {
            wp_send_json_error(__('Security token verification failed.', 'vitapro-appointments-fse'));
        }
        
        wp_send_json_success();
    }
    
    /**
     * Advanced input sanitization
     */
    public function advanced_sanitization($data) {
        $sanitized = array();
        
        foreach ($data as $key => $value) {
            switch ($key) {
                case 'customer_name':
                    $sanitized[$key] = $this->sanitize_name($value);
                    break;
                    
                case 'customer_email':
                    $sanitized[$key] = $this->sanitize_email($value);
                    break;
                    
                case 'customer_phone':
                    $sanitized[$key] = $this->sanitize_phone($value);
                    break;
                    
                case 'appointment_notes':
                    $sanitized[$key] = $this->sanitize_notes($value);
                    break;
                    
                default:
                    $sanitized[$key] = sanitize_text_field($value);
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize name field
     */
    private function sanitize_name($name) {
        // Remove any HTML tags
        $name = strip_tags($name);
        
        // Remove special characters except spaces, hyphens, and apostrophes
        $name = preg_replace('/[^a-zA-Z\s\-\']/', '', $name);
        
        // Limit length
        $name = substr($name, 0, 100);
        
        // Check for suspicious patterns
        if ($this->contains_suspicious_patterns($name)) {
            $this->log_security_event('suspicious_name_input', $this->get_client_ip(), $name);
            return '';
        }
        
        return trim($name);
    }
    
    /**
     * Sanitize email field
     */
    private function sanitize_email($email) {
        $email = sanitize_email($email);
        
        // Additional validation
        if (!is_email($email)) {
            return '';
        }
        
        // Check for disposable email domains
        if ($this->is_disposable_email($email)) {
            $this->log_security_event('disposable_email_attempt', $this->get_client_ip(), $email);
            return '';
        }
        
        return $email;
    }
    
    /**
     * Sanitize phone field
     */
    private function sanitize_phone($phone) {
        // Remove all non-numeric characters except + and spaces
        $phone = preg_replace('/[^0-9+\s\-$$$$]/', '', $phone);
        
        // Limit length
        $phone = substr($phone, 0, 20);
        
        return trim($phone);
    }
    
    /**
     * Sanitize notes field
     */
    private function sanitize_notes($notes) {
        // Remove HTML tags
        $notes = strip_tags($notes);
        
        // Check for suspicious patterns
        if ($this->contains_suspicious_patterns($notes)) {
            $this->log_security_event('suspicious_notes_input', $this->get_client_ip(), $notes);
            return '';
        }
        
        // Limit length
        $notes = substr($notes, 0, 1000);
        
        return trim($notes);
    }
    
    /**
     * Check for suspicious patterns
     */
    private function contains_suspicious_patterns($input) {
        $suspicious_patterns = array(
            '/script/i',
            '/javascript/i',
            '/vbscript/i',
            '/onload/i',
            '/onerror/i',
            '/onclick/i',
            '/<iframe/i',
            '/<object/i',
            '/<embed/i',
            '/eval\(/i',
            '/expression\(/i',
            '/union.*select/i',
            '/drop.*table/i',
            '/insert.*into/i',
            '/update.*set/i',
            '/delete.*from/i'
        );
        
        foreach ($suspicious_patterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if email is from disposable domain
     */
    private function is_disposable_email($email) {
        $domain = substr(strrchr($email, "@"), 1);
        
        $disposable_domains = array(
            '10minutemail.com',
            'guerrillamail.com',
            'mailinator.com',
            'tempmail.org',
            'throwaway.email',
            'temp-mail.org'
        );
        
        return in_array(strtolower($domain), $disposable_domains);
    }
    
    /**
     * Add honeypot field
     */
    public function add_honeypot_field() {
        echo '<div style="position: absolute; left: -9999px;">';
        echo '<input type="text" name="vpa_website" tabindex="-1" autocomplete="off" />';
        echo '</div>';
    }
    
    /**
     * Check honeypot
     */
    public function check_honeypot($is_valid) {
        if (!empty($_POST['vpa_website'])) {
            $this->log_security_event('honeypot_triggered', $this->get_client_ip());
            return false;
        }
        
        return $is_valid;
    }
    
    /**
     * Add security headers
     */
    public function add_security_headers() {
        if (!headers_sent()) {
            header('X-Content-Type-Options: nosniff');
            header('X-Frame-Options: SAMEORIGIN');
            header('X-XSS-Protection: 1; mode=block');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
        }
    }
    
    /**
     * Get client IP address
     */
    private function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Check if IP is blocked
     */
    private function is_ip_blocked($ip) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_security_blocks';
        
        $block = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE ip_address = %s AND expires_at > NOW()",
            $ip
        ));
        
        return !empty($block);
    }
    
    /**
     * Block IP address
     */
    private function block_ip($ip, $reason = '', $duration = null) {
        if (!$duration) {
            $duration = $this->lockout_duration;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_security_blocks';
        
        $wpdb->insert(
            $table_name,
            array(
                'ip_address' => $ip,
                'reason' => $reason,
                'blocked_at' => current_time('mysql'),
                'expires_at' => date('Y-m-d H:i:s', current_time('timestamp') + $duration)
            ),
            array('%s', '%s', '%s', '%s')
        );
        
        $this->log_security_event('ip_blocked', $ip, $reason);
    }
    
    /**
     * Log security event
     */
    private function log_security_event($event_type, $ip, $details = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_security_log';
        
        $wpdb->insert(
            $table_name,
            array(
                'event_type' => $event_type,
                'ip_address' => $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'details' => $details,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
        
        // Alert admin for critical events
        $critical_events = array('sql_injection_attempt', 'xss_attempt', 'multiple_failed_logins');
        if (in_array($event_type, $critical_events)) {
            $this->send_security_alert($event_type, $ip, $details);
        }
    }
    
    /**
     * Send security alert to admin
     */
    private function send_security_alert($event_type, $ip, $details) {
        $admin_email = get_option('admin_email');
        $site_name = get_bloginfo('name');
        
        $subject = sprintf(__('[%s] Security Alert: %s', 'vitapro-appointments-fse'), $site_name, $event_type);
        
        $message = sprintf(
            __("A security event has been detected on your website:\n\nEvent Type: %s\nIP Address: %s\nTime: %s\nDetails: %s\n\nPlease review your security logs.", 'vitapro-appointments-fse'),
            $event_type,
            $ip,
            current_time('mysql'),
            $details
        );
        
        wp_mail($admin_email, $subject, $message);
    }
    
    /**
     * Setup security tables
     */
    public function setup_security_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Security log table
        $log_table = $wpdb->prefix . 'vpa_security_log';
        $log_sql = "CREATE TABLE IF NOT EXISTS $log_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            ip_address varchar(45) NOT NULL,
            user_agent text,
            details text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY ip_address (ip_address),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        // Security blocks table
        $blocks_table = $wpdb->prefix . 'vpa_security_blocks';
        $blocks_sql = "CREATE TABLE IF NOT EXISTS $blocks_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            ip_address varchar(45) NOT NULL,
            reason varchar(255),
            blocked_at datetime NOT NULL,
            expires_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY ip_address (ip_address),
            KEY expires_at (expires_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($log_sql);
        dbDelta($blocks_sql);
    }
    
    /**
     * Load blocked IPs
     */
    private function load_blocked_ips() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_security_blocks';
        
        $blocked_ips = $wpdb->get_col(
            "SELECT ip_address FROM {$table_name} WHERE expires_at > NOW()"
        );
        
        $this->blocked_ips = $blocked_ips;
    }
    
    /**
     * Check IP blocking
     */
    private function check_ip_blocking() {
        $ip = $this->get_client_ip();
        
        if (in_array($ip, $this->blocked_ips)) {
            wp_die(__('Access denied.', 'vitapro-appointments-fse'), 'Blocked', array('response' => 403));
        }
    }
    
    /**
     * Log appointment creation
     */
    public function log_appointment_creation($appointment_id) {
        $this->log_security_event('appointment_created', $this->get_client_ip(), "Appointment ID: {$appointment_id}");
    }
    
    /**
     * Log status change
     */
    public function log_status_change($appointment_id, $new_status, $old_status) {
        $details = "Appointment ID: {$appointment_id}, Status changed from {$old_status} to {$new_status}";
        $this->log_security_event('appointment_status_changed', $this->get_client_ip(), $details);
    }
    
    /**
     * Create daily backup
     */
    public function create_daily_backup() {
        global $wpdb;
        
        $backup_dir = wp_upload_dir()['basedir'] . '/vpa-backups/';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        $backup_file = $backup_dir . 'vpa-backup-' . date('Y-m-d') . '.sql';
        
        $tables = array(
            $wpdb->prefix . 'vpa_appointments',
            $wpdb->prefix . 'vpa_security_log',
            $wpdb->prefix . 'vpa_security_blocks'
        );
        
        $backup_content = '';
        
        foreach ($tables as $table) {
            $backup_content .= $this->backup_table($table);
        }
        
        file_put_contents($backup_file, $backup_content);
        
        // Keep only last 7 days of backups
        $this->cleanup_old_backups($backup_dir);
    }
    
    /**
     * Backup single table
     */
    private function backup_table($table) {
        global $wpdb;
        
        $backup = "-- Backup for table {$table}\n";
        $backup .= "DROP TABLE IF EXISTS {$table};\n";
        
        $create_table = $wpdb->get_row("SHOW CREATE TABLE {$table}", ARRAY_N);
        $backup .= $create_table[1] . ";\n\n";
        
        $rows = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        
        foreach ($rows as $row) {
            $values = array();
            foreach ($row as $value) {
                $values[] = "'" . $wpdb->_escape($value) . "'";
            }
            $backup .= "INSERT INTO {$table} VALUES (" . implode(',', $values) . ");\n";
        }
        
        $backup .= "\n";
        
        return $backup;
    }
    
    /**
     * Cleanup old backups
     */
    private function cleanup_old_backups($backup_dir) {
        $files = glob($backup_dir . 'vpa-backup-*.sql');
        
        if (count($files) > 7) {
            usort($files, function($a, $b) {
                return filemtime($a) - filemtime($b);
            });
            
            $files_to_delete = array_slice($files, 0, count($files) - 7);
            
            foreach ($files_to_delete as $file) {
                unlink($file);
            }
        }
    }
}