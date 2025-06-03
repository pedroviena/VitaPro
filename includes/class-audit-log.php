<?php
/**
 * Class VitaPro_Appointments_FSE_Audit_Log
 *
 * Handles comprehensive audit logging and monitoring for VitaPro Appointments FSE.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class VitaPro_Appointments_FSE_Audit_Log {
    
    private $log_levels = array(
        'emergency' => 0,
        'alert' => 1,
        'critical' => 2,
        'error' => 3,
        'warning' => 4,
        'notice' => 5,
        'info' => 6,
        'debug' => 7
    );
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'init_audit_system'));
        
        // Hook into various actions for logging
        add_action('vitapro_appointment_created', array($this, 'log_appointment_created'));
        add_action('vitapro_appointment_updated', array($this, 'log_appointment_updated'), 10, 2);
        add_action('vitapro_appointment_deleted', array($this, 'log_appointment_deleted'));
        add_action('vitapro_appointment_status_changed', array($this, 'log_status_change'), 10, 3);
        
        // User actions
        add_action('wp_login', array($this, 'log_user_login'), 10, 2);
        add_action('wp_logout', array($this, 'log_user_logout')); // Corrigido para chamar um método que existe
        add_action('wp_login_failed', array($this, 'log_failed_login'));
        
        // Admin actions
        add_action('admin_init', array($this, 'track_admin_actions')); // Agora o método existe
        
        // Settings changes
        add_action('update_option', array($this, 'log_settings_change'), 10, 3);
        
        // File operations (Exemplo, pode não ser o hook exato que você precisa)
        // add_action('wp_handle_upload', array($this, 'log_file_upload')); // Você precisaria criar este método
        
        // AJAX handlers for audit log viewer
        add_action('wp_ajax_vpa_get_audit_logs', array($this, 'get_audit_logs_ajax_handler')); // Renomeado para clareza
        add_action('wp_ajax_vpa_export_audit_logs', array($this, 'export_audit_logs_ajax_handler')); // Renomeado
        add_action('wp_ajax_vpa_clear_audit_logs', array($this, 'clear_audit_logs_ajax_handler')); // Renomeado
        
        // Scheduled cleanup
        add_action('vpa_cleanup_audit_logs_hook', array($this, 'cleanup_old_logs')); // Nome do hook consistente
        
        // Real-time monitoring
        add_action('wp_ajax_vpa_get_live_activity', array($this, 'get_live_activity_ajax_handler')); // Renomeado
        // add_action('wp_ajax_nopriv_vpa_get_live_activity', array($this, 'get_live_activity_ajax_handler')); // Geralmente _nopriv não é para admin
    }

    /**
     * NOVO MÉTODO ADICIONADO PARA CORRIGIR O ERRO
     * Implemente a lógica de rastreamento de ações do administrador aqui.
     */
    public function track_admin_actions() {
        if (!current_user_can('manage_options')) {
            return;
        }

        // Evitar múltiplos hooks
        static $hooks_registered = false;
        if ($hooks_registered) {
            return;
        }
        $hooks_registered = true;

        // CPTs do plugin
        $plugin_cpts = array('vpa_service', 'vpa_professional', 'vpa_appointment', 'vpa_holiday');

        // Log criação/atualização de posts dos CPTs
        add_action('save_post', function($post_id, $post, $update) use ($plugin_cpts) {
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
            if (!in_array($post->post_type, $plugin_cpts, true)) return;

            $action = $update ? 'update' : 'create';
            $object_type = $post->post_type;
            $user_id = get_current_user_id();

            $old_data = $update ? get_post($post_id, ARRAY_A) : array();
            $new_data = (array) $post;

            $this->log_event(
                $object_type . '_' . $action,
                $action,
                sprintf(
                    __('%s %s: %s', 'vitapro-appointments-fse'),
                    ucfirst($object_type),
                    $action === 'create' ? __('created', 'vitapro-appointments-fse') : __('updated', 'vitapro-appointments-fse'),
                    $post->post_title
                ),
                array(
                    'category' => $object_type,
                    'severity' => 'info',
                    'user_id' => $user_id,
                    'object_type' => $object_type,
                    'object_id' => $post_id,
                    'old_values' => $old_data,
                    'new_values' => $new_data,
                )
            );
        }, 10, 3);

        // Log exclusão de posts dos CPTs
        add_action('before_delete_post', function($post_id) use ($plugin_cpts) {
            $post = get_post($post_id);
            if (!$post || !in_array($post->post_type, $plugin_cpts, true)) return;

            $object_type = $post->post_type;
            $user_id = get_current_user_id();

            $this->log_event(
                $object_type . '_deleted',
                'delete',
                sprintf(
                    __('%s deleted: %s', 'vitapro-appointments-fse'),
                    ucfirst($object_type),
                    $post->post_title
                ),
                array(
                    'category' => $object_type,
                    'severity' => 'warning',
                    'user_id' => $user_id,
                    'object_type' => $object_type,
                    'object_id' => $post_id,
                    'old_values' => (array) $post,
                )
            );
        });

        // Log mudanças de status de agendamento (CPT)
        add_action('transition_post_status', function($new_status, $old_status, $post) use ($plugin_cpts) {
            if ($post->post_type !== 'vpa_appointment' || $new_status === $old_status) return;
            $user_id = get_current_user_id();

            $this->log_event(
                'appointment_status_changed',
                'status_change',
                sprintf(
                    __('Appointment #%d status changed from %s to %s', 'vitapro-appointments-fse'),
                    $post->ID, $old_status, $new_status
                ),
                array(
                    'category' => 'appointment',
                    'severity' => $new_status === 'cancelled' ? 'warning' : 'info',
                    'user_id' => $user_id,
                    'object_type' => 'appointment',
                    'object_id' => $post->ID,
                    'old_values' => array('status' => $old_status),
                    'new_values' => array('status' => $new_status),
                )
            );
        }, 10, 3);

        // Log alterações de taxonomia dos CPTs
        add_action('set_object_terms', function($object_id, $terms, $tt_ids, $taxonomy, $append, $old_tt_ids) use ($plugin_cpts) {
            $post = get_post($object_id);
            if (!$post || !in_array($post->post_type, $plugin_cpts, true)) return;
            $user_id = get_current_user_id();

            $this->log_event(
                'taxonomy_updated',
                'update',
                sprintf(
                    __('%s taxonomy "%s" updated for %s', 'vitapro-appointments-fse'),
                    ucfirst($post->post_type),
                    $taxonomy,
                    $post->post_title
                ),
                array(
                    'category' => $post->post_type,
                    'severity' => 'info',
                    'user_id' => $user_id,
                    'object_type' => $post->post_type,
                    'object_id' => $object_id,
                    'old_values' => array('term_taxonomy_ids' => $old_tt_ids),
                    'new_values' => array('term_taxonomy_ids' => $tt_ids),
                )
            );
        }, 10, 6);

        // Log atualização de opções do plugin
        add_action('update_option', function($option, $old_value, $value) {
            if (strpos($option, 'vpa_') === 0 || strpos($option, 'vitapro_appointments') === 0) {
                $user_id = get_current_user_id();
                $this->log_event(
                    'settings_changed',
                    'update',
                    sprintf(__('Plugin setting "%s" updated', 'vitapro-appointments-fse'), $option),
                    array(
                        'category' => 'configuration',
                        'severity' => 'notice',
                        'user_id' => $user_id,
                        'object_type' => 'setting',
                        'object_id' => 0,
                        'old_values' => array($option => $old_value),
                        'new_values' => array($option => $value),
                    )
                );
            }
        }, 10, 3);

        // Log atualização de perfil de usuário
        add_action('edit_user_profile_update', function($user_id) {
            $user = get_userdata($user_id);
            $current_user = get_current_user_id();
            $this->log_event(
                'user_profile_updated',
                'update',
                sprintf(__('User profile updated: %s', 'vitapro-appointments-fse'), $user->user_login),
                array(
                    'category' => 'user',
                    'severity' => 'info',
                    'user_id' => $current_user,
                    'object_type' => 'user',
                    'object_id' => $user_id,
                )
            );
        });

        // Log login/logout já são tratados nos métodos da classe.
    }

    /**
     * Initialize audit system
     */
    public function init_audit_system() {
        // A criação da tabela agora é pública e chamada na ativação do plugin principal
        // $this->create_audit_tables(); 
        $this->schedule_cleanup();
        // $this->setup_monitoring(); // Se setup_monitoring existir e for necessário no init
    }
    
    /**
     * Create audit tables (Tornado público para ser chamado na ativação do plugin)
     */
    public function create_audit_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $audit_table = $wpdb->prefix . 'vpa_audit_log';
        $audit_sql = "CREATE TABLE IF NOT EXISTS $audit_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_category varchar(30) NOT NULL,
            severity enum('emergency','alert','critical','error','warning','notice','info','debug') DEFAULT 'info',
            user_id bigint(20),
            user_ip varchar(45),
            user_agent text,
            object_type varchar(50),
            object_id bigint(20),
            action varchar(50) NOT NULL,
            description text,
            old_values longtext,
            new_values longtext,
            metadata longtext,
            session_id varchar(255),
            request_uri text,
            referer text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY event_category (event_category),
            KEY severity (severity),
            KEY user_id (user_id),
            KEY user_ip (user_ip),
            KEY object_type (object_type),
            KEY object_id (object_id),
            KEY action (action),
            KEY created_at (created_at),
            KEY session_id (session_id)
        ) $charset_collate;";
        
        $performance_table = $wpdb->prefix . 'vpa_performance_log';
        $performance_sql = "CREATE TABLE IF NOT EXISTS $performance_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            endpoint varchar(255) NOT NULL,
            method varchar(10) NOT NULL,
            response_time float NOT NULL,
            memory_usage bigint(20),
            query_count int(11),
            query_time float,
            cache_hits int(11),
            cache_misses int(11),
            user_id bigint(20),
            user_ip varchar(45),
            status_code int(11),
            error_message text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY endpoint (endpoint),
            KEY method (method),
            KEY response_time (response_time),
            KEY user_id (user_id),
            KEY status_code (status_code),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        $health_table = $wpdb->prefix . 'vpa_system_health';
        $health_sql = "CREATE TABLE IF NOT EXISTS $health_table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            metric_name varchar(50) NOT NULL,
            metric_value float NOT NULL,
            metric_unit varchar(20),
            threshold_warning float,
            threshold_critical float,
            status enum('ok','warning','critical') DEFAULT 'ok',
            details text,
            created_at datetime NOT NULL,
            PRIMARY KEY (id),
            KEY metric_name (metric_name),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($audit_sql);
        dbDelta($performance_sql);
        dbDelta($health_sql);
    }
    
    public function log_event($event_type, $action, $description = '', $options = array()) {
        global $wpdb;
        
        $defaults = array(
            'category' => 'general',
            'severity' => 'info',
            'user_id' => get_current_user_id(),
            'object_type' => '',
            'object_id' => 0,
            'old_values' => array(),
            'new_values' => array(),
            'metadata' => array()
        );
        
        $options = wp_parse_args($options, $defaults);
        
        $table_name = $wpdb->prefix . 'vpa_audit_log';
        
        $data = array(
            'event_type' => $event_type,
            'event_category' => $options['category'],
            'severity' => $options['severity'],
            'user_id' => $options['user_id'],
            'user_ip' => $this->get_client_ip(),
            'user_agent' => isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : '',
            'object_type' => $options['object_type'],
            'object_id' => $options['object_id'],
            'action' => $action,
            'description' => $description,
            'old_values' => wp_json_encode($options['old_values']), // Usar wp_json_encode
            'new_values' => wp_json_encode($options['new_values']), // Usar wp_json_encode
            'metadata' => wp_json_encode($options['metadata']),   // Usar wp_json_encode
            'session_id' => session_id() ? session_id() : '', // Verificar se session_id() retorna algo
            'request_uri' => isset($_SERVER['REQUEST_URI']) ? esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])) : '',
            'referer' => isset($_SERVER['HTTP_REFERER']) ? esc_url_raw(wp_unslash($_SERVER['HTTP_REFERER'])) : '',
            'created_at' => current_time('mysql', 1) // GMT
        );
        
        $result = $wpdb->insert($table_name, $data); // Não precisa de formatos se todos são strings/auto
        if ($result === false) {
            error_log('VitaPro DB Error: ' . $wpdb->last_error . ' on query: ' . $wpdb->last_query);
            return false;
        }
        
        if (in_array($options['severity'], array('emergency', 'alert', 'critical'))) {
            // $this->trigger_alert($event_type, $action, $description, $options); // Método trigger_alert não definido
        }
        
        return true;
    }
    
    public function log_appointment_created($appointment_id) {
        $appointment_data = $this->get_appointment_data_for_log($appointment_id);
        $customer_name = isset($appointment_data['customer_name']) ? $appointment_data['customer_name'] : 'N/A';
        
        $this->log_event(
            'appointment_created',
            'create',
            sprintf(__('New appointment created for %s', 'vitapro-appointments-fse'), $customer_name),
            array(
                'category' => 'appointment',
                'severity' => 'info',
                'object_type' => 'appointment',
                'object_id' => $appointment_id,
                'new_values' => $appointment_data,
                'metadata' => array(
                    'service_id' => isset($appointment_data['service_id']) ? $appointment_data['service_id'] : null,
                    'professional_id' => isset($appointment_data['professional_id']) ? $appointment_data['professional_id'] : null,
                    'appointment_date' => isset($appointment_data['appointment_date']) ? $appointment_data['appointment_date'] : null,
                    'appointment_time' => isset($appointment_data['appointment_time']) ? $appointment_data['appointment_time'] : null
                )
            )
        );
    }
    
    public function log_appointment_updated($appointment_id, $old_data) {
        $new_data = $this->get_appointment_data_for_log($appointment_id);
        $changes = $this->get_data_changes($old_data, $new_data);
        
        $this->log_event(
            'appointment_updated',
            'update',
            sprintf(__('Appointment #%d updated', 'vitapro-appointments-fse'), $appointment_id),
            array(
                'category' => 'appointment',
                'severity' => 'info',
                'object_type' => 'appointment',
                'object_id' => $appointment_id,
                'old_values' => $old_data,
                'new_values' => $new_data,
                'metadata' => array(
                    'changes' => $changes,
                    'change_count' => count($changes)
                )
            )
        );
    }

    // Adicionar método log_appointment_deleted se necessário
    public function log_appointment_deleted($appointment_id) {
        $this->log_event(
            'appointment_deleted',
            'delete',
            sprintf(__('Appointment #%d deleted', 'vitapro-appointments-fse'), $appointment_id),
            array(
                'category' => 'appointment',
                'severity' => 'warning',
                'object_type' => 'appointment',
                'object_id' => $appointment_id
            )
        );
    }
    
    public function log_status_change($appointment_id, $new_status, $old_status) {
        $this->log_event(
            'appointment_status_changed',
            'status_change',
            sprintf(__('Appointment #%d status changed from %s to %s', 'vitapro-appointments-fse'), $appointment_id, $old_status, $new_status),
            array(
                'category' => 'appointment',
                'severity' => $new_status === 'cancelled' ? 'warning' : 'info',
                'object_type' => 'appointment',
                'object_id' => $appointment_id,
                'old_values' => array('status' => $old_status),
                'new_values' => array('status' => $new_status),
                'metadata' => array(
                    'status_change' => array(
                        'from' => $old_status,
                        'to' => $new_status
                    )
                )
            )
        );
    }
    
    public function log_user_login($user_login, $user) {
        if (!$user instanceof WP_User) return; // Adicionar verificação
        $this->log_event(
            'user_login',
            'login',
            sprintf(__('User %s logged in', 'vitapro-appointments-fse'), $user_login),
            array(
                'category' => 'authentication',
                'severity' => 'info',
                'user_id' => $user->ID,
                'object_type' => 'user',
                'object_id' => $user->ID,
                'metadata' => array(
                    'user_login' => $user_login,
                    'user_email' => $user->user_email,
                    'user_roles' => $user->roles
                )
            )
        );
    }

    // Adicionar método log_user_logout se ele for chamado por um hook 'wp_logout'
    public function log_user_logout($user_id) { // O hook wp_logout passa o user_id
        $user = get_userdata($user_id);
        if (!$user) return;

        $this->log_event(
            'user_logout',
            'logout',
            sprintf(__('User %s logged out', 'vitapro-appointments-fse'), $user->user_login),
            array(
                'category' => 'authentication',
                'severity' => 'info',
                'user_id' => $user_id,
                'object_type' => 'user',
                'object_id' => $user_id
            )
        );
    }
    
    public function log_failed_login($username) {
        $this->log_event(
            'user_login_failed',
            'login_failed',
            sprintf(__('Failed login attempt for username: %s', 'vitapro-appointments-fse'), $username),
            array(
                'category' => 'security',
                'severity' => 'warning',
                'user_id' => 0, // Usuário não autenticado
                'metadata' => array(
                    'attempted_username' => $username,
                    'ip_address' => $this->get_client_ip()
                )
            )
        );
        $this->check_brute_force_attempts($username);
    }
    
    public function log_settings_change($option_name, $old_value, $new_value) {
        if (strpos($option_name, 'vitapro_appointments') !== 0 && strpos($option_name, 'vpa_') !== 0) {
            return;
        }
        
        // Para opções grandes (como arrays ou objetos), pode ser útil mostrar apenas que mudou
        // ou um diff se for simples.
        $old_value_display = is_scalar($old_value) ? $old_value : (is_array($old_value) ? 'Array' : 'Object');
        $new_value_display = is_scalar($new_value) ? $new_value : (is_array($new_value) ? 'Array' : 'Object');


        $this->log_event(
            'settings_changed',
            'update',
            sprintf(__('Setting %s was changed', 'vitapro-appointments-fse'), $option_name),
            array(
                'category' => 'configuration',
                'severity' => 'notice',
                'object_type' => 'setting',
                // 'old_values' => array($option_name => $old_value_display), // Evitar armazenar valores potencialmente grandes
                // 'new_values' => array($option_name => $new_value_display),
                'metadata' => array(
                    'setting_name' => $option_name,
                    'changed_from' => $old_value_display, // Indicar que mudou
                    'changed_to'   => $new_value_display  // Indicar para o que mudou
                )
            )
        );
    }
        
    public function log_performance($endpoint, $method, $response_time, $options = array()) {
        global $wpdb;
        $defaults = array(
            'memory_usage' => memory_get_peak_usage(true),
            'query_count' => get_num_queries(),
            'query_time' => 0, // Você precisaria calcular isso se quisesse
            'cache_hits' => 0, // Exemplo, precisaria de um sistema de cache para rastrear
            'cache_misses' => 0, // Exemplo
            'status_code' => http_response_code() ? http_response_code() : 200, // Capturar status HTTP
            'error_message' => ''
        );
        $options = wp_parse_args($options, $defaults);
        
        $table_name = $wpdb->prefix . 'vpa_performance_log';
        $data = array(
            'endpoint' => substr($endpoint, 0, 255),
            'method' => substr($method, 0, 10),
            'response_time' => (float)$response_time,
            'memory_usage' => (int)$options['memory_usage'],
            'query_count' => (int)$options['query_count'],
            'query_time' => (float)$options['query_time'],
            'cache_hits' => (int)$options['cache_hits'],
            'cache_misses' => (int)$options['cache_misses'],
            'user_id' => get_current_user_id(),
            'user_ip' => $this->get_client_ip(),
            'status_code' => (int)$options['status_code'],
            'error_message' => $options['error_message'],
            'created_at' => current_time('mysql', 1)
        );
        return $wpdb->insert($table_name, $data);
    }
    
    public function log_health_metric($metric_name, $value, $options = array()) {
        global $wpdb;
        $defaults = array(
            'unit' => '',
            'threshold_warning' => null,
            'threshold_critical' => null,
            'details' => ''
        );
        $options = wp_parse_args($options, $defaults);
        
        $status = 'ok';
        if ($options['threshold_critical'] !== null && $value >= $options['threshold_critical']) {
            $status = 'critical';
        } elseif ($options['threshold_warning'] !== null && $value >= $options['threshold_warning']) {
            $status = 'warning';
        }
        
        $table_name = $wpdb->prefix . 'vpa_system_health';
        $data = array(
            'metric_name' => substr($metric_name, 0, 50),
            'metric_value' => (float)$value,
            'metric_unit' => substr($options['unit'], 0, 20),
            'threshold_warning' => ($options['threshold_warning'] !== null) ? (float)$options['threshold_warning'] : null,
            'threshold_critical' => ($options['threshold_critical'] !== null) ? (float)$options['threshold_critical'] : null,
            'status' => $status,
            'details' => $options['details'],
            'created_at' => current_time('mysql', 1)
        );
        $result = $wpdb->insert($table_name, $data);
        
        if ($status === 'critical') {
            // $this->trigger_health_alert($metric_name, $value, $options); // trigger_health_alert não definido
        }
        return $result;
    }
    
    public function get_audit_logs_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vpa_audit_nonce')) { // Use um nonce específico para auditoria
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_audit_log';
        
        $page = isset($_POST['page']) ? absint($_POST['page']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;
        $offset = ($page - 1) * $per_page;
        
        $filters = isset($_POST['filters']) && is_array($_POST['filters']) ? $_POST['filters'] : array();
        
        $where_conditions = array('1=1');
        $where_values = array();
        
        // Aplicar filtros (exemplos)
        if (!empty($filters['event_type'])) {
            $where_conditions[] = 'event_type = %s';
            $where_values[] = sanitize_text_field($filters['event_type']);
        }
        // Adicionar mais filtros conforme necessário
        
        $where_clause = implode(' AND ', $where_conditions);
        
        $total_query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        $total = $wpdb->get_var($wpdb->prepare($total_query, $where_values));
        
        $logs_query = "
            SELECT l.*, u.display_name as user_name 
            FROM {$table_name} l 
            LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
            WHERE {$where_clause} 
            ORDER BY l.created_at DESC 
            LIMIT %d OFFSET %d
        ";
        $logs = $wpdb->get_results($wpdb->prepare(
            $logs_query, 
            array_merge($where_values, array($per_page, $offset))
        ));
        
        $formatted_logs = array();
        foreach ($logs as $log) {
            $formatted_logs[] = array(
                'id' => $log->id,
                'event_type' => esc_html($log->event_type),
                'category' => esc_html($log->event_category),
                'severity' => esc_html($log->severity),
                'severity_color' => $this->get_severity_color($log->severity),
                'user_name' => $log->user_name ? esc_html($log->user_name) : __('System', 'vitapro-appointments-fse'),
                'user_ip' => esc_html($log->user_ip),
                'action' => esc_html($log->action),
                'description' => esc_html($log->description),
                'object_type' => esc_html($log->object_type),
                'object_id' => $log->object_id,
                'old_values' => json_decode($log->old_values, true), // Não escapar, pode ser usado em JS
                'new_values' => json_decode($log->new_values, true),
                'metadata' => json_decode($log->metadata, true),
                'created_at' => esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $log->created_at)),
                'time_ago' => esc_html(human_time_diff(strtotime($log->created_at), current_time('timestamp'))) . ' ' . __('ago', 'vitapro-appointments-fse')
            );
        }
        
        wp_send_json_success(array(
            'logs' => $formatted_logs,
            'total' => intval($total),
            'page' => $page,
            'per_page' => $per_page,
            'total_pages' => ceil($total / $per_page)
        ));
    }

    public function get_live_activity_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        if (
            !isset($_POST['nonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_POST['nonce'])), 'vpa_live_activity_nonce')
        ) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_audit_log';
        
        $last_check_timestamp = isset($_POST['last_check']) ? sanitize_text_field($_POST['last_check']) : current_time('mysql', 1);
        // Converter para formato de data do MySQL se necessário, ou garantir que last_check seja um timestamp Unix
        // $last_check_mysql_format = date('Y-m-d H:i:s', $last_check_timestamp_unix);


        $recent_logs = $wpdb->get_results($wpdb->prepare(
            "SELECT l.id, l.event_type, l.severity, l.description, l.created_at, u.display_name as user_name 
             FROM {$table_name} l 
             LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
             WHERE l.created_at > %s 
             ORDER BY l.created_at DESC 
             LIMIT 20",
            $last_check_timestamp
        ));
        
        $activity = array();
        if ($recent_logs) {
            foreach ($recent_logs as $log) {
                $activity[] = array(
                    'id' => $log->id,
                    'event_type' => esc_html($log->event_type),
                    'severity' => esc_html($log->severity),
                    'user_name' => $log->user_name ? esc_html($log->user_name) : __('System', 'vitapro-appointments-fse'),
                    'description' => esc_html($log->description),
                    'created_at' => esc_html($log->created_at), // Ou formatar
                    'time_ago' => esc_html(human_time_diff(strtotime($log->created_at), current_time('timestamp'))) . ' ' . __('ago', 'vitapro-appointments-fse')
                );
            }
        }
        
        wp_send_json_success(array(
            'activity' => $activity,
            'last_check' => current_time('mysql', 1) // Retorna o tempo atual para o próximo request
        ));
    }

    // Outros handlers AJAX (export_audit_logs_ajax_handler, clear_audit_logs_ajax_handler) precisam ser implementados
    public function export_audit_logs_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        // Implementar lógica de exportação
        wp_send_json_error('Not implemented yet');
    }

    public function clear_audit_logs_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        // Implementar lógica para limpar logs (com confirmação!)
        wp_send_json_error('Not implemented yet');
    }

    private function check_brute_force_attempts($username) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_audit_log';
        $ip = $this->get_client_ip();
        $time_window = current_time('mysql', 1); // GMT
        $time_window_past = date('Y-m-d H:i:s', strtotime($time_window . ' -15 minutes'));
        
        $failed_attempts = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_name} 
             WHERE event_type = 'user_login_failed' 
             AND user_ip = %s 
             AND created_at > %s",
            $ip,
            $time_window_past
        ));
        
        if ($failed_attempts >= 5) { // Limite de tentativas
            $this->log_event(
                'brute_force_detected',
                'security_alert',
                sprintf(__('Brute force attack detected from IP %s for username %s', 'vitapro-appointments-fse'), $ip, $username),
                array(
                    'category' => 'security',
                    'severity' => 'critical',
                    'metadata' => array(
                        'ip_address' => $ip,
                        'failed_attempts' => $failed_attempts,
                        'attempted_username' => $username
                    )
                )
            );
            // Aqui você poderia adicionar o IP à lista de bloqueio
            // if(class_exists('VitaPro_Appointments_FSE_Security')) {
            // $security = new VitaPro_Appointments_FSE_Security();
            // $security->block_ip($ip, 'Brute force attempt', 3600); // Bloquear por 1 hora
            // }
        }
    }
        
    private function get_severity_color($severity) {
        $colors = array(
            'emergency' => '#D32F2F', 'alert' => '#E53935', 'critical' => '#F44336',
            'error' => '#FB8C00', 'warning' => '#FFB300', 'notice' => '#1E88E5',
            'info' => '#43A047', 'debug' => '#757575'
        );
        return isset($colors[$severity]) ? $colors[$severity] : '#9E9E9E';
    }
    
    private function get_data_changes($old_data, $new_data) {
        $changes = array();
        if (!is_array($old_data)) $old_data = array();
        if (!is_array($new_data)) $new_data = array();

        $all_keys = array_unique(array_merge(array_keys($old_data), array_keys($new_data)));
        
        foreach ($all_keys as $key) {
            $old_val = isset($old_data[$key]) ? $old_data[$key] : null;
            $new_val = isset($new_data[$key]) ? $new_data[$key] : null;

            if ($old_val !== $new_val) {
                $changes[$key] = array(
                    'old' => is_scalar($old_val) ? $old_val : wp_json_encode($old_val),
                    'new' => is_scalar($new_val) ? $new_val : wp_json_encode($new_val)
                );
            }
        }
        return $changes;
    }
    
    private function get_client_ip() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_REAL_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');
        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return isset($_SERVER['REMOTE_ADDR']) ? sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR'])) : '0.0.0.0';
    }
    
    private function get_appointment_data_for_log($appointment_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        $appointment = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $appointment_id), ARRAY_A);

        if (!$appointment) {
            return array('id' => $appointment_id, 'error' => 'Data not found');
        }
        return $appointment;
    }
    
    private function schedule_cleanup() {
        if (!wp_next_scheduled('vpa_cleanup_audit_logs_hook')) {
            wp_schedule_event(time(), 'daily', 'vpa_cleanup_audit_logs_hook');
        }
    }
    
    public function cleanup_old_logs() {
        global $wpdb;
        $retention_days = (int) get_option('vpa_audit_log_retention_days', 90);
        if ($retention_days <= 0) $retention_days = 90; // Default seguro
        
        $date_threshold = date('Y-m-d H:i:s', strtotime("-{$retention_days} days", current_time('timestamp')));

        $tables_to_clean = array(
            $wpdb->prefix . 'vpa_audit_log',
            $wpdb->prefix . 'vpa_performance_log',
            $wpdb->prefix . 'vpa_system_health'
        );
        
        foreach ($tables_to_clean as $table) {
            if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table) {
                $wpdb->query($wpdb->prepare(
                    "DELETE FROM {$table} WHERE created_at < %s",
                    $date_threshold
                ));
                error_log("VitaPro Audit Log: Cleaned old logs from table {$table} older than {$date_threshold}.");
            }
        }
    }
    
    // Outros métodos como setup_monitoring, monitor_system_resources, parse_size seriam implementados aqui
    // se fossem necessários para a lógica principal da classe de log.

} // Fim da classe VitaPro_Appointments_FSE_Audit_Log