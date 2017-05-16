<?php

/**
 * LaterPay subscription helper.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Helper_Subscription
{

    const TOKEN = 'sub';

    /**
     * Get subscriptions default options.
     *
     * @param null $key option name
     *
     * @return mixed option value | array of options
     */
    public static function get_default_options( $key = null ) {

        $defaults = array(
            'id'              => '0',
            'duration'        => '1',
            'period'          => '3',
            'access_to'       => '0',
            'access_category' => '',
            'price'           => laterpay_get_plugin_config()->get( 'currency.sis_min' ),
            'title'           => __( '1 Month Subscription', 'laterpay' ),
            'description'     => __( '1 month access to all content on this website (cancellable anytime)', 'laterpay' ),
        );

        if ( isset( $key ) ) {
            if ( isset( $defaults[ $key ] ) ) {
                return $defaults[ $key ];
            }
        }

        return $defaults;
    }

    /**
     * Get short subscription description.
     *
     * @param  array  $subscription subscription data
     * @param  bool   $full_info need to display full info
     *
     * @return string short subscription description
     */
    public static function get_description( $subscription = array(), $full_info = false ) {
        $details  = array();
        $config   = laterpay_get_plugin_config();

        if ( ! $subscription ) {
            $time_pass['duration']  = self::get_default_options( 'duration' );
            $time_pass['period']    = self::get_default_options( 'period' );
            $time_pass['access_to'] = self::get_default_options( 'access_to' );
        }

        $currency = $config->get( 'currency.default' );

        $details['duration'] = $subscription['duration'] . ' ' .
            LaterPay_Helper_TimePass::get_period_options( $subscription['period'], $subscription['duration'] > 1 );
        $details['access']   = __( 'access to', 'laterpay' ) . ' ' .
            LaterPay_Helper_TimePass::get_access_options( $subscription['access_to'] );

        // also display category, price, and revenue model, if full_info flag is used
        if ( $full_info ) {
            if ( $subscription['access_to'] > 0 ) {
                $category_id = $subscription['access_category'];
                $details['category'] = '"' . get_the_category_by_ID( $category_id ) . '"';
            }

            $details['price']    = __( 'for', 'laterpay' ) . ' ' .
                LaterPay_Helper_View::format_number( $subscription['price'] ) .
                ' ' . strtoupper( $currency );
            $details['cancellable']  = '(cancellable anytime)';
        }

        return implode( ' ', $details );
    }

    /**
     * Get subscriptions select options by type.
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
                $elements      = LaterPay_Helper_TimePass::get_duration_options();
                $default_value = self::get_default_options( 'duration' );
                break;

            case 'period':
                $elements      = LaterPay_Helper_TimePass::get_period_options();
                $default_value = self::get_default_options( 'period' );
                break;

            case 'access':
                $elements      = LaterPay_Helper_TimePass::get_access_options();
                $default_value = self::get_default_options( 'access_to' );
                break;

            default:
                return $options_html;
        }

        if ( $elements && is_array( $elements ) ) {
            foreach ( $elements as $id => $name ) {
                if ( $id == $default_value ) {
                    $options_html .= '<option selected="selected" value="' . esc_attr( $id ) . '">' . laterpay_sanitize_output( $name ) . '</option>';
                } else {
                    $options_html .= '<option value="' . esc_attr( $id ) . '">' . laterpay_sanitize_output( $name ) . '</option>';
                }
            }
        }

        return $options_html;
    }

    /**
     * Get tokenized subscription id.
     *
     * @param string $id untokenized subscription id
     *
     * @return array $result
     */
    public static function get_tokenized_id( $id ) {
        return sprintf( '%s_%s', self::TOKEN , $id );
    }

    /**
     * Get untokenized subscription id.
     *
     * @param string $tokenized_id tokenized subscription id
     *
     * @return string|null pass id
     */
    public static function get_untokenized_id( $tokenized_id ) {
        list( $prefix, $id ) = array_pad( explode( '_', $tokenized_id ), 2, null );
        if ( $prefix === self::TOKEN ) {
            return $id;
        }

        return null;
    }

    /**
     * Get all tokenized subscription ids.
     *
     * @param null $subscriptions array of subscriptions
     *
     * @return array $result
     */
    public static function get_tokenized_ids( $subscriptions = null ) {
        if ( ! isset( $subscriptions ) ) {
            $model        = new LaterPay_Model_Subscription();
            $subscriptions = $model->get_all_subscriptions();
        }

        $result = array();
        foreach ( $subscriptions as $subscription ) {
            $result[] = self::get_tokenized_id( $subscription['id'] );
        }

        return $result;
    }

    /**
     * Get all active subscriptions.
     *
     * @return array of subscriptions
     */
    public static function get_active_subscriptions() {
        $model = new LaterPay_Model_Subscription();
        return $model->get_active_subscriptions();
    }

    /**
     * Get subscription data by id.
     *
     * @param  int  $id
     * @param  bool $ignore_deleted ignore deleted time passes
     *
     * @return array
     */
    public static function get_subscription_by_id( $id = null, $ignore_deleted = false ) {
        $model = new LaterPay_Model_Subscription();

        if ( $id ) {
            return $model->get_subscription( (int) $id, $ignore_deleted );
        }

        return array();
    }

    /**
     * Get the LaterPay purchase link for a subscription
     *
     * @param int  $id               subscription id
     * @param null $data             additional data
     *
     * @return string url || empty string if something went wrong
     */
    public static function get_subscription_purchase_link( $id, $data = null ) {
        $subscription_model = new LaterPay_Model_Subscription();

        $subscription = $subscription_model->get_subscription( $id );
        if ( empty( $subscription ) ) {
            return '';
        }

        if ( ! isset( $data ) ) {
            $data = array();
        }

        $config   = laterpay_get_plugin_config();
        $currency = $config->get( 'currency.default' );
        $price    = isset( $data['price'] ) ? $data['price'] : $subscription['price'];
        $link     = isset( $data['link'] ) ? $data['link'] : get_permalink();

        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );

        // parameters for LaterPay purchase form
        $params = array(
            'article_id' => self::get_tokenized_id( $id ),
            'sub_id'     => self::get_tokenized_id( $id ),
            'pricing'    => $currency . ( $price * 100 ),
            'period'     => self::get_expiry_time( $subscription ),
            'url'        => $link,
            'title'      => $subscription['title'],
        );

        // Subscription purchase
        return $client->get_subscription_url( $params );
    }

    /**
     * Get subscription expiry time.
     *
     * @param array $subscription
     *
     * @return $time expiry time
     */
    protected static function get_expiry_time( $subscription ) {
        switch ( $subscription['period'] ) {
            // hours
            case 0:
                $time = $subscription['duration'] * 60 * 60;
                break;

            // days
            case 1:
                $time = $subscription['duration'] * 60 * 60 * 24;
                break;

            // weeks
            case 2:
                $time = $subscription['duration'] * 60 * 60 * 24 * 7;
                break;

            // months
            case 3:
                $time = $subscription['duration'] * 60 * 60 * 24 * 31;
                break;

            // years
            case 4:
                $time = $subscription['duration'] * 60 * 60 * 24 * 365;
                break;

            default :
                $time = 0;
        }

        return $time;
    }

    /*
     * Get count of existing subscriptions.
     *
     * @return int count of subscriptions
     */
    public static function get_subscriptions_count() {
        $model = new LaterPay_Model_Subscription();
        return $model->get_subscriptions_count();
    }
}
