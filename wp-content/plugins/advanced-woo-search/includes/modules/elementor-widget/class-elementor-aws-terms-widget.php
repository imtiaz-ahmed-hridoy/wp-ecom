<?php

/**
 * Elementor widget to add AWS search taxonomy terms results
 *
 * @since 3.31
 */
class Elementor_AWS_Terms_Widget extends \Elementor\Widget_Base {

    /**
     * Get widget name.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget name.
     */
    public function get_name() {
        return 'aws-terms';
    }

    /**
     * Get widget title.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget title.
     */
    public function get_title() {
        return __( 'Advanced Woo Search', 'advanced-woo-search' ) . ': ' . __( 'Taxonomies Results', 'advanced-woo-search' );
    }

    /**
     * Get widget icon.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Widget icon.
     */
    public function get_icon() {
        return 'aws-elementor-terms-icon';
    }

    /**
     * Get widget categories.
     *
     * @since 1.0.0
     * @access public
     *
     * @return array Widget categories.
     */
    public function get_categories() {
        return array( 'general', 'woocommerce-elements' );
    }

    /**
     * Register widget controls.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function register_controls() {

        $taxonomies_options = array(
            'all'         => __( 'All', 'advanced-woo-search' ),
            'product_cat' => __( "Category", "advanced-woo-search" ),
            'product_tag' => __( "Tag", "advanced-woo-search" ),
        );

        $this->start_controls_section(
            'content_section',
            array(
                'label' => __( 'Content', 'advanced-woo-search' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            )
        );

        $this->add_control(
            'limit',
            array(
                'label' => __( 'Results Count', 'advanced-woo-search' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'input_type' => 'number',
                'placeholder' => '',
                'min' => -1,
                'step' => 1,
                'default' => 10,
            )
        );

        $this->add_control(
            'columns',
            array(
                'label' => __( 'Columns', 'advanced-woo-search' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'input_type' => 'number',
                'placeholder' => '',
                'min' => 1,
                'step' => 1,
                'default' => 4,
            )
        );

        $this->add_control(
            'taxonomy',
            array(
                'label' => __( 'Taxonomy', 'advanced-woo-search' ),
                'type' => \Elementor\Controls_Manager::SELECT,
                'multiple' => true,
                'options' => $taxonomies_options,
                'default' => 'all',
            )
        );

        $this->end_controls_section();

    }

    /**
     * Render widget output on the frontend.
     *
     * @since 1.0.0
     * @access protected
     */
    protected function render() {

        $settings = $this->get_settings_for_display();

        $limit = isset( $settings['limit'] ) && $settings['limit'] ? intval( $settings['limit'] ) : -1;
        $columns = isset( $settings['columns'] ) && $settings['columns'] ? intval( $settings['columns'] ) : 4;
        $taxonomy = isset( $settings['taxonomy'] ) && $settings['taxonomy'] ? sanitize_text_field( $settings['taxonomy'] ): 'all';

        $force_terms = '';
        $is_edit_mode = false;

        if ( class_exists( '\Elementor\Plugin' ) &&
            isset( \Elementor\Plugin::$instance ) &&
            method_exists( \Elementor\Plugin::$instance->editor, 'is_edit_mode' ) ) {
            $is_edit_mode = \Elementor\Plugin::$instance->editor->is_edit_mode();
        }

        if ( $is_edit_mode ) {
            $force_terms = $this->get_terms_examples( $taxonomy, $limit );
        }

        echo do_shortcode( '[aws_taxonomy_terms_results limit="'. $limit .'" columns="'. $columns .'" taxonomy="'. $taxonomy .'" force_terms="' . $force_terms .'"]' );

    }

    /*
     * Get some terms for preview window
     */
    private function get_terms_examples( $taxonomy, $limit ) {

        $terms_ids = '';
        $taxonomy = $taxonomy === 'all' ? 'product_cat' : $taxonomy;

        $terms = get_terms( array(
            'taxonomy' => $taxonomy,
            'number'   => $limit,
            'fields'   => 'ids',
        ) );

        if ( ! empty( $terms ) ) {
            $terms_ids = implode( ',', $terms );
        }

        return $terms_ids;

    }

}