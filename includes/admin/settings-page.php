<?php
/**
 * Adds the plugin settings page to the WordPress admin.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add plugin menu page.
 */
function vitapro_appointments_add_admin_menu() {
    add_menu_page(
        __( 'VitaPro Appointments', 'vitapro-appointments-fse' ),
        __( 'VitaPro Appointments', 'vitapro-appointments-fse' ),
        'manage_options',
        'vitapro-appointments',
        'vitapro_appointments_all_appointments_page_render',
        'dashicons-calendar-alt',
        30
    );

    add_submenu_page(
        'vitapro-appointments',
        __( 'All Appointments', 'vitapro-appointments-fse' ),
        __( 'All Appointments', 'vitapro-appointments-fse' ),
        'manage_options',
        'vitapro-appointments',
        'vitapro_appointments_all_appointments_page_render'
    );

    add_submenu_page(
        'vitapro-appointments',
        __( 'Settings', 'vitapro-appointments-fse' ),
        __( 'Settings', 'vitapro-appointments-fse' ),
        'manage_options',
        'vitapro-appointments-settings',
        'vitapro_appointments_settings_page_render'
    );
}
add_action( 'admin_menu', 'vitapro_appointments_add_admin_menu' );

/**
 * Register plugin settings.
 */
function vitapro_appointments_register_settings() {
    register_setting( 'vitapro_appointments_options_group', 'vitapro_appointments_settings', 'vitapro_appointments_settings_sanitize' );

    // General Settings Section
    add_settings_section(
        'vitapro_appointments_general_section',
        __( 'General Booking Settings', 'vitapro-appointments-fse' ),
        'vitapro_appointments_general_section_callback',
        'vitapro_appointments_settings_page'
    );

    add_settings_field(
        'default_opening_time',
        __( 'Default Opening Time', 'vitapro-appointments-fse' ),
        'vitapro_setting_time_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_general_section',
        array( 'id' => 'default_opening_time', 'default' => '09:00' )
    );

    add_settings_field(
        'default_closing_time',
        __( 'Default Closing Time', 'vitapro-appointments-fse' ),
        'vitapro_setting_time_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_general_section',
        array( 'id' => 'default_closing_time', 'default' => '17:00' )
    );

    add_settings_field(
        'time_slot_interval',
        __( 'Time Slot Interval', 'vitapro-appointments-fse' ),
        'vitapro_setting_time_slot_interval_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_general_section'
    );

    add_settings_field(
        'min_advance_notice',
        __( 'Minimum Advance Notice (hours)', 'vitapro-appointments-fse' ),
        'vitapro_setting_min_max_notice_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_general_section',
        array( 'id' => 'min_advance_notice', 'default' => 2 )
    );

    add_settings_field(
        'max_advance_notice',
        __( 'Maximum Advance Notice (days)', 'vitapro-appointments-fse' ),
        'vitapro_setting_min_max_notice_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_general_section',
        array( 'id' => 'max_advance_notice', 'default' => 90 )
    );

    add_settings_field(
        'manual_approval',
        __( 'Manual Booking Approval', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_general_section',
        array(
            'id' => 'manual_approval',
            'label' => __( 'Require manual approval for all new bookings.', 'vitapro-appointments-fse' )
        )
    );

    // Patient Interaction Section
    add_settings_section(
        'vitapro_appointments_patient_interaction_section',
        __( 'Patient Interactions', 'vitapro-appointments-fse' ),
        'vitapro_appointments_patient_interaction_section_callback',
        'vitapro_appointments_settings_page'
    );

    add_settings_field(
        'allow_patient_cancellation',
        __( 'Allow Patient Cancellation', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_patient_interaction_section',
        array(
            'id' => 'allow_patient_cancellation',
            'label' => __( 'Allow patients to cancel their own appointments.', 'vitapro-appointments-fse' )
        )
    );

    add_settings_field(
        'patient_cancellation_buffer_hours',
        __( 'Patient Cancellation Buffer (hours)', 'vitapro-appointments-fse' ),
        'vitapro_setting_number_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_patient_interaction_section',
        array(
            'id' => 'patient_cancellation_buffer_hours',
            'default' => 48,
            'desc' => __( 'Minimum hours before the appointment that a patient can cancel.', 'vitapro-appointments-fse' )
        )
    );

    // Email Settings Section
    add_settings_section(
        'vitapro_appointments_email_section',
        __( 'Email Notifications', 'vitapro-appointments-fse' ),
        'vitapro_appointments_email_section_callback',
        'vitapro_appointments_settings_page'
    );

    add_settings_field(
        'email_from_name',
        __( 'From Name', 'vitapro-appointments-fse' ),
        'vitapro_setting_text_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array( 'id' => 'email_from_name', 'default' => get_bloginfo( 'name' ) )
    );

    add_settings_field(
        'email_from_address',
        __( 'From Email Address', 'vitapro-appointments-fse' ),
        'vitapro_setting_email_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array( 'id' => 'email_from_address', 'default' => get_option( 'admin_email' ) )
    );

    add_settings_field(
        'email_admin_new_booking',
        __( 'Admin Notification Email', 'vitapro-appointments-fse' ),
        'vitapro_setting_email_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array( 'id' => 'email_admin_new_booking', 'default' => get_option( 'admin_email' ) )
    );

    add_settings_field(
        'enable_patient_confirmation',
        __( 'Patient Confirmation Emails', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array(
            'id' => 'enable_patient_confirmation',
            'label' => __( 'Send confirmation emails to patients.', 'vitapro-appointments-fse' )
        )
    );

    add_settings_field(
        'enable_admin_notification',
        __( 'Admin Notification Emails', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array(
            'id' => 'enable_admin_notification',
            'label' => __( 'Send notification emails to admin for new bookings.', 'vitapro-appointments-fse' )
        )
    );

    add_settings_field(
        'enable_reminders',
        __( 'Appointment Reminders', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array(
            'id' => 'enable_reminders',
            'label' => __( 'Send reminder emails to patients before appointments.', 'vitapro-appointments-fse' )
        )
    );

    add_settings_field(
        'reminder_lead_time_hours',
        __( 'Reminder Lead Time (hours)', 'vitapro-appointments-fse' ),
        'vitapro_setting_number_render',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_email_section',
        array(
            'id' => 'reminder_lead_time_hours',
            'default' => 24,
            'desc' => __( 'How many hours before the appointment should the reminder be sent?', 'vitapro-appointments-fse' )
        )
    );

    // Custom Fields Section
    add_settings_section(
        'vitapro_appointments_custom_fields_section',
        __( 'Custom Booking Fields', 'vitapro-appointments-fse' ),
        'vitapro_appointments_custom_fields_section_callback',
        'vitapro_appointments_settings_page'
    );

    add_settings_field(
        'custom_fields_ui',
        __( 'Manage Custom Fields', 'vitapro-appointments-fse' ),
        'vitapro_render_custom_fields_ui',
        'vitapro_appointments_settings_page',
        'vitapro_appointments_custom_fields_section'
    );
}
add_action( 'admin_init', 'vitapro_appointments_register_settings' );

/**
 * Section callbacks.
 */
function vitapro_appointments_general_section_callback() {
    echo '<p>' . esc_html__( 'Configure general booking settings for your clinic.', 'vitapro-appointments-fse' ) . '</p>';
}

function vitapro_appointments_patient_interaction_section_callback() {
    echo '<p>' . esc_html__( 'Settings related to patient interactions and self-service options.', 'vitapro-appointments-fse' ) . '</p>';
}

function vitapro_appointments_email_section_callback() {
    echo '<p>' . esc_html__( 'Configure email notifications and reminders.', 'vitapro-appointments-fse' ) . '</p>';
}

function vitapro_appointments_custom_fields_section_callback() {
    echo '<p>' . esc_html__( 'Define additional fields to display on the booking form.', 'vitapro-appointments-fse' ) . '</p>';
    echo '<p><em>' . esc_html__( 'Important: Field IDs should be unique, lowercase, and use underscores instead of spaces (e.g., reason_for_visit). Once a field is used and has data, changing its ID can lead to data loss for that field in existing appointments.', 'vitapro-appointments-fse' ) . '</em></p>';
}

/**
 * Render UI for managing custom fields.
 */
function vitapro_render_custom_fields_ui() {
    $custom_fields = vitapro_appointments_get_option( 'custom_fields', array() );
    $field_types = array(
        'text'     => __( 'Text Input', 'vitapro-appointments-fse' ),
        'textarea' => __( 'Text Area', 'vitapro-appointments-fse' ),
        'select'   => __( 'Dropdown (Select)', 'vitapro-appointments-fse' ),
    );
    ?>
    <div id="vpa-custom-fields-container">
        <table class="wp-list-table widefat striped" id="vpa-custom-fields-table">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Field Label', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php esc_html_e( 'Field ID / Name', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php esc_html_e( 'Field Type', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php esc_html_e( 'Required?', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php esc_html_e( 'Options (for Dropdown)', 'vitapro-appointments-fse' ); ?></th>
                    <th><?php esc_html_e( 'Actions', 'vitapro-appointments-fse' ); ?></th>
                </tr>
            </thead>
            <tbody id="vpa-custom-fields-list">
                <?php if ( ! empty( $custom_fields ) ) : ?>
                    <?php foreach ( $custom_fields as $field_id => $field ) : ?>
                        <tr data-field-id="<?php echo esc_attr( $field_id ); ?>">
                            <td><input type="text" name="vitapro_appointments_settings[custom_fields][<?php echo esc_attr( $field_id ); ?>][label]" value="<?php echo esc_attr( $field['label'] ?? '' ); ?>" class="regular-text vpa-custom-field-label" /></td>
                            <td>
                                <input type="text" value="<?php echo esc_attr( $field_id ); ?>" class="regular-text vpa-custom-field-id-display" readonly="readonly" />
                                <input type="hidden" name="vitapro_appointments_settings[custom_fields][<?php echo esc_attr( $field_id ); ?>][id_hidden]" value="<?php echo esc_attr( $field_id ); ?>" />
                            </td>
                            <td>
                                <select name="vitapro_appointments_settings[custom_fields][<?php echo esc_attr( $field_id ); ?>][type]" class="vpa-custom-field-type">
                                    <?php foreach ( $field_types as $type_val => $type_label ) : ?>
                                        <option value="<?php echo esc_attr( $type_val ); ?>" <?php selected( $field['type'] ?? 'text', $type_val ); ?>><?php echo esc_html( $type_label ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="checkbox" name="vitapro_appointments_settings[custom_fields][<?php echo esc_attr( $field_id ); ?>][required]" value="1" <?php checked( ! empty( $field['required'] ) ); ?> class="vpa-custom-field-required" /></td>
                            <td class="vpa-custom-field-options-cell <?php echo ( ( $field['type'] ?? 'text' ) === 'select' ) ? '' : 'vpa-hidden'; ?>">
                                <textarea name="vitapro_appointments_settings[custom_fields][<?php echo esc_attr( $field_id ); ?>][options]" class="large-text vpa-custom-field-options" rows="3" placeholder="<?php esc_attr_e( 'One option per line (e.g., value : Label)', 'vitapro-appointments-fse' ); ?>"><?php echo esc_textarea( $field['options'] ?? '' ); ?></textarea>
                            </td>
                            <td><button type="button" class="button button-link-delete vpa-remove-custom-field"><?php esc_html_e( 'Remove', 'vitapro-appointments-fse' ); ?></button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="no-items"><td colspan="6"><?php esc_html_e( 'No custom fields defined yet.', 'vitapro-appointments-fse' ); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><button type="button" class="button" id="vpa-add-custom-field"><?php esc_html_e( 'Add New Custom Field', 'vitapro-appointments-fse' ); ?></button></p>
    </div>

    <script type="text/html" id="tmpl-vpa-custom-field-row">
        <tr data-field-id="{{data.id_placeholder}}">
            <td><input type="text" name="vitapro_appointments_settings[custom_fields][{{data.id_placeholder}}][label]" value="" class="regular-text vpa-custom-field-label" placeholder="<?php esc_attr_e( 'Field Label', 'vitapro-appointments-fse' ); ?>" /></td>
            <td><input type="text" name="vitapro_appointments_settings[custom_fields][{{data.id_placeholder}}][id]" value="" class="regular-text vpa-custom-field-id" placeholder="<?php esc_attr_e( 'Field ID', 'vitapro-appointments-fse' ); ?>" /></td>
            <td>
                <select name="vitapro_appointments_settings[custom_fields][{{data.id_placeholder}}][type]" class="vpa-custom-field-type">
                    <?php foreach ( $field_types as $type_val => $type_label ) : ?>
                        <option value="<?php echo esc_attr( $type_val ); ?>"><?php echo esc_html( $type_label ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="checkbox" name="vitapro_appointments_settings[custom_fields][{{data.id_placeholder}}][required]" value="1" class="vpa-custom-field-required" /></td>
            <td class="vpa-custom-field-options-cell vpa-hidden">
                <textarea name="vitapro_appointments_settings[custom_fields][{{data.id_placeholder}}][options]" class="large-text vpa-custom-field-options" rows="3" placeholder="<?php esc_attr_e( 'One option per line (e.g., value : Label)', 'vitapro-appointments-fse' ); ?>"></textarea>
            </td>
            <td><button type="button" class="button button-link-delete vpa-remove-custom-field"><?php esc_html_e( 'Remove', 'vitapro-appointments-fse' ); ?></button></td>
        </tr>
    </script>
    <?php
}

/**
 * Generic render functions for settings fields.
 */
function vitapro_setting_text_render( $args ) {
    $option_name = $args['id'];
    $default = isset( $args['default'] ) ? $args['default'] : '';
    $value = vitapro_appointments_get_option( $option_name, $default );
    ?>
    <input type="text" id="<?php echo esc_attr( $option_name ); ?>" name="vitapro_appointments_settings[<?php echo esc_attr( $option_name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
    <?php
    if ( isset( $args['desc'] ) ) {
        echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
    }
}

function vitapro_setting_email_render( $args ) {
    $option_name = $args['id'];
    $default = isset( $args['default'] ) ? $args['default'] : '';
    $value = vitapro_appointments_get_option( $option_name, $default );
    ?>
    <input type="email" id="<?php echo esc_attr( $option_name ); ?>" name="vitapro_appointments_settings[<?php echo esc_attr( $option_name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="regular-text" />
    <?php
    if ( isset( $args['desc'] ) ) {
        echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
    }
}

function vitapro_setting_number_render( $args ) {
    $option_name = $args['id'];
    $default = isset( $args['default'] ) ? $args['default'] : 0;
    $value = vitapro_appointments_get_option( $option_name, $default );
    ?>
    <input type="number" id="<?php echo esc_attr( $option_name ); ?>" name="vitapro_appointments_settings[<?php echo esc_attr( $option_name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="small-text" min="0" />
    <?php
    if ( isset( $args['desc'] ) ) {
        echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
    }
}

function vitapro_setting_time_render( $args ) {
    $option_name = $args['id'];
    $default = isset( $args['default'] ) ? $args['default'] : '09:00';
    $value = vitapro_appointments_get_option( $option_name, $default );
    ?>
    <input type="time" id="<?php echo esc_attr( $option_name ); ?>" name="vitapro_appointments_settings[<?php echo esc_attr( $option_name ); ?>]" value="<?php echo esc_attr( $value ); ?>" />
    <?php
}

function vitapro_setting_time_slot_interval_render() {
    $value = vitapro_appointments_get_option( 'time_slot_interval', 30 );
    $intervals = array(
        15 => __( '15 minutes', 'vitapro-appointments-fse' ),
        30 => __( '30 minutes', 'vitapro-appointments-fse' ),
        45 => __( '45 minutes', 'vitapro-appointments-fse' ),
        60 => __( '1 hour', 'vitapro-appointments-fse' ),
    );
    ?>
    <select id="time_slot_interval" name="vitapro_appointments_settings[time_slot_interval]">
        <?php foreach ( $intervals as $interval_value => $interval_label ) : ?>
            <option value="<?php echo esc_attr( $interval_value ); ?>" <?php selected( $value, $interval_value ); ?>>
                <?php echo esc_html( $interval_label ); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
}

function vitapro_setting_min_max_notice_render( $args ) {
    $option_name = $args['id'];
    $default = isset( $args['default'] ) ? $args['default'] : 0;
    $value = vitapro_appointments_get_option( $option_name, $default );
    ?>
    <input type="number" id="<?php echo esc_attr( $option_name ); ?>" name="vitapro_appointments_settings[<?php echo esc_attr( $option_name ); ?>]" value="<?php echo esc_attr( $value ); ?>" class="small-text" min="0" />
    <?php
    if ( isset( $args['desc'] ) ) {
        echo '<p class="description">' . esc_html( $args['desc'] ) . '</p>';
    }
}

function vitapro_setting_checkbox_render( $args ) {
    $option_name = $args['id'];
    $value = vitapro_appointments_get_option( $option_name, false );
    $label = isset( $args['label'] ) ? $args['label'] : '';
    ?>
    <label>
        <input type="checkbox" id="<?php echo esc_attr( $option_name ); ?>" name="vitapro_appointments_settings[<?php echo esc_attr( $option_name ); ?>]" value="1" <?php checked( $value ); ?> />
        <?php echo esc_html( $label ); ?>
    </label>
    <?php
}

/**
 * Sanitize settings fields.
 */
function vitapro_appointments_settings_sanitize( $input ) {
    $sanitized_input = array();
    $existing_options = get_option( 'vitapro_appointments_settings', array() );
    $input = wp_parse_args( $input, $existing_options );

    $options_definition = array(
        'default_opening_time'                => 'time',
        'default_closing_time'                => 'time',
        'time_slot_interval'                  => 'int',
        'min_advance_notice'                  => 'int',
        'max_advance_notice'                  => 'int',
        'manual_approval'                     => 'bool',
        'allow_patient_cancellation'          => 'bool',
        'patient_cancellation_buffer_hours'   => 'int',
        'email_from_name'                     => 'text',
        'email_from_address'                  => 'email',
        'email_admin_new_booking'             => 'email',
        'enable_patient_confirmation'         => 'bool',
        'enable_admin_notification'           => 'bool',
        'enable_reminders'                    => 'bool',
        'reminder_lead_time_hours'            => 'int',
    );

    // Ensure all boolean fields are correctly handled if not present in $input
    $boolean_fields = array( 'manual_approval', 'allow_patient_cancellation', 'enable_patient_confirmation', 'enable_admin_notification', 'enable_reminders' );
    foreach ( $boolean_fields as $bf ) {
        if ( ! isset( $input[ $bf ] ) ) {
            $input[ $bf ] = false;
        }
    }

    foreach ( $options_definition as $key => $type ) {
        if ( isset( $input[ $key ] ) ) {
            switch ( $type ) {
                case 'text':
                    $sanitized_input[ $key ] = sanitize_text_field( $input[ $key ] );
                    break;
                case 'email':
                    $sanitized_input[ $key ] = sanitize_email( $input[ $key ] );
                    break;
                case 'int':
                    $sanitized_input[ $key ] = absint( $input[ $key ] );
                    break;
                case 'bool':
                    $sanitized_input[ $key ] = (bool) $input[ $key ];
                    break;
                case 'time':
                    $sanitized_input[ $key ] = sanitize_text_field( $input[ $key ] );
                    break;
                default:
                    $sanitized_input[ $key ] = sanitize_text_field( $input[ $key ] );
                    break;
            }
        }
    }

    // Sanitize custom fields
    $sanitized_input['custom_fields'] = array();
    if ( isset( $input['custom_fields'] ) && is_array( $input['custom_fields'] ) ) {
        foreach ( $input['custom_fields'] as $temp_id_or_real_id => $field_data ) {
            if ( empty( $field_data['label'] ) ) {
                continue;
            }

            $field_label = sanitize_text_field( $field_data['label'] );
            $field_id_input = isset( $field_data['id'] ) ? sanitize_key( $field_data['id'] ) : '';
            $field_id_hidden = isset( $field_data['id_hidden'] ) ? sanitize_key( $field_data['id_hidden'] ) : '';
            $final_field_id = ! empty( $field_id_input ) ? $field_id_input : ( ! empty( $field_id_hidden ) ? $field_id_hidden : sanitize_key( $temp_id_or_real_id ) );

            if ( empty( $final_field_id ) || isset( $sanitized_input['custom_fields'][ $final_field_id ] ) ) {
                continue;
            }

            $field_type = isset( $field_data['type'] ) ? sanitize_key( $field_data['type'] ) : 'text';
            $field_options = '';
            if ( $field_type === 'select' && isset( $field_data['options'] ) ) {
                $lines = explode( "\n", $field_data['options'] );
                $sanitized_lines = array();
                foreach ( $lines as $line ) {
                    $line = trim( $line );
                    if ( ! empty( $line ) ) {
                        $parts = explode( ':', $line, 2 );
                        $opt_val = sanitize_text_field( trim( $parts[0] ) );
                        $opt_label = isset( $parts[1] ) ? sanitize_text_field( trim( $parts[1] ) ) : $opt_val;
                        if ( ! empty( $opt_val ) ) {
                            $sanitized_lines[] = $opt_val . ' : ' . $opt_label;
                        }
                    }
                }
                $field_options = implode( "\n", $sanitized_lines );
            }

            $sanitized_input['custom_fields'][ $final_field_id ] = array(
                'label'    => $field_label,
                'type'     => $field_type,
                'required' => ! empty( $field_data['required'] ),
                'options'  => $field_options,
            );
        }
    }

    return $sanitized_input;
}

/**
 * Render settings page.
 */
function vitapro_appointments_settings_page_render() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields( 'vitapro_appointments_options_group' );
            do_settings_sections( 'vitapro_appointments_settings_page' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render all appointments page.
 */
function vitapro_appointments_all_appointments_page_render() {
    ?>
    <div class="wrap">
        <h1><?php esc_html_e( 'All Appointments', 'vitapro-appointments-fse' ); ?></h1>
        <p><?php esc_html_e( 'Manage all appointments from the Appointments menu in the sidebar.', 'vitapro-appointments-fse' ); ?></p>
        <p><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=vpa_appointment' ) ); ?>" class="button button-primary"><?php esc_html_e( 'View All Appointments', 'vitapro-appointments-fse' ); ?></a></p>
        
        <h2><?php esc_html_e( 'Quick Stats', 'vitapro-appointments-fse' ); ?></h2>
        <?php
        $today = date( 'Y-m-d' );
        $appointments_today = get_posts( array(
            'post_type'      => 'vpa_appointment',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_vpa_appointment_date',
                    'value'   => $today,
                    'compare' => '=',
                ),
            ),
        ) );

        $pending_appointments = get_posts( array(
            'post_type'      => 'vpa_appointment',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_vpa_appointment_status',
                    'value'   => 'pending',
                    'compare' => '=',
                ),
            ),
        ) );
        ?>
        <div class="vpa-stats-grid">
            <div class="vpa-stat-box">
                <h3><?php echo count( $appointments_today ); ?></h3>
                <p><?php esc_html_e( 'Appointments Today', 'vitapro-appointments-fse' ); ?></p>
            </div>
            <div class="vpa-stat-box">
                <h3><?php echo count( $pending_appointments ); ?></h3>
                <p><?php esc_html_e( 'Pending Approval', 'vitapro-appointments-fse' ); ?></p>
            </div>
        </div>
    </div>
    <?php
}