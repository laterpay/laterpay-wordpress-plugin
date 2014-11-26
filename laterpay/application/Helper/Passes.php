<?php

class LaterPay_Helper_Passes
{

    const PASS_TOKEN    = 'tlp';

     /**
     * Default time range. Used during passes creation.
     *
     * @var string
     */
    public static $defaults = array(
        'pass_id'           => '0',
        'duration'          => '1',
        'period'            => '1',
        'access_to'         => '0',
        'access_category'   => '',
        'price'             => 0.99,
        'revenue_model'     => 'ppu',
        'title'             => '24-Hour Pass',
        'title_color'       => '#444',
        'description'       => '',
        'description_color' => '#444',
        'background_path'   => '',
        'background_color'  => '#fff',
    );

    /**
     * @var array
     */
    public static $durations = array(
        1 => 1,
        2,
        3,
        4,
        5,
        6,
        7,
        8,
        9,
        10,
        12,
        13,
        14,
        15,
        16,
        17,
        18,
        19,
        20,
        21,
        22,
        23,
        24
    );

    /**
     * @var array
     */
    public static $periods = array(
        'Hour',
        'Day',
        'Week',
        'Month',
        'Year',
    );

    /**
     * @var array
     */
    public static $revenue_model = array(
        'ppu' => 'later',
        'sis' => 'immediately',
    );

    /**
     * @var array
     */
    public static $access_to = array(
        'All content',
        'All content except for category',
        'All content in category',
    );

    /**
     * FIXME: #196 add comment
     *
     * @param  [type] $k [description]
     *
     * @return [type]    [description]
     */
    public static function get_defaults( $k ) {
        if ( isset( self::$defaults[$k] ) ) {
            return self::$defaults[$k];
        }
    }

    /**
     * FIXME: #196 add comment
     *
     * @param  int $duration [description]
     * @param  int $period   [description]
     * @param  int $access   [description]
     *
     * @return string        [description]
     */
    public static function get_description( $duration = null, $period = null, $access = null ) {
        if ( ! $duration ) {
            $duration = self::$defaults['duration'];
        }
        if ( ! $period ) {
            $period = self::$defaults['period'];
        }
        if ( ! $access ) {
            $access = self::$defaults['access_to'];
        }
        if ( $period == 1 ) { // Day
            $period   = 0;
            $duration = $duration * 24;
        }

        $str = strtolower( sprintf(
                __( '%d %s access to %s', 'laterpay' ),
                $duration,
                __( self::$periods[$period] . 's', 'laterpay' ),
                __( self::$access_to[$access], 'laterpay' )
        ));

        return $str;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return [type] [description]
     */
    public static function get_select_durations() {
        $options_html = '';

        foreach ( self::$durations as $id => $name ) {
            if ( $id == self::$defaults['duration'] ) {
                $options_html .= "<option selected value='$id'>" . __( $name, 'laterpay' ) . "</option>";
            } else {
                $options_html .= "<option value='$id'>" . __( $name, 'laterpay' ) . "</option>";
            }
        }

        return $options_html;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return [type] [description]
     */
    public static function get_select_periods() {
        $options_html = '';

        foreach ( self::$periods as $id => $name ) {
            if ( $id == self::$defaults['period'] ) {
                $options_html .= "<option selected value='$id'>" . __( $name, 'laterpay' ) . "</option>";
            } else {
                $options_html .= "<option value='$id'>" . __( $name, 'laterpay' ) . "</option>";
            }
        }

        return $options_html;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return [type] [description]
     */
    public static function get_select_access_to() {
        $options_html = '';

        foreach ( self::$access_to as $id => $name ) {
            if ( $id == self::$defaults['access_to'] ) {
                $options_html .= "<option selected value='$id'>" . __( $name, 'laterpay' ) . "</option>";
            } else {
                $options_html .= "<option value='$id'>" . __( $name, 'laterpay' ) . "</option>";
            }
        }

        return $options_html;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return [type] [description]
     */
    public static function get_select_access_categories() {
        $options_html = '';
        $categories = self::get_wp_categories(array());
        foreach ( $categories as $category ) {
            if ( $category->term_id == self::$defaults['access_category'] ) {
                $options_html .= "<option selected value='{$category->term_id}'>{$category->name}</option>";
            } else {
                $options_html .= "<option value='{$category->term_id}'>{$category->name}</option>";
            }
        }

        return $options_html;
    }

    /**
     * Get wp categories
     *
     * @param array $args query args for get_categories
     *
     * @return array $categories
     */
    protected static function get_wp_categories( $args ) {
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
     * Get all tokenized pass ids.
     *
     * @param null   $passes array of passes
     *
     * @return array $result
     */
    public static function get_tokenized_passes( $passes = null ) {
        if ( ! $passes ) {
            $passes = self::get_all_passes();
        }
        $result = array();
        foreach ($passes as $pass) {
            $result[] = self::get_tokenized_pass($pass->pass_id);
        }

        return $result;
    }

    /**
     * Get time limited passes for specified post
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
            foreach( $post_categories as $category ) {
                $post_category_ids[] = $category->term_id;
            }

            // get post passes
            $passes_list = (array) $model->get_post_passes( $post_category_ids );
        } else {
            $passes_list = (array) $model->get_post_passes();
        }

        // correct result if we have passes purchased
        if ( $passes_with_access ) {
            // check if user has access to the current post with pass
            $has_access = false;
            foreach ( $passes_list as $pass ) {
                if ( in_array( $pass->pass_id, $passes_with_access ) ) {
                    $has_access = true;
                    break;
                }
            }

            if ( $has_access ) {
                // categories with access ( type 2 )
                $covered_categories  = array(
                    'included' => array(),
                    'excluded' => null,
                );
                // excluded categories ( type 1 )
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

                // if we have full data access except specific categories
                if ( $excluded_categories ) {
                    foreach ( $excluded_categories as $excluded_category_id ) {
                        // search for excluded category in covered categories
                        $has_covered_category = array_search( $excluded_category_id, $covered_categories );
                        if ( $has_covered_category !== false ) {
                            return array();
                        } else {
                            //  if more than 1 passes with excluded category purchased, if its values not mached, then
                            //  all categories covered
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
     * @param int $pass_id
     *
     * @return string url || empty string if something went wrong
     */
    public static function get_laterpay_purchase_link( $pass_id ) {
        $time_pass_model = new LaterPay_Model_Pass();

        $pass = (array) $time_pass_model->get_pass_data( $pass_id );
        if ( empty($pass) ) {
            return '';
        }

        $currency       = get_option( 'laterpay_currency' );
        $price          = $pass['price'];
        $revenue_model  = $pass['revenue_model'];

        $currency_model = new LaterPay_Model_Currency();
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
                $client_options['cp_key'],
                $client_options['api_key'],
                $client_options['api_root'],
                $client_options['web_root'],
                $client_options['token_name']
        );

        $url    = get_permalink();

        // parameters for LaterPay purchase form
        $params = array(
            'article_id'    => self::get_tokenized_pass($pass_id),
            'pricing'       => $currency . ( $price * 100 ),
            'expiry'        => '+' . self::getPassExpiryTime($pass),
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
     * FIXME: #196 add documentation
     *
     * @param array $pass
     */
    protected static function getPassExpiryTime( $pass ) {
        $timestamp  = time();
        $time       = 0;

        switch ( $pass['period'] ) {
            // months
            case 3:
                $time = $pass['duration'] * 60 * 60 * 24 * 31;
                break;

            // years
            case 4:
                $time = $pass['duration'] * 60 * 60 * 24 * 365;
                break;

            default :
                $period = self::$periods[$pass['period']];
                if ( $pass['duration'] > 1 ) {
                    $period .= 's';
                }
                $time = strtotime( strtolower( '+' . $pass['duration'] . ' ' . $period ) ) - $timestamp;
        }

        return $time;
    }
}
