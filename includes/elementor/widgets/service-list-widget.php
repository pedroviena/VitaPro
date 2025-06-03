<?php
/**
 * Elementor Service List Widget
 */

if (!defined('ABSPATH')) {
    exit;
}

class VitaPro_Elementor_Service_List_Widget extends \Elementor\Widget_Base {
    
    /**
     * Get widget name
     */
    public function get_name() {
        return 'vitapro-service-list';
    }
    
    /**
     * Get widget title
     */
    public function get_title() {
        return __('Service List', 'vitapro-appointments-fse');
    }
    
    /**
     * Get widget icon
     */
    public function get_icon() {
        return 'eicon-posts-grid';
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
        return ['service', 'list', 'grid', 'vitapro'];
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
        
        // Get service categories for dropdown
        $categories = get_terms(array(
            'taxonomy' => 'vpa_service_category',
            'hide_empty' => false,
        ));
        
        $category_options = array('' => __('All Categories', 'vitapro-appointments-fse'));
        if (!is_wp_error($categories)) {
            foreach ($categories as $category) {
                $category_options[$category->term_id] = $category->name;
            }
        }
        
        $this->add_control(
            'category_id',
            array(
                'label' => __('Filter by Category', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => $category_options,
                'default' => '',
            )
        );
        
        $this->add_control(
            'limit',
            array(
                'label' => __('Number of Services', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 0,
                'max' => 50,
                'step' => 1,
                'default' => 0,
                'description' => __('Set to 0 to show all services', 'vitapro-appointments-fse'),
            )
        );
        
        $this->add_control(
            'show_image',
            array(
                'label' => __('Show Service Image', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_description',
            array(
                'label' => __('Show Description', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_price',
            array(
                'label' => __('Show Price', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => __('Show', 'vitapro-appointments-fse'),
                'label_off' => __('Hide', 'vitapro-appointments-fse'),
                'return_value' => 'yes',
                'default' => 'yes',
            )
        );
        
        $this->add_control(
            'show_duration',
            array(
                'label' => __('Show Duration', 'vitapro-appointments-fse'),
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
                    '{{WRAPPER}} .vpa-service-card' => 'background-color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            array(
                'name' => 'card_border',
                'label' => __('Border', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-service-card',
            )
        );
        
        $this->add_control(
            'card_border_radius',
            array(
                'label' => __('Border Radius', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-service-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            array(
                'name' => 'card_box_shadow',
                'label' => __('Box Shadow', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-service-card',
            )
        );
        
        $this->add_responsive_control(
            'card_padding',
            array(
                'label' => __('Padding', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => array('px', 'em', '%'),
                'selectors' => array(
                    '{{WRAPPER}} .vpa-service-card' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                    '{{WRAPPER}} .vpa-service-card' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
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
                'name' => 'title_typography',
                'label' => __('Title Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-service-title',
            )
        );
        
        $this->add_control(
            'title_color',
            array(
                'label' => __('Title Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-service-title' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'description_typography',
                'label' => __('Description Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-service-description',
            )
        );
        
        $this->add_control(
            'description_color',
            array(
                'label' => __('Description Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-service-description' => 'color: {{VALUE}}',
                ),
            )
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            array(
                'name' => 'price_typography',
                'label' => __('Price Typography', 'vitapro-appointments-fse'),
                'selector' => '{{WRAPPER}} .vpa-service-price',
            )
        );
        
        $this->add_control(
            'price_color',
            array(
                'label' => __('Price Color', 'vitapro-appointments-fse'),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => array(
                    '{{WRAPPER}} .vpa-service-price' => 'color: {{VALUE}}',
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
            'category_id' => $settings['category_id'],
            'limit' => intval($settings['limit']),
            'show_image' => $settings['show_image'] === 'yes',
            'show_description' => $settings['show_description'] === 'yes',
            'show_price' => $settings['show_price'] === 'yes',
            'show_duration' => $settings['show_duration'] === 'yes',
            'show_book_button' => $settings['show_book_button'] === 'yes',
            'booking_form_id' => $settings['booking_form_id']
        );
        
        // Use the existing block render function
        if (class_exists('VitaPro_Appointments_FSE_Blocks')) {
            $blocks = new VitaPro_Appointments_FSE_Blocks();
            echo $blocks->render_service_list_block($attributes);
        }
    }
    
    /**
     * Render widget output in the editor
     */
    protected function _content_template() {
        ?>
        <div class="vpa-elementor-preview">
            <div class="vpa-elementor-preview-title">
                <i class="eicon-posts-grid"></i>
                <?php _e('Service List', 'vitapro-appointments-fse'); ?>
            </div>
            <div class="vpa-elementor-preview-description">
                <?php _e('This widget displays a list of available services. Configure the layout and display options in the left panel.', 'vitapro-appointments-fse'); ?>
            </div>
        </div>
        <?php
    }
}