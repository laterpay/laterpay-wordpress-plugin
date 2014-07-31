<?php

class LaterPay_Controller_Post_Column extends LaterPay_Controller_Abstract {

	/**
	 * Add custom columns to posts table.
	 *
	 * @param   array $columns
	 *
	 * @return  array $extended_columns
	 */
	public function add_columns_to_posts_table( $columns ) {
		$extended_columns   = array();
		$insert_after       = 'title';

		foreach ( $columns as $key => $val ) {
			$extended_columns[$key] = $val;
			if ( $key == $insert_after ) {
				$extended_columns['post_price']         = __( 'Price', 'laterpay' );
				$extended_columns['post_price_type']    = __( 'Price Type', 'laterpay' );
			}
		}

		return $extended_columns;
	}

	/**
	 * Populate custom columns in posts table with data.
	 *
	 * @wp-hook manage_post_posts_custom_column
	 *
	 * @param   string $column_name
	 * @param   int $post_id
	 *
	 * @return  void
	 */
	public function add_data_to_posts_table( $column_name, $post_id ) {
		if ( $column_name == 'post_price' ) {
			$price      = number_format( (float) LaterPay_Controller_Post_Content::get_post_price( $post_id ), 2 );
			$currency   = get_option( 'laterpay_currency' );

			if ( $price > 0 ) {
				echo "<strong>$price</strong> <span>$currency</span>";
			} else {
				echo '&mdash;';
			}

		} else if ( $column_name == 'post_price_type' ) {
			$post_price_type = get_post_meta( $post_id, 'Pricing Post Type', true );

			if ( $post_price_type !== '' ) {
				echo __($post_price_type, 'laterpay' );
			} else {
				echo '&mdash;';
			}

		}
	}


}
