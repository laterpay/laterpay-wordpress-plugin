<?php

class LaterPay_Controller_Statistics extends LaterPay_Controller_Abstract
{

    /**
     * Check requirements for logging and rendering the post statistic pane via Ajax callback.
     *
     * @return bool
     */
    protected function check_requirements( $post = null ) {
        if ( empty($post) ) {
            // check, if we're on a singular page
            if ( ! is_singular() ) {
                $this->logger->warning(
                    __METHOD__. ' - !is_singular',
                    array(
                        'post' => $post
                    )
                );
                return false;
            }

            // check, if we have a post
            $post = get_post();
            if ( $post === null ) {
                return false;
            }
        }
        // check, if the current post_type is an allowed post_type
        $allowed_post_types = $this->config->get( 'content.enabled_post_types' );
        if ( ! in_array( $post->post_type, $allowed_post_types ) ) {
            $this->logger->warning(
                __METHOD__. ' - post is not purchasable',
                array(
                    'post' => $post,
                    'allowed_post_types' => $allowed_post_types
                )
            );
            return false;
        }

        // check, if the current post is purchasable
        if ( ! LaterPay_Helper_Pricing::is_purchasable( $post ) ){
            $this->logger->warning(
                __METHOD__. ' - post is not purchasable',
                array(
                    'post' => $post
                )
            );
            return false;
        }

        // check, if logging is enabled
        if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
            $this->logger->warning( __METHOD__. ' - access logging is not enabled' );
            return false;
        }

        return true;
    }

    /**
     * Track unique visitors.
     *
     * @wp-hook template_redirect
     *
     * @return void
     */
    public function add_unique_visitors_tracking() {
        if ( ! $this->check_requirements() ) {
            return;
        }

        $post_id = get_the_ID();

        $this->logger->info(
            __METHOD__,
            array(
                'post_id' => $post_id
            )
        );

        LaterPay_Helper_Statistics::track( $post_id );
    }
    
    /**
     * Ajax method to track unique visitors when caching compatible mode is enabled.
     *
     * @wp-hook wp_ajax_laterpay_post_load_track_views, wp_ajax_nopriv_laterpay_post_load_track_views
     *
     * @return void
     */
    public function ajax_track_views() {
        if ( ! isset( $_POST[ 'action' ] ) || $_POST[ 'action' ] !== 'laterpay_post_track_views' ) {
            exit;
        }

        if ( ! isset( $_POST[ 'nonce' ] ) || ! wp_verify_nonce( $_POST[ 'nonce' ], $_POST[ 'action' ] ) ) {
            exit;
        }

        if ( ! isset( $_POST[ 'post_id' ] ) ) {
            return;
        }

        $post_id    = absint( $_POST[ 'post_id' ] );
        $post       = get_post( $post_id );
        
        if ( $this->check_requirements($post) ) {
            LaterPay_Helper_Statistics::track( $post_id );
        }
        
        exit;
    }

    /**
     * Callback to add the statistics placeholder to the footer.
     *
     * @wp-hook wp_footer
     *
     * @return void
     */
    public function modify_footer() {
        if ( ! $this->check_requirements() ) {
            return;
        }

        // don't add the statistics pane placeholder to the footer, if the user is not logged in
        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', get_the_ID() ) ) {

            $this->logger->warning(
                __METHOD__ . ' - user cannot read post statistics',
                array(
                    'post_id'       => get_the_ID(),
                    'current_user'  => wp_get_current_user()
                )
            );

            return;
        }

        echo '<div id="lp_js_post-statistics-placeholder"></div>';
    }

    /**
     * Ajax callback to toggle the preview mode of the post.
     *
     * @wp-hook wp_ajax_laterpay_post_statistic_toggle_preview
     *
     * @return void
     */
    public function ajax_toggle_preview() {
        $error = array(
            'success' => false,
            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
        );

        // check the admin referer
        if ( ! check_admin_referer( 'laterpay_form' ) ) {
            $error[ 'code' ] = 1;
            wp_send_json( $error );
        }

        if ( ! isset( $_POST[ 'preview_post' ] ) ) {
            $error[ 'code' ] = 2;
            wp_send_json( $error );
        }

        // check if we have a valid user
        $current_user = wp_get_current_user();
        if ( ! is_a( $current_user, 'WP_User' ) ) {
            $error[ 'code' ] = 3;
            wp_send_json( $error );
        }

        // check for required capabilities to perform action
        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false ) ) {
            $error[ 'code' ] = 4;
            wp_send_json( $error );
        }

        $result = update_user_meta(
            $current_user->ID,
            'laterpay_preview_post_as_visitor',
            $_POST[ 'preview_post' ]
        );

        if ( ! $result ) {
            $error[ 'code' ] = 5;
            wp_send_json( $error );
        }

        wp_send_json(
            array(
                'success' => true,
                'message' => __( 'Updated.', 'laterpay' )
            )
        );
    }

    /**
     * Ajax callback to toggle the visibility of the statistics pane.
     *
     * @wp-hook wp_ajax_laterpay_post_statistic_visibility
     *
     * @return void
     */
    public function ajax_toggle_visibility() {
        $error = array(
            'success' => false,
            'message' => __("You don't have sufficient user capabilities to do this.", 'laterpay' )
        );

        // check the admin referer
        if ( ! check_admin_referer( 'laterpay_form' ) ) {
            wp_send_json( $error );
        }

        if ( ! isset( $_POST[ 'hide_statistics_pane' ] ) ) {
            wp_send_json( $error );
        }

        // check if we have a valid user
        $current_user = wp_get_current_user();
        if ( ! is_a( $current_user, 'WP_User' ) ) {
            wp_send_json( $error );
        }

        // check for required capabilities to perform action
        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false ) ) {
            wp_send_json( $error );
        }

        $result = update_user_meta(
            $current_user->ID,
            'laterpay_hide_statistics_pane',
            absint( $_POST['hide_statistics_pane'] )
        );

        if ( ! $result ) {
            wp_send_json( $error );
        }

        wp_send_json(
            array(
                'success' => true,
                'message' => __( 'Updated.', 'laterpay' )
            )
        );
    }

    /**
     * Ajax callback to render the statistics pane.
     *
     * @wp-hook wp_ajax_laterpay_post_statistic_render
     *
     * @return void
     */
    public function ajax_render_tab() {

        if ( ! isset( $_GET[ 'post_id' ] ) ) {
            exit;
        }

        if ( ! isset( $_GET[ 'action' ] ) || $_GET[ 'action' ] !== 'laterpay_post_statistic_render' ) {
            exit;
        }

        if ( ! isset( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ], $_GET[ 'action' ] ) ) {
            exit;
        }

        $post_id    = absint( $_GET[ 'post_id' ] );
        $post       = get_post( $post_id );
        if ( $post === null ) {
            exit;
        }

        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id ) ) {
            exit;
        }

        // assign variables
        $view_args = array(
            'preview_post_as_visitor'   => LaterPay_Helper_User::preview_post_as_visitor( $post ),
            'hide_statistics_pane'      => LaterPay_Helper_User::statistics_pane_is_hidden(),
            'currency'                  => get_option( 'laterpay_currency' ),
            'post_id'                   => $post_id
        );
        $this->assign( 'laterpay', $view_args );

        $this->initialize_post_statistics( $post );
        wp_send_json( $this->get_text_view( 'frontend/partials/post/post_statistics' ) );
        exit;
    }

    /**
     * Generate performance data statistics for post.
     *
     * @param WP_Post $post
     *
     * @return void
     */
    protected function initialize_post_statistics( WP_Post $post ) {
        // get currency
        $currency = get_option( 'laterpay_currency' );

        // get historical performance data for post
        $payments_history_model = new LaterPay_Model_Payments_History();
        $post_views_model       = new LaterPay_Model_Post_Views();
        $currency_model         = new LaterPay_Model_Currency();

        // get total revenue and total sales
        $total = array();
        $history_total = (array) $payments_history_model->get_total_history_by_post_id( $post->ID );
        foreach ( $history_total as $item ) {
            $currency_short_name = $currency_model->get_short_name_by_currency_id( $item->currency_id );
            $total[$currency_short_name]['sum']      = round( $item->sum, 2 );
            $total[$currency_short_name]['quantity'] = $item->quantity;
        }

        // get revenue
        $last30DaysRevenue = array();
        $history_last30DaysRevenue = (array) $payments_history_model->get_last_30_days_history_by_post_id( $post->ID );
        foreach ( $history_last30DaysRevenue as $item ) {
            $currency_short_name = $currency_model->get_short_name_by_currency_id( $item->currency_id );
            $last30DaysRevenue[$currency_short_name][$item->date] = array(
                'sum'       => round( $item->sum, 2 ),
                'quantity'  => $item->quantity,
            );
        }

        $todayRevenue = array();
        $history_todayRevenue = (array) $payments_history_model->get_todays_history_by_post_id( $post->ID );
        foreach ( $history_todayRevenue as $item ) {
            $currency_short_name = $currency_model->get_short_name_by_currency_id( $item->currency_id );
            $todayRevenue[$currency_short_name]['sum']       = round( $item->sum, 2 );
            $todayRevenue[$currency_short_name]['quantity']  = $item->quantity;
        }

        // get visitors
        $last30DaysVisitors = array();
        $history_last30DaysVisitors = (array) $post_views_model->get_last_30_days_history( $post->ID );
        foreach ( $history_last30DaysVisitors as $item ) {
            $last30DaysVisitors[$item->date] = array(
                'quantity' => $item->quantity,
            );
        }

        $todayVisitors = (array) $post_views_model->get_todays_history( $post->ID );
        $todayVisitors = $todayVisitors[0]->quantity;

        // get buyers (= conversion rate)
        $last30DaysBuyers = array();
        if ( isset( $last30DaysRevenue[$currency] ) ) {
            $revenues = $last30DaysRevenue[$currency];
        } else {
            $revenues = array();
        }
        foreach ( $revenues as $date => $item ) {
            $percentage = 0;
            if ( isset( $last30DaysVisitors[$date] ) && ! empty( $last30DaysVisitors[$date]['quantity'] ) ) {
                $percentage = round( 100 * $item['quantity'] / $last30DaysVisitors[$date]['quantity'] );
            }
            $last30DaysBuyers[$date] = array( 'percentage' => $percentage );
        }

        $todayBuyers = 0;
        if ( ! empty( $todayVisitors ) && isset( $todayRevenue[$currency] ) ) {
            // percentage of buyers (sales divided by visitors)
            $todayBuyers = round( 100 * $todayRevenue[$currency]['quantity'] / $todayVisitors );
        }

        // assign variables
        $statistic_args = array(
            'total'             => $total,
            'last30DaysRevenue' => $last30DaysRevenue,
            'todayRevenue'      => $todayRevenue,
            'last30DaysBuyers'  => $last30DaysBuyers,
            'todayBuyers'       => $todayBuyers,
            'last30DaysVisitors'=> $last30DaysVisitors,
            'todayVisitors'     => $todayVisitors,
        );

        $this->assign( 'statistic', $statistic_args );
    }

}
