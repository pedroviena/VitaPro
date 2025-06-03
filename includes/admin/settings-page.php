<?php
/**
 * Renders the main plugin settings page and registers its fields.
 * Este arquivo é chamado pela classe VitaPro_Appointments_FSE_Admin_Settings.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// REMOVIDA a função vitapro_appointments_add_admin_menu() e seu hook add_action('admin_menu', ...)
// O menu e submenu de Configurações são adicionados por VitaPro_Appointments_FSE_Admin_Settings

/**
 * Register plugin settings sections and fields.
 * Esta função é hookada em 'admin_init'.
 */
function vitapro_appointments_register_settings() {
    // O grupo de opções 'vitapro_appointments_options_group' e a opção 'vitapro_appointments_settings'
    // são registrados em VitaPro_Appointments_FSE_Admin_Settings::register_settings_groups().
    // Esta função aqui adiciona as seções e campos a essa página de configurações.
    // O nome da página aqui 'vitapro_appointments_settings_page' é usado para do_settings_sections().

    $settings_page_slug = 'vitapro_appointments_settings_page'; // Slug usado para add_settings_section/field

    // General Settings Section
    add_settings_section(
        'vitapro_appointments_general_section', // ID da Seção
        __('General Booking Settings', 'vitapro-appointments-fse'), // Título
        'vitapro_appointments_general_section_callback', // Callback para descrição da seção
        $settings_page_slug // Slug da página onde esta seção aparece
    );

    add_settings_field(
        'default_opening_time',
        __('Default Opening Time', 'vitapro-appointments-fse'),
        'vitapro_setting_time_render',
        $settings_page_slug,
        'vitapro_appointments_general_section',
        array( 'id' => 'default_opening_time', 'default' => '09:00', 'option_group' => 'vitapro_appointments_settings' )
    );

    add_settings_field(
        'default_closing_time',
        __('Default Closing Time', 'vitapro-appointments-fse'),
        'vitapro_setting_time_render',
        $settings_page_slug,
        'vitapro_appointments_general_section',
        array( 'id' => 'default_closing_time', 'default' => '17:00', 'option_group' => 'vitapro_appointments_settings' )
    );

    add_settings_field(
        'time_slot_interval',
        __('Time Slot Interval', 'vitapro-appointments-fse'),
        'vitapro_setting_time_slot_interval_render',
        $settings_page_slug,
        'vitapro_appointments_general_section',
        array( 'id' => 'time_slot_interval', 'option_group' => 'vitapro_appointments_settings' )
    );

    // ... (todos os outros add_settings_field para a seção 'vitapro_appointments_general_section' como estavam)
    // Exemplo de um campo:
    add_settings_field(
        'business_name', // ID do campo
        __('Business Name', 'vitapro-appointments-fse'), // Label
        'vitapro_setting_text_render', // Callback de renderização
        $settings_page_slug, // Slug da página
        'vitapro_appointments_general_section', // ID da Seção
        array( 'id' => 'business_name', 'default' => get_bloginfo('name'), 'option_group' => 'vitapro_appointments_settings' ) // Args para o callback
    );
    add_settings_field(
        'business_email',
        __('Business Email', 'vitapro-appointments-fse'),
        'vitapro_setting_email_render',
        $settings_page_slug,
        'vitapro_appointments_general_section',
        array( 'id' => 'business_email', 'default' => get_option('admin_email'), 'option_group' => 'vitapro_appointments_settings' )
    );
     add_settings_field(
        'manual_approval',
        __( 'Manual Booking Approval', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        $settings_page_slug,
        'vitapro_appointments_general_section',
        array(
            'id' => 'manual_approval',
            'label' => __( 'Require manual approval for all new bookings.', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );
    // ... adicione os outros campos da seção geral (phone, address, timezone, formats, currency, duration, advance_time, etc.)


    // Patient Interaction Section
    add_settings_section(
        'vitapro_appointments_patient_interaction_section',
        __('Patient Interactions', 'vitapro-appointments-fse'),
        'vitapro_appointments_patient_interaction_section_callback',
        $settings_page_slug
    );
    // ... (add_settings_field para esta seção como estavam, adicionando 'option_group')
    add_settings_field(
        'allow_patient_cancellation',
        __( 'Allow Patient Cancellation', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        $settings_page_slug,
        'vitapro_appointments_patient_interaction_section',
        array(
            'id' => 'allow_patient_cancellation',
            'label' => __( 'Allow patients to cancel their own appointments.', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );
    add_settings_field(
        'patient_cancellation_buffer_hours',
        __( 'Patient Cancellation Buffer (hours)', 'vitapro-appointments-fse' ),
        'vitapro_setting_number_render',
        $settings_page_slug,
        'vitapro_appointments_patient_interaction_section',
        array(
            'id' => 'patient_cancellation_buffer_hours',
            'default' => 48,
            'desc' => __( 'Minimum hours before the appointment that a patient can cancel.', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );


    // Email Settings Section (AINDA NA MESMA PÁGINA DE CONFIGURAÇÕES GERAIS)
    add_settings_section(
        'vitapro_appointments_email_section',
        __('Email Notifications Settings', 'vitapro-appointments-fse'), // Título pode ser mais específico
        'vitapro_appointments_email_section_callback',
        $settings_page_slug
    );
    // ... (add_settings_field para esta seção como estavam, adicionando 'option_group')
    add_settings_field(
        'email_from_name',
        __( 'From Name', 'vitapro-appointments-fse' ),
        'vitapro_setting_text_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array( 'id' => 'email_from_name', 'default' => get_bloginfo( 'name' ), 'option_group' => 'vitapro_appointments_settings' )
    );
    add_settings_field(
        'email_from_address',
        __( 'From Email Address', 'vitapro-appointments-fse' ),
        'vitapro_setting_email_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array( 'id' => 'email_from_address', 'default' => get_option( 'admin_email' ), 'option_group' => 'vitapro_appointments_settings' )
    );
    add_settings_field(
        'email_admin_new_booking',
        __( 'Admin Notification Email for New Booking', 'vitapro-appointments-fse' ),
        'vitapro_setting_email_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array( 'id' => 'email_admin_new_booking', 'default' => get_option( 'admin_email' ), 'option_group' => 'vitapro_appointments_settings' )
    );
    add_settings_field(
        'enable_patient_confirmation',
        __( 'Patient Confirmation Emails', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array(
            'id' => 'enable_patient_confirmation',
            'label' => __( 'Send confirmation emails to patients.', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );
     add_settings_field(
        'enable_admin_notification',
        __( 'Admin Notification Emails for New Booking', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array(
            'id' => 'enable_admin_notification',
            'label' => __( 'Send notification emails to admin for new bookings.', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );
    add_settings_field(
        'enable_reminders',
        __( 'Appointment Reminders', 'vitapro-appointments-fse' ),
        'vitapro_setting_checkbox_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array(
            'id' => 'enable_reminders',
            'label' => __( 'Send reminder emails to patients before appointments.', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );
    add_settings_field(
        'reminder_lead_time_hours',
        __( 'Reminder Lead Time (hours)', 'vitapro-appointments-fse' ),
        'vitapro_setting_number_render',
        $settings_page_slug,
        'vitapro_appointments_email_section',
        array(
            'id' => 'reminder_lead_time_hours',
            'default' => 24,
            'desc' => __( 'How many hours before the appointment should the reminder be sent?', 'vitapro-appointments-fse' ),
            'option_group' => 'vitapro_appointments_settings'
        )
    );


    // Custom Fields Section (AINDA NA MESMA PÁGINA DE CONFIGURAÇÕES GERAIS)
    // A UI para campos personalizados é mais complexa, então ela tem seu próprio callback de renderização de campo.
    add_settings_section(
        'vitapro_appointments_custom_fields_section',
        __('Custom Booking Fields', 'vitapro-appointments-fse'),
        'vitapro_appointments_custom_fields_section_callback',
        $settings_page_slug
    );
    add_settings_field(
        'custom_fields', // O ID do campo que armazena todos os campos personalizados como um array
        __('Define Custom Fields', 'vitapro-appointments-fse'), // O label para o grupo de campos
        'vitapro_render_custom_fields_ui', // Callback que renderiza toda a UI da tabela de campos
        $settings_page_slug,
        'vitapro_appointments_custom_fields_section',
        array( 'id' => 'custom_fields', 'option_group' => 'vitapro_appointments_settings' ) // Passando id e option_group
    );
}
// O add_action('admin_init', 'vitapro_appointments_register_settings') permanece.

/**
 * Section callbacks.
 */
function vitapro_appointments_general_section_callback() {
    echo '<p>' . esc_html__('Configure general booking settings for your clinic.', 'vitapro-appointments-fse') . '</p>';
}
function vitapro_appointments_patient_interaction_section_callback() {
    echo '<p>' . esc_html__('Settings related to patient interactions and self-service options.', 'vitapro-appointments-fse') . '</p>';
}
function vitapro_appointments_email_section_callback() {
    echo '<p>' . esc_html__('Configure email notifications and reminders.', 'vitapro-appointments-fse') . '</p>';
}
function vitapro_appointments_custom_fields_section_callback() {
    echo '<p>' . esc_html__('Define additional fields to display on the booking form.', 'vitapro-appointments-fse') . '</p>';
    echo '<p><em>' . esc_html__('Important: Field IDs should be unique, lowercase, and use underscores instead of spaces (e.g., reason_for_visit). Once a field is used and has data, changing its ID can lead to data loss for that field in existing appointments.', 'vitapro-appointments-fse') . '</em></p>';
}


/**
 * Render UI for managing custom fields.
 * Esta função agora usa o $args['id'] para construir o nome dos campos.
 */
function vitapro_render_custom_fields_ui($args) {
    $option_group_name = $args['option_group']; // Ex: 'vitapro_appointments_settings'
    $field_array_key = $args['id']; // Ex: 'custom_fields'
    
    // Obter todos os settings para encontrar o array de custom_fields
    $all_options = get_option($option_group_name, array());
    $custom_fields = isset($all_options[$field_array_key]) && is_array($all_options[$field_array_key]) ? $all_options[$field_array_key] : array();

    $field_types = array(
        'text'     => __('Text Input', 'vitapro-appointments-fse'),
        'textarea' => __('Text Area', 'vitapro-appointments-fse'),
        'select'   => __('Dropdown (Select)', 'vitapro-appointments-fse'),
        // Adicionar outros tipos se necessário: 'email', 'tel', 'checkbox', 'radio'
    );
    ?>
    <div id="vpa-custom-fields-container">
        <table class="wp-list-table widefat striped" id="vpa-custom-fields-table">
            <thead>
                <tr>
                    <th><?php esc_html_e('Field Label', 'vitapro-appointments-fse'); ?></th>
                    <th><?php esc_html_e('Field ID / Name', 'vitapro-appointments-fse'); ?></th>
                    <th><?php esc_html_e('Field Type', 'vitapro-appointments-fse'); ?></th>
                    <th><?php esc_html_e('Required?', 'vitapro-appointments-fse'); ?></th>
                    <th><?php esc_html_e('Options (for Dropdown)', 'vitapro-appointments-fse'); ?></th>
                    <th><?php esc_html_e('Actions', 'vitapro-appointments-fse'); ?></th>
                </tr>
            </thead>
            <tbody id="vpa-custom-fields-list">
                <?php if (!empty($custom_fields)) : ?>
                    <?php foreach ($custom_fields as $field_id => $field) : ?>
                        <tr data-field-id="<?php echo esc_attr($field_id); ?>">
                            <td><input type="text" name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][<?php echo esc_attr($field_id); ?>][label]" value="<?php echo esc_attr($field['label'] ?? ''); ?>" class="regular-text vpa-custom-field-label" /></td>
                            <td>
                                <input type="text" value="<?php echo esc_attr($field_id); ?>" class="regular-text vpa-custom-field-id-display" readonly="readonly" title="<?php esc_attr_e('ID cannot be changed after creation.', 'vitapro-appointments-fse'); ?>" />
                                </td>
                            <td>
                                <select name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][<?php echo esc_attr($field_id); ?>][type]" class="vpa-custom-field-type">
                                    <?php foreach ($field_types as $type_val => $type_label) : ?>
                                        <option value="<?php echo esc_attr($type_val); ?>" <?php selected($field['type'] ?? 'text', $type_val); ?>><?php echo esc_html($type_label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="checkbox" name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][<?php echo esc_attr($field_id); ?>][required]" value="1" <?php checked(!empty($field['required'])); ?> class="vpa-custom-field-required" /></td>
                            <td class="vpa-custom-field-options-cell <?php echo (($field['type'] ?? 'text') === 'select') ? '' : 'vpa-hidden'; ?>">
                                <textarea name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][<?php echo esc_attr($field_id); ?>][options]" class="large-text vpa-custom-field-options" rows="3" placeholder="<?php esc_attr_e('One option per line (e.g., value : Label)', 'vitapro-appointments-fse'); ?>"><?php echo esc_textarea($field['options'] ?? ''); ?></textarea>
                            </td>
                            <td><button type="button" class="button button-link-delete vpa-remove-custom-field"><?php esc_html_e('Remove', 'vitapro-appointments-fse'); ?></button></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="no-items"><td colspan="6"><?php esc_html_e('No custom fields defined yet.', 'vitapro-appointments-fse'); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <p><button type="button" class="button" id="vpa-add-custom-field"><?php esc_html_e('Add New Custom Field', 'vitapro-appointments-fse'); ?></button></p>
    </div>

    <script type="text/html" id="tmpl-vpa-custom-field-row">
        <tr data-field-id="{{data.id_placeholder}}">
            <td><input type="text" name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][{{data.id_placeholder}}][label]" value="" class="regular-text vpa-custom-field-label" placeholder="<?php esc_attr_e('Field Label', 'vitapro-appointments-fse'); ?>" /></td>
            <td>
                <input type="text" name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][{{data.id_placeholder}}][id_input_temp]" value="{{data.id_placeholder}}" class="regular-text vpa-custom-field-id" placeholder="<?php esc_attr_e('Field ID (e.g. reason_for_visit)', 'vitapro-appointments-fse'); ?>" />
                <small><?php esc_html_e('Use lowercase letters, numbers, and underscores only. Cannot be changed later.', 'vitapro-appointments-fse'); ?></small>
            </td>
            <td>
                <select name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][{{data.id_placeholder}}][type]" class="vpa-custom-field-type">
                    <?php foreach ($field_types as $type_val => $type_label) : ?>
                        <option value="<?php echo esc_attr($type_val); ?>"><?php echo esc_html($type_label); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td><input type="checkbox" name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][{{data.id_placeholder}}][required]" value="1" class="vpa-custom-field-required" /></td>
            <td class="vpa-custom-field-options-cell vpa-hidden">
                <textarea name="<?php echo esc_attr($option_group_name); ?>[<?php echo esc_attr($field_array_key); ?>][{{data.id_placeholder}}][options]" class="large-text vpa-custom-field-options" rows="3" placeholder="<?php esc_attr_e('One option per line (e.g., value : Label)', 'vitapro-appointments-fse'); ?>"></textarea>
            </td>
            <td><button type="button" class="button button-link-delete vpa-remove-custom-field"><?php esc_html_e('Remove', 'vitapro-appointments-fse'); ?></button></td>
        </tr>
    </script>
     <script type="text/javascript">
        jQuery(document).ready(function($) {
            var fieldIndex = <?php echo count($custom_fields); ?>; // Start index for new fields

            $('#vpa-add-custom-field').on('click', function() {
                var template = wp.template('vpa-custom-field-row');
                var newFieldId = 'new_field_' + fieldIndex; // Placeholder ID
                $('#vpa-custom-fields-list').find('tr.no-items').remove();
                $('#vpa-custom-fields-list').append(template({ id_placeholder: newFieldId }));
                fieldIndex++;
            });

            $('#vpa-custom-fields-list').on('click', '.vpa-remove-custom-field', function() {
                $(this).closest('tr').remove();
                if ($('#vpa-custom-fields-list tr').length === 0) {
                     $('#vpa-custom-fields-list').append('<tr class="no-items"><td colspan="6"><?php esc_html_e('No custom fields defined yet.', 'vitapro-appointments-fse'); ?></td></tr>');
                }
            });

            $('#vpa-custom-fields-list').on('change', '.vpa-custom-field-type', function() {
                var $this = $(this);
                var $optionsCell = $this.closest('tr').find('.vpa-custom-field-options-cell');
                if ($this.val() === 'select') {
                    $optionsCell.removeClass('vpa-hidden');
                } else {
                    $optionsCell.addClass('vpa-hidden');
                }
            });

            // Renomear o campo do ID antes do submit do formulário,
            // para que o ID seja a chave do array.
            // Isso é um pouco complexo porque o `name` do input do ID precisaria ser dinâmico
            // OU você processa isso no PHP (na função de sanitização).
            // A função de sanitização atual (`vitapro_appointments_settings_sanitize`) já tenta
            // lidar com isso usando `id_input_temp` e `id_hidden`.
            // A parte JS para atualizar o nome real do campo ID antes de salvar:
             $('form[action="options.php"]').on('submit', function() {
                $('#vpa-custom-fields-list tr').each(function() {
                    var $row = $(this);
                    var $idInput = $row.find('.vpa-custom-field-id'); // O input onde o usuário digita o ID
                    if ($idInput.length && !$idInput.is('[readonly]')) { // Se não for readonly, é um novo campo
                        var newId = $idInput.val().trim();
                        if (newId) {
                            newId = newId.toLowerCase().replace(/\s+/g, '_').replace(/[^a-z0-9_]/gi, '');
                            $idInput.val(newId); // Atualiza o valor do input
                            
                            // Atualiza os names de todos os inputs na linha
                            $row.find('input, select, textarea').each(function() {
                                var currentName = $(this).attr('name');
                                if (currentName) {
                                    var oldIdPlaceholder = $row.data('field-id');
                                    var newName = currentName.replace('[' + oldIdPlaceholder + ']', '[' + newId + ']');
                                    $(this).attr('name', newName);
                                }
                            });
                            $row.data('field-id', newId); // Atualiza o data attribute da linha
                        } else {
                            // Se o ID estiver vazio, talvez remover a linha ou marcar para não salvar
                            // $row.remove(); // Ou alguma outra lógica
                        }
                    }
                });
            });
        });
    </script>
    <?php
}


/**
 * Generic render functions for settings fields.
 * Adicionar $args['option_group'] para construir o nome completo da opção.
 */
function vitapro_setting_text_render( $args ) {
    $option_group = $args['option_group'];
    $option_name = $args['id'];
    $all_options = get_option( $option_group, array() );
    $value = isset( $all_options[$option_name] ) ? $all_options[$option_name] : (isset($args['default']) ? $args['default'] : '');
    ?>
    <input type="text" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_group); ?>[<?php echo esc_attr($option_name); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <?php
    if (isset($args['desc'])) {
        echo '<p class="description">' . esc_html($args['desc']) . '</p>';
    }
}

function vitapro_setting_email_render( $args ) {
    $option_group = $args['option_group'];
    $option_name = $args['id'];
    $all_options = get_option( $option_group, array() );
    $value = isset( $all_options[$option_name] ) ? $all_options[$option_name] : (isset($args['default']) ? $args['default'] : '');
    ?>
    <input type="email" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_group); ?>[<?php echo esc_attr($option_name); ?>]" value="<?php echo esc_attr($value); ?>" class="regular-text" />
    <?php
    if (isset($args['desc'])) {
        echo '<p class="description">' . esc_html($args['desc']) . '</p>';
    }
}

function vitapro_setting_number_render( $args ) {
    $option_group = $args['option_group'];
    $option_name = $args['id'];
    $all_options = get_option( $option_group, array() );
    $value = isset( $all_options[$option_name] ) ? $all_options[$option_name] : (isset($args['default']) ? $args['default'] : 0);
    ?>
    <input type="number" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_group); ?>[<?php echo esc_attr($option_name); ?>]" value="<?php echo esc_attr($value); ?>" class="small-text" min="<?php echo isset($args['min']) ? esc_attr($args['min']) : '0'; ?>" step="<?php echo isset($args['step']) ? esc_attr($args['step']) : '1'; ?>" />
    <?php
    if (isset($args['desc'])) {
        echo '<p class="description">' . esc_html($args['desc']) . '</p>';
    }
}

function vitapro_setting_time_render( $args ) {
    $option_group = $args['option_group'];
    $option_name = $args['id'];
    $all_options = get_option( $option_group, array() );
    $value = isset( $all_options[$option_name] ) ? $all_options[$option_name] : (isset($args['default']) ? $args['default'] : '09:00');
    ?>
    <input type="time" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_group); ?>[<?php echo esc_attr($option_name); ?>]" value="<?php echo esc_attr($value); ?>" />
    <?php
    if (isset($args['desc'])) {
        echo '<p class="description">' . esc_html($args['desc']) . '</p>';
    }
}

function vitapro_setting_time_slot_interval_render($args) {
    $option_group = $args['option_group'];
    $option_name = $args['id'];
    $all_options = get_option( $option_group, array() );
    $value = isset( $all_options[$option_name] ) ? $all_options[$option_name] : (isset($args['default']) ? $args['default'] : 30);

    $intervals = array(
        15 => __('15 minutes', 'vitapro-appointments-fse'),
        30 => __('30 minutes', 'vitapro-appointments-fse'),
        45 => __('45 minutes', 'vitapro-appointments-fse'),
        60 => __('1 hour', 'vitapro-appointments-fse'),
    );
    ?>
    <select id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_group); ?>[<?php echo esc_attr($option_name); ?>]">
        <?php foreach ($intervals as $interval_value => $interval_label) : ?>
            <option value="<?php echo esc_attr($interval_value); ?>" <?php selected($value, $interval_value); ?>>
                <?php echo esc_html($interval_label); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <?php
    if (isset($args['desc'])) {
        echo '<p class="description">' . esc_html($args['desc']) . '</p>';
    }
}

function vitapro_setting_min_max_notice_render( $args ) {
    // Funciona da mesma forma que vitapro_setting_number_render
    vitapro_setting_number_render($args);
}

function vitapro_setting_checkbox_render( $args ) {
    $option_group = $args['option_group'];
    $option_name = $args['id'];
    $all_options = get_option( $option_group, array() );
    $value = isset( $all_options[$option_name] ) ? (bool)$all_options[$option_name] : (isset($args['default']) ? (bool)$args['default'] : false);
    $label = isset($args['label']) ? $args['label'] : '';
    ?>
    <label>
        <input type="checkbox" id="<?php echo esc_attr($option_name); ?>" name="<?php echo esc_attr($option_group); ?>[<?php echo esc_attr($option_name); ?>]" value="1" <?php checked($value); ?> />
        <?php if (!empty($label)) echo ' ' . esc_html($label); ?>
    </label>
    <?php
    if (isset($args['desc']) && !empty($label) ) { // Descrição se houver label principal
         echo '<p class="description">' . esc_html($args['desc']) . '</p>';
    }
}

/**
 * Sanitize ALL settings from the 'vitapro_appointments_settings' option group.
 */
function vitapro_appointments_settings_sanitize($input) {
    $sanitized_input = array();
    $existing_options = get_option('vitapro_appointments_settings', array()); // Nome da opção principal

    // Definições de todas as opções esperadas e seus tipos de sanitização
    $options_definition = array(
        'business_name' => 'text',
        'business_email' => 'email',
        'business_phone' => 'text', // Poderia ser mais específico
        'business_address' => 'textarea',
        'timezone' => 'text', // Poderia ser uma lista de timezones válidos
        'date_format' => 'text', // Poderia ser uma lista de formatos válidos
        'time_format' => 'text', // Poderia ser uma lista de formatos válidos
        'currency' => 'text', // Poderia ser uma lista de códigos de moeda válidos
        'currency_symbol' => 'text',
        'currency_position' => 'text', // Poderia ser uma lista de posições válidas
        'default_appointment_duration' => 'int',
        'booking_advance_time' => 'int',
        'cancellation_time_limit' => 'int',
        'max_appointments_per_day' => 'int',
        'require_login' => 'bool',
        'auto_confirm_appointments' => 'bool',
        'send_email_notifications' => 'bool',
        'send_sms_notifications' => 'bool',
        // Campos de settings-page.php
        'default_opening_time' => 'time',
        'default_closing_time' => 'time',
        'time_slot_interval' => 'int',
        'min_advance_notice' => 'int',
        'max_advance_notice' => 'int',
        'manual_approval' => 'bool',
        'allow_patient_cancellation' => 'bool',
        'patient_cancellation_buffer_hours' => 'int',
        'email_from_name' => 'text',
        'email_from_address' => 'email',
        'email_admin_new_booking' => 'email', // Adicionado este campo
        'enable_patient_confirmation' => 'bool',
        'enable_admin_notification' => 'bool',
        'enable_reminders' => 'bool',
        'reminder_lead_time_hours' => 'int',
        // 'custom_fields' é tratado separadamente abaixo
    );

    foreach ($options_definition as $key => $type) {
        if (isset($input[$key])) {
            switch ($type) {
                case 'text': $sanitized_input[$key] = sanitize_text_field($input[$key]); break;
                case 'textarea': $sanitized_input[$key] = sanitize_textarea_field($input[$key]); break;
                case 'email': $sanitized_input[$key] = sanitize_email($input[$key]); break;
                case 'int': $sanitized_input[$key] = absint($input[$key]); break;
                case 'bool': $sanitized_input[$key] = (bool)$input[$key]; break;
                case 'time': $sanitized_input[$key] = preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $input[$key]) ? $input[$key] : '09:00'; break;
                default: $sanitized_input[$key] = sanitize_text_field($input[$key]); break;
            }
        } elseif ($type === 'bool') { // Garante que checkboxes desmarcados sejam salvos como false
            $sanitized_input[$key] = false;
        } elseif (isset($existing_options[$key])) { // Manter valor existente se não enviado
             $sanitized_input[$key] = $existing_options[$key];
        }
    }

    // Tratamento especial para custom_fields
    $sanitized_input['custom_fields'] = array();
    if (isset($input['custom_fields']) && is_array($input['custom_fields'])) {
        foreach ($input['custom_fields'] as $temp_or_real_id => $field_data) {
            if (empty($field_data['label'])) {
                continue; // Pular campos sem label
            }

            $field_label = sanitize_text_field($field_data['label']);
            
            // Determinar o ID final do campo
            // Se 'id_input_temp' foi enviado (novo campo), use seu valor sanitizado.
            // Senão, use a chave do array (que é o ID existente).
            $field_id_from_input = isset($field_data['id_input_temp']) ? sanitize_key($field_data['id_input_temp']) : '';
            $final_field_id = !empty($field_id_from_input) ? $field_id_from_input : sanitize_key($temp_or_real_id);

            if (empty($final_field_id)) {
                // Gerar um ID se estiver realmente faltando, embora o JS deva fornecer um placeholder
                $final_field_id = 'field_' . uniqid();
            }
             // Evitar sobrescrever um campo já sanitizado se houver IDs duplicados (o JS deve tentar evitar isso)
            if (isset($sanitized_input['custom_fields'][$final_field_id])) {
                // Poderia adicionar um sufixo ou logar um erro
                continue;
            }


            $field_type = isset($field_data['type']) ? sanitize_key($field_data['type']) : 'text';
            $field_options_sanitized = '';
            if ($field_type === 'select' && isset($field_data['options'])) {
                $options_lines = explode("\n", $field_data['options']);
                $temp_options_array = array();
                foreach ($options_lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        // Permitir tanto "valor : Rótulo" quanto apenas "Rótulo" (onde valor = rótulo sanitizado)
                        $parts = array_map('trim', explode(':', $line, 2));
                        $opt_val = sanitize_text_field($parts[0]);
                        $opt_label = isset($parts[1]) ? sanitize_text_field(trim($parts[1])) : $opt_val;
                        if(empty($opt_val)) $opt_val = sanitize_key(str_replace(' ', '_', strtolower($opt_label))); // Gerar valor a partir do rótulo se o valor for vazio

                        if (!empty($opt_val)) { // Garantir que o valor não seja vazio
                           $temp_options_array[] = $opt_val . ' : ' . $opt_label;
                        }
                    }
                }
                $field_options_sanitized = implode("\n", $temp_options_array);
            }

            $sanitized_input['custom_fields'][$final_field_id] = array(
                'label'    => $field_label,
                'type'     => $field_type,
                'required' => !empty($field_data['required']), // Checkbox valor será '1' ou não existirá
                'options'  => $field_options_sanitized,
            );
        }
    }

    return $sanitized_input;
}


/**
 * Render a página principal de configurações.
 * Chamada pela classe VitaPro_Appointments_FSE_Admin_Settings.
 */
function vitapro_appointments_settings_page_render() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Este é o nome do grupo de opções que você registrou.
            settings_fields('vitapro_appointments_options_group');
            // Este é o slug da página que você usou em add_settings_section e add_settings_field.
            do_settings_sections('vitapro_appointments_settings_page');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// A função vitapro_appointments_all_appointments_page_render()
// foi removida deste arquivo pois sua lógica de exibir uma página estática
// de "todos os agendamentos" é melhor gerenciada pela página de Visão Geral (presentation.php)
// ou diretamente linkando para a listagem do CPT.
?>