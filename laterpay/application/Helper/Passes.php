<?php

class LaterPay_Helper_Passes
{

     /**
     * Default time range. Used during passes creation.
     *
     * @var string
     */
    public static $defaults = array(
        'pass_id'           => '0',
        'status'            => 'active',
        'valid_duration'    => '1',
        'valid_period'      => 'Day',
        'access_to'         => 'All content',
        'access_category'   => 'First category',
        'price'             => 0.99,
        'revenue_model'     => 'later', // FIXME: value should be ppu or sis, label should be 'later' or 'immediately'
        'title'             => 'Title',
        'title_color'       => '#3f3f3f',
        'description'       => '',
        'description_color' => '#3f3f3f',
        'background_path'   => '',
        'background_color'  => '#fff',
    );

    /**
     * @var array
     */
    public static $valid_durations = array(
        1,
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
    public static $valid_periods = array(
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
        'later' => '',
        'immediately',
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
     * @var array
     */
    public static $access_detail = array(
        'First category',
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
     * @param  [type] $term   [description]
     * @param  [type] $period [description]
     * @param  [type] $access [description]
     *
     * @return [type]         [description]
     */
    public static function get_description( $term = null, $period = null, $access = null ) {
        if ( ! $term ) {
            $term = self::$defaults['valid_duration'];
        }
        if ( ! $period ) {
            $period = self::$defaults['valid_period'];
        }
        if ( ! $access ) {
            $access = self::$defaults['access_to'];
        }
        if ( $period == 'Day' ) {
            $period = 'Hour';
            $term   = $term * 24;
        }

        $str = strtolower( $term . ' ' . $period . 's access to ' . $access );

        return $str;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return [type] [description]
     */
    public static function get_select_durations() {
        $options_html = '';

        foreach ( self::$valid_durations as $key ) {
            if ( $key == self::$defaults['valid_duration'] ) {
                $options_html .= "<option selected value='$key'>$key</option>";
            } else {
                $options_html .= "<option value='$key'>$key</option>";
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

        foreach ( self::$valid_periods as $key ) {
            if ( $key == self::$defaults['valid_period'] ) {
                $options_html .= "<option selected value='$key'>$key</option>";
            } else {
                $options_html .= "<option value='$key'>$key</option>";
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

        foreach ( self::$access_to as $key ) {
            if ( $key == self::$defaults['access_to'] ) {
                $options_html .= "<option selected value='$key'>$key</option>";
            } else {
                $options_html .= "<option value='$key'>$key</option>";
            }
        }

        return $options_html;
    }

    /**
     * FIXME: #196 add comment
     *
     * @return [type] [description]
     */
    public static function get_select_access_detail() {
        $options_html = '';

        foreach ( self::$access_detail as $key ) {
            if ( $key == self::$defaults['access_category'] ) {
                $options_html .= "<option selected value='$key'>$key</option>";
            } else {
                $options_html .= "<option value='$key'>$key</option>";
            }
        }

        return $options_html;
    }

}
