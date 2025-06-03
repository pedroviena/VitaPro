<?php
/**
 * Admin Settings
 *
 * Handles admin settings and submenus for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 * @version 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Admin_Settings
 *
 * Handles the registration of admin submenus and settings groups for the plugin.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
if (!class_exists('VitaPro_Appointments_FSE_Admin_Settings')) {

class VitaPro_Appointments_FSE_Admin_Settings {

    /**
     * VitaPro_Appointments_FSE_Admin_Settings constructor.
     *
     * Registers admin menu and settings group hooks.
     *
     * @since 1.0.0
     * @uses add_action()
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_submenus'));
        // O registro do grupo de opções principal é feito aqui.
        // O registro das seções e campos DENTRO desse grupo é feito pela
        // função `vitapro_appointments_register_settings` em `includes/cpt/settings-page.php`,
        // que é hookada em 'admin_init'.
        add_action('admin_init', array($this, 'register_main_settings_group'));
    }

    /**
     * Add admin submenus under the main 'vitapro-appointments' menu.
     *
     * @since 1.0.0
     * @uses add_submenu_page()
     * @return void
     */
    public function add_settings_submenus() {
        $parent_slug = 'vitapro-appointments';

        // Submenu de Configurações Gerais
        add_submenu_page(
            $parent_slug,
            esc_html__('Settings - VitaPro Appointments', 'vitapro-appointments-fse'), // Page Title
            esc_html__('Settings', 'vitapro-appointments-fse'),                       // Menu Title
            'manage_options',                                                         // Capability
            'vitapro-appointments-settings',                                          // Menu slug
            array($this, 'render_general_settings_page_callback')                     // Callback
        );

        // Submenu de Modelos de Email
        add_submenu_page(
            $parent_slug,
            esc_html__('Email Templates - VitaPro Appointments', 'vitapro-appointments-fse'),
            esc_html__('Email Templates', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-email-templates',
            array($this, 'render_email_templates_page_callback')
        );

        // Adicione outros submenus conforme necessário.
    }

    /**
     * Registra o principal grupo de opções do plugin.
     * A função de sanitização para este grupo está em `includes/cpt/settings-page.php`.
     *
     * @since 1.0.0
     * @uses register_setting()
     * @return void
     */
    public function register_main_settings_group() {
        register_setting(
            'vitapro_appointments_options_group',          // Option group name.
            'vitapro_appointments_settings',               // Option name in the database.
            'vitapro_appointments_settings_sanitize'       // Sanitization callback from includes/cpt/settings-page.php
        );
        // Registre outros grupos de opções aqui se necessário.
    }

    /**
     * Callback para renderizar a página de Configurações Gerais.
     * Esta função chama a função de renderização de 'includes/cpt/settings-page.php'.
     *
     * @since 1.0.0
     * @uses vitapro_appointments_settings_page_render()
     * @return void
     */
    public function render_general_settings_page_callback() {
        if (function_exists('vitapro_appointments_settings_page_render')) {
            vitapro_appointments_settings_page_render();
        } else {
            echo '<div class="wrap"><h1>' . esc_html__('Error: Settings page render function not found.', 'vitapro-appointments-fse') . '</h1></div>';
        }
    }

    /**
     * Callback para renderizar a página de Modelos de Email.
     *
     * @since 1.0.0
     * @return void
     */
    public function render_email_templates_page_callback() {
        ?>
        <div class="wrap vpa-settings-page">
            <h1><?php esc_html_e('Email Templates', 'vitapro-appointments-fse'); ?></h1>
            <p><?php esc_html_e('Customize email templates for various appointment notifications.', 'vitapro-appointments-fse'); ?></p>
            <p>
                <?php esc_html_e('Available placeholders:', 'vitapro-appointments-fse'); ?>
                <code>{customer_name}</code>, <code>{service_name}</code>, <code>{professional_name}</code>,
                <code>{appointment_date}</code>, <code>{appointment_time}</code>, <code>{status}</code>,
                <code>{cancellation_link}</code>, <code>{site_name}</code>, <code>{site_url}</code>,
                <code>{appointment_id}</code>, <code>{appointment_reference}</code>, <code>{custom_fields}</code>.
            </p>
            <?php
            $template_files = array(
                'new-booking-patient.php' => esc_html__('New Booking - Patient Confirmation', 'vitapro-appointments-fse'),
                'new-booking-admin.php'   => esc_html__('New Booking - Admin Notification', 'vitapro-appointments-fse'),
                'reminder-patient.php'    => esc_html__('Appointment Reminder - Patient', 'vitapro-appointments-fse'),
                'cancellation-patient.php'=> esc_html__('Cancellation - Patient Confirmation', 'vitapro-appointments-fse'),
                'cancellation-admin.php'  => esc_html__('Cancellation - Admin Notification', 'vitapro-appointments-fse'),
                // Adicione 'status-update-patient.php' se existir
            );
            ?>
            <div id="vpa-email-template-list" style="margin-top: 20px;">
                <?php foreach ($template_files as $file => $description) : ?>
                    <div class="card" style="margin-bottom: 20px;">
                        <h2 style="font-size: 1.2em; padding-bottom: 0.5em; border-bottom: 1px solid #eee; margin-bottom: 1em;"><?php echo esc_html($description); ?></h2>
                        <p>
                            <?php esc_html_e('Template file:', 'vitapro-appointments-fse'); ?>
                            <code>templates/email/<?php echo esc_html($file); ?></code>
                        </p>
                        <p>
                            <em>
                                <?php
                                printf(
                                    wp_kses_post(__('To customize, copy this file to %s and modify it.', 'vitapro-appointments-fse')),
                                    '<code>your-theme/vitapro-appointments/email/' . esc_html($file) . '</code>'
                                );
                                ?>
                            </em>
                        </p>
                    </div>
                <?php endforeach; ?>

                <div class="card" style="margin-bottom: 20px;">
                    <h2 style="font-size: 1.2em; padding-bottom: 0.5em; border-bottom: 1px solid #eee; margin-bottom: 1em;"><?php esc_html_e('Email Header, Footer & Styles', 'vitapro-appointments-fse'); ?></h2>
                    <p>
                        <?php esc_html_e('Files:', 'vitapro-appointments-fse'); ?>
                        <code>templates/email/email-header.php</code>,
                        <code>templates/email/email-footer.php</code>,
                        <code>templates/email/email-styles.php</code>.
                    </p>
                    <p><em><?php esc_html_e('To customize, copy these files to <code>your-theme/vitapro-appointments/email/</code> directory and modify them.', 'vitapro-appointments-fse'); ?></em></p>
                </div>
            </div>
             <?php
                $settings_page_url = admin_url('admin.php?page=vitapro-appointments-settings');
             ?>
             <h2 style="font-size: 1.3em; margin-top: 30px;"><?php esc_html_e('Global Email Settings', 'vitapro-appointments-fse'); ?></h2>
             <p>
                <?php
                printf(
                    wp_kses_post(__('The "From Name" and "From Email Address" used in outgoing emails are configured on the main <a href="%s">Settings page</a> (under "Email Notifications Settings" section).', 'vitapro-appointments-fse')),
                    esc_url($settings_page_url)
                );
                ?>
             </p>
        </div>
        <?php
    }
    // A função enqueue_admin_scripts foi removida daqui para evitar duplicação,
    // já que o arquivo principal do plugin lida com o enfileiramento para páginas do plugin.
}

}