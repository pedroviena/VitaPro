<?php
/**
 * Cron Jobs
 * Handles scheduled tasks for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Cron_Jobs
 *
 * Handles scheduled tasks for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Cron_Jobs {
    
    private $email_functions_instance; // Para armazenar a instância

    /**
     * Constructor
     */
    public function __construct() {
        add_action(VITAPRO_REMINDER_CRON_HOOK, array($this, 'process_appointment_reminders'));

        // Use singleton se disponível
        if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) {
            if (method_exists('VitaPro_Appointments_FSE_Email_Functions', 'get_instance')) {
                $this->email_functions_instance = VitaPro_Appointments_FSE_Email_Functions::get_instance();
            } else {
                // Fallback: instancia diretamente (não recomendado se houver hooks no construtor)
                $this->email_functions_instance = new VitaPro_Appointments_FSE_Email_Functions();
            }
        }
    }

    /**
     * Process appointment reminders.
     */
    public function process_appointment_reminders() {
        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';

        // Usar a função helper ou get_option para buscar as configurações do plugin
        $options = get_option('vitapro_appointments_settings', array()); // Melhor usar get_option diretamente
        $enable_reminders = isset($options['enable_reminders']) ? (bool)$options['enable_reminders'] : false;

        if (!$enable_reminders) {
            return;
        }

        $reminder_lead_time_hours = isset($options['reminder_lead_time_hours']) ? (int)$options['reminder_lead_time_hours'] : 24;
        
        $target_time = current_time('timestamp') + ($reminder_lead_time_hours * 3600); 
        $target_date = date('Y-m-d', $target_time);

        $appointments = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE appointment_date = %s AND status = %s AND (ISNULL(reminder_sent) OR reminder_sent = '')",
                $target_date, 'confirmed'
            )
        );

        foreach ($appointments as $appointment) {
            $appointment_date_meta = $appointment->appointment_date;
            $appointment_time_meta = $appointment->appointment_time;
            
            if (empty($appointment_date_meta) || empty($appointment_time_meta)) {
                error_log("VitaPro Cron: Appointment ID {$appointment->id} has incomplete date/time meta.");
                continue;
            }

            $appointment_timestamp = strtotime($appointment_date_meta . ' ' . $appointment_time_meta);

            // Enviar se o agendamento está dentro da janela de 'lead time' a partir de agora, mas ainda no futuro.
            // O cron roda de hora em hora, então verificamos se o agendamento está para acontecer dentro do lead_time.
            if ($appointment_timestamp <= $target_time && $appointment_timestamp > current_time('timestamp')) {
                $email_sent = false;
                if ($this->email_functions_instance && method_exists($this->email_functions_instance, 'send_reminder_email')) {
                    $this->email_functions_instance->send_reminder_email($appointment->id);
                }
                // Atualize a coluna reminder_sent na tabela customizada
                $wpdb->update($table, array('reminder_sent' => current_time('mysql', 1)), array('id' => $appointment->id));
                error_log("VitaPro Cron: Reminder sent for Appointment ID {$appointment->id}.");
            } else {
                error_log("VitaPro Cron: Appointment ID {$appointment->id} is not within the reminder lead time.");
            }
        }
    }

    public function cleanup_old_appointments() {
        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $options = get_option('vitapro_appointments_settings', array());
        $cleanup_days = apply_filters('vitapro_appointment_cleanup_days', isset($options['cleanup_days']) ? (int)$options['cleanup_days'] : 365); 
        $cleanup_date = date('Y-m-d', strtotime('-' . $cleanup_days . ' days', current_time('timestamp')));

        // Exclui agendamentos antigos da tabela customizada
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM $table WHERE appointment_date < %s AND status IN ('completed', 'cancelled', 'no-show', 'archived')",
                $cleanup_date
            )
        );
    }

    public static function schedule_events() {
        if (!wp_next_scheduled(VITAPRO_REMINDER_CRON_HOOK)) {
            wp_schedule_event(time(), 'hourly', VITAPRO_REMINDER_CRON_HOOK);
        }
        // Adicionar outros agendamentos se necessário
    }

    public static function unschedule_events() {
        wp_clear_scheduled_hook(VITAPRO_REMINDER_CRON_HOOK);
        // Limpar outros hooks
    }
}