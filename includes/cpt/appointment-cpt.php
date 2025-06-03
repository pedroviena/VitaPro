<?php
/**
 * Registers the Appointment Custom Post Type.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Appointment custom post type.
 *
 * @return void
 * @uses register_post_type()
 */
function vitapro_appointments_register_appointment_cpt() {
    register_post_type('vpa_appointment', array(
        'labels' => array(
            'name' => __('Appointments', 'vitapro-appointments-fse'),
            'singular_name' => __('Appointment', 'vitapro-appointments-fse'),
            'menu_name' => __('Appointments', 'vitapro-appointments-fse'),
            'name_admin_bar' => __('Appointment', 'vitapro-appointments-fse'),
            'archives' => __('Appointment Archives', 'vitapro-appointments-fse'),
            'attributes' => __('Appointment Attributes', 'vitapro-appointments-fse'),
            'parent_item_colon' => __('Parent Appointment:', 'vitapro-appointments-fse'),
            'all_items' => __('All Appointments', 'vitapro-appointments-fse'),
            'add_new_item' => __('Add New Appointment', 'vitapro-appointments-fse'),
            'add_new' => __('Add New', 'vitapro-appointments-fse'),
            'new_item' => __('New Appointment', 'vitapro-appointments-fse'),
            'edit_item' => __('Edit Appointment', 'vitapro-appointments-fse'),
            'update_item' => __('Update Appointment', 'vitapro-appointments-fse'),
            'view_item' => __('View Appointment', 'vitapro-appointments-fse'),
            'view_items' => __('View Appointments', 'vitapro-appointments-fse'),
            'search_items' => __('Search Appointment', 'vitapro-appointments-fse'),
            'not_found' => __('Not found', 'vitapro-appointments-fse'),
            'not_found_in_trash' => __('Not found in Trash', 'vitapro-appointments-fse'),
            'featured_image' => __('Featured Image', 'vitapro-appointments-fse'),
            'set_featured_image' => __('Set featured image', 'vitapro-appointments-fse'),
            'remove_featured_image' => __('Remove featured image', 'vitapro-appointments-fse'),
            'use_featured_image' => __('Use as featured image', 'vitapro-appointments-fse'),
            'insert_into_item' => __('Insert into appointment', 'vitapro-appointments-fse'),
            'uploaded_to_this_item' => __('Uploaded to this appointment', 'vitapro-appointments-fse'),
            'items_list' => __('Appointments list', 'vitapro-appointments-fse'),
            'items_list_navigation' => __('Appointments list navigation', 'vitapro-appointments-fse'),
            'filter_items_list' => __('Filter appointments list', 'vitapro-appointments-fse'),
        ),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => 'vitapro-appointments',
        'supports' => array('title', 'author'),
        'show_in_rest' => false,
    ));
}
add_action( 'init', 'vitapro_appointments_register_appointment_cpt', 0 );

/**
 * Add meta boxes for the Appointment CPT.
 *
 * @param string $post_type The current post type.
 * @return void
 * @uses add_meta_box()
 */
function vitapro_add_appointment_meta_boxes($post_type) {
    add_meta_box(
        'vpa_appointment_details',
        __( 'Appointment Details', 'vitapro-appointments-fse' ),
        'vitapro_render_appointment_details_meta_box',
        'vpa_appointment',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'vitapro_add_appointment_meta_boxes' );

/**
 * Render the Appointment Details meta box.
 *
 * @param WP_Post $post The current post object.
 * @return void
 * @uses get_post_meta()
 */
function vitapro_render_appointment_details_meta_box( $post ) {
    global $wpdb;
    $custom_table_id = get_post_meta($post->ID, '_vpa_custom_table_id', true);
    $appointment = $custom_table_id
        ? $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vpa_appointments WHERE id = %d", $custom_table_id), ARRAY_A)
        : array();

    $services = get_posts( array(
        'post_type'      => 'vpa_service',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    ?>
    <div class="vpa-admin-appointment-fields">
        <label><?php _e('Service', 'vitapro-appointments-fse'); ?></label>
        <select name="service_id">
            <?php
            foreach ($services as $service) {
                echo '<option value="' . esc_attr($service->ID) . '" ' . selected($appointment['service_id'] ?? '', $service->ID, false) . '>' . esc_html($service->post_title) . '</option>';
            }
            ?>
        </select>
        <!-- Repita para professional_id, customer_name, customer_email, customer_phone, appointment_date, appointment_time, status, notes, etc. -->
        <!-- Campos customizados: -->
        <?php
        // Exemplo de campo customizado:
        // <input type="text" name="custom_fields[field_id]" value="<?php echo esc_attr($appointment['custom_fields']['field_id'] ?? ''); ?>">
        ?>
    </div>
    <?php
}

/**
 * Salva os dados do agendamento apenas na tabela customizada.
 *
 * @param int $post_id
 * @return void
 */
function vitapro_save_appointment_meta_data($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (get_post_type($post_id) !== 'vpa_appointment') return;

    global $wpdb;
    $table = $wpdb->prefix . 'vpa_appointments';
    $custom_table_id = get_post_meta($post_id, '_vpa_custom_table_id', true);

    $fields = array(
        'service_id'      => isset($_POST['service_id']) ? intval($_POST['service_id']) : null,
        'professional_id' => isset($_POST['professional_id']) ? intval($_POST['professional_id']) : null,
        'customer_name'   => isset($_POST['customer_name']) ? sanitize_text_field($_POST['customer_name']) : '',
        'customer_email'  => isset($_POST['customer_email']) ? sanitize_email($_POST['customer_email']) : '',
        'customer_phone'  => isset($_POST['customer_phone']) ? sanitize_text_field($_POST['customer_phone']) : '',
        'appointment_date'=> isset($_POST['appointment_date']) ? sanitize_text_field($_POST['appointment_date']) : '',
        'appointment_time'=> isset($_POST['appointment_time']) ? sanitize_text_field($_POST['appointment_time']) : '',
        'status'          => isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '',
        'notes'           => isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '',
        // 'custom_fields'   => ... // serializar se necessário
        'updated_at'      => current_time('mysql', 1),
    );

    if ($custom_table_id) {
        $wpdb->update($table, $fields, array('id' => $custom_table_id));
        do_action('vitapro_appointment_updated', $custom_table_id, $fields);
    } else {
        $fields['created_at'] = current_time('mysql', 1);
        $wpdb->insert($table, $fields);
        $new_id = $wpdb->insert_id;
        update_post_meta($post_id, '_vpa_custom_table_id', $new_id);
        do_action('vitapro_appointment_created', $new_id);
    }

    // Atualize o título do post para refletir os dados
    $title = $fields['customer_name'] . ' - ' . get_the_title($fields['service_id']) . ' - ' . $fields['appointment_date'];
    remove_action('save_post', 'vitapro_save_appointment_meta_data');
    wp_update_post(array('ID' => $post_id, 'post_title' => $title));
    add_action('save_post', 'vitapro_save_appointment_meta_data');
}

/**
 * Deleta o registro da tabela customizada ao excluir o CPT
 */
function vitapro_delete_appointment_from_custom_table($post_id) {
    if (get_post_type($post_id) !== 'vpa_appointment') return;
    global $wpdb;
    $custom_table_id = get_post_meta($post_id, '_vpa_custom_table_id', true);
    if ($custom_table_id) {
        $wpdb->delete($wpdb->prefix . 'vpa_appointments', array('id' => $custom_table_id));
        do_action('vitapro_appointment_deleted', $custom_table_id);
    }
}

/**
 * Customize appointment list columns.
 *
 * @param array $columns The existing columns.
 * @return array Modified columns.
 */
function vitapro_set_appointment_columns( $columns ) {
    $columns = array(
        'cb'           => $columns['cb'],
        'title'        => __( 'Appointment', 'vitapro-appointments-fse' ),
        'service'      => __( 'Service', 'vitapro-appointments-fse' ),
        'professional' => __( 'Professional', 'vitapro-appointments-fse' ),
        'patient'      => __( 'Patient', 'vitapro-appointments-fse' ),
        'date_time'    => __( 'Date & Time', 'vitapro-appointments-fse' ),
        'status'       => __( 'Status', 'vitapro-appointments-fse' ),
        'date'         => __( 'Created', 'vitapro-appointments-fse' ),
    );
    return $columns;
}
add_filter( 'manage_vpa_appointment_posts_columns', 'vitapro_set_appointment_columns' );

/**
 * Render custom column content for the Appointment CPT.
 *
 * @param string $column The column name.
 * @param int $post_id The post ID.
 * @return void
 */
function vitapro_render_appointment_columns( $column, $post_id ) {
    global $wpdb;
    $custom_table_id = get_post_meta($post_id, '_vpa_custom_table_id', true);
    if (!$custom_table_id) { echo '—'; return; }
    $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}vpa_appointments WHERE id = %d", $custom_table_id));
    if (!$appointment) { echo '—'; return; }

    switch ($column) {
        case 'vpa_service':
            echo $appointment->service_id ? esc_html(get_the_title($appointment->service_id)) : '—';
            break;
        case 'vpa_professional':
            echo $appointment->professional_id ? esc_html(get_the_title($appointment->professional_id)) : __('Any', 'vitapro-appointments-fse');
            break;
        case 'vpa_date_time':
            $options = get_option('vitapro_appointments_main_settings', array());
            $date_format = isset($options['date_format']) ? $options['date_format'] : get_option('date_format');
            $time_format = isset($options['time_format']) ? $options['time_format'] : get_option('time_format');
            if ($appointment->appointment_date && $appointment->appointment_time) {
                echo esc_html(date_i18n($date_format, strtotime($appointment->appointment_date))) . ' @ ' . esc_html(date_i18n($time_format, strtotime($appointment->appointment_time)));
            } else {
                echo '—';
            }
            break;
        case 'vpa_status':
            $status = $appointment->status;
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
add_action( 'manage_vpa_appointment_posts_custom_column', 'vitapro_render_appointment_columns', 10, 2 );