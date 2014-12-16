<?php

class LaterPay_Helper_Vouchers
{
    const VOUCHER_CODE_LENGTH = 6;
    const VOUCHER_CHARS       = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

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
     * Save vouchers for current pass.
     *
     * @param int   $pass_id
     * @param array $vouchers_data
     * @param bool  $no_explode
     *
     * @return void
     */
    public static function save_pass_vouchers( $pass_id, $vouchers_data, $no_explode = false, $is_gifts = false ) {
        $vouchers     = self::get_all_vouchers( $is_gifts );
        $new_vouchers = array();

        if ( $vouchers_data && is_array( $vouchers_data ) ) {
            foreach ( $vouchers_data as $voucher ) {
                if ( $no_explode ) {
                    $new_vouchers = $vouchers_data;
                    break;
                }

                list( $code, $price ) = explode( '|', $voucher );
                // format and save price
                $price = number_format( (float) str_replace( ',', '.', $price ), 2 );
                $new_vouchers[$code] = $price;
            }
        }

        if ( ! $new_vouchers ) {
            unset( $vouchers[ $pass_id ] );
        } else {
            $vouchers[ $pass_id ] = $new_vouchers;
        }

        // save new voucher data
        $is_gifts ? update_option( 'laterpay_gift_codes', $vouchers ) : update_option( 'laterpay_voucher_codes', $vouchers );
        // actualize voucher statistic
        self::actualize_voucher_statistic();
    }

    /**
     * Get voucher codes of current time pass.
     *
     * @param int $pass_id
     *
     * @return array
     */
    public static function get_pass_vouchers( $pass_id, $is_gifts = false ) {
        $vouchers = self::get_all_vouchers( $is_gifts );
        if ( ! isset( $vouchers[ $pass_id ] ) ) {
            return array();
        }

        return $vouchers[ $pass_id ];
    }

    /**
     * Get all vouchers.
     *
     * @return array of vouchers
     */
    public static function get_all_vouchers( $is_gifts = false ) {
        $vouchers = $is_gifts ? get_option( 'laterpay_gift_codes' ) : get_option( 'laterpay_voucher_codes' );
        if ( ! $vouchers || ! is_array( $vouchers ) ) {
            $is_gifts ? update_option( 'laterpay_gift_codes', '' ) : update_option( 'laterpay_voucher_codes', '' );

            return array();
        }

        return $vouchers;
    }

    /**
     * Delete voucher code.
     *
     * @param int       $pass_id
     * @param string    $code
     *
     * @return void
     */
    public static function delete_voucher_code( $pass_id, $code = null, $is_gifts = false ) {
        $pass_vouchers = self::get_pass_vouchers( $pass_id, $is_gifts );
        if ( $pass_vouchers && is_array( $pass_vouchers ) ) {
            if ( $code ) {
                unset( $pass_vouchers[$code] );
            } else {
                $pass_vouchers = array();
            }
        }

        self::save_pass_vouchers( $pass_id, $pass_vouchers, true, $is_gifts = false );
    }

    /**
     * Check, if voucher code exists and return pass_id and new price.
     *
     * @param $code
     *
     * @return mixed $voucher_data
     */
    public static function check_voucher_code( $code, $is_gifts = false ) {
        $vouchers = self::get_all_vouchers( $is_gifts );

        // search code
        foreach ( $vouchers as $pass_id => $pass_vouchers ) {
            foreach ( $pass_vouchers as $voucher_code => $voucher_price ) {
                if ( $code === $voucher_code) {
                    $voucher_data = array(
                        'pass_id' => $pass_id,
                        'code'    => $voucher_code,
                        'price'   => $voucher_price,
                    );

                    return $voucher_data;
                }
            }
        }

        return null;
    }

    /**
     * Check, if given time passes have vouchers.
     *
     * @param array $passes array of time passes
     *
     * @return bool $has_vouchers
     */
    public static function passes_have_vouchers( $passes, $is_gifts = false ) {
        $has_vouchers = false;

        if ( $passes && is_array( $passes ) ) {
            foreach ( $passes as $pass ) {
                $pass = (array) $pass;
                if ( self::get_pass_vouchers( $pass[ 'pass_id' ], $is_gifts ) ) {
                    $has_vouchers = true;
                    break;
                }
            }
        }

        return $has_vouchers;
    }


    /**
     * Actualize voucher statistic.
     *
     * @return void
     */
    public static function actualize_voucher_statistic( ) {
        $vouchers  = self::get_all_vouchers();
        $statistic = self::get_all_vouchers_statistic();
        $result    = $statistic;

        foreach ( $statistic as $pass_id => $statistic_data ) {
            if ( ! isset( $vouchers[$pass_id] ) ) {
                unset( $result[$pass_id] );
            } else {
                foreach ( $statistic_data as $code => $usages ) {
                    if ( ! isset( $vouchers[$pass_id][$code] ) ) {
                        unset( $result[$pass_id][$code] );
                    }
                }
            }
        }

        // update voucher statistic
        update_option( 'laterpay_voucher_statistic', $result );
    }

    /**
     * Update voucher statistic.
     *
     * @param int    $pass_id time pass id
     * @param string $code    voucher code
     *
     * @return bool success or error
     */
    public static function update_voucher_statistic( $pass_id, $code ) {
        $pass_vouchers = self::get_pass_vouchers( $pass_id );

        // check, if such voucher exists
        if ( $pass_vouchers && isset( $pass_vouchers[ $code ] ) ) {
            // get all voucher statistics for this pass
            $voucher_statistic_data = self::get_pass_vouchers_statistic( $pass_id );
            // check, if statistic is empty
            if ( $voucher_statistic_data ) {
                // increment counter by 1, if statistic exists
                $voucher_statistic_data[ $code ] += 1;
            } else {
                // create new data array, if statistic is empty
                $voucher_statistic_data[ $code ] = 1;
            }

            $statistic           = self::get_all_vouchers_statistic();
            $statistic[$pass_id] = $voucher_statistic_data;
            update_option( 'laterpay_voucher_statistic', $statistic );

            return true;
        }

        return false;
    }

    /**
     * Get time pass voucher statistic by time pass id.
     *
     * @param  int $pass_id time pass id
     *
     * @return array $statistic
     */
    public static function get_pass_vouchers_statistic( $pass_id ) {
        $statistic = self::get_all_vouchers_statistic();

        if ( isset( $statistic[$pass_id] ) ) {
            return $statistic[$pass_id];
        }

        return array();
    }

    /**
     * Get statistics for all vouchers.
     *
     * @return array $statistic
     */
    public static function get_all_vouchers_statistic() {
        $statistic = get_option( 'laterpay_voucher_statistic' );
        if ( ! $statistic || ! is_array( $statistic ) ) {
            update_option( 'laterpay_voucher_statistic', '' );

            return array();
        }

        return $statistic;
    }

    /**
     * Get the LaterPay purchase link for a voucher.
     *
     * @param int  $pass_id
     * @param null $price   new price (voucher code)
     * @param null $code    url of page to redirect
     *
     * @return string url || empty string if something went wrong
     */
    public static function get_laterpay_purchase_link( $pass_id, $price = null, $code = null, $link = null ) {
        $time_pass_model = new LaterPay_Model_Pass();

        $pass = (array) $time_pass_model->get_pass_data( $pass_id );
        if ( empty( $pass ) ) {
            return '';
        }

        $currency       = get_option( 'laterpay_currency' );
        $price          = isset( $price ) ? $price : $pass['price'];
        $revenue_model  = LaterPay_Helper_Pricing::ensure_valid_revenue_model( $pass['revenue_model'], $price );

        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        $url = isset( $link ) ? $link : get_permalink();

        // parameters for LaterPay purchase form
        $params = array(
            'article_id'    => '#' . $code,
            'pricing'       => $currency . ( $price * 100 ),
            'vat'           => laterpay_get_plugin_config()->get( 'currency.default_vat' ),
            'url'           => $url,
            'title'         => '#' . $code,
        );

        if ( $revenue_model == 'sis' ) {
            // Single Sale purchase
            return $client->get_buy_url( $params );
        } else {
            // Pay-per-Use purchase
            return $client->get_add_url( $params );
        }
    }
}
