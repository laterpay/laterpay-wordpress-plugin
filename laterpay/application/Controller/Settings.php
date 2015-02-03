<?php

class LaterPay_Controller_Settings extends LaterPay_Controller_Abstract
{
    /**
     * Add LaterPay advanced settings to the settings menu.
     *
     * @return void
     */
    public function add_laterpay_advanced_settings_page() {
        add_options_page(
            __( 'LaterPay Advanced Settings', 'laterpay' ),
            'LaterPay',
            'manage_options',
            'laterpay',
            array( $this, 'render_advanced_settings_page' )
        );
    }

    /**
     * Render the settings page for all LaterPay advanced settings.
     *
     * @return string
     */
    public function render_advanced_settings_page() {
        // pass variables to template
        $view_args = array(
            'settings_title' => __( 'LaterPay Advanced Settings', 'laterpay'),
        );

        $this->assign( 'laterpay', $view_args );

        // register and enqueue stylesheet
        wp_register_style(
            'laterpay-options',
            $this->config->css_url . 'laterpay-options.css',
            array(),
            $this->config->version
        );
        wp_enqueue_style( 'laterpay-options' );

        // render view template for options page
        echo $this->get_text_view( 'backend/options' );
    }

    /**
     * Configure content of LaterPay advanced settings page.
     *
     * @return void
     */
    public function init_laterpay_advanced_settings() {
        // add sections with fields
        $this->add_caching_settings();
        $this->add_enabled_post_types_settings();
        $this->add_time_passes_settings();
        $this->add_gift_codes_settings();
        $this->add_teaser_content_settings();
        $this->add_preview_excerpt_settings();
        $this->add_unlimited_access_settings();
        $this->add_logger_settings();
        $this->add_api_settings();
    }

    /**
     * Add caching section and fields.
     *
     * @return void
     */
    public function add_caching_settings() {
        add_settings_section(
            'laterpay_caching',
            __( 'Caching Compatibility Mode', 'laterpay' ),
            array( $this, 'get_caching_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_caching_compatibility',
            __( 'Caching Compatibility', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_caching',
            array(
                'name'  => 'laterpay_caching_compatibility',
                'value' => 1,
                'label' => __( 'I am using a caching plugin (e.g. WP Super Cache or Cachify)', 'laterpay' ),
            )
        );

        register_setting( 'laterpay', 'laterpay_caching_compatibility' );
    }

    /**
     * Render the hint text for the caching section.
     *
     * @return string description
     */
    public function get_caching_section_description() {
        echo '<p>' .
            __( 'You MUST enable caching compatiblity mode, if you are using a caching solution that caches
                entire HTML pages.<br>
                In caching compatibility mode the plugin works like this:<br>
                It renders paid posts only with the teaser content. This allows to cache them as static files without
                risking to leak the paid content.<br>
                When someone visits the page, it makes an Ajax request to determine, if the visitor has already bought
                the post and replaces the teaser with the full content, if required.', 'laterpay') .
            '</p>';
    }

    /**
     * Add activated post types section and fields.
     *
     * @return void
     */
    public function add_enabled_post_types_settings() {
        add_settings_section(
            'laterpay_post_types',
            __( 'LaterPay-enabled Post Types', 'laterpay' ),
            array( $this, 'get_enabled_post_types_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_enabled_post_types',
            __( 'Enabled Post Types', 'laterpay' ),
            array( $this, 'get_enabled_post_types_markup' ),
            'laterpay',
            'laterpay_post_types'
        );

        register_setting( 'laterpay', 'laterpay_enabled_post_types' );
    }

    /**
     * Render the hint text for the enabled post types section.
     *
     * @return string description
     */
    public function get_enabled_post_types_section_description() {
        echo '<p>' .
                __( 'Please choose, which standard and custom post types should be sellable with LaterPay.',
                'laterpay') .
            '</p>';
    }

    /**
     * Add time passes section and fields.
     *
     * @return void
     */
    public function add_time_passes_settings() {
        add_settings_section(
            'laterpay_time_passes',
            __( 'Offering Time Passes on Free Posts', 'laterpay' ),
            array( $this, 'get_time_passes_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_show_time_passes_widget_on_free_posts',
            __( 'Time Passes Widget', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_time_passes',
            array(
                'name'  => 'laterpay_show_time_passes_widget_on_free_posts',
                'value' => 1,
                'label' => __( 'I want to display the time passes widget on free and paid posts', 'laterpay' ),
            )
        );

        register_setting( 'laterpay', 'laterpay_show_time_passes_widget_on_free_posts' );
    }

    /**
     * Render the hint text for the enabled post types section.
     *
     * @return string description
     */
    public function get_time_passes_section_description() {
        echo '<p>' .
                __( 'Please choose, if you want to show the time passes widget on free posts, or only on paid posts.',
                'laterpay') .
            '</p>';
    }

    /**
     * Add gift codes section and fields.
     *
     * @return void
     */
    public function add_gift_codes_settings() {
        add_settings_section(
            'laterpay_gift_codes',
            __( 'Gift Codes Limit', 'laterpay' ),
            array( $this, 'get_gift_codes_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_maximum_redemptions_per_gift_code',
            __( 'Times Redeemable', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_gift_codes',
            array(
                'name'  => 'laterpay_maximum_redemptions_per_gift_code',
                'class' => 'lp_numberInput',
            )
        );

        register_setting( 'laterpay', 'laterpay_maximum_redemptions_per_gift_code', array( $this, 'sanitize_maximum_redemptions_per_gift_code_input' ) );
    }

    /**
     * Render the hint text for the gift codes section.
     *
     * @return string description
     */
    public function get_gift_codes_section_description() {
        echo '<p>' .
                __( 'Specify, how many times a gift code can be redeemed for the associated time pass.', 'laterpay' ) .
            '</p>';
    }

    /**
     * Sanitize maximum redemptions per gift code
     *
     * @param $input
     *
     * @return int
     */
    public function sanitize_maximum_redemptions_per_gift_code_input( $input ) {
        $error = '';
        $input = absint( $input );

        if ( $input < 1 ) {
            $input = 1;
            $error = 'Please enter a valid limit ( 1 or greater )';
        }

        if ( ! empty( $error ) ) {
            add_settings_error(
                'laterpay',
                'gift_code_redeem_error',
                $error,
                'error'
            );
        }
        return $input;
    }

    /**
     * Add teaser content section and fields.
     *
     * @return void
     */
    public function add_teaser_content_settings() {
        add_settings_section(
            'laterpay_teaser_content',
            __( 'Automatically Generated Teaser Content', 'laterpay' ),
            array( $this, 'get_teaser_content_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_teaser_content_word_count',
            __( 'Teaser Content Word Count', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_teaser_content',
            array(
                'name'          => 'laterpay_teaser_content_word_count',
                'class'         => 'lp_numberInput',
                'appended_text' => __( 'Number of words extracted from paid posts as teaser content.', 'laterpay' ),
            )
        );

        register_setting( 'laterpay', 'laterpay_teaser_content_word_count', 'absint' );
    }

    /**
     * Render the hint text for the teaser content section.
     *
     * @return string description
     */
    public function get_teaser_content_section_description() {
        echo '<p>' .
                __( 'The LaterPay WordPress plugin automatically generates teaser content for every paid post
                    without teaser content.<br>
                    While technically possible, setting this parameter to zero is HIGHLY DISCOURAGED.<br>
                    If you really, really want to have NO teaser content for a post, enter one space
                    into the teaser content editor for that post.', 'laterpay') .
            '</p>';
    }

    /**
     * Add preview excerpt section and fields.
     *
     * @return void
     */
    public function add_preview_excerpt_settings() {
        add_settings_section(
            'laterpay_preview_excerpt',
            __( 'Content Preview under Overlay', 'laterpay' ),
            array( $this, 'get_preview_excerpt_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_preview_excerpt_percentage_of_content',
            __( 'Percentage of Post Content', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_preview_excerpt',
            array(
                'name'          => 'laterpay_preview_excerpt_percentage_of_content',
                'class'         => 'lp_numberInput',
                'appended_text' => __( 'Percentage of content to be extracted;
                                      20 means "extract 20% of the total number of words of the post".', 'laterpay' ),
            )
        );

        add_settings_field(
            'laterpay_preview_excerpt_word_count_min',
            __( 'Minimum Number of Words', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_preview_excerpt',
            array(
                'name'          => 'laterpay_preview_excerpt_word_count_min',
                'class'         => 'lp_numberInput',
                'appended_text' => __( 'Applied if number of words as percentage of the total number of words is less
                                      than this value.', 'laterpay' ),
            )
        );

        add_settings_field(
            'laterpay_preview_excerpt_word_count_max',
            __( 'Maximum Number of Words', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_preview_excerpt',
            array(
                'name'          => 'laterpay_preview_excerpt_word_count_max',
                'class'         => 'lp_numberInput',
                'appended_text' => __( 'Applied if number of words as percentage of the total number of words exceeds
                                      this value.', 'laterpay' ),
            )
        );

        register_setting( 'laterpay', 'laterpay_preview_excerpt_percentage_of_content', 'absint' );
        register_setting( 'laterpay', 'laterpay_preview_excerpt_word_count_min', 'absint' );
        register_setting( 'laterpay', 'laterpay_preview_excerpt_word_count_max', 'absint' );
    }

    /**
     * Render the hint text for the preview excerpt section.
     *
     * @return string description
     */
    public function get_preview_excerpt_section_description() {
        echo '<p>' .
                __( 'In the appearance tab, you can choose to preview your paid posts with the teaser content plus
                    an excerpt of the full content, covered by a semi-transparent overlay.<br>
                    The following three parameters give you fine-grained control over the length of this excerpt.<br>
                    These settings do not affect the teaser content in any way.', 'laterpay') .
            '</p>';
    }

    /**
     * Add unlimited access section and fields.
     *
     * @return void
     */
    public function add_unlimited_access_settings() {
        add_settings_section(
            'laterpay_unlimited_access',
            __( 'Unlimited Access to Paid Content', 'laterpay' ),
            array( $this, 'get_unlimited_access_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_unlimited_access_to_paid_content',
            __( 'Roles with Unlimited Access', 'laterpay' ),
            array( $this, 'get_unlimited_access_markup' ),
            'laterpay',
            'laterpay_unlimited_access'
        );

        register_setting( 'laterpay', 'laterpay_unlimited_access_to_paid_content' );
    }

    /**
     * Render the hint text for the unlimited access section.
     *
     * @return string description
     */
    public function get_unlimited_access_section_description() {
        echo '<p>' .
                __( "Logged in users can skip LaterPay entirely, if they have a role with unlimited access
                    to paid content.<br>
                    You can use this e.g. for giving free access to existing subscribers.<br>
                    We recommend the plugin 'User Role Editor' for adding custom roles to WordPress.", 'laterpay') .
            '</p>';
    }

    /**
     * Add logger section and fields.
     *
     * @return void
     */
    public function add_logger_settings() {
        add_settings_section(
            'laterpay_logger',
            __( 'Access Logging for Generating Sales Statistics', 'laterpay' ),
            array( $this, 'get_logger_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_access_logging_enabled',
            __( 'Access Logging', 'laterpay' ),
            array( $this, 'get_checkbox_field_markup' ),
            'laterpay',
            'laterpay_logger',
            array(
                'name'  => 'laterpay_access_logging_enabled',
                'value' => 1,
                'label' => __( 'I want to record access to my site to generate sales statistics', 'laterpay' ),
            )
        );

        register_setting( 'laterpay', 'laterpay_access_logging_enabled' );
    }

    /**
     * Render the hint text for the logger section.
     *
     * @return string description
     */
    public function get_logger_section_description() {
        echo '<p>' .
                __( 'The LaterPay WordPress plugin generates sales statistics for you on the dashboard and on the posts
                    pages.<br>
                    For collecting the required data it sets a cookie and stores all requests from visitors of your
                    blog.<br>
                    This data is stored anonymously on your server and not shared with LaterPay or anyone else.<br>
                    It will automatically be deleted after three months.', 'laterpay') .
            '</p>';
    }

    /**
     * Add LaterPay API settings section and fields.
     *
     * @return void
     */
    public function add_api_settings() {
        add_settings_section(
            'laterpay_api',
            __( 'LaterPay API URLs', 'laterpay' ),
            array( $this, 'get_api_settings_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_api_sandbox_url',
            __( 'Sandbox API endpoint', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_sandbox_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        add_settings_field(
            'laterpay_api_sandbox_web_url',
            __( 'Sandbox web URL', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_sandbox_web_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        add_settings_field(
            'laterpay_api_live_url',
            __( 'Live API endpoint', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_live_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        add_settings_field(
            'laterpay_api_live_web_url',
            __( 'Live web URL', 'laterpay' ),
            array( $this, 'get_text_field_markup' ),
            'laterpay',
            'laterpay_api',
            array(
                'name'  => 'laterpay_api_live_web_url',
                'type'  => 'url',
                'class' => 'code',
            )
        );

        register_setting( 'laterpay', 'laterpay_api_sandbox_url' );
        register_setting( 'laterpay', 'laterpay_api_sandbox_web_url' );
        register_setting( 'laterpay', 'laterpay_api_live_url' );
        register_setting( 'laterpay', 'laterpay_api_live_web_url' );
    }

    /**
     * Render the hint text for the API settings section.
     *
     * @return string description
     */
    public function get_api_settings_section_description() {
        echo '<p>' .
                __( 'There is only a single reason for changing the API settings:<br>
                    By replacing the Live API URLs with the Sandbox API URLs,
                    you can configure your installation to be in
                    Test mode, but behave like an installation in Live mode.<br>
                    This is what LaterPay is doing at <a href="www.laterpaydemo.com">laterpaydemo.com</a>.',
                    'laterpay') .
            '</p>';
    }

    /**
     * Generic method to render text inputs (text, url, email, number).
     *
     * @param array $field array of field params
     *
     * @return string text markup
     */
    public function get_text_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field[ 'name' ] ) ) {
            $option_value = get_option( $field[ 'name' ] );
            $type         = isset( $field[ 'type' ] ) ? $field['type']  : 'text';
            $class        = isset( $field[ 'class'] ) ? $field['class'] : '';

            $inputs_markup = '<input type="' . $type .'" name="' . $field[ 'name' ] . '" ' .
                            'class="regular-text ' . $class . '" value="' . $option_value . '">';
            if ( isset( $field[ 'appended_text' ] ) ) {
                $inputs_markup .= '<dfn class="lp_appendedText">' . $field[ 'appended_text' ] . '<dfn>';
            }
        }

        echo $inputs_markup;
    }

    /**
     * Generic method to render checkboxes.
     *
     * @param array $field array of field params
     *
     * @return string checkbox markup
     */
    public function get_checkbox_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field[ 'name' ] ) && isset( $field[ 'value' ] ) ) {
            $option_value = get_option( $field[ 'name' ] );
            $field_value  = $field[ 'value' ];

            $inputs_markup = '';
            if ( isset( $field[ 'label' ] ) ) {
                $inputs_markup .= '<label>';
            }
            $inputs_markup .= '<input type="checkbox" name="' . $field[ 'name' ] . '" value="' . $field_value . '"';
            $inputs_markup .= $option_value ? ' checked="checked"' : '';
            $inputs_markup .= '>';
            if ( isset( $field[ 'label' ] ) ) {
                $inputs_markup .= $field[ 'label' ];
                $inputs_markup .= '</label>';
            }
        }

        echo $inputs_markup;
    }

    /**
     * Render the inputs for the unlimited access section.
     *
     * @return string unlimited access checkboxes markup
     */
    public function get_unlimited_access_markup() {
        global $wp_roles;

        $default_roles    = array( 'administrator', 'editor', 'contributor', 'author', 'subscriber' );
        $has_custom_roles = false;
        $option_value     = get_option( 'laterpay_unlimited_access_to_paid_content' );

        $inputs_markup = '';
        foreach ( $wp_roles->roles as $role => $role_data ) {
            if ( ! in_array( $role, $default_roles ) ) {
                $has_custom_roles = true;
                $inputs_markup .= '<label title="' . $role_data['name'] . '">';
                $inputs_markup .= '<input type="checkbox" name="laterpay_unlimited_access_to_paid_content" value="' . $role . '" ';
                if ( in_array( $role, ( array ) $option_value ) ) {
                    $inputs_markup .= 'checked="checked"';
                }
                $inputs_markup .= '>';
                $inputs_markup .= '<span>' . $role_data['name'] . '</span>';
                $inputs_markup .= '</label><br>';
            }
        }

        if ( ! $has_custom_roles ) {
            $inputs_markup = __( 'Please add a custom role first.', 'laterpay' );
        }

        echo $inputs_markup;
    }

    /**
     * Render the inputs for the enabled post types section.
     *
     * @return string enabled post types checkboxes markup
     */
    public function get_enabled_post_types_markup() {
        $all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
        $enabled_post_types = get_option( 'laterpay_enabled_post_types' );

        $inputs_markup = '';
        foreach ( $all_post_types as $slug => $post_type ) {
            $inputs_markup .= '<label title="' . $post_type->labels->name . '">';
            $inputs_markup .= '<input type="checkbox" name="laterpay_enabled_post_types[]" value="' . $slug . '" ';
            if ( is_array( $enabled_post_types ) && in_array( $slug, $enabled_post_types ) ) {
                $inputs_markup .= 'checked="checked"';
            }
            $inputs_markup .= '>';
            $inputs_markup .= '<span>' . $post_type->labels->name . '</span>';
            $inputs_markup .= '</label><br>';
        }

        echo $inputs_markup;
    }

}
