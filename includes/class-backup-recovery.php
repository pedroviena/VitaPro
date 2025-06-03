<?php
/**
 * Backup and Recovery System
 * * Handles comprehensive backup and recovery functionality for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Backup_Recovery {
    
    private $backup_dir;
    private $max_backups = 10;
    
    /**
     * Constructor
     */
    public function __construct() {
        $upload_dir = wp_upload_dir();
        $this->backup_dir = $upload_dir['basedir'] . '/vpa-backups/';
        
        add_action('init', array($this, 'init_backup_system'));
        add_action('wp_ajax_vpa_create_backup', array($this, 'create_backup_ajax_handler'));
        add_action('wp_ajax_vpa_restore_backup', array($this, 'restore_backup_ajax_handler'));
        add_action('wp_ajax_vpa_download_backup', array($this, 'download_backup_ajax_handler'));
        add_action('wp_ajax_vpa_delete_backup', array($this, 'delete_backup_ajax_handler'));
        add_action('wp_ajax_vpa_get_backup_list', array($this, 'get_backup_list_ajax_handler'));
        add_action('wp_ajax_vpa_schedule_backup', array($this, 'schedule_backup_ajax_handler'));
        
        add_action('vpa_scheduled_backup_hook', array($this, 'run_scheduled_backup'));
        
        add_action('wp_ajax_vpa_upload_to_cloud', array($this, 'upload_to_cloud_ajax_handler'));
        add_action('wp_ajax_vpa_test_cloud_connection', array($this, 'test_cloud_connection_ajax_handler'));
        
        add_action('wp_ajax_vpa_verify_backup', array($this, 'verify_backup_ajax_handler'));
        
        add_action('vpa_cleanup_old_backups_hook', array($this, 'cleanup_old_backups'));
        
        add_action('vpa_process_backup_hook', array($this, 'process_backup'), 10, 2);
    }
    
    /**
     * Initialize backup system
     */
    public function init_backup_system() {
        $this->create_backup_directory();
        $this->schedule_auto_cleanup();
        // A criação da tabela de backups agora é pública e chamada na ativação do plugin principal.
        // $this->setup_backup_tables(); 
    }
    
    /**
     * Create backup directory
     */
    private function create_backup_directory() {
        if (!file_exists($this->backup_dir)) {
            wp_mkdir_p($this->backup_dir);
            
            if (!file_exists($this->backup_dir . '.htaccess')) {
                $htaccess_content = "Order deny,allow\nDeny from all\n";
                @file_put_contents($this->backup_dir . '.htaccess', $htaccess_content);
            }
            
            if (!file_exists($this->backup_dir . 'index.php')) {
                @file_put_contents($this->backup_dir . 'index.php', '<?php // Silence is golden');
            }
        }
    }
    
    /**
     * Setup backup tables (Tornado público para ser chamado na ativação do plugin)
     */
    public function setup_backup_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'vpa_backups';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            backup_name varchar(255) NOT NULL,
            backup_type enum('manual','scheduled','auto') DEFAULT 'manual',
            backup_size bigint(20) NOT NULL,
            backup_path varchar(500) NOT NULL,
            backup_hash varchar(64),
            includes_files tinyint(1) DEFAULT 0,
            includes_database tinyint(1) DEFAULT 1,
            includes_uploads tinyint(1) DEFAULT 0,
            compression_type varchar(20) DEFAULT 'zip',
            encryption_enabled tinyint(1) DEFAULT 0,
            cloud_storage varchar(50),
            cloud_path varchar(500),
            status enum('creating','completed','failed','corrupted') DEFAULT 'creating',
            error_message text,
            created_by bigint(20),
            created_at datetime NOT NULL,
            verified_at datetime,
            PRIMARY KEY (id),
            KEY backup_type (backup_type),
            KEY status (status),
            KEY created_at (created_at),
            KEY created_by (created_by)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * AJAX Handler for creating backup
     */
    public function create_backup_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vpa_backup_nonce')) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        $backup_options = array(
            'name' => isset($_POST['backup_name']) && !empty($_POST['backup_name']) ? sanitize_text_field($_POST['backup_name']) : 'Manual Backup ' . date('Y-m-d H-i-s'),
            'type' => 'manual',
            'include_files' => isset($_POST['include_files']) && $_POST['include_files'] === 'true',
            'include_database' => isset($_POST['include_database']) && $_POST['include_database'] === 'true',
            'include_uploads' => isset($_POST['include_uploads']) && $_POST['include_uploads'] === 'true',
            'compression' => isset($_POST['compression']) ? sanitize_text_field($_POST['compression']) : 'zip',
            'encryption' => isset($_POST['encryption']) && $_POST['encryption'] === 'true',
            'password' => isset($_POST['password']) ? wp_unslash($_POST['password']) : '', // Não sanitizar senha aqui, será usada para criptografia
            'cloud_storage' => isset($_POST['cloud_storage']) ? sanitize_text_field($_POST['cloud_storage']) : '',
            'description' => isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : ''
        );

        if (!$backup_options['include_files'] && !$backup_options['include_database'] && !$backup_options['include_uploads']) {
            $backup_options['include_database'] = true;
        }

        $backup_id = $this->start_backup($backup_options);
        
        if ($backup_id) {
            wp_send_json_success(array(
                'backup_id' => $backup_id,
                'message' => __('Backup process started successfully. It will run in the background.', 'vitapro-appointments-fse')
            ));
        } else {
            wp_send_json_error(__('Failed to start backup process.', 'vitapro-appointments-fse'));
        }
    }
    
    /**
     * Start backup process
     */
    private function start_backup($options) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';
        
        $backup_data = array(
            'backup_name' => $options['name'],
            'backup_type' => $options['type'],
            'backup_size' => 0,
            'backup_path' => '',
            'includes_files' => $options['include_files'] ? 1 : 0,
            'includes_database' => $options['include_database'] ? 1 : 0,
            'includes_uploads' => $options['include_uploads'] ? 1 : 0,
            'compression_type' => $options['compression'],
            'encryption_enabled' => $options['encryption'] ? 1 : 0,
            'cloud_storage' => $options['cloud_storage'],
            'status' => 'creating',
            'created_by' => get_current_user_id(),
            'created_at' => current_time('mysql', 1)
        );
        
        $result = $wpdb->insert($table_name, $backup_data);
        if ($result === false) {
            error_log('VitaPro DB Error: ' . $wpdb->last_error . ' on query: ' . $wpdb->last_query);
            return false;
        }
        
        $backup_id = $wpdb->insert_id;
        
        // Pass a cópia das opções para o agendador, pois $options pode ser modificado
        $scheduled_options = $options; 
        wp_schedule_single_event(time() + 5, 'vpa_process_backup_hook', array($backup_id, $scheduled_options));
        
        return $backup_id;
    }
    
    /**
     * Process backup (Este método será chamado pelo WP Cron)
     */
    public function process_backup($backup_id, $options) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';

        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        try {
            $backup_filename_base = 'vpa-backup-' . $backup_id . '-' . date('Y-m-d H-i-s');
            $backup_path_base = $this->backup_dir . $backup_filename_base;
            
            $temp_dir = $this->backup_dir . 'temp_backup_' . $backup_id . '_' . time() . '/';
            if (!wp_mkdir_p($temp_dir)) {
                throw new Exception(__('Could not create temporary backup directory.', 'vitapro-appointments-fse'));
            }
            
            $backup_size = 0;
            
            if ($options['include_database']) {
                $db_backup_filename = 'database.sql';
                $db_backup_path = $temp_dir . $db_backup_filename;
                $this->backup_database($db_backup_path);
                if (file_exists($db_backup_path)) {
                    $backup_size += filesize($db_backup_path);
                } else {
                     error_log("VitaPro Backup: Database backup file not created for backup ID {$backup_id}.");
                }
            }
            
            if ($options['include_files']) {
                $files_backup_dir_name = 'plugin_files';
                $files_backup_path = $temp_dir . $files_backup_dir_name . '/';
                if (wp_mkdir_p($files_backup_path)) {
                    $this->backup_plugin_files($files_backup_path);
                    $backup_size += $this->get_directory_size($files_backup_path);
                } else {
                    error_log("VitaPro Backup: Could not create plugin files backup directory for backup ID {$backup_id}.");
                }
            }
            
            if ($options['include_uploads']) {
                $uploads_backup_dir_name = 'uploads';
                $uploads_backup_path = $temp_dir . $uploads_backup_dir_name . '/';
                 if (wp_mkdir_p($uploads_backup_path)) {
                    $this->backup_uploads($uploads_backup_path);
                    $backup_size += $this->get_directory_size($uploads_backup_path);
                } else {
                    error_log("VitaPro Backup: Could not create uploads backup directory for backup ID {$backup_id}.");
                }
            }
            
            $manifest = array(
                'backup_id' => $backup_id,
                'backup_name' => $options['name'],
                'created_at' => current_time('mysql', 1),
                'wordpress_version' => get_bloginfo('version'),
                'plugin_version' => defined('VITAPRO_APPOINTMENTS_FSE_VERSION') ? VITAPRO_APPOINTMENTS_FSE_VERSION : 'N/A',
                'includes' => array(
                    'database' => $options['include_database'],
                    'files' => $options['include_files'],
                    'uploads' => $options['include_uploads']
                ),
                'site_url' => home_url(),
                'admin_email' => get_option('admin_email')
            );
            
            file_put_contents($temp_dir . 'manifest.json', json_encode($manifest, JSON_PRETTY_PRINT));
            
            $final_backup_path = $backup_path_base . '.' . $options['compression'];
            
            if ($options['compression'] === 'zip') {
                $this->create_zip_archive($temp_dir, $final_backup_path);
            } else { 
                $this->create_tar_archive($temp_dir, $final_backup_path);
            }
            
            // A senha para criptografia é passada em $options['password']
            if ($options['encryption'] && !empty($options['password'])) {
                $encrypted_path = $final_backup_path . '.enc';
                $this->encrypt_file($final_backup_path, $encrypted_path, $options['password']);
                if (file_exists($final_backup_path)) {
                    unlink($final_backup_path);
                }
                $final_backup_path = $encrypted_path;
            }
            
            $final_size = file_exists($final_backup_path) ? filesize($final_backup_path) : 0;
            $backup_hash = file_exists($final_backup_path) ? hash_file('sha256', $final_backup_path) : '';
            
            $this->delete_directory($temp_dir);
            
            $result = $wpdb->update($table_name, array('status' => 'completed'), array('id' => $backup_id));
            if ($result === false) {
                error_log("[VitaPro Backup] Failed to update backup status to 'completed'. Backup ID: $backup_id. DB Error: {$wpdb->last_error}. Query: {$wpdb->last_query}");
                throw new Exception(__('Could not update backup status in the database. Please check server logs.', 'vitapro-appointments-fse'));
            }
            
            if (!empty($options['cloud_storage'])) {
                // $this->upload_backup_to_cloud($backup_id, $final_backup_path, $options['cloud_storage']);
                error_log("VitaPro Backup: Cloud upload for backup ID {$backup_id} to {$options['cloud_storage']} - SKIPPED (not implemented).");
            }
            
            do_action('vpa_backup_completed', $backup_id, $final_backup_path);
            error_log("VitaPro Backup: Backup ID {$backup_id} completed successfully. Path: {$final_backup_path}");

        } catch (Exception $e) {
            error_log("[VitaPro Backup] Exception in process_backup (ID: $backup_id): " . $e->getMessage());
            // Opcional: atualizar status para 'failed'
            $wpdb->update($table_name, array('status' => 'failed'), array('id' => $backup_id));
            throw new Exception(__('A backup error ocorreu: ', 'vitapro-appointments-fse') . $e->getMessage());
        }
    }
    
    private function backup_database($output_file) {
        global $wpdb;
        $plugin_tables = array(
            $wpdb->prefix . 'vpa_appointments',
            $wpdb->prefix . 'vpa_audit_log',
            $wpdb->prefix . 'vpa_security_log',
            $wpdb->prefix . 'vpa_security_blocks',
            $wpdb->prefix . 'vpa_notifications',
            $wpdb->prefix . 'vpa_reports',
            $wpdb->prefix . 'vpa_backups'
        );
        $tables_to_backup = $plugin_tables;

        $backup_content = "-- VitaPro Appointments FSE Database Backup\n";
        $backup_content .= "-- Generated on: " . current_time('mysql', 1) . " (GMT)\n";
        $backup_content .= "-- WordPress Version: " . get_bloginfo('version') . "\n";
        $backup_content .= "-- Plugin Version: " . (defined('VITAPRO_APPOINTMENTS_FSE_VERSION') ? VITAPRO_APPOINTMENTS_FSE_VERSION : 'N/A') . "\n\n";
        
        $backup_content .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $backup_content .= "SET time_zone = \"+00:00\";\n\n";
        $backup_content .= "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n";
        $backup_content .= "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n";
        $backup_content .= "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n";
        $backup_content .= "/*!40101 SET NAMES utf8mb4 */;\n\n";

        foreach ($tables_to_backup as $table) {
            try {
                if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table)) === $table) {
                    $backup_content .= $this->get_table_structure_sql($table);
                    $backup_content .= $this->get_table_data_sql($table);
                }
            } catch (Exception $e) {
                error_log("[VitaPro Backup] Failed to backup table: $table. Error: " . $e->getMessage());
                throw new Exception(sprintf(__('Failed to backup table %s. See server logs for details.', 'vitapro-appointments-fse'), $table));
            }
        }
        
        $backup_content .= "\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n";
        $backup_content .= "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n";
        $backup_content .= "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";
        
        if (file_put_contents($output_file, $backup_content) === false) {
            error_log("[VitaPro Backup] Failed to write database backup file: $output_file");
            throw new Exception(__('Could not write database backup file. Check server permissions.', 'vitapro-appointments-fse'));
        }
    }

    private function get_table_structure_sql($table) {
        global $wpdb;
        $sql = "\n-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for table `{$table}`\n";
        $sql .= "--\n\n";
        $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $create_table_row = $wpdb->get_row("SHOW CREATE TABLE `{$table}`", ARRAY_N);
        if ($create_table_row && isset($create_table_row[1])) {
            $sql .= $create_table_row[1] . ";\n\n";
        }
        return $sql;
    }

    private function get_table_data_sql($table) {
        global $wpdb;
        $sql = "--\n-- Dumping data for table `{$table}`\n--\n\n";
        
        $rows = $wpdb->get_results("SELECT * FROM `{$table}`", ARRAY_A);
        
        if (!empty($rows)) {
            $columns = array_keys($rows[0]);
            $insert_sql_start = "INSERT INTO `{$table}` (`" . implode('`, `', $columns) . "`) VALUES\n";
            
            $values_to_insert = array();
            $current_batch_sql = '';

            foreach ($rows as $row_index => $row) {
                $escaped_values = array();
                foreach ($row as $value) {
                    if ($value === null) {
                        $escaped_values[] = 'NULL';
                    } else {
                        $escaped_values[] = "'" . $wpdb->_real_escape((string)$value) . "'";
                    }
                }
                $values_to_insert[] = '(' . implode(', ', $escaped_values) . ')';

                if (count($values_to_insert) >= 100 || ($row_index + 1) == count($rows)) {
                    if (!empty($values_to_insert)) {
                        $current_batch_sql .= $insert_sql_start . implode(",\n", $values_to_insert) . ";\n";
                        $values_to_insert = array();
                    }
                }
            }
            if (!empty($current_batch_sql)) {
                 $sql .= $current_batch_sql;
            }
            $sql .= "\n";
        }
        return $sql;
    }
    
    private function backup_plugin_files($output_dir) {
        $plugin_dir_path = defined('VITAPRO_APPOINTMENTS_FSE_PATH') ? VITAPRO_APPOINTMENTS_FSE_PATH : trailingslashit(dirname(VITAPRO_APPOINTMENTS_FSE_PLUGIN_FILE));
        $this->copy_directory($plugin_dir_path, $output_dir);
    }
    
    private function backup_uploads($output_dir) {
        if (!file_exists($output_dir . 'placeholder.txt')) { 
            @file_put_contents($output_dir . 'placeholder.txt', 'No plugin-specific uploads to backup.');
        }
    }
    
    private function create_zip_archive($source_dir, $output_file) {
        if (!class_exists('ZipArchive')) {
            throw new Exception(__('ZipArchive class not available. Please ensure the PHP Zip extension is enabled.', 'vitapro-appointments-fse'));
        }
        
        $zip = new ZipArchive();
        $res = $zip->open($output_file, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        if ($res !== TRUE) {
            throw new Exception(sprintf(__('Cannot create ZIP file: %s. Error code: %s', 'vitapro-appointments-fse'), $output_file, $res));
        }
        
        $source_dir = rtrim($source_dir, '/\\');
        
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source_dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::LEAVES_ONLY
        );
        
        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source_dir) + 1);
                
                if ($zip->addFile($filePath, $relativePath) === false) {
                     error_log("VitaPro Backup: Failed to add file to ZIP: {$filePath}");
                     // Não lançar exceção aqui para permitir que o backup continue o máximo possível
                }
            }
        }
        
        if (!$zip->close()) {
             throw new Exception(__('Failed to finalize ZIP archive.', 'vitapro-appointments-fse'));
        }
    }

    private function create_tar_archive($source_dir, $output_file) {
        if (class_exists('PharData')) {
            try {
                $tar_path = $output_file; // Saída direta como .tar.gz
                if (substr($tar_path, -3) === '.gz') {
                    $tar_path = substr($tar_path, 0, -3); // Remove .gz para criar .tar
                }
                if (substr($tar_path, -4) !== '.tar') {
                    $tar_path .= '.tar';
                }

                $phar = new PharData($tar_path);
                $phar->buildFromDirectory($source_dir);
                $phar->compress(Phar::GZ); 
                
                if (file_exists($tar_path)) { // Se o .tar ainda existir
                    unlink($tar_path); 
                }
                // O arquivo final será $tar_path . '.gz'
                if (file_exists($tar_path . '.gz') && $output_file !== $tar_path . '.gz') {
                    rename($tar_path . '.gz', $output_file);
                }


            } catch (Exception $e) {
                throw new Exception(sprintf(__('Failed to create tar.gz archive: %s', 'vitapro-appointments-fse'), $e->getMessage()));
            }
        } else {
            throw new Exception(__('PharData class not available for tar.gz compression. Please ensure the PHP Phar extension is enabled.', 'vitapro-appointments-fse'));
        }
    }
    
    private function encrypt_file($input_file, $output_file, $password) {
        if (!function_exists('openssl_encrypt')) {
            error_log("[VitaPro Backup] OpenSSL not available for encryption.");
            throw new Exception(__('Encryption is not available on this server.', 'vitapro-appointments-fse'));
        }
        if (empty($password)) {
            error_log("[VitaPro Backup] Encryption password is empty.");
            throw new Exception(__('Encryption password is required.', 'vitapro-appointments-fse'));
        }

        $data = file_get_contents($input_file);
        if ($data === false) {
            error_log("[VitaPro Backup] Failed to read file for encryption: $input_file");
            throw new Exception(__('Could not read file for encryption.', 'vitapro-appointments-fse'));
        }

        $cipher = 'aes-256-cbc';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $salt = openssl_random_pseudo_bytes(16); 
        $key = hash_pbkdf2("sha256", $password, $salt, 10000, 32, true);

        $encrypted = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($encrypted === false) {
            error_log("[VitaPro Backup] OpenSSL encryption failed for file: $input_file");
            throw new Exception(__('Failed to encrypt backup file.', 'vitapro-appointments-fse'));
        }

        $encrypted_data_with_prefix = $salt . $iv . $encrypted; 

        if (file_put_contents($output_file, $encrypted_data_with_prefix) === false) {
            error_log("[VitaPro Backup] Failed to write encrypted file: $output_file");
            throw new Exception(__('Could not write encrypted backup file. Check server permissions.', 'vitapro-appointments-fse'));
        }
    }
    
    private function decrypt_file($input_file, $output_file, $password) {
        if (!function_exists('openssl_decrypt')) {
            error_log("[VitaPro Restore] OpenSSL not available for decryption.");
            throw new Exception(__('Decryption is not available on this server.', 'vitapro-appointments-fse'));
        }
        if (empty($password)) {
            error_log("[VitaPro Restore] Decryption password is empty.");
            throw new Exception(__('Decryption password is required.', 'vitapro-appointments-fse'));
        }

        $encrypted_data_with_prefix = file_get_contents($input_file);
        if ($encrypted_data_with_prefix === false) {
            error_log("[VitaPro Restore] Failed to read encrypted file: $input_file");
            throw new Exception(__('Could not read encrypted backup file.', 'vitapro-appointments-fse'));
        }

        $cipher = 'aes-256-cbc';
        $ivlen = openssl_cipher_iv_length($cipher);
        $saltlen = 16;

        if (strlen($encrypted_data_with_prefix) < ($saltlen + $ivlen)) {
            error_log("[VitaPro Restore] Encrypted file too short or corrupted: $input_file");
            throw new Exception(__('Encrypted backup file is corrupted or incomplete.', 'vitapro-appointments-fse'));
        }

        $salt = substr($encrypted_data_with_prefix, 0, $saltlen);
        $iv = substr($encrypted_data_with_prefix, $saltlen, $ivlen);
        $encrypted_data = substr($encrypted_data_with_prefix, $saltlen + $ivlen);

        $key = hash_pbkdf2("sha256", $password, $salt, 10000, 32, true);

        $decrypted = openssl_decrypt($encrypted_data, $cipher, $key, OPENSSL_RAW_DATA, $iv);

        if ($decrypted === false) {
            error_log("[VitaPro Restore] OpenSSL decryption failed for file: $input_file");
            throw new Exception(__('Failed to decrypt backup file. The password may be incorrect.', 'vitapro-appointments-fse'));
        }

        if (file_put_contents($output_file, $decrypted) === false) {
            error_log("[VitaPro Restore] Failed to write decrypted file: $output_file");
            throw new Exception(__('Could not write decrypted backup file. Check server permissions.', 'vitapro-appointments-fse'));
        }
    }

    private function extract_zip_archive($zip_file, $extract_dir) {
        if (!class_exists('ZipArchive')) {
            error_log("[VitaPro Restore] ZipArchive class not available for extraction.");
            throw new Exception(__('ZIP extraction is not available on this server.', 'vitapro-appointments-fse'));
        }

        $zip = new ZipArchive();
        $res = $zip->open($zip_file);
        if ($res !== TRUE) {
            error_log("[VitaPro Restore] Failed to open ZIP file: $zip_file. ZipArchive error code: $res");
            throw new Exception(__('Could not open ZIP backup file. The file may be corrupted.', 'vitapro-appointments-fse'));
        }

        if (!$zip->extractTo($extract_dir)) {
            error_log("[VitaPro Restore] Failed to extract ZIP file: $zip_file to $extract_dir");
            $zip->close();
            throw new Exception(__('Failed to extract backup archive. Check server permissions.', 'vitapro-appointments-fse'));
        }
        $zip->close();
    }

    private function extract_tar_archive($tar_file, $extract_dir) {
        if (class_exists('PharData')) {
            try {
                // Se o arquivo for .tar.gz, primeiro descompacte para .tar
                if (substr($tar_file, -3) === '.gz') {
                    $phar_gz = new PharData($tar_file);
                    $phar_tar_path = rtrim($extract_dir, '/') . '/' . basename($tar_file, '.gz');
                    if ($phar_gz->decompress()) { // Extrai para .tar no mesmo diretório
                        $tar_file_to_extract = $phar_tar_path; // O arquivo agora é .tar
                    } else {
                        throw new Exception("Failed to decompress .gz file: {$tar_file}");
                    }
                } else {
                    $tar_file_to_extract = $tar_file;
                }

                if (file_exists($tar_file_to_extract)) {
                    $phar = new PharData($tar_file_to_extract);
                    $phar->extractTo($extract_dir, null, true);
                    if ($tar_file_to_extract !== $tar_file) { // Se um .tar temporário foi criado
                        unlink($tar_file_to_extract);
                    }
                } else {
                     throw new Exception("Intermediate .tar file not found after decompression: {$tar_file_to_extract}");
                }

            } catch (Exception $e) {
                throw new Exception(sprintf(__('Failed to extract tar archive: %s', 'vitapro-appointments-fse'), $e->getMessage()));
            }
        } else {
            throw new Exception(__('PharData class not available for tar archive extraction.', 'vitapro-appointments-fse'));
        }
    }
    
    private function restore_database($sql_file) {
        global $wpdb;
        
        $sql_content = file_get_contents($sql_file);
        if ($sql_content === false) {
            error_log("[VitaPro Restore] Failed to read SQL file: $sql_file");
            throw new Exception(__('Could not read SQL backup file.', 'vitapro-appointments-fse'));
        }
        
        $sql_content = preg_replace('%/\*(?:(?!\*/).)*\*/%s', '', $sql_content);
        $sql_content = preg_replace('/^-- .*$/m', '', $sql_content);
        $queries = preg_split('/;\s*(\n|$)/', $sql_content);

        $wpdb->query('SET foreign_key_checks = 0');
        $wpdb->hide_errors();

        foreach ($queries as $query) {
            if (trim($query)) {
                $result = $wpdb->query($query);
                if ($result === false) {
                    error_log("[VitaPro Restore] SQL execution failed. Query: $query | Error: {$wpdb->last_error}");
                    // Opcional: lançar exceção ou continuar
                }
            }
        }
        $wpdb->query('SET foreign_key_checks = 1');
        $wpdb->show_errors();
    }

    private function copy_directory($source, $destination) {
        if (!is_dir($destination)) {
            if (!mkdir($destination, 0755, true)) {
                error_log("[VitaPro Backup] Failed to create directory: $destination");
                throw new Exception(sprintf(__('Could not create directory: %s', 'vitapro-appointments-fse'), $destination));
            }
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $target = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
            if ($item->isDir()) {
                if (!is_dir($target) && !mkdir($target, 0755, true)) {
                    error_log("[VitaPro Backup] Failed to create directory during copy: $target");
                    throw new Exception(sprintf(__('Could not create directory: %s', 'vitapro-appointments-fse'), $target));
                }
            } else {
                if (!copy($item, $target)) {
                    error_log("[VitaPro Backup] Failed to copy file: {$item} to $target");
                    throw new Exception(sprintf(__('Failed to copy file: %s', 'vitapro-appointments-fse'), $item));
                }
            }
        }
    }

    private function delete_directory($dir) {
        if (!file_exists($dir)) {
            return true;
        }
        if (!is_dir($dir)) {
            return @unlink($dir); // Adicionar @ para suprimir warning se o arquivo não puder ser deletado
        }
        $items = scandir($dir);
        if ($items === false) return false;

        foreach ($items as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            if (!$this->delete_directory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        return @rmdir($dir); // Adicionar @ para suprimir warning
    }
    
    private function get_directory_size($dir) {
        $size = 0;
        if (!is_dir($dir)) return $size;

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::FOLLOW_SYMLINKS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                     $size += $file->getSize();
                }
            }
        } catch(UnexpectedValueException $e) {
            error_log("VitaPro Backup: Error calculating directory size for {$dir}: " . $e->getMessage());
            // Pode acontecer se houver links simbólicos quebrados ou problemas de permissão
        }
        return $size;
    }
    
    private function schedule_auto_cleanup() {
        if (!wp_next_scheduled('vpa_cleanup_old_backups_hook')) {
            wp_schedule_event(time(), 'daily', 'vpa_cleanup_old_backups_hook');
        }
    }
    
    public function cleanup_old_backups() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';

        $old_backups = $wpdb->get_results($wpdb->prepare(
            "SELECT id, backup_path FROM {$table_name}
             WHERE status = 'completed'
             ORDER BY created_at DESC
             LIMIT %d, 999999", 
            $this->max_backups
        ));

        foreach ($old_backups as $backup) {
            if (!empty($backup->backup_path)) {
                $full_backup_path = ABSPATH . $backup->backup_path;
                if (file_exists($full_backup_path)) {
                    @unlink($full_backup_path);
                }
            }
            $wpdb->delete($table_name, array('id' => $backup->id), array('%d'));
            error_log("VitaPro Backup: Cleaned up old backup ID {$backup->id}.");
        }

        $failed_backups = $wpdb->get_results($wpdb->prepare(
            "SELECT id, backup_path FROM {$table_name}
             WHERE status IN ('failed', 'corrupted')
             AND created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
             7 
        ));

        foreach ($failed_backups as $backup) {
            if (!empty($backup->backup_path)) {
                $full_backup_path_failed = ABSPATH . $backup->backup_path;
                if (file_exists($full_backup_path_failed)) {
                    @unlink($full_backup_path_failed);
                }
            }
            $wpdb->delete($table_name, array('id' => $backup->id), array('%d'));
            error_log("VitaPro Backup: Cleaned up failed/corrupted backup ID {$backup->id}.");
        }
    }
    
    public function get_backup_list_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vpa_backup_nonce')) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';
        
        $backups = $wpdb->get_results(
            "SELECT b.*, u.display_name as created_by_name 
             FROM {$table_name} b 
             LEFT JOIN {$wpdb->users} u ON b.created_by = u.ID 
             ORDER BY b.created_at DESC"
        );
        
        $formatted_backups = array();
        foreach ($backups as $backup) {
            $full_backup_path = '';
            if (!empty($backup->backup_path)) {
                $full_backup_path = ABSPATH . $backup->backup_path;
            }
            $formatted_backups[] = array(
                'id' => $backup->id,
                'name' => esc_html($backup->backup_name),
                'type' => esc_html($backup->backup_type),
                'size' => $backup->status === 'completed' && $backup->backup_size > 0 ? size_format($backup->backup_size) : ($backup->status === 'creating' ? __('Creating...', 'vitapro-appointments-fse') : '-'),
                'size_bytes' => intval($backup->backup_size),
                'status' => esc_html($backup->status),
                'includes' => array(
                    'files' => (bool)$backup->includes_files,
                    'database' => (bool)$backup->includes_database,
                    'uploads' => (bool)$backup->includes_uploads
                ),
                'compression' => esc_html($backup->compression_type),
                'encrypted' => (bool)$backup->encryption_enabled,
                'cloud_storage' => esc_html($backup->cloud_storage),
                'created_by' => $backup->created_by_name ? esc_html($backup->created_by_name) : __('System', 'vitapro-appointments-fse'),
                'created_at' => esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $backup->created_at)),
                'verified_at' => $backup->verified_at ? esc_html(mysql2date(get_option('date_format') . ' ' . get_option('time_format'), $backup->verified_at)) : '-',
                'error_message' => esc_html($backup->error_message),
                'can_download' => $backup->status === 'completed' && !empty($full_backup_path) && file_exists($full_backup_path),
                'time_ago' => esc_html(human_time_diff(strtotime($backup->created_at), current_time('timestamp'))) . ' ' . __('ago', 'vitapro-appointments-fse')
            );
        }
        
        wp_send_json_success($formatted_backups);
    }
    
    public function download_backup_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'vitapro-appointments-fse'));
        }
        
        if (!isset($_GET['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['nonce'])), 'vpa_backup_nonce')) {
            wp_die(__('Security check failed', 'vitapro-appointments-fse'));
        }
        
        $backup_id = isset($_GET['backup_id']) ? intval($_GET['backup_id']) : 0;
        if (!$backup_id) {
            wp_die(__('Invalid backup ID.', 'vitapro-appointments-fse'));
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';
        $backup = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $backup_id));
        
        if (!$backup || $backup->status !== 'completed' || empty($backup->backup_path)) {
            wp_die(__('Backup record not found or backup not completed.', 'vitapro-appointments-fse'));
        }
        
        $full_backup_path = ABSPATH . $backup->backup_path;

        if (!file_exists($full_backup_path)) {
             wp_die(sprintf(__('Backup file not found at: %s', 'vitapro-appointments-fse'), esc_html($full_backup_path)));
        }
        
        do_action('vpa_backup_downloaded', $backup_id, get_current_user_id());
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($full_backup_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($full_backup_path));
        
        // Limpar buffers de saída antes de enviar o arquivo
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        readfile($full_backup_path);
        exit;
    }

    public function delete_backup_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vpa_backup_nonce')) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }

        $backup_id = isset($_POST['backup_id']) ? intval($_POST['backup_id']) : 0;
        if (!$backup_id) {
            wp_send_json_error(__('Invalid backup ID.', 'vitapro-appointments-fse'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';
        $backup = $wpdb->get_row($wpdb->prepare("SELECT backup_path FROM {$table_name} WHERE id = %d", $backup_id));

        if ($backup && !empty($backup->backup_path)) {
            $full_backup_path = ABSPATH . $backup->backup_path;
            if (file_exists($full_backup_path)) {
                @unlink($full_backup_path);
            }
        }

        $deleted = $wpdb->delete($table_name, array('id' => $backup_id), array('%d'));

        if ($deleted !== false) { // $wpdb->delete retorna número de linhas afetadas ou false em erro
            wp_send_json_success(array('message' => __('Backup deleted successfully.', 'vitapro-appointments-fse')));
        } else {
            wp_send_json_error(__('Failed to delete backup record from database.', 'vitapro-appointments-fse'));
        }
    }

    public function schedule_backup_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        wp_send_json_success(array('message' => __('Backup scheduling not fully implemented yet.', 'vitapro-appointments-fse')));
    }

    public function run_scheduled_backup() {
        error_log("VitaPro Backup: Scheduled backup hook triggered.");
        // Exemplo de como iniciar um backup agendado:
        // $options = get_option('vpa_scheduled_backup_options', array( /* opções padrão */ ));
        // $options['type'] = 'scheduled';
        // $this->start_backup($options);
    }

    public function upload_to_cloud_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        wp_send_json_error(__('Cloud upload not implemented yet.', 'vitapro-appointments-fse'));
    }
    
    public function test_cloud_connection_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        wp_send_json_error(__('Cloud connection test not implemented yet.', 'vitapro-appointments-fse'));
    }

    public function verify_backup_ajax_handler() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(__('Insufficient permissions', 'vitapro-appointments-fse'), 403);
            return;
        }
        
        if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'vpa_backup_nonce')) {
            wp_send_json_error(__('Security check failed', 'vitapro-appointments-fse'), 403);
            return;
        }

        $backup_id = isset($_POST['backup_id']) ? intval($_POST['backup_id']) : 0;
        if (!$backup_id) {
            wp_send_json_error(__('Invalid backup ID.', 'vitapro-appointments-fse'));
            return;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_backups';
        $backup = $wpdb->get_row($wpdb->prepare("SELECT backup_path, backup_hash FROM {$table_name} WHERE id = %d", $backup_id));

        if (!$backup || empty($backup->backup_path) || empty($backup->backup_hash) ) {
             wp_send_json_error(__('Backup record incomplete or missing hash.', 'vitapro-appointments-fse'));
             return;
        }
        
        $full_backup_path = ABSPATH . $backup->backup_path;
        if (!file_exists($full_backup_path)) {
             wp_send_json_error(sprintf(__('Backup file not found at: %s', 'vitapro-appointments-fse'), esc_html($full_backup_path)));
             return;
        }


        $current_hash = hash_file('sha256', $full_backup_path);

        if ($current_hash === $backup->backup_hash) {
            $wpdb->update($table_name, array('verified_at' => current_time('mysql', 1)), array('id' => $backup_id));
            wp_send_json_success(array('message' => __('Backup integrity verified successfully.', 'vitapro-appointments-fse')));
        } else {
            $wpdb->update($table_name, array('status' => 'corrupted'), array('id' => $backup_id));
            wp_send_json_error(sprintf(__('Backup verification failed. File may be corrupted. Expected hash: %s, Actual hash: %s', 'vitapro-appointments-fse'), esc_html($backup->backup_hash), esc_html($current_hash) ));
        }
    }
} // Fim da classe VitaPro_Appointments_FSE_Backup_Recovery