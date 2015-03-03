<?php

/**
 * LaterPay installation controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Install extends LaterPay_Controller_Abstract
{

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
            echo '<div class="error">' . $out . '</div>';
        }
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
     * Update the existing database table for 'laterpay_payment_history' and add the 'revenue_model' column.
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_payment_history_add_revenue_model() {
        global $wpdb;

        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.9', '<' ) ) {
            return;
        }

        $table      = $wpdb->prefix . 'laterpay_payment_history';
        $columns    = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table .';' );

        // before version 0.9.9 we had no 'revenue_model'
        $is_up_to_date = false;
        $modified      = false;
        $passes_added  = false;
        $code_added    = false;

        foreach ( $columns as $column ) {
            if ( $column->Field === 'revenue_model' ) {
                $modified      = strpos( strtolower( $column->Type ), 'enum' ) !== false;
                $is_up_to_date = true;
            }

            if ( $column->Field === 'pass_id' ) {
                $passes_added  = true;
            }

            if ( $column->Field === 'code' ) {
                $code_added    = true;
            }
        }

        $this->logger->info(
            __METHOD__,
            array(
                'current_version'   => $current_version,
                'is_up_to_date'     => $is_up_to_date ? 'yes' : 'no',
            )
        );

        // if the table needs an update, add the 'revenue_model' column and set the current values to 'ppu'
        if ( ! $is_up_to_date ) {
            // add the missing column to our table
            $wpdb->query( "ALTER TABLE " . $table . " ADD revenue_model CHAR( 3 ) NOT NULL DEFAULT 'ppu';" );
            // update the existing data
            $wpdb->query( "UPDATE " . $table . " SET revenue_model = IF( " . $table . ".price < 5, 'ppu', 'sis' )" );
        }

        // modify revenue model column
        if ( ! $modified ) {
            $wpdb->query( "ALTER TABLE " . $table . " MODIFY revenue_model ENUM('ppu', 'sis') NOT NULL DEFAULT 'ppu';" );
        }

        // add pass id column for version >= 0.9.10
        if ( ! $passes_added && version_compare( $current_version, '0.9.10', '>=' ) ) {
            $wpdb->query( "ALTER TABLE " . $table . " ADD pass_id INT( 11 ) NOT NULL DEFAULT 0;" );
            $wpdb->query( "ALTER TABLE " . $table . " MODIFY post_id INT( 11 ) NOT NULL DEFAULT 0;" );
        }

        // add voucher code column for version >= 0.9.10
        if ( ! $code_added && version_compare( $current_version, '0.9.10', '>=' ) ) {
            $wpdb->query( "ALTER TABLE " . $table . " ADD code VARCHAR(6) NULL DEFAULT NULL;" );
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
            $wpdb->query( "ALTER TABLE " . $table . " MODIFY revenue_model ENUM('ppu', 'sis') NOT NULL DEFAULT 'ppu';" );
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
        if ( version_compare( $current_version, '0.9.10', '<' ) ) {
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

        foreach ( $columns as $column ) {
            if ( in_array( $column->Field, $removed_columns ) ) {
                $is_up_to_date = false;
            }
        }

        $this->logger->info(
            __METHOD__,
            array(
                'current_version'   => $current_version,
                'is_up_to_date'     => $is_up_to_date,
            )
        );

        // if the table needs an update
        if ( ! $is_up_to_date ) {
            $wpdb->query( 'ALTER TABLE ' . $table . ' DROP title_color, DROP description_color, DROP background_color, DROP background_path;' );
        }
    }

    /**
     * Adding option to allow only time pass purchases.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_add_only_time_pass_purchase_option() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        if ( get_option( 'laterpay_only_time_pass_purchases_allowed' ) === null ) {
            add_option( 'laterpay_only_time_pass_purchases_allowed', 0 );
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
     * @wp-hook get_post_metadata
     *
     * @param null      $return
     * @param int       $post_id    the current post_id
     * @param string    $meta_key   the meta_key
     *
     * @return null $return
     */
    public function migrate_pricing_post_meta( $return, $post_id, $meta_key ) {
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
                    $new_meta_values[$new_key] = $value;

                    // delete the old postmeta
                    delete_post_meta( $post_id, $old_meta_key );
                }
            }

            if ( ! empty( $new_meta_values ) ) {
                add_post_meta( $post_id, 'laterpay_post_prices', $new_meta_values, true );
            }
        }

        return $return;
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
     * Update post view table structure.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_post_views() {
        global $wpdb;

        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        $table   = $wpdb->prefix . 'laterpay_post_views';
        $columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table .';' );

        $is_up_to_date = false;

        foreach ( $columns as $column ) {
            if ( $column->Field === 'mode' ) {
                $is_up_to_date = true;
            }
        }

        if ( ! $is_up_to_date ) {
            $wpdb->query( "ALTER TABLE " . $table . " ADD mode ENUM('test', 'live') NOT NULL DEFAULT 'test';" );
            // count all existing data as 'live' data to ensure continuity of statistics after migration
            $wpdb->query( "UPDATE " . $table . " SET mode = 'live';" );
            $wpdb->query( "ALTER TABLE " . $table . " DROP INDEX post_id;" );
            $wpdb->query( "ALTER TABLE " . $table . " ADD UNIQUE INDEX (post_id, user_id, mode);" );
        }
    }

    /**
     * Clear dashboard cache.
     *
     * @since 0.9.11
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_clear_dashboard_cache() {
        $current_version = get_option( 'laterpay_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        // remove cache directory
        LaterPay_Helper_File::delete_directory( laterpay_get_plugin_config()->get( 'cache_dir' ) . 'cron/' );
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
        $table_history         = $wpdb->prefix . 'laterpay_payment_history';
        $table_post_views      = $wpdb->prefix . 'laterpay_post_views';
        $table_passes          = $wpdb->prefix . 'laterpay_passes';

        $sql = "
            CREATE TABLE $table_terms_price (
                id                INT(11)            NOT NULL AUTO_INCREMENT,
                term_id           INT(11)            NOT NULL,
                price             DOUBLE             NOT NULL DEFAULT '0',
                revenue_model     ENUM('ppu', 'sis') NOT NULL DEFAULT 'ppu',
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE $table_history (
                id                INT(11)              NOT NULL AUTO_INCREMENT,
                mode              ENUM('test', 'live') NOT NULL DEFAULT 'test',
                post_id           INT(11)              NOT NULL DEFAULT 0,
                currency_id       INT(11)              NOT NULL,
                price             FLOAT                NOT NULL,
                date              DATETIME             NOT NULL,
                ip                INT                  NOT NULL,
                hash              VARCHAR(32)          NOT NULL,
                revenue_model     ENUM('ppu', 'sis')   NOT NULL DEFAULT 'ppu',
                pass_id           INT(11)              NOT NULL DEFAULT 0,
                code              VARCHAR(6)           NULL DEFAULT NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE $table_post_views (
                post_id           INT(11)              NOT NULL,
                mode              ENUM('test', 'live') NOT NULL DEFAULT 'test',
                date              DATETIME             NOT NULL,
                user_id           VARCHAR(32)          NOT NULL,
                count             BIGINT UNSIGNED      NOT NULL DEFAULT 1,
                ip                VARBINARY(16)        NOT NULL,
                UNIQUE KEY  (post_id, user_id, mode)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
        dbDelta( $sql );

        $sql = "
            CREATE TABLE IF NOT EXISTS $table_passes (
                pass_id           INT(11)       NOT NULL AUTO_INCREMENT,
                duration          INT(11)       NULL DEFAULT NULL,
                period            INT(11)       NULL DEFAULT NULL,
                access_to         INT(11)       NULL DEFAULT NULL,
                access_category   BIGINT(20)    NULL DEFAULT NULL,
                price             DECIMAL(10,2) NULL DEFAULT NULL,
                revenue_model     VARCHAR(12)   NULL DEFAULT NULL,
                title             VARCHAR(255)  NULL DEFAULT NULL,
                description       VARCHAR(255)  NULL DEFAULT NULL,
                PRIMARY KEY (pass_id),
                INDEX access_to (access_to),
                INDEX period (period),
                INDEX duration (duration)
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
        add_option( 'laterpay_only_time_pass_purchases_allowed',        0  );
        add_option( 'laterpay_is_in_visible_test_mode',                 0  );

        // advanced settings
        add_option( 'laterpay_sandbox_backend_api_url',                 'https://api.sandbox.laterpaytest.net' );
        add_option( 'laterpay_sandbox_dialog_api_url',                  'https://web.sandbox.laterpaytest.net' );
        add_option( 'laterpay_live_backend_api_url',                    'https://api.laterpay.net' );
        add_option( 'laterpay_live_dialog_api_url',                     'https://web.laterpay.net' );
        add_option( 'laterpay_api_merchant_backend_url',                'https://merchant.laterpay.net/' );
        add_option( 'laterpay_access_logging_enabled',                  1 );
        add_option( 'laterpay_caching_compatibility',                   (bool) LaterPay_Helper_Cache::site_uses_page_caching() );
        add_option( 'laterpay_show_purchase_button',                    1 );
        add_option( 'laterpay_teaser_content_word_count',               '60' );
        add_option( 'laterpay_preview_excerpt_percentage_of_content',   '25' );
        add_option( 'laterpay_preview_excerpt_word_count_min',          '26' );
        add_option( 'laterpay_preview_excerpt_word_count_max',          '200' );
        add_option( 'laterpay_enabled_post_types',                      get_post_types( array( 'public' => true ) ) );
        add_option( 'laterpay_show_time_passes_widget_on_free_posts',   '' );
        add_option( 'laterpay_maximum_redemptions_per_gift_code',       1 );

        // keep the plugin version up to date
        update_option( 'laterpay_version', $this->config->get( 'version' ) );

        // update / remove plugin options
        $this->maybe_update_options();

        // clear opcode cache
        LaterPay_Helper_Cache::reset_opcode_cache();

        // update capabilities
        $laterpay_capabilities = new LaterPay_Core_Capability ();
        $laterpay_capabilities->populate_roles();
    }
}
