<?php
/**
 * Elementor Integration
 * 
 * Handles Elementor widgets integration for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Elementor_Integration {
    
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
    public function register_widgets() {
        // Include widget files
        require_once VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/booking-form-widget.php';
        require_once VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/service-list-widget.php';
        require_once VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/professional-list-widget.php';
        require_once VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/availability-calendar-widget.php';
        require_once VITAPRO_APPOINTMENTS_FSE_PATH . 'includes/elementor/widgets/my-appointments-widget.php';
        
        // Register widgets
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Booking_Form_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Service_List_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Professional_List_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_Availability_Calendar_Widget());
        \Elementor\Plugin::instance()->widgets_manager->register_widget_type(new \VitaPro_Elementor_My_Appointments_Widget());
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
    new VitaPro_Elementor_Integration();
}