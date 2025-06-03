<?php
/**
 * Reports
 *
 * Handles reporting features for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Reports
 *
 * Handles reporting features for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Reports {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('wp_ajax_vpa_generate_report', array($this, 'generate_report'));
        add_action('wp_ajax_vpa_export_report', array($this, 'export_report'));
        add_action('wp_ajax_vpa_schedule_report', array($this, 'schedule_report'));
        add_action('wp_ajax_vpa_get_report_templates', array($this, 'get_report_templates'));
        
        // Scheduled reports
        add_action('vpa_send_scheduled_report', array($this, 'send_scheduled_report'), 10, 2);
        
        // Report page
        add_action('admin_menu', array($this, 'add_reports_page'));
    }
    
    /**
     * Add reports page
     */
    public function add_reports_page() {
        add_submenu_page(
            'vitapro-appointments',
            __('Reports - VitaPro Appointments', 'vitapro-appointments-fse'),
            __('Reports', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-reports',
            array($this, 'display_reports_page')
        );
    }
    /**
     * Display reports page
     */
    public function display_reports_page() {
        ?>
        <div class="wrap vpa-reports-page">
            <h1><?php esc_html_e('Reports', 'vitapro-appointments-fse'); ?></h1>
            <!-- Conteúdo da página de relatórios -->
        </div>
        <?php
    }
    
    /**
     * Generate report
     */
    public function generate_report() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_reports_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $report_type = sanitize_text_field($_POST['report_type']);
        $date_range_type = sanitize_text_field($_POST['date_range_type']);
        $start_date = sanitize_text_field($_POST['start_date']);
        $end_date = sanitize_text_field($_POST['end_date']);
        $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
        $metrics = isset($_POST['metrics']) ? $_POST['metrics'] : array();
        
        // Calculate date range
        $date_range = $this->calculate_date_range($date_range_type, $start_date, $end_date);
        
        // Generate report data based on type
        $report_data = $this->generate_report_data($report_type, $date_range, $filters, $metrics);
        
        // Save report to database
        $report_id = $this->save_report($report_type, $date_range, $filters, $metrics, $report_data);
        
        wp_send_json_success(array(
            'report_id' => $report_id,
            'report_data' => $report_data,
            'date_range' => $date_range
        ));
    }
    
    /**
     * Export report
     */
    public function export_report() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_reports_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $report_id = intval($_POST['report_id']);
        $export_format = sanitize_text_field($_POST['export_format']);
        $options = isset($_POST['options']) ? $_POST['options'] : array();
        
        // Get report data
        $report = $this->get_saved_report($report_id);
        
        if (!$report) {
            wp_send_json_error(__('Report not found', 'vitapro-appointments-fse'));
        }
        
        // Generate export file
        $export_file = $this->create_export_file($report, $export_format, $options);
        
        if ($export_file) {
            wp_send_json_success(array(
                'download_url' => $export_file['url'],
                'filename' => $export_file['filename']
            ));
        } else {
            wp_send_json_error(__('Failed to create export file', 'vitapro-appointments-fse'));
        }
    }
    
    /**
     * Calculate date range
     */
    private function calculate_date_range($type, $start_date = '', $end_date = '') {
        $today = current_time('Y-m-d');
        
        switch ($type) {
            case 'last_7_days':
                return array(
                    'start' => date('Y-m-d', strtotime('-7 days')),
                    'end' => $today
                );
                
            case 'last_30_days':
                return array(
                    'start' => date('Y-m-d', strtotime('-30 days')),
                    'end' => $today
                );
                
            case 'last_90_days':
                return array(
                    'start' => date('Y-m-d', strtotime('-90 days')),
                    'end' => $today
                );
                
            case 'this_month':
                return array(
                    'start' => date('Y-m-01'),
                    'end' => $today
                );
                
            case 'last_month':
                return array(
                    'start' => date('Y-m-01', strtotime('first day of last month')),
                    'end' => date('Y-m-t', strtotime('last day of last month'))
                );
                
            case 'this_year':
                return array(
                    'start' => date('Y-01-01'),
                    'end' => $today
                );
                
            case 'custom':
                return array(
                    'start' => $start_date,
                    'end' => $end_date
                );
                
            default:
                return array(
                    'start' => date('Y-m-d', strtotime('-30 days')),
                    'end' => $today
                );
        }
    }
    
    /**
     * Generate report data
     */
    private function generate_report_data($type, $date_range, $filters, $metrics) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        // Build base query
        $where_conditions = array();
        $where_values = array();
        
        // Date range
        $where_conditions[] = "appointment_date BETWEEN %s AND %s";
        $where_values[] = $date_range['start'];
        $where_values[] = $date_range['end'];
        
        // Apply filters
        if (!empty($filters['professionals'])) {
            $placeholders = implode(',', array_fill(0, count($filters['professionals']), '%d'));
            $where_conditions[] = "professional_id IN ($placeholders)";
            $where_values = array_merge($where_values, $filters['professionals']);
        }
        
        if (!empty($filters['services'])) {
            $placeholders = implode(',', array_fill(0, count($filters['services']), '%d'));
            $where_conditions[] = "service_id IN ($placeholders)";
            $where_values = array_merge($where_values, $filters['services']);
        }
        
        if (!empty($filters['status'])) {
            $placeholders = implode(',', array_fill(0, count($filters['status']), '%s'));
            $where_conditions[] = "status IN ($placeholders)";
            $where_values = array_merge($where_values, $filters['status']);
        }
        
        $where_clause = "WHERE " . implode(" AND ", $where_conditions);
        
        // Generate data based on report type
        switch ($type) {
            case 'appointments':
                return $this->generate_appointments_report($table_name, $where_clause, $where_values, $metrics);
                
            case 'revenue':
                return $this->generate_revenue_report($table_name, $where_clause, $where_values, $metrics);
                
            case 'professional_performance':
                return $this->generate_professional_performance_report($table_name, $where_clause, $where_values, $metrics);
                
            case 'service_analysis':
                return $this->generate_service_analysis_report($table_name, $where_clause, $where_values, $metrics);
                
            case 'customer_insights':
                return $this->generate_customer_insights_report($table_name, $where_clause, $where_values, $metrics);
                
            case 'cancellation_analysis':
                return $this->generate_cancellation_analysis_report($table_name, $where_clause, $where_values, $metrics);
                
            case 'time_slot_utilization':
                return $this->generate_time_slot_utilization_report($table_name, $where_clause, $where_values, $metrics);
                
            default:
                return array();
        }
    }
    
    /**
     * Generate appointments report
     */
    private function generate_appointments_report($table_name, $where_clause, $where_values, $metrics) {
        global $wpdb;
        
        $data = array();
        
        // Summary statistics
        $summary = $wpdb->get_row($wpdb->prepare(
            "SELECT 
                COUNT(*) as total_appointments,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_appointments,
                COUNT(CASE WHEN status = 'no-show' THEN 1 END) as no_show_appointments,
                AVG(duration) as average_duration
            FROM {$table_name} 
            {$where_clause}",
            $where_values
        ));
        
        $data['summary'] = $summary;
        
        // Daily breakdown
        if (in_array('daily_breakdown', $metrics)) {
            $daily_data = $wpdb->get_results($wpdb->prepare(
                "SELECT 
                    appointment_date,
                    COUNT(*) as total_appointments,
                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments,
                    COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_appointments
                FROM {$table_name} 
                {$where_clause}
                GROUP BY appointment_date
                ORDER BY appointment_date",
                $where_values
            ));
            
            $data['daily_breakdown'] = $daily_data;
        }
        
        // Status distribution
        $status_data = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                status,
                COUNT(*) as count
            FROM {$table_name} 
            {$where_clause}
            GROUP BY status",
            $where_values
        ));
        
        $data['status_distribution'] = $status_data;
        
        return $data;
    }
    
    /**
     * Save report
     */
    private function save_report($type, $date_range, $filters, $metrics, $data) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vpa_reports';
        
        // Create reports table if it doesn't exist
        $this->create_reports_table();
        
        $result = $wpdb->insert(
            $table_name,
            array(
                'report_type' => $type,
                'date_range_start' => $date_range['start'],
                'date_range_end' => $date_range['end'],
                'filters' => json_encode($filters),
                'metrics' => json_encode($metrics),
                'report_data' => json_encode($data),
                'generated_by' => get_current_user_id(),
                'generated_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    /**
     * Create reports table
     */
    public function create_reports_table() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vpa_reports';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            report_type varchar(50) NOT NULL,
            date_range_start date NOT NULL,
            date_range_end date NOT NULL,
            filters longtext,
            metrics longtext,
            report_data longtext,
            generated_by bigint(20) NOT NULL,
            generated_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY report_type (report_type),
            KEY generated_by (generated_by),
            KEY generated_at (generated_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}