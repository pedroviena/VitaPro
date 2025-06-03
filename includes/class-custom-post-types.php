<?php
/**
 * Custom Post Types
 * 
 * Registers and manages custom post types for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Custom_Post_Types {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('init', array($this, 'register_taxonomies'));
        add_filter('manage_vpa_service_posts_columns', array($this, 'service_columns'));
        add_action('manage_vpa_service_posts_custom_column', array($this, 'service_column_content'), 10, 2);
        add_filter('manage_vpa_professional_posts_columns', array($this, 'professional_columns'));
        add_action('manage_vpa_professional_posts_custom_column', array($this, 'professional_column_content'), 10, 2);
        add_filter('manage_vpa_appointment_posts_columns', array($this, 'appointment_columns'));
        add_action('manage_vpa_appointment_posts_custom_column', array($this, 'appointment_column_content'), 10, 2);
        add_filter('manage_vpa_holiday_posts_columns', array($this, 'holiday_columns'));
        add_action('manage_vpa_holiday_posts_custom_column', array($this, 'holiday_column_content'), 10, 2);
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
     * Custom columns for service post type
     */
    public function service_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['price'] = __('Price', 'vitapro-appointments-fse');
        $new_columns['duration'] = __('Duration', 'vitapro-appointments-fse');
        $new_columns['category'] = __('Category', 'vitapro-appointments-fse');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Content for service custom columns
     */
    public function service_column_content($column, $post_id) {
        switch ($column) {
            case 'price':
                $price = get_post_meta($post_id, '_vpa_service_price', true);
                $currency = get_option('vitapro_appointments_general_settings')['currency_symbol'] ?? '$';
                echo !empty($price) ? esc_html($currency . $price) : '-';
                break;
                
            case 'duration':
                $duration = get_post_meta($post_id, '_vpa_service_duration', true);
                echo !empty($duration) ? esc_html($duration . ' ' . __('minutes', 'vitapro-appointments-fse')) : '-';
                break;
                
            case 'category':
                $terms = get_the_terms($post_id, 'vpa_service_category');
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = $term->name;
                    }
                    echo esc_html(implode(', ', $term_names));
                } else {
                    echo '-';
                }
                break;
        }
    }
    
    /**
     * Custom columns for professional post type
     */
    public function professional_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['specialty'] = __('Specialty', 'vitapro-appointments-fse');
        $new_columns['services'] = __('Services', 'vitapro-appointments-fse');
        $new_columns['date'] = $columns['date'];
        
        return $new_columns;
    }
    
    /**
     * Content for professional custom columns
     */
    public function professional_column_content($column, $post_id) {
        switch ($column) {
            case 'specialty':
                $terms = get_the_terms($post_id, 'vpa_professional_specialty');
                if (!empty($terms) && !is_wp_error($terms)) {
                    $term_names = array();
                    foreach ($terms as $term) {
                        $term_names[] = $term->name;
                    }
                    echo esc_html(implode(', ', $term_names));
                } else {
                    echo '-';
                }
                break;
                
            case 'services':
                $services = get_post_meta($post_id, '_vpa_professional_services', true);
                if (!empty($services) && is_array($services)) {
                    $service_names = array();
                    foreach ($services as $service_id) {
                        $service_names[] = get_the_title($service_id);
                    }
                    echo esc_html(implode(', ', $service_names));
                } else {
                    echo '-';
                }
                break;
        }
    }
    
    /**
     * Custom columns for appointment post type
     */
    public function appointment_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = __('Customer', 'vitapro-appointments-fse');
        $new_columns['service'] = __('Service', 'vitapro-appointments-fse');
        $new_columns['professional'] = __('Professional', 'vitapro-appointments-fse');
        $new_columns['date_time'] = __('Date & Time', 'vitapro-appointments-fse');
        $new_columns['status'] = __('Status', 'vitapro-appointments-fse');
        
        return $new_columns;
    }
    
    /**
     * Content for appointment custom columns
     */
    public function appointment_column_content($column, $post_id) {
        switch ($column) {
            case 'service':
                $service_id = get_post_meta($post_id, '_vpa_appointment_service', true);
                echo !empty($service_id) ? esc_html(get_the_title($service_id)) : '-';
                break;
                
            case 'professional':
                $professional_id = get_post_meta($post_id, '_vpa_appointment_professional', true);
                echo !empty($professional_id) ? esc_html(get_the_title($professional_id)) : '-';
                break;
                
            case 'date_time':
                $date = get_post_meta($post_id, '_vpa_appointment_date', true);
                $time = get_post_meta($post_id, '_vpa_appointment_time', true);
                
                if (!empty($date) && !empty($time)) {
                    $date_format = get_option('date_format');
                    $time_format = get_option('time_format');
                    $formatted_date = date_i18n($date_format, strtotime($date));
                    $formatted_time = date_i18n($time_format, strtotime($time));
                    echo esc_html($formatted_date . ' ' . $formatted_time);
                } else {
                    echo '-';
                }
                break;
                
            case 'status':
                $status = get_post_meta($post_id, '_vpa_appointment_status', true);
                $status_labels = array(
                    'pending' => __('Pending', 'vitapro-appointments-fse'),
                    'confirmed' => __('Confirmed', 'vitapro-appointments-fse'),
                    'cancelled' => __('Cancelled', 'vitapro-appointments-fse'),
                    'completed' => __('Completed', 'vitapro-appointments-fse'),
                );
                
                $status_class = 'vpa-status-badge vpa-status-' . $status;
                $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
                
                echo '<span class="' . esc_attr($status_class) . '">' . esc_html($status_label) . '</span>';
                break;
        }
    }
    
    /**
     * Custom columns for holiday post type
     */
    public function holiday_columns($columns) {
        $new_columns = array();
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = $columns['title'];
        $new_columns['start_date'] = __('Start Date', 'vitapro-appointments-fse');
        $new_columns['end_date'] = __('End Date', 'vitapro-appointments-fse');
        $new_columns['professionals'] = __('Professionals', 'vitapro-appointments-fse');
        
        return $new_columns;
    }
    
    /**
     * Content for holiday custom columns
     */
    public function holiday_column_content($column, $post_id) {
        switch ($column) {
            case 'start_date':
                $start_date = get_post_meta($post_id, '_vpa_holiday_start_date', true);
                if (!empty($start_date)) {
                    $date_format = get_option('date_format');
                    echo esc_html(date_i18n($date_format, strtotime($start_date)));
                } else {
                    echo '-';
                }
                break;
                
            case 'end_date':
                $end_date = get_post_meta($post_id, '_vpa_holiday_end_date', true);
                if (!empty($end_date)) {
                    $date_format = get_option('date_format');
                    echo esc_html(date_i18n($date_format, strtotime($end_date)));
                } else {
                    echo '-';
                }
                break;
                
            case 'professionals':
                $professionals = get_post_meta($post_id, '_vpa_holiday_professionals', true);
                if (!empty($professionals) && is_array($professionals)) {
                    if (in_array('all', $professionals)) {
                        echo esc_html__('All Professionals', 'vitapro-appointments-fse');
                    } else {
                        $professional_names = array();
                        foreach ($professionals as $professional_id) {
                            $professional_names[] = get_the_title($professional_id);
                        }
                        echo esc_html(implode(', ', $professional_names));
                    }
                } else {
                    echo esc_html__('All Professionals', 'vitapro-appointments-fse');
                }
                break;
        }
    }
}