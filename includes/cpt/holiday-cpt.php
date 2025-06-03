<?php
/**
 * Registers the Holiday Custom Post Type.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Register Holiday Custom Post Type.
 */
function vitapro_appointments_register_holiday_cpt() {
    $labels = array(
        'name'                  => _x( 'Holidays', 'Post Type General Name', 'vitapro-appointments-fse' ),
        'singular_name'         => _x( 'Holiday', 'Post Type Singular Name', 'vitapro-appointments-fse' ),
        'menu_name'             => __( 'Holidays', 'vitapro-appointments-fse' ),
        'name_admin_bar'        => __( 'Holiday', 'vitapro-appointments-fse' ),
        'archives'              => __( 'Holiday Archives', 'vitapro-appointments-fse' ),
        'attributes'            => __( 'Holiday Attributes', 'vitapro-appointments-fse' ),
        'parent_item_colon'     => __( 'Parent Holiday:', 'vitapro-appointments-fse' ),
        'all_items'             => __( 'All Holidays', 'vitapro-appointments-fse' ),
        'add_new_item'          => __( 'Add New Holiday', 'vitapro-appointments-fse' ),
        'add_new'               => __( 'Add New', 'vitapro-appointments-fse' ),
        'new_item'              => __( 'New Holiday', 'vitapro-appointments-fse' ),
        'edit_item'             => __( 'Edit Holiday', 'vitapro-appointments-fse' ),
        'update_item'           => __( 'Update Holiday', 'vitapro-appointments-fse
        'update_item'           => __( 'Update Holiday', 'vitapro-appointments-fse' ),
        'view_item'             => __( 'View Holiday', 'vitapro-appointments-fse' ),
        'view_items'            => __( 'View Holidays', 'vitapro-appointments-fse' ),
        'search_items'          => __( 'Search Holiday', 'vitapro-appointments-fse' ),
        'not_found'             => __( 'Not found', 'vitapro-appointments-fse' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vitapro-appointments-fse' ),
        'featured_image'        => __( 'Featured Image', 'vitapro-appointments-fse' ),
        'set_featured_image'    => __( 'Set featured image', 'vitapro-appointments-fse' ),
        'remove_featured_image' => __( 'Remove featured image', 'vitapro-appointments-fse' ),
        'use_featured_image'    => __( 'Use as featured image', 'vitapro-appointments-fse' ),
        'insert_into_item'      => __( 'Insert into holiday', 'vitapro-appointments-fse' ),
        'uploaded_to_this_item' => __( 'Uploaded to this holiday', 'vitapro-appointments-fse' ),
        'items_list'            => __( 'Holidays list', 'vitapro-appointments-fse' ),
        'items_list_navigation' => __( 'Holidays list navigation', 'vitapro-appointments-fse' ),
        'filter_items_list'     => __( 'Filter holidays list', 'vitapro-appointments-fse' ),
    );

    $args = array(
        'label'                 => __( 'Holiday', 'vitapro-appointments-fse' ),
        'description'           => __( 'Clinic holidays and closures', 'vitapro-appointments-fse' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor' ),
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

    register_post_type( 'vpa_holiday', $args );
}
add_action( 'init', 'vitapro_appointments_register_holiday_cpt', 0 );

/**
 * Add meta boxes for Holiday CPT.
 */
function vitapro_add_holiday_meta_boxes() {
    add_meta_box(
        'vpa_holiday_date',
        __( 'Holiday Date', 'vitapro-appointments-fse' ),
        'vitapro_render_holiday_date_meta_box',
        'vpa_holiday',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'vitapro_add_holiday_meta_boxes' );

/**
 * Render Holiday Date meta box.
 */
function vitapro_render_holiday_date_meta_box( $post ) {
    wp_nonce_field( 'vitapro_holiday_meta_box', 'vitapro_holiday_meta_box_nonce' );

    $holiday_date = get_post_meta( $post->ID, '_vpa_holiday_date', true );
    $is_recurring = get_post_meta( $post->ID, '_vpa_holiday_recurring', true );

    ?>
    <table class="form-table">
        <tr>
            <th scope="row">
                <label for="vpa_holiday_date"><?php _e( 'Holiday Date', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <input type="date" id="vpa_holiday_date" name="vpa_holiday_date" value="<?php echo esc_attr( $holiday_date ); ?>" class="regular-text vpa-datepicker-field" />
                <p class="description"><?php _e( 'Select the date for this holiday.', 'vitapro-appointments-fse' ); ?></p>
            </td>
        </tr>
        <tr>
            <th scope="row">
                <label for="vpa_holiday_recurring"><?php _e( 'Recurring Holiday', 'vitapro-appointments-fse' ); ?></label>
            </th>
            <td>
                <label>
                    <input type="checkbox" id="vpa_holiday_recurring" name="vpa_holiday_recurring" value="1" <?php checked( $is_recurring ); ?> />
                    <?php _e( 'This holiday occurs every year on the same date', 'vitapro-appointments-fse' ); ?>
                </label>
            </td>
        </tr>
    </table>
    <?php
}

/**
 * Save Holiday meta data.
 */
function vitapro_save_holiday_meta_data( $post_id ) {
    if ( ! isset( $_POST['vitapro_holiday_meta_box_nonce'] ) ) {
        return;
    }

    if ( ! wp_verify_nonce( $_POST['vitapro_holiday_meta_box_nonce'], 'vitapro_holiday_meta_box' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( isset( $_POST['post_type'] ) && 'vpa_holiday' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    if ( isset( $_POST['vpa_holiday_date'] ) ) {
        update_post_meta( $post_id, '_vpa_holiday_date', sanitize_text_field( $_POST['vpa_holiday_date'] ) );
    }

    if ( isset( $_POST['vpa_holiday_recurring'] ) ) {
        update_post_meta( $post_id, '_vpa_holiday_recurring', true );
    } else {
        update_post_meta( $post_id, '_vpa_holiday_recurring', false );
    }
}
add_action( 'save_post', 'vitapro_save_holiday_meta_data' );

/**
 * Customize holiday list columns.
 */
function vitapro_set_holiday_columns( $columns ) {
    $columns = array(
        'cb'        => $columns['cb'],
        'title'     => __( 'Holiday Name', 'vitapro-appointments-fse' ),
        'date'      => __( 'Holiday Date', 'vitapro-appointments-fse' ),
        'recurring' => __( 'Recurring', 'vitapro-appointments-fse' ),
        'created'   => __( 'Created', 'vitapro-appointments-fse' ),
    );
    return $columns;
}
add_filter( 'manage_vpa_holiday_posts_columns', 'vitapro_set_holiday_columns' );

/**
 * Render custom holiday list columns.
 */
function vitapro_render_holiday_columns( $column, $post_id ) {
    switch ( $column ) {
        case 'date':
            $holiday_date = get_post_meta( $post_id, '_vpa_holiday_date', true );
            if ( $holiday_date ) {
                echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $holiday_date ) ) );
            }
            break;

        case 'recurring':
            $is_recurring = get_post_meta( $post_id, '_vpa_holiday_recurring', true );
            echo $is_recurring ? __( 'Yes', 'vitapro-appointments-fse' ) : __( 'No', 'vitapro-appointments-fse' );
            break;

        case 'created':
            echo get_the_date( get_option( 'date_format' ), $post_id );
            break;
    }
}
add_action( 'manage_vpa_holiday_posts_custom_column', 'vitapro_render_holiday_columns', 10, 2 );