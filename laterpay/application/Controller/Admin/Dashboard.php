<?php

class LaterPay_Controller_Admin_Dashboard extends LaterPay_Controller_Abstract
{

    private $cache_file_exists;
    private $cache_file_is_broken;

    private $ajax_nonce = 'laterpay_dashboard';

    /**
     * @see LaterPay_Controller_Abstract::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_register_script(
            'laterpay-flot',
            $this->config->js_url . 'vendor/lp_jquery.flot.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-peity',
            $this->config->get( 'js_url' ) . 'vendor/jquery.peity.min.js',
            array( 'jquery' ),
            $this->config->get( 'version' ),
            true
        );
        wp_register_script(
            'laterpay-backend-dashboard',
            $this->config->js_url . 'laterpay-backend-dashboard.js',
            array( 'jquery', 'laterpay-flot', 'laterpay-peity' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-flot' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-backend-dashboard' );

        // get the cache filename - by default 8 days back and a maximum of 10 items
        $cache_filename = $this->get_cache_filename( 'week', 10 );
        $cache_dir      = $this->get_cache_dir();

        // contains the state, if the cache file was generated for today
        $this->cache_file_exists    = file_exists( $cache_dir . $cache_filename );
        $this->cache_file_is_broken = false;
        $cache_data                 = array();

        // load the cache data
        if ( $this->cache_file_exists ) {
            $cache_data           = $this->load_cache_data( $cache_dir . $cache_filename );
            // the cached data will be empty, if it is not serializable
            $this->cache_file_is_broken = empty( $cache_data );
        }

        $this->logger->info( __METHOD__ );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-dashboard',
            'lpVars',
            array(
                'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
                'nonces'    => array(
                                    'dashboard' => wp_create_nonce( $this->ajax_nonce ),
                                ),
                'i18n'      => array(
                                     'noData'   => __( 'No data available', 'laterpay' ),
                                ),
                'data'      =>  $cache_data,
            )
        );
    }

    /**
     * @see LaterPay_Controller_Abstract::render_page
     */
    public function render_page() {
        $this->load_assets();

        $view_args = array(
            'plugin_is_in_live_mode'    => $this->config->get( 'is_in_live_mode' ),
            'top_nav'                   => $this->get_menu(),
            'admin_menu'                => LaterPay_Helper_View::get_admin_menu(),
            'currency'                  => get_option( 'laterpay_currency' ),

            // in wp-config.php the user can disable the WP-Cron completely OR replace it with real server crons.
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
       // check, if the _wpnonce is set
       if( ! isset( $_POST[ '_wpnonce' ] ) || empty( $_POST[ '_wpnonce' ] ) ) {
            wp_send_json( array(
                'success' => false,
                'message' => __( "You don't have sufficient user capabilities to do this.", 'laterpay' ),
                'step'    => 1,
            ) );
       }

        // check. if the _wpnonce is valid
        $nonce = $_POST[ '_wpnonce' ];
        if( ! wp_verify_nonce( $nonce, $this->ajax_nonce ) ) {
            wp_send_json( array(
              'success' => false,
              'message' => __( "You don't have sufficient user capabilities to do this.", 'laterpay' ),
              'step'    => 2,
            ) );
        }

        $interval = 'weekly';
        if ( isset( $_POST[ 'interval' ] ) ) {
            $interval = LaterPay_Helper_Dashboard::get_interval( $_POST[ 'interval' ] );
        }

        $count = 10;
        if ( isset( $_POST[ 'count' ] ) ) {
            $count = absint( $_POST[ 'count' ] );
        }

        $start_day = strtotime( 'today GMT' );
        if( isset( $_POST[ 'start_day' ] ) ){
            $start_day = $_POST[ 'start_day' ];
        }

        $refresh = true;
        if ( isset( $_POST[ 'refresh' ] ) ) {
           $refresh = (bool) $_POST[ 'refresh' ];
        }

        $cache_dir      = $this->get_cache_dir( $start_day );
        $cache_filename = $this->get_cache_filename( $interval, $count );

        if ( $refresh || ! file_exists( $cache_dir . $cache_filename ) ) {
            $this->refresh_dashboard_data( $start_day, $count, $interval );
            // refresh the cache, if refresh == false and the file doesn't exist
            $refresh = true;
        }

        // load the refreshed data
        $data = $this->load_cache_data( $cache_dir . $cache_filename );

        // check, if the cached data is valid
        if ( empty( $data ) ) {
            wp_send_json( array(
                'success' => false,
                'message' => __( 'Error on cache reload', 'laterpay' ),
                'step'    => 3,
            ) );
        }

        $msg = __( 'Cache successfully reloaded', 'laterpay' );
        if ( ! $refresh ) {
            $msg = __( 'Data successfully loaded', 'laterpay' );
        }

        // return the cached data and success message
        wp_send_json( array(
              'success' => true,
              'message' => $msg,
              'data'    => $data,
        ) );

    }

    /**
     * Callback for wp-ajax and wp-cron to refresh today's dashboard data.
     * The Cron job provides two params for {x} days back and {n} count of items to
     * register your own cron with custom params to cache data.
     *
     * @wp-hook laterpay_refresh_dashboard_data
     *
     * @param int $start_timestamp
     * @param int $count
     * @param string $interval
     *
     * @return void
     */
    public function refresh_dashboard_data( $start_timestamp = null, $count = 10, $interval = 'week' ) {
        if ( $start_timestamp === null ) {
            $start_timestamp = strtotime( 'today GMT' );
        }

        $cache_dir  = $this->get_cache_dir( $start_timestamp );

        $interval = LaterPay_Helper_Dashboard::get_interval( $interval );

        // set the time limit to 0 to prevent timeouts in our cronjob
        set_time_limit( 0 );

        $data = $this->get_dashboard_data( $start_timestamp, $count, $interval );

        // create the cache dir, if it doesn't exist
        wp_mkdir_p( $cache_dir );

        $cache_filename = $this->get_cache_filename( $interval, $count );

        $this->logger->info(
            __METHOD__,
            array(
                'interval'              => $interval,
                'timestamp'             => $start_timestamp,
                'formatted_start_day'   => date( 'Y-m-d', $start_timestamp ),
                'count'                 => $count,
                'data'                  => $data,
                'cache_filename'        => $cache_filename,
                'cache_dir'             => $cache_dir,
            )
        );

        // write the data to the cache dir
        file_put_contents(
            $cache_dir . $cache_filename,
            serialize( $data )
        );
    }

    /**
     * Helper function to load the cached data by a given file path.
     *
     * @param string $file_path
     *
     * @return array $cache_data array with cached data or empty array on failure
     */
    protected function load_cache_data( $file_path ) {
        if ( !file_exists( $file_path ) ) {
            $this->logger->error(
                __METHOD__ . ' - cache-file not found',
                array( 'file_path' => $file_path )
            );

            return array();
        }

        $cache_data = file_get_contents( $file_path );
        $cache_data = maybe_unserialize( $cache_data );

        if ( ! is_array( $cache_data ) ) {
            $this->logger->error(
                __METHOD__ . ' - invalid cache data',
                array(
                    'file_path'     => $file_path,
                    'cache_data'    => $cache_data,
                )
            );

            return array();
        }

        $this->logger->info(
            __METHOD__,
            array(
                'file_path'     => $file_path,
                'cache_data'    => $cache_data,
            )
        );

        return $cache_data;
    }

    /**
     * Helper function to load the complete dashboard data.
     *
     * @param int $start_timestamp  timestamp when we start loading data from - by default "today"
     * @param int $count            number of items, the top {n} items - default: 10
     * @param string $interval      how many days from $start_timestamp we want to go back
     *
     * @return array $data
     */
    protected function get_dashboard_data( $start_timestamp = null, $count = 10, $interval = 'week' ) {

        $interval = LaterPay_Helper_Dashboard::get_interval( $interval );

        $post_views_model   = new LaterPay_Model_Post_Views();
        $history_model      = new LaterPay_Model_Payments_History();

        if ( $start_timestamp === null ) {
            $start_timestamp = strtotime('yesterday GMT');
        }
        $end_timestamp = LaterPay_Helper_Dashboard::get_end_timestamp( $start_timestamp, $interval );

        $where = array(
            'date' => array(
                array(
                    'before'=> LaterPay_Helper_Date::get_date_query_before_end_of_day( $start_timestamp ),
                    'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $end_timestamp ),
                ),
            ),
        );

        // search args for metrics
        $args = array(
            'where' => $where,
        );

        // get the user stats for the given params
        $user_stats = $history_model->get_user_stats( $args );

        // get the user stats for the given params
        $user_stats             = $history_model->get_user_stats( $args );
        $total_customers        = count( $user_stats );
        $new_customers          = 0;
        $returning_customers    = 0;
        foreach ( $user_stats as $stat ) {
            if ( (int) $stat->quantity === 1 ) {
                $new_customers += 1;
            } else {
                $returning_customers += 1;
            }
        }

        if ( $total_customers > 0 ) {
            $new_customers          = round( $new_customers * 100 / $total_customers );
            $returning_customers    = round( $returning_customers * 100 / $total_customers );
        }

        $total_items_sold           = $history_model->get_total_items_sold( $args );
        $total_items_sold           = number_format_i18n( $total_items_sold->quantity );

        $total_revenue_items        = $history_model->get_total_revenue_items( $args );
        $total_revenue_items        = number_format_i18n( $total_revenue_items->amount, 2 );

        $impressions                = $post_views_model->get_total_post_impression( $args );
        $impressions                = number_format_i18n( $impressions->quantity );

        $avg_purchase = 0;
        if ( $total_revenue_items > 0 ) {
            $avg_purchase = number_format_i18n($total_items_sold / $total_revenue_items, 1);
        }

        $conversion = 0;
        if ( $impressions > 0 ) {
            $conversion = number_format_i18n($total_items_sold / $impressions, 1);
        }

        $avg_items_sold = 0;
        if ( $total_items_sold > 0 ) {
            if( $interval === 'week' ){
                $diff = 7;
            } else if( $interval === '2-weeks' ) {
                $diff = 14;
            } else if( $interval === 'month' ){
                $diff = 30;
            } else {
                // hour
                $diff = 24;
            }
            $avg_items_sold = number_format_i18n($total_items_sold / $diff, 0);
        }

        // search args for history items
        $args = array(
            'order_by'  => LaterPay_Helper_Dashboard::get_order_and_group_by( $interval ),
            'group_by'  => LaterPay_Helper_Dashboard::get_order_and_group_by( $interval ),
            'where'     => $where,
        );
        $converting_items   = $post_views_model->get_history( $args );
        $selling_items      = $history_model->get_history( $args );
        $revenue_item       = $history_model->get_revenue_history( $args );

        // search args for items with sparkline
        $search_args = array(
            'where' => $where,
            'limit' => (int) $count,
        );

        $data = array(
            // diagrams
            'converting_items_by_day'   => LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data( $converting_items, $start_timestamp, $interval ),
            'selling_items_by_day'      => LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data( $selling_items, $start_timestamp, $interval ),
            'revenue_items_by_day'      => LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data( $revenue_item, $start_timestamp, $interval ),

            // row 1 - conversion
            // metrics
            'impressions'               => $impressions,
            'conversion'                => $conversion,
            'new_customers'             => $new_customers,
            'returning_customers'       => $returning_customers,
            // most / least viewed posts
            'best_converting_items'     => $post_views_model->get_most_viewed_posts( $search_args, $start_timestamp, $interval ),
            'least_converting_items'    => $post_views_model->get_least_viewed_posts( $search_args, $start_timestamp, $interval ),

            // row 2 - sales
            // metrics
            'avg_items_sold'            => $avg_items_sold,
            'total_items_sold'          => $total_items_sold,
            // beast / least selling posts
            'most_selling_items'        => $history_model->get_best_selling_posts( $search_args, $start_timestamp, $interval ),
            'least_selling_items'       => $history_model->get_least_selling_posts( $search_args, $start_timestamp, $interval ),

            // row 3 - revenue
            // metrics
            'avg_purchase'              => $avg_purchase,
            'total_revenue'             => $total_revenue_items,
            // most / least revenue generating posts
            'most_revenue_items'        => $history_model->get_most_revenue_generating_posts( $search_args, $start_timestamp, $interval ),
            'least_revenue_items'       => $history_model->get_least_revenue_generating_posts( $search_args, $start_timestamp, $interval ),
        );

        return $data;
    }

    /**
     * Return the cache file name for the given days and item count.
     *
     * @param string $interval
     * @param int $count
     *
     * @return string $cache_filename
     */
    protected function get_cache_filename( $interval, $count ) {
        $interval = LaterPay_Helper_Dashboard::get_interval( $interval );
        $cache_filename =  $interval . '-' . $count . '.cache';
        $this->logger->info(
            __METHOD__,
            array(
                'interval'          => $interval,
                'count'             => $count,
                'cache_filename'    => $cache_filename,
            )
        );

        return $cache_filename;
    }

    /**
     * Return the cache dir by a given strottime() timestamp.
     *
     * @param int|null $timestamp default null will be set to strototime( 'today GMT' );
     *
     * @return  string $cache_dir
     */
    protected function get_cache_dir( $timestamp = null ) {
        if ( $timestamp === null ) {
           $timestamp = strtotime( 'today GMT' );
        }
        $cache_dir = $this->config->get( 'cache_dir' ) . 'cron/' . gmdate( 'Y/m/d', $timestamp ) . '/';
        $this->logger->info(
            __METHOD__,
            array(
                'timestamp' => $timestamp,
                'cache_dir' => $cache_dir,
            )
        );

        return $cache_dir;
    }

}
