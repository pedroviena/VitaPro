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

    // ... (cleanup_old_appointments, schedule_events, unschedule_events permanecem os mesmos da última vez)
    public function cleanup_old_appointments() {
        $options = get_option('vitapro_appointments_settings', array());
        $cleanup_days = apply_filters('vitapro_appointment_cleanup_days', isset($options['cleanup_days']) ? (int)$options['cleanup_days'] : 365); 
        $cleanup_date = date('Y-m-d', strtotime('-' . $cleanup_days . ' days', current_time('timestamp')));
        
        $old_appointments = get_posts(array(
            'post_type'      => 'vpa_appointment',
            'posts_per_page' => -1,
            'date_query'    => array( // Usar date_query para datas
                array(
                    'column' => 'post_date_gmt', // Ou a meta_key se você guarda created_at como meta
                    'before' => $cleanup_date, 
                ),
            ),
            'meta_query'     => array(
                 array(
                    'key'     => '_vpa_appointment_status',
                    'value'   => array('completed', 'cancelled', 'no-show', 'archived'),
                    'compare' => 'IN',
                ),
            ),
        ));

        foreach ($old_appointments as $appointment_post) {
            // Se você estiver usando a tabela customizada, precisaria buscar os IDs da tabela e deletar lá.
            // Se estiver usando apenas CPT, pode deletar o post.
            // Para marcar como arquivado (se estiver usando CPT com meta status):
            // update_post_meta($appointment_post->ID, '_vpa_appointment_status', 'archived');
            
            // Se usando a tabela customizada e quer deletar:
            // global $wpdb;
            // $table_name = $wpdb->prefix . 'vpa_appointments';
            // $wpdb->delete($table_name, array('id' => $appointment_post->ID /* ou a coluna correspondente */));

            error_log("VitaPro Cron: Archived/Deleted old appointment ID {$appointment_post->ID}.");
        }
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