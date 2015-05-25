<?php

/**
 * LaterPay settings controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Settings extends LaterPay_Controller_Base
{
    private $has_custom_roles = false;

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();
        // register and enqueue stylesheet
        wp_register_style(
            'laterpay-options',
            $this->config->css_url . 'laterpay-options.css',
            array(),
            $this->config->version
        );
        wp_enqueue_style( 'laterpay-options' );

        // load page-specific JS
        wp_register_script(
            'laterpay-backend',
            $this->config->js_url . 'laterpay-backend.js',
            array( 'jquery' ),
            $this->config->version,
            true
        );
        wp_register_script(
            'laterpay-backend-options',
            $this->config->js_url . 'laterpay-backend-options.js',
            array( 'jquery', 'laterpay-backend' ),
            $this->config->version,
            true
        );

        // pass localized strings and variables to script
        wp_localize_script(
            'laterpay-backend-options',
            'lpVars',
            array(
                'i18nFetchingUpdate'                                => __( 'Fetching data from browscap.org', 'laterpay' ),
                'i18nUpdateFailed'                                  => __( 'Browscap cache update has failed', 'laterpay' ),
                'i18nUpToDate'                                      => __( 'Your database is up to date :-)', 'laterpay' ),
                'i18nconfirmTechnicalRequirementsForBrowscapUpdate' => __( 'Your server must have > 100 MB of RAM and the /cache folder within the LaterPay plugin must be writable for an update. Start database update?', 'laterpay' ),
                'laterpayApiOptions'                                => self::get_laterpay_api_options(),
            )
        );

        wp_enqueue_script( 'laterpay-backend-options' );
    }

    /**
     * Process Ajax requests from appearance tab.
     *
     * @return void
     */
    public static function process_ajax_requests() {
        // check for required capabilities to perform action
        if ( ! current_user_can( 'activate_plugins' ) ) {
            wp_send_json(
                array(
                    'success' => false,
                    'message' => __( 'You don\'t have sufficient user capabilities to do this.', 'laterpay' )
                )
            );
        }
        $from = isset( $_POST['form'] ) ? sanitize_text_field( $_POST['form'] ) : '';
        switch ( $from ) {
            // update presentation mode for paid content
            case 'update_browscap_cache':

                $browscap = LaterPay_Helper_Browser::php_browscap();
                // to check current cache version we need to load it first
                $browscap->getBrowser();
                $remote_version = $browscap->getRemoteVersionNumber();
                $current_version = $browscap->getSourceVersion();
                if ( $current_version == $remote_version ) {
                    wp_send_json(
                        array(
                            'success' => true,
                            'message' => __( 'Your database is already up to date', 'laterpay' ),
                        )
                    );
                }
                try {
                    $result = $browscap->updateCache();
                    if ( ! $result ) {
                        wp_send_json(
                            array(
                                'success' => false,
                                'message' => __( 'Browscap cache update has failed', 'laterpay' ),
                            )
                        );
                    }
                } catch (Exception $e) {
                    wp_send_json(
                        array(
                            'success' => false,
                            'message' => __( 'Browscap cache update has failed', 'laterpay' ),
                        )
                    );
                }

                wp_send_json(
                    array(
                        'success' => true,
                        'message' => __( 'Browscap cache has been updated', 'laterpay' ),
                    )
                );
                break;
            default:
                break;
        }

        wp_send_json(
            array(
                'success' => false,
                'message' => __( 'An error occurred when trying to save your settings. Please try again.', 'laterpay' ),
            )
        );
    }

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
        $this->load_assets();
        // pass variables to template
        $view_args = array(
            'settings_title' => __( 'LaterPay Advanced Settings', 'laterpay' ),
        );

        $this->assign( 'laterpay', $view_args );

        // render view template for options page
        echo laterpay_sanitized( $this->get_text_view( 'backend/options' ) );
    }

    /**
     * Configure content of LaterPay advanced settings page.
     *
     * @return void
     */
    public function init_laterpay_advanced_settings() {
        // add sections with fields
        $this->add_debugger_settings();
        $this->add_caching_settings();
        $this->add_enabled_post_types_settings();
        $this->add_time_passes_settings();
        $this->add_gift_codes_settings();
        $this->add_teaser_content_settings();
        $this->add_preview_excerpt_settings();
        $this->add_unlimited_access_settings();
        $this->add_logger_settings();
        $this->add_browscap_settings();
        $this->add_laterpay_api_settings();
    }

    /**
     * Add debugger section and fields.
     *
     * @return void
     */
    public function add_debugger_settings() {
        add_settings_section(
            'laterpay_debugger',
            __( 'Debugger Pane', 'laterpay' ),
            array( $this, 'get_debugger_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_debugger_enabled',
            __( 'LaterPay Debugger', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_debugger',
            array(
                'name'  => 'laterpay_debugger_enabled',
                'value' => 1,
                'type'  => 'checkbox',
                'label' => __( 'I want to view the LaterPay debugger pane', 'laterpay' ),
            )
        );

        register_setting( 'laterpay', 'laterpay_debugger_enabled' );
    }

    /**
     * Render the hint text for the logger section.
     *
     * @return string description
     */
    public function get_debugger_section_description() {
        echo laterpay_sanitize_output( '<p>' .
            __( 'The LaterPay debugger pane contains a lot of helpful plugin- and system-related information
               for debugging the LaterPay plugin and fixing configuration problems.<br>
               When activated, the debugger pane is rendered at the bottom of the screen.<br>
               It is visible both for logged in not logged in users!<br>
               On a production installation you should switch it off again as soon as you don\'t need it anymore.', 'laterpay' ) .
        '</p>' );
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
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_caching',
            array(
                'name'  => 'laterpay_caching_compatibility',
                'value' => 1,
                'type' => 'checkbox',
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'You MUST enable caching compatiblity mode, if you are using a caching solution that caches
                entire HTML pages.<br>
                In caching compatibility mode the plugin works like this:<br>
                It renders paid posts only with the teaser content. This allows to cache them as static files without
                risking to leak the paid content.<br>
                When someone visits the page, it makes an Ajax request to determine, if the visitor has already bought
                the post and replaces the teaser with the full content, if required.', 'laterpay' ) .
        '</p>' );
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'Please choose, which standard and custom post types should be sellable with LaterPay.',
            'laterpay' ) .
        '</p>' );
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
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_time_passes',
            array(
                'name'  => 'laterpay_show_time_passes_widget_on_free_posts',
                'value' => 1,
                'type'  => 'checkbox',
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'Please choose, if you want to show the time passes widget on free posts, or only on paid posts.',
            'laterpay' ) .
        '</p>' );
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
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_gift_codes',
            array(
                'name'  => 'laterpay_maximum_redemptions_per_gift_code',
                'class' => 'lp_number-input',
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'Specify, how many times a gift code can be redeemed for the associated time pass.', 'laterpay' ) .
        '</p>' );
    }

    /**
     * Sanitize maximum redemptions per gift code.
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
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_teaser_content',
            array(
                'name'          => 'laterpay_teaser_content_word_count',
                'class'         => 'lp_number-input',
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'The LaterPay WordPress plugin automatically generates teaser content for every paid post
                without teaser content.<br>
                While technically possible, setting this parameter to zero is HIGHLY DISCOURAGED.<br>
                If you really, really want to have NO teaser content for a post, enter one space
                into the teaser content editor for that post.', 'laterpay' ) .
        '</p>' );
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
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_preview_excerpt',
            array(
                'name'          => 'laterpay_preview_excerpt_percentage_of_content',
                'class'         => 'lp_number-input',
                'appended_text' => __( 'Percentage of content to be extracted;
                                      20 means "extract 20% of the total number of words of the post".', 'laterpay' ),
            )
        );

        add_settings_field(
            'laterpay_preview_excerpt_word_count_min',
            __( 'Minimum Number of Words', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_preview_excerpt',
            array(
                'name'          => 'laterpay_preview_excerpt_word_count_min',
                'class'         => 'lp_number-input',
                'appended_text' => __( 'Applied if number of words as percentage of the total number of words is less
                                      than this value.', 'laterpay' ),
            )
        );

        add_settings_field(
            'laterpay_preview_excerpt_word_count_max',
            __( 'Maximum Number of Words', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_preview_excerpt',
            array(
                'name'          => 'laterpay_preview_excerpt_word_count_max',
                'class'         => 'lp_number-input',
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'In the appearance tab, you can choose to preview your paid posts with the teaser content plus
                an excerpt of the full content, covered by a semi-transparent overlay.<br>
                The following three parameters give you fine-grained control over the length of this excerpt.<br>
                These settings do not affect the teaser content in any way.', 'laterpay' ) .
        '</p>' );
    }

    /**
     * Add unlimited access section and fields.
     *
     * @return void
     */
    public function add_unlimited_access_settings() {
        global $wp_roles;
        $custom_roles  = array();

        $default_roles = array(
            'administrator',
            'editor',
            'contributor',
            'author',
            'subscriber',
        );

        $categories    = array(
            'none' => 'none',
            'all'  => 'all',
        );

        $args          = array(
            'hide_empty' => false,
            'taxonomy'   => 'category',
        );

        // get custom roles
        foreach ( $wp_roles->roles as $role => $role_data ) {
            if ( ! in_array( $role, $default_roles ) ) {
                $this->has_custom_roles = true;
                $custom_roles[ $role ] = $role_data['name'];
            }
        }

        // get categories and add them to the array
        $wp_categories = get_categories( $args );
        foreach ( $wp_categories as $category ) {
            $categories[ $category->term_id ] = $category->name;
        }

        add_settings_section(
            'laterpay_unlimited_access',
            __( 'Unlimited Access to Paid Content', 'laterpay' ),
            array( $this, 'get_unlimited_access_section_description' ),
            'laterpay'
        );

        register_setting( 'laterpay', 'laterpay_unlimited_access', array( $this, 'validate_unlimited_access' ) );

        // add options for each custom role
        foreach ( $custom_roles as $role => $name ) {
            add_settings_field(
                $role,
                $name,
                array( $this, 'get_unlimited_access_markup' ),
                'laterpay',
                'laterpay_unlimited_access',
                array(
                    'role'       => $role,
                    'categories' => $categories,
                )
            );
        }

    }

    /**
     * Render the hint text for the unlimited access section.
     *
     * @return string description
     */
    public function get_unlimited_access_section_description() {
        echo laterpay_sanitize_output( '<p>' .
            __( "You can give logged-in users unlimited access to specific categories depending on their user
                role.<br>
                This feature can be useful e.g. for giving free access to existing subscribers.<br>
                We recommend the plugin 'User Role Editor' for adding custom roles to WordPress.", 'laterpay' ) .
        '</p>' );

        if ( $this->has_custom_roles ) {
            // show header
            echo laterpay_sanitize_output( '<table class="form-table">
                        <tr>
                            <th>' . __( 'User Role', 'laterpay' ) . '</th>
                            <td>' . __( 'Unlimited Access to Categories', 'laterpay' ) . '</td>
                        </tr>
                  </table>' );
        } else {
            // tell the user that he needs to have at least one custom role defined
            echo laterpay_sanitize_output( '<h4>' . __( 'Please add a custom role first.', 'laterpay' ) . '</h4>' );
        }
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
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_logger',
            array(
                'name'  => 'laterpay_access_logging_enabled',
                'value' => 1,
                'type'  => 'checkbox',
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
        echo laterpay_sanitize_output( '<p>' .
            __( 'The LaterPay WordPress plugin generates sales statistics for you on the dashboard and on the posts
                pages.<br>
                For collecting the required data it sets a cookie and stores all requests from visitors of your
                blog.<br>
                This data is stored anonymously on your server and not shared with LaterPay or anyone else.<br>
                It will automatically be deleted after three months.', 'laterpay' ) .
        '</p>' );
    }

    /**
     * Add Browscap cache file section and fields.
     *
     * @return void
     */
    public function add_browscap_settings() {
        $browscap = LaterPay_Helper_Browser::php_browscap();
        // to check current cache version we need to load it first
        $browscap->getBrowser();
        $remote_version = $browscap->getRemoteVersionNumber();
        $current_version = $browscap->getSourceVersion();
        $update_required = $remote_version > $current_version;

        add_settings_section(
            'browscap',
            __( 'Detection of Crawlers', 'laterpay' ),
            array( $this, 'get_browscap_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_browscap_cache_version',
            __( 'Crawler Database', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'browscap',
            array(
                'type'          => 'submit',
                'name'          => 'laterpay_browscap_cache_version',
                'value'         => __( 'Update Database', 'laterpay' ),
                'classes'       => 'button button-primary',
                'disabled'      => ! $update_required,
                'appended_text' => $update_required ? __( 'Update required', 'laterpay' ) : __( 'Your database is up to date :-)', 'laterpay' ),
                'id'            => 'lp_js_updateBrowscapCache',
            )
        );

        register_setting( 'laterpay', 'laterpay_access_logging_enabled' );
    }

    /**
     * Render the hint text for the Browscap section.
     *
     * @return string description
     */
    public function get_browscap_description() {
        echo laterpay_sanitize_output( '<p>' .
            __( 'The LaterPay WordPress plugin uses the <a href="http://browscap.org/" target="_blank">Browscap</a>
               library to detect when a crawler visits your site.<br>
               Crawlers are not compatible with LaterPay, because they don\'t support the forwarding that LaterPay
               performs to identify a visitor.<br>
               When a crawler is detected, it is simply served the teaser content. This ensures your site is not
               reported as broken e.g. by search engines.<br>
               Because new browsers and crawlers are released frequently, the Browscap database needs to be updated
               occasionally, which is usually done with the plugin releases.<br>
               If you encounter problems, you can trigger a manual update with the "Update Database" button.', 'laterpay' ) .
        '</p>' );
    }

    /**
     * Generic method to render input fields.
     *
     * @param array $field array of field params
     *
     * @return string input markup
     */
    public function get_input_field_markup( $field = null ) {
        $inputs_markup = '';

        if ( $field && isset( $field['name'] ) ) {
            $option_value = get_option( $field['name'] );
            $field_value  = isset( $field['value'] ) ? $field['value'] : get_option( $field['name'], '' );
            $type         = isset( $field['type'] ) ? $field['type'] : 'text';
            $classes      = isset( $field['classes'] ) ? $field['classes'] : array();

            // clean 'class' data
            if ( ! is_array( $classes ) ) {
                $classes = array($classes);
            }
            $classes = array_unique( $classes );

            if ( $type == 'text' ) {
                $classes[] = 'regular-text';
            }

            $inputs_markup = '';
            if ( isset( $field['label'] ) ) {
                $inputs_markup .= '<label>';
            }

            $inputs_markup .= '<input type="' . $type . '" name="' . $field['name'] . '" value="' . sanitize_text_field( $field_value ) . '"';

            // add id, if set
            if ( isset( $field['id'] ) ) {
                $inputs_markup .= ' id="' . $field['id'] . '"';
            }

            // add classes, if set
            $inputs_markup .= ! empty( $classes ) ? ' class="' . implode( ' ', $classes ) . '"' : '';

            // add checked property, if set
            if ( 'checkbox' == $type ) {
                $inputs_markup .= $option_value ? ' checked' : '';
            }

            // add disabled property, if set
            if ( isset( $field['disabled'] ) && $field['disabled'] ) {
                $inputs_markup .= ' disabled';
            }

            $inputs_markup .= '>';

            if ( isset( $field['appended_text'] ) ) {
                $inputs_markup .= '<dfn class="lp_appended-text">' . laterpay_sanitize_output( $field['appended_text'] ) . '</dfn>';
            }
            if ( isset( $field['label'] ) ) {
                $inputs_markup .= $field['label'];
                $inputs_markup .= '</label>';
            }
        }

        echo laterpay_sanitized( $inputs_markup );
    }

    /**
     * Generic method to render select fields.
     *
     * @param array $field array of field params
     *
     * @return string select markup
     */
    public function get_select_field_markup( $field = null ) {
        $select_markup = '';

        if ( $field && isset( $field['name'] ) ) {
            $field_value  = isset( $field['value'] ) ? $field['value'] : get_option( $field['name'] );
            $options      = isset( $field['options'] ) ? (array) $field['options'] : array();
            $classes      = isset( $field['class'] ) ? $field['class'] : array();
            if ( ! is_array( $classes ) ) {
                $classes = array($classes);
            }

            $select_markup = '';
            if ( isset( $field['label'] ) ) {
                $select_markup .= '<label>';
            }
            // remove duplicated classes
            $classes = array_unique( $classes );

            $select_markup .= '<select name="' . $field['name'] . '"';

            if ( isset( $field['id'] ) ) {
                $select_markup .= ' id="' . $field['id'] . '"';
            }

            if ( isset( $field['disabled'] ) && $field['disabled'] ) {
                $select_markup .= ' disabled';
            }
            $select_markup .= ! empty( $classes ) ? ' class="' . implode( ' ', $classes ) . '"' : '';
            $select_markup .= '>';

            $options_markup = '';
            foreach ( $options as $option ) {
                if ( ! is_array( $option ) ) {
                    $option_value = $option_text = $option;
                } else {
                    $option_value   = isset( $option['value'] ) ? $option['value'] : '';
                    $option_text    = isset( $option['text'] ) ? $option['text'] : '';
                }
                $selected = '';
                if ( $field_value == $option_value ) {
                    $selected = 'selected';
                }
                $options_markup .= '<option value="' . esc_attr( $option_value ) .  '" ' . $selected . '>' . laterpay_sanitize_output( $option_text ) . '</option>';
            }
            $select_markup .= $options_markup;
            $select_markup .= '</select>';
            if ( isset( $field['appended_text'] ) ) {
                $select_markup .= '<dfn class="lp_appended-text">' . laterpay_sanitize_output( $field['appended_text'] ) . '</dfn>';
            }
            if ( isset( $field['label'] ) ) {
                $select_markup .= $field['label'];
                $select_markup .= '</label>';
            }
        }

        echo laterpay_sanitized( $select_markup );
    }

    /**
     * Render the inputs for the unlimited access section.
     *
     * @param array $field array of field parameters
     *
     * @return string unlimited access form markup
     */
    public function get_unlimited_access_markup( $field = null ) {
        $role       = isset( $field['role'] ) ? $field['role'] : null;
        $categories = isset( $field['categories'] ) ? $field['categories'] : array();
        $unlimited  = get_option( 'laterpay_unlimited_access' ) ? get_option( 'laterpay_unlimited_access' ) : array();

        $inputs_markup  = '';
        $count          = 1;

        if ( $role ) {
            foreach ( $categories as $id => $name ) {
                $need_default   = ! isset( $unlimited[ $role ] ) || ! $unlimited[ $role ];
                $is_none_or_all = in_array( $id, array( 'none', 'all' ), true );
                $is_selected    = ! $need_default ? in_array( $id, $unlimited[ $role ] ) : false;

                $inputs_markup .= '<input type="checkbox" ';
                $inputs_markup .= 'id="lp_category--' . $role . $count . '"';
                $inputs_markup .= 'class="lp_category-access-input';
                $inputs_markup .= $is_none_or_all ? ' lp_global-access" ' : '" ';
                $inputs_markup .= 'name="laterpay_unlimited_access[' . $role . '][]"';
                $inputs_markup .= 'value="' . $id . '" ';
                $inputs_markup .= $is_selected || ( $need_default && $id === 'none' ) ? 'checked' : '';
                $inputs_markup .= '>';
                $inputs_markup .= '<label class="lp_category-access-label';
                $inputs_markup .= $is_none_or_all ? ' lp_global-access" ' : '" ';
                $inputs_markup .= 'for="lp_category--' . $role . $count . '">';
                $inputs_markup .= $is_none_or_all ? __( $name, 'laterpay' ) : $name;
                $inputs_markup .= '</label>';

                $count += 1;
            }
        }

        echo laterpay_sanitized( $inputs_markup );
    }

    /**
     * Validate unlimited access inputs before saving.
     *
     * @param $input
     *
     * @return array $valid array of valid values
     */
    public function validate_unlimited_access( $input ) {
        $valid      = array();
        $args       = array(
            'hide_empty' => false,
            'taxonomy'   => 'category',
            'parent'     => 0,
        );

        // get only 1st level categories
        $categories = get_categories( $args );

        if ( $input && is_array( $input ) ) {
            foreach ( $input as $role => $data ) {
                // check, if selected categories cover entire blog
                $covered = 1;
                foreach ( $categories as $category ) {
                    if ( ! in_array( $category->term_id, $data ) ) {
                        $covered = 0;
                        break;
                    }
                }

                // set option 'all' for this role, if entire blog is covered
                if ( $covered ) {
                    $valid[ $role ] = array( 'all' );
                    continue;
                }

                // filter values, if entire blog is not covered
                if ( in_array( 'all', $data ) && in_array( 'none', $data ) && count( $data ) == 2 ) {
                    // unset option 'all', if option 'all' and option 'none' are selected at the same time
                    unset( $data[ array_search( 'all', $data ) ] );
                } elseif ( count( $data ) > 1 ) {
                    // unset option 'all', if at least one category is selected
                    if ( array_search( 'all', $data ) !== false ) {
                        foreach ( $data as $key => $option ) {
                            if ( ! in_array( $option, array( 'none', 'all' ) ) ) {
                                unset( $data[ $key ] );
                            }
                        }
                    }

                    // unset all categories, if option 'none' is selected
                    if ( array_search( 'none', $data ) !== false ) {
                        foreach ( $data as $key => $option ) {
                            if ( ! in_array( $option, array( 'none', 'all' ) ) ) {
                                unset( $data[ $key ] );
                            }
                        }
                    }
                }

                $valid[ $role ] = array_values( $data );
            }
        }

        return $valid;
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
                $inputs_markup .= 'checked';
            }
            $inputs_markup .= '>';
            $inputs_markup .= '<span>' . $post_type->labels->name . '</span>';
            $inputs_markup .= '</label><br>';
        }

        echo laterpay_sanitized( $inputs_markup );
    }

    /**
     * Add LaterPay API settings section and fields.
     *
     * @return void
     */
    public function add_laterpay_api_settings() {
        add_settings_section(
            'laterpay_api_settings',
            __( 'LaterPay API Settings', 'laterpay' ),
            array( $this, 'get_laterpay_api_description' ),
            'laterpay'
        );

        $value      = absint( get_option( 'laterpay_api_fallback_behavior' ) );
        $options    = self::get_laterpay_api_options();
        add_settings_field(
            'laterpay_api_fallback_behavior',
            __( 'Fallback Behavior', 'laterpay' ),
            array( $this, 'get_select_field_markup' ),
            'laterpay',
            'laterpay_api_settings',
            array(
                'name'          => 'laterpay_api_fallback_behavior',
                'value'         => $value,
                'options'       => $options,
                'id'            => 'lp_js_laterpayApiFallbackSelect',
                'appended_text' => isset( $options[ $value ] ) ? $options[ $value ]['description'] : '',
            )
        );

        register_setting( 'laterpay', 'laterpay_api_fallback_behavior' );
    }

    /**
     * Render the hint text for the LaterPay API section.
     *
     * @return string description
     */
    public function get_laterpay_api_description() {
        echo laterpay_sanitize_output( '<p>' .
            __( 'Define fallback behavior in case LaterPay API is not responding', 'laterpay' ) .
        '</p>' );
    }

    /**
     * Get LaterPay API options array.
     *
     * @return string description
     */
    public static function get_laterpay_api_options() {
        return array(
            array(
                'value'         => '0',
                'text'          => __( 'Do nothing', 'laterpay' ),
                'description'   => __( 'No user can access premium content while the LaterPay API is not responding.', 'laterpay' ),
            ),
            array(
                'value'         => '1',
                'text'          => __( 'Give full access', 'laterpay' ),
                'description'   => __( 'All users have full access to premium content in order to not disappoint paying users.', 'laterpay' ),
            ),
            array(
                'value'         => '2',
                'text'          => __( 'Hide premium content', 'laterpay' ),
                'description'   => __( 'Premium content is hidden from users. Direct access would be blocked.', 'laterpay' ),
            ),
        );
    }
}
