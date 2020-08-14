<?php

/**
 * LaterPay controller for migrating custom table data to CPT.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Compatibility_Migrate extends LaterPay_Controller_Base {
    /**
     * Contains count of items in old table.
     */
    private $subscriptions_count, $timepass_count, $category_price_count ;


    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_admin_notices' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'render_data_migration_notice' ),
            ),
            'wp_ajax_laterpay_start_migration' => array(
                array( 'laterpay_on_plugin_is_working', 200 ),
                array( 'laterpay_on_ajax_send_json', 300 ),
                array( 'ajax_start_migration', 400 ),
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
    public function render_data_migration_notice( LaterPay_Core_Event $event ) {

        $current_version = get_option( 'laterpay_plugin_version' );

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) || version_compare( $current_version, '2.3.0', '>' ) ) {
            return;
        }

        if ( ! laterpay_check_is_vip() && ! laterpay_is_migration_complete() ) {
            printf( '<div id="lp_migration_notice" class="notice notice-error"><p>%s <a id="lp_js_startDataMigration" href="#">%s</a> %s</p> <p>%s</p> </br.> %s <a id="lp_js_startDataMigrationTwo" href="#">%s</a> %s</div>',
                esc_html__( 'Laterpay has updated their plugin to remove dependencies on custom tables. Please', 'laterpay' ),
                esc_html__( 'migrate your data', 'laterpay' ),
                esc_html__( 'today.', 'laterpay' ),
                esc_html__( 'This will be required before you can update to future versions of Laterpay and will not result in any visible changes to your site or your plugin set up.', 'laterpay' ),
                esc_html__( 'Click', 'laterpay' ),
                esc_html__( 'here', 'laterpay' ),
                esc_html__( 'to migrate data.', 'laterpay' ) );
        }

        // load page-specific JS
        wp_register_script(
            'laterpay-migrate-data',
            $this->config->js_url . '/laterpay-migrate-data.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );

        $nonce = wp_create_nonce( 'migration_nonce' ) ;

        wp_enqueue_script( 'laterpay-migrate-data' );
        wp_localize_script( 'laterpay-migrate-data', 'migration_nonce', $nonce );

        wp_localize_script(
            'laterpay-migrate-data',
            'lp_i18n',
            array(
                'MigratingData'           => esc_html( esc_js( __( 'Migrating Data',             'laterpay' ) ) ),
                'MigratingSubscriptions'  => esc_html( esc_js( __( 'Migrating Subscriptions',    'laterpay' ) ) ),
                'MigratingTimepasses'     => esc_html( esc_js( __( 'Migrating Time Passes.',      'laterpay' ) ) ),
                'MigratingCategoryPrices' => esc_html( esc_js( __( 'Migrating Category Prices.', 'laterpay' ) ) ),
                'MigrationCompleted'      => esc_html( esc_js( __( 'Migration Completed.',       'laterpay' ) ) ),
                'RemovingCustomTables'    => esc_html( esc_js( __( 'Migration Cleanup Started',  'laterpay' ) ) ),
                'RemovedCustomTables'     => esc_html( esc_js( __( 'Migration Cleanup Completed', 'laterpay' ) ) ),
            )
        );
    }

    /**
     * Starts data migration.
     *
     * @wp-hook wp_ajax_laterpay_start_migration
     * @param LaterPay_Core_Event $event
     *
     * @return void
     */
    public function ajax_start_migration( LaterPay_Core_Event $event ) {

        if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
            return;
        }

        check_ajax_referer('migration_nonce', 'security' );

        $result  = [];
        $migrate = filter_input( INPUT_POST, 'migrate', FILTER_SANITIZE_STRING );
        $offset  = filter_input( INPUT_POST, 'offset', FILTER_SANITIZE_NUMBER_INT );

        if ( isset( $migrate ) && isset( $offset ) ) {

            if ( 'subscription' === $migrate ) {

                $new_offset = $this->migrate_subscription_table( $offset );
                $this->subscriptions_count = LaterPay_Model_SubscriptionWP::get_instance()->get_subscriptions_count();

                $result['subscription_migrated'] = $new_offset >= $this->subscriptions_count;

                if ( $result['subscription_migrated'] ) {
                    $result['offset'] = LaterPay_Model_TimePassWP::get_instance( true )->get_time_passes_count();
                } else {
                    $result['offset'] = $new_offset;
                }

            } elseif ( 'time_pass' === $migrate ) {

                $new_offset = $this->migrate_timepass_table( $offset );
                $this->timepass_count = LaterPay_Model_TimePassWP::get_instance()->get_time_passes_count();

                $result['time_pass_migrated'] = $new_offset >= $this->timepass_count;

                if ( $result['time_pass_migrated'] ) {
                    $result['offset'] = LaterPay_Model_CategoryPriceWP::get_instance( true )->get_categories_with_defined_price_count();
                } else {
                    $result['offset'] = $new_offset;
                }

            } elseif ( 'category_price' === $migrate ) {

                $new_offset = $this->migrate_terms_table( $offset );
                $this->category_price_count = LaterPay_Model_CategoryPriceWP::get_instance()->get_categories_with_defined_price_count();

                $result['category_price_migrated'] = $new_offset >= $this->category_price_count;

                if ( $result['category_price_migrated'] ) {
                    $result['offset'] = 0;
                    update_option( 'laterpay_data_migrated_to_cpt', '1' );
                    $result['cleanup'] = $this->drop_custom_tables();
                } else {
                    $result['offset'] = $new_offset;
                }

            }

            $event->set_result( $result );
        }
    }

    /**
     * Migrate timepass table to cpt.
     *
     * @param int $offset migration starting id of timepass.
     *
     * @return int new offset.
     */
    public function migrate_timepass_table( $offset ) {
        global $wpdb;

        $timepass_table    = $wpdb->prefix . 'laterpay_passes';
        $sql               = "SELECT * FROM {$timepass_table} limit %d, %d ";
        $passes            = $wpdb->get_results( $wpdb->prepare( $sql, $offset, 10 ) );
        $laterpay_timepass = LaterPay_Model_TimePassWP::get_instance( true );
        if ( ! empty( $passes ) ) {
            $size  = count( $passes );
            $count = 0;
            foreach ( $passes as $pass ) {
                $count ++;
                $args = array(
                    'post_type'      => 'lp_passes',
                    'no_found_rows'  => true,
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                    'meta_key'       => '_lp_id',
                    'meta_value'     => $pass->pass_id,
                );

                $result = new WP_Query( $args );
                if ( ! $result->have_posts() ) {

                    $data = array(
                        'pass_id'         => '',
                        'counter_id'      => (int) $pass->pass_id,
                        'duration'        => (int) $pass->duration,
                        'period'          => (int) $pass->period,
                        'access_to'       => (int) $pass->access_to,
                        'access_category' => (int) $pass->access_category,
                        'price'           => (float) $pass->price,
                        'title'           => $pass->title,
                        'description'     => $pass->description,
                    );
                    $success = $laterpay_timepass->update_time_pass( $data );
                    if ( $count === $size ) {
                        update_option( 'lp_pass_count', absint( $pass->pass_id ) );
                    }
                    if ( 1 === (int) $pass->is_deleted ) {
                        $laterpay_timepass->delete_time_pass_by_id( $success['pass_id'] );
                    }
                }
            }
        }

        return $offset + count( $passes );
    }

    /**
     * Migrate subscription table to cpt.
     *
     * @param int $offset migration starting id of subscription.
     *
     * @return int new offset.
     */
    public function migrate_subscription_table( $offset ) {
        global $wpdb;

        if ( 0 === $offset ) {
            $offset = LaterPay_Model_SubscriptionWP::get_instance( true )->get_subscriptions_count();
        }

        $subscription_table    = $wpdb->prefix . 'laterpay_subscriptions';
        $sql                   = "SELECT * FROM {$subscription_table} limit %d, %d ";
        $subscriptions         = $wpdb->get_results( $wpdb->prepare( $sql, $offset, 10 ) );
        $laterpay_subscription = LaterPay_Model_SubscriptionWP::get_instance( true );

        if ( ! empty( $subscriptions ) ) {
            $size  = count( $subscriptions );
            $count = 0;
            foreach ( $subscriptions as $subscription ) {
                $count ++;
                $args = array(
                    'post_type'      => 'lp_subscription',
                    'no_found_rows'  => true,
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                    // Meta query is required for post id.
                    'meta_key'       => '_lp_id', // phpcs:ignore
                    'meta_value'     => $subscription->id, // phpcs:ignore
                );

                $result = new WP_Query( $args );
                if ( ! $result->have_posts() ) {

                    $data = array(
                        'ID'              => '',
                        'counter_id'      => (int) $subscription->id,
                        'duration'        => (int) $subscription->duration,
                        'period'          => (int) $subscription->period,
                        'access_to'       => (int) $subscription->access_to,
                        'access_category' => (int) $subscription->access_category,
                        'price'           => (float) $subscription->price,
                        'title'           => $subscription->title,
                        'description'     => $subscription->description,
                    );

                    $success = $laterpay_subscription->update_subscription( $data, true );
                    if ( $count === $size ) {
                        update_option( 'lp_sub_count', absint( $subscription->id ) );
                    }

                    if ( 1 === (int) $subscription->is_deleted ) {
                        $laterpay_subscription->delete_subscription_by_id( $success['id'] );
                    }
                }
            }
        }

        return $offset + count( $subscriptions );
    }

    /**
     * Migrate term table to cpt.
     *
     * @param int $offset migration starting id of term.
     *
     * @return int new offset.
     */
    public function migrate_terms_table( $offset ) {
        global $wpdb;

        $term_table     = $wpdb->prefix . 'laterpay_terms_price';
        $sql            = "SELECT * FROM {$term_table} limit %d, %d ";
        $terms          = $wpdb->get_results( $wpdb->prepare( $sql, $offset, 10 ) );
        $laterpay_terms = LaterPay_Model_CategoryPriceWP::get_instance( true );

        if ( ! empty( $terms ) ) {

            foreach ( $terms as $term ) {
                $term_exist = get_term_meta( $term->term_id, '_lp_revenue_model', true );

                if ( empty( $term_exist ) ) {
                    $laterpay_terms->set_category_price( $term->term_id, $term->price, $term->revenue_model );
                }
            }

        }

        return $offset + count( $terms );
    }

    /**
     *  Drop laterpay custom tables.
     *
     * @return bool
     */
    public function drop_custom_tables() {

        if ( laterpay_is_migration_complete() ) {
            global $wpdb;

            $timepass_table     = $wpdb->prefix . 'laterpay_passes';
            $subscription_table = $wpdb->prefix . 'laterpay_subscriptions';
            $term_table         = $wpdb->prefix . 'laterpay_terms_price';

            $wpdb->query( 'DROP TABLE IF EXISTS ' . $timepass_table . ';' );
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $subscription_table . ';' );
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $term_table . ';' );

            return true;
        }

    }
}
