<?php
/**
 * Registers the Professional Custom Post Type.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register the Professional custom post type.
 *
 * @return void
 * @uses register_post_type()
 */
function vitapro_appointments_register_professional_cpt() {
    $labels = array(
        'name'                  => _x( 'Professionals', 'Post Type General Name', 'vitapro-appointments-fse' ),
        'singular_name'         => _x( 'Professional', 'Post Type Singular Name', 'vitapro-appointments-fse' ),
        'menu_name'             => __( 'Professionals', 'vitapro-appointments-fse' ),
        'name_admin_bar'        => __( 'Professional', 'vitapro-appointments-fse' ),
        'archives'              => __( 'Professional Archives', 'vitapro-appointments-fse' ),
        'attributes'            => __( 'Professional Attributes', 'vitapro-appointments-fse' ),
        'parent_item_colon'     => __( 'Parent Professional:', 'vitapro-appointments-fse' ),
        'all_items'             => __( 'All Professionals', 'vitapro-appointments-fse' ),
        'add_new_item'          => __( 'Add New Professional', 'vitapro-appointments-fse' ),
        'add_new'               => __( 'Add New', 'vitapro-appointments-fse' ),
        'new_item'              => __( 'New Professional', 'vitapro-appointments-fse' ),
        'edit_item'             => __( 'Edit Professional', 'vitapro-appointments-fse' ),
        'update_item'           => __( 'Update Professional', 'vitapro-appointments-fse' ),
        'view_item'             => __( 'View Professional', 'vitapro-appointments-fse' ),
        'view_items'            => __( 'View Professionals', 'vitapro-appointments-fse' ),
        'search_items'          => __( 'Search Professional', 'vitapro-appointments-fse' ),
        'not_found'             => __( 'Not found', 'vitapro-appointments-fse' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vitapro-appointments-fse' ),
        'featured_image'        => __( 'Professional Photo', 'vitapro-appointments-fse' ),
        'set_featured_image'    => __( 'Set professional photo', 'vitapro-appointments-fse' ),
        'remove_featured_image' => __( 'Remove professional photo', 'vitapro-appointments-fse' ),
        'use_featured_image'    => __( 'Use as professional photo', 'vitapro-appointments-fse' ),
        'insert_into_item'      => __( 'Insert into professional', 'vitapro-appointments-fse' ),
        'uploaded_to_this_item' => __( 'Uploaded to this professional', 'vitapro-appointments-fse' ),
        'items_list'            => __( 'Professionals list', 'vitapro-appointments-fse' ),
        'items_list_navigation' => __( 'Professionals list navigation', 'vitapro-appointments-fse' ),
        'filter_items_list'     => __( 'Filter professionals list', 'vitapro-appointments-fse' ),
    );

    $args = array(
        'label'                 => __( 'Professional', 'vitapro-appointments-fse' ),
        'description'           => __( 'Healthcare professionals', 'vitapro-appointments-fse' ),
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

    register_post_type( 'vpa_professional', $args );
}
add_action( 'init', 'vitapro_appointments_register_professional_cpt', 0 );

/**
 * Add meta boxes for the Professional CPT.
 *
 * @param string $post_type The current post type.
 * @return void
 * @uses add_meta_box()
 */
function vitapro_add_professional_meta_boxes($post_type) {
    add_meta_box(
        'vpa_professional_details',
        __('Professional Details', 'vitapro-appointments-fse'),
        'vitapro_render_professional_details_meta_box',
        'vpa_professional',
        'normal',
        'high'
    );
    add_meta_box(
        'vpa_professional_schedule',
        __('Schedule', 'vitapro-appointments-fse'),
        'vitapro_render_professional_schedule_meta_box',
        'vpa_professional',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'vitapro_add_professional_meta_boxes');

function vitapro_render_professional_details_meta_box($post) {
    ?>
    <div class="vpa-meta-row">
        <label><?php _e('Title', 'vitapro-appointments-fse'); ?></label>
        <input type="text" name="_vpa_professional_title" value="<?php echo esc_attr(get_post_meta($post->ID, '_vpa_professional_title', true)); ?>" />
    </div>
    <?php
}

/**
 * Render the Professional Schedule meta box.
 *
 * @param WP_Post $post The current post object.
 * @return void
 * @uses get_post_meta()
 */
function vitapro_render_professional_schedule_meta_box( $post ) {
    wp_nonce_field( 'vitapro_professional_meta_box', 'vitapro_professional_meta_box_nonce' );

    $schedule = get_post_meta( $post->ID, '_vpa_professional_schedule', true );
    if ( ! is_array( $schedule ) ) {
        $schedule = array();
    }

    $days = array(
        'monday'    => __( 'Monday', 'vitapro-appointments-fse' ),
        'tuesday'   => __( 'Tuesday', 'vitapro-appointments-fse' ),
        'wednesday' => __( 'Wednesday', 'vitapro-appointments-fse' ),
        'thursday'  => __( 'Thursday', 'vitapro-appointments-fse' ),
        'friday'    => __( 'Friday', 'vitapro-appointments-fse' ),
        'saturday'  => __( 'Saturday', 'vitapro-appointments-fse' ),
        'sunday'    => __( 'Sunday', 'vitapro-appointments-fse' ),
    );

    ?>
    <div class="vpa-meta-row">
        <label><?php _e('Monday Start', 'vitapro-appointments-fse'); ?></label>
        <input type="time" name="_vpa_professional_schedule[monday][start]" value="<?php echo esc_attr( /* ... */ ); ?>" />
        <!-- ... -->
    </div>
    <!-- ...outros dias... -->
    <?php
}

/**
 * Render the Professional Services meta box.
 *
 * @param WP_Post $post The current post object.
 * @return void
 * @uses get_post_meta()
 */
function vitapro_render_professional_services_meta_box( $post ) {
    $assigned_services = get_post_meta( $post->ID, '_vpa_professional_services', true );
    if ( ! is_array( $assigned_services ) ) {
        $assigned_services = array();
    }

    $services = get_posts( array(
        'post_type'      => 'vpa_service',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    ?>
    <p><?php _e( 'Select the services this professional can provide:', 'vitapro-appointments-fse' ); ?></p>
    <?php if ( ! empty( $services ) ) : ?>
        <?php foreach ( $services as $service ) : ?>
            <label style="display: block; margin-bottom: 5px;">
                <input type="checkbox" name="vpa_professional_services[]" value="<?php echo esc_attr( $service->ID ); ?>" <?php checked( in_array( $service->ID, $assigned_services ) ); ?> />
                <?php echo esc_html( $service->post_title ); ?>
            </label>
        <?php endforeach; ?>
    <?php else : ?>
        <p><?php _e( 'No services found. Please create services first.', 'vitapro-appointments-fse' ); ?></p>
    <?php endif; ?>
    <?php
}

/**
 * Render the Professional Custom Days Off meta box.
 *
 * @param WP_Post $post The current post object.
 * @return void
 * @uses get_post_meta()
 */
function vitapro_render_professional_custom_days_off_meta_box( $post ) {
    $custom_days_off = get_post_meta( $post->ID, '_vpa_professional_custom_days_off', true );
    if ( ! is_array( $custom_days_off ) ) {
        $custom_days_off = array();
    }

    ?>
    <div id="vpa-custom-days-off-container">
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th><?php _e( 'Date', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php _e( 'Reason', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php _e( 'Actions', 'vitapro-appointments-fse' ); ?></th>
                </tr>
            </thead>
            <tbody id="vpa-custom-days-off-list">
                <?php if ( ! empty( $custom_days_off ) ) : ?>
                    <?php foreach ( $custom_days_off as $index => $day_off ) : ?>
                        <tr>
                            <td>
                                <input type="date" name="vpa_custom_days_off[<?php echo esc_attr( $index ); ?>][date]" value="<?php echo esc_attr( $day_off['date'] ); ?>" class="vpa-datepicker-field" />
                            </td>
                            <td>
                                <input type="text" name="vpa_custom_days_off[<?php echo esc_attr( $index ); ?>][reason]" value="<?php echo esc_attr( $day_off['reason'] ); ?>" class="regular-text" />
                            </td>
                            <td>
                                <button type="button" class="button button-link-delete vpa-remove-day-off"><?php _e( 'Remove', 'vitapro-appointments-fse' ); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <p>
            <button type="button" class="button" id="vpa-add-day-off"><?php _e( 'Add Day Off', 'vitapro-appointments-fse' ); ?></button>
        </p>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var dayOffIndex = <?php echo count( $custom_days_off ); ?>;

            $('#vpa-add-day-off').on('click', function() {
                var newRow = '<tr>' +
                    '<td><input type="date" name="vpa_custom_days_off[' + dayOffIndex + '][date]" value="" class="vpa-datepicker-field" /></td>' +
                    '<td><input type="text" name="vpa_custom_days_off[' + dayOffIndex + '][reason]" value="" class="regular-text" /></td>' +
                    '<td><button type="button" class="button button-link-delete vpa-remove-day-off"><?php _e( 'Remove', 'vitapro-appointments-fse' ); ?></button></td>' +
                    '</tr>';
                $('#vpa-custom-days-off-list').append(newRow);
                dayOffIndex++;
            });

            $(document).on('click', '.vpa-remove-day-off', function() {
                $(this).closest('tr').remove();
            });
        });
    </script>
    <?php
}

/**
 * Save the Professional meta data when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 * @return void
 * @uses update_post_meta()
 */
function vitapro_save_professional_meta_data( $post_id ) {
    if ( ! isset( $_POST['vitapro_professional_meta_box_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['vitapro_professional_meta_box_nonce'], 'vitapro_professional_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'vpa_professional' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Save schedule
    if ( isset( $_POST['vpa_schedule'] ) ) {
        $schedule = array();
        foreach ( $_POST['vpa_schedule'] as $day => $day_data ) {
            $schedule[ $day ] = array(
                'working'     => isset( $day_data['working'] ) ? true : false,
                'start'       => sanitize_text_field( $day_data['start'] ),
                'end'         => sanitize_text_field( $day_data['end'] ),
                'break_start' => sanitize_text_field( $day_data['break_start'] ),
                'break_end'   => sanitize_text_field( $day_data['break_end'] ),
            );
        }
        update_post_meta( $post_id, '_vpa_professional_schedule', $schedule );
    }

    // Save services
    if ( isset( $_POST['vpa_professional_services'] ) ) {
        $services = array_map( 'absint', $_POST['vpa_professional_services'] );
        update_post_meta( $post_id, '_vpa_professional_services', $services );
    } else {
        update_post_meta( $post_id, '_vpa_professional_services', array() );
    }

    // Save custom days off
    if ( isset( $_POST['vpa_custom_days_off'] ) ) {
        $custom_days_off = array();
        foreach ( $_POST['vpa_custom_days_off'] as $day_off ) {
            if ( ! empty( $day_off['date'] ) ) {
                $custom_days_off[] = array(
                    'date'   => sanitize_text_field( $day_off['date'] ),
                    'reason' => sanitize_text_field( $day_off['reason'] ),
                );
            }
        }
        update_post_meta( $post_id, '_vpa_professional_custom_days_off', $custom_days_off );
    }
}
add_action( 'save_post', 'vitapro_save_professional_meta_data' );

/**
 * Enqueue admin scripts and styles.
 *
 * @param string $hook The current admin page hook.
 * @return void
 * @uses wp_enqueue_script(), wp_enqueue_style()
 */
function vitapro_enqueue_admin_scripts( $hook ) {
    global $post_type;
    if ( in_array( $post_type, array( 'vpa_professional' ) ) ) {
        wp_enqueue_style( 'vpa-admin', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/admin.css', array(), VITAPRO_APPOINTMENTS_FSE_VERSION );
        wp_enqueue_script( 'vpa-admin', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/admin.js', array( 'jquery', 'jquery-ui-datepicker', 'jquery-ui-slider' ), VITAPRO_APPOINTMENTS_FSE_VERSION, true );
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'jquery-ui-timepicker', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/vendor/jquery-ui-timepicker-addon.js', array( 'jquery', 'jquery-ui-datepicker' ), VITAPRO_APPOINTMENTS_FSE_VERSION, true );
        wp_enqueue_style( 'jquery-ui-timepicker', VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/vendor/jquery-ui-timepicker-addon.css', array(), VITAPRO_APPOINTMENTS_FSE_VERSION );
    }
}
add_action( 'admin_enqueue_scripts', 'vitapro_enqueue_admin_scripts' );