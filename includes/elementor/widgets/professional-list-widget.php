<?php
/**
 * Professional List Widget
 *
 * Elementor widget for VitaPro Appointments Professional List.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class Professional_List_Widget
 *
 * Elementor widget for VitaPro Appointments Professional List.
 *
 * @package VitaPro_Appointments_FSE
 * @since 1.0.0
 */
class Professional_List_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'vitapro-professional-list';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Professional List', 'vitapro-appointments-fse');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-person';
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
        return ['professional', 'doctor', 'staff', 'list', 'vitapro'];
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
        
        $this->add_control(
            'layout',
            array(
                'label' => __('Layout', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    'grid' => __('Grid', 'vitapro-appointments-fse'),
                    'list' => __('List', 'vitapro-appointments-fse'),
                ),
                'default' => 'grid',
            )
        );
        
        $this->add_control(
            'columns',
            array(
                'label' => __('Columns', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => array(
                    '1' => '1',
                    '2' => '2',
                    '3' => '3',
                    '4' => '4',
                ),
                'default' => '3',
                'condition' => array(
                    'layout' => 'grid',
                ),
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
            'limit',
            array(
                'label' => __('Number of Professionals', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'default' => 0,
                'description' => __('Set to 0 to show all professionals', 'vitapro-appointments-fse'),
            )
        );
        
        $this->add_control(
            'show_image',
            array(
                'label' => __('Show Professional Image', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_bio',
            array(
                'label' => __('Show Bio', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_services',
            array(
                'label' => __('Show Services', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_book_button',
            array(
                'label' => __('Show Book Button', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'booking_form_id',
            array(
                'label' => __('Booking Form ID', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::TEXT,
                'placeholder' => __('Enter booking form ID', 'vitapro-appointments-fse'),
                'description' => __('ID of the booking form to link to when clicking book buttons.', 'vitapro-appointments-fse'),
                'condition' => array(
                    'show_book_button' => 'yes',
                ),
            )
        );
        
        $this->end_controls_section();
        
        // Style Section
        $this->start_controls_section(
            'style_section',
            array(
                'label' => __('Card Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            )
        );
        
        $this->add_control(
            'card_background_color',
            array(
                'label' => __('Background Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-professional-card' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'card_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-professional-card',
            )
        );
        
        $this->add_control(
            'card_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-professional-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_box_shadow',
                'label' => __('Box Shadow', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-professional-card',
            )
        );
        
        $this->add_responsive_control(
            'card_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-professional-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->end_controls_section();
        
        // Image Style Section
        $this->start_controls_section(
            'image_style_section',
            array(
                'label' => __('Image Style', 'vitapro-appointments-fse'),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'show_image' => 'yes',
                ),
            )
        );
        
        $this->add_responsive_control(
            'image_width',
            array(
                'label' => __('Width', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px', '%'),
                'range' => array(
                    'px' => array(
                        'min' => 50,
                        'max' => 500,
                    ),
                    '%' => array(
                        'min' => 10,
                        'max' => 100,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-professional-image img' => 'width: {{SIZE}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_responsive_control(
            'image_height',
            array(
                'label' => __('Height', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => array('px'),
                'range' => array(
                    'px' => array(
                        'min' => 50,
                        'max' => 500,
                    ),
                ),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-professional-image img' => 'height: {{SIZE}}{{UNIT}}; object-fit: cover;',
                ),
            )
        );
        
        $this->add_control(
            'image_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-professional-image img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
            'layout' => $settings['layout'],
            'columns' => intval($settings['columns']),
            'service_id' => $settings['service_id'],
            'limit' => intval($settings['limit']),
            'show_image' => $settings['show_image'] === 'yes',
            'show_bio' => $settings['show_bio'] === 'yes',
            'show_services' => $settings['show_services'] === 'yes',
            'show_book_button' => $settings['show_book_button'] === 'yes',
            'booking_form_id' => $settings['booking_form_id']
        );
        
        // Use the existing block render function
        if (class_exists('VitaPro_Appointments_FSE_Blocks')) {
            $blocks = new VitaPro_Appointments_FSE_Blocks();
            echo $blocks->render_professional_list_block($attributes);
        }
    }
    
    /**
     * Render widget output in the editor
     */
    protected function content_template() {
        ?>
        <div class="vpa-elementor-preview">
            <div class="vpa-elementor-preview-title">
                <i class="eicon-person"></i>
                <?php _e('Professional List', 'vitapro-appointments-fse'); ?>
            </div>
            <div class="vpa-elementor-preview-description">
                <?php _e('This widget displays a list of healthcare professionals. Configure the layout and display options in the left panel.', 'vitapro-appointments-fse'); ?>
            </div>
        </div>
        <?php
    }
}