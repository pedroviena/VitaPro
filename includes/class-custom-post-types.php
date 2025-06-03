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

        // Hooks para colunas personalizadas nas listagens de admin
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
        $menu_parent_slug = 'vitapro-appointments'; // Slug do menu pai principal

        // Service post type
        register_post_type('vpa_service', array(
            'labels' => array(
                'name' => __('Services', 'vitapro-appointments-fse'),
                'singular_name' => __('Service', 'vitapro-appointments-fse'),
                'add_new' => __('Add New Service', 'vitapro-appointments-fse'), // Alterado
                'add_new_item' => __('Add New Service', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Service', 'vitapro-appointments-fse'),
                'new_item' => __('New Service', 'vitapro-appointments-fse'),
                'view_item' => __('View Service', 'vitapro-appointments-fse'),
                'search_items' => __('Search Services', 'vitapro-appointments-fse'),
                'not_found' => __('No services found', 'vitapro-appointments-fse'),
                'not_found_in_trash' => __('No services found in trash', 'vitapro-appointments-fse'),
                'menu_name' => __('Services', 'vitapro-appointments-fse'), // Nome do menu
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => $menu_parent_slug, // Associado ao menu pai
            'query_var' => true,
            'rewrite' => array('slug' => 'service'),
            'capability_type' => 'post', // Ou um capability type customizado se necessário
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 10, // Ordem dentro do menu pai
            'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'custom-fields'),
            'show_in_rest' => true, // Para API REST e editor de blocos
            'menu_icon' => 'dashicons-clipboard', // Ícone opcional para o CPT no menu
        ));

        // Professional post type
        register_post_type('vpa_professional', array(
            'labels' => array(
                'name' => __('Professionals', 'vitapro-appointments-fse'),
                'singular_name' => __('Professional', 'vitapro-appointments-fse'),
                'add_new' => __('Add New Professional', 'vitapro-appointments-fse'), // Alterado
                'add_new_item' => __('Add New Professional', 'vitapro-appointments-fse'),
                'edit_item' => __('Edit Professional', 'vitapro-appointments-fse'),
                // ... (resto dos labels como estavam)
                'menu_name' => __('Professionals', 'vitapro-appointments-fse'),
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => $menu_parent_slug,
            'query_var' => true,
            'rewrite' => array('slug' => 'professional'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 20,
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-businessman',
        ));

        // Appointment post type
        register_post_type('vpa_appointment', array(
            'labels' => array(
                'name' => __('Appointments', 'vitapro-appointments-fse'),
                'singular_name' => __('Appointment', 'vitapro-appointments-fse'),
                'add_new' => __('Add New Appointment', 'vitapro-appointments-fse'), // Alterado
                'add_new_item' => __('Add New Appointment', 'vitapro-appointments-fse'),
                // ... (resto dos labels)
                'menu_name' => __('Appointments', 'vitapro-appointments-fse'),
            ),
            'public' => false, // Geralmente agendamentos não são públicos
            'publicly_queryable' => false, // Não deve ser acessível via URL direta
            'show_ui' => true,
            'show_in_menu' => $menu_parent_slug,
            'query_var' => false, // Se publicly_queryable é false
            'rewrite' => false, // Se publicly_queryable é false
            'capability_type' => 'post', // Considere 'vpa_appointment' e mapeie capabilities
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 5, // Mais importante, aparece antes
            'supports' => array('title', 'custom-fields', 'author'), // 'Author' pode ser útil para o admin que criou
            'show_in_rest' => true, // Para interações via API, se necessário
            'menu_icon' => 'dashicons-calendar-alt',
        ));

        // Holiday post type
        register_post_type('vpa_holiday', array(
            'labels' => array(
                'name' => __('Holidays', 'vitapro-appointments-fse'),
                'singular_name' => __('Holiday', 'vitapro-appointments-fse'),
                'add_new' => __('Add New Holiday', 'vitapro-appointments-fse'), // Alterado
                // ... (resto dos labels)
                'menu_name' => __('Holidays', 'vitapro-appointments-fse'),
            ),
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => $menu_parent_slug,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 30,
            'supports' => array('title', 'custom-fields'), // Removido 'editor' se não for usado
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-flag',
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
                // ... (resto dos labels)
                'menu_name' => __('Categories', 'vitapro-appointments-fse'),
            ),
            'hierarchical' => true,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'service-category'),
            'show_in_rest' => true, // Para API REST e editor de blocos
        ));

        // Professional Specialty taxonomy
        register_taxonomy('vpa_professional_specialty', 'vpa_professional', array(
            'labels' => array(
                'name' => __('Specialties', 'vitapro-appointments-fse'),
                'singular_name' => __('Specialty', 'vitapro-appointments-fse'),
                // ... (resto dos labels)
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
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => __('Service Name', 'vitapro-appointments-fse'), // Mais específico
            'vpa_service_category' => __('Category', 'vitapro-appointments-fse'), // Coluna da taxonomia
            'price' => __('Price', 'vitapro-appointments-fse'),
            'duration' => __('Duration', 'vitapro-appointments-fse'),
            'date' => $columns['date'] // Coluna de data padrão do WP
        );
        return $new_columns;
    }

    /**
     * Content for service custom columns
     */
    public function service_column_content($column, $post_id) {
        switch ($column) {
            case 'price':
                $price = get_post_meta($post_id, '_vpa_service_price', true);
                // Obter símbolo da moeda das opções do plugin
                $options = get_option('vitapro_appointments_settings'); // Ajustar nome da opção se necessário
                $currency_symbol = isset($options['currency_symbol']) ? $options['currency_symbol'] : '$';
                echo !empty($price) ? esc_html($currency_symbol . number_format_i18n(floatval($price), 2)) : '—';
                break;

            case 'duration':
                $duration = get_post_meta($post_id, '_vpa_service_duration', true);
                echo !empty($duration) ? esc_html($duration . ' ' . __('minutes', 'vitapro-appointments-fse')) : '—';
                break;

            // 'vpa_service_category' será preenchida automaticamente pelo WordPress se 'show_admin_column' => true
        }
    }

    /**
     * Custom columns for professional post type
     */
    public function professional_columns($columns) {
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => __('Professional Name', 'vitapro-appointments-fse'),
            'vpa_professional_specialty' => __('Specialty', 'vitapro-appointments-fse'), // Coluna da taxonomia
            'services_offered' => __('Services Offered', 'vitapro-appointments-fse'), // Renomeado
            'date' => $columns['date']
        );
        return $new_columns;
    }

    /**
     * Content for professional custom columns
     */
    public function professional_column_content($column, $post_id) {
        switch ($column) {
            // 'vpa_professional_specialty' será preenchida automaticamente
            case 'services_offered':
                $services_ids = get_post_meta($post_id, '_vpa_professional_services', true); // Supondo que seja um array de IDs
                if (!empty($services_ids) && is_array($services_ids)) {
                    $service_names = array_map(function($service_id) {
                        return get_the_title($service_id);
                    }, $services_ids);
                    echo esc_html(implode(', ', array_filter($service_names)));
                } else {
                    echo '—';
                }
                break;
        }
    }

    /**
     * Custom columns for appointment post type
     */
    public function appointment_columns($columns) {
        // A coluna 'title' será o título do post, que você está formatando para 'Customer - Service - Date'
        // Pode ser melhor renomeá-la explicitamente ou adicionar uma coluna 'customer_name'
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => __('Appointment For', 'vitapro-appointments-fse'), // Ou 'Customer'
            'vpa_service' => __('Service', 'vitapro-appointments-fse'),
            'vpa_professional' => __('Professional', 'vitapro-appointments-fse'),
            'vpa_date_time' => __('Date & Time', 'vitapro-appointments-fse'),
            'vpa_status' => __('Status', 'vitapro-appointments-fse'),
            'date' => __('Booked On', 'vitapro-appointments-fse') // Data de criação do post
        );
        return $new_columns;
    }

    /**
     * Content for appointment custom columns
     */
    public function appointment_column_content($column, $post_id) {
        // As meta keys usadas aqui devem corresponder às salvas em includes/cpt/appointment-cpt.php
        // e/ou na sua tabela customizada se você estiver migrando.
        // Esta classe foca em CPTs, então usará get_post_meta.
        switch ($column) {
            case 'vpa_service':
                $service_id = get_post_meta($post_id, '_vpa_appointment_service_id', true); // Corrigido para _id
                echo $service_id ? esc_html(get_the_title($service_id)) : '—';
                break;

            case 'vpa_professional':
                $professional_id = get_post_meta($post_id, '_vpa_appointment_professional_id', true); // Corrigido para _id
                echo $professional_id ? esc_html(get_the_title($professional_id)) : __('Any', 'vitapro-appointments-fse');
                break;

            case 'vpa_date_time':
                $date = get_post_meta($post_id, '_vpa_appointment_date', true);
                $time = get_post_meta($post_id, '_vpa_appointment_time', true);
                $options = get_option('vitapro_appointments_settings'); // Ajustar nome da opção
                $date_format = isset($options['date_format']) ? $options['date_format'] : get_option('date_format');
                $time_format = isset($options['time_format']) ? $options['time_format'] : get_option('time_format');

                if ($date && $time) {
                    echo esc_html(date_i18n($date_format, strtotime($date))) . ' @ ' . esc_html(date_i18n($time_format, strtotime($time)));
                } else {
                    echo '—';
                }
                break;

            case 'vpa_status':
                $status = get_post_meta($post_id, '_vpa_appointment_status', true);
                $status_labels = array(
                    'pending'   => __('Pending', 'vitapro-appointments-fse'),
                    'confirmed' => __('Confirmed', 'vitapro-appointments-fse'),
                    'completed' => __('Completed', 'vitapro-appointments-fse'),
                    'cancelled' => __('Cancelled', 'vitapro-appointments-fse'),
                    'no_show'   => __('No Show', 'vitapro-appointments-fse'),
                );
                $status_label = isset($status_labels[$status]) ? $status_labels[$status] : ucfirst($status);
                echo '<span class="vpa-status-badge vpa-status-' . esc_attr($status) . '">' . esc_html($status_label) . '</span>';
                break;
        }
    }

    /**
     * Custom columns for holiday post type
     */
    public function holiday_columns($columns) {
        $new_columns = array(
            'cb' => $columns['cb'],
            'title' => __('Holiday Name', 'vitapro-appointments-fse'),
            'vpa_holiday_date' => __('Date', 'vitapro-appointments-fse'), // Mudado para data única
            'vpa_holiday_recurring' => __('Recurring', 'vitapro-appointments-fse'),
            // 'professionals' => __('Affected Professionals', 'vitapro-appointments-fse'), // Se você implementar essa lógica
            'date' => $columns['date']
        );
        return $new_columns;
    }

    /**
     * Content for holiday custom columns
     */
    public function holiday_column_content($column, $post_id) {
        // As meta keys usadas aqui devem corresponder às salvas em includes/cpt/holiday-cpt.php
        switch ($column) {
            case 'vpa_holiday_date':
                $date = get_post_meta($post_id, '_vpa_holiday_date', true);
                $options = get_option('vitapro_appointments_settings');
                $date_format = isset($options['date_format']) ? $options['date_format'] : get_option('date_format');
                echo $date ? esc_html(date_i18n($date_format, strtotime($date))) : '—';
                break;
            case 'vpa_holiday_recurring':
                $is_recurring = get_post_meta($post_id, '_vpa_holiday_recurring', true);
                echo $is_recurring ? __('Yes', 'vitapro-appointments-fse') : __('No', 'vitapro-appointments-fse');
                break;
            /*
            case 'professionals':
                $professionals = get_post_meta($post_id, '_vpa_holiday_professionals', true); // Supondo que seja um array de IDs ou 'all'
                if (!empty($professionals)) {
                    if (is_array($professionals) && in_array('all', $professionals)) {
                        echo esc_html__('All Professionals', 'vitapro-appointments-fse');
                    } elseif (is_array($professionals)) {
                        $names = array_map(function($id){ return get_the_title($id); }, $professionals);
                        echo esc_html(implode(', ', $names));
                    } else {
                        echo '—';
                    }
                } else {
                    echo esc_html__('All Professionals', 'vitapro-appointments-fse'); // Ou 'N/A' se não definido
                }
                break;
            */
        }
    }
}