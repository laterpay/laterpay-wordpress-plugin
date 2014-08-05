<?php

class LaterPay_Controller_Tracking extends LaterPay_Controller_Abstract
{

	/**
	 * Track unique visitors.
	 *
	 * @return  void
	 */
	public function add_unique_visitors_tracking() {
		if ( ! $this->config->get( 'logging.access_logging_enabled' ) ) {
			return;
		}
        if ( ! is_singular() ) {
            return;
        }
        $post_id = get_the_ID();
        if ( ! $post_id ) {
            return;
        }
		LaterPay_Helper_Statistics::track( $post_id );
	}

}
