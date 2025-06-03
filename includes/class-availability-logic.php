<?php
/**
 * Availability Logic
 * Handles advanced availability logic for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Availability_Logic {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_vpa_get_available_dates', array($this, 'ajax_get_available_dates'));
        add_action('wp_ajax_nopriv_vpa_get_available_dates', array($this, 'ajax_get_available_dates'));
        
        add_action('wp_ajax_vpa_get_professional_availability', array($this, 'ajax_get_professional_availability'));
        add_action('wp_ajax_nopriv_vpa_get_professional_availability', array($this, 'ajax_get_professional_availability'));
        
        add_action('wp_ajax_vpa_check_slot_availability', array($this, 'ajax_check_slot_availability'));
        add_action('wp_ajax_nopriv_vpa_check_slot_availability', array($this, 'ajax_check_slot_availability'));
    }
    
    /**
     * AJAX Handler: Get available dates for a service/professional
     */
    public function ajax_get_available_dates() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'vitapro-appointments-fse')), 403);
            return;
        }
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $professional_id = isset($_POST['professional_id']) ? intval($_POST['professional_id']) : 0;
        $month_str = isset($_POST['month']) ? sanitize_text_field($_POST['month']) : date('n');
        $year_str = isset($_POST['year']) ? sanitize_text_field($_POST['year']) : date('Y');
        
        $month = intval($month_str);
        $year = intval($year_str);

        if (!$service_id || !$month || !$year ) {
            wp_send_json_error(array('message' => __('Missing parameters for getting available dates.', 'vitapro-appointments-fse')));
            return;
        }

        $available_dates_data = $this->calculate_available_dates_for_month_display($service_id, $professional_id, $month, $year);
        
        wp_send_json_success(array('available_dates' => $available_dates_data));
    }
    
    /**
     * AJAX Handler: Get professional availability for a specific date range
     */
    public function ajax_get_professional_availability() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'vitapro-appointments-fse')), 403);
            return;
        }
        
        $professional_id = isset($_POST['professional_id']) ? intval($_POST['professional_id']) : 0;
        $start_date_str = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date_str = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';

        if (!$professional_id || !$start_date_str || !$end_date_str) {
            wp_send_json_error(array('message' => __('Missing parameters for professional availability.', 'vitapro-appointments-fse')));
            return;
        }
        
        $availability = $this->get_professional_availability_range($professional_id, $start_date_str, $end_date_str);
        wp_send_json_success($availability);
    }
    
    /**
     * AJAX Handler: Check if a specific time slot is available
     */
    public function ajax_check_slot_availability() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vitapro_appointments_nonce')) {
            wp_send_json_error(array('message' => __('Security check failed', 'vitapro-appointments-fse')), 403);
            return;
        }
        
        $service_id = isset($_POST['service_id']) ? intval($_POST['service_id']) : 0;
        $professional_id = isset($_POST['professional_id']) ? intval($_POST['professional_id']) : 0;
        $date_str = isset($_POST['date']) ? sanitize_text_field($_POST['date']) : '';
        $time_str = isset($_POST['time']) ? sanitize_text_field($_POST['time']) : '';

        if (!$service_id || !$date_str || !$time_str) {
            wp_send_json_error(array('message' => __('Missing parameters for slot availability check.', 'vitapro-appointments-fse')));
            return;
        }
        
        $duration_needed = $this->get_service_duration($service_id);
        $is_available = !$this->is_slot_booked($professional_id, $date_str, $time_str, $duration_needed);
        
        wp_send_json_success(array('available' => $is_available));
    }

    /**
     * Calculate available dates for a month (for calendar display)
     * Returns an array of date strings 'Y-m-d'.
     */
    public function calculate_available_dates_for_month_display($service_id, $professional_id, $month, $year) {
        $available_dates_display = array();
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $service_duration = $this->get_service_duration($service_id);

        for ($day = 1; $day <= $days_in_month; $day++) {
            $date_str = sprintf('%04d-%02d-%02d', $year, $month, $day);
            
            if (strtotime($date_str) < strtotime(current_time('Y-m-d'))) {
                continue; 
            }
            
            $slots = $this->calculate_available_slots($date_str, $service_id, $professional_id, $service_duration);
            if (!empty($slots)) {
                $available_dates_display[] = $date_str;
            }
        }
        return $available_dates_display;
    }

    /**
     * Calculate available time slots for a given date, service, and professional.
     */
    public function calculate_available_slots( $date_str, $service_id, $professional_id, $duration_needed ) {
        $available_slots = array();
        $date_obj = DateTime::createFromFormat('Y-m-d', $date_str);
        if (!$date_obj) {
            return $available_slots;
        }
        $day_of_week = strtolower($date_obj->format('l'));

        if ($this->is_holiday_for_professional($professional_id, $date_str)) {
            return $available_slots;
        }

        $schedule = $this->get_effective_schedule_for_day($professional_id, $day_of_week);
        if (!$schedule['working']) {
            return $available_slots;
        }

        if ($this->is_custom_day_off_for_professional($professional_id, $date_str)) {
            return $available_slots;
        }

        $options = get_option('vitapro_appointments_main_settings', array()); // Use get_option consistently
        $interval = isset($options['time_slot_interval']) ? (int)$options['time_slot_interval'] : 30;
        $buffer_time = (int)get_post_meta($service_id, '_vpa_service_buffer_time', true); // Buffer específico do serviço
        $total_duration_for_slot = $duration_needed + $buffer_time;

        $start_time_obj = DateTime::createFromFormat('H:i', $schedule['start']);
        $end_time_obj = DateTime::createFromFormat('H:i', $schedule['end']);
        $break_start_obj = !empty($schedule['break_start']) ? DateTime::createFromFormat('H:i', $schedule['break_start']) : null;
        $break_end_obj = !empty($schedule['break_end']) ? DateTime::createFromFormat('H:i', $schedule['break_end']) : null;

        if (!$start_time_obj || !$end_time_obj) {
            error_log("VitaPro Availability: Invalid working hours for date {$date_str}, professional ID {$professional_id}. Schedule: " . print_r($schedule, true));
            return $available_slots;
        }

        $current_slot_start_obj = clone $start_time_obj;
        $min_advance_notice_hours = isset($options['min_advance_notice']) ? (int)$options['min_advance_notice'] : 2;
        $min_booking_timestamp = current_time('timestamp') + ($min_advance_notice_hours * 3600);
        
        $max_advance_notice_days = isset($options['max_advance_notice']) ? (int)$options['max_advance_notice'] : 90;
        $max_booking_timestamp = current_time('timestamp') + ($max_advance_notice_days * 86400);


        while (true) {
            $current_slot_end_obj = clone $current_slot_start_obj;
            $current_slot_end_obj->add(new DateInterval('PT' . $duration_needed . 'M')); 

            $slot_with_buffer_end_obj = clone $current_slot_start_obj;
            $slot_with_buffer_end_obj->add(new DateInterval('PT' . $total_duration_for_slot . 'M'));

            if ($current_slot_end_obj > $end_time_obj) {
                break;
            }

            $slot_conflicts_with_break = false;
            if ($break_start_obj && $break_end_obj) {
                if (!($current_slot_end_obj <= $break_start_obj || $current_slot_start_obj >= $break_end_obj)) {
                    $slot_conflicts_with_break = true;
                }
            }

            $slot_start_timestamp = strtotime($date_str . ' ' . $current_slot_start_obj->format('H:i'));

            $is_within_booking_window = true;
            if ($slot_start_timestamp < $min_booking_timestamp) {
                $is_within_booking_window = false; // Slot muito cedo
            }
            if ($max_advance_notice_days > 0 && $slot_start_timestamp > $max_booking_timestamp) {
                $is_within_booking_window = false; // Slot muito distante no futuro
            }


            if ($is_within_booking_window && !$slot_conflicts_with_break) {
                $slot_time_str = $current_slot_start_obj->format('H:i');
                if (!$this->is_slot_booked($professional_id, $date_str, $slot_time_str, $duration_needed)) {
                    $available_slots[] = $slot_time_str;
                }
            }
            
            if ($slot_with_buffer_end_obj > $end_time_obj && $current_slot_start_obj != $start_time_obj) {
                 break;
            }
            $current_slot_start_obj->add(new DateInterval('PT' . $interval . 'M'));
        }
        return $available_slots;
    }

    /**
     * Check if a time slot is already booked.
     */
    public function is_slot_booked( $professional_id, $date_str, $time_str, $duration_needed ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';

        $query_conditions = array(
            $wpdb->prepare("appointment_date = %s", $date_str),
            $wpdb->prepare("status IN (%s, %s)", 'confirmed', 'pending')
        );
        if ($professional_id) {
            $query_conditions[] = $wpdb->prepare("professional_id = %d", $professional_id);
        }
        
        $existing_appointments_raw = $wpdb->get_results(
            "SELECT appointment_time, duration, service_id FROM {$table_name} WHERE " . implode(" AND ", $query_conditions)
        );


        if (empty($existing_appointments_raw)) {
            return false;
        }

        $requested_slot_start_dt = DateTime::createFromFormat('Y-m-d H:i', $date_str . ' ' . $time_str);
        if (!$requested_slot_start_dt) return true; 
        
        $requested_slot_end_dt = clone $requested_slot_start_dt;
        $requested_slot_end_dt->add(new DateInterval('PT' . (int)$duration_needed . 'M'));

        foreach ($existing_appointments_raw as $appointment_obj) {
            $app_time_str = $appointment_obj->appointment_time;
            $app_service_id = $appointment_obj->service_id; // Usar service_id da tabela
            
            $app_duration_meta = (int)$this->get_service_duration($app_service_id); // Duração do serviço do agendamento existente
            $app_buffer_meta = (int)get_post_meta($app_service_id, '_vpa_service_buffer_time', true);
            $total_app_block_time = $app_duration_meta + $app_buffer_meta;

            $existing_app_start_dt = DateTime::createFromFormat('Y-m-d H:i', $date_str . ' ' . $app_time_str);
            if (!$existing_app_start_dt) continue;

            $existing_app_end_dt_with_buffer = clone $existing_app_start_dt;
            $existing_app_end_dt_with_buffer->add(new DateInterval('PT' . $total_app_block_time . 'M'));
            
            if ($requested_slot_start_dt < $existing_app_end_dt_with_buffer && $requested_slot_end_dt > $existing_app_start_dt) {
                return true; 
            }
        }
        return false;
    }

    private function get_service_duration($service_id) {
        $duration = get_post_meta($service_id, '_vpa_service_duration', true);
        if (empty($duration) || !is_numeric($duration)) {
            $options = get_option('vitapro_appointments_main_settings', array());
            return isset($options['default_appointment_duration']) ? (int)$options['default_appointment_duration'] : 60;
        }
        return (int)$duration;
    }

    private function is_holiday_for_professional($professional_id, $date_str) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $date_str);
        if (!$date_obj) return true;

        $holidays_query_args = array(
            'post_type'      => 'vpa_holiday',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'     => '_vpa_holiday_date',
                    'value'   => $date_str,
                    'compare' => '=',
                    'type'    => 'DATE',
                ),
                array(
                    'relation' => 'AND',
                    array(
                        'key'     => '_vpa_holiday_recurring',
                        'value'   => '1',
                        'compare' => '=',
                    ),
                    array(
                        'key'     => '_vpa_holiday_date',
                        'value'   => $date_obj->format('-m-d'), 
                        'compare' => 'LIKE',
                    ),
                ),
            ),
        );
        $holidays = get_posts($holidays_query_args);

        if (empty($holidays)) {
            return false;
        }

        // Verifica se o feriado afeta o profissional
        foreach ($holidays as $holiday) {
            $affected = get_post_meta($holiday->ID, '_vpa_holiday_professionals', true);
            if (empty($affected) || $affected === 'all' || (is_array($affected) && in_array($professional_id, $affected))) {
                return true;
            }
        }
        return false;
    }
    
    private function get_effective_schedule_for_day($professional_id, $day_of_week_lowercase) {
        $default_schedule = array(
            'working'     => true, 
            'start'       => '09:00',
            'end'         => '17:00',
            'break_start' => '',
            'break_end'   => '',
        );
        $options = get_option('vitapro_appointments_main_settings', array());
        if (isset($options['default_opening_time'])) $default_schedule['start'] = $options['default_opening_time'];
        if (isset($options['default_closing_time'])) $default_schedule['end'] = $options['default_closing_time'];

        if ($professional_id) {
            $professional_schedule_meta = get_post_meta($professional_id, '_vpa_professional_schedule', true);
            if (is_array($professional_schedule_meta) && isset($professional_schedule_meta[$day_of_week_lowercase])) {
                return wp_parse_args($professional_schedule_meta[$day_of_week_lowercase], $default_schedule);
            }
        }
        return $default_schedule;
    }

    private function is_custom_day_off_for_professional($professional_id, $date_str) {
        if (!$professional_id) return false;

        $custom_days_off = get_post_meta($professional_id, '_vpa_professional_custom_days_off', true);
        if (is_array($custom_days_off)) {
            foreach ($custom_days_off as $day_off) {
                if (isset($day_off['date']) && $day_off['date'] === $date_str) {
                    return true;
                }
            }
        }
        return false;
    }
    
    public function get_professional_availability_range($professional_id, $start_date_str, $end_date_str) {
        $availability = array();
        $current_dt = new DateTime($start_date_str);
        $end_dt = new DateTime($end_date_str);

        while ($current_dt <= $end_dt) {
            $date_str = $current_dt->format('Y-m-d');
            $day_of_week = strtolower($current_dt->format('l'));
            
            $is_holiday = $this->is_holiday_for_professional($professional_id, $date_str);
            $is_custom_off = $this->is_custom_day_off_for_professional($professional_id, $date_str);
            $schedule = $this->get_effective_schedule_for_day($professional_id, $day_of_week);

            $availability[$date_str] = array(
                'is_working_day' => $schedule['working'] && !$is_holiday && !$is_custom_off,
                'working_hours' => ($schedule['working'] && !$is_holiday && !$is_custom_off) ? $schedule : null,
                'is_holiday' => $is_holiday,
                'is_custom_off' => $is_custom_off
            );
            $current_dt->add(new DateInterval('P1D'));
        }
        return $availability;
    }
}