<?php

/**
 * LaterPay time pass helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://laterpay.net/developers/plugins-and-libraries
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Passes
{

    const PASS_TOKEN = 'tlp';

    /**
     * Get time pass default options.
     *
     * @param null $key option name
     *
     * @return mixed option value | array of options
     */
    public static function get_default_options( $key = null ) {
        // Default time range. Used during passes creation.
        $defaults = array(
            'pass_id'                => '0',
            'duration'               => '1',
            'period'                 => '1',
            'access_to'              => '0',
            'access_category'        => '',
            'price'                  => '0.99',
            'revenue_model'          => 'ppu',
            'title'                  => __( '24-Hour Pass', 'laterpay' ),
            'description'            => __( '24 hours access to all content on this website', 'laterpay' ),
        );

        if ( isset ( $key ) ) {
            if ( isset( $defaults[$key] ) ) {
                return $defaults[$key];
            }
        }

        return $defaults;
    }

    /**
     * Get valid time pass durations.
     *
     * @param null $key option name
     *
     * @return mixed option value | array of options
     */
    public static function get_duration_options( $key = null ) {
        $durations = array(
            1 => 1, 2, 3, 4, 5, 6,
            7, 8, 9, 10, 11, 12,
            13, 14, 15, 16, 17, 18,
            19, 20, 21, 22, 23, 24,
        );

        if ( isset ( $key ) ) {
            if ( isset( $durations[$key] ) ) {
                return $durations[$key];
            }
        }

        return $durations;
    }

    /**
     * Get valid time pass periods.
     *
     * @param null $key option name
     * @param bool $pluralized
     *
     * @return mixed option value | array of options
     */
    public static function get_period_options( $key = null, $pluralized = false ) {
        // single periods
        $periods = array(
            __( 'Hour', 'laterpay' ),
            __( 'Day', 'laterpay' ),
            __( 'Week', 'laterpay' ),
            __( 'Month', 'laterpay' ),
            __( 'Year', 'laterpay' ),
        );

        // pluralized periods
        $periods_pluralized = array(
            __( 'Hours', 'laterpay' ),
            __( 'Days', 'laterpay' ),
            __( 'Weeks', 'laterpay' ),
            __( 'Months', 'laterpay' ),
            __( 'Years', 'laterpay' ),
        );

        $selected_array = $pluralized ? $periods_pluralized : $periods;

        if ( isset ( $key ) ) {
            if ( isset( $selected_array[$key] ) ) {
                return $selected_array[$key];
            }
        }

        return $selected_array;
    }

    /**
     * Get valid time pass revenue models.
     *
     * @param null $key option name
     *
     * @return mixed option value | array of options
     */
    public static function get_revenue_model_options( $key = null ) {
        $revenues = array(
            'ppu' => __( 'later', 'laterpay' ),
            'sis' => __( 'immediately', 'laterpay' ),
        );

        if ( isset ( $key ) ) {
            if ( isset( $revenues[$key] ) ) {
                return $revenues[$key];
            }
        }

        return $revenues;
    }

    /**
     * Get valid scope of time pass options.
     *
     * @param null $key option name
     *
     * @return mixed option value | array of options
     */
    public static function get_access_options( $key = null ) {
        $access_to = array(
            __( 'All content', 'laterpay' ),
            __( 'All content except for category', 'laterpay' ),
            __( 'All content in category', 'laterpay' ),
        );

        if ( isset ( $key ) ) {
            if ( isset( $access_to[$key] ) ) {
                return $access_to[$key];
            }
        }

        return $access_to;
    }

    /**
     * Get short time pass description.
     *
     * @param  array  $pass_id    time pass data
     * @param  bool   $full_info  need to display full info
     *
     * @return string short time pass description
     */
    public static function get_description( $pass = array(), $full_info = false ) {
        $details  = array();

        if ( ! $pass ) {
            $pass['duration']  = self::get_default_options( 'duration' );
            $pass['period']    = self::get_default_options( 'period' );
            $pass['access_to'] = self::get_default_options( 'access_to' );
        }

        $currency = get_option( 'laterpay_currency' );

        $details['duration'] = $pass['duration'] . ' ' . LaterPay_Helper_Passes::get_period_options( $pass['period'], $pass['duration'] > 1 );
        $details['access']   = __( 'access to', 'laterpay' ) . ' ' . LaterPay_Helper_Passes::get_access_options( $pass['access_to'] );

        // also display category, price, and revenue model, if full_info flag is used
        if ( $full_info ) {
            if ( $pass['access_to'] > 0 ) {
                $category_id = $pass['access_category'];
                $details['category'] = '"' . get_the_category_by_ID( $category_id) . '"';
            }

            $details['price']    = __( 'for', 'laterpay' ) . ' ' . LaterPay_Helper_View::format_number( $pass['price'] ) . ' ' . strtoupper( $currency );
            $details['revenue']  = '(' . strtoupper( $pass['revenue_model'] ) . ')';
        }

        return implode( ' ', $details );
    }

    /**
     * Get time pass select options by type.
     *
     * @param string $type type of select
     *
     * @return string of options
     */
    public static function get_select_options( $type ) {
        $options_html  = '';
        $default_value = null;

        switch ( $type ) {
            case 'duration':
                $elements      = self::get_duration_options();
                $default_value = self::get_default_options( 'duration' );
                break;

            case 'period':
                $elements      = self::get_period_options();
                $default_value = self::get_default_options( 'period' );
                break;

            case 'access':
                $elements      = self::get_access_options();
                $default_value = self::get_default_options( 'access_to' );
                break;

            default:
                return $options_html;
        }

        if ( $elements && is_array( $elements ) ) {
            foreach ( $elements as $id => $name ) {
                if ( $id == $default_value ) {
                    $options_html .= '<option selected="selected" value="' . $id . '">' . $name. '</option>';
                } else {
                    $options_html .= '<option value="' . $id . '">' . $name . '</option>';
                }
            }
        }

        return $options_html;
    }

    /**
     * Get tokenized pass id.
     *
     * @return array $result
     */
    public static function get_tokenized_pass( $pass_id ) {
        return sprintf( '%s_%s', self::PASS_TOKEN , $pass_id );
    }

    /**
     * Get untokenized pass id.
     *
     * @param string $tokenized_pass_id tokenized pass id
     *
     * @return int|null pass id
     */
    public static function get_untokenized_pass_id( $tokenized_pass_id ) {
        $pass_parts = explode( '_', $tokenized_pass_id );
        if ( $pass_parts[0] === self::PASS_TOKEN ) {
            return $pass_parts[1];
        }

        return null;
    }

    /**
     * Get all tokenized pass ids.
     *
     * @param null $passes array of time passes
     *
     * @return array $result
     */
    public static function get_tokenized_passes( $passes = null ) {
        if ( ! isset( $passes ) ) {
            $passes = self::get_all_passes();
        }

        $result = array();
        foreach ( $passes as $pass ) {
            $result[] = self::get_tokenized_pass( $pass->pass_id );
        }

        return $result;
    }

    /**
     * Get time passes for given post.
     *
     * @param int    $post_id             post ID
     * @param null   $passes_with_access  ids of passes with access
     *
     * @return array $passes_list
     */
    public static function get_time_passes_list_for_the_post( $post_id, $passes_with_access = null ) {
        $model = new LaterPay_Model_Pass();

        if ( $post_id !== null ) {
            // get all post categories
            $post_categories = get_the_category( $post_id );
            $post_category_ids = null;

            // get category ids
            foreach ( $post_categories as $category ) {
                $post_category_ids[] = $category->term_id;
                // get category parents and include them in the ids array as well
                $parent_id = get_category( $category->term_id )->parent;
                while ( $parent_id ) {
                    $post_category_ids[] = $parent_id;
                    $parent_id = get_category( $parent_id )->parent;
                }
            }

            // get post passes
            $passes_list = (array) $model->get_post_passes( $post_category_ids );
        } else {
            $passes_list = (array) $model->get_post_passes();
        }

        // correct result, if we have purchased passes
        if ( $passes_with_access ) {
            // check, if user has access to the current post with pass
            $has_access = false;
            foreach ( $passes_list as $pass ) {
                if ( in_array( $pass->pass_id, $passes_with_access ) ) {
                    $has_access = true;
                    break;
                }
            }

            if ( $has_access ) {
                // categories with access (type 2)
                $covered_categories  = array(
                    'included' => array(),
                    'excluded' => null,
                );
                // excluded categories (type 1)
                $excluded_categories = array();

                // go through passes with access and find covered and excluded categories
                foreach ( $passes_with_access as $pass_with_access_id ) {
                    $pass_with_access_data = (array) $model->get_pass_data( $pass_with_access_id );
                    $access_category       = $pass_with_access_data['access_category'];
                    $access_type           = $pass_with_access_data['access_to'];
                    if ( $access_type == 2 ) {
                        $covered_categories['included'][] = $access_category;
                    } else if ( $access_type == 1 ) {
                        $excluded_categories[] = $access_category;
                    } else {
                        return array();
                    }
                }

                // case: full access, except for specific categories
                if ( $excluded_categories ) {
                    foreach ( $excluded_categories as $excluded_category_id ) {
                        // search for excluded category in covered categories
                        $has_covered_category = array_search( $excluded_category_id, $covered_categories );
                        if ( $has_covered_category !== false ) {
                            return array();
                        } else {
                            //  if more than 1 time pass with excluded category was purchased,
                            //  and if its values are not matched, then all categories are covered
                            if ( isset( $covered_categories['excluded'] ) && ( $covered_categories['excluded'] !== $excluded_category_id ) ) {
                                return array();
                            }
                            // store the only category not covered
                            $covered_categories['excluded'] = $excluded_category_id;
                        }
                    }
                }

                // get data without covered categories or only excluded
                if ( isset( $covered_categories['excluded'] ) ) {
                    $passes_list = $model->get_post_passes( array( $covered_categories['excluded'] ) );
                } else {
                    $passes_list = $model->get_post_passes( $covered_categories['included'], true );
                }
            }
        }

        return (array) $passes_list;
    }

    /**
     * Get all time passes.
     *
     * @return array of passes
     */
    public static function get_all_passes() {
        $model = new LaterPay_Model_Pass();

        return $model->get_all_passes();
    }

    /**
     * Get time pass data by id.
     *
     * @param $pass_id
     *
     * @return array
     */
    public static function get_time_pass_by_id( $pass_id ) {
        $model = new LaterPay_Model_Pass();

        if ( $pass_id ) {
            return $model->get_pass_data( (int) $pass_id );
        }

        return array();
    }

    /**
     * Get the LaterPay purchase link for a time pass.
     *
     * @param int  $pass_id pass id
     * @param null $data    additional data
     *
     * @return string url || empty string if something went wrong
     */
    public static function get_laterpay_purchase_link( $pass_id, $data = null ) {
        $time_pass_model = new LaterPay_Model_Pass();

        $pass = (array) $time_pass_model->get_pass_data( $pass_id );
        if ( empty( $pass ) ) {
            return '';
        }

        if ( ! isset ($data) ) {
            $data = array();
        }

        $currency       = get_option( 'laterpay_currency' );
        $currency_model = new LaterPay_Model_Currency();
        $price          = isset( $data['price'] ) ? $data['price'] : $pass['price'];
        $revenue_model  = LaterPay_Helper_Pricing::ensure_valid_revenue_model( $pass['revenue_model'], $price );

        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
                $client_options['cp_key'],
                $client_options['api_key'],
                $client_options['api_root'],
                $client_options['web_root'],
                $client_options['token_name']
        );

        $link = isset( $data['link'] ) ? $data['link'] : get_permalink();

        // prepare URL
        $url_params = array(
            'pass_id'       => self::get_tokenized_pass( $pass_id ),
            'id_currency'   => $currency_model->get_currency_id_by_iso4217_code( $currency ),
            'price'         => $price,
            'date'          => time(),
            'ip'            => ip2long( $_SERVER['REMOTE_ADDR'] ),
            'revenue_model' => $revenue_model,
            'link'          => $link,
        );

        $url  = add_query_arg( array_merge( $url_params, $data ) , $link );
        $hash = LaterPay_Helper_Pricing::get_hash_by_url( $url );
        $url  = $url . '&hash=' . $hash;

        // parameters for LaterPay purchase form
        $params = array(
            'article_id'    => isset( $data['voucher'] ) ? '[#' . $data['voucher'] . ']' : self::get_tokenized_pass( $pass_id ),
            'pricing'       => $currency . ( $price * 100 ),
            'expiry'        => '+' . self::get_pass_expiry_time( $pass ),
            'vat'           => laterpay_get_plugin_config()->get( 'currency.default_vat' ),
            'url'           => $url,
            'title'         => isset( $data['voucher'] ) ? $pass['title'] . ', Code: ' . $data['voucher'] : $pass['title'],
        );

        if ( $revenue_model == 'sis' ) {
            // Single Sale purchase
            return $client->get_buy_url( $params );
        } else {
            // Pay-per-Use purchase
            return $client->get_add_url( $params );
        }
    }

    /**
     * Get time pass expiry time.
     *
     * @param array $pass
     *
     * @return $time expiry time
     */
    protected static function get_pass_expiry_time( $pass ) {
        switch ( $pass['period'] ) {
            // hours
            case 0:
                $time = $pass['duration'] * 60 * 60;
                break;

            // days
            case 1:
                $time = $pass['duration'] * 60 * 60 * 24;
                break;

            // weeks
            case 2:
                $time = $pass['duration'] * 60 * 60 * 24 * 7;
                break;

            // months
            case 3:
                $time = $pass['duration'] * 60 * 60 * 24 * 31;
                break;

            // years
            case 4:
                $time = $pass['duration'] * 60 * 60 * 24 * 365;
                break;

            default :
                $time = 0;
        }

        return $time;
    }

    /**
<<<<<<< HEAD
     * Get time passes statistic.
     *
     * @return array return summary and individual statistics
     */
    public static function get_time_passes_statistic() {
        $history_model      = new LaterPay_Model_Payments_History();
        $passes             = LaterPay_Helper_Passes::get_all_passes();
        $summary_active     = 0;
        $summary_unredeemed = 0;
        $summary_revenue    = 0;
        $summary_sold       = 0;

        if ( $passes ) {
            foreach ( $passes as $pass ) {
                $pass         = (array) $pass;
                $pass_history = $history_model->get_time_pass_history( $pass['pass_id'] );
                $duration     = self::get_pass_expiry_time( $pass ); // in seconds

                // calculate committed revenue, unredeemed codes, and number of active time passes
                $committed_revenue   = 0;
                $unredeemed         = 0;
                $active             = 0;

                if  ( $pass_history && is_array( $pass_history ) ) {
                    foreach ( $pass_history as $hist ) {
                        $committed_revenue += $hist->price;

                        // check, if there are unredeemed gift codes
                        if ( $hist->code && ! LaterPay_Helper_Vouchers::get_gift_code_usages_count( $hist->code ) ) {
                            $unredeemed++;
                            $summary_unredeemed++;
                        }

                        // check, if pass is still active
                        $start_date   = strtotime( $hist->date );
                        $current_date = time();
                        if ( ( $start_date + $duration ) > $current_date ) {
                            $active++;
                            $summary_active++;
                        }
                    }
                } else {
                    $pass_history = array();
                }

                $pass_statistics = array(
                    'data'              => $pass,
                    'active'            => $active,                 // number of active time passes
                    'sold'              => count( $pass_history ),  // number of purchases
                    'unredeemed'        => $unredeemed,             // number of unredeemed gift codes
                    'committed_revenue' => $committed_revenue,      // total value of purchases
                    'paid_price'        => 0,
                );

                $statistic['individual'][$pass['pass_id']] = $pass_statistics;
            }
        }

        // calculate summary statistics
        $passes_history = $history_model->get_time_pass_history();

        if ( $passes_history && is_array( $passes_history ) ) {
            $summary_sold = count( $passes_history );
            foreach ( $passes_history as $hist ) {
                $summary_revenue += $hist->price;
            }
        }

        $statistic['summary'] = array(
            'active'            => $summary_active,
            'sold'              => $summary_sold,
            'unredeemed'        => $summary_unredeemed,
            'committed_revenue' => $summary_revenue,
            'paid_price'        => 0,
        );

        return $statistic;
    }

    /**
     * Get number of expiring time passes for each week, week numbers determined by ticks parameter.
     *
     * @param $pass_id pass id | 0 or null for all time passes
     * @param $ticks   period in weeks
     *
     * @return array
     */
    public static function get_time_pass_expiry_by_weeks( $pass_id, $ticks ) {
        $history_model = new LaterPay_Model_Payments_History();
        $data          = array();
        $duration      = 0;

        // init array
        if ( ! $ticks ) {
            return $data;
        } else {
            $i = 0;
            while ( $i <= $ticks ) {
                $data[] = 0;
                $i++;
            }
        }

        if ( $pass_id ) {
            // get history for one given time pass
            $pass     = (array) self::get_time_pass_by_id( $pass_id );
            $duration = self::get_pass_expiry_time( $pass );
            $history  = $history_model->get_time_pass_history( $pass_id );
        } else {
            // get history for all time passes
            $history  = $history_model->get_time_pass_history();
        }

        if ( $history && is_array( $history ) ) {
            $week_duration  = 7 * 24 * 60 * 60; // in seconds
            $current_date   = time();

            // get expiry data for each time pass
            foreach ( $history as $hist ) {
                $key        = 0;
                $start_date = strtotime( $hist->date );

                // determine expiry date of time pass
                if ( ! $duration ) {
                    $pass_id        = $hist->pass_id;
                    $pass           = (array) self::get_time_pass_by_id( $pass_id );
                    $expiry_date    = $start_date + self::get_pass_expiry_time( $pass );
                } else {
                    $expiry_date    = $start_date + $duration;
                }

                // get week in which time pass expires, if time pass is active
                if ( $expiry_date > $current_date ) {
                    $week_number = 1;

                    while( ( $start_date + $week_number * $week_duration ) < $expiry_date ) {
                        $week_number++;
                        $key++;
                    }

                    $data[$key]++;
                }
            }
        }

        return $data;
    }

    /*
     * Get count of existing passes.
     *
     * @return int count of passes
     */
    public static function get_passes_count() {
        $model = new LaterPay_Model_Pass();

        return $model->get_passes_count();
    }
}
