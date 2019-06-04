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
            'laterpay_update_capabilities' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'update_capabilities' ),
            ),
            'laterpay_admin_notices' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'render_requirements_notices' ),
            ),
            'laterpay_init_finished' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'install_updates' ),
            )
        );
    }

    /**
     * Render admin notices, if requirements are not fulfilled.
     *
     * @wp-hook admin_notices
     *
     * @return  void
     */
    public function render_requirements_notices( LaterPay_Core_Event $event ) {
        $notices = $this->check_requirements();
        if ( count( $notices ) > 0 ) {
            $out = join( "\n", $notices );

            echo '<div class="error">';
            echo wp_kses( $out, [ 'p' => [], 'strong' => [] ] );
            echo '</div>';


            $event->stop_propagation();
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
        $required_php_version           = '5.6';
        $required_wp_version            = '4.6';
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

        if ( ! laterpay_check_is_vip() && ! laterpay_is_migration_complete() ) {

            $notices[] = '';

            printf(
                '<div class="notice notice-error is-dismissible"> <p>%s</p> <p>%s</p> <p>%s</p> <p>%s <a href="%s" target="_blank">%s</a> %s</p></div>',
                esc_html__( 'WARNING! Your LaterPay plugin has been disabled.', 'laterpay' ),
                esc_html__( 'LaterPay has updated their plugin to remove dependencies on custom tables. You must migrate your data before you can re-activate your plugin.', 'laterpay' ),
                esc_html__( 'To complete this process, please Rollback your plugin to v.2.3.0 and click the header to migrate your data. Once this is complete you will be free to successfully update and re-activate your plugin.', 'laterpay' ),
                esc_html__( 'If you do not have a preferred Rollback method, we recommend', 'laterpay' ),
                'https://wordpress.org/plugins/wp-rollback/',
                'WP Rollback',
                esc_html__( 'Please contact support@laterpay.net if you have any questions or concerns.', 'laterpay' )
            );
        }

        // deactivate plugin, if requirements are not fulfilled
        if ( count( $notices ) > 0 ) {
            // suppress 'Plugin activated' notice
            unset( $_GET['activate'] ); // WPCS: input var ok.
            deactivate_plugins( $this->config->plugin_base_name );
            $notices[] = __( 'The LaterPay plugin could not be installed. Please fix the reported issues and try again.', 'laterpay' );
        }

        return $notices;
    }

    /**
     * Compare plugin version with latest version and perform an update, if required.
     *
     * @return void
     */
    public function install_updates() {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, $this->config->version, '!=' ) ) {
            $this->install();
        }
    }

    /**
     * Refresh config
     *
     * @return void
     */
    public function refresh_config()
    {
        parent::refresh_config();
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
        $current_version = get_option( 'laterpay_plugin_version' );
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
        $current_version = get_option( 'laterpay_plugin_version' );
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
            if ( $option_value !== $correct_value ) {
                update_option( $option_name, $correct_value );
            }
        }
    }

    /**
     * Update the existing options during update.
     *
     * @return void
     */
    protected function maybe_update_options() {
        $current_version = get_option( 'laterpay_plugin_version' );

        if ( version_compare( $current_version, '0.9.8.1', '>=' ) ) {
            delete_option( 'laterpay_plugin_is_activated' );
        }

        if ( version_compare( $current_version, '0.9.14', '>=' ) ) {
            delete_option( 'laterpay_access_logging_enabled' );
        }

        if ( version_compare( $current_version, '0.9.25', '>' ) ) {
            delete_option( 'laterpay_version' );
        }

        // actualize sandbox creds values
        LaterPay_Helper_Config::prepare_sandbox_creds();
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
                    if ( $old_meta_key === 'laterpay_post_pricing_type' && in_array( intval( $value ), [ 0, 1 ], true ) ) {
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
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.11', '<' ) ) {
            return;
        }

        $unlimited_role = get_option( 'laterpay_unlimited_access_to_paid_content' );

        if ( false !== $unlimited_role ) {
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
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.14', '>' ) ) {
            return;
        }

        $data = array();

        // process voucher codes
        $voucher_codes = get_option( 'laterpay_voucher_codes' );
        if ( $voucher_codes ) {
            foreach ( $voucher_codes as $pass_id => $codes ) {
                foreach ( $codes as $code => $price ) {
                    if ( is_array( $price ) ) {
                        $data[ $pass_id ][ $code ] = $price;
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
                        $data[ $pass_id ][ $code ] = $price;
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
     * Init color options
     *
     * @since 0.9.17
     *
     * @return void
     */
    public function init_colors_options() {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.17', '<' ) ) {
            return;
        }

        add_option( 'laterpay_main_color',  '#01a99d' );
        add_option( 'laterpay_hover_color', '#01766e' );
    }

    /**
     * Remove old api settings
     *
     * @since 0.9.23
     *
     * @return void
     */
    public function remove_old_api_settings() {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.23', '<' ) ) {
            return;
        }

        delete_option( 'laterpay_sandbox_backend_api_url' );
        delete_option( 'laterpay_sandbox_dialog_api_url' );
        delete_option( 'laterpay_live_backend_api_url' );
        delete_option( 'laterpay_live_dialog_api_url' );
        delete_option( 'laterpay_api_merchant_backend_url' );
    }

    /**
     * Set (reset) any customization for overlay
     *
     * @since 0.9.26.2
     */
    public function set_overlay_defaults()
    {
        $overlay_default_options = LaterPay_Helper_Appearance::get_default_options();

        foreach ($overlay_default_options as $key => $value) {
            if ( false === get_option( 'laterpay_overlay_' . $key ) ) {
                update_option('laterpay_overlay_' . $key, $value);
            }
        }
    }

    /**
     * Change teaser mode
     *
     * @since 1.0.0
     */
    public function change_teaser_mode()
    {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.10.0', '>' ) ) {
            return;
        }

        // set proper teaser mode
        $teaser_mode = get_option( 'laterpay_teaser_content_only' );
        if ( false !== $teaser_mode ) {
            update_option( 'laterpay_teaser_mode', '0' );
        } else {
            update_option( 'laterpay_teaser_mode', '1' );
        }

        // remove old property and set new one
        delete_option( 'laterpay_teaser_content_only' );

    }

    /**
     * Create custom tables and set the required options.
     *
     * @return void
     */
    public function install() {

        // check if current environment is not vip.
        $is_not_vip                 = ( ! laterpay_check_is_vip() );
        // check plugin version to decide fresh install.
        $plugin_version             = get_option( 'laterpay_plugin_version' );
        // check if table_migration has to be run.
        $should_run_table_migration = ( $is_not_vip && false !== $plugin_version && class_exists( 'LaterPay_Compatibility_InstallCompat' ) );

        if ( false === $plugin_version ) {
            update_option( 'laterpay_data_migrated_to_cpt', '1' );
        }

        add_option( 'laterpay_teaser_mode',                             '2' );
        add_option( 'laterpay_plugin_is_in_live_mode',                  '0' );
        add_option( 'laterpay_sandbox_merchant_id',                     $this->config->get( 'api.sandbox_merchant_id' ) );
        add_option( 'laterpay_sandbox_api_key',                         $this->config->get( 'api.sandbox_api_key' ) );
        add_option( 'laterpay_live_merchant_id',                        '' );
        add_option( 'laterpay_live_api_key',                            '' );
        add_option( 'laterpay_global_price',                            $this->config->get( 'currency.default_price' ) );
        add_option( 'laterpay_global_price_revenue_model',              'ppu' );
        add_option( 'laterpay_voucher_codes',                           '' );
        add_option( 'laterpay_subscription_voucher_codes',              '' );
        add_option( 'laterpay_gift_codes',                              '' );
        add_option( 'laterpay_voucher_statistic',                       '' );
        add_option( 'laterpay_gift_statistic',                          '' );
        add_option( 'laterpay_gift_codes_usages',                       '' );
        add_option( 'laterpay_purchase_button_positioned_manually',     '' );
        add_option( 'laterpay_time_passes_positioned_manually',         '' );

        // advanced settings
        add_option( 'laterpay_region',                                  'us' );
        add_option( 'laterpay_caching_compatibility',                   (bool) LaterPay_Helper_Cache::site_uses_page_caching() );
        add_option( 'laterpay_teaser_content_word_count',               '60' );
        add_option( 'laterpay_preview_excerpt_percentage_of_content',   '25' );
        add_option( 'laterpay_preview_excerpt_word_count_min',          '26' );
        add_option( 'laterpay_preview_excerpt_word_count_max',          '200' );
        add_option( 'laterpay_enabled_post_types', [ 'post' => 'post', 'attachment' => 'attachment' ] );
        add_option( 'laterpay_require_login',                           '' );
        add_option( 'laterpay_maximum_redemptions_per_gift_code',       1 );
        add_option( 'laterpay_api_fallback_behavior',                   0 );

        // keep the plugin version up to date
        update_option( 'laterpay_plugin_version', $this->config->get( 'version' ) );

        // update capabilities
        $laterpay_capabilities = new LaterPay_Core_Capability();
        $laterpay_capabilities->populate_roles();

        // perform data updates
        if ( $should_run_table_migration ) {
            $maybe_perform_updates = LaterPay_Compatibility_InstallCompat::get_instance();
            $maybe_perform_updates->maybe_update_meta_keys();
            $maybe_perform_updates->maybe_update_terms_price_table();
            $maybe_perform_updates->maybe_update_currency_to_euro();
        }
        $this->maybe_update_options();
        $this->maybe_add_is_in_visible_test_mode_option();
        $this->maybe_clean_api_key_options();
        $this->maybe_update_unlimited_access();
        if ( $should_run_table_migration ) {
            $maybe_perform_updates->maybe_update_time_passes_table();
        }
        $this->maybe_update_vouchers();
        if ( $should_run_table_migration ) {
            $maybe_perform_updates->drop_statistics_tables();
        }
        $this->init_colors_options();
        $this->set_overlay_defaults();
        $this->remove_old_api_settings();
        if ( $should_run_table_migration ) {
            $maybe_perform_updates->maybe_remove_ppul();
        }
        $this->change_teaser_mode();
        $this->update_default_pricing_behaviour();
        $this->init_ga_options();
        $this->remove_laterpay_pro_merchant_option();
        $this->remove_custom_table_support();
        $this->update_appearance_config();
        $this->add_update_highlights();

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

    /**
     * Update Default Pricing Behaviour to Provide Backwards Compatibility
     *
     * @since 2.1.0
     */
    public function update_default_pricing_behaviour() {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '2.0.0', '<' ) ) {
            return;
        }

        if ( false === get_option( 'laterpay_post_price_behaviour' ) ) {
            $only_timepass        = (bool) get_option( 'laterpay_only_time_pass_purchases_allowed' );
            $current_global_price = ( float) get_option( 'laterpay_global_price' );

            if ( true === $only_timepass ) {
                update_option( 'laterpay_post_price_behaviour', 1 );
            } elseif ( floatval( 0.00 ) === $current_global_price ) {
                update_option( 'laterpay_post_price_behaviour', 0 );
            } else {
                update_option( 'laterpay_post_price_behaviour', 2 );
            }

            delete_option( 'laterpay_only_time_pass_purchases_allowed' );
        }
    }

    /**
     * Init ga options.
     * Add options needed for sending Google Analytics Events.
     *
     * @since 2.2.0
     *
     * @return void
     */
    public function init_ga_options() {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '2.1.0', '<' ) ) {
            return;
        }


        $lp_personal_data = [];
        $lp_tracking_data = [];

        if ( false === get_option( 'laterpay_user_tracking_data' ) ) {

            $lp_personal_data['laterpay_ga_personal_enabled_status'] = 0;
            $lp_personal_data['laterpay_ga_personal_ua_id']          = '';

            /**
             *  If user has Google Analytics for WordPress by MonsterInsights Plugin installed and configured correctly,
             *  we will try to detect the UA-ID in that and add it to User's Google Analytics Settings.
             *  Plugin URL : https://wordpress.org/plugins/google-analytics-for-wordpress/
             */
            if ( is_multisite() ) {
                $monster_insights_network_profile = get_site_option( 'monsterinsights_network_profile', array() );
                $monster_ua_id                    = ( ! empty( $monster_insights_network_profile['ua'] ) ? $monster_insights_network_profile['ua'] : '' );
            } else {
                $monster_insights_profile = get_option( 'monsterinsights_site_profile', array() );
                $monster_ua_id            = ( ! empty( $monster_insights_profile['viewname'] ) ? $monster_insights_profile['viewname'] : '' );
            }

            if ( ! empty( $monster_ua_id ) ) {
                $lp_personal_data['auto_detected']              = 1;
                $lp_personal_data['laterpay_ga_personal_ua_id'] = $monster_ua_id;
            }

            // Add Personal Google Analytics Data.
            update_option( 'laterpay_user_tracking_data', $lp_personal_data );
        }

        if ( false === get_option( 'laterpay_tracking_data' ) ) {
            $lp_is_plugin_live = LaterPay_Helper_View::is_plugin_in_live_mode();

            // Add LaterPay UA-ID according to status.
            if ( $lp_is_plugin_live ) {
                $lp_tracking_data['laterpay_ga_ua_id'] = $this->config->get( 'tracking_ua_id.live' );
            } else {
                $lp_tracking_data['laterpay_ga_ua_id'] = $this->config->get( 'tracking_ua_id.sandbox' );
            }

            $lp_tracking_data['laterpay_ga_enabled_status'] = 1;

            // Add LaterPay Google Analytics Data.
            update_option( 'laterpay_tracking_data', $lp_tracking_data );
        }
    }

    /**
     * Clean up LaterPay Pro Merchant option.
     *
     * @since 2.3.0
     */
    public function remove_laterpay_pro_merchant_option() {
        $current_version = get_option( 'laterpay_plugin_version' );

        if ( version_compare( $current_version, '2.2.2', '<' ) ) {
            return;
        }

        // Delete option for all versions above 2.2.2.
        delete_option('laterpay_pro_merchant' );
    }

    /**
     * Remove custom table support.
     *
     * @since 2.3.0
     */
    public function remove_custom_table_support() {
        $current_version = get_option( 'laterpay_plugin_version' );

        if ( laterpay_check_is_vip() || version_compare( $current_version, '0.10.0', '<' ) ) {
            return;
        }

        global $wpdb;

        $table       = $wpdb->prefix . 'laterpay_terms_price';
        $table_terms = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table . '\';' );

        if ( $table_terms && laterpay_is_migration_complete() ) {

            $timepass_table     = $wpdb->prefix . 'laterpay_passes';
            $subscription_table = $wpdb->prefix . 'laterpay_subscriptions';
            $term_table         = $wpdb->prefix . 'laterpay_terms_price';

            $wpdb->query( 'DROP TABLE IF EXISTS ' . $timepass_table . ';' );
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $subscription_table . ';' );
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $term_table . ';' );

        }

    }

    /**
     * Update appearance config based on current value to match new options.
     */
    private function update_appearance_config() {
        $current_version = get_option( 'laterpay_plugin_version' );

        if ( version_compare( $current_version, '2.5.2', '<' ) ) {
            return;
        }

        if ( false === get_option( 'lp_appearance_config' ) && false === get_option( 'lp_body_text' ) ) {
            LaterPay_Helper_Appearance::update_appearance_configs();
        }

    }

    /**
     * Add Update Highlights Notice.
     */
    public function add_update_highlights() {

        $current_version = get_option( 'laterpay_plugin_version' );

        $update_highlights = [];

        if ( ! empty( $current_version ) ) {

            if ( version_compare( $current_version, '2.5.3', '<' ) ) {
                return;
            }

            $update_highlights = [
                'version' => '2.6.0',
                'notice'  => __( 'You can now fully customize the appearance of your payment overlay. Visit the Appearance tab to check it out!', 'laterpay' ),
            ];

            update_option( 'lp_update_highlights', $update_highlights );
            return;
        }

        update_option( 'lp_update_highlights', $update_highlights );

    }
}
