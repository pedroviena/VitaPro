<?php
/**
 * Overview/Presentation Page
 *
 * Displays plugin features, information, and getting started guide.
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('VitaPro_Appointments_FSE_Overview_Page')) {
class VitaPro_Appointments_FSE_Overview_Page {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() { // Tornar privado para singleton
        add_action('admin_menu', array($this, 'add_overview_page'));
        add_action('wp_ajax_vpa_quick_setup', array($this, 'quick_setup'));
        add_action('wp_ajax_vpa_dismiss_notice', array($this, 'dismiss_notice'));
    }

    /**
     * Add overview page as the main page for 'vitapro-appointments' menu.
     */
    public function add_overview_page() {
        $parent_slug = 'vitapro-appointments';

        add_submenu_page(
            $parent_slug,
            __('Overview - VitaPro Appointments', 'vitapro-appointments-fse'),
            __('Overview', 'vitapro-appointments-fse'),
            'manage_options',
            $parent_slug,
            array($this, 'display_overview_page'),
            0
        );
    }

    /**
     * Display overview page
     */
    public function display_overview_page() {
        ?>
        <div class="wrap vpa-overview-page">
            <div class="vpa-overview-header">
                <div class="vpa-header-content">
                    <div class="vpa-logo-section">
                        <div class="vpa-plugin-logo">
                            <span class="dashicons dashicons-calendar-alt"></span>
                        </div>
                        <div class="vpa-plugin-info">
                            <h1><?php _e('VitaPro Appointments FSE', 'vitapro-appointments-fse'); ?></h1>
                            <p class="vpa-version"><?php printf(__('Version %s', 'vitapro-appointments-fse'), esc_html(VITAPRO_APPOINTMENTS_FSE_VERSION)); ?></p>
                            <p class="vpa-tagline"><?php _e('Professional Healthcare Appointment Booking System', 'vitapro-appointments-fse'); ?></p>
                        </div>
                    </div>

                    <div class="vpa-quick-actions">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-settings')); ?>" class="button button-primary button-large">
                            <span class="dashicons dashicons-admin-settings"></span>
                            <?php _e('Settings', 'vitapro-appointments-fse'); ?>
                        </a>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-calendar')); ?>" class="button button-secondary button-large">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <?php _e('Calendar', 'vitapro-appointments-fse'); ?>
                        </a>
                        <button type="button" id="vpa-quick-setup-btn" class="button button-secondary button-large">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('Quick Setup', 'vitapro-appointments-fse'); ?>
                        </button>
                    </div>
                </div>
            </div>

            <div class="vpa-stats-overview">
                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo esc_html($this->get_total_appointments()); ?></div>
                        <div class="vpa-stat-label"><?php _e('Total Appointments', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>

                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-groups"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo esc_html($this->get_total_customers()); ?></div>
                        <div class="vpa-stat-label"><?php _e('Total Customers', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>

                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-businessman"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo esc_html($this->get_total_professionals()); ?></div>
                        <div class="vpa-stat-label"><?php _e('Professionals', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>

                <div class="vpa-stat-card">
                    <div class="vpa-stat-icon">
                        <span class="dashicons dashicons-products"></span>
                    </div>
                    <div class="vpa-stat-content">
                        <div class="vpa-stat-number"><?php echo esc_html($this->get_total_services()); ?></div>
                        <div class="vpa-stat-label"><?php _e('Services', 'vitapro-appointments-fse'); ?></div>
                    </div>
                </div>
            </div>

            <div class="vpa-overview-grid">
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
                                <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-settings')); ?>" class="button button-secondary">
                                    <?php _e('Go to Settings', 'vitapro-appointments-fse'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="vpa-step">
                            <div class="vpa-step-number">2</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Add Services & Professionals', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Create your service offerings and add professional staff members.', 'vitapro-appointments-fse'); ?></p>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=vpa_service')); ?>" class="button button-secondary">
                                    <?php _e('Manage Services', 'vitapro-appointments-fse'); ?>
                                </a>
                                <a href="<?php echo esc_url(admin_url('edit.php?post_type=vpa_professional')); ?>" class="button button-secondary" style="margin-left: 10px;">
                                    <?php _e('Manage Professionals', 'vitapro-appointments-fse'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="vpa-step">
                            <div class="vpa-step-number">3</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Set Working Hours & Holidays', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Configure availability schedules for your professionals and general holidays.', 'vitapro-appointments-fse'); ?></p>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-working-hours')); ?>" class="button button-secondary">
                                    <?php _e('Set Working Hours', 'vitapro-appointments-fse'); ?>
                                </a>
                                 <a href="<?php echo esc_url(admin_url('edit.php?post_type=vpa_holiday')); ?>" class="button button-secondary" style="margin-left: 10px;">
                                    <?php _e('Manage Holidays', 'vitapro-appointments-fse'); ?>
                                </a>
                            </div>
                        </div>

                        <div class="vpa-step">
                            <div class="vpa-step-number">4</div>
                            <div class="vpa-step-content">
                                <h3><?php _e('Add Booking Forms', 'vitapro-appointments-fse'); ?></h3>
                                <p><?php _e('Insert booking forms into your pages using Gutenberg blocks or Elementor widgets.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-secondary" onclick="vpaShowShortcodesModal()">
                                    <?php _e('View Shortcodes', 'vitapro-appointments-fse'); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vpa-overview-section vpa-system-status">
                    <h2>
                        <span class="dashicons dashicons-dashboard"></span> <?php _e('System Status', 'vitapro-appointments-fse'); ?>
                    </h2>

                    <div class="vpa-status-grid">
                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('WordPress Version', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo esc_html(get_bloginfo('version')); ?>
                                <span class="vpa-status-indicator <?php echo version_compare(get_bloginfo('version'), '6.0', '>=') ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>

                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('PHP Version', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo esc_html(PHP_VERSION); ?>
                                <span class="vpa-status-indicator <?php echo version_compare(PHP_VERSION, '7.4', '>=') ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>

                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Database Tables', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo $this->check_database_status() ? __('OK', 'vitapro-appointments-fse') : __('Error', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo $this->check_database_status() ? 'good' : 'error'; ?>"></span>
                            </div>
                        </div>

                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Email System', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo $this->check_email_status() ? __('Configured', 'vitapro-appointments-fse') : __('Not Fully Configured', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo $this->check_email_status() ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>

                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Elementor', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php
                                if (!function_exists('is_plugin_active')) {
                                    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
                                }
                                echo is_plugin_active('elementor/elementor.php') ? __('Active', 'vitapro-appointments-fse') : __('Not Active', 'vitapro-appointments-fse');
                                ?>
                                <span class="vpa-status-indicator <?php echo is_plugin_active('elementor/elementor.php') ? 'good' : 'neutral'; ?>"></span>
                            </div>
                        </div>

                        <div class="vpa-status-item">
                            <div class="vpa-status-label"><?php _e('Cron Jobs', 'vitapro-appointments-fse'); ?></div>
                            <div class="vpa-status-value">
                                <?php echo wp_next_scheduled(VITAPRO_REMINDER_CRON_HOOK) ? __('Scheduled', 'vitapro-appointments-fse') : __('Not Scheduled', 'vitapro-appointments-fse'); ?>
                                <span class="vpa-status-indicator <?php echo wp_next_scheduled(VITAPRO_REMINDER_CRON_HOOK) ? 'good' : 'warning'; ?>"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="vpa-overview-section vpa-recent-activity">
                    <h2>
                        <span class="dashicons dashicons-clock"></span>
                        <?php _e('Recent Activity', 'vitapro-appointments-fse'); ?>
                    </h2>

                    <div class="vpa-activity-list">
                        <?php $this->display_recent_activity(); ?>
                    </div>

                    <div class="vpa-activity-footer">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-audit')); ?>" class="button button-secondary">
                            <?php _e('View Audit Log', 'vitapro-appointments-fse'); ?>
                        </a>
                    </div>
                </div>

                <div class="vpa-overview-section vpa-quick-links">
                    <h2>
                        <span class="dashicons dashicons-admin-links"></span>
                        <?php _e('Quick Links', 'vitapro-appointments-fse'); ?>
                    </h2>

                    <div class="vpa-links-grid">
                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-calendar')); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-calendar-alt"></span>
                            <span><?php _e('Calendar View', 'vitapro-appointments-fse'); ?></span>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-analytics')); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <span><?php _e('Analytics', 'vitapro-appointments-fse'); ?></span>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-reports')); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-media-spreadsheet"></span>
                            <span><?php _e('Reports', 'vitapro-appointments-fse'); ?></span>
                        </a>

                        <a href="<?php echo esc_url(admin_url('admin.php?page=vitapro-appointments-backup')); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-backup"></span>
                            <span><?php _e('Backup/Restore', 'vitapro-appointments-fse'); ?></span>
                        </a>

                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=vpa_service')); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-products"></span>
                            <span><?php _e('Manage Services', 'vitapro-appointments-fse'); ?></span>
                        </a>

                        <a href="<?php echo esc_url(admin_url('edit.php?post_type=vpa_professional')); ?>" class="vpa-quick-link">
                            <span class="dashicons dashicons-businessman"></span>
                            <span><?php _e('Manage Professionals', 'vitapro-appointments-fse'); ?></span>
                        </a>
                    </div>
                </div>

                <div class="vpa-overview-section vpa-support">
                    <h2>
                        <span class="dashicons dashicons-sos"></span>
                        <?php _e('Support & Documentation', 'vitapro-appointments-fse'); ?>
                    </h2>

                    <div class="vpa-support-grid">
                        <div class="vpa-support-item">
                            <h3><?php _e('Documentation', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Comprehensive guides and tutorials to help you get the most out of VitaPro Appointments.', 'vitapro-appointments-fse'); ?></p>
                            <a href="https://vitapro.com/appointments-fse/docs" class="button button-secondary" target="_blank" rel="noopener noreferrer">
                                <?php _e('View Documentation', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>

                        <div class="vpa-support-item">
                            <h3><?php _e('Video Tutorials', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Step-by-step video guides covering all major features and setup procedures.', 'vitapro-appointments-fse'); ?></p>
                            <a href="https://vitapro.com/appointments-fse/videos" class="button button-secondary" target="_blank" rel="noopener noreferrer">
                                <?php _e('Watch Tutorials', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>

                        <div class="vpa-support-item">
                            <h3><?php _e('Community Forum', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Connect with other users, share tips, and get help from the community.', 'vitapro-appointments-fse'); ?></p>
                            <a href="https://vitapro.com/appointments-fse/forum" class="button button-secondary" target="_blank" rel="noopener noreferrer">
                                <?php _e('Join Forum', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>

                        <div class="vpa-support-item">
                            <h3><?php _e('Premium Support', 'vitapro-appointments-fse'); ?></h3>
                            <p><?php _e('Get priority support with direct access to our development team.', 'vitapro-appointments-fse'); ?></p>
                            <a href="https://vitapro.com/appointments-fse/support" class="button button-primary" target="_blank" rel="noopener noreferrer">
                                <?php _e('Get Support', 'vitapro-appointments-fse'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div> <div id="vpa-shortcodes-modal" class="vpa-modal" style="display:none;">
                <div class="vpa-modal-content">
                    <div class="vpa-modal-header">
                        <h3><?php _e('Available Shortcodes', 'vitapro-appointments-fse'); ?></h3>
                        <button type="button" class="vpa-modal-close-btn">&times;</button>
                    </div>
                    <div class="vpa-modal-body">
                        <div class="vpa-shortcode-list">
                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Booking Form', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_booking_form]</code>
                                <p><?php _e('Displays the main appointment booking form. Accepts attributes like service_id, professional_id, show_service_step, show_professional_step.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_booking_form]"><?php _e('Copy', 'vitapro-appointments-fse'); ?></button>
                            </div>

                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Service List', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_service_list layout="grid" columns="3"]</code>
                                <p><?php _e('Displays a list of services. Attributes: layout, columns, show_image, show_description, show_price, show_duration, category_id, limit.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode='[vpa_service_list layout="grid" columns="3"]'><?php _e('Copy', 'vitapro-appointments-fse'); ?></button>
                            </div>

                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Professional List', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_professional_list layout="grid" columns="3"]</code>
                                <p><?php _e('Displays a list of professionals. Attributes: layout, columns, show_image, show_bio, show_services, service_id, limit.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode='[vpa_professional_list layout="grid" columns="3"]'><?php _e('Copy', 'vitapro-appointments-fse'); ?></button>
                            </div>

                            <div class="vpa-shortcode-item">
                                <h4><?php _e('Availability Calendar', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_availability_calendar months_to_show="1"]</code>
                                <p><?php _e('Displays an interactive availability calendar. Attributes: service_id, professional_id, months_to_show, show_legend.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode='[vpa_availability_calendar months_to_show="1"]'><?php _e('Copy', 'vitapro-appointments-fse'); ?></button>
                            </div>

                            <div class="vpa-shortcode-item">
                                <h4><?php _e('My Appointments (Customer Dashboard)', 'vitapro-appointments-fse'); ?></h4>
                                <code>[vpa_my_appointments]</code>
                                <p><?php _e('Displays the current logged-in user\'s appointments. Attributes: show_upcoming, show_past, allow_cancellation, upcoming_limit, past_limit.', 'vitapro-appointments-fse'); ?></p>
                                <button type="button" class="button button-small vpa-copy-shortcode" data-shortcode="[vpa_my_appointments]"><?php _e('Copy', 'vitapro-appointments-fse'); ?></button>
                            </div>
                        </div>
                    </div>
                     <div class="vpa-modal-footer">
                        <button type="button" class="button vpa-modal-close-btn"><?php _e('Close', 'vitapro-appointments-fse'); ?></button>
                    </div>
                </div>
            </div>

        </div> <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Quick setup
            $('#vpa-quick-setup-btn').on('click', function() {
                var $button = $(this);
                var originalText = $button.html(); // Salva o HTML interno, incluindo o ícone

                $button.prop('disabled', true).html('<span class="dashicons dashicons-update-alt spin"></span> <?php _e('Setting up...', 'vitapro-appointments-fse'); ?>');

                $.post(ajaxurl, {
                    action: 'vpa_quick_setup',
                    nonce: '<?php echo esc_js(wp_create_nonce('vpa_overview_nonce')); ?>' // Use esc_js para nonces em JS
                })
                .done(function(response) {
                    if (response.success) {
                        alert(response.data.message || '<?php echo esc_js(__('Quick setup completed successfully!', 'vitapro-appointments-fse')); ?>');
                        location.reload();
                    } else {
                        alert(response.data.message || '<?php echo esc_js(__('Setup failed. Please check logs or try again.', 'vitapro-appointments-fse')); ?>');
                    }
                })
                .fail(function() {
                    alert('<?php echo esc_js(__('An error occurred during quick setup. Please try again.', 'vitapro-appointments-fse')); ?>');
                })
                .always(function() {
                    $button.prop('disabled', false).html(originalText);
                });
            });

            // Copy shortcode
            $('.vpa-copy-shortcode').on('click', function() {
                var shortcode = $(this).data('shortcode');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(shortcode).then(function() {
                        alert('<?php echo esc_js(__('Shortcode copied to clipboard!', 'vitapro-appointments-fse')); ?>');
                    }, function(err) {
                        alert('<?php echo esc_js(__('Could not copy shortcode: ', 'vitapro-appointments-fse')); ?>' + err);
                    });
                } else {
                    // Fallback para navegadores mais antigos
                    var $temp = $("<input>");
                    $("body").append($temp);
                    $temp.val(shortcode).select();
                    document.execCommand("copy");
                    $temp.remove();
                    alert('<?php echo esc_js(__('Shortcode copied to clipboard! (fallback)', 'vitapro-appointments-fse')); ?>');
                }
            });

            // Modal controls
            $('.vpa-modal-close-btn').on('click', function() {
                $('#vpa-shortcodes-modal').hide().removeClass('vpa-modal-show');
            });
             // Fechar modal ao clicar fora
            $('#vpa-shortcodes-modal').on('click', function(e) {
                if ($(e.target).is('#vpa-shortcodes-modal')) {
                    $(this).hide().removeClass('vpa-modal-show');
                }
            });

        });
        // Função global para mostrar modal, pois o botão está em PHP
        window.vpaShowShortcodesModal = function() {
            jQuery('#vpa-shortcodes-modal').show().addClass('vpa-modal-show');
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
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) return 0;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$table_name} WHERE status != 'cancelled'") ?: 0;
    }

    /**
     * Get total customers
     */
    private function get_total_customers() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") != $table_name) return 0;
        return $wpdb->get_var("SELECT COUNT(DISTINCT customer_email) FROM {$table_name} WHERE status != 'cancelled'") ?: 0;
    }

    /**
     * Get total professionals
     */
    private function get_total_professionals() {
        $count = wp_count_posts('vpa_professional');
        return $count->publish ?: 0;
    }

    /**
     * Get total services
     */
    private function get_total_services() {
        $count = wp_count_posts('vpa_service');
        return $count->publish ?: 0;
    }

    /**
     * Check database status
     */
    private function check_database_status() {
        global $wpdb;
        $tables_to_check = array(
            $wpdb->prefix . 'vpa_appointments',
            $wpdb->prefix . 'vpa_audit_log',
            $wpdb->prefix . 'vpa_backups',
            $wpdb->prefix . 'vpa_notifications',
            $wpdb->prefix . 'vpa_reports',
            $wpdb->prefix . 'vpa_security_blocks',
            $wpdb->prefix . 'vpa_security_log',
            $wpdb->prefix . 'vpa_system_health',
        );
        foreach($tables_to_check as $table_name){
             if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) != $table_name) {
                return false;
             }
        }
        return true;
    }

    /**
     * Check email status
     */
    private function check_email_status() {
        $options = get_option('vitapro_appointments_settings', array());
        $from_email = isset($options['email_from_address']) ? $options['email_from_address'] : '';
        $admin_email = isset($options['email_admin_new_booking']) ? $options['email_admin_new_booking'] : '';
        return !empty($from_email) && is_email($from_email) && !empty($admin_email) && is_email($admin_email);
    }

    /**
     * Display recent activity
     */
    private function display_recent_activity() {
        global $wpdb;
        $activities = array();

        // Get recent appointments
        $appointments_table = $wpdb->prefix . 'vpa_appointments';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$appointments_table}'") == $appointments_table) {
            $recent_appointments = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, customer_name, status, created_at, service_id FROM {$appointments_table} ORDER BY created_at DESC LIMIT %d",
                    5
                )
            );
            foreach ($recent_appointments as $appointment) {
                $service = get_post($appointment->service_id);
                $service_name = $service ? $service->post_title : __('Unknown Service', 'vitapro-appointments-fse');
                $activities[] = array(
                    'id' => 'app-' . $appointment->id,
                    'type' => 'appointment',
                    'icon' => 'calendar-alt',
                    'title' => sprintf(__('New booking: %s (%s)', 'vitapro-appointments-fse'), esc_html($appointment->customer_name), esc_html($service_name)),
                    'time' => $appointment->created_at,
                    'status' => $appointment->status,
                    'link' => admin_url('post.php?post=' . $appointment->id . '&action=edit') // Assumindo que o ID da tabela é o ID do post
                );
            }
        }

        // Get recent audit logs (apenas alguns tipos para a visão geral)
        $audit_table = $wpdb->prefix . 'vpa_audit_log';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$audit_table}'") == $audit_table) {
            $recent_logs = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, event_type, description, created_at, severity, user_id, object_type, object_id
                     FROM {$audit_table}
                     WHERE event_category IN ('security', 'authentication', 'configuration')
                     ORDER BY created_at DESC LIMIT %d",
                    5
                )
            );
            foreach ($recent_logs as $log) {
                $user_info = $log->user_id ? get_userdata($log->user_id) : null;
                $user_display = $user_info ? $user_info->display_name : __('System', 'vitapro-appointments-fse');
                $activities[] = array(
                    'id' => 'log-' . $log->id,
                    'type' => 'log',
                    'icon' => $this->get_log_icon($log->event_type, $log->severity),
                    'title' => sprintf('%s: %s', esc_html($user_display), esc_html(wp_trim_words($log->description, 10))),
                    'time' => $log->created_at,
                    'status' => $log->severity, // Use severity for log status color
                    'link' => admin_url('admin.php?page=vitapro-appointments-audit&log_id=' . $log->id) // Link para o detalhe do log
                );
            }
        }

        if (empty($activities)) {
            echo '<div class="vpa-no-activity">' . esc_html__('No recent activity to display.', 'vitapro-appointments-fse') . '</div>';
            return;
        }

        // Sort by time (mais recentes primeiro)
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        // Display top 10 mixed activities
        $count = 0;
        foreach (array_slice($activities, 0, 7) as $activity) {
            echo '<div class="vpa-activity-item">';
            echo '<div class="vpa-activity-icon"><span class="dashicons dashicons-' . esc_attr($activity['icon']) . '"></span></div>';
            echo '<div class="vpa-activity-content">';
            echo '<div class="vpa-activity-title">';
            if (!empty($activity['link'])) {
                echo '<a href="' . esc_url($activity['link']) . '">' . esc_html($activity['title']) . '</a>';
            } else {
                echo esc_html($activity['title']);
            }
            echo '</div>';
            echo '<div class="vpa-activity-time">' . esc_html(human_time_diff(strtotime($activity['time']), current_time('timestamp'))) . ' ' . __('ago', 'vitapro-appointments-fse') . '</div>';
            echo '</div>';
            echo '<div class="vpa-activity-status vpa-status-' . esc_attr($activity['status']) . '"></div>';
            echo '</div>';
            $count++;
            if ($count >= 7) break;
        }
        if ($count === 0) { // Se após a filtragem não houver nada
             echo '<div class="vpa-no-activity">' . esc_html__('No recent relevant activity to display.', 'vitapro-appointments-fse') . '</div>';
        }
    }

    /**
     * Get log icon based on event type and severity.
     */
    private function get_log_icon($event_type, $severity = 'info') {
        $icons = array(
            'appointment_created' => 'calendar-alt',
            'appointment_updated' => 'edit',
            'appointment_deleted' => 'trash',
            'appointment_status_changed' => 'update-alt',
            'user_login' => 'admin-users',
            'user_login_failed' => 'lock',
            'user_logout' => 'exit',
            'settings_changed' => 'admin-settings',
            'security_alert' => 'shield-alt',
            'brute_force_detected' => 'shield',
            'backup_created' => 'backup',
            'backup_restored' => 'image-rotate',
            'backup_failed' => 'warning',
            'payment_received' => 'money-alt'
            // Adicionar mais mapeamentos conforme necessário
        );

        if (isset($icons[$event_type])) {
            return $icons[$event_type];
        }
        // Fallback por severidade
        switch($severity) {
            case 'emergency':
            case 'alert':
            case 'critical':
            case 'error':
                return 'error';
            case 'warning':
                return 'warning';
            case 'notice':
            case 'info':
                return 'info-outline';
            case 'debug':
                return 'lightbulb';
            default:
                return 'admin-generic';
        }
    }

    /**
     * Quick setup AJAX handler
     */
    public function quick_setup() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'vitapro-appointments-fse')), 403);
        }
        check_ajax_referer('vpa_overview_nonce', 'nonce');

        // Simula um processo de configuração
        update_option('vitapro_quick_setup_completed', time());

        // Criação de serviço e profissional de exemplo (se não existirem)
        if (!get_page_by_path('general-consultation', OBJECT, 'vpa_service')) {
            $service_id = wp_insert_post(array(
                'post_title'    => __('General Consultation', 'vitapro-appointments-fse'),
                'post_content'  => __('Standard general consultation service.', 'vitapro-appointments-fse'),
                'post_status'   => 'publish',
                'post_type'     => 'vpa_service',
                'post_name'     => 'general-consultation'
            ));
            if ($service_id && !is_wp_error($service_id)) {
                update_post_meta($service_id, '_vpa_service_duration', 30);
                update_post_meta($service_id, '_vpa_service_price', 75);
            }
        }

        if (!get_page_by_path('dr-vita', OBJECT, 'vpa_professional')) {
            $professional_id = wp_insert_post(array(
                'post_title'    => __('Dr. Vita', 'vitapro-appointments-fse'),
                'post_content'  => __('Default professional for VitaPro Appointments.', 'vitapro-appointments-fse'),
                'post_status'   => 'publish',
                'post_type'     => 'vpa_professional',
                'post_name'     => 'dr-vita'
            ));
            if ($professional_id && !is_wp_error($professional_id)) {
                // Definir um horário padrão para o Dr. Vita
                $default_schedule = array();
                $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday');
                foreach ($days as $day) {
                    $default_schedule[$day] = array(
                        'working'     => true,
                        'start'       => '09:00',
                        'end'         => '17:00',
                        'break_start' => '12:00',
                        'break_end'   => '13:00',
                    );
                }
                $default_schedule['saturday'] = array('working' => false, 'start' => '', 'end' => '', 'break_start' => '', 'break_end' => '');
                $default_schedule['sunday'] = array('working' => false, 'start' => '', 'end' => '', 'break_start' => '', 'break_end' => '');
                update_post_meta($professional_id, '_vpa_professional_schedule', $default_schedule);
                // Corrigir: $service_id pode não estar definido se o serviço já existia
                if (!isset($service_id) || !$service_id) {
                    $existing_service = get_page_by_path('general-consultation', OBJECT, 'vpa_service');
                    $service_id_to_link = $existing_service ? $existing_service->ID : 0;
                } else {
                    $service_id_to_link = $service_id;
                }
                if ($service_id_to_link) {
                    update_post_meta($professional_id, '_vpa_professional_services', array($service_id_to_link));
                }
            }
        }
        $main_options = get_option('vitapro_appointments_settings');
        if (empty($main_options['business_name'])) {
            $main_options['business_name'] = get_bloginfo('name');
            $main_options['business_email'] = get_option('admin_email');
            // Outras opções padrão que podem ser redefinidas no Quick Setup
            update_option('vitapro_appointments_settings', $main_options);
        }

        wp_send_json_success(array('message' => __('Quick setup has been initiated. Default settings and sample data (if applicable) have been applied.', 'vitapro-appointments-fse')));
    }

    /**
     * Dismiss notice AJAX handler
     */
    public function dismiss_notice() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('Insufficient permissions.', 'vitapro-appointments-fse')), 403);
        }
        $notice_id = isset($_POST['notice_id']) ? sanitize_key($_POST['notice_id']) : '';
        if ($notice_id) {
            update_user_meta(get_current_user_id(), 'dismissed_vpa_notice_' . $notice_id, true);
            wp_send_json_success();
        }
        wp_send_json_error(array('message' => __('Invalid notice ID.', 'vitapro-appointments-fse')));
    }
} // Fim da classe VitaPro_Appointments_FSE_Overview_Page
} // Fim do if (!class_exists(...))