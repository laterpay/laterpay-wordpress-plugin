<?php

/**
 * LaterPay dynamic pricing data form
 */
class LaterPay_Form_DynamicPricingData extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method
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
                            'eq' => 'laterpay_get_dynamic_pricing_data',
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'post_id',
            array(
                'validators' => array(
                    'is_int',
                    'post_exist',
                ),
                'filters' => array(
                    'to_int',
                ),
            )
        );

        $this->set_field(
            'post_price',
            array(
                'validators' => array(
                    'is_float',
                    'cmp' => array(
                        array(
                            'lte' => 149.99,
                            'gte' => 0.05,
                        ),
                        array(
                            'eq' => 0.00,
                        ),
                    ),
                ),
                'filters' => array(
                    'replace' => array(
                        'type'    => 'str_replace',
                        'search'  => ',',
                        'replace' => '.',
                    ),
                    'format_num' => 2,
                    'to_float',
                ),
            )
        );
    }
}

