<?php

class LaterPay_Helper_Passes
{

    const PASS_TOKEN = 'tlp';

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
        'title_color'       => '#3f3f3f',
        'description'       => '',
        'description_color' => '#3f3f3f',
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
        'All content except for',
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
                __('%d %s access to %s', 'laterpay'),
                $duration,
                __(self::$periods[$period] . 's', 'laterpay'),
                __(self::$access_to[$access], 'laterpay')
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
                $options_html .= "<option selected value='$id'>" . __($name, 'laterpay') . "</option>";
            } else {
                $options_html .= "<option value='$id'>" . __($name, 'laterpay') . "</option>";
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
                $options_html .= "<option selected value='$id'>" . __($name, 'laterpay') . "</option>";
            } else {
                $options_html .= "<option value='$id'>" . __($name, 'laterpay') . "</option>";
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
                $options_html .= "<option selected value='$id'>" . __($name, 'laterpay') . "</option>";
            } else {
                $options_html .= "<option value='$id'>" . __($name, 'laterpay') . "</option>";
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
     * Get WP categories
     *
     * @return array $result
     */
    public static function get_tokenized_passes() {
        $model = new LaterPay_Model_Pass();
        $passes = $model->get_all_passes();
        $result = array();
        foreach ($passes as $pass) {
            $result[] = sprintf('%s_%s', self::PASS_TOKEN , $pass->pass_id);
        }

        return $result;
    }

    /**
     * Get time limited passes for specified post
     * FIXME: #196 get only required passes
     *
     * @param int $post_id post ID
     * @return array $passes_list
     */
    public static function get_time_passes_list_for_the_post( $post_id ) {
        $model = new LaterPay_Model_Pass();
        $passes_list = $model->get_all_passes();

        return $passes_list;
    }

}
