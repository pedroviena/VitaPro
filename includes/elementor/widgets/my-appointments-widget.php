<?php
/**
 * Elementor My Appointments Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Elementor_My_Appointments_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'vitapro-my-appointments';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('My Appointments', 'vitapro-appointments-fse');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-my-account';
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
        return ['appointments', 'user', 'dashboard', 'my', 'vitapro'];
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
        
        $this->add_control(
            'show_upcoming',
            array(
                'label' => __('Show Upcoming Appointments', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'upcoming_limit',
            array(
                'label' => __('Upcoming Appointments Limit', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'default' => 10,
                'condition' => array(
                    'show_upcoming' => 'yes',
                ),
            )
        );
        
        $this->add_control(
            'show_past',
            array(
                'label' => __('Show Past Appointments', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'past_limit',
            array(
                'label' => __('Past Appointments Limit', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 100,
                'step' => 1,
                'default' => 10,
                'condition' => array(
                    'show_past' => 'yes',
                ),
            )
        );
        
        $this->add_control(
            'allow_cancellation',
            array(
                'label' => __('Allow Cancellation', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Allow', 'vitapro-appointments-fse'),
                'label_off' => __('Disable', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Container Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'container_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-my-appointments' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'container_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-my-appointments',
            )
        );
        
        $this->add_control(
            'container_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-my-appointments' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_responsive_control(
            'container_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-my-appointments' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->end_controls_section();
        
        // Appointment Card Style Section
        $this->start_controls_section(
            'card_style_section',
            array(
                'label' => __('Appointment Card Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'card_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-appointment-card' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'card_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-appointment-card',
            )
        );
        
        $this->add_control(
            'card_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-appointment-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_box_shadow',
                'label' => __('Box Shadow', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-appointment-card',
            )
        );
        
        $this->add_responsive_control(
            'card_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-appointment-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_responsive_control(
            'card_margin',
            array(
                'label' => __('Margin', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-appointment-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->end_controls_section();
        
        // Typography Section
        $this->start_controls_section(
            'typography_section',
            array(
                'label' => __('Typography', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'heading_typography',
                'label' => __('Heading Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-appointments-heading',
            )
        );
        
        $this->add_control(
            'heading_color',
            array(
                'label' => __('Heading Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-appointments-heading' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'content_typography',
                'label' => __('Content Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-appointment-details',
            )
        );
        
        $this->add_control(
            'content_color',
            array(
                'label' => __('Content Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-appointment-details' => 'color: {{VALUE}}',
                ),
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
            'show_upcoming' => $settings['show_upcoming'] === 'yes',
            'upcoming_limit' => intval($settings['upcoming_limit']),
            'show_past' => $settings['show_past'] === 'yes',
            'past_limit' => intval($settings['past_limit']),
            'allow_cancellation' => $settings['allow_cancellation'] === 'yes'
        );
        
        // Use the existing block render function
        if (class_exists('VitaPro_Appointments_FSE_Blocks')) {
            $blocks = new VitaPro_Appointments_FSE_Blocks();
            echo $blocks->render_my_appointments_block($attributes);
        }
    }
    
    /**
     * Render widget output in the editor
     */
    protected function _content_template() {
        ?>
        <div class="vpa-elementor-preview">
            <div class="vpa-elementor-preview-title">
                <i class="eicon-my-account"></i>
                <?php _e('My Appointments', 'vitapro-appointments-fse'); ?>
            </div>
            <div class="vpa-elementor-preview-description">
                <?php _e('This widget displays user appointments dashboard (requires login). Configure the display options in the left panel.', 'vitapro-appointments-fse'); ?>
            </div>
        </div>
        <?php
    }
}