<?php

/**
 * AWS plugin gutenberg integrations init
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('AWS_Gutenberg_Init')) :

    /**
     * Class for main plugin functions
     */
    class AWS_Gutenberg_Init {

        /**
         * @var AWS_Gutenberg_Init The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Main AWS_Gutenberg_Init Instance
         *
         * Ensures only one instance of AWS_Gutenberg_Init is loaded or can be loaded.
         *
         * @static
         * @return AWS_Gutenberg_Init - Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct() {

            add_action( 'init', array( $this, 'register_block' ) );

            if ( version_compare( get_bloginfo('version'),'5.8', '>=' ) ) {
                add_filter( 'block_categories_all', array( $this, 'add_block_category' ) );
            } else {
                add_filter( 'block_categories', array( $this, 'add_block_category' ) );
            }

        }

        /*
         * Register gutenberg blocks
         */
        public function register_block() {

            global $pagenow;

            $taxonomies = $this->get_taxonomies_options();

            $scripts = array( 'wp-blocks', 'wp-editor' );
            if ( $pagenow && $pagenow === 'widgets.php' && version_compare( get_bloginfo('version'),'5.8', '>=' ) ) {
                $scripts = array( 'wp-blocks', 'wp-edit-widgets' );
            }

            wp_register_script(
                'aws-gutenberg-search-block',
                AWS_URL . 'includes/modules/gutenberg/aws-gutenberg-search-block.js',
                $scripts,
                AWS_VERSION
            );

            wp_register_script(
                'aws-gutenberg-nav-search-block',
                AWS_URL . 'includes/modules/gutenberg/aws-gutenberg-nav-search-block.js',
                $scripts,
                AWS_VERSION
            );

            wp_register_script(
                'aws-gutenberg-search-terms-block',
                AWS_URL . 'includes/modules/gutenberg/aws-gutenberg-search-terms-block.js',
                $scripts,
                AWS_VERSION
            );

            wp_register_style(
                'aws-gutenberg-styles-editor',
                AWS_URL . 'assets/css/common.css',
                array( 'wp-edit-blocks' ),
                AWS_VERSION
            );

            register_block_type( 'advanced-woo-search/search-block', array(
                'apiVersion' => 2,
                'editor_script' => 'aws-gutenberg-search-block',
                'editor_style' => 'aws-gutenberg-styles-editor',
                'render_callback' => array( $this, 'search_block_dynamic_render_callback' ),
                'attributes'      =>  array(
                    'placeholder'   =>  array(
                        'type'    => 'string',
                        'default' => AWS_Helpers::translate( 'search_field_text', AWS()->get_settings( 'search_field_text' ) ),
                    ),
                ),
            ) );

            register_block_type( 'advanced-woo-search/nav-search-block', array(
                'apiVersion' => 2,
                'editor_script' => 'aws-gutenberg-nav-search-block',
                'editor_style' => 'aws-gutenberg-styles-editor',
                'render_callback' => array( $this, 'search_block_dynamic_render_callback' ),
                'attributes'      =>  array(
                    'placeholder'   =>  array(
                        'type'    => 'string',
                        'default' => AWS_Helpers::translate( 'search_field_text', AWS()->get_settings( 'search_field_text' ) ),
                    ),
                ),
            ) );

            register_block_type( 'advanced-woo-search/search-terms-block', array(
                'apiVersion' => 2,
                'editor_script' => 'aws-gutenberg-search-terms-block',
                'editor_style' => 'aws-gutenberg-styles-editor',
                'render_callback' => array( $this, 'search_terms_block_dynamic_render_callback' ),
                'attributes'      =>  array(
                    'limit'   =>  array(
                        'type'    => 'number',
                        'default' => 10,
                    ),
                    'columns'   =>  array(
                        'type'    => 'number',
                        'default' => 4,
                    ),
                    'taxonomies'   =>  array(
                        'type'    => 'array',
                        'default' => $taxonomies,
                    ),
                    'taxonomies_val'   =>  array(
                        'type'    => 'string',
                        'default' => 'all',
                    ),
                ),
            ) );

        }

        /*
         * Render dynamic content
         */
        public function search_block_dynamic_render_callback( $block_attributes, $content ) {

            $placeholder = $block_attributes['placeholder'];
            $args = $placeholder ? array( 'placeholder' => $placeholder ) : array();
            $search_form = aws_get_search_form( false, $args );

            return $search_form;

        }

        /*
         * Render dynamic content
         */
        public function search_terms_block_dynamic_render_callback( $block_attributes, $content ) {

            $limit = isset( $block_attributes['limit'] ) && $block_attributes['limit'] ? intval( $block_attributes['limit'] ) : -1;
            $columns = isset( $block_attributes['columns'] ) && $block_attributes['columns'] ? intval( $block_attributes['columns'] ) : 4;
            $taxonomy = isset( $block_attributes['taxonomies_val'] ) && $block_attributes['taxonomies_val'] ? sanitize_text_field( $block_attributes['taxonomies_val'] ): 'all';

            $force_terms = '';

            global $current_screen;
            if ( is_admin() || ( defined('REST_REQUEST') && REST_REQUEST ) ||
                ( isset($current_screen) && method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor() ) ) {
                $force_terms = $this->get_terms_examples( $taxonomy, $limit );
            }

            return do_shortcode( '[aws_taxonomy_terms_results limit="'. $limit .'" columns="'. $columns .'" taxonomy="'. $taxonomy .'" force_terms="' . $force_terms .'"]' );

        }

        /*
         * Add new blocks category
         */
        public function add_block_category( $categories ) {
            if ( is_array( $categories ) ) {
                $categories = array_merge(
                    $categories,
                    array(
                        array(
                            'slug'  => 'aws',
                            'title' => 'Advanced Woo Search',
                            'icon'  => 'search',
                        ),
                    )
                );
            }
            return $categories;
        }

        /*
        * Ger available taxonomies
        */
        private function get_taxonomies_options() {

            $taxonomies_options = array(
                array(
                    'label' => __( 'All', 'advanced-woo-search' ),
                    'value' => 'all'
                ),
                array(
                    'label' => __( 'Category', 'advanced-woo-search' ),
                    'value' => 'product_cat'
                ),
                array(
                    'label' => __( 'Tag', 'advanced-woo-search' ),
                    'value' => 'product_tag'
                ),
            );

            return $taxonomies_options;

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


endif;

AWS_Gutenberg_Init::instance();