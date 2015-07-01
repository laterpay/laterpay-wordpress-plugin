<?php

/**
 * LaterPay dashboard controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Dashboard extends LaterPay_Controller_Admin_Base
{

    /**
     * Sections are used by the Ajax laterpay_get_dashboard callback.
     * Every section is mapped to a private method within this controller.
     *
     * @var array
     */
    private $ajax_sections = array(
        'converting_items',
        'selling_items',
        'revenue_items',
        'most_least_converting_items',
        'most_least_selling_items',
        'most_least_revenue_items',
        'metrics',
    );

    private $cache_file_exists;
    private $cache_file_is_broken;

    private $ajax_nonce = 'laterpay_dashboard';

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_enqueue_script(
            'laterpay-flot',
            $this->config->get( 'js_url' ) . 'vendor/lp_jquery.flot.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_enqueue_script(
            'laterpay-peity',
            $this->config->get( 'js_url' ) . 'vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        /* MOVED: #797 Comment out sales statistics
        wp_enqueue_script(
            'laterpay-backend-dashboard',
            $this->config->get( 'js_url' ) . 'laterpay-backend-dashboard.js',
            array( 'jquery', 'laterpay-flot', 'laterpay-peity' ),
            $this->config->get( 'version' ),
            true
        );
        */
        // pass localized strings and variables to script
        $i18n = array(
            'noData'    => __( 'No data available', 'laterpay' ),
            'tooltips'  => array(
                'day'   => array(
                    'next'  => __( 'Show next day', 'laterpay' ),
                    'prev'  => __( 'Show previous day', 'laterpay' ),
                ),
                'week'  => array(
                    'next'  => __( 'Show next 8 days', 'laterpay' ),
                    'prev'  => __( 'Show previous 8 days', 'laterpay' ),
                ),
                '2-weeks'   => array(
                    'next'  => __( 'Show next 2 weeks', 'laterpay' ),
                    'prev'  => __( 'Show previous 2 weeks', 'laterpay' ),
                ),
                'month' => array(
                    'next'  => __( 'Show next month', 'laterpay' ),
                    'prev'  => __( 'Show previous month', 'laterpay' ),
                ),
            )
        );

        $localization = array(
            'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
            'nonces'    => array( 'dashboard' => wp_create_nonce( $this->ajax_nonce ) ),
            'locale'    => get_locale(),
            'i18n'      => $i18n,
        );

        wp_localize_script(
            'laterpay-backend-dashboard',
            'lpVars',
            $localization
        );

        $this->logger->info( __METHOD__, $localization );
    }

    /**
     * @see LaterPay_Core_View::render_page
     */
    public function render_page() {
        $this->load_assets();

        $post_views         = new LaterPay_Model_Post_View();
        $post_views_args    = array(
            'fields' => array(
                'MIN(date) as end_timestamp'
            )
        );
        $end_timestamp = $post_views->get_results( $post_views_args );
        $end_timestamp = strtotime( $end_timestamp[0]->end_timestamp );

        $view_args = array(
            'plugin_is_in_live_mode'    => $this->config->get( 'is_in_live_mode' ),
            'top_nav'                   => $this->get_menu(),
            'admin_menu'                => LaterPay_Helper_View::get_admin_menu(),
            'currency'                  => get_option( 'laterpay_currency' ),
            'end_timestamp'             => $end_timestamp,
            'interval_start'            => strtotime( '-1 days' ),
            'interval_end'              => strtotime( '-8 days' ),

            // in wp-config.php the user can disable the WP cron completely OR replace it with real server cron jobs.
            // this view variable can be used to show additional information that *maybe* the dashboard
            // data will not refresh automatically
            'is_cron_enabled'           => ! defined( 'DISABLE_WP_CRON' ) || ( defined( 'DISABLE_WP_CRON' ) && ! DISABLE_WP_CRON ),
            'cache_file_exists'         => $this->cache_file_exists,
            'cache_file_is_broken'      => $this->cache_file_is_broken,
        );

        $this->assign( 'laterpay', $view_args );

        $this->render( 'backend/dashboard' );
    }

    /**
     * Ajax callback to refresh the dashboard data.
     *
     * @wp-hook wp_ajax_laterpay_get_dashboard_data
     *
     * @return void
     */
    public function ajax_get_dashboard_data() {
        $this->validate_ajax_nonce();
        $this->validate_ajax_section_callback();

        $options = $this->get_ajax_request_options( $_POST );

        if ( $options['refresh'] ) {
            $section    = $options['section'];
            $data       = $this->$section( $options );
            LaterPay_Helper_Dashboard::refresh_cache_data( $options, $data );
        }

        $data = LaterPay_Helper_Dashboard::get_cache_data( $options );

        if ( empty( $data ) ) {
            $response = array(
                'message'   => sprintf( __( 'Cache data is empty on <code>%s</code>', 'laterpay' ), $options['section'] ),
                'success'   => false,
            );
        } else {
            $response = array(
                'data'      => $data,
                'success'   => true,
            );
        }

        if ( $this->config->get( 'debug_mode' ) ) {
            $response['options'] = $options;
        }

        wp_send_json( $response );
    }

    /**
     * Callback for WP cron to refresh today's dashboard data.
     * The cron job provides two parameters for {x} days back and {n} count of items to
     * register your own cron with custom parameters to cache data.
     *
     * @wp-hook laterpay_refresh_dashboard_data
     *
     * @param int       $start_timestamp
     * @param int       $count
     * @param string    $interval
     *
     * @return void
     */
    public function refresh_dashboard_data( $start_timestamp = null, $count = 10, $interval = 'week' ) {
        set_time_limit( 0 );

        if ( $start_timestamp === null ) {
            $start_timestamp = strtotime( 'today GMT' );
        }

        $args = array(
            'start_timestamp'   => $start_timestamp,
            'count'             => (int) $count,
            'interval'          => $interval,
        );

        foreach ( $this->ajax_sections as $section ) {
            $args['section']    = $section;
            $options            = $this->get_ajax_request_options( $args );
            $this->logger->info(
                __METHOD__ . ' - ' . $section,
                $options
            );
            $data = $this->$section( $options );
            LaterPay_Helper_Dashboard::refresh_cache_data( $options, $data );
        }
    }

    /**
     * Callback for WP cron to delete old post views from table.
     *
     * @wp-hook laterpay_delete_old_post_views
     *
     * @param string $modifier
     *
     * @return void
     */
    public function delete_old_post_views( $modifier = '3 month' ) {
        // delete old post views
        $post_views_model = new LaterPay_Model_Post_View();
        $post_views_model->delete_old_data( $modifier );
    }

    /**
     * Internal function to load the conversion data as diagram.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function converting_items( $options ) {
        $post_views_model   = new LaterPay_Model_Post_View();
        $converting_items   = $post_views_model->get_history( $options['query_args'], $options['interval'] );

        $history_model      = new LaterPay_Model_Payment_History();

        if ( $options['revenue_model'] !== 'all' ) {
            $options['query_args']['where']['revenue_model'] = $options['revenue_model'];
        }

        $selling_items      = $history_model->get_history( $options['query_args'], $options['interval'] );

        if ( $options['interval'] === 'day' ) {
            $converting_items = LaterPay_Helper_Dashboard::sort_items_by_hour( $converting_items );
            $converting_items = LaterPay_Helper_Dashboard::fill_empty_hours( $converting_items, $options['start_timestamp'] );

            $selling_items = LaterPay_Helper_Dashboard::sort_items_by_hour( $selling_items );
            $selling_items = LaterPay_Helper_Dashboard::fill_empty_hours( $selling_items, $options['start_timestamp'] );
        } else {
            $days = LaterPay_Helper_Dashboard::get_days_as_array( $options['start_timestamp'], $options['interval'] );

            $converting_items = LaterPay_Helper_Dashboard::sort_items_by_date( $converting_items );
            $converting_items = LaterPay_Helper_Dashboard::fill_empty_days( $converting_items, $days );

            $selling_items = LaterPay_Helper_Dashboard::sort_items_by_date( $selling_items );
            $selling_items = LaterPay_Helper_Dashboard::fill_empty_days( $selling_items, $days );
        }

        $diagram_data = array();
        foreach ( $converting_items as $date => $converting_item ) {
            $selling_item   = $selling_items[ $date ];
            $data           = $converting_item;
            if ( $converting_item->quantity == 0 ) {
                $data->quantity = 0;
            } else {
                // purchases on {date|hour} / views on {date|hour} * 100
                $data->quantity = $selling_item->quantity / $converting_item->quantity * 100;
            }
            $diagram_data[ $date ] = $data;
        }

        $converted_diagram_data = LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data(
            $diagram_data,
            $options['start_timestamp'],
            $options['interval']
        );

        $context = array(
            'options'                   => $options,
            'converting_items'          => $converting_items,
            'selling'                   => $selling_items,
            'diagram_data'              => $diagram_data,
            'converted_diagram_data'    => $converted_diagram_data,
        );

        $this->logger->info(
            __METHOD__,
            $context
        );

        return $converted_diagram_data;
    }

    /**
     * Internal function to load the sales data as diagram.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function selling_items( $options ) {
        $history_model  = new LaterPay_Model_Payment_History();

        if ( $options['revenue_model'] !== 'all' ) {
            $options['query_args']['where']['revenue_model'] = $options['revenue_model'];
        }

        $selling_items  = $history_model->get_history( $options['query_args'] );
        $data           = LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data(
            $selling_items,
            $options['start_timestamp'],
            $options['interval']
        );

        $this->logger->info(
            __METHOD__,
            array(
                'options'   => $options,
                'data'      => $data,
            )
        );

        return $data;
    }

    /**
     * Internal function to load the revenue data items as diagram.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function revenue_items( $options ) {
        $history_model  = new LaterPay_Model_Payment_History();

        if ( $options['revenue_model'] !== 'all' ) {
            $options['query_args']['where']['revenue_model'] = $options['revenue_model'];
        }

        $revenue_item   = $history_model->get_revenue_history( $options['query_args'] );
        $data           = LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data(
            $revenue_item,
            $options['start_timestamp'],
            $options['interval']
        );

        $this->logger->info(
            __METHOD__,
            array(
                'options'   => $options,
                'data'      => $data,
            )
        );

        return $data;
    }

    /**
     * Internal function to load the most / least converting items by given options.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function most_least_converting_items( $options ) {
        $post_args = array(
            'where' => $options['query_where'],
        );

        $history_args = $post_args;
        if ( $options['revenue_model'] !== 'all' ) {
            $history_args['where']['revenue_model'] = $options['revenue_model'];
        }

        $post_args['where']['has_access'] = 0;

        $post_views_model = new LaterPay_Model_Post_View();
        $history_model    = new LaterPay_Model_Payment_History();

        $post_views       = $post_views_model->get_posts_views_data( $post_args );
        $result           = array();
        $item_conversions = array();

        $most  = array();
        $least = array();

        if ( count( $post_views ) ) {
            foreach ( $post_views as $post_data ) {
                $history_args['where']['post_id'] = $post_data->post_id;
                $purchases = $history_model->get_total_items_sold( $history_args );
                $sparkline = $post_views_model->get_sparkline( $post_data->post_id, $options['start_timestamp'], $options['interval'], 0 );

                if ( ! $purchases ) {
                    $purchases = new stdClass();
                    $purchases->quantity = 0;
                }

                $post_data->amount    = ( $purchases->quantity / $post_data->quantity ) * 100;
                $post_data->sparkline = implode( ',', $sparkline );

                $result[ $post_data->post_id ] = $post_data;
                $item_conversions[ $post_data->post_id ] = $post_data->amount;
            }

            arsort( $item_conversions );
            $most_temp = array_slice( $item_conversions, 0, 10, true );
            foreach ( $most_temp as $key => $value ) {
                $most[] = $result[ $key ];
            }

            asort( $item_conversions );
            $least_temp = array_slice( $item_conversions, 0, 10, true );
            foreach ( $least_temp as $key => $value ) {
                $least[] = $result[ $key ];
            }
        }

        $data = array(
            'most'  => LaterPay_Helper_Dashboard::format_amount_value_most_least_data( $most, 1 ),
            'least' => LaterPay_Helper_Dashboard::format_amount_value_most_least_data( $least, 1 ),
            'unit'  => '%',
        );

        $this->logger->info(
            __METHOD__,
            array(
                'options'   => $options,
                'data'      => $data,
            )
        );

        return $data;
    }

    /**
     * Internal function to load the most / least selling items by given options.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function most_least_selling_items( $options ) {
        $history_model = new LaterPay_Model_Payment_History();

        if ( $options['revenue_model'] !== 'all' ) {
            $options['query_args']['where']['revenue_model'] = $options['revenue_model'];
        }

        $most = $history_model->get_best_selling_posts(
            $options['most_least_query'],
            $options['start_timestamp'],
            $options['interval']
        );
        $least = $history_model->get_least_selling_posts(
            $options['most_least_query'],
            $options['start_timestamp'],
            $options['interval']
        );

        $data = array(
            'most'  => LaterPay_Helper_Dashboard::format_amount_value_most_least_data( $most, 0 ),
            'least' => LaterPay_Helper_Dashboard::format_amount_value_most_least_data( $least, 0 ),
            'unit'  => '',
        );

        $this->logger->info(
            __METHOD__,
            array(
                'options'   => $options,
                'data'      => $data,
            )
        );

        return $data;
    }

    /**
     * Internal function to load the most / least revenue generating items by given options.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function most_least_revenue_items( $options ) {
        $history_model = new LaterPay_Model_Payment_History();

        if ( $options['revenue_model'] !== 'all' ) {
            $options['query_args']['where']['revenue_model'] = $options['revenue_model'];
        }

        $most = $history_model->get_most_revenue_generating_posts(
            $options['most_least_query'],
            $options['start_timestamp'],
            $options['interval']
        );
        $least = $history_model->get_least_revenue_generating_posts(
            $options['most_least_query'],
            $options['start_timestamp'],
            $options['interval']
        );

        $data = array(
            'most'  => LaterPay_Helper_Dashboard::format_amount_value_most_least_data( $most ),
            'least' => LaterPay_Helper_Dashboard::format_amount_value_most_least_data( $least ),
            'unit'  => get_option( 'laterpay_currency' ),
        );

        $this->logger->info(
            __METHOD__,
            array(
                'options'   => $options,
                'data'      => $data,
            )
        );

        return $data;
    }

    /**
     * Internal function to load KPIs by given options.
     *
     * @param array $options
     *
     * @return array $data
     */
    private function metrics( $options ) {
        $post_args = array(
            'where' => $options['query_where'],
        );

        $history_args = $post_args;
        if ( $options['revenue_model'] !== 'all' ) {
            $history_args['where']['revenue_model'] = $options['revenue_model'];
        }

        $post_args['where']['has_access'] = 0;

        $history_model       = new LaterPay_Model_Payment_History();
        $post_views_model    = new LaterPay_Model_Post_View();

        $new_customers       = $this->calculate_new_customers( $options );

        $total_items_sold    = $history_model->get_total_items_sold( $history_args );
        $total_items_sold    = $total_items_sold->quantity;

        $impressions         = $post_views_model->get_total_post_impression( $post_args );
        $impressions         = $impressions->quantity;

        $total_revenue_items = $history_model->get_total_revenue_items( $history_args );
        $total_revenue_items = $total_revenue_items->amount;

        $avg_purchase        = 0;
        if ( $total_items_sold > 0 ) {
            $avg_purchase    = $total_revenue_items / $total_items_sold;
        }

        $conversion = 0;
        if ( $impressions > 0 ) {
            $conversion      = ( $total_items_sold / $impressions ) * 100;
        }

        $avg_items_sold = 0;
        if ( $total_items_sold > 0 ) {
            if ( $options['interval'] === 'week' ) {
                $diff = 7;
            } else if ( $options['interval'] === '2-weeks' ) {
                $diff = 14;
            } else if ( $options['interval'] === 'month' ) {
                $diff = 30;
            } else {
                $diff = 24; // hour
            }
            $avg_items_sold = $total_items_sold / $diff;
        }

        $data = array(
            // column 1 - conversion metrics
            'impressions'           => LaterPay_Helper_View::format_number( $impressions, false ),
            'conversion'            => number_format_i18n( $conversion, 1 ),
            'new_customers'         => number_format_i18n( $new_customers, 0 ),

            // column 2 - sales metrics
            'avg_items_sold'        => number_format_i18n( $avg_items_sold, 1 ),
            'total_items_sold'      => LaterPay_Helper_View::format_number( $total_items_sold, false ),

            // column 3 - revenue metrics
            'avg_purchase'          => number_format_i18n( $avg_purchase, 2 ),
            'total_revenue'         => LaterPay_Helper_View::format_number( $total_revenue_items ),
        );

        $this->logger->info(
            __METHOD__,
            array(
                'options'   => $options,
                'data'      => $data,
            )
        );

        return $data;
    }

    /**
     * Internal function to add the query options to the options array.
     *
     * @param array $options
     *
     * @return array $options
     */
    private function get_query_options( $options ) {
        $end_timestamp = LaterPay_Helper_Dashboard::get_end_timestamp( $options['start_timestamp'], $options['interval'] );
        $mode          = LaterPay_Helper_View::get_plugin_mode();
        $where = array(
            'date' => array(
                array(
                    'before' => LaterPay_Helper_Date::get_date_query_before_end_of_day( $options['start_timestamp'] ),
                    'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $end_timestamp ),
                ),
            ),
            'mode' => $mode,
        );

        // add the query options to the options array
        $options['query_args'] = array(
            'order_by'  => LaterPay_Helper_Dashboard::get_order_by( $options['interval'] ),
            'group_by'  => LaterPay_Helper_Dashboard::get_group_by( $options['interval'] ),
            'where'     => $where,
        );

        $options['most_least_query'] = array(
            'where' => $where,
            'limit' => $options['count'],
        );

        $options['query_where'] = $where;

        return $options;
    }

    /**
     * Internal function to convert the $_POST request vars to an options array for the Ajax callbacks.
     *
     * @param array $post_args
     *
     * @return array $options
     */
    private function get_ajax_request_options( $post_args = array() ) {
        $interval = 'week';
        if ( isset( $post_args['interval'] ) ) {
            $interval = LaterPay_Helper_Dashboard::get_interval( $post_args['interval'] );
        }

        $count = 10;
        if ( isset( $post_args['count'] ) ) {
            $count = absint( $post_args['count'] );
        }

        $revenue_model = 'all';
        if ( isset( $post_args['revenue_model'] ) && in_array( $post_args['revenue_model'], array( 'ppu', 'sis' ) ) ) {
            $revenue_model = $post_args['revenue_model'];
        }

        $start_timestamp = strtotime( 'yesterday GMT' );
        if ( isset( $post_args['start_timestamp'] ) ) {
            $start_timestamp = $post_args['start_timestamp'];
        }

        $refresh = false;
        if ( isset( $post_args['refresh'] ) ) {
            $refresh = (bool) $post_args['refresh'];
        }

        $section = (string) $post_args['section'];

        // initial options
        $options = array(
            // request data
            'start_timestamp'   => $start_timestamp,
            'interval'          => $interval,
            'count'             => $count,
            'section'           => $section,
            'revenue_model'     => $revenue_model,
        );

        $cache_dir      = LaterPay_Helper_Dashboard::get_cache_dir( $start_timestamp );
        $cache_filename = LaterPay_Helper_Dashboard::get_cache_filename( $options );
        if ( $refresh || ! file_exists( $cache_dir . $cache_filename ) ) {
            // refresh the cache, if refresh == false and the file doesn't exist
            $refresh = true;
        }

        // cache data
        $options['refresh']           = $refresh;
        $options['cache_filename']    = $cache_filename;
        $options['cache_dir']         = $cache_dir;
        $options['cache_file_path']   = $cache_dir . $cache_filename;

        $options = $this->get_query_options( $options );

        return $options;
    }

    /**
     * Internal function to check the section parameter on Ajax requests.
     *
     * @return void
     */
    private function validate_ajax_section_callback() {
        if ( ! isset( $_POST['section'] ) ) {
            $error = array(
                'message'   => __( 'Error, missing section on request', 'laterpay' ),
                'step'      => 3,
            );
            wp_send_json_error( $error );
        }

        $section = sanitize_text_field( $_POST['section'] );
        if ( ! in_array( $section, $this->ajax_sections ) ) {
            $error = array(
                'message'   => sprintf( __( 'Section is not allowed <code>%s</code>', 'laterpay' ), $section ),
                'step'      => 4,
            );
            wp_send_json_error( $error );
        }

        if ( ! method_exists( $this, $_POST['section'] ) ) {
            $error = array(
                'message'   => sprintf( __( 'Invalid section <code>%s</code>', 'laterpay' ), $section ),
                'step'      => 4,
            );
            wp_send_json_error( $error );
        }
    }

    /**
     * Internal function to check the wpnonce on Ajax requests.
     *
     * @return void
     */
    private function validate_ajax_nonce() {
        if ( ! isset( $_POST['_wpnonce'] ) || empty( $_POST['_wpnonce'] ) ) {
            $error = array(
                'message'   => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' ),
                'step'      => 1,
            );
            wp_send_json_error( $error );
        }

        $nonce = sanitize_text_field( $_POST['_wpnonce'] );
        if ( ! wp_verify_nonce( $nonce, $this->ajax_nonce ) ) {
            $error = array(
                'message'   => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' ),
                'step'      => 2,
            );
            wp_send_json_error( $error );
        }
    }

    /**
     * Internal function to calculate new users.
     *
     * @param  array $options
     *
     * @return float $new_customers
     */
    private function calculate_new_customers( $options ) {
        $history_model = new LaterPay_Model_Payment_History();
        $end_timestamp = LaterPay_Helper_Dashboard::get_end_timestamp( $options['start_timestamp'], $options['interval'] );
        $mode          = LaterPay_Helper_View::get_plugin_mode();
        $new_customers = 0;

        $where = array(
            'date' => array(
                array(
                    'before' => LaterPay_Helper_Date::get_date_query_before_end_of_day( $end_timestamp ),
                ),
            ),
            'mode' => $mode,
        );

        $customer_args = array(
            'where' => $where,
        );

        // get all purchases data before reporting period
        $user_stats_old = $history_model->get_user_stats( $customer_args, true );

        // get the user stats in reporting period
        $customer_args['where']         = $options['query_where'];
        $user_stats_in_reporting_period = $history_model->get_user_stats( $customer_args );
        $total_customers_in_period      = count( $user_stats_in_reporting_period );

        // check if user purchased items before
        if ( $total_customers_in_period > 0 ) {
            foreach ( $user_stats_in_reporting_period as $stat ) {
                $is_new = true;
                foreach ( $user_stats_old as $key => $old_stat ) {
                    if ( $old_stat['ip'] === $stat->ip ) {
                        unset( $user_stats_old[ $key ] );
                        $is_new = false;
                        break;
                    }
                }
                if ( $is_new ) {
                    $new_customers += 1;
                }
            }

            $new_customers = ( $new_customers / $total_customers_in_period ) * 100 ;
        }

        return $new_customers;
    }
}
