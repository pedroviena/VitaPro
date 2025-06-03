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
 * Registra todos os campos e seções de configuração sob o grupo único.
 */
function vitapro_appointments_register_settings() {
    register_setting(
        'vitapro_appointments_options_group',
        'vitapro_appointments_settings',
        'vitapro_appointments_settings_sanitize'
    );

    // Adicione seções e campos para cada aba (General, Booking, Notifications, etc.)
    // Exemplo:
    add_settings_section('vpa_general_section', __('General Settings', 'vitapro-appointments-fse'), '__return_false', 'vitapro-appointments-settings');
    add_settings_field('business_name', __('Business Name', 'vitapro-appointments-fse'), 'vitapro_setting_text_render', 'vitapro-appointments-settings', 'vpa_general_section', array('key' => 'business_name'));
    // Repita para todos os campos/abas...
}
add_action('admin_init', 'vitapro_appointments_register_settings');

/**
 * Sanitiza todo o array de configurações.
 */
function vitapro_appointments_settings_sanitize($input) {
    $output = array();

    // Sanitize cada campo conforme o tipo
    $output['business_name'] = sanitize_text_field($input['business_name'] ?? '');
    $output['business_email'] = sanitize_email($input['business_email'] ?? '');
    // Repita para todos os campos...

    // Campos customizados (array)
    if (!empty($input['custom_fields']) && is_array($input['custom_fields'])) {
        $output['custom_fields'] = array_map(function($field) {
            return array(
                'label' => sanitize_text_field($field['label'] ?? ''),
                'type' => sanitize_text_field($field['type'] ?? ''),
                'required' => !empty($field['required']) ? 1 : 0,
                'options' => sanitize_text_field($field['options'] ?? ''),
            );
        }, $input['custom_fields']);
    } else {
        $output['custom_fields'] = array();
    }

    return $output;
}

/**
 * Renderização dos campos de configuração.
 */
function vitapro_setting_text_render($args) {
    $settings = get_option('vitapro_appointments_settings', array());
    $key = $args['key'];
    $value = isset($settings[$key]) ? esc_attr($settings[$key]) : '';
    echo '<input type="text" name="vitapro_appointments_settings[' . esc_attr($key) . ']" value="' . $value . '" class="regular-text" />';
}

function vitapro_setting_email_render($args) {
    $settings = get_option('vitapro_appointments_settings', array());
    $key = $args['key'];
    $value = isset($settings[$key]) ? esc_attr($settings[$key]) : '';
    echo '<input type="email" name="vitapro_appointments_settings[' . esc_attr($key) . ']" value="' . $value . '" class="regular-text" />';
}

// ...crie funções similares para outros tipos de campo...

/**
 * Renderiza o formulário completo de configurações com abas.
 */
function vitapro_appointments_settings_page_render() {
    ?>
    <div class="wrap">
        <h1><?php _e('VitaPro Appointments Settings', 'vitapro-appointments-fse'); ?></h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('vitapro_appointments_options_group');
            do_settings_sections('vitapro-appointments-settings');
            submit_button();
            ?>
        </form>
        <?php vitapro_render_custom_fields_ui(); ?>
    </div>
    <?php
}

/**
 * Renderiza a UI de campos personalizados.
 */
function vitapro_render_custom_fields_ui() {
    $settings = get_option('vitapro_appointments_settings', array());
    $custom_fields = isset($settings['custom_fields']) ? $settings['custom_fields'] : array();
    ?>
    <h2><?php _e('Custom Fields', 'vitapro-appointments-fse'); ?></h2>
    <table id="vpa-custom-fields-table" class="widefat">
        <thead>
            <tr>
                <th><?php _e('Label', 'vitapro-appointments-fse'); ?></th>
                <th><?php _e('Type', 'vitapro-appointments-fse'); ?></th>
                <th><?php _e('Required', 'vitapro-appointments-fse'); ?></th>
                <th><?php _e('Options', 'vitapro-appointments-fse'); ?></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($custom_fields)) : foreach ($custom_fields as $i => $field) : ?>
                <tr>
                    <td><input type="text" name="vitapro_appointments_settings[custom_fields][<?php echo $i; ?>][label]" value="<?php echo esc_attr($field['label']); ?>" /></td>
                    <td>
                        <select name="vitapro_appointments_settings[custom_fields][<?php echo $i; ?>][type]">
                            <option value="text" <?php selected($field['type'], 'text'); ?>>Text</option>
                            <option value="email" <?php selected($field['type'], 'email'); ?>>Email</option>
                            <option value="tel" <?php selected($field['type'], 'tel'); ?>>Phone</option>
                            <option value="textarea" <?php selected($field['type'], 'textarea'); ?>>Textarea</option>
                            <option value="select" <?php selected($field['type'], 'select'); ?>>Select</option>
                            <option value="checkbox" <?php selected($field['type'], 'checkbox'); ?>>Checkbox</option>
                            <option value="radio" <?php selected($field['type'], 'radio'); ?>>Radio</option>
                        </select>
                    </td>
                    <td><input type="checkbox" name="vitapro_appointments_settings[custom_fields][<?php echo $i; ?>][required]" value="1" <?php checked($field['required'], 1); ?> /></td>
                    <td><input type="text" name="vitapro_appointments_settings[custom_fields][<?php echo $i; ?>][options]" value="<?php echo esc_attr($field['options']); ?>" /></td>
                    <td><button type="button" class="button vpa-remove-custom-field"><?php _e('Remove', 'vitapro-appointments-fse'); ?></button></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    <button type="button" class="button" id="vpa-add-custom-field"><?php _e('Add Field', 'vitapro-appointments-fse'); ?></button>
    <script>
    jQuery(function($){
        $('#vpa-add-custom-field').on('click', function(){
            var $tbody = $('#vpa-custom-fields-table tbody');
            var i = $tbody.find('tr').length;
            var row = '<tr>' +
                '<td><input type="text" name="vitapro_appointments_settings[custom_fields]['+i+'][label]" /></td>' +
                '<td><select name="vitapro_appointments_settings[custom_fields]['+i+'][type]">' +
                '<option value="text">Text</option><option value="email">Email</option><option value="tel">Phone</option><option value="textarea">Textarea</option><option value="select">Select</option><option value="checkbox">Checkbox</option><option value="radio">Radio</option></select></td>' +
                '<td><input type="checkbox" name="vitapro_appointments_settings[custom_fields]['+i+'][required]" value="1" /></td>' +
                '<td><input type="text" name="vitapro_appointments_settings[custom_fields]['+i+'][options]" /></td>' +
                '<td><button type="button" class="button vpa-remove-custom-field"><?php _e('Remove', 'vitapro-appointments-fse'); ?></button></td>' +
                '</tr>';
            $tbody.append(row);
        });
        $(document).on('click', '.vpa-remove-custom-field', function(){
            $(this).closest('tr').remove();
        });
    });
    </script>
    <?php
}

// A função vitapro_appointments_all_appointments_page_render()
// foi removida deste arquivo pois sua lógica de exibir uma página estática
// de "todos os agendamentos" é melhor gerenciada pela página de Visão Geral (presentation.php)
// ou diretamente linkando para a listagem do CPT.
?>