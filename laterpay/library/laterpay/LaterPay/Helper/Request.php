<?php

class LaterPay_Helper_Request {

	/**
	 * Check if the current request is an Ajax request.
     *
	 * @return bool
	 */
	public static function is_ajax() {
        return ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) == 'xmlhttprequest';
    }

}