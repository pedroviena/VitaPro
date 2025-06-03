<?php
/**
 * Availability Logic
 * 
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
        add_action('wp_ajax_vpa_get_available_dates', array($this, 'get_available_dates'));
        add_action('wp_ajax_nopriv_vpa_get_available_dates', array($this, 'get_available_dates'));
        
        add_action('wp_ajax_vpa_get_professional_availability', array($this, 'get_professional_availability'));
        add_action('wp_ajax_nopriv_vpa_get_professional_availability', array($this, 'get_professional_availability'));
        
        add_action('wp_ajax_vpa_check_slot_availability', array($this, 'check_slot_availability'));
        add_action('wp_ajax_nopriv_vpa_check_slot_availability', array($this, 'check_slot_availability'));
    }
    
    /**
     * Get available dates for a service/professional
     */
    public function get_available_dates() {
        if (!wp_verify_nonce($_POST['nonce'], 'vitapro_appointments_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $service_id = intval($_POST['service_id']);
        $professional_id = intval($_POST['professional_id']);
        $month = sanitize_text_field($_POST['month']);
        $year = intval($_POST['year']);
        
        $available_dates = $this->calculate_available_dates($service_id, $professional_id, $month, $year);
        
        wp_send_json_success($available_dates);
    }
    
    /**
     * Get professional availability for a specific date range
     */
    public function get_professional_availability() {
        if (!wp_verify_nonce($_POST['nonce'], 'vitapro_appointments_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $professional_id = intval($_POST['professional_id']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        
        $availability = $this->get_professional_availability_range($professional_id, $start_date, $end_date);
        
        wp_send_json_success($availability);
    }
    
    /**
     * Check if a specific time slot is available
     */
    public function check_slot_availability() {
        if (!wp_verify_nonce($_POST['nonce'], 'vitapro_appointments_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $service_id = intval($_POST['service_id']);
        $professional_id = intval($_POST['professional_id']);
        $date = sanitize_text_field($_POST['date']);
        $time = sanitize_text_field($_POST['time']);
        
        $is_available = $this->is_time_slot_available($service_id, $professional_id, $date, $time);
        
        wp_send_json_success(array('available' => $is_available));
    }
    
    /**
     * Calculate available dates for a month
     */
    public function calculate_available_dates($service_id, $professional_id, $month, $year) {
        $available_dates = array();
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            
            // Skip past dates
            if (strtotime($date) < strtotime(current_time('Y-m-d'))) {
                continue;
            }
            
            $availability = $this->get_date_availability($service_id, $professional_id, $date);
            
            if ($availability['has_slots']) {
                $available_dates[] = array(
                    'date' => $date,
                    'available_slots' => $availability['available_slots'],
                    'total_slots' => $availability['total_slots'],
                    'is_holiday' => $availability['is_holiday'],
                    'is_working_day' => $availability['is_working_day']
                );
            }
        }
        
        return $available_dates;
    }
    
    /**
     * Get availability for a specific date
     */
    public function get_date_availability($service_id, $professional_id, $date) {
        $day_of_week = strtolower(date('l', strtotime($date)));
        
        // Check if it's a working day
        $working_hours = $this->get_professional_working_hours($professional_id);
        $is_working_day = isset($working_hours[$day_of_week]) && $working_hours[$day_of_week]['enabled'];
        
        // Check if it's a holiday
        $is_holiday = $this->is_holiday($professional_id, $date);
        
        if (!$is_working_day || $is_holiday) {
            return array(
                'has_slots' => false,
                'available_slots' => 0,
                'total_slots' => 0,
                'is_holiday' => $is_holiday,
                'is_working_day' => $is_working_day
            );
        }
        
        // Get time slots for the day
        $time_slots = $this->generate_time_slots($service_id, $professional_id, $date);
        $available_slots = 0;
        
        foreach ($time_slots as $slot) {
            if ($slot['available']) {
                $available_slots++;
            }
        }
        
        return array(
            'has_slots' => $available_slots > 0,
            'available_slots' => $available_slots,
            'total_slots' => count($time_slots),
            'is_holiday' => $is_holiday,
            'is_working_day' => $is_working_day
        );
    }
    
    /**
     * Generate time slots for a specific date
     */
    public function generate_time_slots($service_id, $professional_id, $date) {
        $day_of_week = strtolower(date('l', strtotime($date)));
        $working_hours = $this->get_professional_working_hours($professional_id);
        
        if (!isset($working_hours[$day_of_week]) || !$working_hours[$day_of_week]['enabled']) {
            return array();
        }
        
        $service_duration = $this->get_service_duration($service_id);
        $buffer_time = $this->get_buffer_time($professional_id);
        $time_slots = array();
        
        foreach ($working_hours[$day_of_week]['slots'] as $work_slot) {
            $start_time = strtotime($date . ' ' . $work_slot['start']);
            $end_time = strtotime($date . ' ' . $work_slot['end']);
            
            // Generate slots within this work period
            $current_time = $start_time;
            
            while ($current_time + ($service_duration * 60) <= $end_time) {
                $slot_time = date('H:i', $current_time);
                
                // Check if this slot is available
                $is_available = $this->is_time_slot_available($service_id, $professional_id, $date, $slot_time);
                
                // Check minimum advance booking time
                $min_advance_time = $this->get_minimum_advance_time();
                $slot_datetime = strtotime($date . ' ' . $slot_time);
                $current_datetime = current_time('timestamp');
                
                if ($slot_datetime <= $current_datetime + ($min_advance_time * 3600)) {
                    $is_available = false;
                }
                
                // Check maximum advance booking time
                $max_advance_time = $this->get_maximum_advance_time();
                if ($max_advance_time > 0 && $slot_datetime > $current_datetime + ($max_advance_time * 24 * 3600)) {
                    $is_available = false;
                }
                
                $time_slots[] = array(
                    'time' => $slot_time,
                    'display' => date_i18n(get_option('time_format'), $current_time),
                    'available' => $is_available,
                    'datetime' => $slot_datetime
                );
                
                $current_time += ($service_duration + $buffer_time) * 60;
            }
        }
        
        return $time_slots;
    }
    
    /**
     * Check if a time slot is available
     */
    public function is_time_slot_available($service_id, $professional_id, $date, $time) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        // Check for existing appointments
        $existing_appointments = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} 
             WHERE professional_id = %d 
             AND appointment_date = %s 
             AND appointment_time = %s 
             AND status NOT IN ('cancelled', 'no-show')",
            $professional_id,
            $date,
            $time
        ));
        
        if ($existing_appointments > 0) {
            return false;
        }
        
        // Check for overlapping appointments
        $service_duration = $this->get_service_duration($service_id);
        $slot_start = strtotime($date . ' ' . $time);
        $slot_end = $slot_start + ($service_duration * 60);
        
        $overlapping_appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT appointment_time, duration FROM {$table_name} 
             WHERE professional_id = %d 
             AND appointment_date = %s 
             AND status NOT IN ('cancelled', 'no-show')",
            $professional_id,
            $date
        ));
        
        foreach ($overlapping_appointments as $appointment) {
            $existing_start = strtotime($date . ' ' . $appointment->appointment_time);
            $existing_end = $existing_start + ($appointment->duration * 60);
            
            // Check for overlap
            if (($slot_start < $existing_end) && ($slot_end > $existing_start)) {
                return false;
            }
        }
        
        // Check professional-specific availability rules
        if (!$this->check_professional_availability_rules($professional_id, $date, $time)) {
            return false;
        }
        
        // Check service-specific availability rules
        if (!$this->check_service_availability_rules($service_id, $date, $time)) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Get professional working hours
     */
    private function get_professional_working_hours($professional_id) {
        // First check for professional-specific working hours
        $professional_hours = get_post_meta($professional_id, '_vpa_professional_working_hours', true);
        
        if (!empty($professional_hours)) {
            return $professional_hours;
        }
        
        // Fall back to global working hours
        return get_option('vitapro_appointments_working_hours', array());
    }
    
    /**
     * Check if date is a holiday
     */
    private function is_holiday($professional_id, $date) {
        global $wpdb;
        
        // Check for holidays that affect this professional
        $holidays = get_posts(array(
            'post_type' => 'vpa_holiday',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'relation' => 'AND',
                array(
                    'key' => '_vpa_holiday_start_date',
                    'value' => $date,
                    'compare' => '<=',
                    'type' => 'DATE'
                ),
                array(
                    'key' => '_vpa_holiday_end_date',
                    'value' => $date,
                    'compare' => '>=',
                    'type' => 'DATE'
                )
            )
        ));
        
        foreach ($holidays as $holiday) {
            $affected_professionals = get_post_meta($holiday->ID, '_vpa_holiday_professionals', true);
            
            // If it affects all professionals or specifically this professional
            if (empty($affected_professionals) || 
                in_array('all', $affected_professionals) || 
                in_array($professional_id, $affected_professionals)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get service duration
     */
    private function get_service_duration($service_id) {
        $duration = get_post_meta($service_id, '_vpa_service_duration', true);
        
        if (empty($duration)) {
            $general_settings = get_option('vitapro_appointments_general_settings', array());
            $duration = isset($general_settings['default_appointment_duration']) ? $general_settings['default_appointment_duration'] : 60;
        }
        
        return intval($duration);
    }
    
    /**
     * Get buffer time between appointments
     */
    private function get_buffer_time($professional_id) {
        $buffer_time = get_post_meta($professional_id, '_vpa_professional_buffer_time', true);
        
        if (empty($buffer_time)) {
            $general_settings = get_option('vitapro_appointments_general_settings', array());
            $buffer_time = isset($general_settings['default_buffer_time']) ? $general_settings['default_buffer_time'] : 0;
        }
        
        return intval($buffer_time);
    }
    
    /**
     * Get minimum advance booking time
     */
    private function get_minimum_advance_time() {
        $general_settings = get_option('vitapro_appointments_general_settings', array());
        return isset($general_settings['booking_advance_time']) ? $general_settings['booking_advance_time'] : 24;
    }
    
    /**
     * Get maximum advance booking time
     */
    private function get_maximum_advance_time() {
        $general_settings = get_option('vitapro_appointments_general_settings', array());
        return isset($general_settings['max_booking_advance_days']) ? $general_settings['max_booking_advance_days'] : 0;
    }
    
    /**
     * Check professional-specific availability rules
     */
    private function check_professional_availability_rules($professional_id, $date, $time) {
        // Check for professional-specific breaks
        $breaks = get_post_meta($professional_id, '_vpa_professional_breaks', true);
        
        if (!empty($breaks)) {
            $day_of_week = strtolower(date('l', strtotime($date)));
            
            if (isset($breaks[$day_of_week])) {
                foreach ($breaks[$day_of_week] as $break) {
                    $break_start = strtotime($date . ' ' . $break['start']);
                    $break_end = strtotime($date . ' ' . $break['end']);
                    $slot_time = strtotime($date . ' ' . $time);
                    
                    if ($slot_time >= $break_start && $slot_time < $break_end) {
                        return false;
                    }
                }
            }
        }
        
        // Check for professional-specific unavailable dates
        $unavailable_dates = get_post_meta($professional_id, '_vpa_professional_unavailable_dates', true);
        
        if (!empty($unavailable_dates) && in_array($date, $unavailable_dates)) {
            return false;
        }
        
        // Check maximum appointments per day for professional
        $max_appointments = get_post_meta($professional_id, '_vpa_professional_max_appointments_per_day', true);
        
        if (!empty($max_appointments)) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'vpa_appointments';
            
            $appointments_count = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_name} 
                 WHERE professional_id = %d 
                 AND appointment_date = %s 
                 AND status NOT IN ('cancelled', 'no-show')",
                $professional_id,
                $date
            ));
            
            if ($appointments_count >= $max_appointments) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Check service-specific availability rules
     */
    private function check_service_availability_rules($service_id, $date, $time) {
        // Check if service is available on this day of week
        $service_availability = get_post_meta($service_id, '_vpa_service_availability', true);
        
        if (!empty($service_availability)) {
            $day_of_week = strtolower(date('l', strtotime($date)));
            
            if (isset($service_availability[$day_of_week]) && !$service_availability[$day_of_week]['enabled']) {
                return false;
            }
            
            // Check service-specific time restrictions
            if (isset($service_availability[$day_of_week]['time_slots'])) {
                $slot_time = strtotime($date . ' ' . $time);
                $time_allowed = false;
                
                foreach ($service_availability[$day_of_week]['time_slots'] as $allowed_slot) {
                    $slot_start = strtotime($date . ' ' . $allowed_slot['start']);
                    $slot_end = strtotime($date . ' ' . $allowed_slot['end']);
                    
                    if ($slot_time >= $slot_start && $slot_time < $slot_end) {
                        $time_allowed = true;
                        break;
                    }
                }
                
                if (!$time_allowed) {
                    return false;
                }
            }
        }
        
        return true;
    }
    
    /**
     * Get professional availability for date range
     */
    public function get_professional_availability_range($professional_id, $start_date, $end_date) {
        $availability = array();
        $current_date = strtotime($start_date);
        $end_timestamp = strtotime($end_date);
        
        while ($current_date <= $end_timestamp) {
            $date = date('Y-m-d', $current_date);
            $day_availability = $this->get_date_availability(0, $professional_id, $date);
            
            $availability[$date] = $day_availability;
            
            $current_date = strtotime('+1 day', $current_date);
        }
        
        return $availability;
    }
    
    /**
     * Get busy time slots for a professional on a specific date
     */
    public function get_busy_slots($professional_id, $date) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $appointments = $wpdb->get_results($wpdb->prepare(
            "SELECT appointment_time, duration FROM {$table_name} 
             WHERE professional_id = %d 
             AND appointment_date = %s 
             AND status NOT IN ('cancelled', 'no-show')
             ORDER BY appointment_time",
            $professional_id,
            $date
        ));
        
        $busy_slots = array();
        
        foreach ($appointments as $appointment) {
            $start_time = strtotime($date . ' ' . $appointment->appointment_time);
            $end_time = $start_time + ($appointment->duration * 60);
            
            $busy_slots[] = array(
                'start' => date('H:i', $start_time),
                'end' => date('H:i', $end_time),
                'start_timestamp' => $start_time,
                'end_timestamp' => $end_time
            );
        }
        
        return $busy_slots;
    }
    
    /**
     * Calculate next available slot
     */
    public function get_next_available_slot($service_id, $professional_id, $preferred_date = null) {
        if (!$preferred_date) {
            $preferred_date = current_time('Y-m-d');
        }
        
        $max_days_ahead = 90; // Look up to 90 days ahead
        $current_date = strtotime($preferred_date);
        
        for ($i = 0; $i < $max_days_ahead; $i++) {
            $check_date = date('Y-m-d', $current_date);
            $time_slots = $this->generate_time_slots($service_id, $professional_id, $check_date);
            
            foreach ($time_slots as $slot) {
                if ($slot['available']) {
                    return array(
                        'date' => $check_date,
                        'time' => $slot['time'],
                        'display_date' => date_i18n(get_option('date_format'), strtotime($check_date)),
                        'display_time' => $slot['display']
                    );
                }
            }
            
            $current_date = strtotime('+1 day', $current_date);
        }
        
        return false; // No available slots found
    }
}