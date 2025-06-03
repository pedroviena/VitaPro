<?php
/**
 * Overview/Presentation Page
 * 
 * Displays plugin features, information, and getting started guide.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Overview_Page {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_overview_page'));
        add_action('wp_ajax_vpa_quick_setup', array($this, 'quick_setup'));
        add_action('wp_ajax_vpa_dismiss_notice', array($this, 'dismiss_notice'));
    }
    
    /**
     * Add overview page
     */
    public function add_overview_page() {
        add_submenu_page(
            'vitapro-appointments',
            __('Overview', 'vitapro-appointments-fse'),
            __('Overview', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-overview',
            array($this, 'display_overview_page'),
            0 // Position at top
        );
    }
    
    /**
     * Display overview page
     */
    public function display_overview_page() {
        ?>
        <div class="wrap vpa-overview-page">
            <!-- Header Section -->
            <div class="vpa-overview-header">
                <div class="vpa-header-content">
                    <div class="vpa-logo-section">
                        <div class="vpa-plugin-logo">
                            <span class="dashicons dashicons-calendar-alt"></span>
                        </div>
                        <div class="vpa-plugin-info">
                            <h1><?php _e('VitaPro Appointments FSE', 'vitapro-appointments-fse'); ?></h1>
                            <p class="vpa-version"><?php printf(__('Version %s', 'vitapro-appointments-fse'), VITAPRO_APPOINTMENTS_FSE_VERSION); ?></p>
                            <p class="vpa-tagline"><?php _e('Professional Healthcare Appointment Booking System', 'vitapro-appointments-fse'); ?></p>
                        </div>
                    </div>
                    
                    <div class="vpa-quick-actions">
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-settings'); ?>" class="button button-primary button-large">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e('Settings', 'vitapro-appointments-fse'); ?>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-calendar'); ?>" class="button button-secondary button-large">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Calendar', 'vitapro-appointments-fse'); ?>
                        </a>
                        <button type="button" id="vpa-quick-setup" class="button button-secondary button-large">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('Quick Setup', 'vitapro-appointments-fse'); ?>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Stats Overview -->
            <div class="vpa-stats-overview">
                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo $this->get_total_appointments(); ?></div>
                        <div class="vpa-stat-label"><?php _e('Total Appointments', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>
                
                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo $this->get_total_customers(); ?></div>
                        <div class="vpa-stat-label"><?php _e('Total Customers', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>
                
                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-businessman"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo $this->get_total_professionals(); ?></div>
                        <div class="vpa-stat-label"><?php _e('Professionals', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>
                
                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-products"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo $this->get_total_services(); ?></div>
                        <div class="vpa-stat-label"><?php _e('Services', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Main Content Grid -->
            <div class="vpa-overview-grid">
                <!-- Features Section -->
                <div class="vpa-overview-section vpa-features-section">
                    <h2>
                        <span class="dashicons dashicons-star-filled"></span>
                        <?php _e('Key Features', 'vitapro-appointments-fse'); ?>
                    </h2>
                    
                    <div class="vpa-features-grid">
                        <div class="vpa-feature-card">
                            <div class="vpa-feature-icon">
                                <span class="dashicons dashicons-calendar-alt"></span>
                            </div>
                            <h3><?php _e('Advanced Booking System', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Comprehensive appointment booking with real-time availability, automated confirmations, and flexible scheduling options.', 'vitapro-appointments-fse'); ?></p>
                            <ul>
                                <li><?php _e('Real-time availability checking', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Automated email & SMS confirmations', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Flexible time slot management', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Recurring appointment support', 'vitapro-appointments-fse'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="vpa-feature-card">
                            <div class="vpa-feature-icon">
                                <span class="dashicons dashicons-shield"></span>
                            </div>
                            <h3><?php _e('Enterprise Security', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Bank-level security with comprehensive audit logging, data encryption, and HIPAA compliance features.', 'vitapro-appointments-fse'); ?></p>
                            <ul>
                                <li><?php _e('End-to-end data encryption', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Comprehensive audit logging', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('HIPAA compliance tools', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Advanced user permissions', 'vitapro-appointments-fse'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="vpa-feature-card">
                            <div class="vpa-feature-icon">
                                <span class="dashicons dashicons-chart-bar"></span>
                            </div>
                            <h3><?php _e('Analytics & Reporting', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Powerful analytics dashboard with real-time insights, custom reports, and performance tracking.', 'vitapro-appointments-fse'); ?></p>
                            <ul>
                                <li><?php _e('Real-time analytics dashboard', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Custom report builder', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Revenue tracking & forecasting', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Performance metrics', 'vitapro-appointments-fse'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="vpa-feature-card">
                            <div class="vpa-feature-icon">
                                <span class="dashicons dashicons-money-alt"></span>
                            </div>
                            <h3><?php _e('Payment Processing', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Integrated payment processing with support for multiple gateways, deposits, and automated billing.', 'vitapro-appointments-fse'); ?></p>
                            <ul>
                                <li><?php _e('Stripe & PayPal integration', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Flexible deposit options', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Automated invoicing', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Refund management', 'vitapro-appointments-fse'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="vpa-feature-card">
                            <div class="vpa-feature-icon">
                                <span class="dashicons dashicons-bell"></span>
                            </div>
                            <h3><?php _e('Smart Notifications', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Intelligent notification system with email, SMS, and push notifications for all stakeholders.', 'vitapro-appointments-fse'); ?></p>
                            <ul>
                                <li><?php _e('Multi-channel notifications', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Customizable templates', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Automated reminders', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Real-time alerts', 'vitapro-appointments-fse'); ?></li>
                            </ul>
                        </div>
                        
                        <div class="vpa-feature-card">
                            <div class="vpa-feature-icon">
                                <span class="dashicons dashicons-admin-plugins"></span>
                            </div>
                            <h3><?php _e('Integrations', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Seamless integration with popular tools and services to enhance your workflow.', 'vitapro-appointments-fse'); ?></p>
                            <ul>
                                <li><?php _e('Google Calendar sync', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Zoom meeting integration', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('WooCommerce compatibility', 'vitapro-appointments-fse'); ?></li>
                                <li><?php _e('Elementor widgets', 'vitapro-appointments-fse'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Getting Started Section -->
                <div class="vpa-overview-section vpa-getting-started">
                    <h2>
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php _e('Getting Started', 'vitapro-appointments-fse'); ?>
                    </h2>
                    
                    <div class="vpa-setup-steps">
                        <div class="vpa-step">
                            <div class="vpa-step-number">1</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Configure Basic Settings', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Set up your business information, timezone, and basic preferences.', 'vitapro-appointments-fse'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-settings'); ?>" class="button button-secondary">
                                    <?php _e('Go to Settings', 'vitapro-appointments-fse'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="vpa-step">
                            <div class="vpa-step-number">2</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Add Services & Professionals', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Create your service offerings and add professional staff members.', 'vitapro-appointments-fse'); ?></p>
                                <a href="<?php echo admin_url('edit.php?post_type=vpa_service'); ?>" class="button button-secondary">
                                    <?php _e('Manage Services', 'vitapro-appointments-fse'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="vpa-step">
                            <div class="vpa-step-number">3</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Set Working Hours', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Configure availability schedules for your professionals and services.', 'vitapro-appointments-fse'); ?></p>
                                <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-availability'); ?>" class="button button-secondary">
                                    <?php _e('Set Availability', 'vitapro-appointments-fse'); ?>
                                </a>
                            </div>
                        </div>
                        
                        <div class="vpa-step">
                            <div class="vpa-step-number">4</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Add Booking Forms', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Insert booking forms into your pages using shortcodes or Elementor widgets.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-secondary" onclick="vpaShowShortcodes()">
                                    <?php _e('View Shortcodes', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- System Status -->
                <div class="vpa-overview-section vpa-system-status">
                    <h2>
                        <span class="dashicons dashicons-admin-tools"></span>
                        <?php _e('System Status', 'vitapro-appointments-fse'); ?>
                    </h2>
                    
                    <div class="vpa-status-grid">
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('WordPress Version', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo get_bloginfo('version'); ?>
                                <span class="vpa-status-indicator <?php echo version_compare(get_bloginfo('version'), '5.0', '>=') ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>
                        
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('PHP Version', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo PHP_VERSION; ?>
                                <span class="vpa-status-indicator <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>
                        
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Database', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo $this->check_database_status() ? __('Connected', 'vitapro-appointments-fse') : __('Error', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo $this->check_database_status() ? 'good' : 'error'; ?>"></span>
                            </div>
                        </div>
                        
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Email System', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo $this->check_email_status() ? __('Working', 'vitapro-appointments-fse') : __('Not Configured', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo $this->check_email_status() ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>
                        
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Elementor', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo is_plugin_active('elementor/elementor.php') ? __('Active', 'vitapro-appointments-fse') : __('Not Installed', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo is_plugin_active('elementor/elementor.php') ? 'good' : 'neutral'; ?>"></span>
                            </div>
                        </div>
                        
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Security', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo get_option('vpa_security_headers', 0) ? __('Enhanced', 'vitapro-appointments-fse') : __('Basic', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo get_option('vpa_security_headers', 0) ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="vpa-overview-section vpa-recent-activity">
                    <h2>
                        <span class="dashicons dashicons-clock"></span>
                        <?php _e('Recent Activity', 'vitapro-appointments-fse'); ?>
                    </h2>
                    
                    <div class="vpa-activity-list">
                        <?php $this->display_recent_activity(); ?>
                    </div>
                    
                    <div class="vpa-activity-footer">
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-audit'); ?>" class="button button-secondary">
                            <?php _e('View All Activity', 'vitapro-appointments-fse'); ?>
                        </a>
                    </div>
                </div>
                
                <!-- Quick Links -->
                <div class="vpa-overview-section vpa-quick-links">
                    <h2>
                        <span class="dashicons dashicons-admin-links"></span>
                        <?php _e('Quick Links', 'vitapro-appointments-fse'); ?>
                    </h2>
                    
                    <div class="vpa-links-grid">
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-calendar'); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <span><?php _e('Calendar View', 'vitapro-appointments-fse'); ?></span>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-analytics'); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <span><?php _e('Analytics', 'vitapro-appointments-fse'); ?></span>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-reports'); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-media-spreadsheet"></span>
                            <span><?php _e('Reports', 'vitapro-appointments-fse'); ?></span>
                        </a>
                        
                        <a href="<?php echo admin_url('admin.php?page=vitapro-appointments-backup'); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-backup"></span>
                            <span><?php _e('Backup', 'vitapro-appointments-fse'); ?></span>
                        </a>
                        
                        <a href="<?php echo admin_url('edit.php?post_type=vpa_service'); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-products"></span>
                            <span><?php _e('Services', 'vitapro-appointments-fse'); ?></span>
                        </a>
                        
                        <a href="<?php echo admin_url('edit.php?post_type=vpa_professional'); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-businessman"></span>
                            <span><?php _e('Professionals', 'vitapro-appointments-fse'); ?></span>
                        </a>
                    </div>
                </div>
                
                <!-- Support & Documentation -->
                <div class="vpa-overview-section vpa-support">
                    <h2>
                        <span class="dashicons dashicons-sos"></span>
                        <?php _e('Support & Documentation', 'vitapro-appointments-fse'); ?>
                    </h2>
                    
                    <div class="vpa-support-grid">
                        <div class="vpa-support-item">
                            <h3><?php _e('Documentation', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Comprehensive guides and tutorials to help you get the most out of VitaPro Appointments.', 'vitapro-appointments-fse'); ?></p>
                            <a href="#" class="button button-secondary" target="_blank">
                                <?php _e('View Documentation', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>
                        
                        <div class="vpa-support-item">
                            <h3><?php _e('Video Tutorials', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Step-by-step video guides covering all major features and setup procedures.', 'vitapro-appointments-fse'); ?></p>
                            <a href="#" class="button button-secondary" target="_blank">
                                <?php _e('Watch Tutorials', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>
                        
                        <div class="vpa-support-item">
                            <h3><?php _e('Community Forum', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Connect with other users, share tips, and get help from the community.', 'vitapro-appointments-fse'); ?></p>
                            <a href="#" class="button button-secondary" target="_blank">
                                <?php _e('Join Forum', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>
                        
                        <div class="vpa-support-item">
                            <h3><?php _e('Premium Support', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Get priority support with direct access to our development team.', 'vitapro-appointments-fse'); ?></p>
                            <a href="#" class="button button-primary" target="_blank">
                                <?php _e('Get Support', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shortcodes Modal -->
            <div id="vpa-shortcodes-modal" class="vpa-modal">
                <div class="vpa-modal-content">
                    <div class="vpa-modal-header">
                        <h3><?php _e('Available Shortcodes', 'vitapro-appointments-fse'); ?></h3>
                        <button type="button" class="vpa-modal-close">&times;</button>
                    </div>
                    <div class="vpa-modal-body">
                        <div class="vpa-shortcode-list">
                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Booking Form', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_booking_form]</code>
                                <p><?php _e('Display the main appointment booking form', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_booking_form]">
                                    <?php _e('Copy', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                            
                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Service List', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_services]</code>
                                <p><?php _e('Display a list of available services', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_services]">
                                    <?php _e('Copy', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                            
                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Professional List', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_professionals]</code>
                                <p><?php _e('Display a list of professionals', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_professionals]">
                                    <?php _e('Copy', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                            
                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Calendar View', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_calendar]</code>
                                <p><?php _e('Display a public calendar view', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_calendar]">
                                    <?php _e('Copy', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                            
                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Customer Dashboard', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_customer_dashboard]</code>
                                <p><?php _e('Display customer appointment dashboard', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_customer_dashboard]">
                                    <?php _e('Copy', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Quick setup
            $('#vpa-quick-setup').on('click', function() {
                var $button = $(this);
                var originalText = $button.text();
                
                $button.prop('disabled', true).text('<?php _e('Setting up...', 'vitapro-appointments-fse'); ?>');
                
                $.post(ajaxurl, {
                    action: 'vpa_quick_setup',
                    nonce: '<?php echo wp_create_nonce('vpa_overview_nonce'); ?>'
                })
                .done(function(response) {
                    if (response.success) {
                        alert('<?php _e('Quick setup completed successfully!', 'vitapro-appointments-fse'); ?>');
                        location.reload();
                    } else {
                        alert(response.data || '<?php _e('Setup failed', 'vitapro-appointments-fse'); ?>');
                    }
                })
                .fail(function() {
                    alert('<?php _e('Setup failed', 'vitapro-appointments-fse'); ?>');
                })
                .always(function() {
                    $button.prop('disabled', false).text(originalText);
                });
            });
            
            // Copy shortcode
            $('.vpa-copy-shortcode').on('click', function() {
                var shortcode = $(this).data('shortcode');
                navigator.clipboard.writeText(shortcode).then(function() {
                    alert('<?php _e('Shortcode copied to clipboard!', 'vitapro-appointments-fse'); ?>');
                });
            });
            
            // Modal controls
            $('.vpa-modal-close').on('click', function() {
                $('.vpa-modal').removeClass('vpa-modal-show');
            });
        });
        
        function vpaShowShortcodes() {
            jQuery('#vpa-shortcodes-modal').addClass('vpa-modal-show');
        }
        </script>
        <?php
    }
    
    /**
     * Get total appointments
     */
    private function get_total_appointments() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}") ?: 0;
    }
    
    /**
     * Get total customers
     */
    private function get_total_customers() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        return $wpdb->get_var("SELECT COUNT(DISTINCT customer_email) FROM {$table_name}") ?: 0;
    }
    
    /**
     * Get total professionals
     */
    private function get_total_professionals() {
        return wp_count_posts('vpa_professional')->publish ?: 0;
    }
    
    /**
     * Get total services
     */
    private function get_total_services() {
        return wp_count_posts('vpa_service')->publish ?: 0;
    }
    
    /**
     * Check database status
     */
    private function check_database_status() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Check email status
     */
    private function check_email_status() {
        return !empty(get_option('vpa_business_email')) || !empty(get_option('vpa_smtp_host'));
    }
    
    /**
     * Display recent activity
     */
    private function display_recent_activity() {
        global $wpdb;
        
        // Get recent appointments
        $appointments_table = $wpdb->prefix . 'vpa_appointments';
        $recent_appointments = $wpdb->get_results(
            "SELECT * FROM {$appointments_table} ORDER BY created_at DESC LIMIT 5"
        );
        
        // Get recent audit logs
        $audit_table = $wpdb->prefix . 'vpa_audit_log';
        $recent_logs = $wpdb->get_results(
            "SELECT * FROM {$audit_table} ORDER BY created_at DESC LIMIT 5"
        );
        
        $activities = array();
        
        // Process appointments
        foreach ($recent_appointments as $appointment) {
            $activities[] = array(
                'type' => 'appointment',
                'icon' => 'calendar-alt',
                'title' => sprintf(__('New appointment: %s', 'vitapro-appointments-fse'), $appointment->customer_name),
                'time' => $appointment->created_at,
                'status' => $appointment->status
            );
        }
        
        // Process audit logs
        foreach ($recent_logs as $log) {
            $activities[] = array(
                'type' => 'log',
                'icon' => $this->get_log_icon($log->event_type),
                'title' => $log->description,
                'time' => $log->created_at,
                'status' => $log->severity
            );
        }
        
        // Sort by time
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        // Display activities
        if (empty($activities)) {
            echo '<div class="vpa-no-activity">' . __('No recent activity', 'vitapro-appointments-fse') . '</div>';
        } else {
            foreach (array_slice($activities, 0, 10) as $activity) {
                echo '<div class="vpa-activity-item">';
                echo '<div class="vpa-activity-icon">';
                echo '<span class="dashicons dashicons-' . esc_attr($activity['icon']) . '"></span>';
                echo '</div>';
                echo '<div class="vpa-activity-content">';
                echo '<div class="vpa-activity-title">' . esc_html($activity['title']) . '</div>';
                echo '<div class="vpa-activity-time">' . human_time_diff(strtotime($activity['time'])) . ' ' . __('ago', 'vitapro-appointments-fse') . '</div>';
                echo '</div>';
                echo '<div class="vpa-activity-status vpa-status-' . esc_attr($activity['status']) . '"></div>';
                echo '</div>';
            }
        }
    }
    
    /**
     * Get log icon
     */
    private function get_log_icon($event_type) {
        $icons = array(
            'appointment_created' => 'calendar-alt',
            'appointment_updated' => 'edit',
            'appointment_cancelled' => 'dismiss',
            'user_login' => 'admin-users',
            'settings_changed' => 'admin-settings',
            'security_alert' => 'warning',
            'backup_created' => 'backup',
            'payment_received' => 'money-alt'
        );
        
        return isset($icons[$event_type]) ? $icons[$event_type] : 'admin-generic';
    }
    
    /**
     * Quick setup
     */
    public function quick_setup() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_overview_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'vitapro-appointments-fse'));
        }
        
        // Set default options
        $defaults = array(
            'vpa_business_name' => get_bloginfo('name'),
            'vpa_business_email' => get_option('admin_email'),
            'vpa_timezone' => get_option('timezone_string') ?: 'UTC',
            'vpa_currency' => 'USD',
            'vpa_auto_approve' => 1,
            'vpa_allow_cancellation' => 1,
            'vpa_allow_rescheduling' => 1,
            'vpa_primary_color' => '#0073aa',
            'vpa_secondary_color' => '#00a32a',
            'vpa_accent_color' => '#ff8c00'
        );
        
        foreach ($defaults as $option => $value) {
            if (!get_option($option)) {
                update_option($option, $value);
            }
        }
        
        // Create sample service
        if (!get_posts(array('post_type' => 'vpa_service', 'posts_per_page' => 1))) {
            $service_id = wp_insert_post(array(
                'post_title' => __('General Consultation', 'vitapro-appointments-fse'),
                'post_content' => __('General medical consultation and health checkup.', 'vitapro-appointments-fse'),
                'post_status' => 'publish',
                'post_type' => 'vpa_service'
            ));
            
            if ($service_id) {
                update_post_meta($service_id, '_vpa_service_duration', 60);
                update_post_meta($service_id, '_vpa_service_price', 100);
            }
        }
        
        // Create sample professional
        if (!get_posts(array('post_type' => 'vpa_professional', 'posts_per_page' => 1))) {
            $professional_id = wp_insert_post(array(
                'post_title' => __('Dr. John Smith', 'vitapro-appointments-fse'),
                'post_content' => __('Experienced healthcare professional with over 10 years of practice.', 'vitapro-appointments-fse'),
                'post_status' => 'publish',
                'post_type' => 'vpa_professional'
            ));
            
            if ($professional_id) {
                update_post_meta($professional_id, '_vpa_professional_email', get_option('admin_email'));
                update_post_meta($professional_id, '_vpa_professional_phone', '');
            }
        }
        
        wp_send_json_success(__('Quick setup completed successfully', 'vitapro-appointments-fse'));
    }
}

