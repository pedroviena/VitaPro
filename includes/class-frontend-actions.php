<?php
/**
 * Frontend Actions
 * Handles frontend actions for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Frontend_Actions
 *
 * Handles frontend actions for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Frontend_Actions {
    
    private $email_functions_instance; // Adicionado

    /**
     * Constructor
     */
    public function __construct() {
        add_action('template_redirect', array($this, 'handle_frontend_appointment_actions'));
        add_action('wp', array($this, 'maybe_display_frontend_messages'));

        if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) { // Instanciar Email Functions
            $this->email_functions_instance = new VitaPro_Appointments_FSE_Email_Functions();
        }
    }

    /**
     * Handle frontend appointment actions (like cancellation).
     */
    public function handle_frontend_appointment_actions() {
        // Removido: toda lógica de cancelamento deve ser via AJAX
    }

    /**
     * Maybe display frontend messages.
     */
    public function maybe_display_frontend_messages() {
        if (isset($_GET['vpa_message'])) {
            add_filter('the_content', array($this, 'display_cancellation_message_in_content'));
        }
    }

    /**
     * Display cancellation message in content.
     */
    public function display_cancellation_message_in_content($content) {
        if (!isset($_GET['vpa_message'])) {
            return $content;
        }

        $message_type = sanitize_text_field($_GET['vpa_message']);
        $message = '';

        switch ($message_type) {
            case 'appointment_cancelled':
                $message = '<div class="vpa-message vpa-message-success">';
                $message .= '<p>' . __('Your appointment has been successfully cancelled. You will receive a confirmation email shortly.', 'vitapro-appointments-fse') . '</p>';
                $message .= '</div>';
                break;
        }

        return $message . $content;
    }
}