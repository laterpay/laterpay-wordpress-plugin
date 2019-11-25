<?php

/**
 * LaterPay REST controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Rest {

    /**
     * Register endpoints.
     */
    public function register_routes() {
        register_rest_route(
            'laterpay/v1',
            '/media-price',
            [
                'methods'  => 'GET',
                'callback' => [ $this, 'get_media_price' ],
                'args'     => [
                    'media_id' => [
                        'required'          => true,
                        'validate_callback' => function ( $param ) {
                            return ( 0 < intval( $param ) && is_numeric( $param ) );
                        },
                        'sanitize_callback' => function ( $param ) {
                            return intval( $param );
                        },
                    ],
                ],
            ]
        );
    }


    /**
     * REST API callback to get media pricing information.
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_media_price( \WP_REST_Request $request ) {
        $currency_config = LaterPay_Helper_Config::get_currency_config();
        $symbol          = 'USD' === $currency_config['code'] ? '$' : '€';
        $media_id        = $request->get_param( 'media_id' );
        $data['price']   = $symbol . '0.00';
        $is_purchasable  = LaterPay_Helper_Pricing::is_purchasable( $media_id );
        if ( $is_purchasable ) {
            $revenue_label = LaterPay_Helper_Pricing::get_revenue_label( LaterPay_Helper_Pricing::get_post_revenue_model( $media_id ) );
            $price         = LaterPay_Helper_View::format_number( LaterPay_Helper_Pricing::get_post_price( $media_id ) );
            $data['price'] = sprintf( '%s%s %s', $symbol, $price, $revenue_label );
        }

        return new \WP_REST_Response( $data, 200 );
    }

}
