<?php

/**
 * LaterPay statistics controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Statistic extends LaterPay_Controller_Abstract
{

    /**
     * Check requirements for logging and rendering the post statistic pane via Ajax callback.
     *
     * @param WP_Post $post
     *
     * @return bool
     */
    protected function check_requirements( $post = null ) {
        // check, if logging is enabled
        if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
            $this->logger->warning( __METHOD__. ' - access logging is not enabled' );

            return false;
        }

        if ( empty( $post ) ) {
            // check, if we're on a singular page
            if ( ! is_singular() ) {
                $this->logger->warning(
                    __METHOD__. ' - !is_singular',
                    array(
                        'post' => $post,
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
                    'allowed_post_types' => $allowed_post_types,
                )
            );

            return false;
        }

        // check, if the current post is purchasable
        if ( ! LaterPay_Helper_Pricing::is_purchasable( $post->ID ) ) {
            $this->logger->warning(
                __METHOD__. ' - post is not purchasable',
                array(
                    'post' => $post,
                )
            );

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
                'post_id' => $post_id,
            )
        );

        LaterPay_Helper_Statistic::track( $post_id );
    }

    /**
     * Ajax method to track unique visitors when caching compatible mode is enabled.
     *
     * @wp-hook wp_ajax_laterpay_post_load_track_views, wp_ajax_nopriv_laterpay_post_load_track_views
     *
     * @return void
     */
    public function ajax_track_views() {
        $statistic_form = new LaterPay_Form_Statistic();

        if ( $statistic_form->is_valid( $_POST ) ) {
            $post_id    = $statistic_form->get_field_value( 'post_id' );
            $post       = get_post( $post_id );

            if ( $this->check_requirements( $post ) ) {
                LaterPay_Helper_Statistic::track( $post_id );
            }
        }

        wp_die();
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
                    'current_user'  => wp_get_current_user(),
                )
            );

            return;
        }

        echo '<div id="lp_js_postStatisticsPlaceholder"></div>';
    }

    /**
     * Ajax callback to toggle the preview mode of the post.
     *
     * @wp-hook wp_ajax_laterpay_post_statistic_toggle_preview
     *
     * @return void
     */
    public function ajax_toggle_preview() {
        $statistics_preview_form = new LaterPay_Form_StatisticPreview( $_POST );
        $preview_post = $statistics_preview_form->get_field_value( 'preview_post' );

        $error = array(
            'success' => false,
            'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
        );

        // check the admin referer
        if ( ! check_admin_referer( 'laterpay_form' ) ) {
            $error['code'] = 1;
            wp_send_json( $error );
        }

        if ( $preview_post === null ) {
            $error['code'] = 2;
            wp_send_json( $error );
        }

        // check, if we have a valid user
        $current_user = wp_get_current_user();
        if ( ! is_a( $current_user, 'WP_User' ) ) {
            $error['code'] = 3;
            wp_send_json( $error );
        }

        // check for required capabilities to perform action
        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false ) ) {
            $error['code'] = 4;
            wp_send_json( $error );
        }

        $result = update_user_meta(
            $current_user->ID,
            'laterpay_preview_post_as_visitor',
            $preview_post
        );

        if ( ! $result ) {
            $error['code'] = 5;
            wp_send_json( $error );
        }

        wp_send_json(
            array(
                'success' => true,
                'message' => __( 'Updated.', 'laterpay' ),
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
        $statistics_visibility_form = new LaterPay_Form_StatisticVisibility();

        $current_user = wp_get_current_user();
        $error = array(
            'success' => false,
            'message' => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' ),
        );

        // check the admin referer
        if ( ! $statistics_visibility_form->is_valid( $_POST ) ||
             ! check_admin_referer( 'laterpay_form' ) ||
             ! is_a( $current_user, 'WP_User' ) ||
             ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', null, false )
        ) {
            wp_send_json( $error );
        }

        $result = update_user_meta(
            $current_user->ID,
            'laterpay_hide_statistics_pane',
            $statistics_visibility_form->get_field_value( 'hide_statistics_pane' )
        );

        if ( ! $result ) {
            wp_send_json( $error );
        }

        wp_send_json(
            array(
                'success' => true,
                'message' => __( 'Updated.', 'laterpay' ),
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
        $statistic_form = new LaterPay_Form_Statistic( $_GET );

        $condition = array(
            'verify_nonce' => array(
                'action' => $statistic_form->get_field_value( 'action' ),
            )
        );
        $statistic_form->add_validation( 'nonce', $condition );

        if ( ! $statistic_form->is_valid() ) {
            wp_die();
        }

        $post_id = $statistic_form->get_field_value( 'post_id' );
        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id ) ) {
            wp_die();
        }

        $post = get_post( $post_id );
        $statistic = $this->initialize_post_statistics( $post );

        // assign variables
        $view_args = array(
            'preview_post_as_visitor' => LaterPay_Helper_User::preview_post_as_visitor( $post ),
            'hide_statistics_pane'    => LaterPay_Helper_User::statistics_pane_is_hidden(),
            'currency'                => get_option( 'laterpay_currency' ),
            'post_id'                 => $post_id,
            'statistic'               => $statistic,
        );
        $this->assign( 'laterpay', $view_args );

        wp_send_json( $this->get_text_view( 'frontend/partials/post/post_statistics' ) );
    }

    /**
     * Ajax callback to render the statistics pane.
     *
     * @wp-hook wp_ajax_laterpay_post_statistic_render
     *
     * @return void
     */
    public function ajax_render_tab_without_statistics() {
        $statistic_form = new LaterPay_Form_Statistic( $_GET );

        $condition = array(
            'verify_nonce' => array(
                'action' => $statistic_form->get_field_value( 'action' ),
            )
        );
        $statistic_form->add_validation( 'nonce', $condition );

        if ( ! $statistic_form->is_valid() ) {
            wp_die();
        }

        $post_id = $statistic_form->get_field_value( 'post_id' );
        if ( ! LaterPay_Helper_User::can( 'laterpay_read_post_statistics', $post_id ) ) {
            wp_die();
        }

        $post = get_post( $post_id );
        // assign variables
        $view_args = array(
            'preview_post_as_visitor'   => LaterPay_Helper_User::preview_post_as_visitor( $post ),
            'hide_statistics_pane'      => LaterPay_Helper_User::statistics_pane_is_hidden(),
        );
        $this->assign( 'laterpay', $view_args );

        wp_send_json( $this->get_text_view( 'frontend/partials/post/select_preview_mode_tab' ) );
    }

    /**
     * Generate performance data statistics for post.
     *
     * @param WP_Post $post
     *
     * @return array  $statistic_args
     */
    protected function initialize_post_statistics( WP_Post $post ) {
        // get currency
        $currency = get_option( 'laterpay_currency' );

        // get historical performance data for post
        $payments_history_model = new LaterPay_Model_Payment_History();
        $post_views_model       = new LaterPay_Model_Post_View();
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
            'total'                 => $total,
            'last30DaysRevenue'     => $last30DaysRevenue,
            'todayRevenue'          => $todayRevenue,
            'last30DaysBuyers'      => $last30DaysBuyers,
            'todayBuyers'           => $todayBuyers,
            'last30DaysVisitors'    => $last30DaysVisitors,
            'todayVisitors'         => $todayVisitors,
        );

        return $statistic_args;
    }
}
