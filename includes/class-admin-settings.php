<?php
/**
 * Admin Settings
 * 
 * Handles admin settings for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Admin_Settings {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Settings submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Settings', 'vitapro-appointments-fse'),
            __('Settings', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-settings',
            array($this, 'display_settings_page')
        );
        
        // Email Templates submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Email Templates', 'vitapro-appointments-fse'),
            __('Email Templates', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-email-templates',
            array($this, 'display_email_templates_page')
        );
        
        // Custom Fields submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Custom Fields', 'vitapro-appointments-fse'),
            __('Custom Fields', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-custom-fields',
            array($this, 'display_custom_fields_page')
        );
        
        // Working Hours submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Working Hours', 'vitapro-appointments-fse'),
            __('Working Hours', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-working-hours',
            array($this, 'display_working_hours_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting(
            'vitapro_appointments_general_settings',
            'vitapro_appointments_general_settings',
            array($this, 'sanitize_general_settings')
        );
        
        // Email Settings
        register_setting(
            'vitapro_appointments_email_settings',
            'vitapro_appointments_email_settings',
            array($this, 'sanitize_email_settings')
        );
        
        // Custom Fields
        register_setting(
            'vitapro_appointments_custom_fields',
            'vitapro_appointments_custom_fields',
            array($this, 'sanitize_custom_fields')
        );
        
        // Working Hours
        register_setting(
            'vitapro_appointments_working_hours',
            'vitapro_appointments_working_hours',
            array($this, 'sanitize_working_hours')
        );
    }
    
    /**
     * Enqueue admin scripts
     */
    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'vitapro-appointments') === false) {
            return;
        }
        
        wp_enqueue_script('jquery');
        wp_enqueue_media();
    }
    
    /**
     * Sanitize general settings
     */
    public function sanitize_general_settings($input) {
        $sanitized = array();
        
        if (isset($input['business_name'])) {
            $sanitized['business_name'] = sanitize_text_field($input['business_name']);
        }
        
        if (isset($input['business_email'])) {
            $sanitized['business_email'] = sanitize_email($input['business_email']);
        }
        
        if (isset($input['business_phone'])) {
            $sanitized['business_phone'] = sanitize_text_field($input['business_phone']);
        }
        
        if (isset($input['business_address'])) {
            $sanitized['business_address'] = sanitize_textarea_field($input['business_address']);
        }
        
        if (isset($input['timezone'])) {
            $sanitized['timezone'] = sanitize_text_field($input['timezone']);
        }
        
        if (isset($input['date_format'])) {
            $sanitized['date_format'] = sanitize_text_field($input['date_format']);
        }
        
        if (isset($input['time_format'])) {
            $sanitized['time_format'] = sanitize_text_field($input['time_format']);
        }
        
        if (isset($input['currency'])) {
            $sanitized['currency'] = sanitize_text_field($input['currency']);
        }
        
        if (isset($input['currency_symbol'])) {
            $sanitized['currency_symbol'] = sanitize_text_field($input['currency_symbol']);
        }
        
        if (isset($input['currency_position'])) {
            $sanitized['currency_position'] = sanitize_text_field($input['currency_position']);
        }
        
        if (isset($input['default_appointment_duration'])) {
            $sanitized['default_appointment_duration'] = absint($input['default_appointment_duration']);
        }
        
        if (isset($input['booking_advance_time'])) {
            $sanitized['booking_advance_time'] = absint($input['booking_advance_time']);
        }
        
        if (isset($input['cancellation_time_limit'])) {
            $sanitized['cancellation_time_limit'] = absint($input['cancellation_time_limit']);
        }
        
        if (isset($input['max_appointments_per_day'])) {
            $sanitized['max_appointments_per_day'] = absint($input['max_appointments_per_day']);
        }
        
        $sanitized['require_login'] = isset($input['require_login']) ? (bool) $input['require_login'] : false;
        $sanitized['auto_confirm_appointments'] = isset($input['auto_confirm_appointments']) ? (bool) $input['auto_confirm_appointments'] : false;
        $sanitized['send_email_notifications'] = isset($input['send_email_notifications']) ? (bool) $input['send_email_notifications'] : true;
        $sanitized['send_sms_notifications'] = isset($input['send_sms_notifications']) ? (bool) $input['send_sms_notifications'] : false;
        
        return $sanitized;
    }
    
    /**
     * Sanitize email settings
     */
    public function sanitize_email_settings($input) {
        $sanitized = array();
        
        if (isset($input['from_name'])) {
            $sanitized['from_name'] = sanitize_text_field($input['from_name']);
        }
        
        if (isset($input['from_email'])) {
            $sanitized['from_email'] = sanitize_email($input['from_email']);
        }
        
        if (isset($input['admin_notification_email'])) {
            $sanitized['admin_notification_email'] = sanitize_email($input['admin_notification_email']);
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize custom fields
     */
    public function sanitize_custom_fields($input) {
        $sanitized = array();
        
        if (is_array($input)) {
            foreach ($input as $key => $field) {
                if (is_array($field)) {
                    $sanitized[$key] = array(
                        'name' => isset($field['name']) ? sanitize_text_field($field['name']) : '',
                        'type' => isset($field['type']) ? sanitize_text_field($field['type']) : 'text',
                        'label' => isset($field['label']) ? sanitize_text_field($field['label']) : '',
                        'placeholder' => isset($field['placeholder']) ? sanitize_text_field($field['placeholder']) : '',
                        'required' => isset($field['required']) ? (bool) $field['required'] : false,
                        'options' => isset($field['options']) ? sanitize_textarea_field($field['options']) : '',
                        'default' => isset($field['default']) ? sanitize_text_field($field['default']) : '',
                    );
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize working hours
     */
    public function sanitize_working_hours($input) {
        $sanitized = array();
        $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');
        
        foreach ($days as $day) {
            $sanitized[$day] = array(
                'enabled' => isset($input[$day]['enabled']) ? (bool) $input[$day]['enabled'] : false,
                'slots' => array(),
            );
            
            if (isset($input[$day]['slots']) && is_array($input[$day]['slots'])) {
                foreach ($input[$day]['slots'] as $slot) {
                    if (is_array($slot)) {
                        $sanitized[$day]['slots'][] = array(
                            'start' => isset($slot['start']) ? sanitize_text_field($slot['start']) : '',
                            'end' => isset($slot['end']) ? sanitize_text_field($slot['end']) : '',
                        );
                    }
                }
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('VitaPro Appointments Settings', 'vitapro-appointments-fse'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('vitapro_appointments_general_settings');
                $options = get_option('vitapro_appointments_general_settings', array());
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="business_name"><?php _e('Business Name', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="business_name" name="vitapro_appointments_general_settings[business_name]" value="<?php echo esc_attr(isset($options['business_name']) ? $options['business_name'] : get_bloginfo('name')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_email"><?php _e('Business Email', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="business_email" name="vitapro_appointments_general_settings[business_email]" value="<?php echo esc_attr(isset($options['business_email']) ? $options['business_email'] : get_option('admin_email')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="business_phone"><?php _e('Business Phone', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="business_phone" name="vitapro_appointments_general_settings[business_phone]" value="<?php echo esc_attr(isset($options['business_phone']) ? $options['business_phone'] : ''); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="require_login"><?php _e('Require Login', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="require_login" name="vitapro_appointments_general_settings[require_login]" value="1" <?php checked(isset($options['require_login']) ? $options['require_login'] : false); ?> />
                            <p class="description"><?php _e('Require users to be logged in to book appointments.', 'vitapro-appointments-fse'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="auto_confirm_appointments"><?php _e('Auto-Confirm Appointments', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="auto_confirm_appointments" name="vitapro_appointments_general_settings[auto_confirm_appointments]" value="1" <?php checked(isset($options['auto_confirm_appointments']) ? $options['auto_confirm_appointments'] : false); ?> />
                            <p class="description"><?php _e('Automatically confirm appointments when booked.', 'vitapro-appointments-fse'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="send_email_notifications"><?php _e('Email Notifications', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="checkbox" id="send_email_notifications" name="vitapro_appointments_general_settings[send_email_notifications]" value="1" <?php checked(isset($options['send_email_notifications']) ? $options['send_email_notifications'] : true); ?> />
                            <p class="description"><?php _e('Send email notifications for appointments.', 'vitapro-appointments-fse'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Display email templates page
     */
    public function display_email_templates_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Email Templates', 'vitapro-appointments-fse'); ?></h1>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('vitapro_appointments_email_settings');
                $options = get_option('vitapro_appointments_email_settings', array());
                ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="from_name"><?php _e('From Name', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="from_name" name="vitapro_appointments_email_settings[from_name]" value="<?php echo esc_attr(isset($options['from_name']) ? $options['from_name'] : get_bloginfo('name')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="from_email"><?php _e('From Email', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="from_email" name="vitapro_appointments_email_settings[from_email]" value="<?php echo esc_attr(isset($options['from_email']) ? $options['from_email'] : get_option('admin_email')); ?>" class="regular-text" />
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="admin_notification_email"><?php _e('Admin Notification Email', 'vitapro-appointments-fse'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="admin_notification_email" name="vitapro_appointments_email_settings[admin_notification_email]" value="<?php echo esc_attr(isset($options['admin_notification_email']) ? $options['admin_notification_email'] : get_option('admin_email')); ?>" class="regular-text" />
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
    
    /**
     * Display custom fields page
     */
    public function display_custom_fields_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Custom Fields', 'vitapro-appointments-fse'); ?></h1>
            <p><?php _e('Add custom fields to your booking forms.', 'vitapro-appointments-fse'); ?></p>
            
            <div class="card">
                <h2><?php _e('Custom Fields', 'vitapro-appointments-fse'); ?></h2>
                <p><?php _e('Custom fields functionality will be available in the full version.', 'vitapro-appointments-fse'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display working hours page
     */
    public function display_working_hours_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('Working Hours', 'vitapro-appointments-fse'); ?></h1>
            <p><?php _e('Configure working hours for your business.', 'vitapro-appointments-fse'); ?></p>
            
            <div class="card">
                <h2><?php _e('Working Hours', 'vitapro-appointments-fse'); ?></h2>
                <p><?php _e('Working hours functionality will be available in the full version.', 'vitapro-appointments-fse'); ?></p>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get appointment count
     */
    public function get_appointment_count($type = 'all') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $where_clause = '';
        $current_date = current_time('Y-m-d');
        $current_time = current_time('H:i:s');
        
        switch ($type) {
            case 'today':
                $where_clause = $wpdb->prepare("WHERE appointment_date = %s", $current_date);
                break;
            case 'upcoming':
                $where_clause = $wpdb->prepare("WHERE (appointment_date > %s OR (appointment_date = %s AND appointment_time > %s)) AND status != 'cancelled'", $current_date, $current_date, $current_time);
                break;
            case 'pending':
                $where_clause = "WHERE status = 'pending'";
                break;
            case 'completed':
                $where_clause = "WHERE status = 'completed'";
                break;
        }
        
        $sql = "SELECT COUNT(*) FROM {$table_name} {$where_clause}";
        $count = $wpdb->get_var($sql);
        
        return $count ? $count : 0;
    }
    
    /**
     * Display recent appointments
     */
    public function display_recent_appointments() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $sql = "SELECT * FROM {$table_name} ORDER BY created_at DESC LIMIT 5";
        $appointments = $wpdb->get_results($sql);
        
        if (empty($appointments)) {
            echo '<p>' . __('No recent appointments found.', 'vitapro-appointments-fse') . '</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>' . __('Customer', 'vitapro-appointments-fse') . '</th>';
        echo '<th>' . __('Service', 'vitapro-appointments-fse') . '</th>';
        echo '<th>' . __('Date', 'vitapro-appointments-fse') . '</th>';
        echo '<th>' . __('Status', 'vitapro-appointments-fse') . '</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($appointments as $appointment) {
            $service_title = get_the_title($appointment->service_id);
            $date_format = get_option('date_format');
            $formatted_date = date_i18n($date_format, strtotime($appointment->appointment_date));
            
            echo '<tr>';
            echo '<td>' . esc_html($appointment->customer_name) . '</td>';
            echo '<td>' . esc_html($service_title) . '</td>';
            echo '<td>' . esc_html($formatted_date) . '</td>';
            echo '<td><span class="status-' . esc_attr($appointment->status) . '">' . esc_html(ucfirst($appointment->status)) . '</span></td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    }
}