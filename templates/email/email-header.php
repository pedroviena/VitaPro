<?php
/**
 * Email Template Part: Header
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Exemplo de inclusão de estilos:
$styles_path = VITAPRO_APPOINTMENTS_FSE_PATH . 'templates/email/email-styles.php';
if (file_exists($styles_path)) {
    include $styles_path;
} else {
    error_log('VitaPro Error: Template file not found: ' . $styles_path);
    // Opcional: echo '<style>/* Minimal fallback styles here */</style>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html( $args['site_name'] ?? get_bloginfo( 'name' ) ); ?></title>
    <style type="text/css">
        <?php echo $styles; ?>
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <h1><?php echo esc_html( $args['site_name'] ?? get_bloginfo( 'name' ) ); ?></h1>
        </div>
        <div class="email-content">