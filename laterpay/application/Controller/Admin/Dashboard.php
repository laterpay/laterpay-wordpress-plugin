<?php

class LaterPay_Controller_Admin_Dashboard extends LaterPay_Controller_Abstract
{

    private $cache_file_exists;
    private $cache_file_is_broken;

    private $cache_dir;

    private $ajax_nonce = 'laterpay_dashboard';

    /**
     * {@inheritdoc}
     */
    protected function initialize() {
        $this->cache_dir  = $this->config->get( 'cache_dir' ) . 'cron/' . gmdate( 'Y/m/d' ) . '/';

    }

    /**
     * @see LaterPay_Controller_Abstract::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // load page-specific JS
        wp_register_script(
            'laterpay-flot',
            $this->config->js_url . 'vendor/jquery.flot.min.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-flot-time',
            $this->config->js_url . 'vendor/jquery.flot.time.js',
            array( 'jquery', 'laterpay-flot' ),
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
            array( 'jquery', 'laterpay-flot', 'laterpay-flot-time', 'laterpay-peity' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-flot' );
        wp_enqueue_script( 'laterpay-flot-time' );
        wp_enqueue_script( 'laterpay-peity' );
        wp_enqueue_script( 'laterpay-backend-dashboard' );

        // get the cache filename - by default 8 days back and a maximum of 10 items
        $cache_filename = $this->get_cache_filename( 8, 10 );

        // contains the state, if the cache file was generated for today
        $this->cache_file_exists      = file_exists( $this->cache_dir . $cache_filename );

        $this->cache_file_is_broken = false;
        $cache_data                 = array();

        // load the cache data
        if ( $this->cache_file_exists ) {
            $cache_data           = $this->load_cache_data( $this->cache_dir . $cache_filename );
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
                                    'dashboard' => wp_create_nonce( $this->ajax_nonce )
                                ),
                'i18n'      => array(
                                     'noData'    => __( 'No data available', 'laterpay' ),
                                ),
                'data'      =>  $cache_data
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

        $days = 8;
        if ( isset( $_POST[ 'days' ] ) ) {
            $days = absint( $_POST[ 'days' ] );
        }

        $count = 10;
        if ( isset( $_POST[ 'count' ] ) ) {
            $count = absint( $_POST[ 'count' ] );
        }

        $refresh = true;
        if ( isset( $_POST[ 'refresh' ] ) ) {
           $refresh = (bool) $_POST[ 'refresh' ];
        }

        $cache_filename = $this->get_cache_filename( $days, $count );

        if ( $refresh || ! file_exists( $this->cache_dir . $cache_filename ) ) {
            $this->refresh_dashboard_data( $days, $count );
            // refresh the cache, if refresh == false and the file doesn't exist
            $refresh = true;
        }

        // load the refreshed data
        $data = $this->load_cache_data( $this->cache_dir . $cache_filename );

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
              'data'    => $data
        ) );

    }

    /**
     * Callback for wp-ajax and wp-cron to refresh today's dashboard data.
     * The Cron job provides two params for {x} days back and {n} count of items to
     * register your own cron with custom params to cache data.
     *
     * @wp-hook laterpay_refresh_dashboard_data
     *
     * @param int $days
     * @param int $count
     *
     * @return void
     */
    public function refresh_dashboard_data( $days = 8, $count = 10 ) {

        // setting the time limit to 0 do prevent timeouts in our cronjob
        set_time_limit(0);

        $data = $this->get_dashboard_data( $days, $count );

        // create the cache dir, if it doesn't exist
        wp_mkdir_p( $this->cache_dir );

        $cache_filename = $this->get_cache_filename( $days, $count );

        $this->logger->info(
            __METHOD__,
            array(
                'days'              => $days,
                'count'             => $count,
                'data'              => $data,
                'cache_filename'    => $cache_filename,
                'cache_dir'         => $this->cache_dir,
            )
        );

        // write the data to the cache dir
        file_put_contents(
            $this->cache_dir . $cache_filename,
            serialize( $data )
        );
    }

    /**
     * Helper function to load the cached data by a given file path.0
     *
     * @param string $file_path
     *
     * @return array $cache_data array with cached data or empty array on failure
     */
    protected function load_cache_data( $file_path ) {

        if ( !file_exists( $file_path ) ) {
            $this->logger->error(
                __METHOD__ . ' - cache-file not found',
                array(
                    'file_path' => $file_path,
                )
            );
            return array();
        }

        $cache_data = file_get_contents( $file_path );
        $cache_data = maybe_unserialize( $cache_data );

        if ( ! is_array( $cache_data ) ) {
            $this->logger->error(
                __METHOD__ . ' - invalid cache data',
                array(
                    'file_path' => $file_path,
                    'cache_data'=> $cache_data
                )
            );
            return array();

        }

        $this->logger->info(
            __METHOD__, array(
                'file_path' => $file_path,
                'cache_data'=> $cache_data,
            )
        );

        return $cache_data;
    }

    /**
     * Helper function to load the complete dashboard data.
     *
     * @param int $days     how many days we want to go back - default: 8
     * @param int $count    number of items, the top {n} items - default: 10
     *
     * @return array $data
     */
    protected function get_dashboard_data( $days = 8, $count = 10 ) {
        $post_views_model       = new LaterPay_Model_Post_Views();
        $history_model          = new LaterPay_Model_Payments_History();

        $where = array(
            'date' => array(
                array(
                    'before'=> LaterPay_Helper_Date::get_date_query_before_end_of_day( 0 ), // end of today
                    'after' => LaterPay_Helper_Date::get_date_query_after_start_of_day( $days )
                )
            )
        );

        $args = array(
            'where' => $where,
        );

        // getting the user stats for the given params
        $user_stats = $history_model->get_user_stats( $args );
        $total_customers    = count( $user_stats );
        $new_customers      = 0;
        $returning_customers= 0;
        foreach ( $user_stats as $stat ) {
            if ( (int) $stat->quantity === 1 ) {
                $new_customers += 1;
            } else {
                $returning_customers += 1;
            }
        }

        if ( $total_customers > 0 ) {
            $new_customers      = round( $new_customers * 100 / $total_customers );
            $returning_customers= round( $returning_customers * 100 / $total_customers );
        }

        $total_items_sold       = $history_model->get_total_items_sold( $args );
        $total_items_sold       = number_format_i18n( $total_items_sold->quantity );

        $total_revenue_items    = $history_model->get_total_revenue_items( $args );
        $total_revenue_items    = number_format_i18n( $total_revenue_items->amount, 2 );

        $impressions            = $post_views_model->get_total_post_impression( $args );
        $impressions            = number_format_i18n( $impressions->quantity );

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
            $avg_items_sold = number_format_i18n($total_items_sold / $days, 0);
        }

        $args = array(
            'order_by'  => 'day',
            'group_by'  => 'day',
            'where'     => $where
        );
        $converting_items_by_day = $post_views_model->get_history( $args );
        $converting_items_by_day = LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data( $converting_items_by_day );

        $selling_items_by_day = $history_model->get_history( $args );
        $selling_items_by_day = LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data( $selling_items_by_day );

        $revenue_items_by_day = $history_model->get_revenue_history( $args );
        $revenue_items_by_day = LaterPay_Helper_Dashboard::convert_history_result_to_diagram_data( $revenue_items_by_day );

        // search args for items with sparkline
        $search_args = array(
            'where' => $where,
            'limit' => (int) $count,
        );

        $data = array(
            'converting_items_by_day'   => $converting_items_by_day,
            'best_converting_items'     => $post_views_model->get_most_viewed_posts( $search_args, $days ),
            'least_converting_items'    => $post_views_model->get_least_viewed_posts( $search_args, $days ),

            'selling_items_by_day'      => $selling_items_by_day,
            'most_selling_items'        => $history_model->get_best_selling_posts( $search_args, $days ),
            'least_selling_items'       => $history_model->get_least_selling_posts( $search_args, $days ),

            'revenue_items_by_day'      => $revenue_items_by_day,
            'most_revenue_items'        => $history_model->get_most_revenue_generating_posts( $search_args, $days ),
            'least_revenue_items'       => $history_model->get_least_revenue_generating_posts( $search_args, $days ),

            'impressions'               => $impressions,
            'conversion'                => $conversion,
            'new_customers'             => $new_customers,
            'returning_customers'       => $returning_customers,

            'avg_items_sold'            => $avg_items_sold,
            'total_items_sold'          => $total_items_sold,

            'avg_purchase'              => $avg_purchase,
            'total_revenue'             => $total_revenue_items,
        );

        return $data;
    }

    /**
     * Return the cache file name for the given days and item count.
     *
     * @param int $days
     * @param int $count
     *
     * @return string $cache_filename
     */
    protected function get_cache_filename( $days, $count ) {
        return $days . '-' . $count . '.cache';
    }

}
