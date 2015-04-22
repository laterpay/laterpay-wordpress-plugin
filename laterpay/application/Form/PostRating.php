<?php

/**
 * LaterPay post rating form class.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Form_PostRating extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_post_rate_purchased_content',
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
            'post_id',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'to_int',
                )
            )
        );

        $this->set_field(
            'rating_value',
            array(
                'validators' => array(
                    'is_int',
                    'cmp' => array(
                        array(
                            'gte' => 1,
                            'lte' => 5,
                        ),
                    ),
                ),
                'filters' => array(
                    'to_int',
                ),
            )
        );
    }
}

