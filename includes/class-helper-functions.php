<?php
/**
 * Helper Functions
 * 
 * Provides helper functions for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Helper_Functions {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Helper functions will be implemented here
    }
}

// Funções auxiliares genéricas para o plugin VitaPro Appointments FSE

if (!function_exists('vpa_array_get')) {
    function vpa_array_get($array, $key, $default = null) {
        return isset($array[$key]) ? $array[$key] : $default;
    }
}

if (!function_exists('vpa_format_money')) {
    function vpa_format_money($amount, $currency = '$') {
        return $currency . number_format_i18n(floatval($amount), 2);
    }
}

// Adicione outras funções utilitárias conforme necessário.