<?php

class LaterPay_Controller_Account extends LaterPay_Controller_Abstract
{

    /**
     * Callback to add LaterPay account links.
     *
     * @wp-hook laterpay_account_links
     *
     * @return void
     */
    public function add_account_links( $show, $css = null, $next = null, $forcelang = null ) {
        ?>
        <div id="laterpay-account-links"></div>
        <?php

        if ( empty( $next ) ) {
            // current page by default
            $next = is_singular() ? get_permalink() : home_url();
        }

        if ( empty( $show ) ) {
            // default show value
            $show = 'l';
        }

        // create account links url with passed params
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );
        $links_url = $client->get_account_links_url( $show, $css, $next, $forcelang );
        ?>
        <script>
            var lpAccountLinksUrl = "<?php echo $links_url; ?>";
        </script>
        <?php
        wp_enqueue_script( 'laterpay-yui' );
        wp_enqueue_script( 'laterpay-account-links' );
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
            'laterpay-account-links',
            $this->config->get( 'js_url' ) . 'laterpay-account-links.js',
            NULL,
            $this->config->get( 'version' ),
            true
        );
    }
}