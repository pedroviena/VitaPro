<?php
/**
 * Dashboard
 *
 * Handles the admin dashboard and analytics for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class VitaPro_Appointments_FSE_Dashboard
 *
 * Handles the admin dashboard and analytics for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Dashboard {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('admin_menu', array($this, 'add_dashboard_pages'));
        add_action('wp_ajax_vpa_dashboard_stats', array($this, 'get_dashboard_stats'));
        add_action('wp_ajax_vpa_revenue_chart', array($this, 'get_revenue_chart_data'));
        add_action('wp_ajax_vpa_appointment_trends', array($this, 'get_appointment_trends'));
        add_action('wp_ajax_vpa_professional_performance', array($this, 'get_professional_performance'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_dashboard_assets'));
    }
    
    /**
     * Add dashboard pages
     */
    public function add_dashboard_pages() {
        // Analytics submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Analytics', 'vitapro-appointments-fse'),
            __('Analytics', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-analytics',
            array($this, 'display_analytics_page')
        );
        
        // Calendar View submenu
        add_submenu_page(
            'vitapro-appointments',
            __('Calendar View', 'vitapro-appointments-fse'),
            __('Calendar View', 'vitapro-appointments-fse'),
            'manage_options',
            'vitapro-appointments-calendar',
            array($this, 'display_calendar_page')
        );
    }
    
    /**
     * Enqueue dashboard assets
     */
    public function enqueue_dashboard_assets() {
        // Chart.js local
        wp_enqueue_script(
            'vitapro-chartjs',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/vendor/chart.min.js',
            array(),
            '4.4.1', // ajuste para a versão baixada
            true
        );

        // FullCalendar local (JS e CSS)
        wp_enqueue_script(
            'vitapro-fullcalendar',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/vendor/fullcalendar.min.js',
            array('jquery'),
            '6.1.11', // ajuste para a versão baixada
            true
        );
        wp_enqueue_style(
            'vitapro-fullcalendar',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/vendor/fullcalendar.min.css',
            array(),
            '6.1.11'
        );

        // Dashboard JavaScript
        wp_enqueue_script(
            'vpa-dashboard',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/js/dashboard.js',
            array('jquery', 'chartjs'),
            VITAPRO_APPOINTMENTS_FSE_VERSION,
            true
        );
        
        // Dashboard CSS
        wp_enqueue_style(
            'vpa-dashboard',
            VITAPRO_APPOINTMENTS_FSE_URL . 'assets/css/dashboard.css',
            array(),
            VITAPRO_APPOINTMENTS_FSE_VERSION
        );
        
        // Localize script
        wp_localize_script('vpa-dashboard', 'vpaAdmin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('vpa_dashboard_nonce'),
            'strings' => array(
                'loading' => __('Loading...', 'vitapro-appointments-fse'),
                'error' => __('Error loading data', 'vitapro-appointments-fse'),
                'no_data' => __('No data available', 'vitapro-appointments-fse'),
            )
        ));
    }
    
    /**
     * Display analytics page
     */
    public function display_analytics_page() {
        ?>
        <div class="wrap vpa-analytics-page">
            <h1 class="vpa-page-title">
                <span class="dashicons dashicons-chart-area"></span>
                <?php _e('Analytics Dashboard', 'vitapro-appointments-fse'); ?>
            </h1>
            
            <!-- Date Range Selector -->
            <div class="vpa-date-range-selector">
                <label for="vpa-date-range"><?php _e('Date Range:', 'vitapro-appointments-fse'); ?></label>
                <select id="vpa-date-range">
                    <option value="7"><?php _e('Last 7 days', 'vitapro-appointments-fse'); ?></option>
                    <option value="30" selected><?php _e('Last 30 days', 'vitapro-appointments-fse'); ?></option>
                    <option value="90"><?php _e('Last 90 days', 'vitapro-appointments-fse'); ?></option>
                    <option value="365"><?php _e('Last year', 'vitapro-appointments-fse'); ?></option>
                    <option value="custom"><?php _e('Custom range', 'vitapro-appointments-fse'); ?></option>
                </select>
                
                <div id="vpa-custom-date-range" style="display: none;">
                    <input type="date" id="vpa-start-date" />
                    <span><?php _e('to', 'vitapro-appointments-fse'); ?></span>
                    <input type="date" id="vpa-end-date" />
                    <button type="button" id="vpa-apply-date-range" class="button"><?php _e('Apply', 'vitapro-appointments-fse'); ?></button>
                </div>
            </div>
            
            <!-- KPI Cards -->
            <div class="vpa-kpi-grid">
                <div class="vpa-kpi-card vpa-kpi-appointments">
                    <div class="vpa-kpi-icon">
                        <span class="dashicons dashicons-calendar-alt"></span>
                    </div>
                    <div class="vpa-kpi-content">
                        <div class="vpa-kpi-value" id="total-appointments">-</div>
                        <div class="vpa-kpi-label"><?php _e('Total Appointments', 'vitapro-appointments-fse'); ?></div>
                        <div class="vpa-kpi-change" id="appointments-change">-</div>
                    </div>
                </div>
                
                <div class="vpa-kpi-card vpa-kpi-revenue">
                    <div class="vpa-kpi-icon">
                        <span class="dashicons dashicons-money-alt"></span>
                    </div>
                    <div class="vpa-kpi-content">
                        <div class="vpa-kpi-value" id="total-revenue">-</div>
                        <div class="vpa-kpi-label"><?php _e('Total Revenue', 'vitapro-appointments-fse'); ?></div>
                        <div class="vpa-kpi-change" id="revenue-change">-</div>
                    </div>
                </div>
                
                <div class="vpa-kpi-card vpa-kpi-conversion">
                    <div class="vpa-kpi-icon">
                        <span class="dashicons dashicons-chart-line"></span>
                    </div>
                    <div class="vpa-kpi-content">
                        <div class="vpa-kpi-value" id="conversion-rate">-</div>
                        <div class="vpa-kpi-label"><?php _e('Conversion Rate', 'vitapro-appointments-fse'); ?></div>
                        <div class="vpa-kpi-change" id="conversion-change">-</div>
                    </div>
                </div>
                
                <div class="vpa-kpi-card vpa-kpi-satisfaction">
                    <div class="vpa-kpi-icon">
                        <span class="dashicons dashicons-star-filled"></span>
                    </div>
                    <div class="vpa-kpi-content">
                        <div class="vpa-kpi-value" id="satisfaction-score">-</div>
                        <div class="vpa-kpi-label"><?php _e('Satisfaction Score', 'vitapro-appointments-fse'); ?></div>
                        <div class="vpa-kpi-change" id="satisfaction-change">-</div>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row 1 -->
            <div class="vpa-charts-row">
                <div class="vpa-chart-container vpa-chart-large">
                    <div class="vpa-chart-header">
                        <h3><?php _e('Appointment Trends', 'vitapro-appointments-fse'); ?></h3>
                        <div class="vpa-chart-controls">
                            <button type="button" class="button" data-chart-type="line"><?php _e('Line', 'vitapro-appointments-fse'); ?></button>
                            <button type="button" class="button" data-chart-type="bar"><?php _e('Bar', 'vitapro-appointments-fse'); ?></button>
                        </div>
                    </div>
                    <canvas id="appointments-trend-chart"></canvas>
                </div>
                
                <div class="vpa-chart-container vpa-chart-medium">
                    <div class="vpa-chart-header">
                        <h3><?php _e('Service Distribution', 'vitapro-appointments-fse'); ?></h3>
                    </div>
                    <canvas id="services-distribution-chart"></canvas>
                </div>
            </div>
            
            <!-- Charts Row 2 -->
            <div class="vpa-charts-row">
                <div class="vpa-chart-container vpa-chart-medium">
                    <div class="vpa-chart-header">
                        <h3><?php _e('Revenue by Month', 'vitapro-appointments-fse'); ?></h3>
                    </div>
                    <canvas id="revenue-chart"></canvas>
                </div>
                
                <div class="vpa-chart-container vpa-chart-medium">
                    <div class="vpa-chart-header">
                        <h3><?php _e('Professional Performance', 'vitapro-appointments-fse'); ?></h3>
                    </div>
                    <canvas id="professional-performance-chart"></canvas>
                </div>
            </div>
            
            <!-- Detailed Tables -->
            <div class="vpa-tables-row">
                <div class="vpa-table-container">
                    <h3><?php _e('Top Services', 'vitapro-appointments-fse'); ?></h3>
                    <table class="wp-list-table widefat fixed striped" id="top-services-table">
                        <thead>
                            <tr>
                                <th><?php _e('Service', 'vitapro-appointments-fse'); ?></th>
                                <th><?php _e('Bookings', 'vitapro-appointments-fse'); ?></th>
                                <th><?php _e('Revenue', 'vitapro-appointments-fse'); ?></th>
                                <th><?php _e('Avg. Rating', 'vitapro-appointments-fse'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="top-services-body">
                            <tr><td colspan="4"><?php _e('Loading...', 'vitapro-appointments-fse'); ?></td></tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="vpa-table-container">
                    <h3><?php _e('Professional Rankings', 'vitapro-appointments-fse'); ?></h3>
                    <table class="wp-list-table widefat fixed striped" id="professional-rankings-table">
                        <thead>
                            <tr>
                                <th><?php _e('Professional', 'vitapro-appointments-fse'); ?></th>
                                <th><?php _e('Appointments', 'vitapro-appointments-fse'); ?></th>
                                <th><?php _e('Revenue', 'vitapro-appointments-fse'); ?></th>
                                <th><?php _e('Rating', 'vitapro-appointments-fse'); ?></th>
                            </tr>
                        </thead>
                        <tbody id="professional-rankings-body">
                            <tr><td colspan="4"><?php _e('Loading...', 'vitapro-appointments-fse'); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Display calendar page
     */
    public function display_calendar_page() {
        ?>
        <div class="wrap vpa-calendar-page">
            <h1 class="vpa-page-title">
                <span class="dashicons dashicons-calendar"></span>
                <?php _e('Calendar View', 'vitapro-appointments-fse'); ?>
            </h1>
            
            <!-- Calendar Controls -->
            <div class="vpa-calendar-controls">
                <div class="vpa-calendar-filters">
                    <label for="vpa-filter-professional"><?php _e('Professional:', 'vitapro-appointments-fse'); ?></label>
                    <select id="vpa-filter-professional">
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
                    
                    <label for="vpa-filter-service"><?php _e('Service:', 'vitapro-appointments-fse'); ?></label>
                    <select id="vpa-filter-service">
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
                    
                    <label for="vpa-filter-status"><?php _e('Status:', 'vitapro-appointments-fse'); ?></label>
                    <select id="vpa-filter-status">
                        <option value=""><?php _e('All Statuses', 'vitapro-appointments-fse'); ?></option>
                        <option value="pending"><?php _e('Pending', 'vitapro-appointments-fse'); ?></option>
                        <option value="confirmed"><?php _e('Confirmed', 'vitapro-appointments-fse'); ?></option>
                        <option value="completed"><?php _e('Completed', 'vitapro-appointments-fse'); ?></option>
                        <option value="cancelled"><?php _e('Cancelled', 'vitapro-appointments-fse'); ?></option>
                    </select>
                </div>
                
                <div class="vpa-calendar-actions">
                    <button type="button" id="vpa-add-appointment" class="button button-primary">
                        <span class="dashicons dashicons-plus"></span>
                        <?php _e('Add Appointment', 'vitapro-appointments-fse'); ?>
                    </button>
                    
                    <button type="button" id="vpa-export-calendar" class="button">
                        <span class="dashicons dashicons-download"></span>
                        <?php _e('Export', 'vitapro-appointments-fse'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Calendar Container -->
            <div id="vpa-calendar-container">
                <div id="vpa-fullcalendar"></div>
            </div>
            
            <!-- Legend -->
            <div class="vpa-calendar-legend">
                <h4><?php _e('Legend', 'vitapro-appointments-fse'); ?></h4>
                <div class="vpa-legend-items">
                    <div class="vpa-legend-item">
                        <span class="vpa-legend-color vpa-status-pending"></span>
                        <span><?php _e('Pending', 'vitapro-appointments-fse'); ?></span>
                    </div>
                    <div class="vpa-legend-item">
                        <span class="vpa-legend-color vpa-status-confirmed"></span>
                        <span><?php _e('Confirmed', 'vitapro-appointments-fse'); ?></span>
                    </div>
                    <div class="vpa-legend-item">
                        <span class="vpa-legend-color vpa-status-completed"></span>
                        <span><?php _e('Completed', 'vitapro-appointments-fse'); ?></span>
                    </div>
                    <div class="vpa-legend-item">
                        <span class="vpa-legend-color vpa-status-cancelled"></span>
                        <span><?php _e('Cancelled', 'vitapro-appointments-fse'); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Appointment Modal -->
        <div id="vpa-appointment-modal" class="vpa-modal" style="display: none;">
            <div class="vpa-modal-content">
                <div class="vpa-modal-header">
                    <h3 id="vpa-modal-title"><?php _e('Appointment Details', 'vitapro-appointments-fse'); ?></h3>
                    <span class="vpa-modal-close">&times;</span>
                </div>
                <div class="vpa-modal-body" id="vpa-modal-body">
                    <!-- Content will be loaded dynamically -->
                </div>
                <div class="vpa-modal-footer">
                    <button type="button" class="button" id="vpa-modal-close-btn"><?php _e('Close', 'vitapro-appointments-fse'); ?></button>
                    <button type="button" class="button button-primary" id="vpa-modal-save-btn"><?php _e('Save', 'vitapro-appointments-fse'); ?></button>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Get dashboard stats
     */
    public function get_dashboard_stats() {
        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $total = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table");
        $confirmed = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'confirmed'");
        $pending = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'");
        $cancelled = (int)$wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'cancelled'");
        $revenue = (float)$wpdb->get_var("SELECT SUM(price) FROM $table WHERE status = 'confirmed'");
        wp_send_json_success(array(
            'total' => $total,
            'confirmed' => $confirmed,
            'pending' => $pending,
            'cancelled' => $cancelled,
            'revenue' => $revenue,
        ));
    }
    
    /**
     * Get revenue chart data
     */
    public function get_revenue_chart_data() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_dashboard_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $revenue_data = $wpdb->get_results("
            SELECT 
                DATE_FORMAT(appointment_date, '%Y-%m') as month,
                SUM(CASE WHEN status = 'completed' THEN 
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = service_id AND meta_key = '_vpa_service_price')
                    ELSE 0 END) as revenue
            FROM {$table_name} 
            WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY DATE_FORMAT(appointment_date, '%Y-%m')
            ORDER BY month
        ");
        
        $labels = array();
        $data = array();
        
        foreach ($revenue_data as $row) {
            $labels[] = date('M Y', strtotime($row->month . '-01'));
            $data[] = floatval($row->revenue);
        }
        
        wp_send_json_success(array(
            'labels' => $labels,
            'data' => $data
        ));
    }
    
    /**
     * Get appointment trends
     */
    public function get_appointment_trends() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_dashboard_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $days = intval($_POST['days']);
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $trends_data = $wpdb->get_results($wpdb->prepare("
            SELECT 
                appointment_date,
                COUNT(*) as total_appointments,
                COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_appointments,
                COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_appointments
            FROM {$table_name} 
            WHERE appointment_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
            GROUP BY appointment_date
            ORDER BY appointment_date
        ", $days));
        
        $labels = array();
        $total_data = array();
        $completed_data = array();
        $cancelled_data = array();
        
        foreach ($trends_data as $row) {
            $labels[] = date_i18n(get_option('date_format'), strtotime($row->appointment_date));
            $total_data[] = intval($row->total_appointments);
            $completed_data[] = intval($row->completed_appointments);
            $cancelled_data[] = intval($row->cancelled_appointments);
        }
        
        wp_send_json_success(array(
            'labels' => $labels,
            'datasets' => array(
                array(
                    'label' => __('Total Appointments', 'vitapro-appointments-fse'),
                    'data' => $total_data,
                    'borderColor' => '#0073aa',
                    'backgroundColor' => 'rgba(0, 115, 170, 0.1)'
                ),
                array(
                    'label' => __('Completed', 'vitapro-appointments-fse'),
                    'data' => $completed_data,
                    'borderColor' => '#00a32a',
                    'backgroundColor' => 'rgba(0, 163, 42, 0.1)'
                ),
                array(
                    'label' => __('Cancelled', 'vitapro-appointments-fse'),
                    'data' => $cancelled_data,
                    'borderColor' => '#d63638',
                    'backgroundColor' => 'rgba(214, 54, 56, 0.1)'
                )
            )
        ));
    }
    
    /**
     * Get professional performance
     */
    public function get_professional_performance() {
        if (!wp_verify_nonce($_POST['nonce'], 'vpa_dashboard_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        
        $performance_data = $wpdb->get_results("
            SELECT 
                p.post_title as professional_name,
                COUNT(a.id) as total_appointments,
                COUNT(CASE WHEN a.status = 'completed' THEN 1 END) as completed_appointments,
                SUM(CASE WHEN a.status = 'completed' THEN 
                    (SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = a.service_id AND meta_key = '_vpa_service_price')
                    ELSE 0 END) as revenue
            FROM {$table_name} a
            JOIN {$wpdb->posts} p ON a.professional_id = p.ID
            WHERE a.appointment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
            GROUP BY a.professional_id, p.post_title
            ORDER BY total_appointments DESC
            LIMIT 10
        ");
        
        $labels = array();
        $appointments_data = array();
        $revenue_data = array();
        
        foreach ($performance_data as $row) {
            $labels[] = $row->professional_name;
            $appointments_data[] = intval($row->total_appointments);
            $revenue_data[] = floatval($row->revenue);
        }
        
        wp_send_json_success(array(
            'labels' => $labels,
            'appointments' => $appointments_data,
            'revenue' => $revenue_data
        ));
    }
    
    /**
     * Get calendar events
     */
    public function get_calendar_events() {
        global $wpdb;
        $table = $wpdb->prefix . 'vpa_appointments';
        $events = array();
        $rows = $wpdb->get_results("SELECT * FROM $table WHERE status IN ('confirmed','pending')");
        foreach ($rows as $row) {
            $events[] = array(
                'id' => $row->id,
                'title' => get_the_title($row->service_id) . ' - ' . $row->customer_name,
                'start' => $row->appointment_date . 'T' . $row->appointment_time,
                'end' => $row->appointment_date . 'T' . $row->appointment_time, // Ajuste se houver duração
                'status' => $row->status,
            );
        }
        wp_send_json_success($events);
    }
}