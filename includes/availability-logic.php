<?php
/**
 * Availability calculation logic.
 *
 * @package VitaPro_Appointments_FSE
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Calculate available time slots for a given date, service, and professional.
 */
function vitapro_calculate_available_slots( $date_str, $service_id, $professional_id, $duration_needed ) {
    $available_slots = array();
    
    // Validate date
    $date = DateTime::createFromFormat( 'Y-m-d', $date_str );
    if ( ! $date ) {
        return $available_slots;
    }

    $day_of_week = strtolower( $date->format( 'l' ) );
    
    // Check if it's a holiday
    $holidays = get_posts( array(
        'post_type'      => 'vpa_holiday',
        'posts_per_page' => -1,
        'meta_query'     => array(
            'relation' => 'OR',
            array(
                'key'     => '_vpa_holiday_date',
                'value'   => $date_str,
                'compare' => '=',
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
                    'value'   => $date->format( 'm-d' ),
                    'compare' => 'LIKE',
                ),
            ),
        ),
    ) );

    if ( ! empty( $holidays ) ) {
        return $available_slots; // No slots available on holidays
    }

    // Get professional's schedule
    $schedule = array();
    if ( $professional_id ) {
        $professional_schedule = get_post_meta( $professional_id, '_vpa_professional_schedule', true );
        if ( is_array( $professional_schedule ) && isset( $professional_schedule[ $day_of_week ] ) ) {
            $schedule = $professional_schedule[ $day_of_week ];
        }
    }

    // If no professional specified or no schedule found, use default hours
    if ( empty( $schedule ) || ! isset( $schedule['working'] ) || ! $schedule['working'] ) {
        $schedule = array(
            'working'     => true,
            'start'       => vitapro_appointments_get_option( 'default_opening_time', '09:00' ),
            'end'         => vitapro_appointments_get_option( 'default_closing_time', '17:00' ),
            'break_start' => '',
            'break_end'   => '',
        );
    }

    if ( ! $schedule['working'] ) {
        return $available_slots; // Professional doesn't work on this day
    }

    // Check for custom days off
    if ( $professional_id ) {
        $custom_days_off = get_post_meta( $professional_id, '_vpa_professional_custom_days_off', true );
        if ( is_array( $custom_days_off ) ) {
            foreach ( $custom_days_off as $day_off ) {
                if ( isset( $day_off['date'] ) && $day_off['date'] === $date_str ) {
                    return $available_slots; // Professional has this day off
                }
            }
        }
    }

    // Get time slot interval
    $interval = vitapro_appointments_get_option( 'time_slot_interval', 30 );
    
    // Get service buffer time
    $buffer_time = get_post_meta( $service_id, '_vpa_service_buffer_time', true );
    if ( ! $buffer_time ) {
        $buffer_time = 0;
    }

    $total_duration = $duration_needed + $buffer_time;

    // Generate time slots
    $start_time = DateTime::createFromFormat( 'H:i', $schedule['start'] );
    $end_time = DateTime::createFromFormat( 'H:i', $schedule['end'] );
    $break_start = ! empty( $schedule['break_start'] ) ? DateTime::createFromFormat( 'H:i', $schedule['break_start'] ) : null;
    $break_end = ! empty( $schedule['break_end'] ) ? DateTime::createFromFormat( 'H:i', $schedule['break_end'] ) : null;

    $current_time = clone $start_time;
    
    while ( $current_time < $end_time ) {
        $slot_end_time = clone $current_time;
        $slot_end_time->add( new DateInterval( 'PT' . $total_duration . 'M' ) );

        // Check if slot fits before end time
        if ( $slot_end_time > $end_time ) {
            break;
        }

        // Check if slot conflicts with break time
        $slot_conflicts_with_break = false;
        if ( $break_start && $break_end ) {
            if ( ( $current_time < $break_end && $slot_end_time > $break_start ) ) {
                $slot_conflicts_with_break = true;
            }
        }

        if ( ! $slot_conflicts_with_break ) {
            $slot_time = $current_time->format( 'H:i' );
            
            // Check if slot is not already booked
            if ( ! vitapro_is_slot_booked( $professional_id, $date_str, $slot_time, $total_duration ) ) {
                $available_slots[] = $slot_time;
            }
        }

        // Move to next slot
        $current_time->add( new DateInterval( 'PT' . $interval . 'M' ) );
    }

    return $available_slots;
}

/**
 * Check if a time slot is already booked.
 */
function vitapro_is_slot_booked( $professional_id, $date_str, $time_str, $duration_needed ) {
    $meta_query = array(
        'relation' => 'AND',
        array(
            'key'     => '_vpa_appointment_date',
            'value'   => $date_str,
            'compare' => '=',
        ),
        array(
            'key'     => '_vpa_appointment_status',
            'value'   => array( 'confirmed', 'pending' ),
            'compare' => 'IN',
        ),
    );

    // If professional is specified, check their bookings
    if ( $professional_id ) {
        $meta_query[] = array(
            'key'     => '_vpa_appointment_professional_id',
            'value'   => $professional_id,
            'compare' => '=',
        );
    }

    $existing_appointments = get_posts( array(
        'post_type'      => 'vpa_appointment',
        'posts_per_page' => -1,
        'meta_query'     => $meta_query,
    ) );

    if ( empty( $existing_appointments ) ) {
        return false;
    }

    $slot_start = DateTime::createFromFormat( 'H:i', $time_str );
    $slot_end = clone $slot_start;
    $slot_end->add( new DateInterval( 'PT' . $duration_needed . 'M' ) );

    foreach ( $existing_appointments as $appointment ) {
        $appointment_time = get_post_meta( $appointment->ID, '_vpa_appointment_time', true );
        $appointment_service_id = get_post_meta( $appointment->ID, '_vpa_appointment_service_id', true );
        
        $appointment_duration = get_post_meta( $appointment_service_id, '_vpa_service_duration', true );
        $appointment_buffer = get_post_meta( $appointment_service_id, '_vpa_service_buffer_time', true );
        
        if ( ! $appointment_duration ) {
            $appointment_duration = 30; // Default duration
        }
        if ( ! $appointment_buffer ) {
            $appointment_buffer = 0;
        }

        $total_appointment_duration = $appointment_duration + $appointment_buffer;

        $existing_start = DateTime::createFromFormat( 'H:i', $appointment_time );
        $existing_end = clone $existing_start;
        $existing_end->add( new DateInterval( 'PT' . $total_appointment_duration . 'M' ) );

        // Check for overlap
        if ( $slot_start < $existing_end && $slot_end > $existing_start ) {
            return true; // Slot is booked
        }
    }

    return false;
}