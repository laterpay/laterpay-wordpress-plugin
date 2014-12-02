<?php

class LaterPay_Helper_Pricing
{
    const TYPE_GLOBAL_DEFAULT_PRICE     = 'global default price';
    const TYPE_CATEGORY_DEFAULT_PRICE   = 'category default price';
    const TYPE_INDIVIDUAL_PRICE         = 'individual price';
    const TYPE_INDIVIDUAL_DYNAMIC_PRICE = 'individual price, dynamic';

    const STATUS_POST_PUBLISHED         = 'publish';

    const ppu_min                       = 0.05;
    const ppu_max                       = 1.48;
    const ppusis_max                    = 5.00;
    const sis_min                       = 1.49;
    const sis_max                       = 149.99;
    const price_ppu_end                 = 0.05;
    const price_ppusis_end              = 1.49;
    const price_sis_end                 = 5.01;
    const price_start_day               = 13;
    const price_end_day                 = 18;

    const META_KEY                      = 'laterpay_post_prices';

    /**
     * Get array of price ranges by revenue model (Pay-per-Use or Single Sale).
     *
     * @return array
     */
    public static function get_price_ranges_by_revenue_model() {
        return array(
            'ppu_min'           => LaterPay_Helper_Pricing::ppu_min,
            'ppu_max'           => LaterPay_Helper_Pricing::ppu_max,
            'ppusis_max'        => LaterPay_Helper_Pricing::ppusis_max,
            'sis_min'           => LaterPay_Helper_Pricing::sis_min,
            'sis_max'           => LaterPay_Helper_Pricing::sis_max,
            'price_ppu_end'     => LaterPay_Helper_Pricing::price_ppu_end,
            'price_ppusis_end'  => LaterPay_Helper_Pricing::price_ppusis_end,
            'price_sis_end'     => LaterPay_Helper_Pricing::price_sis_end,
            'price_start_day'   => LaterPay_Helper_Pricing::price_start_day,
            'price_end_day'     => LaterPay_Helper_Pricing::price_end_day,
        );
    }

    /**
     * Check, if the current post or a given post is purchasable.
     *
     * @param null|WP_Post $post
     *
     * @return bool true|false
     */
    public static function is_purchasable( $post = null ) {
        if ( ! is_a( $post, 'WP_POST' ) ) {
            // load the current post in $GLOBAL['post']
            $post = get_post();
            if ( $post === null ) {
                return false;
            }
        }

        // check, if the current post price is not 0
        $price = LaterPay_Helper_Pricing::get_post_price( $post->ID );
        if ( $price == 0 ) {
            return false;
        }

        return true;
    }

    /**
     * Return all posts that have a price applied.
     *
     * @return array
     */
    public static function get_all_posts_with_price() {
        $post_args = array(
            'meta_query'        => array( array( 'meta_key' => LaterPay_Helper_Pricing::META_KEY ) ),
            'posts_per_page'    => '-1',
        );
        $posts = get_posts( $post_args );

        return $posts;
    }

    /**
     * Return all post_ids with a given category_id that have a price applied.
     *
     * @param int $category_id
     *
     * @return array
     */
    public static function get_post_ids_with_price_by_category_id( $category_id ) {
        $config     = laterpay_get_plugin_config();
        $post_args  = array(
            'fields'            => 'ids',
            'meta_query'        => array( array( 'meta_key' => LaterPay_Helper_Pricing::META_KEY ) ),
            'cat'               => $category_id,
            'posts_per_page'    => '-1',
            'post_type'         => $config->get( 'content.enabled_post_types' ),
        );
        $posts      = get_posts( $post_args );

        return $posts;
    }

    /**
     * Apply the global default price to a post.
     *
     * @param int $post_id
     *
     * @return boolean true|false
     */
    public static function apply_global_default_price_to_post( $post_id ) {
        $global_default_price = get_option( 'laterpay_global_price' );

        if ( $global_default_price == 0 ) {
            return false;
        }

        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        $post_price = array();
        $post_price[ 'type' ] = LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE;

        return update_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, $post_price );
    }


    /**
     * Apply the 'category default price' to all posts with a 'global default price' by a given category_id.
     *
     * @param integer $category_id
     *
     * @return array $updated_post_ids all updated post_ids
     */
    public static function apply_category_price_to_posts_with_global_price( $category_id ) {
        $updated_post_ids   = array();
        $post_ids           = LaterPay_Helper_Pricing::get_post_ids_with_price_by_category_id( $category_id );

        foreach ( $post_ids as $post_id ) {
            $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );
            if ( ! is_array( $post_price ) ) {
                continue;
            }

            // check, if the post uses a global default price
            if ( ! array_key_exists( 'type', $post_price ) || $post_price[ 'type' ] !== LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE ) {
                continue;
            }

            $success = LaterPay_Helper_Pricing::apply_category_default_price_to_post( $post_id, $category_id, true );
            if ( $success ) {
                $updated_post_ids[] = $post_id;
            }
        }

        return $updated_post_ids;
    }

    /**
     * Apply a given category default price to a given post.
     *
     * @param int     $post_id
     * @param int     $category_id
     * @param boolean $strict - checks, if the given category_id is assigned to the post_id
     *
     * @return boolean true|false
     */
    public static function apply_category_default_price_to_post( $post_id, $category_id, $strict = false ) {
        $post = get_post( $post_id );
        if ( $post === null ) {
            return false;
        }

        // check, if the post has the given category_id
        if ( $strict && ! has_category( $category_id, $post ) ) {
            return false;
        }

        $post_price = array(
            'type'          => LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE,
            'category_id'   => (int) $category_id,
        );

        return update_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, $post_price );
    }

    /**
     * Get post price, depending on price type applied to post.
     *
     * @param int $post_id
     *
     * @return float $price
     */
    public static function get_post_price( $post_id ) {
        $global_default_price = get_option( 'laterpay_global_price' );

        $cache_key = 'laterpay_post_price_' . $post_id;

        // checks if the price is in cache and returns it
        $price = wp_cache_get( $cache_key, 'laterpay' );
        if ( $price ) {
            return $price;
        }

        $post = get_post( $post_id );
        $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );
        if ( ! is_array( $post_price ) ) {
            $post_price = array();
        }
        $post_price_type    = array_key_exists( 'type', $post_price )        ? $post_price[ 'type' ]        : '';
        $category_id        = array_key_exists( 'category_id', $post_price ) ? $post_price[ 'category_id' ] : '';

        switch ( $post_price_type ) {
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
                $price = array_key_exists( 'price', $post_price ) ? $post_price[ 'price' ] : '';
                break;

            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
                $price = LaterPay_Helper_Pricing::get_dynamic_price( $post, $post_price );
                break;

            case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
                $LaterPay_Category_Model = new LaterPay_Model_CategoryPrice();
                $price                   = $LaterPay_Category_Model->get_price_by_category_id( (int) $category_id );
                break;

            case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
                $price = $global_default_price;
                break;

            default:
                if ( $global_default_price > 0 ) {
                    $price = $global_default_price;
                } else {
                    $price = 0;
                }
                break;
        }

        $price = (float) $price;

        // add the price to the current post cache
        wp_cache_set( $cache_key, $price, 'laterpay' );

        return (float) $price;
    }

    /**
     * Get the post price type. Returns global default price or individual price, if no valid type is set.
     *
     * @param int $post_id
     *
     * @return string $post_price_type
     */
    public static function get_post_price_type( $post_id ) {
        $cache_key = 'laterpay_post_price_type_' . $post_id;

        // get the price from the cache, if it exists
        $post_price_type = wp_cache_get( $cache_key, 'laterpay' );
        if ( $post_price_type ) {
            return $post_price_type;
        }

        $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );
        if ( ! is_array( $post_price ) ) {
            $post_price = array();
        }
        $post_price_type = array_key_exists( 'type', $post_price ) ? $post_price['type'] : '';

        // set a price type (global default price or individual price), if the returned post price type is invalid
        switch ( $post_price_type ) {
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
            case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
            case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
                break;

            default:
                $global_default_price = get_option( 'laterpay_global_price' );
                if ( $global_default_price > 0 ) {
                    $post_price_type = LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE;
                } else {
                    $post_price_type = LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE;
                }
                break;
        }

        // cache the post price type
        wp_cache_set( $cache_key, $post_price_type, 'laterpay' );

        return (string) $post_price_type;
    }

    /**
     * Get the current price for a post with dynamic pricing scheme defined.
     *
     * @param WP_Post $post
     * @param array   $post_price see post_meta 'laterpay_post_prices'
     * @param string  $post_revenue_model
     *
     * @return float price
     */
    public static function get_dynamic_price( $post, $post_price ) {
        $days_since_publication = self::dynamic_price_days_after_publication( $post );

        if ( $post_price[ 'change_start_price_after_days' ] >= $days_since_publication ) {
            $price = $post_price[ 'start_price' ];
        } else {
            if ( $post_price[ 'transitional_period_end_after_days' ] <= $days_since_publication ||
                 $post_price[ 'transitional_period_end_after_days' ] == 0
                ) {
                $price = $post_price[ 'end_price' ];
            } else {    // transitional period between start and end of dynamic price change
                $price = LaterPay_Helper_Pricing::calculate_transitional_price( $post_price, $days_since_publication );
            }
        }

        return number_format( $price, 2 );
    }

    /**
     * Get the current days count since publication.
     *
     * @param WP_Post $post
     *
     * @return int days
     */
    public static function dynamic_price_days_after_publication( $post ) {
        $days_since_publication = 0;

        // unpublished posts always have 0 days after publication
        if ( $post->post_status != LaterPay_Helper_Pricing::STATUS_POST_PUBLISHED ) {
            return $days_since_publication;
        }

        if ( function_exists( 'date_diff' ) ) {
            $date_time = new DateTime( date( 'Y-m-d' ) );
            $days_since_publication = $date_time->diff( new DateTime( date( 'Y-m-d', strtotime( $post->post_date ) ) ) )->format( '%a' );
        } else {
            $d1 = strtotime( date( 'Y-m-d' ) );
            $d2 = strtotime( $post->post_date );
            $diff_secs = abs( $d1 - $d2 );
            $days_since_publication = floor( $diff_secs / ( 3600 * 24 ) );
        }

        return $days_since_publication;
    }

    /**
     * Calculate transitional price between start price and end price based on linear equation.
     *
     * @param array $post_price postmeta see 'laterpay_post_prices'
     * @param int   $days_since_publication
     *
     * @return float
     */
    private static function calculate_transitional_price( $post_price, $days_since_publication ) {
        $end_price          = $post_price[ 'end_price' ];
        $start_price        = $post_price[ 'start_price' ];
        $days_until_end     = $post_price[ 'transitional_period_end_after_days' ];
        $days_until_start   = $post_price[ 'change_start_price_after_days' ];

        $coefficient = ( $end_price - $start_price ) / ( $days_until_end - $days_until_start );

        return $start_price + ( $days_since_publication - $days_until_start ) * $coefficient;
    }

    /**
     * Get revenue model of post price (Pay-per-Use or Single Sale).
     *
     * @param int $post_id
     *
     * @return string $revenue_model
     */
    public static function get_post_revenue_model( $post_id ) {
        $post_price = get_post_meta( $post_id, LaterPay_Helper_Pricing::META_KEY, true );

        if ( ! is_array( $post_price ) ) {
            $post_price = array();
        }

        $post_price_type = array_key_exists( 'type', $post_price ) ? $post_price['type'] : '';

        $revenue_model = '';

        // set a price type (global default price or individual price), if the returned post price type is invalid
        switch ( $post_price_type ) {
            // Dynamic Price does currently not support Single Sale as revenue model
            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_DYNAMIC_PRICE:
                $revenue_model = 'ppu';
                break;

            case LaterPay_Helper_Pricing::TYPE_INDIVIDUAL_PRICE:
                if ( array_key_exists( 'revenue_model', $post_price ) ) {
                    $revenue_model = $post_price['revenue_model'];
                }
                break;

            case LaterPay_Helper_Pricing::TYPE_CATEGORY_DEFAULT_PRICE:
                if ( array_key_exists( 'category_id', $post_price ) ) {
                    $category_model = new LaterPay_Model_CategoryPrice( );
                    $revenue_model = $category_model->get_revenue_model_by_category_id( $post_price[ 'category_id' ] );
                }
                break;

            case LaterPay_Helper_Pricing::TYPE_GLOBAL_DEFAULT_PRICE:
                $revenue_model = get_option( 'laterpay_global_price_revenue_model' );
                break;
        }

        // fallback in case the revenue_model is not correct
        if ( ! in_array( $revenue_model, array( 'ppu', 'sis' ) ) ) {

            $price = array_key_exists( 'price', $post_price ) ? $post_price['price'] : get_option( 'laterpay_global_price' );

            if ( ( $price >= self::ppu_min && $price <= self::ppusis_max ) || $price == 0.00 ) {
                $revenue_model = 'ppu';
            } else if ( $price > self::ppusis_max && $price <= self::sis_max ) {
                $revenue_model = 'sis';
            }
        }

        return $revenue_model;
    }

    /**
     * Return the revenue model of the post.
     * Validates and - if required - corrects the given combination of price and revenue model.
     *
     * @param string $revenue_model
     * @param float  $price
     *
     * @return string $revenue_model
     */
    public static function ensure_valid_revenue_model( $revenue_model, $price ) {
        if ( $revenue_model == 'ppu' ) {
            if ( $price == 0.00 || ( $price >= self::ppu_min && $price <= self::ppusis_max ) ) {
                return 'ppu';
            } else {
                return 'sis';
            }
        } else {
            if ( $price >= self::sis_min && $price <= self::sis_max ) {
                return 'sis';
            } else {
                return 'ppu';
            }
        }

    }

     /**
     * Return data for dynamic prices. Can be values already set or defaults.
     *
     * @param WP_Post $post
     * @param null $price
     *
     * @return array
     */
    public static function get_dynamic_prices( $post, $price = null ) {
        $dynamic_pricing_data = array();

        if ( ! LaterPay_Helper_User::can( 'laterpay_edit_individual_price', $post ) ) {
            return;
        }

        $post_prices = get_post_meta( $post->ID, 'laterpay_post_prices', true );
        if ( ! is_array( $post_prices ) ) {
            $post_prices = array();
        }

        $post_price = array_key_exists( 'price', $post_prices ) ? (float) $post_prices[ 'price' ] : LaterPay_Helper_Pricing::get_post_price( $post->ID );
        if ( $price !== null ) {
            $post_price = $price;
        }

        $start_price                        = array_key_exists( 'start_price',      $post_prices ) ? (float) $post_prices[ 'start_price' ] : '';
        $end_price                          = array_key_exists( 'end_price',        $post_prices ) ? (float) $post_prices[ 'end_price' ] : '';
        $reach_end_price_after_days         = array_key_exists( 'reach_end_price_after_days',           $post_prices ) ? (float) $post_prices[ 'reach_end_price_after_days' ] : '';
        $change_start_price_after_days      = array_key_exists( 'change_start_price_after_days',        $post_prices ) ? (float) $post_prices[ 'change_start_price_after_days' ] : '';
        $transitional_period_end_after_days = array_key_exists( 'transitional_period_end_after_days',   $post_prices ) ? (float) $post_prices[ 'transitional_period_end_after_days' ] : '';
        // return dynamic pricing widget start values
        if ( ( $start_price === '' ) && ( $price !== null ) ) {
            if ( $post_price > self::ppusis_max ) {
                // Single Sale (sis), if price >= 5.01
                $end_price = self::price_sis_end;
            } elseif ( $post_price > self::sis_min ) {
                // Single Sale or Pay-per-Use, if 1.49 >= price <= 5.00
                $end_price = self::price_ppusis_end;
            } else {
                // Pay-per-Use (ppu), if price <= 1.48
                $end_price = self::price_ppu_end;
            }

            $dynamic_pricing_data = array(
                array(
                      'x' => 0,
                      'y' => $post_price,
                ),
                array(
                      'x' => self::price_start_day,
                      'y' => $post_price,
                ),
                array(
                      'x' => self::price_end_day,
                      'y' => $end_price,
                ),
                array(
                      'x' => 30,
                      'y' => $end_price,
                ),
            );
        } elseif ( $transitional_period_end_after_days === '' ) {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => $start_price,
                ),
                array(
                    'x' => $change_start_price_after_days,
                    'y' => $start_price,
                ),
                array(
                    'x' => $reach_end_price_after_days,
                    'y' => $end_price,
                ),
            );
        } else {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => $start_price,
                ),
                array(
                    'x' => $change_start_price_after_days,
                    'y' => $start_price,
                ),
                array(
                    'x' => $transitional_period_end_after_days,
                    'y' => $end_price,
                ),
                array(
                    'x' => $reach_end_price_after_days,
                    'y' => $end_price,
                ),
            );
        }

        // get number of days since publication to render an indicator in the dynamic pricing widget
        $days_after_publication = LaterPay_Helper_Pricing::dynamic_price_days_after_publication( $post );

        $result = array(
            'values' => $dynamic_pricing_data,
            'price'  => array(
                'pubDays'    => $days_after_publication,
                'todayPrice' => $price,
            ),
        );

        return $result;
    }

     /**
     * Return adjusted prices.
     *
     * @param float $start
     * @param float $end
     *
     * @return array
     */
    public static function adjust_dynamic_price_points( $start, $end ) {
        if ( $start >= self::price_sis_end || $end >= self::price_sis_end ) {
            // SIS
            if ( $start != 0 && $start < self::price_sis_end ) {
                $start = self::price_sis_end;
            }
            if ( $end != 0 && $end < self::price_sis_end ) {
                $end = self::price_sis_end;
            }
        } elseif (
            ( $start >= self::sis_min && $start <= self::ppusis_max ) ||
                ( $end >= self::sis_min && $end <= self::ppusis_max )
            ) {
            // SIS or PPU
            if ( $start != 0 ) {
                if ( $start < self::sis_min ) {
                    $start = self::sis_min;
                }
                if ( $start > self::ppusis_max ) {
                    $start = self::ppusis_max;
                }
            };
            if ( $end != 0 ) {
                if ( $end < self::sis_min ) {
                    $end = self::sis_min;
                }
                if ( $end > self::ppusis_max ) {
                    $end = self::ppusis_max;
                }
            }
        } else {
            // PPU
            if ( $start != 0 ) {
                if ( $start < self::ppu_min ) {
                    $start = self::ppu_min;
                }
                if ( $start > self::ppu_max ) {
                    $start = self::ppu_max;
                }
            };
            if ( $end != 0 ) {
                if ( $end < self::ppu_min ) {
                    $end = self::ppu_min;
                }
                if ( $end > self::ppu_max ) {
                    $end = self::ppu_max;
                }
            }
        }

        return array( $start, $end );
    }

    /**
     * Select categories from a given list of categories that have a category default price
     * and return an array of their ids.
     *
     * @param array $categories
     *
     * @return array
     */
    public static function get_categories_with_price( $categories ) {
        $categories_with_price = array();
        $ids                   = array();

        if ( is_array( $categories ) ) {
            foreach ( $categories as $category ) {
                $ids[] = $category->term_id;
            }
        }

        if ( $ids ) {
            $laterpay_category_model = new LaterPay_Model_CategoryPrice();
            $categories_with_price   = $laterpay_category_model->get_category_price_data_by_category_ids( $ids );
        }

        return $categories_with_price;
    }

    /**
     * Assign a valid amount to the price, if it is outside of the allowed range.
     *
     * @param float $price
     *
     * @return float
     */
    public static function ensure_valid_price( $price ) {
        $validated_price = 0;

        // set all prices between 0.01 and 0.04 to lowest possible price of 0.05
        if ( $price > 0 && $price < self::ppu_min ) {
            $validated_price = self::ppu_min;
        }

        if ( $price == 0 || ( $price >= self::ppu_min && $price <= self::sis_max ) ) {
            $validated_price = $price;
        }

        // set all prices greater 149.99 to highest possible price of 149.99
        if ( $price > self::sis_max ) {
            $validated_price = self::sis_max;
        }

        return $validated_price;
    }

    /**
     * Get all bulk operations.
     *
     * @return mixed|null
     */
    public static function get_bulk_operations() {
        $operations = get_option( 'laterpay_bulk_operations' );

        return $operations ? unserialize( $operations ) : null;
    }

    /**
     * Get bulk operation data by id.
     *
     * @param  int $id
     *
     * @return mixed|null
     */
    public static function get_bulk_operation_data_by_id( $id ) {
        $operations = LaterPay_Helper_Pricing::get_bulk_operations();
        $data       = null;

        if ( $operations && isset( $operations[$id] ) ) {
            $data = $operations[$id]['data'];
        }

        return $data;
    }

    /**
     * Save bulk operation.
     *
     * @param  string $data    serialized bulk data
     * @param  string $message message
     *
     * @return int    $id      id of new operation
     */
    public static function save_bulk_operation( $data, $message ) {
        $operations = LaterPay_Helper_Pricing::get_bulk_operations();
        $operations = $operations ? $operations : array();

        // save bulk operation
        $operations[] = array(
                            'data'    => $data,
                            'message' => $message,
                        );
        update_option( 'laterpay_bulk_operations', serialize( $operations ) );

        end( $operations );

        return key( $operations );
    }

    /**
     * Delete bulk operation by id.
     *
     * @param  int $id
     *
     * @return bool
     */
    public static function delete_bulk_operation_by_id( $id ) {
        $was_deleted = false;
        $operations = LaterPay_Helper_Pricing::get_bulk_operations();

        if ( $operations ) {
            if ( isset( $operations[$id] ) ) {
                unset( $operations[$id] );
                $was_deleted = true;
                $operations  = $operations ? $operations : '';
                update_option( 'laterpay_bulk_operations', serialize( $operations ) );
            }
        }

        return $was_deleted;
    }

     /**
     * Reset post publication date.
     *
     * @param WP_Post $post
     *
     * @return void
     */
    public static function reset_post_publication_date( $post ) {
        $actual_date        = date( 'Y-m-d H:i:s' );
        $actual_date_gmt    = gmdate( 'Y-m-d H:i:s' );
        $post_update_data   = array(
                                    'ID'            => $post->ID,
                                    'post_date'     => $actual_date,
                                    'post_date_gmt' => $actual_date_gmt,
                                );

        wp_update_post( $post_update_data );
    }

    /**
     * Return the URL hash for a given URL.
     *
     * @param string $url
     *
     * @return string $hash
     */
    public static function get_hash_by_url( $url ) {
        return md5( md5( $url ) . wp_salt() );
    }

}
