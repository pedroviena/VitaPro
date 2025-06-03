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
            __('Reports', 'vitapro-appointments-fse'),
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
            <h1 class="vpa-page-title">
                <span class="dashicons dashicons-chart-bar"></span>
                <?php _e('Advanced Reports', 'vitapro-appointments-fse'); ?>
            </h1>
            
            <!-- Report Builder -->
            <div class="vpa-report-builder">
                <div class="vpa-report-builder-header">
                    <h2><?php _e('Report Builder', 'vitapro-appointments-fse'); ?></h2>
                    <div class="vpa-report-actions">
                        <button type="button" id="vpa-save-report-template" class="button">
                            <span class="dashicons dashicons-saved"></span>
                            <?php _e('Save Template', 'vitapro-appointments-fse'); ?>
                        </button>
                        <button type="button" id="vpa-load-report-template" class="button">
                            <span class="dashicons dashicons-upload"></span>
                            <?php _e('Load Template', 'vitapro-appointments-fse'); ?>
                        </button>
                    </div>
                </div>
                
                <form id="vpa-report-form" class="vpa-report-form">
                    <div class="vpa-form-row">
                        <div class="vpa-form-group">
                            <label for="report-type"><?php _e('Report Type', 'vitapro-appointments-fse'); ?></label>
                            <select id="report-type" name="report_type" required>
                                <option value=""><?php _e('Select Report Type', 'vitapro-appointments-fse'); ?></option>
                                <option value="appointments"><?php _e('Appointments Report', 'vitapro-appointments-fse'); ?></option>
                                <option value="revenue"><?php _e('Revenue Report', 'vitapro-appointments-fse'); ?></option>
                                <option value="professional_performance"><?php _e('Professional Performance', 'vitapro-appointments-fse'); ?></option>
                                <option value="service_analysis"><?php _e('Service Analysis', 'vitapro-appointments-fse'); ?></option>
                                <option value="customer_insights"><?php _e('Customer Insights', 'vitapro-appointments-fse'); ?></option>
                                <option value="cancellation_analysis"><?php _e('Cancellation Analysis', 'vitapro-appointments-fse'); ?></option>
                                <option value="time_slot_utilization"><?php _e('Time Slot Utilization', 'vitapro-appointments-fse'); ?></option>
                                <option value="custom"><?php _e('Custom Report', 'vitapro-appointments-fse'); ?></option>
                            </select>
                        </div>
                        
                        <div class="vpa-form-group">
                            <label for="date-range-type"><?php _e('Date Range', 'vitapro-appointments-fse'); ?></label>
                            <select id="date-range-type" name="date_range_type">
                                <option value="last_7_days"><?php _e('Last 7 days', 'vitapro-appointments-fse'); ?></option>
                                <option value="last_30_days"><?php _e('Last 30 days', 'vitapro-appointments-fse'); ?></option>
                                <option value="last_90_days"><?php _e('Last 90 days', 'vitapro-appointments-fse'); ?></option>
                                <option value="this_month"><?php _e('This month', 'vitapro-appointments-fse'); ?></option>
                                <option value="last_month"><?php _e('Last month', 'vitapro-appointments-fse'); ?></option>
                                <option value="this_year"><?php _e('This year', 'vitapro-appointments-fse'); ?></option>
                                <option value="custom"><?php _e('Custom range', 'vitapro-appointments-fse'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="vpa-form-row" id="custom-date-range" style="display: none;">
                        <div class="vpa-form-group">
                            <label for="start-date"><?php _e('Start Date', 'vitapro-appointments-fse'); ?></label>
                            <input type="date" id="start-date" name="start_date" />
                        </div>
                        
                        <div class="vpa-form-group">
                            <label for="end-date"><?php _e('End Date', 'vitapro-appointments-fse'); ?></label>
                            <input type="date" id="end-date" name="end_date" />
                        </div>
                    </div>
                    
                    <!-- Filters Section -->
                    <div class="vpa-filters-section">
                        <h3><?php _e('Filters', 'vitapro-appointments-fse'); ?></h3>
                        
                        <div class="vpa-form-row">
                            <div class="vpa-form-group">
                                <label for="filter-professionals"><?php _e('Professionals', 'vitapro-appointments-fse'); ?></label>
                                <select id="filter-professionals" name="professionals[]" multiple>
                                    <option value=""><?php _e('All Professionals', 'vitapro-appointments-fse'); ?></option>
                                    <?php
                                    $professionals = get_posts(array(
                                        'post_type' => 'vpa_professional',
                                        'posts_per_page' => -1,
                                        'post_status' => 'publish'
                                    ));
                                    
                                    foreach ($professionals as $professional) {
                                        echo '<option value="' . esc_attr($professional->ID) . '">' . esc_html($professional->post_title) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="filter-services"><?php _e('Services', 'vitapro-appointments-fse'); ?></label>
                                <select id="filter-services" name="services[]" multiple>
                                    <option value=""><?php _e('All Services', 'vitapro-appointments-fse'); ?></option>
                                    <?php
                                    $services = get_posts(array(
                                        'post_type' => 'vpa_service',
                                        'posts_per_page' => -1,
                                        'post_status' => 'publish'
                                    ));
                                    
                                    foreach ($services as $service) {
                                        echo '<option value="' . esc_attr($service->ID) . '">' . esc_html($service->post_title) . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="filter-status"><?php _e('Status', 'vitapro-appointments-fse'); ?></label>
                                <select id="filter-status" name="status[]" multiple>
                                    <option value=""><?php _e('All Statuses', 'vitapro-appointments-fse'); ?></option>
                                    <option value="pending"><?php _e('Pending', 'vitapro-appointments-fse'); ?></option>
                                    <option value="confirmed"><?php _e('Confirmed', 'vitapro-appointments-fse'); ?></option>
                                    <option value="completed"><?php _e('Completed', 'vitapro-appointments-fse'); ?></option>
                                    <option value="cancelled"><?php _e('Cancelled', 'vitapro-appointments-fse'); ?></option>
                                    <option value="no-show"><?php _e('No Show', 'vitapro-appointments-fse'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Metrics Selection -->
                    <div class="vpa-metrics-section">
                        <h3><?php _e('Metrics to Include', 'vitapro-appointments-fse'); ?></h3>
                        
                        <div class="vpa-metrics-grid">
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="total_appointments" checked />
                                <span><?php _e('Total Appointments', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="completed_appointments" checked />
                                <span><?php _e('Completed Appointments', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="cancelled_appointments" />
                                <span><?php _e('Cancelled Appointments', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="total_revenue" checked />
                                <span><?php _e('Total Revenue', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="average_revenue" />
                                <span><?php _e('Average Revenue per Appointment', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="conversion_rate" />
                                <span><?php _e('Conversion Rate', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="cancellation_rate" />
                                <span><?php _e('Cancellation Rate', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="no_show_rate" />
                                <span><?php _e('No Show Rate', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="average_duration" />
                                <span><?php _e('Average Appointment Duration', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="peak_hours" />
                                <span><?php _e('Peak Hours Analysis', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="customer_retention" />
                                <span><?php _e('Customer Retention Rate', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="metrics[]" value="booking_lead_time" />
                                <span><?php _e('Average Booking Lead Time', 'vitapro-appointments-fse'); ?></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Visualization Options -->
                    <div class="vpa-visualization-section">
                        <h3><?php _e('Visualization Options', 'vitapro-appointments-fse'); ?></h3>
                        
                        <div class="vpa-form-row">
                            <div class="vpa-form-group">
                                <label for="chart-types"><?php _e('Chart Types', 'vitapro-appointments-fse'); ?></label>
                                <select id="chart-types" name="chart_types[]" multiple>
                                    <option value="line"><?php _e('Line Chart', 'vitapro-appointments-fse'); ?></option>
                                    <option value="bar"><?php _e('Bar Chart', 'vitapro-appointments-fse'); ?></option>
                                    <option value="pie"><?php _e('Pie Chart', 'vitapro-appointments-fse'); ?></option>
                                    <option value="doughnut"><?php _e('Doughnut Chart', 'vitapro-appointments-fse'); ?></option>
                                    <option value="area"><?php _e('Area Chart', 'vitapro-appointments-fse'); ?></option>
                                    <option value="scatter"><?php _e('Scatter Plot', 'vitapro-appointments-fse'); ?></option>
                                </select>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="grouping"><?php _e('Group Data By', 'vitapro-appointments-fse'); ?></label>
                                <select id="grouping" name="grouping">
                                    <option value="day"><?php _e('Day', 'vitapro-appointments-fse'); ?></option>
                                    <option value="week"><?php _e('Week', 'vitapro-appointments-fse'); ?></option>
                                    <option value="month"><?php _e('Month', 'vitapro-appointments-fse'); ?></option>
                                    <option value="quarter"><?php _e('Quarter', 'vitapro-appointments-fse'); ?></option>
                                    <option value="year"><?php _e('Year', 'vitapro-appointments-fse'); ?></option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="vpa-form-row">
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="include_tables" checked />
                                <span><?php _e('Include Data Tables', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="include_summary" checked />
                                <span><?php _e('Include Executive Summary', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="include_trends" />
                                <span><?php _e('Include Trend Analysis', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="include_forecasting" />
                                <span><?php _e('Include Forecasting', 'vitapro-appointments-fse'); ?></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Export Options -->
                    <div class="vpa-export-section">
                        <h3><?php _e('Export Options', 'vitapro-appointments-fse'); ?></h3>
                        
                        <div class="vpa-form-row">
                            <div class="vpa-form-group">
                                <label for="export-format"><?php _e('Export Format', 'vitapro-appointments-fse'); ?></label>
                                <select id="export-format" name="export_format">
                                    <option value="pdf"><?php _e('PDF Report', 'vitapro-appointments-fse'); ?></option>
                                    <option value="excel"><?php _e('Excel Spreadsheet', 'vitapro-appointments-fse'); ?></option>
                                    <option value="csv"><?php _e('CSV Data', 'vitapro-appointments-fse'); ?></option>
                                    <option value="html"><?php _e('HTML Report', 'vitapro-appointments-fse'); ?></option>
                                    <option value="json"><?php _e('JSON Data', 'vitapro-appointments-fse'); ?></option>
                                </select>
                            </div>
                            
                            <div class="vpa-form-group">
                                <label for="report-title"><?php _e('Report Title', 'vitapro-appointments-fse'); ?></label>
                                <input type="text" id="report-title" name="report_title" placeholder="<?php _e('Enter report title', 'vitapro-appointments-fse'); ?>" />
                            </div>
                        </div>
                        
                        <div class="vpa-form-row">
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="include_logo" checked />
                                <span><?php _e('Include Business Logo', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="include_branding" checked />
                                <span><?php _e('Include Business Branding', 'vitapro-appointments-fse'); ?></span>
                            </label>
                            
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="password_protect" />
                                <span><?php _e('Password Protect PDF', 'vitapro-appointments-fse'); ?></span>
                            </label>
                        </div>
                        
                        <div class="vpa-form-row" id="password-field" style="display: none;">
                            <div class="vpa-form-group">
                                <label for="pdf-password"><?php _e('PDF Password', 'vitapro-appointments-fse'); ?></label>
                                <input type="password" id="pdf-password" name="pdf_password" />
                            </div>
                        </div>
                    </div>
                    
                    <!-- Scheduling Options -->
                    <div class="vpa-scheduling-section">
                        <h3><?php _e('Scheduling Options', 'vitapro-appointments-fse'); ?></h3>
                        
                        <div class="vpa-form-row">
                            <label class="vpa-checkbox-label">
                                <input type="checkbox" name="schedule_report" id="schedule-report" />
                                <span><?php _e('Schedule this report to run automatically', 'vitapro-appointments-fse'); ?></span>
                            </label>
                        </div>
                        
                        <div id="scheduling-options" style="display: none;">
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="schedule-frequency"><?php _e('Frequency', 'vitapro-appointments-fse'); ?></label>
                                    <select id="schedule-frequency" name="schedule_frequency">
                                        <option value="daily"><?php _e('Daily', 'vitapro-appointments-fse'); ?></option>
                                        <option value="weekly"><?php _e('Weekly', 'vitapro-appointments-fse'); ?></option>
                                        <option value="monthly"><?php _e('Monthly', 'vitapro-appointments-fse'); ?></option>
                                        <option value="quarterly"><?php _e('Quarterly', 'vitapro-appointments-fse'); ?></option>
                                    </select>
                                </div>
                                
                                <div class="vpa-form-group">
                                    <label for="schedule-time"><?php _e('Time', 'vitapro-appointments-fse'); ?></label>
                                    <input type="time" id="schedule-time" name="schedule_time" value="09:00" />
                                </div>
                            </div>
                            
                            <div class="vpa-form-row">
                                <div class="vpa-form-group">
                                    <label for="email-recipients"><?php _e('Email Recipients', 'vitapro-appointments-fse'); ?></label>
                                    <textarea id="email-recipients" name="email_recipients" placeholder="<?php _e('Enter email addresses, one per line', 'vitapro-appointments-fse'); ?>"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="vpa-form-actions">
                        <button type="button" id="vpa-preview-report" class="button button-secondary">
                            <span class="dashicons dashicons-visibility"></span>
                            <?php _e('Preview Report', 'vitapro-appointments-fse'); ?>
                        </button>
                        
                        <button type="submit" id="vpa-generate-report" class="button button-primary">
                            <span class="dashicons dashicons-chart-bar"></span>
                            <?php _e('Generate Report', 'vitapro-appointments-fse'); ?>
                        </button>
                        
                        <button type="button" id="vpa-export-report" class="button button-secondary">
                            <span class="dashicons dashicons-download"></span>
                            <?php _e('Export Report', 'vitapro-appointments-fse'); ?>
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Report Preview/Results -->
            <div id="vpa-report-results" class="vpa-report-results" style="display: none;">
                <div class="vpa-report-header">
                    <h2 id="vpa-report-title"><?php _e('Report Results', 'vitapro-appointments-fse'); ?></h2>
                    <div class="vpa-report-meta">
                        <span id="vpa-report-date"></span>
                        <span id="vpa-report-range"></span>
                    </div>
                </div>
                
                <div id="vpa-report-content">
                    <!-- Report content will be loaded here -->
                </div>
            </div>
            
            <!-- Saved Reports -->
            <div class="vpa-saved-reports">
                <h2><?php _e('Recent Reports', 'vitapro-appointments-fse'); ?></h2>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Report Name', 'vitapro-appointments-fse'); ?></th>
                            <th><?php _e('Type', 'vitapro-appointments-fse'); ?></th>
                            <th><?php _e('Date Range', 'vitapro-appointments-fse'); ?></th>
                            <th><?php _e('Generated', 'vitapro-appointments-fse'); ?></th>
                            <th><?php _e('Actions', 'vitapro-appointments-fse'); ?></th>
                        </tr>
                    </thead>
                    <tbody id="vpa-saved-reports-list">
                        <tr>
                            <td colspan="5"><?php _e('No reports generated yet.', 'vitapro-appointments-fse'); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
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