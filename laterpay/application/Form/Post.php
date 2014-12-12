<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_Post extends LaterPay_Form_Abstract
{

    /**
     * Implementation of abstract method.
     *
     * @return void
     */
    public function init() {
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
                ),
            )
        );

        $this->set_field(
            'laterpay_pricing_post_content_box_nonce',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'ne' => null,
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'laterpay_teaser_content_box_nonce',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'ne' => null,
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'post-price',
            array(
                'validators' => array(
                    'is_float',
                    'cmp' => array(
                        array(
                            'lte' => 149.99,
                            'gte' => 0.05,
                        ),
                        array(
                            'eq'  => 0.00,
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
                'can_be_null' => true,
            )
        );

        $this->set_field(
            'post_revenue_model',
            array(
                'validators' => array(
                    'is_string',
                    'in_array' => array( 'ppu', 'sis' ),
                    'depends' => array(
                        array(
                            'field' => 'post-price',
                            'value' => 'sis',
                            'conditions' => array(
                                'cmp' => array(
                                    array(
                                        'lte' => LaterPay_Helper_Pricing::sis_max,
                                        'gte' => LaterPay_Helper_Pricing::price_ppusis_end,
                                    ),
                                    array(
                                        'eq' => null,
                                    ),
                                ),
                            ),
                        ),
                        array(
                            'field' => 'post-price',
                            'value' => 'ppu',
                            'conditions' => array(
                                'cmp' => array(
                                    array(
                                        'lte' => LaterPay_Helper_Pricing::ppusis_max,
                                        'gte' => LaterPay_Helper_Pricing::ppu_min,
                                    ),
                                    array(
                                        'eq'  => 0.00,
                                    ),
                                    array(
                                        'eq' => null,
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                ),
                'can_be_null' => true,
            )
        );

        $this->set_field(
            'post_price_type',
            array(
                'validators' => array(
                    'is_string',
                    'in_array' => array(
                        LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE,
                        LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE,
                        LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE,
                        LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE,
                    )
                ),
                'filters'    => array(
                    'to_string',
                    'unslash',
                ),
                'can_be_null' => true,
            )
        );

        $this->set_field(
            'laterpay_post_teaser',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters'    => array(
                    'to_string',
                )
            )
        );

        $this->set_field(
            'start_price',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'end_price',
            array(
                'validators' => array(
                    'is_string',
                ),
                'filters' => array(
                    'to_string',
                    'unslash',
                )
            )
        );

        $this->set_field(
            'change_start_price_after_days',
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
            'transitional_period_end_after_days',
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
            'reach_end_price_after_days',
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
            'post_default_category',
            array(
                'validators' => array(
                    'is_int',
                ),
                'filters' => array(
                    'unslash',
                    'to_int',
                ),
                'can_be_null' => true,
            )
        );
    }
}

