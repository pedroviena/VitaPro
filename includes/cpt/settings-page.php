<?php
/**
 * Settings Page
 * 
 * Handles all plugin settings and configuration options.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Redireciona para o novo local da lógica de settings
require_once dirname(__FILE__, 2) . '/admin/settings-page.php';