<?php
/**
 * Registers the Service Custom Post Type.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Service custom post type.
 *
 * @return void
 * @uses register_post_type()
 */
function vitapro_appointments_register_service_cpt() {
    $labels = array(
        'name'                  => _x( 'Services', 'Post Type General Name', 'vitapro-appointments-fse' ),
        'singular_name'         => _x( 'Service', 'Post Type Singular Name', 'vitapro-appointments-fse' ),
        'menu_name'             => __( 'Services', 'vitapro-appointments-fse' ),
        'name_admin_bar'        => __( 'Service', 'vitapro-appointments-fse' ),
        'archives'              => __( 'Service Archives', 'vitapro-appointments-fse' ),
        'attributes'            => __( 'Service Attributes', 'vitapro-appointments-fse' ),
        'parent_item_colon'     => __( 'Parent Service:', 'vitapro-appointments-fse' ),
        'all_items'             => __( 'All Services', 'vitapro-appointments-fse' ),
        'add_new_item'          => __( 'Add New Service', 'vitapro-appointments-fse' ),
        'add_new'               => __( 'Add New', 'vitapro-appointments-fse' ),
        'new_item'              => __( 'New Service', 'vitapro-appointments-fse' ),
        'edit_item'             => __( 'Edit Service', 'vitapro-appointments-fse' ),
        'update_item'           => __( 'Update Service', 'vitapro-appointments-fse' ),
        'view_item'             => __( 'View Service', 'vitapro-appointments-fse' ),
        'view_items'            => __( 'View Services', 'vitapro-appointments-fse' ),
        'search_items'          => __( 'Search Service', 'vitapro-appointments-fse' ),
        'not_found'             => __( 'Not found', 'vitapro-appointments-fse' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vitapro-appointments-fse' ),
        'featured_image'        => __( 'Featured Image', 'vitapro-appointments-fse' ),
        'set_featured_image'    => __( 'Set featured image', 'vitapro-appointments-fse' ),
        'remove_featured_image' => __( 'Remove featured image', 'vitapro-appointments-fse' ),
        'use_featured_image'    => __( 'Use as featured image', 'vitapro-appointments-fse' ),
        'insert_into_item'      => __( 'Insert into service', 'vitapro-appointments-fse' ),
        'uploaded_to_this_item' => __( 'Uploaded to this service', 'vitapro-appointments-fse' ),
        'items_list'            => __( 'Services list', 'vitapro-appointments-fse' ),
        'items_list_navigation' => __( 'Services list navigation', 'vitapro-appointments-fse' ),
        'filter_items_list'     => __( 'Filter services list', 'vitapro-appointments-fse' ),
    );

    $args = array(
        'label'                 => __( 'Service', 'vitapro-appointments-fse' ),
        'description'           => __( 'Services offered by the clinic', 'vitapro-appointments-fse' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail' ),
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => 'vitapro-appointments',
        'menu_position'         => 5,
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest'          => true,
    );

    register_post_type( 'vpa_service', $args );
}
add_action( 'init', 'vitapro_appointments_register_service_cpt', 0 );

/**
 * Add meta boxes for the Service CPT.
 *
 * @param string $post_type The current post type.
 * @return void
 * @uses add_meta_box()
 */
function vitapro_add_service_meta_boxes($post_type) {
    add_meta_box(
        'vpa_service_details',
        __( 'Service Details', 'vitapro-appointments-fse' ),
        'vitapro_render_service_details_meta_box',
        'vpa_service',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'vitapro_add_service_meta_boxes' );

/**
 * Render the Service Details meta box.
 *
 * @param WP_Post $post The current post object.
 * @return void
 * @uses get_post_meta()
 */
function vitapro_render_service_details_meta_box( $post ) {
    wp_nonce_field( 'vitapro_service_meta_box', 'vitapro_service_meta_box_nonce' );

    $duration = get_post_meta( $post->ID, '_vpa_service_duration', true );
    $price = get_post_meta( $post->ID, '_vpa_service_price', true );
    $buffer_time = get_post_meta( $post->ID, '_vpa_service_buffer_time', true );

    ?>
    <div class="vpa-meta-row">
        <label><?php _e('Price', 'vitapro-appointments-fse'); ?></label>
        <input type="number" name="_vpa_service_price" value="<?php echo esc_attr(get_post_meta($post->ID, '_vpa_service_price', true)); ?>" step="0.01" min="0" />
    </div>
    <div class="vpa-meta-row">
        <label><?php _e('Duration (minutes)', 'vitapro-appointments-fse'); ?></label>
        <input type="number" name="_vpa_service_duration" value="<?php echo esc_attr(get_post_meta($post->ID, '_vpa_service_duration', true)); ?>" min="1" />
    </div>
    <!-- ...outros campos... -->
    <?php
}

/**
 * Save the Service meta data when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 * @return void
 * @uses update_post_meta()
 */
function vitapro_save_service_meta_data( $post_id ) {
    if ( ! isset( $_POST['vitapro_service_meta_box_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['vitapro_service_meta_box_nonce'], 'vitapro_service_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'vpa_service' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( isset( $_POST['_vpa_service_duration'] ) ) {
        update_post_meta( $post_id, '_vpa_service_duration', absint( $_POST['_vpa_service_duration'] ) );
    }

    if ( isset( $_POST['_vpa_service_price'] ) ) {
        update_post_meta( $post_id, '_vpa_service_price', floatval( $_POST['_vpa_service_price'] ) );
    }

    if ( isset( $_POST['_vpa_service_buffer_time'] ) ) {
        update_post_meta( $post_id, '_vpa_service_buffer_time', absint( $_POST['_vpa_service_buffer_time'] ) );
    }
}
add_action( 'save_post', 'vitapro_save_service_meta_data' );

// Enqueue scripts/styles para admin
add_action('admin_enqueue_scripts', function($hook) {
    global $post_type;
    if (in_array($post_type, array('vpa_service'))) {
        wp_enqueue_style('vpa-admin', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/admin.css', array(), VITAPRO_APPOINTMENTS_FSE_VERSION);
        wp_enqueue_script('vpa-admin', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/admin.js', array('jquery', 'jquery-ui-datepicker', 'jquery-ui-slider'), VITAPRO_APPOINTMENTS_FSE_VERSION, true);
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-timepicker', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/vendor/jquery-ui-timepicker-addon.js', array('jquery', 'jquery-ui-datepicker'), VITAPRO_APPOINTMENTS_FSE_VERSION, true);
        wp_enqueue_style('jquery-ui-timepicker', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/vendor/jquery-ui-timepicker-addon.css', array(), VITAPRO_APPOINTMENTS_FSE_VERSION);
    }
});