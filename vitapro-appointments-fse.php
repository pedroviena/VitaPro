<?php
/**
 * Plugin Name: VitaPro Appointments FSE
 * Plugin URI: https://vitapro.com/appointments-fse
 * Description: A comprehensive appointment booking system for healthcare professionals with Full Site Editing and Elementor support.
 * Version: 1.0.0
 * Author: VitaPro Team
 * Author URI: https://vitapro.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: vitapro-appointments-fse
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * Network: false
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('VITAPRO_APPOINTMENTS_FSE_VERSION', '1.0.0');
define('VITAPRO_APPOINTMENTS_FSE_PLUGIN_FILE', __FILE__);
define('VITAPRO_APPOINTMENTS_FSE_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('VITAPRO_APPOINTMENTS_FSE_PATH', plugin_dir_path(__FILE__));
define('VITAPRO_APPOINTMENTS_FSE_URL', plugin_dir_url(__FILE__));
define('VITAPRO_REMINDER_CRON_HOOK', 'vitapro_process_appointment_reminders_cron');

/**
 * Main plugin class
 */
class VitaPro_Appointments_FSE {

    /**
     * Plugin instance
     */
    private static $instance = null;

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        add_action('init', array($this, 'init'));
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        // Activation and deactivation hooks
        register_activation_hook(VITAPRO_APPOINTMENTS_FSE_PLUGIN_FILE, array($this, 'activate')); // Usar constante aqui
        register_deactivation_hook(VITAPRO_APPOINTMENTS_FSE_PLUGIN_FILE, array($this, 'deactivate')); // Usar constante aqui

        // Admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
    }

    /**
     * Initialize the plugin
     */
    public function init() {
        // Load dependencies safely
        $this->load_dependencies();

        // Register post types
        $this->register_post_types();

        // Register taxonomies
        $this->register_taxonomies();
    }

    /**
     * Load plugin dependencies
     */
// Em vitapro-appointments-fse.php, dentro da classe VitaPro_Appointments_FSE
private function load_dependencies() {
    // Define required files
    $required_files = array(
        'includes/class-custom-post-types.php',
        'includes/class-admin-settings.php', // Para adicionar menus e registrar settings
        'includes/cpt/settings-page.php',     // Para a UI da página de configurações (VitaPro_Appointments_FSE_Settings_Page)
        'includes/class-blocks.php',
        'includes/class-ajax-handlers.php',    // Mantém a classe
        'includes/class-email-functions.php', 
        'includes/class-availability-logic.php',
        'includes/common/helpers.php',          // Arquivo de helpers
        'includes/class-cron-jobs.php',
        'includes/class-frontend-actions.php',
        'includes/class-security.php',
        'includes/class-audit-log.php',
        'includes/class-backup-recovery.php',
        'includes/class-notifications.php',
        'includes/class-reports.php',
        'includes/class-dashboard.php'        // Para a página de Dashboard/Analytics
    );
    // REMOVIDO: 'includes/ajax-handlers.php', (o arquivo procedural)

    foreach ($required_files as $file) {
        $file_path = VITAPRO_APPOINTMENTS_FSE_PATH . $file;
        if (file_exists($file_path)) {
            require_once $file_path;
        }
    }

    if (did_action('elementor/loaded')) {
        $elementor_file = VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/class-elementor-integration.php';
        if (file_exists($elementor_file)) {
            require_once $elementor_file;
        }
    }

    // Instanciar classes controladoras principais
    if (class_exists('VitaPro_Appointments_FSE_Admin_Settings')) { // Adiciona menus
        new VitaPro_Appointments_FSE_Admin_Settings();
    }
    if (class_exists('VitaPro_Appointments_FSE_Settings_Page')) { // Lida com a UI da página de configurações
        new VitaPro_Appointments_FSE_Settings_Page();
    }
    if (class_exists('VitaPro_Appointments_FSE_Frontend_Actions')) {
        new VitaPro_Appointments_FSE_Frontend_Actions();
    }
    if (class_exists('VitaPro_Appointments_FSE_Ajax_Handlers')) {
        new VitaPro_Appointments_FSE_Ajax_Handlers();
    }
    if (class_exists('VitaPro_Appointments_FSE_Cron_Jobs')) {
        new VitaPro_Appointments_FSE_Cron_Jobs();
    }
    if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) { // Se ela tiver hooks no construtor
        new VitaPro_Appointments_FSE_Email_Functions();
    }
    if (class_exists('VitaPro_Appointments_FSE_Blocks')) {
        new VitaPro_Appointments_FSE_Blocks();
    }
    if (class_exists('VitaPro_Appointments_FSE_Dashboard')) {
        new VitaPro_Appointments_FSE_Dashboard();
    }
    // Outras classes como Security, AuditLog, etc., são instanciadas se necessário ou se
    // elas registram seus próprios hooks nos seus construtores.
    // Por exemplo, se a classe Security precisa rodar no init:
    if (class_exists('VitaPro_Appointments_FSE_Security')) {
        new VitaPro_Appointments_FSE_Security(); // Se o construtor dela tiver add_action('init', ...)
    }
     if (class_exists('VitaPro_Appointments_FSE_Audit_Log')) {
        new VitaPro_Appointments_FSE_Audit_Log();
    }
    if (class_exists('VitaPro_Appointments_FSE_Backup_Recovery')) {
        new VitaPro_Appointments_FSE_Backup_Recovery();
    }
    if (class_exists('VitaPro_Appointments_FSE_Notifications')) {
        new VitaPro_Appointments_FSE_Notifications();
    }
    if (class_exists('VitaPro_Appointments_FSE_Reports')) {
        new VitaPro_Appointments_FSE_Reports();
    }
     if (class_exists('VitaPro_Appointments_FSE_Availability_Logic')) {
        new VitaPro_Appointments_FSE_Availability_Logic(); // Se tiver hooks AJAX próprios
    }


}

    /**
     * Register custom post types
     */
    public function register_post_types() {
        // Service post type
        register_post_type('vpa_service', array(
            'labels' => array(
                'name' => __('Services', 'vitapro-appointments-fse'),
                'singular_name' => __('Service', 'vitapro-appointments-fse'),
                'add_new' => __('Add New', 'vitapro-appointments-fse'),
                'add_new_item' => __('Add New Service', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Service', 'vitapro-appointments-fse'),
                'new_item' => __('New Service', 'vitapro-appointments-fse'),
                'view_item' => __('View Service', 'vitapro-appointments-fse'),
                'search_items' => __('Search Services', 'vitapro-appointments-fse'),
                'not_found' => __('No services found', 'vitapro-appointments-fse'),
                'not_found_in_trash' => __('No services found in trash', 'vitapro-appointments-fse'),
                'menu_name' => __('Services', 'vitapro-appointments-fse'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'vitapro-appointments',
            'query_var' => true,
            'rewrite' => array('slug' => 'service'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt'),
            'show_in_rest' => true,
        ));

        // Professional post type
        register_post_type('vpa_professional', array(
            'labels' => array(
                'name' => __('Professionals', 'vitapro-appointments-fse'),
                'singular_name' => __('Professional', 'vitapro-appointments-fse'),
                'add_new' => __('Add New', 'vitapro-appointments-fse'),
                'add_new_item' => __('Add New Professional', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Professional', 'vitapro-appointments-fse'),
                'new_item' => __('New Professional', 'vitapro-appointments-fse'),
                'view_item' => __('View Professional', 'vitapro-appointments-fse'),
                'search_items' => __('Search Professionals', 'vitapro-appointments-fse'),
                'not_found' => __('No professionals found', 'vitapro-appointments-fse'),
                'not_found_in_trash' => __('No professionals found in trash', 'vitapro-appointments-fse'),
                'menu_name' => __('Professionals', 'vitapro-appointments-fse'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => 'vitapro-appointments',
            'query_var' => true,
            'rewrite' => array('slug' => 'professional'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'editor', 'thumbnail'),
            'show_in_rest' => true,
        ));

        // Appointment post type
        register_post_type('vpa_appointment', array(
            'labels' => array(
                'name' => __('Appointments', 'vitapro-appointments-fse'),
                'singular_name' => __('Appointment', 'vitapro-appointments-fse'),
                'add_new' => __('Add New', 'vitapro-appointments-fse'),
                'add_new_item' => __('Add New Appointment', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Appointment', 'vitapro-appointments-fse'),
                'new_item' => __('New Appointment', 'vitapro-appointments-fse'),
                'view_item' => __('View Appointment', 'vitapro-appointments-fse'),
                'search_items' => __('Search Appointments', 'vitapro-appointments-fse'),
                'not_found' => __('No appointments found', 'vitapro-appointments-fse'),
                'not_found_in_trash' => __('No appointments found in trash', 'vitapro-appointments-fse'),
                'menu_name' => __('Appointments', 'vitapro-appointments-fse'),
            ),
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => 'vitapro-appointments',
            'query_var' => true,
            'rewrite' => array('slug' => 'appointment'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title', 'custom-fields'),
            'show_in_rest' => true,
        ));

        // Holiday post type
        register_post_type('vpa_holiday', array(
            'labels' => array(
                'name' => __('Holidays', 'vitapro-appointments-fse'),
                'singular_name' => __('Holiday', 'vitapro-appointments-fse'),
                'add_new' => __('Add New', 'vitapro-appointments-fse'),
                'add_new_item' => __('Add New Holiday', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Holiday', 'vitapro-appointments-fse'),
                'new_item' => __('New Holiday', 'vitapro-appointments-fse'),
                'view_item' => __('View Holiday', 'vitapro-appointments-fse'),
                'search_items' => __('Search Holidays', 'vitapro-appointments-fse'),
                'not_found' => __('No holidays found', 'vitapro-appointments-fse'),
                'not_found_in_trash' => __('No holidays found in trash', 'vitapro-appointments-fse'),
                'menu_name' => __('Holidays', 'vitapro-appointments-fse'),
            ),
            'public' => true,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => 'vitapro-appointments',
            'query_var' => true,
            'rewrite' => array('slug' => 'holiday'),
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => null,
            'supports' => array('title'),
            'show_in_rest' => true,
        ));
    }

    /**
     * Register taxonomies
     */
    public function register_taxonomies() {
        // Service Category taxonomy
        register_taxonomy('vpa_service_category', 'vpa_service', array(
            'labels' => array(
                'name' => __('Service Categories', 'vitapro-appointments-fse'),
                'singular_name' => __('Service Category', 'vitapro-appointments-fse'),
                'search_items' => __('Search Service Categories', 'vitapro-appointments-fse'),
                'all_items' => __('All Service Categories', 'vitapro-appointments-fse'),
                'parent_item' => __('Parent Service Category', 'vitapro-appointments-fse'),
                'parent_item_colon' => __('Parent Service Category:', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Service Category', 'vitapro-appointments-fse'),
                'update_item' => __('Update Service Category', 'vitapro-appointments-fse'),
                'add_new_item' => __('Add New Service Category', 'vitapro-appointments-fse'),
                'new_item_name' => __('New Service Category Name', 'vitapro-appointments-fse'),
                'menu_name' => __('Categories', 'vitapro-appointments-fse'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'service-category'),
            'show_in_rest' => true,
        ));

        // Professional Specialty taxonomy
        register_taxonomy('vpa_professional_specialty', 'vpa_professional', array(
            'labels' => array(
                'name' => __('Specialties', 'vitapro-appointments-fse'),
                'singular_name' => __('Specialty', 'vitapro-appointments-fse'),
                'search_items' => __('Search Specialties', 'vitapro-appointments-fse'),
                'all_items' => __('All Specialties', 'vitapro-appointments-fse'),
                'parent_item' => __('Parent Specialty', 'vitapro-appointments-fse'),
                'parent_item_colon' => __('Parent Specialty:', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Specialty', 'vitapro-appointments-fse'),
                'update_item' => __('Update Specialty', 'vitapro-appointments-fse'),
                'add_new_item' => __('Add New Specialty', 'vitapro-appointments-fse'),
                'new_item_name' => __('New Specialty Name', 'vitapro-appointments-fse'),
                'menu_name' => __('Specialties', 'vitapro-appointments-fse'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'specialty'),
            'show_in_rest' => true,
        ));
    }

    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('VitaPro Appointments', 'vitapro-appointments-fse'),
            __('VitaPro Appointments', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments',
            array($this, 'display_dashboard_page'),
            'dashicons-calendar-alt',
            30
        );

        // Dashboard submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Dashboard', 'vitapro-appointments-fse'),
            __('Dashboard', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments',
            array($this, 'display_dashboard_page')
        );

        // Settings submenu
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
     * Display dashboard page
     */
    public function display_dashboard_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('VitaPro Appointments Dashboard', 'vitapro-appointments-fse'); ?></h1>
            <div class="card">
                <h2><?php _e('Welcome to VitaPro Appointments FSE', 'vitapro-appointments-fse'); ?></h2>
                <p><?php _e('Your appointment booking system is now active. Here you can manage your services, professionals, and appointments.', 'vitapro-appointments-fse'); ?></p>

                <h3><?php _e('Quick Start', 'vitapro-appointments-fse'); ?></h3>
                <ol>
                    <li><?php _e('Add your services under Services menu', 'vitapro-appointments-fse'); ?></li>
                    <li><?php _e('Add your healthcare professionals under Professionals menu', 'vitapro-appointments-fse'); ?></li>
                    <li><?php _e('Configure your settings', 'vitapro-appointments-fse'); ?></li>
                    <li><?php _e('Add booking forms to your pages using Gutenberg blocks or Elementor widgets', 'vitapro-appointments-fse'); ?></li>
                </ol>

                <h3><?php _e('Available Blocks/Widgets', 'vitapro-appointments-fse'); ?></h3>
                <ul>
                    <li><strong><?php _e('Booking Form', 'vitapro-appointments-fse'); ?>:</strong> <?php _e('Interactive appointment booking form', 'vitapro-appointments-fse'); ?></li>
                    <li><strong><?php _e('Service List', 'vitapro-appointments-fse'); ?>:</strong> <?php _e('Display available services', 'vitapro-appointments-fse'); ?></li>
                    <li><strong><?php _e('Professional List', 'vitapro-appointments-fse'); ?>:</strong> <?php _e('Showcase healthcare professionals', 'vitapro-appointments-fse'); ?></li>
                    <li><strong><?php _e('Availability Calendar', 'vitapro-appointments-fse'); ?>:</strong> <?php _e('Visual calendar showing available dates', 'vitapro-appointments-fse'); ?></li>
                    <li><strong><?php _e('My Appointments', 'vitapro-appointments-fse'); ?>:</strong> <?php _e('User dashboard for managing appointments', 'vitapro-appointments-fse'); ?></li>
                </ul>

                <?php if (did_action('elementor/loaded')): ?>
                <div class="notice notice-success inline">
                    <p><strong><?php _e('Elementor Detected!', 'vitapro-appointments-fse'); ?></strong> <?php _e('All blocks are also available as Elementor widgets with advanced styling options.', 'vitapro-appointments-fse'); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }

    /**
     * Display settings page
     */
    public function display_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php _e('VitaPro Appointments Settings', 'vitapro-appointments-fse'); ?></h1>
            <div class="card">
                <h2><?php _e('Settings', 'vitapro-appointments-fse'); ?></h2>
                <p><?php _e('Settings functionality will be available in the full version.', 'vitapro-appointments-fse'); ?></p>

                <h3><?php _e('Plugin Information', 'vitapro-appointments-fse'); ?></h3>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Version', 'vitapro-appointments-fse'); ?></th>
                        <td><?php echo VITAPRO_APPOINTMENTS_FSE_VERSION; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Plugin Path', 'vitapro-appointments-fse'); ?></th>
                        <td><?php echo VITAPRO_APPOINTMENTS_FSE_PATH; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Plugin URL', 'vitapro-appointments-fse'); ?></th>
                        <td><?php echo VITAPRO_APPOINTMENTS_FSE_URL; ?></td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Elementor Status', 'vitapro-appointments-fse'); ?></th>
                        <td><?php echo did_action('elementor/loaded') ? __('Active', 'vitapro-appointments-fse') : __('Not Active', 'vitapro-appointments-fse'); ?></td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
    }

    /**
     * Load plugin textdomain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'vitapro-appointments-fse',
            false,
            dirname(VITAPRO_APPOINTMENTS_FSE_PLUGIN_BASENAME) . '/languages'
        );
    }

    /**
     * Enqueue frontend assets
     */
    public function enqueue_frontend_assets() {
        // Frontend CSS
        if (file_exists(VITAPRO_APPOINTMENTS_FSE_PATH . 'assets/css/frontend.css')) {
            wp_enqueue_style(
                'vitapro-appointments-fse-frontend',
                VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/frontend.css',
                array(),
                VITAPRO_APPOINTMENTS_FSE_VERSION
            );
        }

        // Elementor CSS (if Elementor is active)
        if (did_action('elementor/loaded') && file_exists(VITAPRO_APPOINTMENTS_FSE_PATH . 'assets/css/elementor.css')) {
            wp_enqueue_style(
                'vitapro-appointments-fse-elementor',
                VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/elementor.css',
                array('vitapro-appointments-fse-frontend'),
                VITAPRO_APPOINTMENTS_FSE_VERSION
            );
        }

        // Frontend JavaScript
        if (file_exists(VITAPRO_APPOINTMENTS_FSE_PATH . 'assets/js/frontend.js')) {
            wp_enqueue_script(
                'vitapro-appointments-fse-frontend',
                VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/frontend.js',
                array('jquery'),
                VITAPRO_APPOINTMENTS_FSE_VERSION,
                true
            );

            // Localize script for AJAX
            wp_localize_script('vitapro-appointments-fse-frontend', 'vitaproAjax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('vitapro_appointments_nonce'),
                'strings' => array(
                    'loading' => __('Loading...', 'vitapro-appointments-fse'),
                    'error' => __('An error occurred. Please try again.', 'vitapro-appointments-fse'),
                    'success' => __('Success!', 'vitapro-appointments-fse'),
                )
            ));
        }
    }

    /**
     * Enqueue admin assets
     */
    public function enqueue_admin_assets($hook) {
        // Only load on plugin pages
        if (strpos($hook, 'vitapro-appointments') === false &&
            !in_array(get_post_type(), array('vpa_service', 'vpa_professional', 'vpa_appointment', 'vpa_holiday'))) {
            return;
        }

        // Admin CSS
        if (file_exists(VITAPRO_APPOINTMENTS_FSE_PATH . 'assets/css/admin.css')) {
            wp_enqueue_style(
                'vitapro-appointments-fse-admin',
                VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/admin.css',
                array(),
                VITAPRO_APPOINTMENTS_FSE_VERSION
            );
        }

        // Admin JavaScript
        if (file_exists(VITAPRO_APPOINTMENTS_FSE_PATH . 'assets/js/admin.js')) {
            wp_enqueue_script(
                'vitapro-appointments-fse-admin',
                VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/admin.js',
                array('jquery', 'wp-media'),
                VITAPRO_APPOINTMENTS_FSE_VERSION,
                true
            );
        }
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Ensure all dependency files are loaded before trying to use their classes
        $dependency_files_for_activation = array(
            'includes/class-audit-log.php',
            'includes/class-backup-recovery.php',
            'includes/class-notifications.php',
            'includes/class-reports.php',
            'includes/class-security.php',
            'includes/class-cron-jobs.php', // Adicionada a classe de Cron Jobs
        );

        foreach ($dependency_files_for_activation as $file) {
            $file_path = VITAPRO_APPOINTMENTS_FSE_PATH . $file;
            if (file_exists($file_path)) {
                require_once $file_path;
            }
        }

        // Create main appointments table
        $this->create_database_tables();

        // Create tables from other modules
        if (class_exists('VitaPro_Appointments_FSE_Audit_Log')) {
            $audit_log_instance = new VitaPro_Appointments_FSE_Audit_Log();
            if (method_exists($audit_log_instance, 'create_audit_tables')) {
                 $audit_log_instance->create_audit_tables();
            }
        }
        if (class_exists('VitaPro_Appointments_FSE_Backup_Recovery')) {
            $backup_instance = new VitaPro_Appointments_FSE_Backup_Recovery();
            if (method_exists($backup_instance, 'setup_backup_tables')) {
                $backup_instance->setup_backup_tables();
            }
        }
        if (class_exists('VitaPro_Appointments_FSE_Notifications')) {
            $notifications_instance = new VitaPro_Appointments_FSE_Notifications();
            if (method_exists($notifications_instance, 'create_notifications_table')) {
                $notifications_instance->create_notifications_table();
            }
        }
        if (class_exists('VitaPro_Appointments_FSE_Reports')) {
            $reports_instance = new VitaPro_Appointments_FSE_Reports();
            if (method_exists($reports_instance, 'create_reports_table')) {
                $reports_instance->create_reports_table();
            }
        }
        if (class_exists('VitaPro_Appointments_FSE_Security')) {
            $security_instance = new VitaPro_Appointments_FSE_Security();
            if (method_exists($security_instance, 'setup_security_tables')) {
                 $security_instance->setup_security_tables();
            }
        }
        
        // Schedule cron events
        if (class_exists('VitaPro_Appointments_FSE_Cron_Jobs')) {
            VitaPro_Appointments_FSE_Cron_Jobs::schedule_events();
        }


        // Flush rewrite rules
        flush_rewrite_rules();

        // Set default options
        $this->set_default_options();
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Unschedule cron events
        if (class_exists('VitaPro_Appointments_FSE_Cron_Jobs')) {
            VitaPro_Appointments_FSE_Cron_Jobs::unschedule_events();
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create database tables (specific to this main class)
     */
    private function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Appointments table
        $table_name = $wpdb->prefix . 'vpa_appointments';

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            service_id bigint(20) NOT NULL,
            professional_id bigint(20) NOT NULL,
            customer_name varchar(255) NOT NULL,
            customer_email varchar(255) NOT NULL,
            customer_phone varchar(50),
            appointment_date date NOT NULL,
            appointment_time time NOT NULL,
            duration int(11) NOT NULL DEFAULT 60,
            status varchar(20) NOT NULL DEFAULT 'pending',
            notes text,
            custom_fields longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY service_id (service_id),
            KEY professional_id (professional_id),
            KEY appointment_date (appointment_date),
            KEY status (status)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        // Update database version for this table
        update_option('vitapro_appointments_fse_db_version', '1.0.0');
    }

    /**
     * Set default plugin options
     */
    private function set_default_options() {
        $default_options = array(
            'general_settings' => array(
                'business_name' => get_bloginfo('name'),
                'business_email' => get_option('admin_email'),
                'business_phone' => '',
                'business_address' => '',
                'timezone' => get_option('timezone_string', 'UTC'),
                'date_format' => get_option('date_format'),
                'time_format' => get_option('time_format'),
                'currency' => 'USD',
                'currency_symbol' => '$',
                'currency_position' => 'before',
                'default_appointment_duration' => 60,
                'booking_advance_time' => 24,
                'cancellation_time_limit' => 24,
                'max_appointments_per_day' => 10,
                'require_login' => false,
                'auto_confirm_appointments' => false,
                'send_email_notifications' => true,
                'send_sms_notifications' => false,
            ),
        );

        foreach ($default_options as $option_name => $option_value) {
            if (!get_option('vitapro_appointments_' . $option_name)) {
                update_option('vitapro_appointments_' . $option_name, $option_value);
            }
        }
    }
}

// Initialize the plugin
VitaPro_Appointments_FSE::get_instance();