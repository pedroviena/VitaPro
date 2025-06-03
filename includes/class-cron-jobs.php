<?php
/**
 * Cron Jobs
 * * Handles scheduled tasks for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Cron_Jobs {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Register cron hook for appointment reminders
        add_action(VITAPRO_REMINDER_CRON_HOOK, array($this, 'process_appointment_reminders'));
        
        // Hook para limpeza de agendamentos antigos (opcional, pode ser ativado nas configurações)
        // add_action('vitapro_cleanup_appointments_cron_hook', array($this, 'cleanup_old_appointments'));

        // Agendar os hooks se não estiverem agendados
        // O agendamento real dos hooks (wp_schedule_event) deve ser feito durante a ativação do plugin
        // ou em um local que não rode em cada carregamento de página, a menos que seja para garantir.
        // Por enquanto, o add_action acima apenas registra o callback para quando o hook rodar.
    }

    /**
     * Process appointment reminders.
     * Este método será o callback para o hook VITAPRO_REMINDER_CRON_HOOK.
     */
    public function process_appointment_reminders() {
        // Verificar se a funcionalidade de lembretes está ativa nas configurações do plugin
        // Esta função vitapro_appointments_get_option precisará estar acessível
        // ou você pode obter as opções diretamente com get_option() aqui.
        // Exemplo: $options = get_option('vitapro_appointments_settings');
        // $enable_reminders = isset($options['enable_reminders']) ? $options['enable_reminders'] : false;

        if (!function_exists('vitapro_appointments_get_option') || !vitapro_appointments_get_option('enable_reminders', false)) {
            return;
        }

        $reminder_lead_time = vitapro_appointments_get_option('reminder_lead_time_hours', 24);
        
        // Calculate the target time for reminders
        // Adicionando (int) para garantir que $reminder_lead_time seja um número para o cálculo
        $target_time = time() + ((int)$reminder_lead_time * 3600); 
        $target_date = date('Y-m-d', $target_time);
        $target_hour = date('H', $target_time); // Hora do dia (00-23)

        // Get appointments that need reminders
        $appointments = get_posts(array(
            'post_type'      => 'vpa_appointment',
            'posts_per_page' => -1,
            'meta_query'     => array(
                'relation' => 'AND',
                array(
                    'key'     => '_vpa_appointment_date',
                    'value'   => $target_date,
                    'compare' => '=',
                ),
                array(
                    'key'     => '_vpa_appointment_status',
                    'value'   => array('confirmed'), // Lembretes apenas para agendamentos confirmados
                    'compare' => 'IN',
                ),
                array(
                    'key'     => '_vpa_reminder_sent', // Verifica se o lembrete já foi enviado
                    'compare' => 'NOT EXISTS',
                ),
            ),
        ));

        foreach ($appointments as $appointment) {
            $appointment_time_meta = get_post_meta($appointment->ID, '_vpa_appointment_time', true);
            
            // Verificação adicional se $appointment_time_meta está vazio
            if (empty($appointment_time_meta)) {
                error_log("VitaPro Cron: Appointment ID {$appointment->ID} has no time meta.");
                continue;
            }

            $appointment_datetime_str = get_post_meta($appointment->ID, '_vpa_appointment_date', true) . ' ' . $appointment_time_meta;
            $appointment_timestamp = strtotime($appointment_datetime_str);

            // Se o horário do agendamento estiver dentro da janela de "lead time" a partir de agora
            if ($appointment_timestamp <= $target_time && $appointment_timestamp > time()) {
                // A função vitapro_send_appointment_reminder_email precisa estar acessível.
                // Se ela estiver em class-email-functions.php, essa classe precisa estar carregada
                // e a função pode ser chamada estaticamente, ou através de uma instância.
                $email_sent = false;
                if (class_exists('VitaPro_Appointments_FSE_Email_Functions')) {
                    // Se for um método estático na classe:
                    // $email_sent = VitaPro_Appointments_FSE_Email_Functions::send_appointment_reminder_email($appointment->ID);
                    // Se for um método de instância e você tem um singleton para Email_Functions:
                    // $email_handler = VitaPro_Appointments_FSE_Email_Functions::get_instance(); // Supondo um singleton
                    // $email_sent = $email_handler->send_appointment_reminder_email($appointment->ID);
                    // Por enquanto, vamos assumir que é uma função global ou acessível de outra forma
                    if (function_exists('vitapro_send_appointment_reminder_email')) {
                         $email_sent = vitapro_send_appointment_reminder_email($appointment->ID);
                    } else if (function_exists('vitapro_appointments_send_email')) { 
                        // Fallback para uma função global se a específica não existir
                        // Você precisaria de uma função específica para formatar o e-mail de lembrete
                        error_log("VitaPro Cron: vitapro_send_appointment_reminder_email function not found for Appt ID {$appointment->ID}.");
                    }

                } else {
                     error_log("VitaPro Cron: VitaPro_Appointments_FSE_Email_Functions class not found for Appt ID {$appointment->ID}.");
                }
                
                if ($email_sent) {
                    // Mark reminder as sent
                    update_post_meta($appointment->ID, '_vpa_reminder_sent', current_time('mysql'));
                    error_log("VitaPro Cron: Reminder sent for Appointment ID {$appointment->ID}.");
                } else {
                    error_log("VitaPro Cron: Failed to send reminder for Appointment ID {$appointment->ID}.");
                }
            }
        }
    }

    /**
     * Clean up old appointment data (optional).
     */
    public function cleanup_old_appointments() {
        // Esta função pode ser usada para limpar agendamentos muito antigos
        // Atualmente não agendada, mas pode ser adicionada se necessário
        
        $cleanup_days = apply_filters('vitapro_appointment_cleanup_days', 365); // 1 ano por padrão
        $cleanup_date = date('Y-m-d', strtotime('-' . (int)$cleanup_days . ' days'));
        
        $old_appointments = get_posts(array(
            'post_type'      => 'vpa_appointment',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'     => '_vpa_appointment_date',
                    'value'   => $cleanup_date,
                    'compare' => '<',
                ),
                 array(
                    'key'     => '_vpa_appointment_status', // Apenas agendamentos não ativos
                    'value'   => array('completed', 'cancelled', 'no-show'),
                    'compare' => 'IN',
                ),
            ),
        ));

        foreach ($old_appointments as $appointment) {
            // Você pode escolher deletar ou apenas atualizar o status
            // wp_delete_post($appointment->ID, true); // Deletar permanentemente
            
            // Ou apenas marcar como arquivado
            update_post_meta($appointment->ID, '_vpa_appointment_status', 'archived');
            error_log("VitaPro Cron: Archived old appointment ID {$appointment->ID}.");
        }
    }

    /**
     * Método para agendar os eventos cron (chamar na ativação do plugin)
     */
    public static function schedule_events() {
        if (!wp_next_scheduled(VITAPRO_REMINDER_CRON_HOOK)) {
            wp_schedule_event(time(), 'hourly', VITAPRO_REMINDER_CRON_HOOK); // Verificar a cada hora
        }

        // Exemplo para agendar a limpeza (se desejar que seja automática)
        // if (!wp_next_scheduled('vitapro_cleanup_appointments_cron_hook')) {
        //     wp_schedule_event(time(), 'daily', 'vitapro_cleanup_appointments_cron_hook');
        // }
    }

    /**
     * Método para des-agendar os eventos cron (chamar na desativação do plugin)
     */
    public static function unschedule_events() {
        wp_clear_scheduled_hook(VITAPRO_REMINDER_CRON_HOOK);
        // wp_clear_scheduled_hook('vitapro_cleanup_appointments_cron_hook');
    }
}