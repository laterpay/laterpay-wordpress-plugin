<?php

/**
 * LaterPay WordPress.org Compatible
 */
class LaterPay_Compatibility_InstallCompat extends LaterPay_Controller_Base
{

    /**
     * instance of the LaterPay_Compatibility_InstallCompat
     *
     * @var LaterPay_Compatibility_InstallCompat $instance
     *
     * @access private
     */
    private static $instance;

    /**
     * function for sigleton object.
     *
     * @return object LaterPay_Compatibility_InstallCompat
     */
    public static function get_instance()
    {
        $is_not_vip                 = ( ! laterpay_check_is_vip() );
        $plugin_version             = get_option( 'laterpay_plugin_version' );
        $should_run_table_migration = ( $is_not_vip && false !== $plugin_version );
        if ( $should_run_table_migration ) {
            if ( ! isset( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
    }

    /**
     * Update the existing database table for 'terms_price' and set all prices to 'ppu'.
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_terms_price_table() {
        global $wpdb;

        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.8', '<' ) ) {
            return;
        }

        $table       = $wpdb->prefix . 'laterpay_terms_price';
        $table_terms = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table . '\';' );

        if ( $table_terms ) {
            $columns    = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table .';' );

            // before version 0.9.8 we had no 'revenue_model' column
            $is_up_to_date = false;
            $modified      = false;
            foreach ( $columns as $column ) {
                if ( $column->Field === 'revenue_model' ) {
                    $modified      = strpos( strtolower( $column->Type ), 'enum' ) !== false;
                    $is_up_to_date = true;
                }
            }

            // if the table needs an update, add the 'revenue_model' column and set the current values to 'ppu'
            if ( ! $is_up_to_date ) {
                $wpdb->query( 'ALTER TABLE ' . $table . " ADD revenue_model CHAR( 3 ) NOT NULL DEFAULT  'ppu';" );
            }

            // change revenue model column data type to ENUM
            if ( ! $modified ) {
                $wpdb->query( 'ALTER TABLE ' . $table . " MODIFY revenue_model ENUM('ppu', 'sis') NOT NULL DEFAULT 'ppu';" );
            }
        }
    }

    /**
     * Updating the existing currency option to EUR, if new version is greater than or equal 0.9.8.
     *
     * @since 0.9.8
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_currency_to_euro() {
        global $wpdb;

        $current_version = $this->config->get( 'version' );

        // check, if the current version is greater than or equal 0.9.8
        if ( version_compare( $current_version, '0.9.8', '>=' ) ) {

            // remove currency table
            $sql = 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'laterpay_currency';
            $wpdb->query( $sql );
        }
    }

    /**
     * Updating the existing time passes table and remove unused columns.
     *
     * @since 0.9.10
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_time_passes_table() {
        global $wpdb;

        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.11.4', '<' ) ) {
            return;
        }

        $table        = $wpdb->prefix . 'laterpay_passes';
        $table_passes = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table . '\';' );

        if ( $table_passes ) {

            $columns    = $wpdb->get_results( 'SHOW COLUMNS FROM ' . $table .';' );

            // before version 0.9.10 we have 'title_color', 'description_color', 'background_color',
            //  and 'background_path' columns that we will remove
            $is_up_to_date   = true;
            $removed_columns = array(
                'title_color',
                'description_color',
                'background_color',
                'background_path',
            );

            $is_deleted_flag_present = false;

            foreach ( $columns as $column ) {
                if ( in_array( $column->Field, $removed_columns, true ) ) {
                    $is_up_to_date = false;
                }
                if ( 'is_deleted' === $column->Field ) {
                    $is_deleted_flag_present = true;
                }
            }

            // if the table needs an update
            if ( ! $is_up_to_date ) {
                $wpdb->query( 'ALTER TABLE ' . $table . ' DROP title_color, DROP description_color, DROP background_color, DROP background_path;' );
            }

            // if need to add is_deleted field
            if ( ! $is_deleted_flag_present ) {
                $wpdb->query( 'ALTER TABLE ' . $table . ' ADD `is_deleted` int(1) NOT NULL DEFAULT 0;' );
            }
        }
    }

    /**
     * Drop statistic tables
     *
     * @since 0.9.14
     *
     * @return void
     */
    public function drop_statistics_tables() {
        global $wpdb;

        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.14', '<' ) ) {
            return;
        }

        $table_history    = $wpdb->prefix . 'laterpay_payment_history';
        $table_post_views = $wpdb->prefix . 'laterpay_post_views';

        $table_history_exist    = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table_history . '\';' );
        $table_post_views_exist = $wpdb->get_results( 'SHOW TABLES LIKE \'' . $table_post_views . '\';' );

        if ( $table_history_exist ) {
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $table_history . ';' );
        }

        if ( $table_post_views_exist ) {
            $wpdb->query( 'DROP TABLE IF EXISTS ' . $table_post_views . ';' );
        }
    }

    /**
     * Remove ppul values
     *
     * @since 0.9.24
     *
     * @return void
     */
    public function maybe_remove_ppul() {
        $current_version = get_option( 'laterpay_plugin_version' );
        if ( version_compare( $current_version, '0.9.24', '<' ) ) {
            return;
        }

        // update time pass revenues
        $time_pass_model = LaterPay_Model_TimePassWP::get_instance();
        $time_passes     = $time_pass_model->get_all_time_passes();

        if ( $time_passes ) {
            foreach ( $time_passes as $time_pass ) {
                if ( $time_pass['revenue_model'] === 'ppul' ) {
                    $time_pass['revenue_model'] = 'ppu';
                    $time_pass_model->update_time_pass( $time_pass );
                }
            }
        }

        // update global revenue
        $global_revenue  = get_option( 'laterpay_global_price_revenue_model' );

        if ( $global_revenue === 'ppul' ) {
            update_option( 'laterpay_global_price_revenue_model', 'ppu' );
        }

        // update category revenues
        $category_price_model          = LaterPay_Model_CategoryPriceWP::get_instance();
        $categories_with_defined_price = $category_price_model->get_categories_with_defined_price();

        if ( $categories_with_defined_price ) {
            foreach ( $categories_with_defined_price as $category ) {
                if ( $category->revenue_model === 'ppul' ) {

                    $category_price_model->set_category_price(
                        $category->category_id,
                        $category->category_price,
                        'ppu',
                        $category->id
                    );
                }
            }
        }
    }

    /**
     * Update the existing postmeta meta_keys when the new version is greater than or equal 0.9.7.
     *
     * @since 0.9.7
     * @wp-hook admin_notices
     *
     * @return void
     */
    public function maybe_update_meta_keys() {
        global $wpdb;

        // check, if the current version is greater than or equal 0.9.7
        if ( version_compare( $this->config->get( 'version' ), '0.9.7', '>=' ) ) {
            // map old values to new ones
            $meta_key_mapping = array(
                'Teaser content'    => 'laterpay_post_teaser',
                'Pricing Post'      => 'laterpay_post_pricing',
                'Pricing Post Type' => 'laterpay_post_pricing_type',
            );

            $sql = 'UPDATE ' . $wpdb->postmeta . " SET meta_key = '%s' WHERE meta_key = '%s'";

            foreach ( $meta_key_mapping as $before => $after ) {
                $prepared_sql = $wpdb->prepare( $sql, array( $after, $before ) );
                $wpdb->query( $prepared_sql );
            }
        }
    }
}