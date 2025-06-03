<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
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
    <?php
    // Renderização dos campos personalizados
    if (function_exists('vitapro_render_custom_fields_ui')) {
        vitapro_render_custom_fields_ui();
    }
    ?>
</div>
