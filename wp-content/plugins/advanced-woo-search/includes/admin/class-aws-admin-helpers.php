<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Admin_Helpers' ) ) :

    /**
     * Class for plugin help methods
     */
    class AWS_Admin_Helpers {

        /*
         * Order one array according to order from the second array
         * @return array
         */
        static public function order_by_array( $choices, $post_array ) {

            if ( $post_array && is_array( $post_array ) ) {

                $order   = $post_array;
                $ordered = array();

                foreach ( $order as $key => $val ) {
                    if ( isset($choices[$key]) ) {
                        $ordered[$key] = $choices[$key];
                    }
                }

                // Add back any missing keys (if $_POST didn't include them)
                foreach ( $choices as $key => $val ) {
                    if ( ! isset($ordered[$key]) ) {
                        $ordered[$key] = $val;
                    }
                }

                $choices = $ordered;

            }

            return $choices;

        }

        /*
         * Get value from multidimension array
         * @return bool|array|string
         */
        static public function array_get( array $array, array $path ) {
            foreach ( $path as $step ) {
                if ( ! $step ) {
                    break;
                }
                if ( ! isset( $array[$step] ) ) {
                    return false;
                }
                $array = $array[$step];
            }
            return $array;
        }

        /*
         * Get new option value from $_POST array
         * @return array|string
         */
        static public function get_new_option_value( $field, $key = false, $subkey = false ) {

            $post_value = AWS_Admin_Helpers::array_get( $_POST, array( $field['id'], $key, $subkey ) );

            switch ( $field['type'] ) {
                case 'toggler':
                    $new_value = $post_value ? 'true' : 'false';
                    break;
                case 'checkbox':
                    $new_value = $post_value ? '1' : '0';
                    break;
                case 'terms_select':
                    $new_value = $post_value ? $post_value : array();
                    break;
                default:
                    $new_value = $post_value ? $post_value : '';
            }

            return $new_value;

        }

        /*
         * Prepare options values before saving to db
         * @return array|string
         */
        static public function prepare_settings( $field, $value = '' ) {

            if ( isset( $field['type'] ) ) {

                switch ( $field['type'] ) {

                    case 'textarea':

                        if ( isset( $field['allow_tags'] ) ) {
                            $value = (string) addslashes( wp_kses( stripslashes( html_entity_decode( $value ) ), AWS_Helpers::get_kses( $field['allow_tags'] ) ) );
                        } else {
                            if ( function_exists('sanitize_textarea_field') ) {
                                $value = (string) sanitize_textarea_field( $value );
                            } else {
                                $value = (string) str_replace( "<\n", "&lt;\n", wp_strip_all_tags( $value ) );
                            }
                        }

                        break;

                    case 'terms_select':
                    case 'filter_rules':
                        $value = (array) $value;
                        break;

                    default:
                        $value = sanitize_text_field( $value );

                }

            }

            return $value;

        }

        /*
         * Get current option value and all its sub values ( if exists )
         * @param $field Array of option parameters
         * @param $default Return option default values or new values
         * @return array|string|num
         */
        static public function get_current_option( $field, $default = false ) {

            $current_option = array();

            if ( isset( $field['choices'] ) &&  $field['value'] && is_array( $field['value'] ) ) {

                $keep_order = ( isset( $field['type'] ) && $field['type'] = 'sortable' ) || ( isset( $field['sortable'] ) && $field['sortable'] );
                $choices = $keep_order && isset( $_POST[$field['id']] ) ? AWS_Admin_Helpers::order_by_array( $field['choices'], $_POST[$field['id']] ) : $field['choices'] ;

                // get options values inside 'choices' array
                foreach ( $choices as $key => $val ) {

                    if ( $default && ! isset( $field['value'][$key] ) ) {
                        continue;
                    }

                    if ( strpos( $key, ':disabled' ) !== false ) {
                        continue;
                    }

                    if ( isset( $val['suboptions'] ) && is_array( $val['suboptions'] ) ) {

                        $new_value = $default ? $field['value'][$key] : AWS_Admin_Helpers::get_new_option_value( $field, $key, 'value' );
                        $current_option[$key]['value'] = sanitize_text_field( $new_value );

                        // get options values inside 'suboptions' array
                        foreach ( $val['suboptions'] as $suboption_key => $suboption_val ) {
                            $new_value = $default ? $suboption_val['value'] : AWS_Admin_Helpers::get_new_option_value( $field, $key, $suboption_val['id'] );
                            $current_option[$key][$suboption_val['id']] = AWS_Admin_Helpers::prepare_settings( $suboption_val, $new_value );
                        }

                    } else {

                        $new_value = $default ? $field['value'][$key] : AWS_Admin_Helpers::get_new_option_value( $field, $key );
                        $current_option[$key] = AWS_Admin_Helpers::prepare_settings( $field, $new_value );

                    }

                }

            } else {

                // get all other single options
                $new_value = $default ? $field['value'] : AWS_Admin_Helpers::get_new_option_value( $field );
                $current_option = AWS_Admin_Helpers::prepare_settings( $field, $new_value );

            }

            return $current_option;

        }

    }

endif;