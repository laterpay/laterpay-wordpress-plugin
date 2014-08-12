<?php

class LaterPay_Controller_Admin_GetStarted extends LaterPay_Controller_Abstract
{

	/**
	 * @see LaterPay_Controller_Abstract::load_assets()
	 */
	public function load_assets() {
		parent::load_assets();

		// load page-specific JS
		wp_register_script(
			'laterpay-backend-getstarted',
			$this->config->js_url . 'laterpay-backend-getStarted.js',
			array( 'jquery' ),
			$this->config->version,
			true
		);
		wp_enqueue_script( 'laterpay-backend-getstarted' );

		// pass localized strings and variables to script
		wp_localize_script(
			'laterpay-backend-getstarted',
			'lpVars',
			array(
				'locale'                        => get_locale(),
				'i18nOutsideAllowedPriceRange'  => __( 'The price you tried to set is outside the allowed range of 0 or 0.05-5.00.', 'laterpay' ),
				'i18nInvalidMerchantId'         => __( 'The Merchant ID you entered is not a valid LaterPay Sandbox Merchant ID!', 'laterpay' ),
				'i18nInvalidApiKey'             => __( 'The API key you entered is not a valid LaterPay Sandbox API key!', 'laterpay' ),
				'i18nTabsDisabled'             	=> __( 'Please fill in this page before proceeding to other settings pages.', 'laterpay' ),
			)
		);
	}

	/**
	 * @see LaterPay_Controller_Abstract::render_page()
	 */
	public function render_page() {
		$this->load_assets();

		$currency_model = new LaterPay_Model_Currency();
		$currencies  	= $currency_model->get_currencies();

		$this->assign( 'global_default_price',   LaterPay_Helper_View::format_number( (float) $this->config->get( 'currency.default_price' ), 2 ) );
		$this->assign( 'currencies',             $currencies );
		$this->assign( 'top_nav',                $this->get_menu() );
		$this->assign( 'admin_menu',             LaterPay_Helper_View::get_admin_menu() );

		$this->render( 'backend/getstarted' );
	}

	/**
	 * Process Ajax requests from get started tab.
	 *
	 * @return void
	 */
	public static function process_ajax_requests() {
		if ( isset( $_POST['get_started'] ) ) {
			// check for required capabilities to perform action
			if ( ! current_user_can( 'edit_plugins' ) ) {
				wp_send_json(
					array(
						'success' => false,
						'message' => __( "You don't have sufficient user capabilities to do this.", 'laterpay' )
					)
				);
			}
			if ( function_exists( 'check_admin_referer' ) ) {
				check_admin_referer( 'laterpay_form' );
			}

			// validate user inputs
			$sandbox_merchant_id 	= wp_strip_all_tags( $_POST['get_started']['laterpay_sandbox_merchant_id'], true );
			$sandbox_api_key 		= wp_strip_all_tags( $_POST['get_started']['laterpay_sandbox_api_key'], true );
			// use the provided default API credentials, if one of the user submitted values is invalid
			if (
				! LaterPay_Controller_Admin_Account::is_valid_merchant_id( $sandbox_merchant_id ) ||
				! LaterPay_Controller_Admin_Account::is_valid_api_key( $sandbox_api_key )
			) {
				$sandbox_merchant_id 	= $config->get( 'api.sandbox_merchant_id' );
				$sandbox_api_key 		= $config->get( 'api.sandbox_api_key' );
			}
			$global_default_price 	= str_replace( ',', '.', wp_strip_all_tags( $_POST['get_started']['laterpay_global_price'], true ) );
			$currency 				= $_POST['get_started']['laterpay_currency'];

			// save initial settings
			update_option( 'laterpay_sandbox_merchant_id',  $sandbox_merchant_id );
			update_option( 'laterpay_sandbox_api_key',      $sandbox_api_key );
			update_option( 'laterpay_global_price',         $global_default_price );
			update_option( 'laterpay_currency',             $currency );
			update_option( 'laterpay_plugin_is_activated',  '1' );

			// automatically dismiss pointer to LaterPay plugin after saving the initial settings
			$dismissed_pointers = explode( ',', (string) get_user_meta( get_current_user_id(), 'dismissed_wp_pointers', true ) );

			if ( ! in_array( LaterPay_Controller_Admin::ADMIN_MENU_POINTER, $dismissed_pointers ) ) {
				update_user_meta( $current_user_id, 'dismissed_wp_pointers', LaterPay_Controller_Admin::ADMIN_MENU_POINTER );
			}

			wp_send_json( array( 'success' => true ) );
		} else {
			wp_send_json(
				array(
					'success' => false,
					'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' )
				)
			);
		}
	}

}
