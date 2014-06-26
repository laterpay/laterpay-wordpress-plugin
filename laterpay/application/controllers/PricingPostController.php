<?php

class PricingPostController extends AbstractController {

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
            LATERPAY_ASSET_PATH . '/css/laterpay-post-edit.css',
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
            LATERPAY_ASSET_PATH . '/js/vendor/d3.min.js',
            array(),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-d3-dynamic-pricing-widget',
            LATERPAY_ASSET_PATH . '/js/d3.dynamic.widget.js',
            array('laterpay-d3'),
            $laterpay_version,
            true
        );
        wp_register_script(
            'laterpay-post-edit',
            LATERPAY_ASSET_PATH . '/js/laterpay-post-edit.js',
            array('laterpay-d3', 'laterpay-d3-dynamic-pricing-widget', 'jquery'),
            $laterpay_version,
            true
        );
        wp_enqueue_script('laterpay-d3');
        wp_enqueue_script('laterpay-d3-dynamic-pricing-widget');
        wp_enqueue_script('laterpay-post-edit');
    }

    /**
     * Render editor for teaser content
     *
     * @param object $object post object
     * @param object $box    post box
     *
     * @access public
     */
    public function teaserContentBox( $object, $box ) {
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
     * @param object  $post    post
     *
     * @access public
     */
    public function saveTeaserContentBox( $post_id, $post ) {
        if ( !isset($_POST['laterpay_teaser_content_box_nonce']) || !wp_verify_nonce($_POST['laterpay_teaser_content_box_nonce'], plugin_basename(__FILE__)) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( !current_user_can('edit_post', $post_id) ) {
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
     * @param object $box    post box
     *
     * @access public
     */
    public function pricingPostContentBox( $object, $box ) {
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
            $data = array(
                array( 'x' => 0,  'y' => 1.8 ),
                array( 'x' => 13, 'y' => 1.8 ),
                array( 'x' => 18, 'y' => 0.2 ),
                array( 'x' => 30, 'y' => 0.2 )
            );
        } elseif ( get_post_meta($object->ID, 'laterpay_transitional_period_end_after_days', true) == 0 ) {
            $data = array(
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
            $data = array(
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

        $this->assign('price',             (double)$post_specific_price);
        $this->assign('price_category',    (double)$category_default_price);
        $this->assign('price_post_type',   $price_post_type);
        $this->assign('data',              Zend_Json::encode($data));

        $this->render('pricingPostFormView');
    }

    /**
     * Save post-specific pricing
     *
     * @param integer $post_id post id
     * @param object  $post    post
     *
     * @access public
     */
    public function savePricingPostContentBox( $post_id, $post ) {
        if ( !isset($_POST['laterpay_pricing_post_content_box_nonce']) || !wp_verify_nonce($_POST['laterpay_pricing_post_content_box_nonce'], plugin_basename(__FILE__)) ) {
            return $post_id;
        }

        // check for required privileges to perform action
        if ( !current_user_can('edit_post', $post_id) ) {
            return $post_id;
        }

        $delocalized_price = (double)str_replace(',', '.', $_POST['pricing-post']);

        if ( $delocalized_price > 5 || $delocalized_price < 0 ) {
            $category               = get_the_category($post_id);
            $category_default_price = null;
            if ( !empty($category) ) {
                $id = $category[0]->term_id;
                $LaterPayModelCategory = new LaterPayModelCategory();
                $category_default_price = $LaterPayModelCategory->getPriceByCategoryId($id);
            }
        }

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
