<?php

/**
 * LaterPay vouchers helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Voucher
{
    /**
     * @const int Default length of voucher code.
     */
    const VOUCHER_CODE_LENGTH  = 6;

    /**
     * @const string Chars allowed in voucher code.
     */
    const VOUCHER_CHARS        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    /**
     * @const string Name of option for time pass voucher.
     */
    const VOUCHER_CODES_OPTION = 'laterpay_voucher_codes';

    /**
     * @const string Name of option to update for subscription voucher.
     */
    const SUBSCRIPTION_VOUCHER_CODES_OPTION = 'laterpay_subscription_voucher_codes';

    /**
     * @const string Name of option to update for global vouchers.
     */
    const GLOBAL_VOUCHER_CODES_OPTION = 'laterpay_global_voucher_codes';

    /**
     * Generate random voucher code.
     *
     * @param int $length voucher code length
     *
     * @return string voucher code
     */
    public static function generate_voucher_code( $length = self::VOUCHER_CODE_LENGTH ) {
        $voucher_code  = '';
        $possibleChars = self::VOUCHER_CHARS;

        for ( $i = 0; $i < $length; $i++ ) {
            mt_srand();
            $rand = mt_rand( 0, strlen( $possibleChars ) - 1 );
            $voucher_code .= substr( $possibleChars, $rand, 1 );
        }

        return $voucher_code;
    }

    /**
     * Save vouchers for current time pass.
     *
     * @param int   $pass_id Time Pass Id.
     * @param array $data    Time Pass Voucher Data.
     *
     * @return void
     */
    public static function save_time_pass_vouchers( $pass_id, $data ) {
        $vouchers = self::get_all_time_pass_vouchers();

        if ( ! $data ) {
            unset( $vouchers[ $pass_id ] );
        } elseif ( is_array( $data ) ) {
            $vouchers[ $pass_id ] = $data;
        }

        // save new voucher data
        update_option( self::VOUCHER_CODES_OPTION, $vouchers );
    }

    /**
     * Save vouchers for current subscription.
     *
     * @param int   $sub_id Subscription Id.
     * @param array $data   Subscription Voucher Data.
     *
     * @return void
     */
    public static function save_subscription_vouchers( $sub_id, $data ) {
        $vouchers    = self::get_all_subscription_vouchers();
        $option_name = self::SUBSCRIPTION_VOUCHER_CODES_OPTION;

        if ( ! $data ) {
            unset( $vouchers[ $sub_id ] );
        } elseif ( is_array( $data ) ) {
            $vouchers[ $sub_id ] = $data;
        }

        // save new voucher data
        update_option( $option_name, $vouchers );
    }

    /**
     * Get voucher codes of current time pass.
     *
     * @param int $pass_id Time Pass Id.
     *
     * @return array
     */
    public static function get_time_pass_vouchers( $pass_id ) {
        $vouchers = self::get_all_time_pass_vouchers();
        if ( ! isset( $vouchers[ $pass_id ] ) ) {
            return array();
        }

        return $vouchers[ $pass_id ];
    }

    /**
     * Get voucher codes of subscription.
     *
     * @param int $sub_id Subscription Id.
     *
     * @return array
     */
    public static function get_subscription_vouchers( $sub_id ) {
        $vouchers = self::get_all_subscription_vouchers();
        if ( ! isset( $vouchers[ $sub_id ] ) ) {
            return array();
        }

        return $vouchers[ $sub_id ];
    }

    /**
     * Get all time pass vouchers.
     *
     * @return array of vouchers
     */
    public static function get_all_time_pass_vouchers() {
        $vouchers = get_option( self::VOUCHER_CODES_OPTION );

        if ( ! $vouchers || ! is_array( $vouchers ) ) {
            update_option( self::VOUCHER_CODES_OPTION, '' );
            $vouchers = array();
        }

        // format prices
        foreach ( $vouchers as $time_pass_id => $time_pass_voucher ) {
            foreach ( $time_pass_voucher as $code => $data ) {
                $vouchers[ $time_pass_id ][ $code ]['price'] = LaterPay_Helper_View::format_number( $data['price'] );
            }
        }

        return $vouchers;
    }

    /**
     * Get all subscription vouchers.
     *
     * @return array of vouchers
     */
    public static function get_all_subscription_vouchers() {
        $option_name = self::SUBSCRIPTION_VOUCHER_CODES_OPTION;

        $vouchers = get_option( $option_name );
        if ( ! $vouchers || ! is_array( $vouchers ) ) {
            update_option( $option_name, '' );
            $vouchers = array();
        }

        // format prices
        foreach ( $vouchers as $subscription_id => $subscription_voucher ) {
            foreach ( $subscription_voucher as $code => $data ) {
                $vouchers[ $subscription_id ][ $code ]['price'] = LaterPay_Helper_View::format_number( $data['price'] );
            }
        }

        return $vouchers;
    }

    /**
     * Delete time pass voucher code.
     *
     * @param int    $pass_id Time Pass Id.
     * @param string $code    Time Pass Voucher Data.
     *
     * @return void
     */
    public static function delete_time_pass_voucher_code( $pass_id, $code = null ) {
        $pass_vouchers = self::get_time_pass_vouchers( $pass_id );
        if ( $pass_vouchers && is_array( $pass_vouchers ) ) {
            if ( $code ) {
                unset( $pass_vouchers[ $code ] );
            } else {
                $pass_vouchers = array();
            }
        }

        self::save_time_pass_vouchers( $pass_id, $pass_vouchers );
    }

    /**
     * Delete subscription voucher code.
     *
     * @param int    $sub_id Subscription Id.
     * @param string $code   Subscription Voucher Data.
     *
     * @return void
     */
    public static function delete_subscription_voucher_code( $sub_id, $code = null ) {
        $sub_vouchers = self::get_subscription_vouchers( $sub_id );
        if ( $sub_vouchers && is_array( $sub_vouchers ) ) {
            if ( $code ) {
                unset( $sub_vouchers[ $code ] );
            } else {
                $sub_vouchers = array();
            }
        }

        self::save_subscription_vouchers( $sub_id, $sub_vouchers );
    }

    /**
     * Check, if voucher code exists and return pass_id and new price.
     *
     * @param string $code
     *
     * @return mixed $voucher_data
     */
    public static function check_voucher_code( $code ) {
        $all_passes              = [];
        $time_pass_vouchers      = self::get_all_time_pass_vouchers();
        $all_passes['time_pass'] = $time_pass_vouchers;

        // Subscription vouchers.
        $subscription_vouchers      = self::get_all_subscription_vouchers();
        $all_passes['subscription'] = $subscription_vouchers;

        // add global vouchers.
        $global_vouchers      = self::get_all_global_vouchers();
        $all_passes['global'] = $global_vouchers;

        foreach ( $all_passes as $key => $vouchers ){
            // search code
            foreach ( $vouchers as $pass_id => $pass_vouchers ) {
                foreach ( $pass_vouchers as $voucher_code => $voucher_data ) {
                    if ( $code === $voucher_code ) {
                        $data = array(
                            'pass_id' => $pass_id,
                            'code'    => $voucher_code,
                            'price'   => number_format( LaterPay_Helper_View::normalize( $voucher_data['price'] ), 2 ),
                            'title'   => $voucher_data['title'],
                            'type'    => $key,
                        );

                        return $data;
                    }
                }
            }

        }

        return null;
    }

    /**
     * Check, if given time passes have vouchers.
     *
     * @param array $time_passes array of time passes
     *
     * @return bool $has_vouchers
     */
    public static function passes_have_vouchers( $time_passes ) {
        $has_vouchers = false;

        if ( $time_passes && is_array( $time_passes ) ) {
            foreach ( $time_passes as $time_pass ) {
                if ( self::get_time_pass_vouchers( $time_pass['pass_id'] ) ) {
                    $has_vouchers = true;
                    break;
                }
            }
        }

        return $has_vouchers;
    }

    /**
     * Check if given subscriptions have vouchers.
     *
     * @param array $subscriptions Array of subscriptions.
     *
     * @return bool $has_vouchers
     */
    public static function subscriptions_have_vouchers( $subscriptions ) {
        $has_vouchers = false;

        if ( $subscriptions && is_array( $subscriptions ) ) {
            foreach ( $subscriptions as $subscription ) {
                if ( self::get_subscription_vouchers( $subscription['id'] ) ) {
                    $has_vouchers = true;
                    break;
                }
            }
        }

        return $has_vouchers;
    }

    /**
     * Save vouchers for global pricing.
     *
     * @param array $data Voucher Data.
     *
     * @return void
     */
    public static function save_global_vouchers( $data ) {
        // save new voucher data
        update_option( self::GLOBAL_VOUCHER_CODES_OPTION, [ $data ] );
    }

    /**
     * Get all global vouchers.
     *
     * @return array of vouchers
     */
    public static function get_all_global_vouchers() {

        $vouchers = get_option( self::GLOBAL_VOUCHER_CODES_OPTION );
        if ( ! $vouchers || ! is_array( $vouchers ) ) {
            update_option( self::GLOBAL_VOUCHER_CODES_OPTION, '' );
            $vouchers = [ [] ]; // Ticket #1397.
        }

        // format prices.
        foreach ( $vouchers as $index => $voucher_data ) {
            foreach ( $voucher_data as $code => $data ) {
                $vouchers[ $index ][ $code ]['price'] = LaterPay_Helper_View::format_number( $data['price'] );
            }
        }

        return $vouchers;
    }
}
