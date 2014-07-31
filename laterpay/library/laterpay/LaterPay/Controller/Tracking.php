<?php

class LaterPay_Controller_Tracking extends LaterPay_Controller_Abstract {

	/**
	 * Track unique visitors.
	 * @return  void
	 */
	public function add_unique_visitors_tracking() {
		if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
			return;
		}
		$url    = LaterPay_Helper_Statistics::get_full_url( $_SERVER );
		$postid = url_to_postid($url);
		LaterPay_Helper_Statistics::track($postid);
	}

}