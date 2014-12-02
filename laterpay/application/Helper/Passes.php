<?php

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
            'pass_id'           => '0',
            'duration'          => '1',
            'period'            => '1',
            'access_to'         => '0',
            'access_category'   => '',
            'price'             => '0.99',
            'revenue_model'     => 'ppu',
            'title'             => __( '24-Hour Pass', 'laterpay' ),
            'description'       => __( '24 hours access to all content on this website', 'laterpay' ),
        );

        if ( isset ( $key ) ) {
            if ( isset( $defaults[ $key ] ) ) {
                return $defaults[ $key ];
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
            if ( isset( $durations[ $key ] ) ) {
                return $durations[ $key ];
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
            if ( isset( $selected_array[ $key ] ) ) {
                return $selected_array[ $key ];
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
            if ( isset( $revenues[ $key ] ) ) {
                return $revenues[ $key ];
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
            if ( isset( $access_to[ $key ] ) ) {
                return $access_to[ $key ];
            }
        }

        return $access_to;
    }

    /**
     * Get short time pass description.
     *
     * @param  int $duration time pass duration
     * @param  int $period   time pass period
     * @param  int $access   time pass access
     *
     * @return string short time pass description
     */
    public static function get_description( $duration = null, $period = null, $access = null ) {
        if ( ! $duration ) {
            $duration = self::get_default_options( 'duration' );
        }
        if ( ! $period ) {
            $period = self::get_default_options( 'period' );
        }
        if ( ! $access ) {
            $access = self::get_default_options( 'access_to' );
        }
        if ( $period == 1 ) { // Day
            $period   = 0;
            $duration = $duration * 24;
        }

        $access_to_string = __( 'access to', 'laterpay' );

        $str = sprintf(
            '%d %s %s %s',
            $duration,
            self::get_period_options( $period ),
            $access_to_string,
            self::get_access_options( $access )
        );

        return strtolower( $str );
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
        $select_first  = true;

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

            case 'category':
                $elements      = self::get_wp_categories();
                $default_value = self::get_default_options( 'access_category' );
                break;

            default:
                return $options_html;
        }

        if ( $elements && is_array( $elements ) ) {
            $is_first = true;
            foreach ( $elements as $id => $name ) {
                // category value is different
                if ( $type == 'category' ) {
                    $id   = $name->term_id;
                    $name = $name->name;
                }

                // set option
                if ( ( $is_first && $select_first && ! $default_value ) || ( $id == $default_value ) ) {
                    $options_html .= '<option selected="selected" value="' . $id . '">' . $name. '</option>';
                } else {
                    $options_html .= '<option value="' . $id . '">' . $name . '</option>';
                }

                $is_first = false;
            }
        }

        return $options_html;
    }

    /**
     * Get wp categories.
     *
     * @param array $args query args for get_categories
     *
     * @return array $categories
     */
    protected static function get_wp_categories( $args = array() ) {
        $default_args = array(
            'hide_empty'    => false,
            'number'        => 10,
        );

        $args = wp_parse_args(
            $args,
            $default_args
        );

        $categories = get_categories( $args );

        return $categories;
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
        if ( ! $passes ) {
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

    public static function get_all_passes() {
        $model = new LaterPay_Model_Pass();

        return $model->get_all_passes();
    }

    /**
     * Get the LaterPay purchase link for a time pass.
     *
     * @param int  $pass_id
     * @param null $price   new price (voucher code)
     * @param null $link    url of page to redirect
     *
     * @return string url || empty string if something went wrong
     */
    public static function get_laterpay_purchase_link( $pass_id, $price = null, $link = null ) {
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
            'article_id'    => self::get_tokenized_pass( $pass_id ),
            'pricing'       => $currency . ( $price * 100 ),
            'expiry'        => '+' . self::get_pass_expiry_time( $pass ),
            'vat'           => laterpay_get_plugin_config()->get( 'currency.default_vat' ),
            'url'           => $url,
            'title'         => $pass['title'],
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

}
