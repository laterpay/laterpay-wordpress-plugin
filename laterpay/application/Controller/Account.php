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
    public function add_account_links( $show, $css = null, $next = null, $forcelang = null ) {
        ?>
        <div class="lp_account-links"></div>
        <?php

        // TODO: define default CSS as constant and extend if statement to check for empty or != default CSS
        if ( empty( $css ) ) {
            // load some default styles, if no specific CSS has been provided
            wp_register_style(
                'laterpay-account-links',
                $this->config->get( 'css_url' ) . 'laterpay-account-links.css',
                array(),
                $this->config->get( 'version' )
            );
            wp_enqueue_style( 'laterpay-account-links' );
            $css = ''; // TODO: define default CSS as constant and apply it as default here
        }

        if ( empty( $next ) ) {
            // forward to current page after login by default
            $next = is_singular() ? get_permalink() : home_url();
        }

        if ( empty( $show ) ) {
            // render the login / logout link by default
            $show = 'l';
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
