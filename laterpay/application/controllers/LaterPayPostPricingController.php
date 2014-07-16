<?php

class LaterPayPostPricingController extends LaterPayAbstractController {

    public function loadAssets() {
        parent::loadAssets();
        $this->loadPostPricingStyles();
        $this->loadPostPricingScripts();
    }

    /**
     * Load page-specific CSS
     */
    public function loadPostPricingStyles() {
        global $laterpay_version;

        wp_register_style(
            'laterpay-post-edit',
            LATERPAY_ASSETS_PATH . '/css/laterpay-post-edit.css',
            array(),
            $laterpay_version
        );
        wp_enqueue_style('laterpay-post-edit');
    }

    /**
     * Load page-specific JS
     */
    public function loadPostPricingScripts() {
        global $laterpay_version;

        wp_register_script(
            'laterpay-d3',
            LATERPAY_ASSETS_PATH . '/js/vendor/d3.min.js',
            array(),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-d3-dynamic-pricing-widget',
            LATERPAY_ASSETS_PATH . '/js/d3.dynamic.widget.js',
            array('laterpay-d3'),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-post-edit',
            LATERPAY_ASSETS_PATH . '/js/laterpay-post-edit.js',
            array('laterpay-d3', 'laterpay-d3-dynamic-pricing-widget', 'jquery'),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-d3');
        wp_enqueue_script('laterpay-d3-dynamic-pricing-widget');
        wp_enqueue_script('laterpay-post-edit');

        // pass localized strings and variables to scripts
        wp_localize_script(
            'laterpay-post-edit',
            'laterpay_post_edit',
            array(
                'globalDefaultPrice'    => (float)get_option('laterpay_global_price'),
                'locale'                => get_locale(),
                'l10n_print_after'      => 'jQuery.extend(window.lpVars, laterpay_post_edit);',
            )
        );
        wp_localize_script(
            'laterpay-d3-dynamic-pricing-widget',
            'laterpay_d3_dynamic_pricing_widget',
            array(
                'currency'              => get_option('laterpay_currency'),
                'i18nDefaultPrice'      => __('default price', 'laterpay'),
                'l10n_print_after'      => 'jQuery.extend(window.lpVars, laterpay_d3_dynamic_pricing_widget);',
            )
        );
    }

    /**
     * Render editor for teaser content
     *
     * @param object $object post object
     *
     * @access public
     */
    public function teaserContentBox( $object ) {
        if (!LaterPayUserHelper::can('laterpay_edit_teaser_content', $object)) {
            return;
        }
        $settings = array(
            'wpautop'         => 1,
            'media_buttons'   => 1,
            'textarea_name'   => 'teaser-content',
            'textarea_rows'   => 8,
            'tabindex'        => null,
            'editor_css'      => '',
            'editor_class'    => '',
            'teeny'           => 1,
            'dfw'             => 1,
            'tinymce'         => 1,
            'quicktags'       => 1
        );
        $content = get_post_meta($object->ID, 'Teaser content', true);
        $editor_id = 'postcueeditor';

        echo "<dfn>" . __("This is shown to visitors, who haven't purchased the article yet. Use an excerpt of the article that makes them want to buy the whole thing!", 'laterpay') . "</dfn>";

        wp_editor($content, $editor_id, $settings);

        echo '<input type="hidden" name="laterpay_teaser_content_box_nonce" value="' . wp_create_nonce(plugin_basename(__FILE__ )) . '" />';
    }

    /**
     * Save teaser content
     *
     * @param integer $post_id post id
     *
     * @access public
     */
    public function saveTeaserContentBox( $post_id ) {
        if ( !isset($_POST['laterpay_teaser_content_box_nonce']) || !wp_verify_nonce($_POST['laterpay_teaser_content_box_nonce'], plugin_basename(__FILE__)) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( !LaterPayUserHelper::can('laterpay_edit_teaser_content', $post_id) ) {
            return $post_id;
        }

        $meta_value     = get_post_meta($post_id, 'Teaser content', true);
        $new_meta_value = $_POST['teaser-content'];

        $this->setPostMeta($meta_value, $new_meta_value, $post_id, 'Teaser content');
    }

    /**
     * Render form for post-specific pricing
     *
     * @param object $object post object
     *
     * @access public
     */
    public function pricingPostContentBox( $object ) {
        if (!LaterPayUserHelper::can('laterpay_edit_individual_price', $object)) {
            return;
        }
        $post_specific_price = get_post_meta($object->ID, 'Pricing Post', true);

        $category = get_the_category($object->ID);
        $category_default_price = null;
        if ( !empty($category) ) {
            $id = $category[0]->term_id;
            $LaterPayModelCategory = new LaterPayModelCategory();

            $category_default_price = $LaterPayModelCategory->getPriceByCategoryId($id);
        }

        $price_post_type = get_post_meta($object->ID, 'Pricing Post Type', true);

        // return dynamic pricing widget start values
        if ( !get_post_meta($object->ID, 'laterpay_start_price', true) ) {
            $dynamic_pricing_data = array(
                array( 'x' => 0,  'y' => 1.8 ),
                array( 'x' => 13, 'y' => 1.8 ),
                array( 'x' => 18, 'y' => 0.2 ),
                array( 'x' => 30, 'y' => 0.2 )
            );
        } elseif ( get_post_meta($object->ID, 'laterpay_transitional_period_end_after_days', true) == 0 ) {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => (float)get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float)get_post_meta($object->ID, 'laterpay_change_start_price_after_days', true),
                    'y' => (float)get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float)get_post_meta($object->ID, 'laterpay_reach_end_price_after_days', true),
                    'y' => (float)get_post_meta($object->ID, 'laterpay_end_price', true)
                )
            );
        } else {
            $dynamic_pricing_data = array(
                array(
                    'x' => 0,
                    'y' => (float)get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float)get_post_meta($object->ID, 'laterpay_change_start_price_after_days', true),
                    'y' => (float)get_post_meta($object->ID, 'laterpay_start_price', true)
                ),
                array(
                    'x' => (float)get_post_meta($object->ID, 'laterpay_transitional_period_end_after_days', true),
                    'y' => (float)get_post_meta($object->ID, 'laterpay_end_price', true)
                ),
                array(
                    'x' => (float)get_post_meta($object->ID, 'laterpay_reach_end_price_after_days', true),
                    'y' => (float)get_post_meta($object->ID, 'laterpay_end_price', true)
                )
            );
        }

        echo '<input type="hidden" name="laterpay_pricing_post_content_box_nonce" value="' . wp_create_nonce(plugin_basename(__FILE__)) . '" />';

        $this->assign('price',                  (float)$post_specific_price);
        $this->assign('category_default_price', (float)$category_default_price);
        $this->assign('global_default_price',   (float)get_option('laterpay_global_price'));
        $this->assign('currency',               get_option('laterpay_currency'));
        $this->assign('price_post_type',        $price_post_type);
        $this->assign('dynamic_pricing_data',   Zend_Json::encode($dynamic_pricing_data));

        $this->render('partials/postPricingForm');
    }

    /**
     * Save post-specific pricing
     *
     * @param integer $post_id post id
     *
     * @access public
     */
    public function savePricingPostContentBox( $post_id ) {
        if ( !isset($_POST['laterpay_pricing_post_content_box_nonce']) || !wp_verify_nonce($_POST['laterpay_pricing_post_content_box_nonce'], plugin_basename(__FILE__)) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( !LaterPayUserHelper::can('laterpay_edit_individual_price', $post_id) ) {
            return $post_id;
        }

        $delocalized_price = (float)str_replace(',', '.', $_POST['pricing-post']);

        $this->setPostMeta(
            get_post_meta($post_id, 'Pricing Post', true),
            $delocalized_price,
            $post_id,
            'Pricing Post'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'Pricing Post Type', true),
            stripslashes($_POST['price_post_type']),
            $post_id,
            'Pricing Post Type'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_start_price', true),
            stripslashes($_POST['laterpay_start_price']),
            $post_id,
            'laterpay_start_price'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_end_price', true),
            stripslashes($_POST['laterpay_end_price']),
            $post_id,
            'laterpay_end_price'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_change_start_price_after_days', true),
            stripslashes($_POST['laterpay_change_start_price_after_days']),
            $post_id,
            'laterpay_change_start_price_after_days'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_transitional_period_end_after_days', true),
            stripslashes($_POST['laterpay_transitional_period_end_after_days']),
            $post_id,
            'laterpay_transitional_period_end_after_days'
        );
        $this->setPostMeta(
            get_post_meta($post_id, 'laterpay_reach_end_price_after_days', true),
            stripslashes($_POST['laterpay_reach_end_price_after_days']),
            $post_id,
            'laterpay_reach_end_price_after_days'
        );
    }

    /**
     * Set post meta data
     *
     * @param string  $meta_value     old meta value
     * @param string  $new_meta_value new meta value
     * @param integer $post_id        post id
     * @param string  $name           meta name
     *
     * @access public
     */
    public function setPostMeta( $meta_value, $new_meta_value, $post_id, $name ) {
        if ( '' == $new_meta_value ) {
            delete_post_meta($post_id, $name);
        } elseif ( $new_meta_value != $meta_value ) {
            add_post_meta($post_id, $name, $new_meta_value, true) || update_post_meta($post_id, $name, $new_meta_value);
        }
    }

}
