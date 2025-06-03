<?php
/**
 * Blocks
 * 
 * Handles Gutenberg blocks for VitaPro Appointments FSE.
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Appointments_FSE_Blocks {
    
    /**
     * Constructor
     */
    public function __construct() {
        add_action('init', array($this, 'register_blocks'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_block_assets'));
    }
    
    /**
     * Register blocks
     */
    public function register_blocks() {
        // Register booking form block
        register_block_type('vitapro-appointments/booking-form', array(
            'render_callback' => array($this, 'render_booking_form_block'),
            'attributes' => array(
                'serviceId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'professionalId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'showServiceStep' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showProfessionalStep' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'formId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
            ),
        ));
        
        // Register service list block
        register_block_type('vitapro-appointments/service-list', array(
            'render_callback' => array($this, 'render_service_list_block'),
            'attributes' => array(
                'layout' => array(
                    'type' => 'string',
                    'default' => 'grid',
                ),
                'columns' => array(
                    'type' => 'number',
                    'default' => 3,
                ),
                'showImage' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showDescription' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showPrice' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showDuration' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'categoryId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'limit' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
            ),
        ));
        
        // Register professional list block
        register_block_type('vitapro-appointments/professional-list', array(
            'render_callback' => array($this, 'render_professional_list_block'),
            'attributes' => array(
                'layout' => array(
                    'type' => 'string',
                    'default' => 'grid',
                ),
                'columns' => array(
                    'type' => 'number',
                    'default' => 3,
                ),
                'showImage' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showBio' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showServices' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'serviceId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'limit' => array(
                    'type' => 'number',
                    'default' => 0,
                ),
            ),
        ));
        
        // Register availability calendar block
        register_block_type('vitapro-appointments/availability-calendar', array(
            'render_callback' => array($this, 'render_availability_calendar_block'),
            'attributes' => array(
                'serviceId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'professionalId' => array(
                    'type' => 'string',
                    'default' => '',
                ),
                'monthsToShow' => array(
                    'type' => 'number',
                    'default' => 1,
                ),
                'showLegend' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
            ),
        ));
        
        // Register my appointments block
        register_block_type('vitapro-appointments/my-appointments', array(
            'render_callback' => array($this, 'render_my_appointments_block'),
            'attributes' => array(
                'showUpcoming' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'showPast' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'allowCancellation' => array(
                    'type' => 'boolean',
                    'default' => true,
                ),
                'upcomingLimit' => array(
                    'type' => 'number',
                    'default' => 10,
                ),
                'pastLimit' => array(
                    'type' => 'number',
                    'default' => 10,
                ),
            ),
        ));
    }
    
    /**
     * Enqueue block assets
     */
    public function enqueue_block_assets() {
        // This will be handled by the main plugin file
    }
    
    /**
     * Render booking form block
     */
    public function render_booking_form_block($attributes) {
        $service_id = isset($attributes['serviceId']) ? $attributes['serviceId'] : '';
        $professional_id = isset($attributes['professionalId']) ? $attributes['professionalId'] : '';
        $show_service_step = isset($attributes['showServiceStep']) ? $attributes['showServiceStep'] : true;
        $show_professional_step = isset($attributes['showProfessionalStep']) ? $attributes['showProfessionalStep'] : true;
        $form_id = isset($attributes['formId']) ? $attributes['formId'] : 'vpa-booking-form-' . uniqid();
        
        ob_start();
        ?>
        <div class="vpa-booking-form" id="<?php echo esc_attr($form_id); ?>">
            <div class="vpa-booking-steps">
                <?php if ($show_service_step): ?>
                <div class="vpa-booking-step vpa-step-service active" data-step="service">
                    <h3><?php _e('Select Service', 'vitapro-appointments-fse'); ?></h3>
                    <div class="vpa-service-selection">
                        <?php $this->render_service_selection($service_id); ?>
                    </div>
                    <div class="vpa-step-navigation">
                        <button type="button" class="vpa-btn vpa-btn-next" data-next="professional"><?php _e('Next', 'vitapro-appointments-fse'); ?></button>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if ($show_professional_step): ?>
                <div class="vpa-booking-step vpa-step-professional" data-step="professional">
                    <h3><?php _e('Select Professional', 'vitapro-appointments-fse'); ?></h3>
                    <div class="vpa-professional-selection">
                        <?php $this->render_professional_selection($professional_id); ?>
                    </div>
                    <div class="vpa-step-navigation">
                        <?php if ($show_service_step): ?>
                        <button type="button" class="vpa-btn vpa-btn-prev" data-prev="service"><?php _e('Previous', 'vitapro-appointments-fse'); ?></button>
                        <?php endif; ?>
                        <button type="button" class="vpa-btn vpa-btn-next" data-next="datetime"><?php _e('Next', 'vitapro-appointments-fse'); ?></button>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="vpa-booking-step vpa-step-datetime" data-step="datetime">
                    <h3><?php _e('Select Date & Time', 'vitapro-appointments-fse'); ?></h3>
                    <div class="vpa-datetime-selection">
                        <div class="vpa-calendar-container">
                            <div id="vpa-calendar"></div>
                        </div>
                        <div class="vpa-time-slots-container">
                            <h4><?php _e('Available Times', 'vitapro-appointments-fse'); ?></h4>
                            <div id="vpa-time-slots"></div>
                        </div>
                    </div>
                    <div class="vpa-step-navigation">
                        <button type="button" class="vpa-btn vpa-btn-prev" data-prev="<?php echo $show_professional_step ? 'professional' : 'service'; ?>"><?php _e('Previous', 'vitapro-appointments-fse'); ?></button>
                        <button type="button" class="vpa-btn vpa-btn-next" data-next="details"><?php _e('Next', 'vitapro-appointments-fse'); ?></button>
                    </div>
                </div>
                
                <div class="vpa-booking-step vpa-step-details" data-step="details">
                    <h3><?php _e('Your Details', 'vitapro-appointments-fse'); ?></h3>
                    <form id="vpa-appointment-form">
                        <div class="vpa-form-row">
                            <label for="vpa-customer-name"><?php _e('Full Name', 'vitapro-appointments-fse'); ?> *</label>
                            <input type="text" id="vpa-customer-name" name="customer_name" required />
                        </div>
                        
                        <div class="vpa-form-row">
                            <label for="vpa-customer-email"><?php _e('Email Address', 'vitapro-appointments-fse'); ?> *</label>
                            <input type="email" id="vpa-customer-email" name="customer_email" required />
                        </div>
                        
                        <div class="vpa-form-row">
                            <label for="vpa-customer-phone"><?php _e('Phone Number', 'vitapro-appointments-fse'); ?></label>
                            <input type="tel" id="vpa-customer-phone" name="customer_phone" />
                        </div>
                        
                        <div class="vpa-form-row">
                            <label for="vpa-appointment-notes"><?php _e('Notes (Optional)', 'vitapro-appointments-fse'); ?></label>
                            <textarea id="vpa-appointment-notes" name="appointment_notes" rows="3"></textarea>
                        </div>
                        
                        <?php $this->render_custom_fields(); ?>
                        
                        <div class="vpa-step-navigation">
                            <button type="button" class="vpa-btn vpa-btn-prev" data-prev="datetime"><?php _e('Previous', 'vitapro-appointments-fse'); ?></button>
                            <button type="submit" class="vpa-btn vpa-btn-submit"><?php _e('Book Appointment', 'vitapro-appointments-fse'); ?></button>
                        </div>
                    </form>
                </div>
                
                <div class="vpa-booking-step vpa-step-confirmation" data-step="confirmation">
                    <h3><?php _e('Booking Confirmation', 'vitapro-appointments-fse'); ?></h3>
                    <div class="vpa-confirmation-message">
                        <div class="vpa-success-icon">✓</div>
                        <p><?php _e('Your appointment has been successfully booked!', 'vitapro-appointments-fse'); ?></p>
                        <div class="vpa-appointment-summary"></div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render service list block
     */
    public function render_service_list_block($attributes) {
        $layout = isset($attributes['layout']) ? $attributes['layout'] : 'grid';
        $columns = isset($attributes['columns']) ? intval($attributes['columns']) : 3;
        $show_image = isset($attributes['showImage']) ? $attributes['showImage'] : true;
        $show_description = isset($attributes['showDescription']) ? $attributes['showDescription'] : true;
        $show_price = isset($attributes['showPrice']) ? $attributes['showPrice'] : true;
        $show_duration = isset($attributes['showDuration']) ? $attributes['showDuration'] : true;
        $category_id = isset($attributes['categoryId']) ? $attributes['categoryId'] : '';
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 0;
        
        $args = array(
            'post_type' => 'vpa_service',
            'post_status' => 'publish',
            'posts_per_page' => $limit > 0 ? $limit : -1,
        );
        
        if (!empty($category_id)) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'vpa_service_category',
                    'field' => 'term_id',
                    'terms' => $category_id,
                ),
            );
        }
        
        $services = get_posts($args);
        
        if (empty($services)) {
            return '<p>' . __('No services found. Please check back later or contact support.', 'vitapro-appointments-fse') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="vpa-service-list vpa-layout-<?php echo esc_attr($layout); ?> vpa-columns-<?php echo esc_attr($columns); ?>">
            <?php foreach ($services as $service): ?>
            <div class="vpa-service-card">
                <?php if ($show_image && has_post_thumbnail($service->ID)): ?>
                <div class="vpa-service-image">
                    <?php echo get_the_post_thumbnail($service->ID, 'medium'); ?>
                </div>
                <?php endif; ?>
                
                <div class="vpa-service-content">
                    <h3 class="vpa-service-title"><?php echo esc_html($service->post_title); ?></h3>
                    
                    <?php if ($show_description && !empty($service->post_excerpt)): ?>
                    <div class="vpa-service-description">
                        <?php echo wp_kses_post($service->post_excerpt); ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="vpa-service-meta">
                        <?php if ($show_price): ?>
                        <div class="vpa-service-price">
                            <?php
                            $price = get_post_meta($service->ID, '_vpa_service_price', true);
                            $currency_symbol = get_option('vitapro_appointments_settings', array());
                            $currency_symbol = isset($currency_symbol['currency_symbol']) ? $currency_symbol['currency_symbol'] : '$';
                            echo !empty($price) ? esc_html($currency_symbol . $price) : __('Contact for pricing', 'vitapro-appointments-fse');
                            ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($show_duration): ?>
                        <div class="vpa-service-duration">
                            <?php
                            $duration = get_post_meta($service->ID, '_vpa_service_duration', true);
                            echo !empty($duration) ? esc_html($duration . ' ' . __('minutes', 'vitapro-appointments-fse')) : '';
                            ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="vpa-service-actions">
                        <button type="button" class="vpa-btn vpa-btn-book" data-service-id="<?php echo esc_attr($service->ID); ?>">
                            <?php _e('Book Now', 'vitapro-appointments-fse'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render professional list block
     */
    public function render_professional_list_block($attributes) {
        $layout = isset($attributes['layout']) ? $attributes['layout'] : 'grid';
        $columns = isset($attributes['columns']) ? intval($attributes['columns']) : 3;
        $show_image = isset($attributes['showImage']) ? $attributes['showImage'] : true;
        $show_bio = isset($attributes['showBio']) ? $attributes['showBio'] : true;
        $show_services = isset($attributes['showServices']) ? $attributes['showServices'] : true;
        $service_id = isset($attributes['serviceId']) ? $attributes['serviceId'] : '';
        $limit = isset($attributes['limit']) ? intval($attributes['limit']) : 0;
        
        $args = array(
            'post_type' => 'vpa_professional',
            'post_status' => 'publish',
            'posts_per_page' => $limit > 0 ? $limit : -1,
        );
        
        if (!empty($service_id)) {
            $args['meta_query'] = array(
                array(
                    'key' => '_vpa_professional_services',
                    'value' => $service_id,
                    'compare' => 'LIKE',
                ),
            );
        }
        
        $professionals = get_posts($args);
        
        if (empty($professionals)) {
            return '<p>' . __('No professionals found. Please check back later or contact support.', 'vitapro-appointments-fse') . '</p>';
        }
        
        ob_start();
        ?>
        <div class="vpa-professional-list vpa-layout-<?php echo esc_attr($layout); ?> vpa-columns-<?php echo esc_attr($columns); ?>">
            <?php foreach ($professionals as $professional): ?>
            <div class="vpa-professional-card">
                <?php if ($show_image && has_post_thumbnail($professional->ID)): ?>
                <div class="vpa-professional-image">
                    <?php echo get_the_post_thumbnail($professional->ID, 'medium'); ?>
                </div>
                <?php endif; ?>
                
                <div class="vpa-professional-content">
                    <h3 class="vpa-professional-name"><?php echo esc_html($professional->post_title); ?></h3>
                    
                    <?php
                    $title = get_post_meta($professional->ID, '_vpa_professional_title', true);
                    if (!empty($title)):
                    ?>
                    <div class="vpa-professional-title"><?php echo esc_html($title); ?></div>
                    <?php endif; ?>
                    
                    <?php if ($show_bio && !empty($professional->post_content)): ?>
                    <div class="vpa-professional-bio">
                        <?php echo wp_kses_post(wp_trim_words($professional->post_content, 30)); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($show_services): ?>
                    <div class="vpa-professional-services">
                        <?php
                        $services = get_post_meta($professional->ID, '_vpa_professional_services', true);
                        if (!empty($services) && is_array($services)):
                        ?>
                        <h4><?php _e('Services:', 'vitapro-appointments-fse'); ?></h4>
                        <ul>
                            <?php foreach ($services as $service_id): ?>
                            <li><?php echo esc_html(get_the_title($service_id)); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="vpa-professional-actions">
                        <button type="button" class="vpa-btn vpa-btn-book" data-professional-id="<?php echo esc_attr($professional->ID); ?>">
                            <?php _e('Book Appointment', 'vitapro-appointments-fse'); ?>
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render availability calendar block
     */
    public function render_availability_calendar_block($attributes) {
        $service_id = isset($attributes['serviceId']) ? $attributes['serviceId'] : '';
        $professional_id = isset($attributes['professionalId']) ? $attributes['professionalId'] : '';
        $months_to_show = isset($attributes['monthsToShow']) ? intval($attributes['monthsToShow']) : 1;
        $show_legend = isset($attributes['showLegend']) ? $attributes['showLegend'] : true;
        
        ob_start();
        ?>
        <div class="vpa-availability-calendar" 
             data-service-id="<?php echo esc_attr($service_id); ?>"
             data-professional-id="<?php echo esc_attr($professional_id); ?>"
             data-months="<?php echo esc_attr($months_to_show); ?>">
            
            <div class="vpa-calendar-header">
                <button type="button" class="vpa-calendar-prev">&lt;</button>
                <div class="vpa-calendar-title"></div>
                <button type="button" class="vpa-calendar-next">&gt;</button>
            </div>
            
            <div class="vpa-calendar-grid"></div>
            
            <?php if ($show_legend): ?>
            <div class="vpa-calendar-legend">
                <div class="vpa-legend-item">
                    <span class="vpa-legend-color vpa-available"></span>
                    <span class="vpa-legend-text"><?php _e('Available', 'vitapro-appointments-fse'); ?></span>
                </div>
                <div class="vpa-legend-item">
                    <span class="vpa-legend-color vpa-unavailable"></span>
                    <span class="vpa-legend-text"><?php _e('Unavailable', 'vitapro-appointments-fse'); ?></span>
                </div>
                <div class="vpa-legend-item">
                    <span class="vpa-legend-color vpa-selected"></span>
                    <span class="vpa-legend-text"><?php _e('Selected', 'vitapro-appointments-fse'); ?></span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Render my appointments block
     */
    public function render_my_appointments_block($attributes) {
        if (!is_user_logged_in()) {
            return '<p>' . __('Please log in to view your appointments.', 'vitapro-appointments-fse') . '</p>';
        }

        $show_upcoming = isset($attributes['showUpcoming']) ? $attributes['showUpcoming'] : true;
        $show_past = isset($attributes['showPast']) ? $attributes['showPast'] : true;
        $allow_cancellation = isset($attributes['allowCancellation']) ? $attributes['allowCancellation'] : true;
        $upcoming_limit = isset($attributes['upcomingLimit']) ? intval($attributes['upcomingLimit']) : 10;
        $past_limit = isset($attributes['pastLimit']) ? intval($attributes['pastLimit']) : 10;

        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;

        ob_start();
        ?>
        <div class="vpa-my-appointments">
            <?php if ($show_upcoming): ?>
            <div class="vpa-appointments-section vpa-upcoming-appointments">
                <h3 class="vpa-appointments-heading"><?php _e('Upcoming Appointments', 'vitapro-appointments-fse'); ?></h3>
                <?php $this->render_user_appointments_custom_table($user_email, 'upcoming', $upcoming_limit, $allow_cancellation); ?>
            </div>
            <?php endif; ?>
            <?php if ($show_past): ?>
            <div class="vpa-appointments-section vpa-past-appointments">
                <h3 class="vpa-appointments-heading"><?php _e('Past Appointments', 'vitapro-appointments-fse'); ?></h3>
                <?php $this->render_user_appointments_custom_table($user_email, 'past', $past_limit, false); ?>
            </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    private function render_user_appointments_custom_table($user_email, $type = 'upcoming', $limit = 10, $allow_cancellation = true) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'vpa_appointments';
        $current_date = current_time('Y-m-d');
        $current_time = current_time('H:i:s');

        if ($type === 'upcoming') {
            $where_clause = "WHERE customer_email = %s AND (appointment_date > %s OR (appointment_date = %s AND appointment_time > %s)) AND status != 'cancelled'";
            $order_clause = "ORDER BY appointment_date ASC, appointment_time ASC";
            $prepare_values = array($user_email, $current_date, $current_date, $current_time);
        } else {
            $where_clause = "WHERE customer_email = %s AND (appointment_date < %s OR (appointment_date = %s AND appointment_time <= %s))";
            $order_clause = "ORDER BY appointment_date DESC, appointment_time DESC";
            $prepare_values = array($user_email, $current_date, $current_date, $current_time);
        }

        $sql = "SELECT * FROM {$table_name} {$where_clause} {$order_clause} LIMIT %d";
        $prepare_values[] = $limit;

        $appointments = $wpdb->get_results($wpdb->prepare($sql, $prepare_values));

        if (empty($appointments)) {
            echo '<p>' . ($type === 'upcoming' ? __('No upcoming appointments. Book your first appointment now!', 'vitapro-appointments-fse') : __('No past appointments.', 'vitapro-appointments-fse')) . '</p>';
            return;
        }

        echo '<div class="vpa-appointments-list">';
        foreach ($appointments as $appointment) {
            $service_title = get_the_title($appointment->service_id);
            $professional_title = get_the_title($appointment->professional_id);
            $options = get_option('vitapro_appointments_settings', array());
            $date_format = isset($options['date_format']) ? $options['date_format'] : get_option('date_format');
            $time_format = isset($options['time_format']) ? $options['time_format'] : get_option('time_format');
            $formatted_date = date_i18n($date_format, strtotime($appointment->appointment_date));
            $formatted_time = date_i18n($time_format, strtotime($appointment->appointment_time));
            
            echo '<div class="vpa-appointment-card vpa-status-' . esc_attr($appointment->status) . '">';
            echo '<div class="vpa-appointment-header">';
            echo '<h4 class="vpa-appointment-service">' . esc_html($service_title) . '</h4>';
            echo '<span class="vpa-appointment-status vpa-status-' . esc_attr($appointment->status) . '">' . esc_html(ucfirst($appointment->status)) . '</span>';
            echo '</div>';
            
            echo '<div class="vpa-appointment-details">';
            echo '<div class="vpa-detail-row">';
            echo '<span class="vpa-detail-label">' . __('Professional:', 'vitapro-appointments-fse') . '</span>';
            echo '<span class="vpa-detail-value">' . esc_html($professional_title) . '</span>';
            echo '</div>';
            
            echo '<div class="vpa-detail-row">';
            echo '<span class="vpa-detail-label">' . __('Date:', 'vitapro-appointments-fse') . '</span>';
            echo '<span class="vpa-detail-value">' . esc_html($formatted_date) . '</span>';
            echo '</div>';
            
            echo '<div class="vpa-detail-row">';
            echo '<span class="vpa-detail-label">' . __('Time:', 'vitapro-appointments-fse') . '</span>';
            echo '<span class="vpa-detail-value">' . esc_html($formatted_time) . '</span>';
            echo '</div>';
            
            if (!empty($appointment->notes)) {
                echo '<div class="vpa-detail-row">';
                echo '<span class="vpa-detail-label">' . __('Notes:', 'vitapro-appointments-fse') . '</span>';
                echo '<span class="vpa-detail-value">' . esc_html($appointment->notes) . '</span>';
                echo '</div>';
            }
            echo '</div>';
            
            if ($type === 'upcoming' && $allow_cancellation && in_array($appointment->status, array('pending', 'confirmed'))) {
                echo '<div class="vpa-appointment-actions">';
                // Botão AJAX para cancelar
                echo '<button type="button" class="vpa-btn vpa-btn-cancel" data-appointment-id="' . esc_attr($appointment->id) . '" data-nonce="' . esc_attr(wp_create_nonce(VITAPRO_FRONTEND_NONCE)) . '">' . __('Cancel Appointment', 'vitapro-appointments-fse') . '</button>';
                echo '</div>';
            }
            
            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Render service selection
     */
    private function render_service_selection($selected_service_id = '') {
        $services = get_posts(array(
            'post_type' => 'vpa_service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));
        
        if (empty($services)) {
            echo '<p>' . __('No services available.', 'vitapro-appointments-fse') . '</p>';
            return;
        }
        
        echo '<div class="vpa-service-options">';
        foreach ($services as $service) {
            $selected = ($service->ID == $selected_service_id) ? 'selected' : '';
            echo '<div class="vpa-service-option ' . $selected . '" data-service-id="' . esc_attr($service->ID) . '">';
            echo '<h4>' . esc_html($service->post_title) . '</h4>';
            
            if (!empty($service->post_excerpt)) {
                echo '<p>' . esc_html($service->post_excerpt) . '</p>';
            }
            
            $price = get_post_meta($service->ID, '_vpa_service_price', true);
            $duration = get_post_meta($service->ID, '_vpa_service_duration', true);
            $currency_symbol = get_option('vitapro_appointments_settings', array());
            $currency_symbol = isset($currency_symbol['currency_symbol']) ? $currency_symbol['currency_symbol'] : '$';
            
            echo '<div class="vpa-service-meta">';
            if (!empty($price)) {
                echo '<span class="vpa-price">' . esc_html($currency_symbol . $price) . '</span>';
            }
            if (!empty($duration)) {
                echo '<span class="vpa-duration">' . esc_html($duration . ' ' . __('minutes', 'vitapro-appointments-fse')) . '</span>';
            }
            echo '</div>';
            
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Render professional selection
     */
    private function render_professional_selection($selected_professional_id = '') {
        $professionals = get_posts(array(
            'post_type' => 'vpa_professional',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        ));
        
        if (empty($professionals)) {
            echo '<p>' . __('No professionals available.', 'vitapro-appointments-fse') . '</p>';
            return;
        }
        
        echo '<div class="vpa-professional-options">';
        foreach ($professionals as $professional) {
            $selected = ($professional->ID == $selected_professional_id) ? 'selected' : '';
            echo '<div class="vpa-professional-option ' . $selected . '" data-professional-id="' . esc_attr($professional->ID) . '">';
            
            if (has_post_thumbnail($professional->ID)) {
                echo '<div class="vpa-professional-avatar">';
                echo get_the_post_thumbnail($professional->ID, 'thumbnail');
                echo '</div>';
            }
            
            echo '<div class="vpa-professional-info">';
            echo '<h4>' . esc_html($professional->post_title) . '</h4>';
            
            $title = get_post_meta($professional->ID, '_vpa_professional_title', true);
            if (!empty($title)) {
                echo '<p class="vpa-professional-title">' . esc_html($title) . '</p>';
            }
            
            if (!empty($professional->post_excerpt)) {
                echo '<p class="vpa-professional-bio">' . esc_html($professional->post_excerpt) . '</p>';
            }
            echo '</div>';
            
            echo '</div>';
        }
        echo '</div>';
    }
    
    /**
     * Render custom fields
     */
    private function render_custom_fields() {
        $settings = get_option('vitapro_appointments_settings', array());
        $custom_fields = isset($settings['custom_fields']) ? $settings['custom_fields'] : array();
        
        if (empty($custom_fields)) {
            return;
        }
        
        foreach ($custom_fields as $field) {
            $field_name = 'custom_field_' . sanitize_key($field['name']);
            $required = isset($field['required']) && $field['required'] ? 'required' : '';
            
            echo '<div class="vpa-form-row vpa-custom-field">';
            echo '<label for="' . esc_attr($field_name) . '">' . esc_html($field['label']);
            if ($required) {
                echo ' *';
            }
            echo '</label>';
            
            switch ($field['type']) {
                case 'text':
                    echo '<input type="text" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" ' . $required . ' />';
                    break;
                    
                case 'email':
                    echo '<input type="email" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" ' . $required . ' />';
                    break;
                    
                case 'tel':
                    echo '<input type="tel" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" ' . $required . ' />';
                    break;
                    
                case 'textarea':
                    echo '<textarea id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" placeholder="' . esc_attr($field['placeholder']) . '" rows="3" ' . $required . '></textarea>';
                    break;
                    
                case 'select':
                    echo '<select id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" ' . $required . '>';
                    echo '<option value="">' . __('Select an option', 'vitapro-appointments-fse') . '</option>';
                    
                    if (!empty($field['options'])) {
                        $options = explode("\n", $field['options']);
                        foreach ($options as $option) {
                            $option = trim($option);
                            if (!empty($option)) {
                                echo '<option value="' . esc_attr($option) . '">' . esc_html($option) . '</option>';
                            }
                        }
                    }
                    echo '</select>';
                    break;
                    
                case 'checkbox':
                    echo '<input type="checkbox" id="' . esc_attr($field_name) . '" name="' . esc_attr($field_name) . '" value="1" ' . $required . ' />';
                    break;
                    
                case 'radio':
                    if (!empty($field['options'])) {
                        $options = explode("\n", $field['options']);
                        foreach ($options as $index => $option) {
                            $option = trim($option);
                            if (!empty($option)) {
                                $option_id = $field_name . '_' . $index;
                                echo '<div class="vpa-radio-option">';
                                echo '<input type="radio" id="' . esc_attr($option_id) . '" name="' . esc_attr($field_name) . '" value="' . esc_attr($option) . '" ' . $required . ' />';
                                echo '<label for="' . esc_attr($option_id) . '">' . esc_html($option) . '</label>';
                                echo '</div>';
                            }
                        }
                    }
                    break;
            }
            
            echo '</div>';
        }
    }
}