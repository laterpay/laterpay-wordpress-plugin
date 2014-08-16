<?php

class LaterPay_Controller_Install extends LaterPay_Controller_Abstract {

	/**
	 * Render admin notices if requirements are not fulfilled.
	 *
	 * @wp-hook admin_notices
	 *
	 * @return  void
	 */
	public function render_requirements_notices() {
        $notices = $this->check_requirements();
		if ( count( $notices ) > 0 ) {
			$out = join( "\n", $notices );
            echo '<div class="error">' . $out . '</div>';
		}
	}

    /**
     * Check plugin requirements. Deactivate plugin and return notices, if requirements are not fulfilled.
     *
     * @global string $wp_version
     *
     * @return array $notices
     */
    public function check_requirements() {
        global $wp_version;

		$installed_php_version          = phpversion();
		$installed_wp_version           = $wp_version;
		$required_php_version           = '5.2.4';
		$required_wp_version            = '3.5.2';
		$installed_php_is_compatible    = version_compare( $installed_php_version, $required_php_version, '>=' );
		$installed_wp_is_compatible     = version_compare( $installed_wp_version, $required_wp_version, '>=' );

		$notices = array();
		$template = __( '<p>LaterPay: Your server <strong>does not</strong> meet the minimum requirement of %s version %s or higher. You are running %s version %s.</p>', 'laterpay' );

		// check PHP compatibility
		if ( ! $installed_php_is_compatible ) {
			$notices[] = sprintf( $template, 'PHP', $required_php_version, 'PHP', $installed_php_version );
		}

		// check WordPress compatibility
		if ( ! $installed_wp_is_compatible ) {
			$notices[] = sprintf( $template, 'Wordpress', $required_wp_version, 'Wordpress', $installed_wp_version );
		}

        // deactivate plugin if requirements are not fulfilled
		if ( count( $notices ) > 0 ) {
            // suppress 'Plugin activated' notice
            unset( $_GET['activate'] );
			deactivate_plugins( $this->config->plugin_base_name );
			$notices[] = __( 'The LaterPay plugin could not be installed. Please fix the reported issues and try again.', 'laterpay' );
        }

        return $notices;
    }

    /**
	 * Compare plugin version with latest version and perform an update, if required.
	 *
	 * @wp-hook plugins_loaded
     *
	 * @return void
	 */
	public function check_for_updates() {
		$current_version = get_option('laterpay_version');
		if ( version_compare( $current_version, $this->config->version, '!=' ) ) {
			$this->install();
		}
	}

    /**
     * Update the existing postmeta meta_keys when current_version < 0.9.7
     *
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_meta_keys() {
        global $wpdb;

        $current_version = get_option('laterpay_version');
        if ( version_compare( $current_version, '0.9.7', '>=' ) ) {
            return;
        }

        // map old values to new ones
        $meta_key_mapping = array(
            'Teaser content'    => 'laterpay_post_teaser',
            'Pricing Post'      => 'laterpay_post_pricing',
            'Pricing Post Type' => 'laterpay_post_pricing_type'
        );

        $sql = "UPDATE " . $wpdb->postmeta . " SET meta_key = '%s' WHERE meta_key = '%s'";

        foreach( $meta_key_mapping as $before => $after ) {
            $prepared_sql = $wpdb->prepare( $sql, array( $after, $before ) );
            $wpdb->query( $prepared_sql );
        }
    }

    /**
     * Migrate old postmeta data to a single postmeta array
     *
     * @wp-hook get_post_metadata
     *
     * @param   null $return
     * @param   int $post_id        the current post_id
     * @param   string $meta_key    the meta_key
     *
     * @return  null $return
     */
    public function migrate_pricing_post_meta( $return, $post_id, $meta_key ) {
        // migrate the pricing postmeta to an array
        if ( $meta_key === 'laterpay_post_prices' ) {
            $meta_migration_mapping = array(
                'laterpay_post_pricing'                         => 'price',
                'laterpay_post_default_category'                => 'category_id',
                'laterpay_post_pricing_type'                    => 'type',
                'laterpay_start_price'                          => 'start_price',
                'laterpay_end_price'                            => 'end_price',
                'laterpay_change_start_price_after_days'        => 'change_start_price_after_days',
                'laterpay_transitional_period_end_after_days'   => 'transitional_period_end_after_days,',
                'laterpay_reach_end_price_after_days'           => 'reach_end_price_after_days',
            );

            $new_meta_values = array();

            foreach ( $meta_migration_mapping as $old_meta_key => $new_key ) {
                $value = get_post_meta( $post_id, $old_meta_key, true );

                if ( $value !== '' ) {
                    // migrate old data: if post_pricing is '0' or '1', set it to 'individual price'
                    if ( $old_meta_key === 'laterpay_post_pricing_type' && in_array( $value, array( '0', '1' ) ) ) {
                        $value = 'individual price';
                    }

                    // add the meta_value to the new postmeta array
                    $new_meta_values[ $new_key ] = $value;

                    // delete the old postmeta
                    delete_post_meta( $post_id, $old_meta_key );
                }
            }

            if ( ! empty( $new_meta_values ) ) {
                add_post_meta( $post_id, 'laterpay_post_prices', $new_meta_values, true );
            }
        }

        return $return;
    }

	/**
	 * Create our custom tables and set the required options.
     *
	 * @return void
	 */
	public function install() {
		global $wpdb;

        // cancel the installation process, if the requirements check returns errors
        $notices = (array) $this->check_requirements();
        if ( count( $notices ) ) {
            return;
        }

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$table_currency     = $wpdb->prefix . 'laterpay_currency';
		$table_terms_price  = $wpdb->prefix . 'laterpay_terms_price';
		$table_history      = $wpdb->prefix . 'laterpay_payment_history';
		$table_post_views   = $wpdb->prefix . 'laterpay_post_views';

		$sql = "
            CREATE TABLE $table_currency (
                id            INT(10)         NOT NULL AUTO_INCREMENT,
                short_name    VARCHAR(3)      NOT NULL,
                full_name     VARCHAR(64)     NOT NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );

		$sql = "
            CREATE TABLE $table_terms_price (
                id                INT(11)         NOT NULL AUTO_INCREMENT,
                term_id           INT(11)         NOT NULL,
                price             DOUBLE          NOT NULL DEFAULT '0',
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		dbDelta( $sql );

		$sql = "
            CREATE TABLE $table_history (
                id                INT(11)         NOT NULL AUTO_INCREMENT,
                mode              ENUM('test', 'live') NOT NULL DEFAULT 'test',
                post_id           INT(11)         NOT NULL,
                currency_id       INT(11)         NOT NULL,
                price             FLOAT           NOT NULL,
                date              DATETIME        NOT NULL,
                ip                INT             NOT NULL,
                hash              VARCHAR(32)     NOT NULL,
                PRIMARY KEY  (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		dbDelta( $sql );

		$sql = "
            CREATE TABLE $table_post_views (
                post_id           INT(11)         NOT NULL,
                date              DATETIME        NOT NULL,
                user_id           VARCHAR(32)     NOT NULL,
                count             BIGINT UNSIGNED NOT NULL DEFAULT 1,
                ip                VARBINARY(16)   NOT NULL,
                UNIQUE KEY  (post_id, user_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";
		dbDelta( $sql );

		// seed currency table
		$wpdb->replace(
			$table_currency,
			array(
				'id'            => 1,
				'short_name'    => 'USD',
				'full_name'     => 'U.S. dollar',
			)
		);
		$wpdb->replace(
			$table_currency,
			array(
				'id'            => 2,
				'short_name'    => 'EUR',
				'full_name'     => 'Euro',
			)
		);

		add_option( 'laterpay_teaser_content_only',      '1' );
		add_option( 'laterpay_plugin_is_in_live_mode',   '0' );
		add_option( 'laterpay_sandbox_merchant_id',      '' );
		add_option( 'laterpay_sandbox_api_key',          '' );
		add_option( 'laterpay_live_merchant_id',         '' );
		add_option( 'laterpay_live_api_key',             '' );
		add_option( 'laterpay_global_price',             $this->config->get( 'currency.default_price' ) );
		add_option( 'laterpay_currency',                 $this->config->get( 'currency.default' ) );
		update_option( 'laterpay_version',               $this->config->version );
        // option 'laterpay_plugin_is_activated' is set to empty on installation, to be able to tell between a fresh
        // installation and an activated but deactivated installation, to lead the user through the get started process,
        // if it is a fresh installation
        add_option( 'laterpay_plugin_is_activated',      '' );

		// clear opcode cache
		LaterPay_Helper_Cache::reset_opcode_cache();

		// re-activate deactivated installation
		$activated = get_option( 'laterpay_plugin_is_activated', '' );
		if ( $activated == '0' ) {
			update_option( 'laterpay_plugin_is_activated', '1' );
		}

        // update capabilities
        $laterpay_capabilities = new LaterPay_Core_Capabilities();
        $laterpay_capabilities->populate_roles();
	}

}
