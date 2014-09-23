<?php

class LaterPay_Controller_Statistics extends LaterPay_Controller_Abstract
{

    /**
     * Check requirements for logging and rendering the post statistic pane via Ajax callback.
     *
     * @return bool
     */
    protected function check_requirements() {
        // check, if we're on a singular page
        if ( ! is_singular() ) {
            return false;
        }

        // check, if we have a post
        $post = get_post();
        if ( $post === null ) {
            return false;
        }

        // check, if the current post_type is an allowed post_type
        if ( ! in_array( $post->post_type, $this->config->get( 'content.enabled_post_types' ) ) ) {
            return false;
        }

        // check, if the current post is purchasable
        if ( ! LaterPay_Helper_Pricing::is_purchasable() ){
            return false;
        }

        // check, if logging is enabled
        if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
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

        LaterPay_Helper_Statistics::track( $post_id );
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

        // get total revenue and total sales
        $total = array();
        $history_total = (array) $payments_history_model->get_total_history_by_post_id( $post->ID );
        foreach ( $history_total as $item ) {
            $total[$item->currency]['sum']      = round( $item->sum, 2 );
            $total[$item->currency]['quantity'] = $item->quantity;
        }

        // get revenue
        $last30DaysRevenue = array();
        $history_last30DaysRevenue = (array) $payments_history_model->get_last_30_days_history_by_post_id( $post->ID );
        foreach ( $history_last30DaysRevenue as $item ) {
            $last30DaysRevenue[$item->currency][$item->date] = array(
                'sum'       => round( $item->sum, 2 ),
                'quantity'  => $item->quantity,
            );
        }

        $todayRevenue = array();
        $history_todayRevenue = (array) $payments_history_model->get_todays_history_by_post_id( $post->ID );
        foreach ( $history_todayRevenue as $item ) {
            $todayRevenue[$item->currency]['sum']       = round( $item->sum, 2 );
            $todayRevenue[$item->currency]['quantity']  = $item->quantity;
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
