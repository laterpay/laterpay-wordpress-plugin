<?php
/**
 * LaterPay post column controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */

class LaterPay_Controller_Admin_Post_Column extends LaterPay_Controller_Base
{

    /**
     * Add custom columns to posts table.
     *
     * @param array $columns
     *
     * @return array $extended_columns
     */
    public function add_columns_to_posts_table( $columns ) {
        $extended_columns   = array();
        $insert_after       = 'title';

        foreach ( $columns as $key => $val ) {
            $extended_columns[ $key ] = $val;
            if ( $key == $insert_after ) {
                $extended_columns['post_price']         = __( 'Price', 'laterpay' );
                $extended_columns['post_price_type']    = __( 'Price Type', 'laterpay' );
            }
        }

        return $extended_columns;
    }

    /**
     * Populate custom columns in posts table with data.
     *
     * @wp-hook manage_post_posts_custom_column
     *
     * @param string $column_name
     * @param int    $post_id
     *
     * @return void
     */
    public function add_data_to_posts_table( $column_name, $post_id ) {
        switch ( $column_name ) {
            case 'post_price':
                $price              = (float) LaterPay_Helper_Pricing::get_post_price( $post_id );
                $localized_price    = LaterPay_Helper_View::format_number( $price );
                $currency           = get_option( 'laterpay_currency' );

                // render the price of the post, if it exists
                if ( $price > 0 ) {
                    echo laterpay_sanitize_output( "<strong>$localized_price</strong> <span>$currency</span>" );
                } else {
                    echo '&mdash;';
                }
                break;

            case 'post_price_type':
                $post_prices = get_post_meta( $post_id, 'laterpay_post_prices', true );
                if ( ! is_array( $post_prices ) ) {
                    $post_prices = array();
                }

                if ( array_key_exists( 'type', $post_prices ) ) {
                    // render the price type of the post, if it exists
                    switch ( $post_prices['type'] ) {
                        case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
                            $revenue_model      = ( LaterPay_Helper_Pricing::get_post_revenue_model( $post_id ) == 'sis' )
                                                    ? __( 'Single Sale', 'laterpay' )
                                                    : __( 'Pay-per-Use', 'laterpay' );
                            $post_price_type    = __( 'individual price', 'laterpay' ) . ' (' . $revenue_model . ')';
                            break;

                        case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
                            $post_price_type = __( 'dynamic individual price', 'laterpay' );
                            break;

                        case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
                            $post_price_type = __( 'category default price', 'laterpay' );
                            break;

                        case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
                            $post_price_type = __( 'global default price', 'laterpay' );
                            break;

                        default:
                            $post_price_type = '&mdash;';
                    }

                    echo laterpay_sanitize_output( $post_price_type );
                } else {
                    // label the post to use the global default price, if a positive  global default price is defined,
                    // but the post does not have a price type defined
                    $global_default_price = (float) get_option( 'laterpay_global_price' );
                    if ( $global_default_price > 0 ) {
                        echo laterpay_sanitize_output( __( 'global default price', 'laterpay' ) );
                    } else {
                        echo '&mdash;';
                    }
                }
                break;
        }
    }
}
