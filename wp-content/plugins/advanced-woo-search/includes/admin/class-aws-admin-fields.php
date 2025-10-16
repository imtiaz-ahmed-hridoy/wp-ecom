<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'AWS_Admin_Fields' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class AWS_Admin_Fields {

        /**
         * @var AWS_Admin_Fields The array of options that is need to be generated
         */
        private $options_array;

        /**
         * @var AWS_Admin_Fields Current plugin instance options
         */
        private $plugin_options;

        private $tab_name;

        private $is_disabled;

        private $current_opt_value;

        /*
         * Constructor
         */
        public function __construct( $tab_name ) {

            $options = AWS_Admin_Options::options_array( $tab_name );

            $this->options_array = $options[$tab_name];
            $this->plugin_options = AWS_Admin_Options::get_settings();;
            $this->tab_name = $tab_name;

            $this->generate_fields();

        }

        /*
         * Generate options fields and print
         */
        private function generate_fields() {

            if ( empty( $this->options_array ) ) {
                return;
            }

            echo '<div data-tab="'. esc_attr( $this->tab_name  ) .'">';

            echo '<table class="form-table">';

                echo '<tbody>';

                foreach ( $this->options_array as $k => $field ) {

                    if ( isset( $field['depends'] ) && ! $field['depends'] ) {
                        continue;
                    }

                    // Generate fields
                    echo $this->generate_field( $field );

                }

                echo '</tbody>';

            echo '</table>';

            echo '<p class="submit"><input name="Submit" type="submit" class="button-primary" value="' . esc_attr__( 'Save Changes', 'advanced-woo-search' ) . '" /></p>';

            echo '</div>';

        }

        /*
         * Generate specific field
         * @return string
         */
        private function generate_field( $field ) {

            $plugin_options = $this->plugin_options;

            $this->current_opt_value = '';
            if ( isset( $field['current_opt_value'] ) && $field['current_opt_value'] ) {
                $this->current_opt_value = $field['current_opt_value'];
            } elseif ( isset( $field['id'] ) && isset( $plugin_options[$field['id']] ) ) {
                $this->current_opt_value = $plugin_options[$field['id']];
            } elseif ( isset( $field['value'] ) ) {
                $this->current_opt_value = $field['value'];
            }

            $this->is_disabled = isset( $field['disabled'] ) && $field['disabled'] ? 'disabled' : '';
            $disabled_row_class = $this->is_disabled ? ' class="aws-disabled"' : '';

            $heading_before = '';
            $heading_after = '';

            if ( $field['type'] === 'heading' ) {
                $heading_tag = isset( $field['heading_type'] ) && $field['heading_type'] === 'text' ? 'span' : 'h3';
                $id = isset( $field['id'] ) && $field['id'] ? 'id="' . $field['id'] . '"' : '';
                $heading_before = '<'. $heading_tag .' '. $id .' class="aws-heading">';
                $heading_after = '</'. $heading_tag .'>';
            }

            $html = '';

            $html .= '<tr'.$disabled_row_class.'>';

                $html .= '<th scope="row">';
                    $html .= $heading_before;
                    $html .= wp_kses_post( $field['name'] );
                    if ( isset( $field['tip'] ) && $field['tip'] ) {
                        $html .= '<span class="aws-help-tip aws-tip" data-tip="'. esc_attr( $field['tip'] ) .'"></span>';
                    }
                    $html .= $heading_after;
                $html .= '</th>';

                $html .= '<td>';

                    // html for specific field type
                    $html .= $this->call_field( $field );

                    if ( isset( $field['desc'] ) && $field['desc'] ) {
                        $html .= '<p class="description">'. wp_kses_post( $field['desc'] ) .'</p>';
                    }

                $html .= '</td>';

            $html .= '</tr>';

            return $html;

        }

        /*
         * Call field type method
         * @param field array Field params
         * @return string
         */
        private function call_field( $field ) {
            $field_type = str_replace( '-', '_', $field['type'] );
            $method = 'get_field_' . $field_type;
            if ( method_exists( $this, $method ) ) {
                return call_user_func_array( array( $this, $method ), array( $field ) );
            } else {
                return '';
            }
        }

        /*
         * Text field type html markup
         * @return string
         */
        private function get_field_text( $field ) {

            $html = '';

            $html .= '<input ' . $this->is_disabled . ' type="text" name="'. esc_attr( $field['id'] ) .'" class="regular-text" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'">';

            return $html;

        }

        /*
         * Number field type html markup
         * @return string
         */
        private function get_field_number( $field ) {

            $params = '';
            $params .= isset($field['step']) ? ' step="' . $field['step'] . '"' : '';
            $params .= isset($field['min']) ? ' min="' . $field['min'] . '"' : '';
            $params .= isset($field['max']) ? ' max="' . $field['max'] . '"' : '';

            $html = '';

            $html .= '<input '. $this->is_disabled .' type="number" '. $params .' name="'. esc_attr( $field['id'] ) .'" class="regular-text" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'">';

            return $html;

        }

        /*
         * Textarea field type html markup
         * @return string
         */
        private function get_field_textarea( $field ) {

            $textarea_cols = isset( $field['cols'] ) ? $field['cols'] : "55";
            $textarea_rows = isset( $field['rows'] ) ? $field['rows'] : "3";
            $textarea_output = isset( $field['allow_tags'] ) ? wp_kses( $this->current_opt_value, AWS_Helpers::get_kses( $field['allow_tags'] ) ) : stripslashes( $this->current_opt_value );

            $html = '';

            $html .= '<textarea '. $this->is_disabled .' id="'. esc_attr( $field['id'] ) .'" name="'. esc_attr( $field['id'] ) .'" cols="'. $textarea_cols .'" rows="'. $textarea_rows .'">'. $textarea_output .'</textarea>';

            return $html;

        }

        /*
         * Image field type html markup
         * @return string
         */
        private function get_field_image( $field ) {

            $full_class = $this->current_opt_value ? ' full' : '';

            $html = '';

            $html .= '<img class="image-preview'. $full_class .'" src="'. esc_url( stripslashes( $this->current_opt_value ) ) .'"  />';
            $html .= '<input type="hidden" size="40" name="'. esc_attr( $field['id'] ) .'" class="image-hidden-input" value="'. esc_attr( stripslashes( $this->current_opt_value ) ) .'" />';
            $html .= '<input '. $this->is_disabled .' class="button image-upload-btn" type="button" value="Upload Image" data-size="'. esc_attr( $field['size'] ) .'" />';

            $html .= '<input class="button image-remove-btn" type="button" value="Remove Image" />';

            return $html;

        }

        /*
         * Checkbox field type html markup
         * @return string
         */
        private function get_field_checkbox( $field ) {

            $checkbox_options = $this->current_opt_value;

            $html = '';

            foreach ( $field['choices'] as $val => $label ) {

                $html .= '<div class="aws-input-checkbox">';
                    $html .= '<input '. $this->is_disabled .' type="checkbox" name="'. esc_attr( $field['id'] . '[' . $val . ']' ) .'" id="'. esc_attr( $field['id'] . '_' . $val ) .'" value="1" '. checked( $checkbox_options[$val], '1', false ) .'> <label for="'. esc_attr( $field['id'] . '_' . $val ) .'">'. $label .'</label>';
                $html .= '</div>';

            }

            return $html;

        }

        /*
         * Radio field type html markup
         * @return string
         */
        private function get_field_radio( $field ) {

            $html = '';

            foreach ( $field['choices'] as $val => $label ) {

                $html .= '<div class="aws-input-radio">';
                    $html .= '<input '. $this->is_disabled .' class="radio" type="radio" name="'. esc_attr( $field['id'] ) .'" id="' . esc_attr( $field['id'] . $val ) . '" value="'. esc_attr( $val ) .'" '. checked( $this->current_opt_value, $val, false ) .'> <label for="'. esc_attr( $field['id'] . $val ) .'">'. $label .'</label>';
                $html .= '</div>';

            }

            return $html;

        }

        /*
         * Toggler field type html markup
         * @return string
         */
        private function get_field_toggler( $field ) {

            $active = $this->current_opt_value == 'true' ? ' checked="checked"' : '';

            $html = '';

            $html .= '<label class="aws-toggle-label aws-toggler-field">';
                $html .= '<input class="aws-toggler" type="checkbox" name="'. esc_attr( $field['id'] ) .'" '. $active .'>';
            $html .= '</label>';

            return $html;

        }

        /*
         * Radio image field type html markup
         * @return string
         */
        private function get_field_radio_image( $field ) {

            $html = '';

            $html .= '<ul class="img-select">';

                foreach ( $field['choices'] as $val => $img ) {

                    $style = isset( $field['styles'] ) &&  isset( $field['styles'][$val] ) ? 'style="' . $field['styles'][$val] . ';"' : '';

                    $html .= '<li class="option" '. $style .'>';

                        $html .= '<input '. $this->is_disabled .' class="radio" type="radio" name="'. esc_attr( $field['id'] ) .'" id="'. esc_attr( $field['id'] . $val ) .'" value="'. esc_attr( $val ) .'" '. checked( $this->current_opt_value, $val, false ) .'>';

                        $html .= '<span class="ico" style="background: url(\''. esc_url( AWS_URL . 'assets/img/' . $img ) .'\') no-repeat 50% 50%;"></span>';

                    $html .= '</li>';

                }

            $html .= '</ul>';

            return $html;

        }

        /*
         * Select field type html markup
         * @return string
         */
        private function get_field_select( $field ) {

            $html = '';

            $html .= '<select '. $this->is_disabled .' name="'. $field['id'] .'">';

                foreach ( $field['choices'] as $val => $label ) {
                    $html .= '<option value="'. esc_attr( $val ) .'" '. selected( $this->current_opt_value, $val, false ) .'>'. esc_html( $label ) .'</option>';
                }

            $html .= '</select>';

            return $html;

        }

        /*
         * Sortable field type html markup
         * @return string
         */
        private function get_field_sortable( $field ) {

            $labels = $field['choices'];

            if ( is_array( $this->current_opt_value ) ) {
                $sortable_items = $this->current_opt_value;
            } else {
                $sortable_items = $field['value'];
            }

            $html = '';

            $html .= '<script>
                jQuery(function($){
                    const ulSel = "#'. $field['id'] .'sort";
                    $(ulSel).sortable({
                        axis: "y",
                        items: ".aws-sotable-item",
                    }).disableSelection();
                });
            </script>';


            $html .= '<div class="sortable-container">';

                $html .= '<ul id="'. esc_attr( $field['id'] ) .'sort" class="aws-sotable">';

                foreach ( $sortable_items as $button_id => $button_value ) {

                    $active = intval( $button_value ) === 1 ? ' checked="checked"' : '';
                    $button_name = isset( $labels[ $button_id ] ) ? $labels[ $button_id ] : 'Def';

                    $html .= '<li id="' . esc_attr( $button_id ) . '" class="aws-sotable-item">';

                        $html .= '<span class="aws-sortable-check">';

                            $html .= '<label class="aws-toggle-label">';
                                $html .= '<input type="hidden" name="'. $field['id'] .'['.$button_id.']" value="0">';
                                $html .= '<input class="aws-toggler" type="checkbox" name="'. $field['id'] .'['.$button_id.']" '. $active .' value="1">';
                            $html .= '</label>';

                            $html .= '<span class="aws-sortable-name">';
                                $html .= $button_name;
                            $html .= '</span>';

                        $html .= '</span>';

                    $html .= '</li>';

                }

                $html .= '</ul>';

            $html .= '</div>';

            return $html;

        }

        /*
         * Table Search For field type html markup
         * @return string
         */
        private function get_field_table( $field ) {

            $table_head = isset( $field['table_head'] ) && $field['table_head'] ? $field['table_head'] : __( 'Search Source', 'advanced-woo-search' );
            $table_options = isset( $this->plugin_options[ $field['id'] ] ) ? $this->plugin_options[ $field['id'] ] : array();

            $html = '';

            $html .= '<div class="aws-table-sources aws-table-sources-sortable">';

                $html .= '<div class="aws-table-sources-body">';

                if ( is_array( $table_options ) ) {
                    foreach ( $field['choices'] as $val => $fchoices ) {

                        $current_val = isset( $table_options[$val] ) && isset( $table_options[$val]['value'] ) ? $table_options[$val]['value'] : '0';
                        $active_class = isset( $table_options[$val] ) && $table_options[$val] ? 'active' : '';
                        $label = is_array( $fchoices ) ? $fchoices['label'] : $fchoices;
                        $suboptions = is_array( $fchoices ) && isset( $fchoices['suboptions'] ) ? $fchoices['suboptions'] : false;

                        $show_weight_column = $suboptions && isset( $suboptions['weight'] );
                        $weight_val = $show_weight_column && isset( $table_options[$val] ) && isset( $table_options[$val]['weight'] ) ? $table_options[$val]['weight'] : '';

                        $show_selected_column = $suboptions && isset( $suboptions['fields'] );
                        if ( $show_selected_column ) {
                            $selected_column_val = 0;
                            $tax_fields = isset( $table_options[$val] ) && isset( $table_options[$val]['fields'] ) ? $table_options[$val]['fields'] : array();
                            foreach ( $tax_fields as $tax_field ) {
                                if ( $tax_field && isset( $tax_field['value'] ) && $tax_field['value'] == '1' ) {
                                    $selected_column_val++;
                                }
                            }
                        }

                        $disabled_class = '';
                        if ( strpos( $val, ':disabled' ) !== false ) {
                            $disabled_class = ' aws-disabled';
                        }

                        $field_name = $field['id'] .'['. $val .']';

                        $html .= '<div class="aws-table-sources-item'. $disabled_class .'">';

                            $html .= '<div class="aws-name">';

                                $html .= '<label class="aws-toggle-label aws-toggler-field">';
                                    $html .= '<input data-field="'.$val.'" value="0" type="hidden" name="'. $field_name .'[value]">';
                                    $html .= '<input data-field="'.$val.'" class="aws-toggler" value="1" type="checkbox" name="'. $field_name .'[value]" '. checked( $current_val, '1', false ) .'>';
                                $html .= '</label>';

                                $html .= $label;

                                if ( $show_weight_column ) {
                                    $html .= '<span class="aws-field-weight">';
                                        $html .= esc_html__( 'weight', 'advanced-woo-search' ) . ': ' . '<span data-item-weight>' . esc_html( $weight_val ) . '</span>';
                                    $html .= '</span>';
                                }

                                if ( $show_selected_column ) {
                                    $html .= '<span class="aws-field-weight aws-field-selected-num">';
                                        $html .= esc_html__( 'selected', 'advanced-woo-search' )  . ': ' . '<span data-item-count>' . esc_html( $selected_column_val ) . '</span>';
                                    $html .= '</span>';
                                }

                            $html .= '</div>';

                            $html .= '<div class="aws-actions">';
                            if ( $suboptions ) {
                                 $html .= '<a data-edit class="button alignright tips edit" title="'. esc_attr__( 'Edit', 'advanced-woo-search' ) .'" href="#">'. esc_attr__( 'Edit', 'advanced-woo-search' ) .'</a>';
                            }
                            $html .= '</div>';

                            if ( $suboptions ) {

                                $html .= '<div class="aws-table-sources-item-settings" style="width:100%;">';

                                    $html .= '<table class="aws-settings-inner-table">';

                                        $html .= '<colgroup>';
                                            $html .= '<col style="width:25%;">';
                                            $html .= '<col style="width:75%;">';
                                        $html .= '</colgroup>';

                                        $html .= '<tbody>';

                                            foreach ( $suboptions as $suboption ) {
                                                $suboption['current_opt_value'] = isset( $table_options[$val] ) && isset( $table_options[$val][$suboption['id']] ) ? $table_options[$val][$suboption['id']] : '';
                                                $suboption['id'] = $field_name . '[' . $suboption['id'] . ']';
                                                $suboption['parent_id'] = $field['id'];
                                                $html .= $this->generate_field( $suboption );
                                            }

                                        $html .= '</tbody>';

                                    $html .= '</table>';

                                $html .= '</div>';

                            }

                        $html .= '</div>';

                    }
                }

                $html .= '</div>';

            $html .= '</div>';

            return $html;

        }

        /*
         * HTML field type html markup
         * @return string
         */
        private function get_field_html( $field ) {

            $html = '';

            $html .= $field['html'];

            return $html;

        }

    }

endif;
