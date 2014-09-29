<?php

class LaterPay_Controller_Invoice extends LaterPay_Controller_Abstract
{

    /**
     * Callback to generate a LaterPay invoice indicator button within the theme that can be freely positioned.
     *
     *
     * @wp-hook laterpay_invoice_indicator
     *
     * @return void
     */
    public function the_invoice_indicator() {
        ?>
        <div id="laterpay-invoice-indicator"></div>
        <?php

        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-invoice-indicator' );
    }

    /**
     * Load LaterPay Javascript libraries.
     *
     * @wp-hook wp_enqueue_scripts
     *
     * @return void
     */
    public function add_frontend_scripts() {
        wp_register_script(
            'laterpay-yui',
            $this->config->get( 'laterpay_yui_js' ),
            array(),
            null,
            false // LaterPay YUI scripts *must* be loaded asynchronously from the HEAD
        );
        wp_register_script(
            'laterpay-invoice-indicator',
            $this->config->get( 'js_url' ) . 'laterpay-invoice-indicator.js',
            NULL,
            $this->config->get( 'version' ),
            true
        );

        // pass localized strings and variables to script
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );
        $balance_url = $client->get_controls_balance_url();

        wp_localize_script(
            'laterpay-invoice-indicator',
            'lpInvoiceIndicatorVars',
            array(
                'lpBalanceUrl'  => $balance_url
            )
        );
    }
}
