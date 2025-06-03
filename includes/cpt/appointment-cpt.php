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
    $labels = array(
        'name'                  => _x( 'Appointments', 'Post Type General Name', 'vitapro-appointments-fse' ),
        'singular_name'         => _x( 'Appointment', 'Post Type Singular Name', 'vitapro-appointments-fse' ),
        'menu_name'             => __( 'Appointments', 'vitapro-appointments-fse' ),
        'name_admin_bar'        => __( 'Appointment', 'vitapro-appointments-fse' ),
        'archives'              => __( 'Appointment Archives', 'vitapro-appointments-fse' ),
        'attributes'            => __( 'Appointment Attributes', 'vitapro-appointments-fse' ),
        'parent_item_colon'     => __( 'Parent Appointment:', 'vitapro-appointments-fse' ),
        'all_items'             => __( 'All Appointments', 'vitapro-appointments-fse' ),
        'add_new_item'          => __( 'Add New Appointment', 'vitapro-appointments-fse' ),
        'add_new'               => __( 'Add New', 'vitapro-appointments-fse' ),
        'new_item'              => __( 'New Appointment', 'vitapro-appointments-fse' ),
        'edit_item'             => __( 'Edit Appointment', 'vitapro-appointments-fse' ),
        'update_item'           => __( 'Update Appointment', 'vitapro-appointments-fse' ),
        'view_item'             => __( 'View Appointment', 'vitapro-appointments-fse' ),
        'view_items'            => __( 'View Appointments', 'vitapro-appointments-fse' ),
        'search_items'          => __( 'Search Appointment', 'vitapro-appointments-fse' ),
        'not_found'             => __( 'Not found', 'vitapro-appointments-fse' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vitapro-appointments-fse' ),
        'featured_image'        => __( 'Featured Image', 'vitapro-appointments-fse' ),
        'set_featured_image'    => __( 'Set featured image', 'vitapro-appointments-fse' ),
        'remove_featured_image' => __( 'Remove featured image', 'vitapro-appointments-fse' ),
        'use_featured_image'    => __( 'Use as featured image', 'vitapro-appointments-fse' ),
        'insert_into_item'      => __( 'Insert into appointment', 'vitapro-appointments-fse' ),
        'uploaded_to_this_item' => __( 'Uploaded to this appointment', 'vitapro-appointments-fse' ),
        'items_list'            => __( 'Appointments list', 'vitapro-appointments-fse' ),
        'items_list_navigation' => __( 'Appointments list navigation', 'vitapro-appointments-fse' ),
        'filter_items_list'     => __( 'Filter appointments list', 'vitapro-appointments-fse' ),
    );

    $args = array(
        'label'                 => __( 'Appointment', 'vitapro-appointments-fse' ),
        'description'           => __( 'Patient appointments', 'vitapro-appointments-fse' ),
        'labels'                => $labels,
        'supports'              => array( 'title' ),
        'hierarchical'          => false,
        'public'                => false,
        'show_ui'               => true,
        'show_in_menu'          => 'vitapro-appointments',
        'menu_position'         => 5,
        'show_in_admin_bar'     => false,
        'show_in_nav_menus'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => true,
        'publicly_queryable'    => false,
        'capability_type'       => 'post',
        'show_in_rest'          => false,
    );

    register_post_type( 'vpa_appointment', $args );
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
    wp_nonce_field( 'vitapro_appointment_meta_box', 'vitapro_appointment_meta_box_nonce' );

    $service_id = get_post_meta( $post->ID, '_vpa_appointment_service_id', true );
    $professional_id = get_post_meta( $post->ID, '_vpa_appointment_professional_id', true );
    $appointment_date = get_post_meta( $post->ID, '_vpa_appointment_date', true );
    $appointment_time = get_post_meta( $post->ID, '_vpa_appointment_time', true );
    $patient_name = get_post_meta( $post->ID, '_vpa_appointment_patient_name', true );
    $patient_email = get_post_meta( $post->ID, '_vpa_appointment_patient_email', true );
    $patient_phone = get_post_meta( $post->ID, '_vpa_appointment_patient_phone', true );
    $status = get_post_meta( $post->ID, '_vpa_appointment_status', true );
    $custom_fields_data = get_post_meta( $post->ID, '_vpa_appointment_custom_fields_data', true );

    $services = get_posts( array(
        'post_type'      => 'vpa_service',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    $professionals = get_posts( array(
        'post_type'      => 'vpa_professional',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
    ) );

    $statuses = array(
        'pending'   => __( 'Pending', 'vitapro-appointments-fse' ),
        'confirmed' => __( 'Confirmed', 'vitapro-appointments-fse' ),
        'completed' => __( 'Completed', 'vitapro-appointments-fse' ),
        'cancelled' => __( 'Cancelled', 'vitapro-appointments-fse' ),
        'no_show'   => __( 'No Show', 'vitapro-appointments-fse' ),
    );

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="vpa_appointment_service"><?php _e( 'Service', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <select id="vpa_appointment_service" name="vpa_appointment_service" class="regular-text">
                    <option value=""><?php _e( 'Select Service', 'vitapro-appointments-fse' ); ?></option>
                    <?php foreach ( $services as $service ) : ?>
                        <option value="<?php echo esc_attr( $service->ID ); ?>" <?php selected( $service_id, $service->ID ); ?>>
                            <?php echo esc_html( $service->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_professional"><?php _e( 'Professional', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <select id="vpa_appointment_professional" name="vpa_appointment_professional" class="regular-text">
                    <option value=""><?php _e( 'Select Professional', 'vitapro-appointments-fse' ); ?></option>
                    <?php foreach ( $professionals as $professional ) : ?>
                        <option value="<?php echo esc_attr( $professional->ID ); ?>" <?php selected( $professional_id, $professional->ID ); ?>>
                            <?php echo esc_html( $professional->post_title ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_date"><?php _e( 'Date', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <input type="date" id="vpa_appointment_date" name="vpa_appointment_date" value="<?php echo esc_attr( $appointment_date ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_time"><?php _e( 'Time', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <input type="time" id="vpa_appointment_time" name="vpa_appointment_time" value="<?php echo esc_attr( $appointment_time ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_status"><?php _e( 'Status', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <select id="vpa_appointment_status" name="vpa_appointment_status" class="regular-text">
                    <?php foreach ( $statuses as $status_key => $status_label ) : ?>
                        <option value="<?php echo esc_attr( $status_key ); ?>" <?php selected( $status, $status_key ); ?>>
                            <?php echo esc_html( $status_label ); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_patient_name"><?php _e( 'Patient Name', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <input type="text" id="vpa_appointment_patient_name" name="vpa_appointment_patient_name" value="<?php echo esc_attr( $patient_name ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_patient_email"><?php _e( 'Patient Email', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <input type="email" id="vpa_appointment_patient_email" name="vpa_appointment_patient_email" value="<?php echo esc_attr( $patient_email ); ?>" class="regular-text" />
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_appointment_patient_phone"><?php _e( 'Patient Phone', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <input type="tel" id="vpa_appointment_patient_phone" name="vpa_appointment_patient_phone" value="<?php echo esc_attr( $patient_phone ); ?>" class="regular-text" />
            </td>
        </tr>
        <?php if ( ! empty( $custom_fields_data ) ) : ?>
            <?php
            $defined_custom_fields = vitapro_appointments_get_option( 'custom_fields', array() );
            foreach ( $custom_fields_data as $field_id => $field_value ) :
                $field_settings = isset( $defined_custom_fields[ $field_id ] ) ? $defined_custom_fields[ $field_id ] : null;
                if ( $field_settings ) :
            ?>
                <tr>
                    <th scope="row"><?php echo esc_html( $field_settings['label'] ); ?></th>
                    <td>
                        <?php if ( $field_settings['type'] === 'textarea' ) : ?>
                            <?php echo nl2br( esc_html( $field_value ) ); ?>
                        <?php else : ?>
                            <?php echo esc_html( $field_value ); ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php
                endif;
            endforeach;
            ?>
        <?php endif; ?>
    </table>
    <?php
}

/**
 * Save the Appointment meta data when the post is saved.
 *
 * @param int $post_id The ID of the post being saved.
 * @return void
 * @uses update_post_meta()
 */
function vitapro_save_appointment_meta_data( $post_id, $post ) {
    if ( ! isset( $_POST['vitapro_appointment_meta_box_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['vitapro_appointment_meta_box_nonce'], 'vitapro_appointment_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'vpa_appointment' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    $old_status = get_post_meta( $post_id, '_vpa_appointment_status', true );

    if ( isset( $_POST['vpa_appointment_service'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_service_id', absint( $_POST['vpa_appointment_service'] ) );
    }

    if ( isset( $_POST['vpa_appointment_professional'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_professional_id', absint( $_POST['vpa_appointment_professional'] ) );
    }

    if ( isset( $_POST['vpa_appointment_date'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_date', sanitize_text_field( $_POST['vpa_appointment_date'] ) );
    }

    if ( isset( $_POST['vpa_appointment_time'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_time', sanitize_text_field( $_POST['vpa_appointment_time'] ) );
    }

    if ( isset( $_POST['vpa_appointment_status'] ) ) {
        $new_status = sanitize_text_field( $_POST['vpa_appointment_status'] );
        update_post_meta( $post_id, '_vpa_appointment_status', $new_status );

        // Send status change email if status changed
        if ( $old_status !== $new_status ) {
            vitapro_send_status_change_email( $post_id, $new_status );
        }
    }

    if ( isset( $_POST['vpa_appointment_patient_name'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_patient_name', sanitize_text_field( $_POST['vpa_appointment_patient_name'] ) );
    }

    if ( isset( $_POST['vpa_appointment_patient_email'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_patient_email', sanitize_email( $_POST['vpa_appointment_patient_email'] ) );
    }

    if ( isset( $_POST['vpa_appointment_patient_phone'] ) ) {
        update_post_meta( $post_id, '_vpa_appointment_patient_phone', sanitize_text_field( $_POST['vpa_appointment_patient_phone'] ) );
    }

    // Update appointment title
    $service_title = '';
    $patient_name = get_post_meta( $post_id, '_vpa_appointment_patient_name', true );
    $appointment_date = get_post_meta( $post_id, '_vpa_appointment_date', true );
    $service_id = get_post_meta( $post_id, '_vpa_appointment_service_id', true );

    if ( $service_id ) {
        $service = get_post( $service_id );
        if ( $service ) {
            $service_title = $service->post_title;
        }
    }

    $new_title = sprintf( '%s - %s - %s', $patient_name, $service_title, $appointment_date );

    // Remove action to prevent infinite loop
    remove_action( 'save_post', 'vitapro_save_appointment_meta_data', 10, 2 );

    wp_update_post( array(
        'ID'         => $post_id,
        'post_title' => $new_title,
    ) );

    // Re-add action
    add_action( 'save_post', 'vitapro_save_appointment_meta_data', 10, 2 );
}
add_action( 'save_post', 'vitapro_save_appointment_meta_data', 10, 2 );

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
    switch ( $column ) {
        case 'service':
            $service_id = get_post_meta( $post_id, '_vpa_appointment_service_id', true );
            if ( $service_id ) {
                $service = get_post( $service_id );
                if ( $service ) {
                    echo esc_html( $service->post_title );
                }
            }
            break;

        case 'professional':
            $professional_id = get_post_meta( $post_id, '_vpa_appointment_professional_id', true );
            if ( $professional_id ) {
                $professional = get_post( $professional_id );
                if ( $professional ) {
                    echo esc_html( $professional->post_title );
                }
            }
            break;

        case 'patient':
            $patient_name = get_post_meta( $post_id, '_vpa_appointment_patient_name', true );
            $patient_email = get_post_meta( $post_id, '_vpa_appointment_patient_email', true );
            echo esc_html( $patient_name );
            if ( $patient_email ) {
                echo '<br><small>' . esc_html( $patient_email ) . '</small>';
            }
            break;

        case 'date_time':
            $appointment_date = get_post_meta( $post_id, '_vpa_appointment_date', true );
            $appointment_time = get_post_meta( $post_id, '_vpa_appointment_time', true );
            if ( $appointment_date ) {
                echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $appointment_date ) ) );
            }
            if ( $appointment_time ) {
                echo '<br>' . esc_html( date_i18n( get_option( 'time_format' ), strtotime( $appointment_time ) ) );
            }
            break;

        case 'status':
            $status = get_post_meta( $post_id, '_vpa_appointment_status', true );
            $statuses = array(
                'pending'   => __( 'Pending', 'vitapro-appointments-fse' ),
                'confirmed' => __( 'Confirmed', 'vitapro-appointments-fse' ),
                'completed' => __( 'Completed', 'vitapro-appointments-fse' ),
                'cancelled' => __( 'Cancelled', 'vitapro-appointments-fse' ),
                'no_show'   => __( 'No Show', 'vitapro-appointments-fse' ),
            );
            $status_label = isset( $statuses[ $status ] ) ? $statuses[ $status ] : $status;
            $status_class = 'vpa-status-' . $status;
            echo '<span class="' . esc_attr( $status_class ) . '">' . esc_html( $status_label ) . '</span>';
            break;
    }
}
add_action( 'manage_vpa_appointment_posts_custom_column', 'vitapro_render_appointment_columns', 10, 2 );