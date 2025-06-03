<?php
/**
 * Elementor Availability Calendar Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Elementor_Availability_Calendar_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'vitapro-availability-calendar';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Availability Calendar', 'vitapro-appointments-fse');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-calendar';
    }
    
    /**
     * Get widget categories
     */
    public function get_categories() {
        return ['vitapro-appointments'];
    }
    
    /**
     * Get widget keywords
     */
    public function get_keywords() {
        return ['calendar', 'availability', 'schedule', 'vitapro'];
    }
    
    /**
     * Register widget controls
     */
    protected function _register_controls() {
        
        // Content Section
        $this->start_controls_section(
            'content_section',
            array(
                'label' => __('Content', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );
        
        // Get services for dropdown
        $services = get_posts(array(
            'post_type' => 'vpa_service',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $service_options = array('' => __('All Services', 'vitapro-appointments-fse'));
        foreach ($services as $service) {
            $service_options[$service->ID] = $service->post_title;
        }
        
        // Get professionals for dropdown
        $professionals = get_posts(array(
            'post_type' => 'vpa_professional',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $professional_options = array('' => __('All Professionals', 'vitapro-appointments-fse'));
        foreach ($professionals as $professional) {
            $professional_options[$professional->ID] = $professional->post_title;
        }
        
        $this->add_control(
            'service_id',
            array(
                'label' => __('Filter by Service', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $service_options,
                'default' => '',
            )
        );
        
        $this->add_control(
            'professional_id',
            array(
                'label' => __('Filter by Professional', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $professional_options,
                'default' => '',
            )
        );
        
        $this->add_control(
            'months_to_show',
            array(
                'label' => __('Months to Show', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 12,
                'step' => 1,
                'default' => 1,
            )
        );
        
        $this->add_control(
            'show_legend',
            array(
                'label' => __('Show Legend', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Calendar Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'calendar_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-calendar' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'calendar_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-calendar',
            )
        );
        
        $this->add_control(
            'calendar_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-calendar' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_responsive_control(
            'calendar_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-calendar' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->end_controls_section();
        
        // Day Style Section
        $this->start_controls_section(
            'day_style_section',
            array(
                'label' => __('Day Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'available_day_color',
            array(
                'label' => __('Available Day Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-calendar-day.available' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_control(
            'unavailable_day_color',
            array(
                'label' => __('Unavailable Day Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-calendar-day.unavailable' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_control(
            'selected_day_color',
            array(
                'label' => __('Selected Day Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-calendar-day.selected' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'day_typography',
                'label' => __('Day Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-calendar-day',
            )
        );
        
        $this->end_controls_section();
    }
    
    /**
     * Render widget output on the frontend
     */
    protected function render() {
        $settings = $this->get_settings_for_display();
        
        $attributes = array(
            'service_id' => $settings['service_id'],
            'professional_id' => $settings['professional_id'],
            'months_to_show' => intval($settings['months_to_show']),
            'show_legend' => $settings['show_legend'] === 'yes'
        );
        
        // Use the existing block render function
        if (class_exists('VitaPro_Appointments_FSE_Blocks')) {
            $blocks = new VitaPro_Appointments_FSE_Blocks();
            echo $blocks->render_availability_calendar_block($attributes);
        }
    }
    
    /**
     * Render widget output in the editor
     */
    protected function _content_template() {
        ?>
        <div class="vpa-elementor-preview">
            <div class="vpa-elementor-preview-title">
                <i class="eicon-calendar"></i>
                <?php _e('Availability Calendar', 'vitapro-appointments-fse'); ?>
            </div>
            <div class="vpa-elementor-preview-description">
                <?php _e('This widget displays an interactive availability calendar. Configure the filters and display options in the left panel.', 'vitapro-appointments-fse'); ?>
            </div>
        </div>
        <?php
    }
}