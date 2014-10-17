<?php

/**
 * LaterPay plugin mode form class
 */
class LaterPay_Form_BulkPrice extends LaterPay_Form_Abstract
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
                            'like' => 'bulk_price_form',
                        ),
                    ),
                ),
            )
        );

        $this->set_field(
            'action',
            array(
                'validators' => array(
                    'is_string',
                    'cmp' => array(
                        array(
                            'eq' => 'laterpay_pricing',
                        ),
                    ),
                ),
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
                ),
            )
        );

        $this->set_field(
            'bulk_action',
            array(

            )
        );

        $this->set_field(
            'bulk_selector',
            array(

            )
        );

        $this->set_field(
            'bulk_category',
            array(

            )
        );

        $this->set_field(
            'bulk_price',
            array(

            )
        );

        $this->set_field(
            'bulk_currency',
            array(

            )
        );
    }
}