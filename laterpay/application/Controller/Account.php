<?php

class LaterPay_Controller_Account extends LaterPay_Controller_Abstract
{
    /**
     * Callback to render LaterPay account links by making an API request to /controls/links.
     * (see https://laterpay.net/developers/docs/inpage-api#GET/controls/links)
     *
     * @wp-hook laterpay_account_links
     *
     * @param $show         'show' attribute for the API request as documented in the LaterPay API docs
     * @param $css          'css' attribute for the API request as documented in the LaterPay API docs
     * @param $next         'next' attribute for the API request as documented in the LaterPay API docs
     * @param $forcelang    'forcelang' attribute for the API request as documented in the LaterPay API docs
     *
     * @return void
     */
    public function render_account_links( $css = null, $forcelang = null, $show = null, $next = null ) {
        ?>
        <div class="lp_account-links"></div>
        <?php

        if ( empty( $css ) ) {
            // use laterpay-account-links to style the login / logout links by default
            $css = $this->config->get( 'css_url' ) . 'laterpay-account-links.css';
        }

        if ( empty( $next ) ) {
            // forward to current page after login by default
            $next = is_singular() ? get_permalink() : home_url();
        }

        if ( empty( $show ) ) {
            // render the login / logout link with greeting by default
            $show = 'lg';
        }

        // create account links URL with passed params
        $client_options = LaterPay_Helper_Config::get_php_client_options();
        $client = new LaterPay_Client(
            $client_options['cp_key'],
            $client_options['api_key'],
            $client_options['api_root'],
            $client_options['web_root'],
            $client_options['token_name']
        );
        $links_url = $client->get_account_links_url( $show, $css, $next, $forcelang );
        // get merchant id
        $is_live = get_option( 'laterpay_plugin_is_in_live_mode' );
        $merchant_id = $is_live ? get_option( 'laterpay_live_merchant_id' ) : get_option( 'laterpay_sandbox_merchant_id' );
        ?>
        <script>
            if (lpAccountLinksUrl === undefined) {
                var lpAccountLinksUrl = "<?php echo $links_url; ?>";
            }
            if (lpAccountNextUrl === undefined) {
                var lpAccountNextUrl = "<?php echo urlencode( $next ); ?>";
            }
            if (lpMerchantId === undefined) {
                var lpMerchantId = "<?php echo $merchant_id; ?>";
            }
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
