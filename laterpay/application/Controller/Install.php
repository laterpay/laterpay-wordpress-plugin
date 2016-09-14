<?php

/**
 * LaterPay installation controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Install extends LaterPay_Controller_Base
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_post_metadata' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'migrate_pricing_post_meta' ),
            ),
            'laterpay_admin_init' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'trigger_requirements_check' ),
                array( 'trigger_update_capabilities' ),
            ),
            'laterpay_update_capabilities' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'update_capabilities' ),
            ),
            'laterpay_check_requirements' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugins_page_view', 200 ),
                array( 'check_requirements' ),
            ),
            'laterpay_admin_notices' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugins_page_view', 200 ),
                array( 'render_requirements_notices' ),
                array( 'check_for_updates' ),
                array( 'maybe_update_meta_keys' ),
                array( 'maybe_update_terms_price_table' ),
                array( 'maybe_update_currency_to_euro' ),
                array( 'maybe_update_time_passes_table' ),
                array( 'maybe_update_api_urls_options_names' ),
                array( 'maybe_add_is_in_visible_test_mode_option' ),
                array( 'maybe_clean_api_key_options' ),
                array( 'maybe_update_unlimited_access' ),
            ),
        );
    }

    /**
     * Render admin notices, if requirements are not fulfilled.
     *
     * @wp-hook admin_notices
     *
     * @return  void
     */
    public function render_requirements_notices() {
        $notices = $this->check_requirements();
        if ( count( $notices ) > 0 ) {
            $out = join( "\n", $notices );
            echo laterpay_sanitize_output( '<div class="error">' . $out . '</div>' );
        }
    }

    /**
     * Trigger requirements check
     *
     * @param LaterPay_Core_Event $event
     */
    public function trigger_requirements_check( LaterPay_Core_Event $event ) {
        $new_event = new LaterPay_Core_Event( $event->get_arguments() );
        laterpay_event_dispatcher()->dispatch( 'laterpay_check_requirements', $new_event );
    }

    /**
     * Check plugin requirements. Deactivate plugin and return notices, if requirements are not fulfilled.
     *
     * @global string $wp_version
     *
     * @return array $notices
     */
    public function check_requirements() {
        global $wp_version;

        $installed_php_version          = phpversion();
        $installed_wp_version           = $wp_version;
        $required_php_version           = '5.2.4';
        $required_wp_version            = '3.5.2';
        $installed_php_is_compatible    = version_compare( $installed_php_version, $required_php_version, '>=' );
        $installed_wp_is_compatible     = version_compare( $installed_wp_version, $required_wp_version, '>=' );

        $notices = array();
        $template = __( '<p>LaterPay: Your server <strong>does not</strong> meet the minimum requirement of %s version %s or higher. You are running %s version %s.</p>', 'laterpay' );

        // check PHP compatibility
        if ( ! $installed_php_is_compatible ) {
            $notices[] = sprintf( $template, 'PHP', $required_php_version, 'PHP', $installed_php_version );
        }

        // check WordPress compatibility
        if ( ! $installed_wp_is_compatible ) {
            $notices[] = sprintf( $template, 'Wordpress', $required_wp_version, 'Wordpress', $installed_wp_version );
        }

        // deactivate plugin, if requirements are not fulfilled
        if ( count( $notices ) > 0 ) {
            // suppress 'Plugin activated' notice
            unset( $_GET['activate'] );
            deactivate_plugins( $this->config->plugin_base_name );
            $notices[] = __( 'The LaterPay plugin could not be installed. Please fix the reported issues and try again.', 'laterpay' );
        }

        return $notices;
    }

    /**
     * Compare plugin version with latest version and perform an update, if required.
     *
     * @wp-hook plugins_loaded
     *
     * @return void
     */
    public function check_for_updates() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, $this->config->version, '!=' ) ) {
            $this->install();
        }
    }

    /**
     * Update the existing database table for 'terms_price' and set all prices to 'ppu'.
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_terms_price_table() {
        global $wpdb;

        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.8', '<' ) ) {
            return;
        }

        $table      = $wpdb->prefix . 'laterpay_terms_price';
        $columns    = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table .';' );

        // before version 0.9.8 we had no 'revenue_model' column
        $is_up_to_date = false;
        $modified      = false;
        foreach ( $columns as $column ) {
            if ( $column->Field === 'revenue_model' ) {
                $modified      = strpos( strtolower( $column->Type ), 'enum' ) !== false;
                $is_up_to_date = true;
            }
        }

        $this->logger->info(
            __METHOD__,
            array(
                'current_version'   => $current_version,
                'is_up_to_date'     => $is_up_to_date,
            )
        );

        // if the table needs an update, add the 'revenue_model' column and set the current values to 'ppu'
        if ( ! $is_up_to_date ) {
            $wpdb->query( 'ALTER TABLE ' . $table . " ADD revenue_model CHAR( 3 ) NOT NULL DEFAULT  'ppu';" );
        }

        // change revenue model column data type to ENUM
        if ( ! $modified ) {
            $wpdb->query( 'ALTER TABLE ' . $table . " MODIFY revenue_model ENUM('ppu', 'sis') NOT NULL DEFAULT 'ppu';" );
        }
    }

    /**
     * Update the existing postmeta meta_keys when the new version is greater than or equal 0.9.7.
     *
     * @since 0.9.7
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_meta_keys() {
        global $wpdb;

        // check, if the current version is greater than or equal 0.9.7
        if ( version_compare( $this->config->get( 'version' ), '0.9.7', '>=' ) ) {
            // map old values to new ones
            $meta_key_mapping = array(
                'Teaser content'    => 'laterpay_post_teaser',
                'Pricing Post'      => 'laterpay_post_pricing',
                'Pricing Post Type' => 'laterpay_post_pricing_type',
            );

            $sql = 'UPDATE ' . $wpdb->postmeta . " SET meta_key = '%s' WHERE meta_key = '%s'";

            foreach ( $meta_key_mapping as $before => $after ) {
                $prepared_sql = $wpdb->prepare( $sql, array( $after, $before ) );
                $wpdb->query( $prepared_sql );
            }
        }
    }

    /**
    * Updating the existing currency option to EUR, if new version is greater than or equal 0.9.8.
    *
    * @since 0.9.8
    * @wp-hook admin_notices
    *
    * @return void
    */
    public function maybe_update_currency_to_euro() {
        global $wpdb;

        $current_version = $this->config->get( 'version' );

        // check, if the current version is greater than or equal 0.9.8
        if ( version_compare( $current_version, '0.9.8', '>=' ) ) {

            // map old values to new ones
            $meta_key_mapping = array(
                'Teaser content'    => 'laterpay_post_teaser',
                'Pricing Post'      => 'laterpay_post_pricing',
                'Pricing Post Type' => 'laterpay_post_pricing_type',
            );

            $this->logger->info(
                __METHOD__,
                array(
                    'current_version'   => $current_version,
                    'meta_key_mapping'  => $meta_key_mapping,
                )
            );

            // update the currency to default currency 'EUR'
            update_option( 'laterpay_currency', $this->config->get( 'currency.default' ) );

            // remove currency table
            $sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'laterpay_currency';
            $wpdb->query( $sql );
        }
    }

    /**
     * Updating the existing time passes table and remove unused columns.
     *
     * @since 0.9.10
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_time_passes_table() {
        global $wpdb;

        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11.4', '<' ) ) {
            return;
        }

        $table      = $wpdb->prefix . 'laterpay_passes';
        $columns    = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table .';' );

        // before version 0.9.10 we have 'title_color', 'description_color', 'background_color',
        //  and 'background_path' columns that we will remove
        $is_up_to_date = true;
        $removed_columns = array(
            'title_color',
            'description_color',
            'background_color',
            'background_path',
        );

        $is_deleted_flag_present = false;

        foreach ( $columns as $column ) {
            if ( in_array( $column->Field, $removed_columns ) ) {
                $is_up_to_date = false;
            }
            if ( $column->Field === 'is_deleted' ) {
                $is_deleted_flag_present = true;
            }
        }

        $this->logger->info(
            __METHOD__,
            array(
                'current_version'         => $current_version,
                'is_up_to_date'           => $is_up_to_date,
                'is_deleted_flag_present' => $is_deleted_flag_present,
            )
        );

        // if the table needs an update
        if ( ! $is_up_to_date ) {
            $wpdb->query( 'ALTER TABLE ' . $table . ' DROP title_color, DROP description_color, DROP background_color, DROP background_path;' );
        }

        // if need to add is_deleted field
        if ( ! $is_deleted_flag_present ) {
            $wpdb->query( 'ALTER TABLE ' . $table . ' ADD `is_deleted` int(1) NOT NULL DEFAULT 0;' );
        }
    }

    /**
     * Changing options names for API URLs.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_api_urls_options_names() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        $old_to_new_option_pair_array = array(
            'laterpay_api_sandbox_url'      => 'laterpay_sandbox_backend_api_url',
            'laterpay_api_sandbox_web_url'  => 'laterpay_sandbox_dialog_api_url',
            'laterpay_api_live_url'         => 'laterpay_live_backend_api_url',
            'laterpay_api_live_web_url'     => 'laterpay_live_dialog_api_url',
        );

        foreach ( $old_to_new_option_pair_array as $old_option_name => $new_option_name ) {
            $old_option_value = get_option( $old_option_name );

            if ( $old_option_value !== false ) {
                delete_option( $old_option_name );
                add_option( $new_option_name, $old_option_value );
            }
        }
    }

    /**
     * Add option for invisible / visible test mode.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_add_is_in_visible_test_mode_option() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        if ( get_option( 'laterpay_is_in_visible_test_mode' ) === false ) {
            add_option( 'laterpay_is_in_visible_test_mode', 0 );
        }
    }

    /**
     * Set correct values for API URLs.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_clean_api_key_options() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        $options = array(
            'laterpay_sandbox_backend_api_url' => 'https://api.sandbox.laterpaytest.net',
            'laterpay_sandbox_dialog_api_url'  => 'https://web.sandbox.laterpaytest.net',
            'laterpay_live_backend_api_url'    => 'https://api.laterpay.net',
            'laterpay_live_dialog_api_url'     => 'https://web.laterpay.net',
        );

        foreach ( $options as $option_name => $correct_value ) {
            $option_value = get_option( $option_name );
            if ( $option_value != $correct_value ) {
                update_option( $option_name, $correct_value );
            }
        }
    }

    /**
     * Update the existing options during update.
     *
     * @deprecated since version 1.0
     *
     * @return void
     */
    protected function maybe_update_options() {
        $current_version = get_option( 'laterpay_version' );

        if ( version_compare( $current_version, '0.9.8.1', '>=' ) ) {
            delete_option( 'laterpay_plugin_is_activated' );
        }
    }

    /**
     * Migrate old postmeta data to a single postmeta array.
     *
     * @param LaterPay_Core_Event $event Event object.
     * @return null $return
     */
    public function migrate_pricing_post_meta( LaterPay_Core_Event $event ) {
        list($return, $post_id, $meta_key) = $event->get_arguments() + array( '', '', '' );
        // migrate the pricing postmeta to an array
        if ( $meta_key === 'laterpay_post_prices' ) {
            $meta_migration_mapping = array(
                'laterpay_post_pricing'                         => 'price',
                'laterpay_post_revenue_model'                   => 'revenue_model',
                'laterpay_post_default_category'                => 'category_id',
                'laterpay_post_pricing_type'                    => 'type',
                'laterpay_start_price'                          => 'start_price',
                'laterpay_end_price'                            => 'end_price',
                'laterpay_change_start_price_after_days'        => 'change_start_price_after_days',
                'laterpay_transitional_period_end_after_days'   => 'transitional_period_end_after_days,',
                'laterpay_reach_end_price_after_days'           => 'reach_end_price_after_days',
            );

            $new_meta_values = array();

            foreach ( $meta_migration_mapping as $old_meta_key => $new_key ) {
                $value = get_post_meta( $post_id, $old_meta_key, true );

                if ( $value !== '' ) {
                    // migrate old data: if post_pricing is '0' or '1', set it to 'individual price'
                    if ( $old_meta_key === 'laterpay_post_pricing_type' && in_array( $value, array( '0', '1' ) ) ) {
                        $value = LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE;
                    }

                    // add the meta_value to the new postmeta array
                    $new_meta_values[ $new_key ] = $value;

                    // delete the old postmeta
                    delete_post_meta( $post_id, $old_meta_key );
                }
            }

            if ( ! empty( $new_meta_values ) ) {
                add_post_meta( $post_id, 'laterpay_post_prices', $new_meta_values, true );
            }
        }
        $event->set_result( $return );
    }

    /**
     * Update the unlimited access option.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_unlimited_access() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        if ( $unlimited_role = get_option( 'laterpay_unlimited_access_to_paid_content' ) ) {
            add_option( 'laterpay_unlimited_access', array( $unlimited_role => array( 'all' ) ) );
            delete_option( 'laterpay_unlimited_access_to_paid_content' );
        }
    }

    /**
     * Update vouchers structure.
     *
     * @since 0.9.13
     *
     * @return void
     */
    public function maybe_update_vouchers() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.13', '<' ) ) {
            return;
        }

        $data = array();

        // process voucher codes
        $voucher_codes = get_option( 'laterpay_voucher_codes' );
        if ( $voucher_codes ) {
            foreach ( $voucher_codes as $pass_id => $codes ) {
                foreach ( $codes as $code => $price ) {
                    if ( is_array( $price ) ) {
                        continue;
                    }

                    $data[ $pass_id ][ $code ] = array(
                        'price' => number_format( LaterPay_Helper_View::normalize( $price ), 2 ),
                        'title' => '',
                    );
                }
            }
            update_option( 'laterpay_voucher_codes', $data );
        }

        // reinit data
        $data = array();

        // process gift codes
        $gift_codes = get_option( 'laterpay_gift_codes' );
        if ( $gift_codes ) {
            foreach ( $gift_codes as $pass_id => $codes ) {
                foreach ( $codes as $code => $price ) {
                    if ( is_array( $price ) ) {
                        continue;
                    }

                    $data[ $pass_id ][ $code ] = array(
                        'price' => 0,
                        'title' => '',
                    );
                }
            }
            update_option( 'laterpay_voucher_codes', $data );
        }
    }

    /**
     * Update revenue structure.
     *
     * @since 0.9.13
     *
     * @return void
     */
    public function maybe_update_revenue() {
        global $wpdb;

        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.14', '<' ) ) {
            return;
        }

        $terms_table = $wpdb->prefix . 'laterpay_terms_price';

        $wpdb->query( 'ALTER TABLE ' . $terms_table . " CHANGE revenue_model revenue_model ENUM('ppu','sis','ppul') NOT NULL DEFAULT 'ppu';" );
    }

    /**
     * Drop statistic tables
     *
     * @since 0.9.14
     *
     * @return void
     */
    public function drop_statistics_tables() {
        global $wpdb;

        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.14', '<' ) ) {
            return;
        }

        $table_history    = $wpdb->prefix . 'laterpay_payment_history';
        $table_post_views = $wpdb->prefix . 'laterpay_post_views';

        $table_history_exist    = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table_history . '\';' );
        $table_post_views_exist = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table_post_views . '\';' );

        if ( $table_history_exist ) {
            $wpdb->query( 'DROP TABLE ' . $table_history . ';' );
        }

        if ( $table_post_views_exist ) {
            $wpdb->query( 'DROP TABLE ' . $table_post_views . ';' );
        }
    }

    /**
     * Create custom tables and set the required options.
     *
     * @return void
     */
    public function install() {
        global $wpdb;

        // cancel the installation process, if the requirements check returns errors
        $notices = (array) $this->check_requirements();
        if ( count( $notices ) ) {
            $this->logger->warning( __METHOD__, $notices );
            return;
        }

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        $table_terms_price     = $wpdb->prefix . 'laterpay_terms_price';
        $table_passes          = $wpdb->prefix . 'laterpay_passes';

        $sql = "
            CREATE TABLE IF NOT EXISTS $table_terms_price (
                id int(11) NOT NULL AUTO_INCREMENT,
                term_id int(11) NOT NULL,
                price double NOT NULL DEFAULT '0',
                revenue_model enum('ppu','sis','ppul') NOT NULL DEFAULT 'ppu',
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE IF NOT EXISTS $table_passes (
                pass_id int(11) NOT NULL AUTO_INCREMENT,
                duration int(11) NULL DEFAULT NULL,
                period int(11) NULL DEFAULT NULL,
                access_to int(11) NULL DEFAULT NULL,
                access_category bigint(20) NULL DEFAULT NULL,
                price decimal(10,2) NULL DEFAULT NULL,
                revenue_model varchar(12) NULL DEFAULT NULL,
                title varchar(255) NULL DEFAULT NULL,
                description varchar(255) NULL DEFAULT NULL,
                is_deleted int(1) NOT NULL DEFAULT 0,
                PRIMARY KEY  (pass_id),
                KEY access_to (access_to),
                KEY period (period),
                KEY duration (duration)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        dbDelta( $sql );

        add_option( 'laterpay_teaser_content_only',                     '1' );
        add_option( 'laterpay_plugin_is_in_live_mode',                  '0' );
        add_option( 'laterpay_sandbox_merchant_id',                     $this->config->get( 'api.sandbox_merchant_id' ) );
        add_option( 'laterpay_sandbox_api_key',                         $this->config->get( 'api.sandbox_api_key' ) );
        add_option( 'laterpay_live_merchant_id',                        '' );
        add_option( 'laterpay_live_api_key',                            '' );
        add_option( 'laterpay_global_price',                            $this->config->get( 'currency.default_price' ) );
        add_option( 'laterpay_global_price_revenue_model',              'ppu' );
        add_option( 'laterpay_currency',                                $this->config->get( 'currency.default' ) );
        add_option( 'laterpay_ratings',                                 false );
        add_option( 'laterpay_bulk_operations',                         '' );
        add_option( 'laterpay_voucher_codes',                           '' );
        add_option( 'laterpay_gift_codes',                              '' );
        add_option( 'laterpay_voucher_statistic',                       '' );
        add_option( 'laterpay_gift_statistic',                          '' );
        add_option( 'laterpay_gift_codes_usages',                       '' );
        add_option( 'laterpay_purchase_button_positioned_manually',     '' );
        add_option( 'laterpay_time_passes_positioned_manually',         '' );
        add_option( 'laterpay_landing_page',                            '' );
        add_option( 'laterpay_only_time_pass_purchases_allowed',        0 );
        add_option( 'laterpay_is_in_visible_test_mode',                 0 );
        add_option( 'laterpay_hide_free_posts',                         0 );

        // advanced settings
        add_option( 'laterpay_sandbox_backend_api_url',                 'https://api.sandbox.laterpaytest.net' );
        add_option( 'laterpay_sandbox_dialog_api_url',                  'https://web.sandbox.laterpaytest.net' );
        add_option( 'laterpay_live_backend_api_url',                    'https://api.laterpay.net' );
        add_option( 'laterpay_live_dialog_api_url',                     'https://web.laterpay.net' );
        add_option( 'laterpay_api_merchant_backend_url',                'https://merchant.laterpay.net/' );
        add_option( 'laterpay_caching_compatibility',                   (bool) LaterPay_Helper_Cache::site_uses_page_caching() );
        add_option( 'laterpay_teaser_content_word_count',               '60' );
        add_option( 'laterpay_preview_excerpt_percentage_of_content',   '25' );
        add_option( 'laterpay_preview_excerpt_word_count_min',          '26' );
        add_option( 'laterpay_preview_excerpt_word_count_max',          '200' );
        add_option( 'laterpay_enabled_post_types',                      get_post_types( array( 'public' => true ) ) );
        add_option( 'laterpay_show_time_passes_widget_on_free_posts',   '' );
        add_option( 'laterpay_maximum_redemptions_per_gift_code',       1 );
        add_option( 'laterpay_debugger_enabled',                        defined( 'WP_DEBUG' ) && WP_DEBUG );
        add_option( 'laterpay_debugger_addresses',                      '127.0.0.1' );
        add_option( 'laterpay_api_fallback_behavior',                   0 );
        add_option( 'laterpay_api_enabled_on_homepage',                 1 );
        add_option( 'laterpay_main_color',                              '#01a99d' );
        add_option( 'laterpay_hover_color',                             '#01766e' );

        // @since 0.9.14
        delete_option( 'laterpay_access_logging_enabled' );

        // keep the plugin version up to date
        update_option( 'laterpay_version', $this->config->get( 'version' ) );

        // update / remove plugin options
        $this->maybe_update_options();

        // update vouchers structure
        $this->maybe_update_vouchers();

        // add ppul
        $this->maybe_update_revenue();

        // remove statistics tables from system
        $this->drop_statistics_tables();

        // clear opcode cache
        LaterPay_Helper_Cache::reset_opcode_cache();

        // update capabilities
        $laterpay_capabilities = new LaterPay_Core_Capability();
        $laterpay_capabilities->populate_roles();
    }

    /**
     * Trigger requirements check.
     *
     * @param LaterPay_Core_Event $event
     */
    public function trigger_update_capabilities( LaterPay_Core_Event $event ) {
        $new_event = new LaterPay_Core_Event();
        $new_event->set_echo( false );
        laterpay_event_dispatcher()->dispatch( 'laterpay_update_capabilities', $new_event );
    }

    /**
     * Update user roles capabilities.
     *
     * @param LaterPay_Core_Event $event
     */
    public function update_capabilities( LaterPay_Core_Event $event ) {
        list( $roles ) = $event->get_arguments() + array( array() );
        // update capabilities
        $laterpay_capabilities = new LaterPay_Core_Capability();
        $laterpay_capabilities->update_roles( (array) $roles );
    }
}
