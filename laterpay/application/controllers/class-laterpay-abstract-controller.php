<?php

class LaterPay_Abstract_Controller
{

    /**
     * Variables for substitution in templates
     */
    public $variables = array();

    public function load_assets() {}

    /**
     * Render HTML file
     *
     * @param string $file file to get HTML string
     *
     * @access public
     */
    public function render( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        require_once LATERPAY_GLOBAL_PATH . "application/views/$file.php";
    }

    /**
     * Assign variable for substitution in templates
     *
     * @param string $variable name variable to assign
     * @param mixed  $value    value variable for assign
     *
     * @access public
     */
    public function assign( $variable, $value ) {
        $this->variables[$variable] = $value;
    }

    /**
     * Get HTML from file
     *
     * @param string $file file to get HTML string
     * @access public
     *
     * @return string html string
     */
    public function get_text_view( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        ob_start();
        include LATERPAY_GLOBAL_PATH . "application/views/$file.php";
        $thread = ob_get_contents();
        ob_end_clean();
        $html = $thread;

        return $html;
    }

    public function get_menu( $file = null ) {
        if ( empty( $file ) ) {
            $file = 'partials/admin-menu';
        }
        $activated      = get_option( 'laterpay_plugin_is_activated', '' );
        $current_page   = isset( $_GET['page'] ) ? $_GET['page'] : LaterPay_View_Helper::$pluginPage;
        $menu           = LaterPay_View_Helper::$admin_menu;
        if ( $activated ) {
            unset( $menu['get_started'] );
        }
        $this->assign( 'menu',         $menu );
        $this->assign( 'current_page', $current_page );
        $this->assign( 'plugin_page',  LaterPay_View_Helper::$pluginPage );
        $this->assign( 'activated',    $activated );

        return $this->get_text_view( $file );
    }

}
