<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Admin_Ajax' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class AWS_Admin_Ajax {

        /*
         * Constructor
         */
        public function __construct() {

            add_action( 'wp_ajax_aws-indexEnable', array( $this, 'index_enable' ) );

            add_action( 'wp_ajax_aws-indexDisabled', array( $this, 'index_disabled' ) );

            add_action( 'wp_ajax_aws-hideWelcomeNotice', array( $this, 'hide_welcome_notice' ) );

        }

        /*
         * Enable needed index fields
         */
        public function index_enable() {

            check_ajax_referer( 'aws_admin_ajax_nonce' );

            $field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : 0;
            $sub_field = isset( $_POST['subField'] ) ? sanitize_text_field( $_POST['subField'] ) : 0;

            if ( $field ) {

                $settings = $this->get_settings();

                if ( $settings && isset( $settings['index_sources'][$field] ) ) {
                    $settings['index_sources'][$field]['value'] = '1';

                    if ( $sub_field ) {
                        $settings['index_sources'][$field]['fields'][$sub_field]['value'] = '1';
                    }

                    update_option( 'aws_settings', $settings );
                }

            }

            wp_send_json_success( '1' );

        }

        /*
         * Disabled search fields on index disable
         */
        public function index_disabled() {

            check_ajax_referer( 'aws_admin_ajax_nonce' );

            $field = isset( $_POST['field'] ) ? sanitize_text_field( $_POST['field'] ) : 0;
            $sub_field = isset( $_POST['subField'] ) ? sanitize_text_field( $_POST['subField'] ) : 0;

            if ( $field ) {

                $settings = $this->get_settings();

                if ( $settings ) {

                    $update = false;

                    if ( $settings && isset( $settings['search_in'][$field] ) ) {

                        if ( $settings['search_in'][$field]['value'] === '1' ) {
                            $settings['search_in'][$field]['value'] = '0';
                            $update = true;
                        }

                        if ( $sub_field && isset( $settings['search_in'][$field]['fields'] ) && isset( $settings['search_in'][$field]['fields'][$sub_field] ) ) {
                            $update = true;
                            unset( $settings['search_in'][$field]['fields'][$sub_field] );
                        }

                    }

                    if ( $update ) {
                        update_option( 'aws_settings', $settings );
                    }

                }

            }

            wp_send_json_success( '1' );

        }

        /*
         * Hide plugin welcome notice
         */
        public function hide_welcome_notice() {

            check_ajax_referer( 'aws_admin_ajax_nonce' );

            update_option( 'aws_hide_welcome_notice', 'true', false );

            wp_send_json_success( '1' );

        }

        /*
         * Get plugin settings
         */
        private function get_settings() {
            $plugin_options = get_option( 'aws_settings' );
            return $plugin_options;
        }

    }

endif;


new AWS_Admin_Ajax();