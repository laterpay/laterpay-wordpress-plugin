<?php

class LaterPay_Controller_Abstract
{

    /**
     * Variables for substitution in templates.
     *
     * @var array
     */
    public $variables = array();

	/**
	 * Contains all settings for our plugin
	 * @var LaterPay_Model_Config
	 */
	protected $config;

	/**
	 * @param   LaterPay_Model_Config $config
	 * @return  LaterPay_Controller_Abstract
	 */
	public function __construct( LaterPay_Model_Config $config ) {
		$this->config = $config;
	}

	/**
	 * Load all assets on boot-up.
     *
	 * @return  void
	 */
	public function load_assets() {}

    /**
     * Render HTML file.
     *
     * @param   string $file file to get HTML string
     *
     * @return  void
     */
    public function render( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        require_once LATERPAY_GLOBAL_PATH . "views/$file.php";
    }

    /**
     * Assign variable for substitution in templates.
     *
     * @param string $variable name variable to assign
     * @param mixed  $value    value variable for assign
     *
     * @return void
     */
    public function assign( $variable, $value ) {
        $this->variables[$variable] = $value;
    }

    /**
     * Get HTML from file.
     *
     * @param   string $file file to get HTML string
     *
     * @return  string $html html output as string
     */
    public function get_text_view( $file ) {
        foreach ( $this->variables as $key => $value ) {
            ${$key} = $value;
        }
        ob_start();
        include LATERPAY_GLOBAL_PATH . "views/$file.php";
        $thread = ob_get_contents();
        ob_end_clean();
        $html = $thread;

        return $html;
    }

	/**
     * Render the navigation for the plugin backend.
     *
	 * @param   string $file
     *
	 * @return  string $html
	 */
	public function get_menu( $file = null ) {
        if ( empty( $file ) ) {
            $file = 'backend/partials/tabs/menu';
        }
        $activated      = get_option( 'laterpay_plugin_is_activated', '' );
        $current_page   = isset( $_GET['page'] ) ? $_GET['page'] : LaterPay_Helper_View::$pluginPage;
        $menu           = LaterPay_Helper_View::get_admin_menu();
        if ( $activated ) {
            unset( $menu['get_started'] );
        }
        $this->assign( 'menu',         $menu );
        $this->assign( 'current_page', $current_page );
        $this->assign( 'plugin_page',  LaterPay_Helper_View::$pluginPage );
        $this->assign( 'activated',    $activated );

        return $this->get_text_view( $file );
    }

}
