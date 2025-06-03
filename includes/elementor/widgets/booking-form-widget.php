<?php
/**
 * Booking Form Widget
 *
 * Elementor widget for VitaPro Appointments Booking Form.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Booking_Form_Widget
 *
 * Elementor widget for VitaPro Appointments Booking Form.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class Booking_Form_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'vitapro-booking-form';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Appointment Booking Form', 'vitapro-appointments-fse');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-form-horizontal';
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
        return ['appointment', 'booking', 'form', 'vitapro'];
    }
    
    /**
     * Register widget controls
     */
    public function register_controls() {
        
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
        
        $service_options = array('' => __('Select a service', 'vitapro-appointments-fse'));
        foreach ($services as $service) {
            $service_options[$service->ID] = $service->post_title;
        }
        
        // Get professionals for dropdown
        $professionals = get_posts(array(
            'post_type' => 'vpa_professional',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        ));
        
        $professional_options = array('' => __('Select a professional', 'vitapro-appointments-fse'));
        foreach ($professionals as $professional) {
            $professional_options[$professional->ID] = $professional->post_title;
        }
        
        $this->add_control(
            'service_id',
            array(
                'label' => __('Pre-select Service', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $service_options,
                'default' => '',
            )
        );
        
        $this->add_control(
            'professional_id',
            array(
                'label' => __('Pre-select Professional', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $professional_options,
                'default' => '',
            )
        );
        
        $this->add_control(
            'show_service_step',
            array(
                'label' => __('Show Service Selection Step', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_professional_step',
            array(
                'label' => __('Show Professional Selection Step', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'form_id',
            array(
                'label' => __('Form ID', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Enter unique form ID', 'vitapro-appointments-fse'),
                'description' => __('Unique ID for this form. Useful when using multiple forms on the same page.', 'vitapro-appointments-fse'),
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'form_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-booking-form' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'form_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-booking-form',
            )
        );
        
        $this->add_control(
            'form_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-booking-form' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'form_box_shadow',
                'label' => __('Box Shadow', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-booking-form',
            )
        );
        
        $this->add_responsive_control(
            'form_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-booking-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->end_controls_section();
        
        // Button Style Section
        $this->start_controls_section(
            'button_style_section',
            array(
                'label' => __('Button Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'button_typography',
                'label' => __('Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-btn',
            )
        );
        
        $this->start_controls_tabs('button_style_tabs');
        
        $this->start_controls_tab(
            'button_normal_tab',
            array(
                'label' => __('Normal', 'vitapro-appointments-fse'),
            )
        );
        
        $this->add_control(
            'button_text_color',
            array(
                'label' => __('Text Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-btn' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_control(
            'button_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-btn' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->end_controls_tab();
        
        $this->start_controls_tab(
            'button_hover_tab',
            array(
                'label' => __('Hover', 'vitapro-appointments-fse'),
            )
        );
        
        $this->add_control(
            'button_hover_text_color',
            array(
                'label' => __('Text Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-btn:hover' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_control(
            'button_hover_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-btn:hover' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->end_controls_tab();
        
        $this->end_controls_tabs();
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'button_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-btn',
            )
        );
        
        $this->add_control(
            'button_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_responsive_control(
            'button_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'service_id' => $settings['service_id'],
            'professional_id' => $settings['professional_id'],
            'show_service_step' => $settings['show_service_step'] === 'yes',
            'show_professional_step' => $settings['show_professional_step'] === 'yes',
            'form_id' => $settings['form_id']
        );
        
        // Use the existing block render function
        if (class_exists('VitaPro_Appointments_FSE_Blocks')) {
            $blocks = new VitaPro_Appointments_FSE_Blocks();
            echo $blocks->render_booking_form_block($attributes);
        }
    }
    
    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="vpa-elementor-preview">
            <div class="vpa-elementor-preview-title">
                <i class="eicon-form-horizontal"></i>
                <?php _e('Appointment Booking Form', 'vitapro-appointments-fse'); ?>
            </div>
            <div class="vpa-elementor-preview-description">
                <?php _e('This widget displays an appointment booking form. Configure the settings in the left panel.', 'vitapro-appointments-fse'); ?>
            </div>
        </div>
        <?php
    }
}