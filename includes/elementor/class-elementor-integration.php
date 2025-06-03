<?php
/**
 * Elementor Integration
 * 
 * Handles Elementor widgets integration for VitaPro Appointments FSE.
 *
 * Integrates VitaPro Appointments FSE with Elementor widgets.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Elementor_Integration
 *
 * Handles Elementor widget registration and integration for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Elementor_Integration {
    
    /**
     * Initialize the integration
     */
    public function __construct() {
        add_action('elementor/widgets/widgets_registered', array($this, 'register_widgets'));
        add_action('elementor/elements/categories_registered', array($this, 'add_elementor_widget_categories'));
        add_action('elementor/frontend/after_enqueue_styles', array($this, 'enqueue_elementor_styles'));
        add_action('elementor/frontend/after_register_scripts', array($this, 'enqueue_elementor_scripts'));
    }
    
    /**
     * Register Elementor widgets
     */
    public function register_widgets($widgets_manager) {
        // Booking Form Widget
        $booking_form_widget_file = VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/booking-form-widget.php';
        if (file_exists($booking_form_widget_file)) {
            require_once $booking_form_widget_file;
        } else {
            error_log('VitaPro Appointments FSE Error: Booking Form Widget file not found at: ' . $booking_form_widget_file);
        }

        // Service List Widget
        $service_list_widget_file = VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/service-list-widget.php';
        if (file_exists($service_list_widget_file)) {
            require_once $service_list_widget_file;
        } else {
            error_log('VitaPro Appointments FSE Error: Service List Widget file not found at: ' . $service_list_widget_file);
        }

        // Professional List Widget
        $professional_list_widget_file = VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/professional-list-widget.php';
        if (file_exists($professional_list_widget_file)) {
            require_once $professional_list_widget_file;
        } else {
            error_log('VitaPro Appointments FSE Error: Professional List Widget file not found at: ' . $professional_list_widget_file);
        }

        // Availability Calendar Widget
        $availability_calendar_widget_file = VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/availability-calendar-widget.php';
        if (file_exists($availability_calendar_widget_file)) {
            require_once $availability_calendar_widget_file;
        } else {
            error_log('VitaPro Appointments FSE Error: Availability Calendar Widget file not found at: ' . $availability_calendar_widget_file);
        }

        // My Appointments Widget
        $my_appointments_widget_file = VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/my-appointments-widget.php';
        if (file_exists($my_appointments_widget_file)) {
            require_once $my_appointments_widget_file;
        } else {
            error_log('VitaPro Appointments FSE Error: My Appointments Widget file not found at: ' . $my_appointments_widget_file);
        }

        // Registrar widgets com verificação de classe
        if (class_exists('VitaPro_Elementor_Booking_Form_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Booking_Form_Widget());
        } else {
            error_log('VitaPro Appointments FSE Error: Class VitaPro_Elementor_Booking_Form_Widget not found after inclusion. Check file content or syntax.');
        }

        if (class_exists('VitaPro_Elementor_Service_List_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Service_List_Widget());
        } else {
            error_log('VitaPro Appointments FSE Error: Class VitaPro_Elementor_Service_List_Widget not found after inclusion. Check file content or syntax.');
        }

        if (class_exists('VitaPro_Elementor_Professional_List_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Professional_List_Widget());
        } else {
            error_log('VitaPro Appointments FSE Error: Class VitaPro_Elementor_Professional_List_Widget not found after inclusion. Check file content or syntax.');
        }

        if (class_exists('VitaPro_Elementor_Availability_Calendar_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Availability_Calendar_Widget());
        } else {
            error_log('VitaPro Appointments FSE Error: Class VitaPro_Elementor_Availability_Calendar_Widget not found after inclusion. Check file content or syntax.');
        }

        if (class_exists('VitaPro_Elementor_My_Appointments_Widget')) {
            \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_My_Appointments_Widget());
        } else {
            error_log('VitaPro Appointments FSE Error: Class VitaPro_Elementor_My_Appointments_Widget not found after inclusion. Check file content or syntax.');
        }
    }
    
    /**
     * Add widget categories
     */
    public function add_elementor_widget_categories($elements_manager) {
        $elements_manager->add_category(
            'vitapro-appointments',
            array(
                'title' => __('VitaPro Appointments', 'vitapro-appointments-fse'),
                'icon' => 'fa fa-calendar-alt',
            )
        );
    }
    
    /**
     * Enqueue Elementor styles
     */
    public function enqueue_elementor_styles() {
        wp_enqueue_style('vitapro-appointments-fse-frontend');
    }
    
    /**
     * Enqueue Elementor scripts
     */
    public function enqueue_elementor_scripts() {
        wp_enqueue_script('vitapro-appointments-fse-frontend');
    }
}

// Initialize if Elementor is active
if (did_action('elementor/loaded')) {
    new VitaPro_Appointments_FSE_Elementor_Integration();
}