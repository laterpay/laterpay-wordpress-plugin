<?php

/**
 * LaterPay hide free posts form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Form_HideFreePosts extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
        $this->set_field(
            'form',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'free_posts_visibility',
                        ),
                    ),
                )
            )
        );

        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_appearance',
                        ),
                    ),
                )
            )
        );

        $this->set_field(
            '_wpnonce',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'ne' => null,
                        ),
                    ),
                )
            )
        );

        $this->set_field(
            'hide_free_posts',
            array(
                'validators' => array(
                    'is_string',
                    'in_array' => array( 'on' ),
                ),
                'filters' => array(
                    'to_string',
                ),
                'can_be_null' => true,
            )
        );
    }
}
