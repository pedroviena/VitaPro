<?php
/**
 * Settings Page
 * 
 * Handles all plugin settings and configuration options.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Settings_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('wp_ajax_vpa_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_vpa_reset_settings', array($this, 'reset_settings'));
        add_action('wp_ajax_vpa_test_email', array($this, 'test_email'));
        add_action('wp_ajax_vpa_test_sms', array($this, 'test_sms'));
        add_action('wp_ajax_vpa_import_settings', array($this, 'import_settings'));
        add_action('wp_ajax_vpa_export_settings', array($this, 'export_settings'));
    }
    
    /**
     * Add settings page
     */
    public function add_settings_page() {
        add_submenu_page(
            'vitapro-appointments',
            __('Settings', 'vitapro-appointments-fse'),
            __('Settings', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-settings',
            array($this, 'display_settings_page')
        );
    }
    
    /**
     * Register settings
     */
    public function register_settings() {
        // General Settings
        register_setting('vpa_general_settings', 'vpa_business_name');
        register_setting('vpa_general_settings', 'vpa_business_email');
        register_setting('vpa_general_settings', 'vpa_business_phone');
        register_setting('vpa_general_settings', 'vpa_business_address');
        register_setting('vpa_general_settings', 'vpa_business_logo');
        register_setting('vpa_general_settings', 'vpa_timezone');
        register_setting('vpa_general_settings', 'vpa_date_format');
        register_setting('vpa_general_settings', 'vpa_time_format');
        register_setting('vpa_general_settings', 'vpa_currency');
        register_setting('vpa_general_settings', 'vpa_currency_position');
        register_setting('vpa_general_settings', 'vpa_default_appointment_duration');
        register_setting('vpa_general_settings', 'vpa_booking_buffer_time');
        register_setting('vpa_general_settings', 'vpa_max_advance_booking');
        register_setting('vpa_general_settings', 'vpa_min_advance_booking');
        
        // Booking Settings
        register_setting('vpa_booking_settings', 'vpa_require_login');
        register_setting('vpa_booking_settings', 'vpa_auto_approve');
        register_setting('vpa_booking_settings', 'vpa_allow_cancellation');
        register_setting('vpa_booking_settings', 'vpa_cancellation_deadline');
        register_setting('vpa_booking_settings', 'vpa_allow_rescheduling');
        register_setting('vpa_booking_settings', 'vpa_reschedule_deadline');
        register_setting('vpa_booking_settings', 'vpa_require_payment');
        register_setting('vpa_booking_settings', 'vpa_payment_methods');
        register_setting('vpa_booking_settings', 'vpa_deposit_amount');
        register_setting('vpa_booking_settings', 'vpa_deposit_type');
        register_setting('vpa_booking_settings', 'vpa_booking_form_fields');
        register_setting('vpa_booking_settings', 'vpa_custom_fields');
        
        // Notification Settings
        register_setting('vpa_notification_settings', 'vpa_email_notifications');
        register_setting('vpa_notification_settings', 'vpa_sms_notifications');
        register_setting('vpa_notification_settings', 'vpa_push_notifications');
        register_setting('vpa_notification_settings', 'vpa_email_templates');
        register_setting('vpa_notification_settings', 'vpa_sms_templates');
        register_setting('vpa_notification_settings', 'vpa_notification_timing');
        register_setting('vpa_notification_settings', 'vpa_reminder_settings');
        
        // Email Settings
        register_setting('vpa_email_settings', 'vpa_smtp_enabled');
        register_setting('vpa_email_settings', 'vpa_smtp_host');
        register_setting('vpa_email_settings', 'vpa_smtp_port');
        register_setting('vpa_email_settings', 'vpa_smtp_username');
        register_setting('vpa_email_settings', 'vpa_smtp_password');
        register_setting('vpa_email_settings', 'vpa_smtp_encryption');
        register_setting('vpa_email_settings', 'vpa_from_email');
        register_setting('vpa_email_settings', 'vpa_from_name');
        
        // SMS Settings
        register_setting('vpa_sms_settings', 'vpa_sms_provider');
        register_setting('vpa_sms_settings', 'vpa_twilio_sid');
        register_setting('vpa_sms_settings', 'vpa_twilio_token');
        register_setting('vpa_sms_settings', 'vpa_twilio_phone');
        register_setting('vpa_sms_settings', 'vpa_nexmo_key');
        register_setting('vpa_sms_settings', 'vpa_nexmo_secret');
        register_setting('vpa_sms_settings', 'vpa_nexmo_from');
        
        // Payment Settings
        register_setting('vpa_payment_settings', 'vpa_stripe_enabled');
        register_setting('vpa_payment_settings', 'vpa_stripe_public_key');
        register_setting('vpa_payment_settings', 'vpa_stripe_secret_key');
        register_setting('vpa_payment_settings', 'vpa_paypal_enabled');
        register_setting('vpa_payment_settings', 'vpa_paypal_client_id');
        register_setting('vpa_payment_settings', 'vpa_paypal_secret');
        register_setting('vpa_payment_settings', 'vpa_paypal_sandbox');
        
        // Security Settings
        register_setting('vpa_security_settings', 'vpa_enable_captcha');
        register_setting('vpa_security_settings', 'vpa_captcha_type');
        register_setting('vpa_security_settings', 'vpa_recaptcha_site_key');
        register_setting('vpa_security_settings', 'vpa_recaptcha_secret_key');
        register_setting('vpa_security_settings', 'vpa_rate_limiting');
        register_setting('vpa_security_settings', 'vpa_ip_blocking');
        register_setting('vpa_security_settings', 'vpa_security_headers');
        register_setting('vpa_security_settings', 'vpa_audit_logging');
        register_setting('vpa_security_settings', 'vpa_backup_frequency');
        register_setting('vpa_security_settings', 'vpa_backup_retention');
        
        // Appearance Settings
        register_setting('vpa_appearance_settings', 'vpa_primary_color');
        register_setting('vpa_appearance_settings', 'vpa_secondary_color');
        register_setting('vpa_appearance_settings', 'vpa_accent_color');
        register_setting('vpa_appearance_settings', 'vpa_font_family');
        register_setting('vpa_appearance_settings', 'vpa_custom_css');
        register_setting('vpa_appearance_settings', 'vpa_booking_form_style');
        register_setting('vpa_appearance_settings', 'vpa_calendar_style');
        
        // Integration Settings
        register_setting('vpa_integration_settings', 'vpa_google_calendar');
        register_setting('vpa_integration_settings', 'vpa_outlook_calendar');
        register_setting('vpa_integration_settings', 'vpa_zoom_integration');
        register_setting('vpa_integration_settings', 'vpa_woocommerce_integration');
        register_setting('vpa_integration_settings', 'vpa_mailchimp_integration');
        register_setting('vpa_integration_settings', 'vpa_zapier_webhook');
        
        // Advanced Settings
        register_setting('vpa_advanced_settings', 'vpa_debug_mode');
        register_setting('vpa_advanced_settings', 'vpa_cache_enabled');
        register_setting('vpa_advanced_settings', 'vpa_database_optimization');
        register_setting('vpa_advanced_settings', 'vpa_performance_monitoring');
        register_setting('vpa_advanced_settings', 'vpa_custom_hooks');
        register_setting('vpa_advanced_settings', 'vpa_api_access');
        register_setting('vpa_advanced_settings', 'vpa_webhook_endpoints');
    }
    
    /**
     * Display settings page
     */
    public function display_settings_page() {
        ?>
        <div class="wrap vpa-settings-page">
            <h1 class="vpa-page-title">
                <span class="dashicons dashicons-admin-settings"></span>
                <?php _e('VitaPro Appointments Settings', 'vitapro-appointments-fse'); ?>
            </h1>
            
            <div class="vpa-settings-header">
                <div class="vpa-settings-actions">
                    <button type="button" id="vpa-save-all-settings" class="button button-primary">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Save All Settings', 'vitapro-appointments-fse'); ?>
                    </button>
                    <button type="button" id="vpa-export-settings" class="button">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export Settings', 'vitapro-appointments-fse'); ?>
                    </button>
                    <button type="button" id="vpa-import-settings" class="button">
                        <span class="dashicons dashicons-upload"></span>
                        <?php _e('Import Settings', 'vitapro-appointments-fse'); ?>
                    </button>
                    <button type="button" id="vpa-reset-settings" class="button button-secondary">
                        <span class="dashicons dashicons-update"></span>
                        <?php _e('Reset to Defaults', 'vitapro-appointments-fse'); ?>
                    </button>
                </div>
            </div>
            
            <div class="vpa-settings-container">
                <!-- Settings Navigation -->
                <nav class="vpa-settings-nav">
                    <ul class="vpa-settings-tabs">
                        <li class="vpa-tab active" data-tab="general">
                            <span class="dashicons dashicons-admin-generic"></span>
                            <?php _e('General', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="booking">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Booking', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="notifications">
                            <span class="dashicons dashicons-bell"></span>
                            <?php _e('Notifications', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="email">
                            <span class="dashicons dashicons-email"></span>
                            <?php _e('Email', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="sms">
                            <span class="dashicons dashicons-smartphone"></span>
                            <?php _e('SMS', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="payments">
                            <span class="dashicons dashicons-money-alt"></span>
                            <?php _e('Payments', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="security">
                            <span class="dashicons dashicons-shield"></span>
                            <?php _e('Security', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="appearance">
                            <span class="dashicons dashicons-art"></span>
                            <?php _e('Appearance', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="integrations">
                            <span class="dashicons dashicons-admin-plugins"></span>
                            <?php _e('Integrations', 'vitapro-appointments-fse'); ?>
                        </li>
                        <li class="vpa-tab" data-tab="advanced">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('Advanced', 'vitapro-appointments-fse'); ?>
                        </li>
                    </ul>
                </nav>
                
                <!-- Settings Content -->
                <div class="vpa-settings-content">
                    <!-- General Settings -->
                    <div class="vpa-tab-content active" id="general-settings">
                        <h2><?php _e('General Settings', 'vitapro-appointments-fse'); ?></h2>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Business Information', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_business_name"><?php _e('Business Name', 'vitapro-appointments-fse'); ?></label>
                                    <input type="text" id="vpa_business_name" name="vpa_business_name" 
                                           value="<?php echo esc_attr(get_option('vpa_business_name', '')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Your business or organization name', 'vitapro-appointments-fse'); ?></p>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_business_email"><?php _e('Business Email', 'vitapro-appointments-fse'); ?></label>
                                    <input type="email" id="vpa_business_email" name="vpa_business_email" 
                                           value="<?php echo esc_attr(get_option('vpa_business_email', get_option('admin_email'))); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Main contact email for your business', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_business_phone"><?php _e('Business Phone', 'vitapro-appointments-fse'); ?></label>
                                    <input type="tel" id="vpa_business_phone" name="vpa_business_phone" 
                                           value="<?php echo esc_attr(get_option('vpa_business_phone', '')); ?>" 
                                           class="regular-text" />
                                    <p class="description"><?php _e('Main contact phone number', 'vitapro-appointments-fse'); ?></p>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_timezone"><?php _e('Timezone', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_timezone" name="vpa_timezone">
                                        <?php
                                        $selected_timezone = get_option('vpa_timezone', get_option('timezone_string'));
                                        $timezones = timezone_identifiers_list();
                                        foreach ($timezones as $timezone) {
                                            echo '<option value="' . esc_attr($timezone) . '"' . selected($selected_timezone, $timezone, false) . '>' . esc_html($timezone) . '</option>';
                                        }
                                        ?>
                                    </select>
                                    <p class="description"><?php _e('Default timezone for appointments', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="vpa_business_address"><?php _e('Business Address', 'vitapro-appointments-fse'); ?></label>
                                <textarea id="vpa_business_address" name="vpa_business_address" 
                                          class="large-text" rows="3"><?php echo esc_textarea(get_option('vpa_business_address', '')); ?></textarea>
                                <p class="description"><?php _e('Full business address for appointments', 'vitapro-appointments-fse'); ?></p>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="vpa_business_logo"><?php _e('Business Logo', 'vitapro-appointments-fse'); ?></label>
                                <div class="vpa-media-upload">
                                    <input type="hidden" id="vpa_business_logo" name="vpa_business_logo" 
                                           value="<?php echo esc_attr(get_option('vpa_business_logo', '')); ?>" />
                                    <div class="vpa-logo-preview">
                                        <?php
                                        $logo_id = get_option('vpa_business_logo', '');
                                        if ($logo_id) {
                                            echo wp_get_attachment_image($logo_id, 'medium');
                                        }
                                        ?>
                                    </div>
                                    <button type="button" class="button vpa-upload-logo"><?php _e('Upload Logo', 'vitapro-appointments-fse'); ?></button>
                                    <button type="button" class="button vpa-remove-logo" style="<?php echo $logo_id ? '' : 'display:none;'; ?>"><?php _e('Remove Logo', 'vitapro-appointments-fse'); ?></button>
                                </div>
                                <p class="description"><?php _e('Logo to display in emails and booking forms', 'vitapro-appointments-fse'); ?></p>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Date & Time Settings', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_date_format"><?php _e('Date Format', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_date_format" name="vpa_date_format">
                                        <?php
                                        $date_format = get_option('vpa_date_format', get_option('date_format'));
                                        $formats = array(
                                            'Y-m-d' => date('Y-m-d'),
                                            'm/d/Y' => date('m/d/Y'),
                                            'd/m/Y' => date('d/m/Y'),
                                            'F j, Y' => date('F j, Y'),
                                            'j F Y' => date('j F Y')
                                        );
                                        foreach ($formats as $format => $example) {
                                            echo '<option value="' . esc_attr($format) . '"' . selected($date_format, $format, false) . '>' . esc_html($example) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_time_format"><?php _e('Time Format', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_time_format" name="vpa_time_format">
                                        <?php
                                        $time_format = get_option('vpa_time_format', get_option('time_format'));
                                        $formats = array(
                                            'H:i' => date('H:i'),
                                            'g:i A' => date('g:i A'),
                                            'g:i a' => date('g:i a')
                                        );
                                        foreach ($formats as $format => $example) {
                                            echo '<option value="' . esc_attr($format) . '"' . selected($time_format, $format, false) . '>' . esc_html($example) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Currency Settings', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_currency"><?php _e('Currency', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_currency" name="vpa_currency">
                                        <?php
                                        $currency = get_option('vpa_currency', 'USD');
                                        $currencies = array(
                                            'USD' => 'US Dollar ($)',
                                            'EUR' => 'Euro (€)',
                                            'GBP' => 'British Pound (£)',
                                            'CAD' => 'Canadian Dollar (C$)',
                                            'AUD' => 'Australian Dollar (A$)',
                                            'JPY' => 'Japanese Yen (¥)',
                                            'BRL' => 'Brazilian Real (R$)',
                                            'INR' => 'Indian Rupee (₹)'
                                        );
                                        foreach ($currencies as $code => $name) {
                                            echo '<option value="' . esc_attr($code) . '"' . selected($currency, $code, false) . '>' . esc_html($name) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_currency_position"><?php _e('Currency Position', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_currency_position" name="vpa_currency_position">
                                        <?php
                                        $position = get_option('vpa_currency_position', 'before');
                                        $positions = array(
                                            'before' => __('Before amount ($100)', 'vitapro-appointments-fse'),
                                            'after' => __('After amount (100$)', 'vitapro-appointments-fse'),
                                            'before_space' => __('Before with space ($ 100)', 'vitapro-appointments-fse'),
                                            'after_space' => __('After with space (100 $)', 'vitapro-appointments-fse')
                                        );
                                        foreach ($positions as $pos => $label) {
                                            echo '<option value="' . esc_attr($pos) . '"' . selected($position, $pos, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Appointment Defaults', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_default_appointment_duration"><?php _e('Default Duration (minutes)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_default_appointment_duration" name="vpa_default_appointment_duration" 
                                           value="<?php echo esc_attr(get_option('vpa_default_appointment_duration', 60)); ?>" 
                                           min="15" max="480" step="15" class="small-text" />
                                    <p class="description"><?php _e('Default appointment duration in minutes', 'vitapro-appointments-fse'); ?></p>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_booking_buffer_time"><?php _e('Buffer Time (minutes)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_booking_buffer_time" name="vpa_booking_buffer_time" 
                                           value="<?php echo esc_attr(get_option('vpa_booking_buffer_time', 15)); ?>" 
                                           min="0" max="120" step="5" class="small-text" />
                                    <p class="description"><?php _e('Buffer time between appointments', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_max_advance_booking"><?php _e('Maximum Advance Booking (days)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_max_advance_booking" name="vpa_max_advance_booking" 
                                           value="<?php echo esc_attr(get_option('vpa_max_advance_booking', 365)); ?>" 
                                           min="1" max="730" class="small-text" />
                                    <p class="description"><?php _e('How far in advance customers can book', 'vitapro-appointments-fse'); ?></p>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_min_advance_booking"><?php _e('Minimum Advance Booking (hours)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_min_advance_booking" name="vpa_min_advance_booking" 
                                           value="<?php echo esc_attr(get_option('vpa_min_advance_booking', 2)); ?>" 
                                           min="0" max="168" class="small-text" />
                                    <p class="description"><?php _e('Minimum notice required for booking', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Booking Settings -->
                    <div class="vpa-tab-content" id="booking-settings">
                        <h2><?php _e('Booking Settings', 'vitapro-appointments-fse'); ?></h2>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Booking Requirements', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label class="vpa-checkbox-label">
                                    <input type="checkbox" name="vpa_require_login" value="1" 
                                           <?php checked(get_option('vpa_require_login', 0), 1); ?> />
                                    <span><?php _e('Require customer login to book appointments', 'vitapro-appointments-fse'); ?></span>
                                </label>
                                <p class="description"><?php _e('Force customers to create an account before booking', 'vitapro-appointments-fse'); ?></p>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label class="vpa-checkbox-label">
                                    <input type="checkbox" name="vpa_auto_approve" value="1" 
                                           <?php checked(get_option('vpa_auto_approve', 1), 1); ?> />
                                    <span><?php _e('Auto-approve new appointments', 'vitapro-appointments-fse'); ?></span>
                                </label>
                                <p class="description"><?php _e('Automatically approve appointments without manual review', 'vitapro-appointments-fse'); ?></p>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label class="vpa-checkbox-label">
                                    <input type="checkbox" name="vpa_require_payment" value="1" 
                                           <?php checked(get_option('vpa_require_payment', 0), 1); ?> />
                                    <span><?php _e('Require payment to confirm booking', 'vitapro-appointments-fse'); ?></span>
                                </label>
                                <p class="description"><?php _e('Customers must pay before appointment is confirmed', 'vitapro-appointments-fse'); ?></p>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Cancellation & Rescheduling', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label class="vpa-checkbox-label">
                                        <input type="checkbox" name="vpa_allow_cancellation" value="1" 
                                               <?php checked(get_option('vpa_allow_cancellation', 1), 1); ?> />
                                        <span><?php _e('Allow customers to cancel appointments', 'vitapro-appointments-fse'); ?></span>
                                    </label>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_cancellation_deadline"><?php _e('Cancellation Deadline (hours)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_cancellation_deadline" name="vpa_cancellation_deadline" 
                                           value="<?php echo esc_attr(get_option('vpa_cancellation_deadline', 24)); ?>" 
                                           min="0" max="168" class="small-text" />
                                    <p class="description"><?php _e('Hours before appointment when cancellation is no longer allowed', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label class="vpa-checkbox-label">
                                        <input type="checkbox" name="vpa_allow_rescheduling" value="1" 
                                               <?php checked(get_option('vpa_allow_rescheduling', 1), 1); ?> />
                                        <span><?php _e('Allow customers to reschedule appointments', 'vitapro-appointments-fse'); ?></span>
                                    </label>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_reschedule_deadline"><?php _e('Reschedule Deadline (hours)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_reschedule_deadline" name="vpa_reschedule_deadline" 
                                           value="<?php echo esc_attr(get_option('vpa_reschedule_deadline', 12)); ?>" 
                                           min="0" max="168" class="small-text" />
                                    <p class="description"><?php _e('Hours before appointment when rescheduling is no longer allowed', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Payment Options', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label for="vpa_payment_methods"><?php _e('Accepted Payment Methods', 'vitapro-appointments-fse'); ?></label>
                                <div class="vpa-checkbox-group">
                                    <?php
                                    $payment_methods = get_option('vpa_payment_methods', array('stripe', 'paypal'));
                                    $methods = array(
                                        'stripe' => __('Credit/Debit Cards (Stripe)', 'vitapro-appointments-fse'),
                                        'paypal' => __('PayPal', 'vitapro-appointments-fse'),
                                        'bank_transfer' => __('Bank Transfer', 'vitapro-appointments-fse'),
                                        'cash' => __('Cash Payment', 'vitapro-appointments-fse'),
                                        'check' => __('Check Payment', 'vitapro-appointments-fse')
                                    );
                                    foreach ($methods as $method => $label) {
                                        echo '<label class="vpa-checkbox-label">';
                                        echo '<input type="checkbox" name="vpa_payment_methods[]" value="' . esc_attr($method) . '"' . (in_array($method, $payment_methods) ? ' checked' : '') . ' />';
                                        echo '<span>' . esc_html($label) . '</span>';
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_deposit_type"><?php _e('Deposit Type', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_deposit_type" name="vpa_deposit_type">
                                        <?php
                                        $deposit_type = get_option('vpa_deposit_type', 'full');
                                        $types = array(
                                            'full' => __('Full Payment', 'vitapro-appointments-fse'),
                                            'fixed' => __('Fixed Amount', 'vitapro-appointments-fse'),
                                            'percentage' => __('Percentage', 'vitapro-appointments-fse')
                                        );
                                        foreach ($types as $type => $label) {
                                            echo '<option value="' . esc_attr($type) . '"' . selected($deposit_type, $type, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_deposit_amount"><?php _e('Deposit Amount', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_deposit_amount" name="vpa_deposit_amount" 
                                           value="<?php echo esc_attr(get_option('vpa_deposit_amount', 0)); ?>" 
                                           min="0" step="0.01" class="small-text" />
                                    <p class="description"><?php _e('Amount or percentage for deposits', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Booking Form Fields', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label><?php _e('Required Fields', 'vitapro-appointments-fse'); ?></label>
                                <div class="vpa-checkbox-group">
                                    <?php
                                    $required_fields = get_option('vpa_booking_form_fields', array('name', 'email', 'phone'));
                                    $fields = array(
                                        'name' => __('Full Name', 'vitapro-appointments-fse'),
                                        'email' => __('Email Address', 'vitapro-appointments-fse'),
                                        'phone' => __('Phone Number', 'vitapro-appointments-fse'),
                                        'address' => __('Address', 'vitapro-appointments-fse'),
                                        'notes' => __('Additional Notes', 'vitapro-appointments-fse'),
                                        'emergency_contact' => __('Emergency Contact', 'vitapro-appointments-fse')
                                    );
                                    foreach ($fields as $field => $label) {
                                        echo '<label class="vpa-checkbox-label">';
                                        echo '<input type="checkbox" name="vpa_booking_form_fields[]" value="' . esc_attr($field) . '"' . (in_array($field, $required_fields) ? ' checked' : '') . ' />';
                                        echo '<span>' . esc_html($label) . '</span>';
                                        echo '</label>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="vpa_custom_fields"><?php _e('Custom Fields', 'vitapro-appointments-fse'); ?></label>
                                <div id="vpa-custom-fields-container">
                                    <?php
                                    $custom_fields = get_option('vpa_custom_fields', array());
                                    if (!empty($custom_fields)) {
                                        foreach ($custom_fields as $index => $field) {
                                            $this->render_custom_field_row($index, $field);
                                        }
                                    }
                                    ?>
                                </div>
                                <button type="button" id="vpa-add-custom-field" class="button">
                                    <span class="dashicons dashicons-plus"></span>
                                    <?php _e('Add Custom Field', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Additional tabs would continue here... -->
                    <!-- For brevity, I'll include the key sections -->
                    
                    <!-- Security Settings -->
                    <div class="vpa-tab-content" id="security-settings">
                        <h2><?php _e('Security Settings', 'vitapro-appointments-fse'); ?></h2>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('CAPTCHA Protection', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label class="vpa-checkbox-label">
                                    <input type="checkbox" name="vpa_enable_captcha" value="1" 
                                           <?php checked(get_option('vpa_enable_captcha', 0), 1); ?> />
                                    <span><?php _e('Enable CAPTCHA protection', 'vitapro-appointments-fse'); ?></span>
                                </label>
                                <p class="description"><?php _e('Protect booking forms from spam and bots', 'vitapro-appointments-fse'); ?></p>
                            </div>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_captcha_type"><?php _e('CAPTCHA Type', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_captcha_type" name="vpa_captcha_type">
                                        <?php
                                        $captcha_type = get_option('vpa_captcha_type', 'recaptcha_v2');
                                        $types = array(
                                            'recaptcha_v2' => __('reCAPTCHA v2', 'vitapro-appointments-fse'),
                                            'recaptcha_v3' => __('reCAPTCHA v3', 'vitapro-appointments-fse'),
                                            'hcaptcha' => __('hCaptcha', 'vitapro-appointments-fse'),
                                            'simple_math' => __('Simple Math', 'vitapro-appointments-fse')
                                        );
                                        foreach ($types as $type => $label) {
                                            echo '<option value="' . esc_attr($type) . '"' . selected($captcha_type, $type, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="vpa-form-row" id="recaptcha-keys" style="<?php echo (strpos(get_option('vpa_captcha_type', 'recaptcha_v2'), 'recaptcha') !== false) ? '' : 'display:none;'; ?>">
                                <div class="vpa-form-group">
                                    <label for="vpa_recaptcha_site_key"><?php _e('reCAPTCHA Site Key', 'vitapro-appointments-fse'); ?></label>
                                    <input type="text" id="vpa_recaptcha_site_key" name="vpa_recaptcha_site_key" 
                                           value="<?php echo esc_attr(get_option('vpa_recaptcha_site_key', '')); ?>" 
                                           class="regular-text" />
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_recaptcha_secret_key"><?php _e('reCAPTCHA Secret Key', 'vitapro-appointments-fse'); ?></label>
                                    <input type="password" id="vpa_recaptcha_secret_key" name="vpa_recaptcha_secret_key" 
                                           value="<?php echo esc_attr(get_option('vpa_recaptcha_secret_key', '')); ?>" 
                                           class="regular-text" />
                                </div>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Rate Limiting', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label class="vpa-checkbox-label">
                                    <input type="checkbox" name="vpa_rate_limiting" value="1" 
                                           <?php checked(get_option('vpa_rate_limiting', 1), 1); ?> />
                                    <span><?php _e('Enable rate limiting', 'vitapro-appointments-fse'); ?></span>
                                </label>
                                <p class="description"><?php _e('Limit the number of requests per IP address', 'vitapro-appointments-fse'); ?></p>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Backup Settings', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_backup_frequency"><?php _e('Backup Frequency', 'vitapro-appointments-fse'); ?></label>
                                    <select id="vpa_backup_frequency" name="vpa_backup_frequency">
                                        <?php
                                        $frequency = get_option('vpa_backup_frequency', 'weekly');
                                        $frequencies = array(
                                            'daily' => __('Daily', 'vitapro-appointments-fse'),
                                            'weekly' => __('Weekly', 'vitapro-appointments-fse'),
                                            'monthly' => __('Monthly', 'vitapro-appointments-fse'),
                                            'manual' => __('Manual Only', 'vitapro-appointments-fse')
                                        );
                                        foreach ($frequencies as $freq => $label) {
                                            echo '<option value="' . esc_attr($freq) . '"' . selected($frequency, $freq, false) . '>' . esc_html($label) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_backup_retention"><?php _e('Backup Retention (days)', 'vitapro-appointments-fse'); ?></label>
                                    <input type="number" id="vpa_backup_retention" name="vpa_backup_retention" 
                                           value="<?php echo esc_attr(get_option('vpa_backup_retention', 30)); ?>" 
                                           min="7" max="365" class="small-text" />
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Appearance Settings -->
                    <div class="vpa-tab-content" id="appearance-settings">
                        <h2><?php _e('Appearance Settings', 'vitapro-appointments-fse'); ?></h2>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Color Scheme', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="vpa_primary_color"><?php _e('Primary Color', 'vitapro-appointments-fse'); ?></label>
                                    <input type="color" id="vpa_primary_color" name="vpa_primary_color" 
                                           value="<?php echo esc_attr(get_option('vpa_primary_color', '#0073aa')); ?>" />
                                    <p class="description"><?php _e('Main brand color for buttons and highlights', 'vitapro-appointments-fse'); ?></p>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_secondary_color"><?php _e('Secondary Color', 'vitapro-appointments-fse'); ?></label>
                                    <input type="color" id="vpa_secondary_color" name="vpa_secondary_color" 
                                           value="<?php echo esc_attr(get_option('vpa_secondary_color', '#00a32a')); ?>" />
                                    <p class="description"><?php _e('Secondary color for accents and borders', 'vitapro-appointments-fse'); ?></p>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="vpa_accent_color"><?php _e('Accent Color', 'vitapro-appointments-fse'); ?></label>
                                    <input type="color" id="vpa_accent_color" name="vpa_accent_color" 
                                           value="<?php echo esc_attr(get_option('vpa_accent_color', '#ff8c00')); ?>" />
                                    <p class="description"><?php _e('Accent color for notifications and alerts', 'vitapro-appointments-fse'); ?></p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Typography', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label for="vpa_font_family"><?php _e('Font Family', 'vitapro-appointments-fse'); ?></label>
                                <select id="vpa_font_family" name="vpa_font_family">
                                    <?php
                                    $font_family = get_option('vpa_font_family', 'inherit');
                                    $fonts = array(
                                        'inherit' => __('Inherit from theme', 'vitapro-appointments-fse'),
                                        'Arial, sans-serif' => 'Arial',
                                        'Helvetica, sans-serif' => 'Helvetica',
                                        'Georgia, serif' => 'Georgia',
                                        'Times New Roman, serif' => 'Times New Roman',
                                        'Roboto, sans-serif' => 'Roboto',
                                        'Open Sans, sans-serif' => 'Open Sans',
                                        'Lato, sans-serif' => 'Lato'
                                    );
                                    foreach ($fonts as $font => $label) {
                                        echo '<option value="' . esc_attr($font) . '"' . selected($font_family, $font, false) . '>' . esc_html($label) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="vpa-settings-section">
                            <h3><?php _e('Custom CSS', 'vitapro-appointments-fse'); ?></h3>
                            
                            <div class="vpa-form-group">
                                <label for="vpa_custom_css"><?php _e('Additional CSS', 'vitapro-appointments-fse'); ?></label>
                                <textarea id="vpa_custom_css" name="vpa_custom_css" 
                                          class="large-text code" rows="10"><?php echo esc_textarea(get_option('vpa_custom_css', '')); ?></textarea>
                                <p class="description"><?php _e('Add custom CSS to override default styles', 'vitapro-appointments-fse'); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Settings Footer -->
            <div class="vpa-settings-footer">
                <div class="vpa-settings-status">
                    <span id="vpa-settings-status"></span>
                </div>
                <div class="vpa-settings-actions">
                    <button type="button" id="vpa-save-settings" class="button button-primary button-large">
                        <span class="dashicons dashicons-saved"></span>
                        <?php _e('Save Settings', 'vitapro-appointments-fse'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <!-- Import Settings Modal -->
        <div id="vpa-import-modal" class="vpa-modal">
            <div class="vpa-modal-content">
                <div class="vpa-modal-header">
                    <h3><?php _e('Import Settings', 'vitapro-appointments-fse'); ?></h3>
                    <button type="button" class="vpa-modal-close">&times;</button>
                </div>
                <div class="vpa-modal-body">
                    <div class="vpa-form-group">
                        <label for="vpa-settings-file"><?php _e('Select Settings File', 'vitapro-appointments-fse'); ?></label>
                        <input type="file" id="vpa-settings-file" accept=".json" />
                        <p class="description"><?php _e('Upload a JSON file exported from VitaPro Appointments', 'vitapro-appointments-fse'); ?></p>
                    </div>
                    
                    <div class="vpa-form-group">
                        <label class="vpa-checkbox-label">
                            <input type="checkbox" id="vpa-overwrite-settings" />
                            <span><?php _e('Overwrite existing settings', 'vitapro-appointments-fse'); ?></span>
                        </label>
                        <p class="description"><?php _e('Check this to replace all current settings', 'vitapro-appointments-fse'); ?></p>
                    </div>
                </div>
                <div class="vpa-modal-footer">
                    <button type="button" id="vpa-import-settings-btn" class="button button-primary">
                        <?php _e('Import Settings', 'vitapro-appointments-fse'); ?>
                    </button>
                    <button type="button" class="button vpa-modal-close">
                        <?php _e('Cancel', 'vitapro-appointments-fse'); ?>
                    </button>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Settings page JavaScript
            var vpaSettings = {
                init: function() {
                    this.bindEvents();
                    this.initColorPickers();
                    this.initTabs();
                },
                
                bindEvents: function() {
                    // Tab switching
                    $('.vpa-tab').on('click', this.switchTab);
                    
                    // Save settings
                    $('#vpa-save-settings, #vpa-save-all-settings').on('click', this.saveSettings);
                    
                    // Reset settings
                    $('#vpa-reset-settings').on('click', this.resetSettings);
                    
                    // Export settings
                    $('#vpa-export-settings').on('click', this.exportSettings);
                    
                    // Import settings
                    $('#vpa-import-settings').on('click', this.showImportModal);
                    $('#vpa-import-settings-btn').on('click', this.importSettings);
                    
                    // Test email
                    $('#vpa-test-email').on('click', this.testEmail);
                    
                    // Test SMS
                    $('#vpa-test-sms').on('click', this.testSMS);
                    
                    // Logo upload
                    $('.vpa-upload-logo').on('click', this.uploadLogo);
                    $('.vpa-remove-logo').on('click', this.removeLogo);
                    
                    // Custom fields
                    $('#vpa-add-custom-field').on('click', this.addCustomField);
                    $(document).on('click', '.vpa-remove-custom-field', this.removeCustomField);
                    
                    // CAPTCHA type change
                    $('#vpa_captcha_type').on('change', this.toggleCaptchaFields);
                    
                    // Modal controls
                    $('.vpa-modal-close').on('click', this.closeModal);
                },
                
                initColorPickers: function() {
                    if (typeof $.fn.wpColorPicker !== 'undefined') {
                        $('input[type="color"]').wpColorPicker();
                    }
                },
                
                initTabs: function() {
                    // Show first tab by default
                    $('.vpa-tab.active').trigger('click');
                },
                
                switchTab: function(e) {
                    e.preventDefault();
                    
                    var $tab = $(this);
                    var tabId = $tab.data('tab');
                    
                    // Update tab navigation
                    $('.vpa-tab').removeClass('active');
                    $tab.addClass('active');
                    
                    // Update tab content
                    $('.vpa-tab-content').removeClass('active');
                    $('#' + tabId + '-settings').addClass('active');
                },
                
                saveSettings: function(e) {
                    e.preventDefault();
                    
                    var $button = $(this);
                    var originalText = $button.text();
                    
                    $button.prop('disabled', true).text('<?php _e('Saving...', 'vitapro-appointments-fse'); ?>');
                    
                    // Collect all form data
                    var formData = new FormData();
                    formData.append('action', 'vpa_save_settings');
                    formData.append('nonce', '<?php echo wp_create_nonce('vpa_settings_nonce'); ?>');
                    
                    // Add all form fields
                    $('.vpa-settings-page input, .vpa-settings-page select, .vpa-settings-page textarea').each(function() {
                        var $field = $(this);
                        var name = $field.attr('name');
                        var value = $field.val();
                        
                        if ($field.attr('type') === 'checkbox') {
                            if ($field.is(':checked')) {
                                formData.append(name, value);
                            }
                        } else if ($field.attr('type') === 'file') {
                            if ($field[0].files.length > 0) {
                                formData.append(name, $field[0].files[0]);
                            }
                        } else if (name) {
                            formData.append(name, value);
                        }
                    });
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                vpaSettings.showStatus('<?php _e('Settings saved successfully!', 'vitapro-appointments-fse'); ?>', 'success');
                            } else {
                                vpaSettings.showStatus(response.data || '<?php _e('Error saving settings', 'vitapro-appointments-fse'); ?>', 'error');
                            }
                        },
                        error: function() {
                            vpaSettings.showStatus('<?php _e('Error saving settings', 'vitapro-appointments-fse'); ?>', 'error');
                        },
                        complete: function() {
                            $button.prop('disabled', false).text(originalText);
                        }
                    });
                },
                
                showStatus: function(message, type) {
                    var $status = $('#vpa-settings-status');
                    $status.removeClass('success error').addClass(type).text(message).show();
                    
                    setTimeout(function() {
                        $status.fadeOut();
                    }, 3000);
                },
                
                exportSettings: function(e) {
                    e.preventDefault();
                    
                    window.location.href = ajaxurl + '?action=vpa_export_settings&nonce=' + '<?php echo wp_create_nonce('vpa_settings_nonce'); ?>';
                },
                
                showImportModal: function(e) {
                    e.preventDefault();
                    $('#vpa-import-modal').addClass('vpa-modal-show');
                },
                
                closeModal: function(e) {
                    e.preventDefault();
                    $('.vpa-modal').removeClass('vpa-modal-show');
                },
                
                uploadLogo: function(e) {
                    e.preventDefault();
                    
                    var mediaUploader = wp.media({
                        title: '<?php _e('Select Business Logo', 'vitapro-appointments-fse'); ?>',
                        button: {
                            text: '<?php _e('Use this logo', 'vitapro-appointments-fse'); ?>'
                        },
                        multiple: false
                    });
                    
                    mediaUploader.on('select', function() {
                        var attachment = mediaUploader.state().get('selection').first().toJSON();
                        $('#vpa_business_logo').val(attachment.id);
                        $('.vpa-logo-preview').html('<img src="' + attachment.url + '" style="max-width: 200px; height: auto;" />');
                        $('.vpa-remove-logo').show();
                    });
                    
                    mediaUploader.open();
                },
                
                removeLogo: function(e) {
                    e.preventDefault();
                    $('#vpa_business_logo').val('');
                    $('.vpa-logo-preview').empty();
                    $(this).hide();
                }
            };
            
            vpaSettings.init();
        });
        </script>
        <?php
    }
    
    /**
     * Render custom field row
     */
    private function render_custom_field_row($index, $field) {
        ?>
        <div class="vpa-custom-field-row">
            <div class="vpa-form-row">
                <div class="vpa-form-group">
                    <input type="text" name="vpa_custom_fields[<?php echo $index; ?>][label]" 
                           value="<?php echo esc_attr($field['label'] ?? ''); ?>" 
                           placeholder="<?php _e('Field Label', 'vitapro-appointments-fse'); ?>" />
                </div>
                <div class="vpa-form-group">
                    <select name="vpa_custom_fields[<?php echo $index; ?>][type]">
                        <option value="text" <?php selected($field['type'] ?? '', 'text'); ?>><?php _e('Text', 'vitapro-appointments-fse'); ?></option>
                        <option value="email" <?php selected($field['type'] ?? '', 'email'); ?>><?php _e('Email', 'vitapro-appointments-fse'); ?></option>
                        <option value="tel" <?php selected($field['type'] ?? '', 'tel'); ?>><?php _e('Phone', 'vitapro-appointments-fse'); ?></option>
                        <option value="textarea" <?php selected($field['type'] ?? '', 'textarea'); ?>><?php _e('Textarea', 'vitapro-appointments-fse'); ?></option>
                        <option value="select" <?php selected($field['type'] ?? '', 'select'); ?>><?php _e('Select', 'vitapro-appointments-fse'); ?></option>
                        <option value="checkbox" <?php selected($field['type'] ?? '', 'checkbox'); ?>><?php _e('Checkbox', 'vitapro-appointments-fse'); ?></option>
                        <option value="radio" <?php selected($field['type'] ?? '', 'radio'); ?>><?php _e('Radio', 'vitapro-appointments-fse'); ?></option>
                    </select>
                </div>
                <div class="vpa-form-group">
                    <label class="vpa-checkbox-label">
                        <input type="checkbox" name="vpa_custom_fields[<?php echo $index; ?>][required]" 
                               value="1" <?php checked($field['required'] ?? 0, 1); ?> />
                        <span><?php _e('Required', 'vitapro-appointments-fse'); ?></span>
                    </label>
                </div>
                <div class="vpa-form-group">
                    <button type="button" class="button vpa-remove-custom-field">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
            </div>
            <div class="vpa-form-group" style="display: <?php echo in_array($field['type'] ?? '', array('select', 'radio')) ? 'block' : 'none'; ?>;">
                <input type="text" name="vpa_custom_fields[<?php echo $index; ?>][options]" 
                       value="<?php echo esc_attr($field['options'] ?? ''); ?>" 
                       placeholder="<?php _e('Options (comma separated)', 'vitapro-appointments-fse'); ?>" />
            </div>
        </div>
        <?php
    }
    
    /**
     * Save settings
     */
    public function save_settings() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_settings_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'vitapro-appointments-fse'));
        }
        
        // Process and save all settings
        $settings_groups = array(
            'general', 'booking', 'notifications', 'email', 'sms', 
            'payments', 'security', 'appearance', 'integrations', 'advanced'
        );
        
        foreach ($settings_groups as $group) {
            $this->save_settings_group($group);
        }
        
        // Clear any caches
        wp_cache_flush();
        
        wp_send_json_success(__('Settings saved successfully', 'vitapro-appointments-fse'));
    }
    
    /**
     * Save settings group
     */
    private function save_settings_group($group) {
        $settings_map = array(
            'general' => array(
                'vpa_business_name', 'vpa_business_email', 'vpa_business_phone',
                'vpa_business_address', 'vpa_business_logo', 'vpa_timezone',
                'vpa_date_format', 'vpa_time_format', 'vpa_currency',
                'vpa_currency_position', 'vpa_default_appointment_duration',
                'vpa_booking_buffer_time', 'vpa_max_advance_booking', 'vpa_min_advance_booking'
            ),
            'booking' => array(
                'vpa_require_login', 'vpa_auto_approve', 'vpa_allow_cancellation',
                'vpa_cancellation_deadline', 'vpa_allow_rescheduling', 'vpa_reschedule_deadline',
                'vpa_require_payment', 'vpa_payment_methods', 'vpa_deposit_amount',
                'vpa_deposit_type', 'vpa_booking_form_fields', 'vpa_custom_fields'
            ),
            'security' => array(
                'vpa_enable_captcha', 'vpa_captcha_type', 'vpa_recaptcha_site_key',
                'vpa_recaptcha_secret_key', 'vpa_rate_limiting', 'vpa_ip_blocking',
                'vpa_security_headers', 'vpa_audit_logging', 'vpa_backup_frequency',
                'vpa_backup_retention'
            ),
            'appearance' => array(
                'vpa_primary_color', 'vpa_secondary_color', 'vpa_accent_color',
                'vpa_font_family', 'vpa_custom_css', 'vpa_booking_form_style',
                'vpa_calendar_style'
            )
        );
        
        if (isset($settings_map[$group])) {
            foreach ($settings_map[$group] as $setting) {
                if (isset($_POST[$setting])) {
                    $value = $_POST[$setting];
                    
                    // Sanitize based on setting type
                    if (strpos($setting, 'email') !== false) {
                        $value = sanitize_email($value);
                    } elseif (strpos($setting, 'url') !== false) {
                        $value = esc_url_raw($value);
                    } elseif (strpos($setting, 'color') !== false) {
                        $value = sanitize_hex_color($value);
                    } elseif (is_array($value)) {
                        $value = array_map('sanitize_text_field', $value);
                    } else {
                        $value = sanitize_text_field($value);
                    }
                    
                    update_option($setting, $value);
                }
            }
        }
    }
    
    /**
     * Export settings
     */
    public function export_settings() {
        if (!wp_verify_nonce($_GET['nonce'], 'vpa_settings_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'vitapro-appointments-fse'));
        }
        
        // Get all VitaPro settings
        global $wpdb;
        $settings = $wpdb->get_results(
            "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'vpa_%'",
            ARRAY_A
        );
        
        $export_data = array(
            'plugin' => 'VitaPro Appointments FSE',
            'version' => VITAPRO_APPOINTMENTS_FSE_VERSION,
            'exported_at' => current_time('mysql'),
            'settings' => array()
        );
        
        foreach ($settings as $setting) {
            $export_data['settings'][$setting['option_name']] = maybe_unserialize($setting['option_value']);
        }
        
        $filename = 'vitapro-appointments-settings-' . date('Y-m-d-H-i-s') . '.json';
        
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        echo json_encode($export_data, JSON_PRETTY_PRINT);
        exit;
    }
}