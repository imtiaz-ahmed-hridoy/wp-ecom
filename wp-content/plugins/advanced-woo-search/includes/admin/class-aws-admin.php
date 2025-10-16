<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Admin' ) ) :

/**
 * Class for plugin admin panel
 */
class AWS_Admin {

    /*
     * Name of the plugin settings page
     */
    var $page_name = 'aws-options';

    /**
     * @var AWS_Admin The single instance of the class
     */
    protected static $_instance = null;


    /**
     * Main AWS_Admin Instance
     *
     * Ensures only one instance of AWS_Admin is loaded or can be loaded.
     *
     * @static
     * @return AWS_Admin - Main instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /*
    * Constructor
    */
    public function __construct() {

        add_action( 'admin_menu', array( &$this, 'add_admin_page' ) );
        add_action( 'admin_init', array( &$this, 'register_settings' ) );

        if ( ! AWS_Admin_Options::get_settings() ) {
            $default_settings = AWS_Admin_Options::get_default_settings();
            update_option( 'aws_settings', $default_settings );
        }

        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

        add_filter( 'aws_admin_page_options_current', array( $this, 'check_sources_in_index' ), 1 );

    }

    /**
     * Add options page
     */
    public function add_admin_page() {
        add_menu_page( esc_html__( 'Adv. Woo Search', 'advanced-woo-search' ), esc_html__( 'Adv. Woo Search', 'advanced-woo-search' ), AWS_Helpers::user_admin_capability(), 'aws-options', array( &$this, 'display_admin_page' ), 'dashicons-search', 70 );
        add_submenu_page( 'aws-options', __( 'Settings', 'advanced-woo-search' ), __( 'Settings', 'advanced-woo-search'), AWS_Helpers::user_admin_capability(), 'aws-options', array( $this, 'display_admin_page' ) );
        add_submenu_page( 'aws-options', __( 'Index Config', 'advanced-woo-search' ), __( 'Index Config', 'advanced-woo-search'), AWS_Helpers::user_admin_capability(), 'aws-performance', array( $this, 'display_admin_page' ) );
        add_submenu_page( 'aws-options', __( 'Premium', 'advanced-woo-search' ),  '<span style="color:rgba(255, 255, 91, 0.8);">' . __( 'Premium', 'advanced-woo-search' ) . '</span>', AWS_Helpers::user_admin_capability(), 'aws-premium', array( $this, 'display_admin_page' ) );
    }

    /**
     * Generate and display options page
     */
    public function display_admin_page() {

        $nonce = wp_create_nonce( 'plugin-settings' );

        $current_page = isset( $_GET['page']  ) ? sanitize_text_field( $_GET['page'] ) : 'aws-options';
        $current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_text_field( $_GET['tab'] );

        if ( isset( $_POST["Submit"] ) && current_user_can( AWS_Helpers::user_admin_capability() ) && isset( $_POST["_wpnonce"] ) && wp_verify_nonce( $_POST["_wpnonce"], 'plugin-settings' ) ) {
            AWS_Admin_Options::update_settings();
        }

        echo AWS_Admin_Meta_Boxes::get_header();

        echo '<div class="wrap">';

        echo '<h1></h1>';

        echo '<form data-current-tab="' . esc_attr( $current_tab ) . '" action="" name="aws_form" id="aws_form" method="post">';

        if ( $current_page === 'aws-performance' ) {

            new AWS_Admin_Fields( 'performance' );

        } elseif ( $current_page === 'aws-premium' ) {

            new AWS_Admin_Page_Premium();

        } else {

            switch ($current_tab) {
                case('search'):
                    new AWS_Admin_Fields( 'search' );
                    break;
                case('form'):
                    new AWS_Admin_Fields( 'form' );
                    break;
                case('results'):
                    new AWS_Admin_Fields( 'results' );
                    break;
                case('suggestions'):
                    new AWS_Admin_Fields( 'suggestions' );
                    break;
                default:
                    echo AWS_Admin_Meta_Boxes::get_general_tab_content();
                    new AWS_Admin_Fields( 'general' );
            }

        }

        echo '<input type="hidden" name="_wpnonce" value="' . esc_attr( $nonce ) . '">';

        echo '</form>';

        echo '</div>';

    }

    /*
	 * Register plugin settings
	 */
    public function register_settings() {
        register_setting( 'aws_settings', 'aws_settings' );
    }

    /*
	 * Get plugin settings
	 */
    public function get_settings() {
        $plugin_options = get_option( 'aws_settings' );
        return $plugin_options;
    }

    /*
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts() {

        if ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'aws-options', 'aws-premium', 'aws-performance' ) ) ) {

            $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

            wp_enqueue_style( 'plugin-admin-style', AWS_URL . 'assets/css/admin' . $suffix . '.css', array(), AWS_VERSION );
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery-ui-sortable' );

            wp_enqueue_script( 'jquery-tiptip', AWS_URL . '/assets/js/jquery.tipTip.js', array( 'jquery' ), AWS_VERSION );
            wp_enqueue_script( 'plugin-admin-scripts', AWS_URL . 'assets/js/admin' . $suffix . '.js', array('jquery', 'jquery-ui-sortable'), AWS_VERSION );

            wp_localize_script( 'plugin-admin-scripts', 'aws_vars', array(
                'ajaxurl' => admin_url( 'admin-ajax.php', 'relative' ),
                'ajax_nonce' => wp_create_nonce( 'aws_admin_ajax_nonce' ),
                'index_text' => __( 'This field is not in the index. Do you want to enable indexing for it?', 'advanced-woo-search' ) . "\n" . __( 'Note: Please re-index the plugin table after enabling all needed fields.', 'advanced-woo-search' ),
                'index_disable_text' => __( 'Disabling the index for this field will turn off searching by this field across all search forms ( if enabled ).', 'advanced-woo-search' ) . "\n" . __( 'Continue?', 'advanced-woo-search' ),
            ) );

        }

    }

    /*
     * Check if some sources for disabled from index
     */
    public function check_sources_in_index( $options ) {

        if ( $options ) {

            $index_options = AWS_Helpers::get_index_options();

            foreach( $options as $options_key => $options_tab ) {
                foreach( $options_tab as $key => $option ) {
                    if ( isset( $option['id'] ) && $option['id'] === 'search_in' && isset( $option['choices'] ) ) {
                        foreach( $option['choices'] as $choice_key => $choice_label ) {
                            if ( isset( $index_options['index'][$choice_key] ) && ! $index_options['index'][$choice_key] ) {
                                $text = '<span data-index-disabled style="font-size:12px;color:#dc3232;">' . __( '(index disabled)', 'advanced-woo-search' ) . '</span>';
                                $options[$options_key][$key]['choices'][$choice_key]['label'] = $choice_label['label'] . ' ' . $text;
                            }
                        }
                    }
                }
            }

        }

        return $options;

    }

}

endif;


add_action( 'init', 'AWS_Admin::instance' );