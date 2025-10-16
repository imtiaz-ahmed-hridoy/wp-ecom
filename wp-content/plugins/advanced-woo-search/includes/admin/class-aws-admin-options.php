<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'AWS_Admin_Options' ) ) :

    /**
     * Class for plugin admin options methods
     */
    class AWS_Admin_Options {

        /*
         * Get default settings values
         * @param string $tab Tab name
		 * @return array
         */
        static public function get_default_settings( $tab = false, $sec = 'none' ) {

            $options = self::options_array( $tab, $sec );
            $default_settings = array();

            foreach ( $options as $section_name => $section ) {

                foreach ($section as $values) {

                    if ( isset( $values['type'] ) && $values['type'] === 'heading' ) {
                        continue;
                    }

                    if ( isset( $values['type'] ) && $values['type'] === 'html' ) {
                        continue;
                    }

                    if ( isset( $values['disabled'] ) && $values['disabled'] ) {
                        continue;
                    }

                    $current_option = AWS_Admin_Helpers::get_current_option( $values, true );

                    $default_settings[$values['id']] = $current_option;

                }

            }

            return $default_settings;

        }

        /*
         * Update plugin settings
         */
        static public function update_settings() {

            $options = self::options_array( false, 'none' );
            $settings = self::get_settings();

            $current_page = isset( $_GET['page']  ) ? sanitize_text_field( $_GET['page'] ) : 'aws-options';
            $current_tab = empty( $_GET['tab'] ) ? 'general' : sanitize_text_field( $_GET['tab'] );
            if ( $current_page === 'aws-performance' ) {
                $current_tab = 'performance';
            }

            foreach ( $options[$current_tab] as $values ) {

                if ( $values['type'] === 'heading' || $values['type'] === 'html' ) {
                    continue;
                }

                if ( isset( $values['disabled'] ) && $values['disabled'] ) {
                    continue;
                }

                $current_option = AWS_Admin_Helpers::get_current_option( $values );

                $settings[ $values['id'] ] = $current_option;

            }

            update_option( 'aws_settings', $settings );

            AWS_Helpers::register_wpml_translations( $settings );

            do_action( 'aws_settings_saved' );

            do_action( 'aws_cache_clear' );

        }

        /*
         * Get plugin settings
         * @return array
         */
        static public function get_settings() {
            $plugin_options = get_option( 'aws_settings' );
            return $plugin_options;
        }

        /*
         * Options array that generate settings page
         *
         * @param string $tab Tab name
         * @return array
         */
        static public function options_array( $tab = false ) {

            $options = self::include_options();
            $options_arr = array();

            foreach ( $options as $tab_name => $tab_options ) {

                if ( $tab && $tab !== $tab_name ) {
                    continue;
                }

                $options_arr[$tab_name] = $tab_options;

            }

            /**
             * Filter admin page options for current page
             * @since 2.23
             * @param array $options_arr Array of options
             * @param bool|string $tab Current settings page tab
             */
            $options_arr = apply_filters( 'aws_admin_page_options_current', $options_arr, $tab );

            return $options_arr;

        }

        /*
         * Include options array
         * @return array
         */
        static public function include_options() {

            $show_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ? 'false' : 'true';

            $options = array();

            $options['general'][] = array(
                "name"  => __( "Seamless integration", "advanced-woo-search" ),
                "desc"  => __( "Replace all the default search forms on your website.", "advanced-woo-search" ),
                "tip"   => __( "May not work with some themes.", "advanced-woo-search" ),
                "id"    => "seamless",
                "value" => 'false',
                "type"  => "radio",
                'choices' => array(
                    'true'  => __( 'On', 'advanced-woo-search' ),
                    'false'  => __( 'Off', 'advanced-woo-search' ),
                )
            );

            $options['search'][] = array(
                "name" => __( "Search Engine", "advanced-woo-search" ),
                "id"   => "main",
                "type" => "heading"
            );

            $options['search'][] = array(
                "name"       => __( "Search in", "advanced-woo-search" ),
                "desc"    => '',
                "tip"    =>  __( "Product fields to search in.", "advanced-woo-search" ) . __( "Click on the status icon to enable or disable the search source for products search.", "advanced-woo-search" ),
                "table_head" => __( 'Search Source', 'advanced-woo-search' ),
                "id"         => "search_in",
                "value"      => array(
                    'title'    => 1,
                    'content'  => 1,
                    'sku'      => 1,
                    'excerpt'  => 1,
                    'category' => 0,
                    'tag'      => 0,
                    'id'       => 0,
                ),
                "choices" => array(
                    "title"    => array(
                        'label'      => __( "Title", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "content"  => array(
                        'label'      => __( "Content", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "sku"      => array(
                        'label'      => __( "SKU", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "excerpt"  => array(
                        'label'      => __( "Short description", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "category"     => array(
                        'label'      => __( "Category", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "tag"     => array(
                        'label'      => __( "Tag", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "id"     => array(
                        'label'      => __( "ID", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "gtin:disabled"     => array(
                        'label'      => __( "GTIN, UPC, EAN, ISBN", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_in">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "brand:disabled"     => array(
                        'label'      => __( "Brand", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_in">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "attr:disabled"     => array(
                        'label'      => __( "Attributes", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_in">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "tax:disabled"     => array(
                        'label'      => __( "Taxonomies", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_in">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "meta:disabled"     => array(
                        'label'      => __( "Custom Fields", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_in">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                ),
                "type"    => "table"
            );

            // pro only
            $options['search'][] = array(
                "name"  => __( "Search logic", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_logic">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Search rules.", "advanced-woo-search" ) . ' ' . __( "Choose between OR or AND search logic.", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/features/search-operators-and-rules/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_logic"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "id"    => "search_logic",
                "value" => 'or',
                "type"  => "select",
                'choices' => array(
                    'or'  => __( 'OR. Show result if at least one word exists in product.', 'advanced-woo-search' ),
                    'and'  => __( 'AND. Show result if only all words exists in product.', 'advanced-woo-search' ),
                ),
                "disabled" => true,
            );

            // pro only
            $options['search'][] = array(
                "name"  => __( "Exact match", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=search_exact">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Whole or partial search words matching.", "advanced-woo-search" ),
                "tip" => __( "Search only for full word matching or display results even if they match only part of word.", "advanced-woo-search" ),
                "id"    => "search_exact",
                "value" => 'false',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( 'Yes. Search only for full words matching.', 'advanced-woo-search' ),
                    'false'  => __( 'No. Partial words match search.', 'advanced-woo-search' ),
                ),
                "disabled" => true,
            );

            $options['search'][] = array(
                "name"  => __( "Misspelling fix", "advanced-woo-search" ),
                "desc"  => sprintf( __( "Fix typos inside search words %s.", "advanced-woo-search" ), '( lapto<b>t</b> -> lapto<b>p</b> )' ) . '<br>' . __( "Applied if the current search query returns no results.", "advanced-woo-search" ),
                "id"    => "fuzzy",
                "value" => 'true',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( 'On. Automatically search for fixed terms.', 'advanced-woo-search' ),
                    'true_text'  => __( "On. Additionally show text \"Showing results for ...\" with a list of fixed terms at the top of search results.", 'advanced-woo-search' ),
                    'false'  => __( 'Off. Totally disable misspelling fix.', 'advanced-woo-search' ),
                    'false_text'  => __( "Off. Instead show text \"Did you mean ...\" with a clickable list of fixed terms at the top of search results.", 'advanced-woo-search' ),
                )
            );

            $options['search'][] = array(
                "name"  => __( "Open product in new tab", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=target_blank">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Open new window on product click.", "advanced-woo-search" ),
                "id"    => "target_blank",
                "value" => 'false',
                "type"  => "toggler",
                "disabled" => true,
            );

            $options['search'][] = array(
                "name"  => __( "Use Google Analytics", "advanced-woo-search" ),
                "desc"   => __( "Enable Google Analytics integration.", "advanced-woo-search" ) . ' <a href="https://advanced-woo-search.com/guide/google-analytics/" target="_blank">' . __( 'More info', 'advanced-woo-search' ) . '</a>',
                "tip"  => __( "Use google analytics to track searches. You need google analytics to be installed on your site.", "advanced-woo-search" ) .
                    '<br>' . __( "Data will be visible inside Google Analytics 'Site Search' report. Need to activate 'Site Search' feature inside GA.", "advanced-woo-search" ) .
                    '<br>' . __( "Also will send event with category - 'AWS search', action - 'AWS Search Form {form_id}' and label of value of search term.", "advanced-woo-search" ),
                "id"    => "use_analytics",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['search'][] = array(
                "name"    => __( "Search Results Page", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['search'][] = array(
                "name"  => __( "Enable results page", "advanced-woo-search" ),
                "desc"  => __( "Search results page support.", "advanced-woo-search" ),
                "tip" => __( "Show plugin search results on a separated search results page. Will use your current theme products search results page template.", "advanced-woo-search" ),
                "id"    => "search_page",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['search'][] = array(
                "name"  => __( "Max number of results", "advanced-woo-search" ),
                "tip" => __( "Applied only for search results page, not AJAX live results. Larger values can lead to slower search speed.", "advanced-woo-search" ),
                "desc"  => __( "Max total number of results for results page.", "advanced-woo-search" ),
                "id"    => "search_page_res_num",
                "value" => 100,
                "min" => 0,
                "type"  => "number"
            );

            $options['search'][] = array(
                "name"  => __( "Results per page", "advanced-woo-search" ),
                "desc"  => __( "Number of search results per results page.", "advanced-woo-search" ),
                "tip"   => __( "Empty or 0 - use theme default value.", "advanced-woo-search" ),
                "id"    => "search_page_res_per_page",
                "value" => '',
                "min" => 0,
                "type"  => "number"
            );

            $options['search'][] = array(
                "name"  => __( "Change query hook", "advanced-woo-search" ),
                "desc"  => __( "Default query hook for results page.", "advanced-woo-search" ),
                "tip"  => __( "If you have any problems with correct products results on the search results page - try to change this option.", "advanced-woo-search" ),
                "id"    => "search_page_query",
                "value" => 'default',
                "type"  => "select",
                'choices' => array(
                    'default' => __( 'Default', 'advanced-woo-search' ),
                    'posts_pre_query' => __( 'posts_pre_query', 'advanced-woo-search' ),
                )
            );

            $options['search'][] = array(
                "name"  => __( "Highlight words", "advanced-woo-search" ),
                "desc"  => __( "Highlight search terms inside results page.", "advanced-woo-search" ),
                "id"    => "search_page_highlight",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"    => __( "Search options", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['performance'][] = array(
                "name"  => __( "Search rule", "advanced-woo-search" ),
                "desc"  => __( "Search rule that will be used for terms search.", "advanced-woo-search" ),
                "id"    => "search_rule",
                "value" => 'contains',
                "type"  => "radio",
                'choices' => array(
                    'contains' => '%s% ' . __( "( contains )", "advanced-woo-search" ) . ' <span class="aws-help-tip aws-tip" data-tip="'. esc_attr( __( "Search query can be inside any part of the product words ( beginning, end, middle ). Slow.", "advanced-woo-search" ) ) .'"></span>',
                    'begins'   => 's% ' . __( "( begins )", "advanced-woo-search" ) . ' <span class="aws-help-tip aws-tip" data-tip="'. esc_attr( __( "Search query can be only at the beginning of the product words. Fast.", "advanced-woo-search" ) ) .'"></span>',
                )
            );

            $options['performance'][] = array(
                "name"  => __( "AJAX timeout", "advanced-woo-search" ),
                "desc"  => __( "Timeout for ajax search event.", "advanced-woo-search" ),
                "tip"  => __( "Time after user input that script is waiting before sending a search event to the server, ms.", "advanced-woo-search" ),
                "id"    => "search_timeout",
                "value" => 300,
                'min'   => 100,
                "type"  => "number"
            );

            $options['performance'][] = array(
                "name"  => __( "Search words number", "advanced-woo-search" ),
                "desc"  => __( "Maximum allowed search words.", "advanced-woo-search" ),
                "tip"  => __( "The maximum number of words allowed for the search. All extra words will be removed from the search query.", "advanced-woo-search" ),
                "id"    => "search_words_num",
                "value" => 6,
                'min'   => 1,
                "type"  => "number"
            );

            $options['performance'][] = array(
                "name"  => __( "Stop words list", "advanced-woo-search" ),
                "desc"  => __( "List of ignored search words.", "advanced-woo-search" ),
                "tip"  => __( "Comma separated list of words that will be excluded from search.", "advanced-woo-search" ) . '<br>' . __( "Re-index required on change.", "advanced-woo-search" ),
                "id"    => "stopwords",
                "value" => "a, also, am, an, and, are, as, at, be, but, by, call, can, co, con, de, do, due, eg, eight, etc, even, ever, every, for, from, full, go, had, has, hasnt, have, he, hence, her, here, his, how, ie, if, in, inc, into, is, it, its, ltd, me, my, no, none, nor, not, now, of, off, on, once, one, only, onto, or, our, ours, out, over, own, part, per, put, re, see, so, some, ten, than, that, the, their, there, these, they, this, three, thru, thus, to, too, top, un, up, us, very, via, was, we, well, were, what, when, where, who, why, will",
                "cols"  => "85",
                "rows"  => "3",
                "type"  => "textarea"
            );

            $options['performance'][] = array(
                "name"  => __( "Synonyms", "advanced-woo-search" ),
                "desc"  => __( "List of synonyms words.", "advanced-woo-search" ),
                "tip"  => __( "Comma separated list of synonym words. Each group of synonyms must be on separated text line.", "advanced-woo-search" ) . '<br>' . __( "Re-index required on change.", "advanced-woo-search" ),
                "id"    => "synonyms",
                "value" => "buy, pay, purchase, acquire&#13;&#10;box, housing, unit, package",
                "cols"  => "85",
                "rows"  => "3",
                "type"  => "textarea"
            );

            $options['performance'][] = array(
                "name"    => __( "Cache options", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['performance'][] = array(
                "name"  => __( "Cache results", "advanced-woo-search" ),
                "desc"  => __( "Cache search results to increase search speed.", "advanced-woo-search" ),
                "tip"  => __( "Turn off if you have old data in the search results after the content of products was changed.", "advanced-woo-search" ),
                "id"    => "cache",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"    => __( "Clear cache", "advanced-woo-search" ),
                "type"    => "html",
                "desc"    =>__( "Clear cache for all search results.", "advanced-woo-search" ),
                "html"    => '<div id="aws-clear-cache"><input class="button" type="button" value="' . esc_attr__( 'Clear cache', 'advanced-woo-search' ) . '"><span class="loader"></span></div>',
            );

            $options['performance'][] = array(
                "name"    => __( "Index table options", "advanced-woo-search" ),
                "id"      => "index_sources",
                "type"    => "heading"
            );

            $options['performance'][] = array(
                "name"         => __( "Overview", "advanced-woo-search" ),
                'heading_type' => 'text',
                'desc'         => __( "Set an options related to plugin index table.", "advanced-woo-search" ) .'<br><b>' . __( "Note:", "advanced-woo-search" ) . '</b> ' . __( "Reindex is required after options changes.", "advanced-woo-search" ),
                'tip'          => __( 'To perform the search plugin use a special index table. This table contains normalized words of all your products from all available sources.', "advanced-woo-search" ) . '<br>' .
                    __( 'Sometimes when there are too many products in your store index table can be very large and that can reflect on search speed.', "advanced-woo-search" ) . '<br>' .
                    __( 'In this section you can use several options to change the table size by disabling some unused product data.', "advanced-woo-search" ),
                "type"         => "heading"
            );

            $options['performance'][] = array(
                "name"       => __( "Data to index", "advanced-woo-search" ),
                "tip"       => __( "Choose what products data to add inside the plugin index table.", "advanced-woo-search" ),
                "table_head" => __( 'What to index', 'advanced-woo-search' ),
                "id"         => "index_sources",
                "value" => array(
                    'title'    => 1,
                    'content'  => 1,
                    'sku'      => 1,
                    'excerpt'  => 1,
                    'category' => 1,
                    'tag'      => 1,
                    'id'       => 1,
                ),
                "choices" => array(
                    "title"    => array(
                        'label'      => __( "Title", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "content"    => array(
                        'label'      => __( "Content", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "sku"    => array(
                        'label'      => __( "SKU", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "excerpt"    => array(
                        'label'      => __( "Short description", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "category"    => array(
                        'label'      => __( "Category", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "tag"    => array(
                        'label'      => __( "Tag", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "id"    => array(
                        'label'      => __( "ID", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "gtin:disabled"    => array(
                        'label'      => __( "GTIN, UPC, EAN, ISBN", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=index_sources">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "brand:disabled"    => array(
                        'label'      => __( "Brand", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=index_sources">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "attr:disabled"    => array(
                        'label'      => __( "Attributes", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=index_sources">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "tax:disabled"    => array(
                        'label'      => __( "Taxonomies", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=index_sources">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "meta:disabled"    => array(
                        'label'      => __( "Custom Fields", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=index_sources">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                ),
                "type"    => "table"
            );

            $options['performance'][] = array(
                "name"  => __( "Index variations", "advanced-woo-search" ),
                "desc"  => __( "Index or not content of product variations.", "advanced-woo-search" ),
                "id"    => "index_variations",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"  => __( "Sync index table", "advanced-woo-search" ),
                "desc"  => __( "Automatically sync index table.", "advanced-woo-search" ),
                "tip"  => __( "Automatically update plugin index table when product content was changed. This means that in search there will be always latest product data.", "advanced-woo-search" ) . '<br>' .
                    __( "Turn this off if you have any problems with performance.", "advanced-woo-search" ),
                "id"    => "autoupdates",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['performance'][] = array(
                "name"  => __( "Run shortcodes", "advanced-woo-search" ),
                "desc"  => __( "Execute shortcodes inside product content.", "advanced-woo-search" ),
                "id"    => "index_shortcodes",
                "value" => 'true',
                "inherit" => "true",
                "type"  => "toggler",
            );

            // Search Form Settings

            $options['form'][] = array(
                "name" => __( "Search Bar Settings", "advanced-woo-search" ),
                "id"   => "main",
                "type" => "heading"
            );

            $options['form'][] = array(
                "name"  => __( "Text for search field", "advanced-woo-search" ),
                "desc"  => __( "Text for search field placeholder.", "advanced-woo-search" ),
                "id"    => "search_field_text",
                "value" => __( "Search", "advanced-woo-search" ),
                "type"  => "text"
            );

            $options['form'][] = array(
                "name"  => __( "Nothing found field", "advanced-woo-search" ),
                "desc"  => __( "Text when there is no search results.", "advanced-woo-search" ),
                "tip"   => __( "HTML tags are allowed.", "advanced-woo-search" ),
                "id"    => "not_found_text",
                "value" => __( "Nothing found", "advanced-woo-search" ),
                "type"  => "textarea",
                'allow_tags' => AWS_Helpers::kses_textarea_allowed_tags()
            );

            $options['form'][] = array(
                "name"  => __( "Minimum number of characters", "advanced-woo-search" ),
                "desc"  => __( "Minimum number of characters required to run ajax search.", "advanced-woo-search" ),
                "id"    => "min_chars",
                "value" => 1,
                "min" => 1,
                "type"  => "number"
            );

            $options['form'][] = array(
                "name"  => __( "AJAX search", "advanced-woo-search" ),
                "desc"  => __( "Use or not live search feature.", "advanced-woo-search" ),
                "id"    => "enable_ajax",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['form'][] = array(
                "name"  => __( "Show loader", "advanced-woo-search" ),
                "desc"  => __( "Show loader animation while searching.", "advanced-woo-search" ),
                "id"    => "show_loader",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['form'][] = array(
                "name"  => __( "Clear button", "advanced-woo-search" ),
                "desc"  => __( "Show 'Clear search string' button.", "advanced-woo-search" ),
                "tip"  => __( "Only for desktop devices. For mobile devices it is always visible.", "advanced-woo-search" ),
                "id"    => "show_clear",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['form'][] = array(
                "name"  => __( "'View All Results' button", "advanced-woo-search" ),
                "desc"  => __( "Show link to the search results page.", "advanced-woo-search" ),
                "tip"  => __( "Displayed at the bottom of live search results block.", "advanced-woo-search" ),
                "id"    => "show_more",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['form'][] = array(
                "name"  => __( "'View All Results' text", "advanced-woo-search" ),
                "desc"  => __( "Text for 'View All Results' link.", "advanced-woo-search" ),
                "id"    => "show_more_text",
                "value" => __( "View all results", "advanced-woo-search" ),
                "type"  => "text"
            );

            $options['form'][] = array(
                "name"  => __( "Mobile full screen", "advanced-woo-search" ),
                "desc"  => __( "Full screen search on focus.", "advanced-woo-search" ),
                "tip"  => __( "Works only on mobile devices. Will not work if the search form is inside the block with position: fixed.", "advanced-woo-search" ),
                "id"    => "mobile_overlay",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['form'][] = array(
                "name"  => __( "Form Styling", "advanced-woo-search" ),
                "desc"  => __( "Choose search form layout", "advanced-woo-search" ),
                "id"    => "buttons_order",
                "value" => '2',
                "type"  => "radio-image",
                'choices' => array(
                    '1' => 'btn-layout1.png',
                    '2' => 'btn-layout2.png',
                    '3' => 'btn-layout3.png',
                )
            );

            // Search Results Settings

            $options['results'][] = array(
                "name" => __( "Live Results Settings", "advanced-woo-search" ),
                "id"   => "main",
                "type" => "heading"
            );

            $options['results'][] = array(
                "name"  => __( "Description source", "advanced-woo-search" ),
                "desc"  => __( "From where to take product description.", "advanced-woo-search" ),
                "tip" => __( "If first source is empty data will be taken from other sources.", "advanced-woo-search" ),
                "id"    => "desc_source",
                "value" => 'content',
                "type"  => "select",
                'choices' => array(
                    'content'  => __( 'Content', 'advanced-woo-search' ),
                    'excerpt'  => __( 'Short description', 'advanced-woo-search' ),
                )
            );

            $options['results'][] = array(
                "name"  => __( "Description content", "advanced-woo-search" ),
                "desc"  => __( "What to show in product description?", "advanced-woo-search" ),
                "id"    => "mark_words",
                "value" => 'true',
                "type"  => "select",
                'choices' => array(
                    'true'  => __( "Smartly scrape matching sentences from descriptions.", "advanced-woo-search" ),
                    'false' => __( "First N words of product description.", "advanced-woo-search" ),
                )
            );

            $options['results'][] = array(
                "name"  => __( "Description length", "advanced-woo-search" ),
                "desc"  => __( "Maximum product description length ( in words ).", "advanced-woo-search" ),
                "id"    => "excerpt_length",
                "value" => 20,
                "min" => 0,
                "type"  => "number"
            );

            $options['results'][] = array(
                "name"  => __( "Products number", "advanced-woo-search" ),
                "desc"  => __( "Maximum displayed search results.", "advanced-woo-search" ),
                "id"    => "results_num",
                "value" => 10,
                "min" => 0,
                "type"  => "number"
            );

            $options['results'][] = array(
                "name"    => __( "Archive pages", "advanced-woo-search" ),
                "desc"    => __( "Enable needed taxonomies / users search.", "advanced-woo-search" ),
                "tip"    => __( "Search for taxonomies and displayed their archive pages in search results.", "advanced-woo-search" ),
                "table_head" => __( 'Archive Pages', 'advanced-woo-search' ),
                "id"      => "search_archives",
                "inherit" => "true",
                "value" => array(
                    'archive_category' => 0,
                    'archive_tag'      => 0,
                ),
                "choices" => array(
                    "archive_category"    => array(
                        'label'      => __( "Category", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "archive_tag"    => array(
                        'label'      => __( "Tag", "advanced-woo-search" ),
                        'suboptions' => array(),
                    ),
                    "archive_brand:disabled"    => array(
                        'label'      => __( "Brand", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=logic">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "archive_tax:disabled"     => array(
                        'label'      => __( "Taxonomies", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=logic">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "archive_attr:disabled"     => array(
                        'label'      => __( "Attributes", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=logic">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                    "archive_users:disabled"     => array(
                        'label'      => __( "Users", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=logic">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                        'suboptions' => array(),
                    ),
                ),
                "type"    => "table"
            );


            $options['results'][] = array(
                "name"  => __( "Archive pages number", "advanced-woo-search" ),
                "desc"  => __( "Maximum displayed archive search results.", "advanced-woo-search" ),
                "id"    => "pages_results_num",
                "value" => 10,
                "min" => 0,
                "type"  => "number"
            );

            $options['results'][] = array(
                "name"  => __( "Show out-of-stock", "advanced-woo-search" ),
                "desc"  => __( "Show out-of-stock products in search", "advanced-woo-search" ),
                "id"    => "outofstock",
                "value" => $show_out_of_stock,
                "type"  => "toggler",
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Style", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=style">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Set style for search results output.", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/features/search-results-layouts/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=style"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "id"    => "style",
                "value" => 'style-inline',
                "type"  => "select",
                'choices' => array(
                    'style-inline'   => __( "Inline Style", "advanced-woo-search" ),
                    'style-grid'     => __( "Grid Style", "advanced-woo-search" ),
                    'style-big-grid' => __( "Big Grid Style", "advanced-woo-search" ),
                ),
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Variable products", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=var_rules">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "How to show variable products.", "advanced-woo-search" ) . ' ' . __( "Only parent, only child, both.", "advanced-woo-search" ) .' <a target="_blank" href="https://advanced-woo-search.com/features/variable-products-search/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=var_rules"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "id"    => "var_rules",
                "value" => 'parent',
                "type"  => "select",
                'choices' => array(
                    'parent' => __( 'Show only parent products', 'advanced-woo-search' ),
                    'both'   => __( 'Show parent and child products', 'advanced-woo-search' ),
                    'child'  => __( 'Show only child products', 'advanced-woo-search' ),
                ),
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Products sale status", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=on_sale">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Search only for products with selected sale status", "advanced-woo-search" ),
                "id"    => "on_sale",
                "value" => 'true',
                "type"  => "radio",
                'choices' => array(
                    'true'  => __( "Show on-sale and not on-sale products", "advanced-woo-search" ),
                    'false' => __( "Show only on-sale products", "advanced-woo-search" ),
                    'not'   => __( "Show only not on-sale products", "advanced-woo-search" ),
                ),
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Products visibility", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=product_visibility">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Search only products with this visibilities.", "advanced-woo-search" ),
                "id"    => "product_visibility",
                "value" => array(
                    'visible'  => 1,
                    'search'   => 1,
                    'catalog'  => 0,
                    'hidden'   => 0,
                ),
                "type"  => "checkbox",
                'choices' => array(
                    'visible'  => __( 'Catalog/search', 'advanced-woo-search' ),
                    'search'   => __( 'Search', 'advanced-woo-search' ),
                    'catalog'  => __( 'Catalog', 'advanced-woo-search' ),
                    'hidden'   => __( 'Hidden', 'advanced-woo-search' ),
                ),
                "disabled" => true,
            );

            $options['results'][] = array(
                "name"    => __( "Results View", "advanced-woo-search" ),
                "type"    => "heading"
            );

            $options['results'][] = array(
                "name"  => __( "Highlight words", "advanced-woo-search" ),
                "desc"  => __( "Highlight search words inside products content.", "advanced-woo-search" ),
                "id"    => "highlight",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show image", "advanced-woo-search" ),
                "desc"  => __( "Show product image for each search result.", "advanced-woo-search" ),
                "id"    => "show_image",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show description", "advanced-woo-search" ),
                "desc"  => __( "Show product description for each search result.", "advanced-woo-search" ),
                "id"    => "show_excerpt",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show price", "advanced-woo-search" ),
                "desc"  => __( "Show product price for each search result.", "advanced-woo-search" ),
                "id"    => "show_price",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show price for out of stock", "advanced-woo-search" ),
                "desc"  => __( "Show product price for out of stock products.", "advanced-woo-search" ),
                "id"    => "show_outofstock_price",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show sale badge", "advanced-woo-search" ),
                "desc"  => __( "Show sale badge for products in search results.", "advanced-woo-search" ),
                "id"    => "show_sale",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show product SKU", "advanced-woo-search" ),
                "desc"  => __( "Show product SKU in search results.", "advanced-woo-search" ),
                "id"    => "show_sku",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show stock status", "advanced-woo-search" ),
                "desc"  => __( "Show stock status for every product in search results.", "advanced-woo-search" ),
                "id"    => "show_stock",
                "value" => 'false',
                "type"  => "toggler",
            );

            $options['results'][] = array(
                "name"  => __( "Show featured icon", "advanced-woo-search" ),
                "desc"  => __( "Show or not star icon for featured products.", "advanced-woo-search" ),
                "id"    => "show_featured",
                "value" => 'false',
                "type"  => "toggler",
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Show rating", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_rating">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Show product rating.", "advanced-woo-search" ),
                "id"    => "show_rating",
                "value" => 'false',
                "type"  => "toggler",
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Show product brand", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_brand">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Show product brand values in search results.", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/features/search-by-brands/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_brand"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "id"    => "show_brand",
                "value" => 'false',
                "type"  => "toggler",
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Show product GTIN, UPC, EAN or ISBN", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_gtin">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Show product global unique id.", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/features/search-by-gtin-upc-ean-or-isbn/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_gtin"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "id"    => "show_gtin",
                "value" => 'false',
                "type"  => "toggler",
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"  => __( "Show 'Add to cart'", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_cart">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "desc"  => __( "Show 'Add to cart' button for each search result.", "advanced-woo-search" ) . ' <a target="_blank" href="https://advanced-woo-search.com/features/add-to-cart-button/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=show_cart"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "id"    => "show_cart",
                "value" => 'false',
                "type"  => "toggler",
                "disabled" => true,
            );

            // pro only
            $options['results'][] = array(
                "name"    => __( "Filter Results", "advanced-woo-search" ) . ' <a style="font-size:14px;" target="_blank" href="https://advanced-woo-search.com/pricing/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=filters">' . __( "(Pro)", "advanced-woo-search" ) . '</a>',
                "id"      => "excludeinclude",
                "type"    => "heading",
            );

            // pro only
            $options['results'][] = array(
                "name"         => __( "Overview", "advanced-woo-search" ),
                'heading_type' => 'text',
                "desc"         => __( "Filtering rules for search results.", "advanced-woo-search" ),
                "tip"         => __( "Filter search results. You can include/exclude search results based on different rules.", "advanced-woo-search" ) . '<br>' .
                    __( "Combine filter rules to AND or OR logical blocks to create advanced filter logic.", "advanced-woo-search" ) . '<br>' .
                    __( "Please try not to use too many filters overwise this can impact on search speed.", "advanced-woo-search" ),
                "type"         => "heading",
                "disabled" => true,
            );

            // <img alt="" src="' . AWS_URL . 'assets/img/pro/feature1.png' . '" />

            // pro only
            $options['results'][] = array(
                "name"    => __( "Products results", "advanced-woo-search" ),
                "desc"    => '',
                "html"  => '<a disabled="disabled" href="#" class="button add-first-filter">' . __( "Filter products search results", "advanced-woo-search" ) . '</a>',
                "id"      => "adv_filters",
                "value"   => '',
                "type"    => "html"
            );

            // pro only
            $options['results'][] = array(
                "name"    => __( "Terms results", "advanced-woo-search" ),
                "desc"    => '',
                "html"  => '<a disabled="disabled" href="#" class="button add-first-filter">' . __( "Filter taxonomies archive pages results", "advanced-woo-search" ) . '</a>',
                "id"      => "adv_filters",
                "value"   => '',
                "type"    => "html"
            );

            // pro only
            $options['results'][] = array(
                "name"    => __( "Users results", "advanced-woo-search" ),
                "desc"    => '',
                "html"  => '<a disabled="disabled" href="#" class="button add-first-filter">' . __( "Filter users archive pages search results", "advanced-woo-search" ) . '</a>',
                "id"      => "adv_filters",
                "value"   => '',
                "type"    => "html"
            );

            // pro only

            $options['suggestions'][] = array(
                "name" => __( "Search Query Suggestions", "advanced-woo-search" ),
                "id"   => "suggestions",
                "type" => "heading"
            );

            $options['suggestions'][] = array(
                "name"    => __( "", "advanced-woo-search" ),
                "desc"    => '',
                "html"  => '<img style="max-width:450px;" alt="" src="' . AWS_URL . 'assets/img/pro/feature15.png' . '" />',
                "id"      => "image",
                "value"   => '',
                "type"    => "html"
            );

            // pro only
            $options['suggestions'][] = array(
                "name"         => __( "", "advanced-woo-search" ),
                'heading_type' => 'text',
                "desc"         => __( "Show automatic suggested search terms based on the current search query - right inside the search results list.", "advanced-woo-search" ) . '<br>' .
                    __( "Allows search suggestions to appear for both live search results and search results pages.", "advanced-woo-search" ) . '<br>' .
                    '<br><a class="button-primary" target="_blank" href="https://advanced-woo-search.com/features/search-suggestions/?utm_source=plugin&utm_medium=pro-option-link&utm_campaign=pricing&utm_content=suggestions"> ' . __( "Learn more", "advanced-woo-search" ) . '</a>',
                "type"         => "heading",
            );

            /**
             * Filter admin page options
             * @since 2.15
             * @param array $options Array of options
             */
            $options = apply_filters( 'aws_admin_page_options', $options );

            return $options;

        }

        /*
         * Get an array of search form admin tabs names
         * @return array
         */
        static public function get_instance_tabs_names() {

            $tabs = array(
                'general'     => esc_html__( 'General', 'advanced-woo-search' ),
                'search'      => esc_html__( 'Search Config', 'advanced-woo-search' ),
                'form'        => esc_html__( 'Search Form', 'advanced-woo-search' ),
                'results'     => esc_html__( 'Search Results', 'advanced-woo-search' ),
                'suggestions' => esc_html__( 'Search Suggestions', 'advanced-woo-search' ) . ' <span class="aws-pro-badge">' . __( "PRO", "advanced-woo-search" ) . '</span>',
            );

            /**
             * Filter tabs names for search form instance
             * @since 3.37
             * @param array $tabs Array of tabs names
             */
            $tabs = apply_filters( 'aws_admin_instance_tabs_names', $tabs );

            return $tabs;

        }

    }

endif;