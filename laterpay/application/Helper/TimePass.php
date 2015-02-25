<?php

/**
 * LaterPay time pass helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_TimePass
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
     * @param  array  $time_pass time pass data
     * @param  bool   $full_info need to display full info
     *
     * @return string short time pass description
     */
    public static function get_description( $time_pass = array(), $full_info = false ) {
        $details  = array();

        if ( ! $time_pass ) {
            $time_pass['duration']  = self::get_default_options( 'duration' );
            $time_pass['period']    = self::get_default_options( 'period' );
            $time_pass['access_to'] = self::get_default_options( 'access_to' );
        }

        $currency = get_option( 'laterpay_currency' );

        $details['duration'] = $time_pass['duration'] . ' ' .
                                LaterPay_Helper_TimePass::get_period_options( $time_pass['period'], $time_pass['duration'] > 1 );
        $details['access']   = __( 'access to', 'laterpay' ) . ' ' .
                                LaterPay_Helper_TimePass::get_access_options( $time_pass['access_to'] );


        // also display category, price, and revenue model, if full_info flag is used
        if ( $full_info ) {
            if ( $time_pass['access_to'] > 0 ) {
                $category_id = $time_pass['access_category'];
                $details['category'] = '"' . get_the_category_by_ID( $category_id) . '"';
            }

            $details['price']    = __( 'for', 'laterpay' ) . ' ' .
                                    LaterPay_Helper_View::format_number( $time_pass['price'] ) .
                                    ' ' . strtoupper( $currency );
            $details['revenue']  = '(' . strtoupper( $time_pass['revenue_model'] ) . ')';
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
     * Get tokenized time pass id.
     *
     * @param string $untokenized_time_pass_id untokenized time pass id
     *
     * @return array $result
     */
    public static function get_tokenized_time_pass_id( $untokenized_time_pass_id ) {
        return sprintf( '%s_%s', self::PASS_TOKEN , $untokenized_time_pass_id );
    }

    /**
     * Get untokenized time pass id.
     *
     * @param string $tokenized_pass_id tokenized time pass id
     *
     * @return int|null pass id
     */
    public static function get_untokenized_time_pass_id( $tokenized_time_pass_id ) {
        $time_pass_parts = explode( '_', $tokenized_time_pass_id );
        if ( $time_pass_parts[0] === self::PASS_TOKEN ) {
            return $time_pass_parts[1];
        }

        return null;
    }

    /**
     * Get all tokenized time pass ids.
     *
     * @param null $time_passes array of time passes
     *
     * @return array $result
     */
    public static function get_tokenized_time_pass_ids( $time_passes = null ) {
        if ( ! isset( $time_passes ) ) {
            $time_passes = self::get_all_time_passes();
        }

        $result = array();
        foreach ( $time_passes as $time_pass ) {
            $result[] = self::get_tokenized_time_pass_id( $time_pass->pass_id );
        }

        return $result;
    }

    /**
     * Get all time passes for a given post.
     *
     * @param int    $post_id                   post id
     * @param null   $time_passes_with_access   ids of time passes with access
     *
     * @return array $time_passes
     */
    public static function get_time_passes_list_by_post_id( $post_id, $time_passes_with_access = null ) {
        $model = new LaterPay_Model_TimePass();

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

            // get list of time passes that cover this post
            $time_passes = (array) $model->get_time_passes_by_category_ids( $post_category_ids );
        } else {
            $time_passes = (array) $model->get_time_passes_by_category_ids();
        }

        // correct result, if we have purchased time passes
        if ( $time_passes_with_access ) {
            // check, if user has access to the current post with time pass
            $has_access = false;
            foreach ( $time_passes as $time_pass ) {
                if ( in_array( $time_pass->pass_id, $time_passes_with_access ) ) {
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

                // go through time passes with access and find covered and excluded categories
                foreach ( $time_passes_with_access as $time_pass_with_access_id ) {
                    $time_pass_with_access_data = (array) $model->get_pass_data( $time_pass_with_access_id );
                    $access_category            = $time_pass_with_access_data['access_category'];
                    $access_type                = $time_pass_with_access_data['access_to'];
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
                    $time_passes = $model->get_time_passes_by_category_ids( array( $covered_categories['excluded'] ) );
                } else {
                    $time_passes = $model->get_time_passes_by_category_ids( $covered_categories['included'], true );
                }
            }
        }

        return (array) $time_passes;
    }

    /**
     * Get all time passes.
     *
     * @return array of time passes
     */
    public static function get_all_time_passes() {
        $model = new LaterPay_Model_TimePass();

        return $model->get_all_time_passes();
    }

    /**
     * Get time pass data by id.
     *
     * @param $time_pass_id
     *
     * @return array
     */
    public static function get_time_pass_by_id( $time_pass_id ) {
        $model = new LaterPay_Model_TimePass();

        if ( $time_pass_id ) {
            return $model->get_pass_data( (int) $time_pass_id );
        }

        return array();
    }

    /**
     * Get the LaterPay purchase link for a time pass.
     *
     * @param int  $time_pass_id pass id
     * @param null $data additional data
     *
     * @return string url || empty string if something went wrong
     */
    public static function get_laterpay_purchase_link( $time_pass_id, $data = null ) {
        $time_pass_model = new LaterPay_Model_TimePass();

        $time_pass = (array) $time_pass_model->get_pass_data( $time_pass_id );
        if ( empty( $time_pass ) ) {
            return '';
        }

        if ( ! isset ($data) ) {
            $data = array();
        }

        $currency       = get_option( 'laterpay_currency' );
        $currency_model = new LaterPay_Model_Currency();
        $price          = isset( $data['price'] ) ? $data['price'] : $time_pass['price'];
        $revenue_model  = LaterPay_Helper_Pricing::ensure_valid_revenue_model( $time_pass['revenue_model'], $price );

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
            'pass_id'       => self::get_tokenized_time_pass_id( $time_pass_id ),
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
            'article_id'    => isset( $data['voucher'] ) ? '[#' . $data['voucher'] . ']' : self::get_tokenized_time_pass_id( $time_pass_id ),
            'pricing'       => $currency . ( $price * 100 ),
            'expiry'        => '+' . self::get_time_pass_expiry_time( $time_pass ),
            'vat'           => laterpay_get_plugin_config()->get( 'currency.default_vat' ),
            'url'           => $url,
            'title'         => isset( $data['voucher'] ) ? $time_pass['title'] . ', Code: ' . $data['voucher'] : $time_pass['title'],
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
     * @param array $time_pass
     *
     * @return $time expiry time
     */
    protected static function get_time_pass_expiry_time( $time_pass ) {
        switch ( $time_pass['period'] ) {
            // hours
            case 0:
                $time = $time_pass['duration'] * 60 * 60;
                break;

            // days
            case 1:
                $time = $time_pass['duration'] * 60 * 60 * 24;
                break;

            // weeks
            case 2:
                $time = $time_pass['duration'] * 60 * 60 * 24 * 7;
                break;

            // months
            case 3:
                $time = $time_pass['duration'] * 60 * 60 * 24 * 31;
                break;

            // years
            case 4:
                $time = $time_pass['duration'] * 60 * 60 * 24 * 365;
                break;

            default :
                $time = 0;
        }

        return $time;
    }

    /**
     * Get time passes statistics.
     *
     * @return array return summary and individual statistics
     */
    public static function get_time_passes_statistic() {
        $history_model      = new LaterPay_Model_Payment_History();
        $time_passes        = LaterPay_Helper_TimePass::get_all_time_passes();
        $summary_active     = 0;
        $summary_unredeemed = 0;
        $summary_revenue    = 0;
        $summary_sold       = 0;

        if ( $time_passes ) {
            foreach ( $time_passes as $time_pass ) {
                $time_pass          = (array) $time_pass;
                $time_pass_history  = $history_model->get_time_pass_history( $time_pass['pass_id'] );
                $duration           = self::get_time_pass_expiry_time( $time_pass ); // in seconds

                // calculate time pass KPIs
                $committed_revenue  = 0; // total value of purchased time passes
                $unredeemed         = 0; // number of unredeemed gift codes
                $active             = 0; // number of active time passes
                $sold               = 0; // number of sold time passes

                if  ( $time_pass_history && is_array( $time_pass_history ) ) {
                    foreach ( $time_pass_history as $hist ) {
                        $has_unredeemed     = false;
                        $committed_revenue += $hist->price;

                        if ( $hist->price > 0 ) {
                            $sold++;
                        }

                        // check, if there are unredeemed gift codes
                        if ( $hist->code && ! LaterPay_Helper_Voucher::get_gift_code_usages_count( $hist->code ) ) {
                            $unredeemed++;
                            $summary_unredeemed++;
                        }

                        if ( $hist->code ) {
                            $has_unredeemed = true;
                        }

                        // check, if pass is still active
                        if ( ! $has_unredeemed ) {
                            $start_date   = strtotime( $hist->date );
                            $current_date = time();
                            if ( ( $start_date + $duration ) > $current_date ) {
                                $active++;
                                $summary_active++;
                            }
                        }
                    }
                }

                $time_pass_statistics = array(
                    'data'              => $time_pass,
                    'active'            => LaterPay_Helper_View::format_number( $active, false ),
                    'sold'              => LaterPay_Helper_View::format_number( $sold, false ),
                    'unredeemed'        => LaterPay_Helper_View::format_number( $unredeemed, false ),
                    'committed_revenue' => number_format_i18n( $committed_revenue, 2 ),
                );

                $statistic['individual'][$time_pass['pass_id']] = $time_pass_statistics;
            }
        }

        // calculate summary statistics
        $time_passes_history = $history_model->get_time_pass_history();

        if ( $time_passes_history && is_array( $time_passes_history ) ) {
            $summary_sold = count( $time_passes_history );
            foreach ( $time_passes_history as $hist ) {
                $summary_revenue += $hist->price;
            }
        }

        $statistic['summary'] = array(
            'active'            => LaterPay_Helper_View::format_number( $summary_active, false ),
            'sold'              => LaterPay_Helper_View::format_number( $summary_sold, false ),
            'unredeemed'        => LaterPay_Helper_View::format_number( $summary_unredeemed, false ),
            'committed_revenue' => number_format_i18n( $summary_revenue, 2 ),
        );

        return $statistic;
    }

    /**
     * Get number of expiring time passes for each week, week numbers determined by ticks parameter.
     *
     * @param $time_pass_id pass id | 0 or null for all time passes
     * @param $ticks   period in weeks
     *
     * @return array
     */
    public static function get_time_pass_expiry_by_weeks( $time_pass_id, $ticks ) {
        $history_model = new LaterPay_Model_Payment_History();
        $data          = array();
        $duration      = 0;

        // initialize array
        if ( ! $ticks ) {
            return $data;
        } else {
            $i = 0;
            while ( $i <= $ticks ) {
                $data[] = 0;
                $i++;
            }
        }

        if ( $time_pass_id ) {
            // get history for one given time pass
            $time_pass  = (array) self::get_time_pass_by_id( $time_pass_id );
            $duration   = self::get_time_pass_expiry_time( $time_pass );
            $history    = $history_model->get_time_pass_history( $time_pass_id );
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
                    $time_pass_id   = $hist->pass_id;
                    $time_pass      = (array) self::get_time_pass_by_id( $time_pass_id );
                    $expiry_date    = $start_date + self::get_time_pass_expiry_time( $time_pass );
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

                    if ( ! $hist->code ) {
                        $data[$key]++;
                    }
                }
            }
        }

        return $data;
    }

    /*
     * Get count of existing time passes.
     *
     * @return int count of time passes
     */
    public static function get_time_passes_count() {
        $model = new LaterPay_Model_TimePass();

        return $model->get_time_passes_count();
    }
}
