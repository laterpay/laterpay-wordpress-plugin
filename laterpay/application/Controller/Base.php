<?php

/**
 * LaterPay base controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Base extends LaterPay_Core_View implements LaterPay_Core_Event_SubscriberInterface
{
    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array();
    }

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_shared_events()
     */
    public static function get_shared_events() {
        return array();
    }

    /**
     * Contains the logger instance.
     *
     * @var LaterPay_Core_Logger
     */
    protected $logger;

    /**
     * @param LaterPay_Model_Config $config
     *
     * @return LaterPay_Core_View
     */
    public function __construct( $config = null ) {
        $this->logger = laterpay_get_logger();
        parent::__construct( $config );
    }
}
