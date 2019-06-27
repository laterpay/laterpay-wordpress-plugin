<?php

/**
 * LaterPay settings controller.
 *
 * Plugin Name: LaterPay
 * Plugin URI: https://github.com/laterpay/laterpay-wordpress-plugin
 * Author URI: https://laterpay.net/
 */
class LaterPay_Controller_Admin_Settings extends LaterPay_Controller_Base {
    private $has_custom_roles = false;

    /**
     * @see LaterPay_Core_Event_SubscriberInterface::get_subscribed_events()
     */
    public static function get_subscribed_events() {
        return array(
            'laterpay_admin_init' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'laterpay_on_plugin_is_active', 200 ),
                array( 'init_laterpay_advanced_settings' ),
            ),
            'laterpay_admin_menu' => array(
                array( 'laterpay_on_admin_view', 200 ),
                array( 'add_laterpay_advanced_settings_page' ),
            ),
        );
    }

    /**
     * @see LaterPay_Core_View::load_assets
     */
    public function load_assets() {
        parent::load_assets();

        // Get data for GA.
        $merchant_key = LaterPay_Controller_Admin::get_merchant_id_for_ga();

        LaterPay_Controller_Admin::register_common_scripts( 'settings' );

        // register and enqueue stylesheet
        wp_register_style(
            'laterpay-options',
            $this->config->css_url . 'laterpay-options.css',
            array(),
            $this->config->version
        );
        wp_enqueue_style( 'laterpay-options' );

        // Add thickbox to display modal.
        add_thickbox();

        // load page-specific JS
        wp_register_script(
            'laterpay-backend-options',
            $this->config->js_url . '/laterpay-backend-options.js',
            array( 'jquery', 'laterpay-common' ),
            $this->config->version,
            true
        );
        wp_enqueue_script( 'laterpay-backend-options' );

        $custom_role_names = array_keys( get_option( 'laterpay_unlimited_access', [] ) );

        // Localize string to be used in script.
        wp_localize_script(
            'laterpay-backend-options',
            'lpVars',
            array(
                'modal'  => array(
                    'id'    => 'lp_ga_modal_id',
                    'title' => esc_html__( 'Disable Tracking', 'laterpay' )
                ),
                'i18n'   => array(
                    'alertEmptyCode' => esc_html__( 'Please enter UA-ID to enable Personal Analytics!', 'laterpay' ),
                    'invalidCode'    => esc_html__( 'Please enter valid UA-ID code!', 'laterpay' ),
                ),
                'gaData' => array(
                    'custom_roles'        => $custom_role_names,
                    'sandbox_merchant_id' => ( ! empty( $merchant_key ) ) ? $merchant_key : '',
                ),
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
     * @return void
     */
    public function render_advanced_settings_page() {
        $this->load_assets();
        // pass variables to template
        $view_args = array(
            'settings_title' => __( 'LaterPay Advanced Settings', 'laterpay' ),
        );

        $this->assign( 'laterpay', $view_args );

        // render view template for options page
        $this->render( 'backend/options' );
    }

    /**
     * Configure content of LaterPay advanced settings page.
     *
     * @return void
     */
    public function init_laterpay_advanced_settings() {
        // add sections with fields
        $this->add_access_settings();
        $this->add_unlimited_access_settings();
        $this->add_analytics_settings();
        $this->add_appearance_settings();
        $this->add_technical_settings();
        $this->add_contact_section();
    }

    /**
     * Add LaterPay Access settings section and fields.
     *
     * @return void
     */
    public function add_access_settings() {
        add_settings_section(
            'laterpay_access',
            sprintf( esc_html__( '%s Access %s', 'laterpay' ), '<a href="#lpaccess" class="lp_options_a"><div id="lpaccess">', '</div></a>' ),
            array( $this, 'get_access_section_description' ),
            'laterpay'
        );

        // Require Login Setting.
        add_settings_field(
            'laterpay_require_login',
            esc_html__( 'Require User Login before Purchase', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_access',
            array(
                'name'    => 'laterpay_require_login',
                'value'   => 1,
                'type'    => 'checkbox',
                'tooltip' => true,
                'title'   => esc_html__( 'Require Login Information', 'laterpay' ),
                'modal'   => array(
                    'id'      => 'login_id',
                    'message' => esc_html__( 'Please choose if you want to require a login for "Pay Later" purchases.', 'laterpay' ),
                ),
            )
        );

        register_setting( 'laterpay', 'laterpay_require_login' );
    }

    /**
     * Get access section description
     *
     * @return void
     */
    public function get_access_section_description() {
        echo '<p>';
        esc_html_e( 'While most content access is controlled by LaterPay, in this section you can require users to log in prior to purchase or allow unlimited access to specific WordPress user roles (this feature can be useful for giving free access to existing subscribers or other stakeholders). We recommend the plugin \'User Role Editor\' for adding custom roles to WordPress.', 'laterpay' );
        echo '</p>';
    }

    /**
     * Add LaterPay Analytics settings section and fields.
     *
     * @return void
     */
    public function add_analytics_settings() {
        add_settings_section(
            'laterpay_analytics',
            sprintf( esc_html__( '%s Analytics %s', 'laterpay' ), '<a href="#lpanalytics" class="lp_options_a"><div id="lpanalytics">', '</div></a>' ),
            array( $this, 'get_analytics_section_description' ),
            'laterpay'
        );

        $user_tracking = $this->get_ga_tracking_value();

        // Get Value of Auto Detected Text if set.
        if ( ! empty( $user_tracking['auto_detected'] ) && 1 === (int) $user_tracking['auto_detected'] ) {
            $show_notice = 1;
        } else {
            $show_notice = 0;
        }

        // Add Personal GA Section.
        add_settings_field(
            'laterpay_user_tracking_data',
            esc_html__( 'Your Google Analytics:', 'laterpay' ),
            array( $this, 'get_ga_field_markup' ),
            'laterpay',
            'laterpay_analytics',
            array(
                array(
                    'name'        => 'laterpay_ga_personal_enabled_status',
                    'value'       => 1,
                    'type'        => 'checkbox',
                    'parent_name' => 'laterpay_user_tracking_data',
                ),
                array(
                    'name'        => 'laterpay_ga_personal_ua_id',
                    'type'        => 'text',
                    'classes'     => [ 'lp_ga-input' ],
                    'parent_name' => 'laterpay_user_tracking_data',
                    'show_notice' => $show_notice,
                )
            )
        );

        // Add LaterPay GA Section.
        add_settings_field(
            'laterpay_tracking_data',
            __( 'LaterPay Google Analytics:', 'laterpay' ),
            array( $this, 'get_ga_field_markup' ),
            'laterpay',
            'laterpay_analytics',
            array(
                array(
                    'name'        => 'laterpay_ga_enabled_status',
                    'value'       => 1,
                    'type'        => 'checkbox',
                    'parent_name' => 'laterpay_tracking_data',
                    'modal'       => array(
                        'id'         => 'lp_ga_modal_id',
                        'message'    => sprintf( '%1$s <br/><br/> %2$s',
                            esc_html__( 'LaterPay collects this information to improve our products and
                                        services and also so that you can determine the effectiveness of your pricing
                                        strategy using our Merchant Analytics dashboard.', 'laterpay' ),
                            esc_html__( 'Are you sure you would like to disable this feature?', 'laterpay' ) ),
                        'saveText'   => esc_html__( 'Yes, Disable Tracking', 'laterpay' ),
                        'cancelText' => esc_html__( 'Cancel', 'laterpay' ),
                    ),
                ),
                array(
                    'name'        => 'laterpay_ga_ua_id',
                    'type'        => 'text',
                    'classes'     => [ 'lp_ga-input' ],
                    'readonly'    => true,
                    'parent_name' => 'laterpay_tracking_data',
                )
            )
        );

        register_setting( 'laterpay', 'laterpay_user_tracking_data' );
        register_setting( 'laterpay', 'laterpay_tracking_data' );

    }

    /**
     * Get Google Analytics Track Section Description.
     *
     * @return void
     */
    public function get_analytics_section_description() {
        echo '<p>';
        printf(
            esc_html__( 'LaterPay is not in the business of selling data. This tracking information is for your benefit so that you can determine the effectiveness of your pricing strategy. %s
            To view your analytics, log in to your LaterPay account at %slaterpay.net%s to view your Merchant Analytics dashboard.', 'laterpay' ),
            "<br/>",
            "<a href='https://www.laterpay.net/' target='_blank'/>",
            "</a>"
        );
        echo '</p>';

        echo '<table class="form-table"><tr><th></th> <td>';
        esc_html_e( 'Enabled', 'laterpay' );
        echo '</td><td width="79%">';
        esc_html_e( 'Google Analytics "UA-ID"', 'laterpay' );
        echo '</td></tr></table>';
    }

    /**
     * Add LaterPay Appearance settings section and fields.
     *
     * @return void
     */
    public function add_appearance_settings() {
        add_settings_section(
            'laterpay_appearance',
            sprintf( esc_html__( '%s Appearance %s', 'laterpay' ), '<a href="#lpappearance" class="lp_options_a"><div id="lpappearance">', '</div></a>' ),
            array( $this, 'get_appearance_section_description' ),
            'laterpay'
        );

        // Teaser content word count Settings.
        add_settings_field(
            'laterpay_teaser_content_word_count',
            esc_html__( 'Default Teaser Content Word Count', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_appearance',
            array(
                'name'    => 'laterpay_teaser_content_word_count',
                'class'   => 'lp_number-input',
                'tooltip' => true,
                'title'   => esc_html__( 'Teaser Content Word Count', 'laterpay' ),
                'modal'   => array(
                    'id'      => 'teaser_word_count_id',
                    'message' => sprintf( esc_html__( 'The LaterPay WordPress plugin automatically generates teaser content for every paid post without teaser content. %1$s %1$s While technically possible, setting this parameter to zero is HIGHLY DISCOURAGED. %1$s %1$s If you really, really want to have NO teaser content for a post, enter one space into the teaser content editor for that post.', 'laterpay' ), '<br/>' ),
                    'style'   => 'font-size:24px',
                ),
            )

        );

        // Percentage of post content Settings.
        add_settings_field(
            'laterpay_preview_excerpt_percentage_of_content',
            esc_html__( 'Percentage of Post Blurred behind Overlay', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_appearance',
            array(
                'name'    => 'laterpay_preview_excerpt_percentage_of_content',
                'class'   => 'lp_number-input',
                'tooltip' => true,
                'title'   => esc_html__( 'Percentage of Post Content', 'laterpay' ),
                'modal'   => array(
                    'id'      => 'percentage_count_id',
                    'message' => esc_html__( 'Percentage of content to be extracted; 20 means "extract 20% of the total number of words of the post".', 'laterpay' ),
                    'style'   => 'font-size:24px',
                ),
            )
        );

        // Minimum Count Settings.
        add_settings_field(
            'laterpay_preview_excerpt_word_count_min',
            esc_html__( 'Minimum Number of Words Blurred behind Overlay', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_appearance',
            array(
                'name'    => 'laterpay_preview_excerpt_word_count_min',
                'class'   => 'lp_number-input',
                'tooltip' => true,
                'title'   => esc_html__( 'Minimum Number of Words', 'laterpay' ),
                'modal'   => array(
                    'id'      => 'minimum_word_count_id',
                    'message' => esc_html__( 'Applied if number of words as percentage of the total number of words is less than this value.', 'laterpay' ),
                    'style'   => 'font-size:24px',
                ),
            )
        );

        // Maximum Count Settings.
        add_settings_field(
            'laterpay_preview_excerpt_word_count_max',
            esc_html__( 'Maximum Number of Words Blurred behind Overlay', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_appearance',
            array(
                'name'    => 'laterpay_preview_excerpt_word_count_max',
                'class'   => 'lp_number-input',
                'tooltip' => true,
                'title'   => esc_html__( 'Maximum Number of Words', 'laterpay' ),
                'modal'   => array(
                    'id'      => 'minimum_word_count_id',
                    'message' => esc_html__( 'Applied if number of words as percentage of the total number of words exceeds this value.', 'laterpay' ),
                    'style'   => 'font-size:24px',
                ),
            )
        );

        register_setting( 'laterpay', 'laterpay_main_color' );
        register_setting( 'laterpay', 'laterpay_hover_color' );
        register_setting( 'laterpay', 'laterpay_teaser_content_word_count', 'absint' );
        register_setting( 'laterpay', 'laterpay_preview_excerpt_percentage_of_content', 'absint' );
        register_setting( 'laterpay', 'laterpay_preview_excerpt_word_count_min', 'absint' );
        register_setting( 'laterpay', 'laterpay_preview_excerpt_word_count_max', 'absint' );

    }

    /**
     * Get appearance section description
     *
     * @return void
     */
    public function get_appearance_section_description() {
        echo '<p>';
        esc_html_e( 'Our most common configuration options are found under the LaterPay plugin’s Appearance tab. Here you can adjust the number of characters automatically generated as your teaser content, and also the length of the content preview blurred behind our paywall.', 'laterpay' );
        echo '</p>';
    }

    /**
     * Add LaterPay Technical settings section and fields.
     *
     * @return void
     */
    public function add_technical_settings() {
        add_settings_section(
            'laterpay_technical',
            sprintf( esc_html__( '%s Technical %s', 'laterpay' ), '<a href="#lptechnical" class="lp_options_a"><div id="lptechnical">', '</div></a>' ),
            array( $this, 'get_technical_section_description' ),
            'laterpay'
        );

        add_settings_field(
            'laterpay_caching_compatibility',
            __( 'I am using a caching plugin (e.g. WP Super Cache or Cachify)', 'laterpay' ),
            array( $this, 'get_input_field_markup' ),
            'laterpay',
            'laterpay_technical',
            array(
                'name'  => 'laterpay_caching_compatibility',
                'value' => 1,
                'type'  => 'checkbox',
            )
        );

        $value   = absint( get_option( 'laterpay_api_fallback_behavior' ) );
        $options = self::get_laterpay_api_options();

        add_settings_field(
            'laterpay_api_fallback_behavior',
            __( 'In the case that the LaterPay API becomes unresponsive:', 'laterpay' ),
            array( $this, 'get_select_field_markup' ),
            'laterpay',
            'laterpay_technical',
            array(
                'name'          => 'laterpay_api_fallback_behavior',
                'value'         => $value,
                'options'       => $options,
                'id'            => 'lp_js_laterpayApiFallbackSelect',
                'appended_text' => isset( $options[ $value ] ) ? $options[ $value ]['description'] : '',
            )
        );

        register_setting( 'laterpay', 'laterpay_caching_compatibility' );
        register_setting( 'laterpay', 'laterpay_api_fallback_behavior' );
    }

    /**
     * Get technical section description.
     *
     * @return void
     */
    public function get_technical_section_description() {
        echo '<p>';
        printf(
            esc_html__( 'You MUST enable caching compatibility mode, if you are using a caching solution
           that caches entire HTML pages. %1$s In caching compatibility mode, the plugin works
           like this: %1$s It renders paid posts only with the teaser content. This allows to cache
           them as static files without risking to leak the paid content. %1$s When someone visits
           the page, it makes an Ajax request to determine, if the visitor has already bought the post
           and replaces the teaser with the full content, if required.', 'laterpay' ),
            '<br/>'
        );
        echo '</p>';
    }

    /**
     * Add LaterPay Contact settings section.
     *
     * @return void
     */
    public function add_contact_section() {
        add_settings_section(
            'laterpay_contact',
            sprintf( esc_html__( '%s Contact LaterPay Support %s', 'laterpay' ), '<a href="#lpcontact" class="lp_options_a"><div id="lpcontact">', '</div></a>' ),
            array( $this, 'get_contact_section_description' ),
            'laterpay'
        );
    }

    /**
     * Get contact section description.
     *
     * @return void
     */
    public function get_contact_section_description() {
        printf( esc_html__( 'Have questions or feature requests? %1$sClick here to contact LaterPay support%2$s', 'laterpay' ), '<a href="https://www.laterpay.net/contact-support">', '</a>' );
    }

    /**
     * Add unlimited access section and fields.
     *
     * @return void
     */
    public function add_unlimited_access_settings() {
        global $wp_roles;
        $custom_roles = array();

        $default_roles = array(
            'administrator',
            'editor',
            'contributor',
            'author',
            'subscriber',
        );

        $categories = array(
            'none' => esc_html__( 'none', 'laterpay' ),
            'all'  => esc_html__( 'all', 'laterpay' ),
        );

        $args = array(
            'hide_empty' => false,
            'taxonomy'   => 'category',
        );

        // get custom roles
        foreach ( $wp_roles->roles as $role => $role_data ) {
            if ( ! in_array( $role, $default_roles, true ) ) {
                $this->has_custom_roles = true;
                $custom_roles[ $role ]  = $role_data['name'];
            }
        }

        // get categories and add them to the array
        $wp_categories = get_categories( $args );
        foreach ( $wp_categories as $category ) {
            $categories[ $category->term_id ] = $category->name;
        }

        add_settings_section(
            'laterpay_unlimited_access',
            '',
            array( $this, 'get_unlimited_access_section_description' ),
            'laterpay'
        );

        register_setting( 'laterpay', 'laterpay_unlimited_access', array( $this, 'validate_unlimited_access' ) );

        // add options for each custom role
        foreach ( $custom_roles as $role => $name ) {
            add_settings_field(
                $role,
                sprintf( esc_html( '%s' . $name . '%s' ), '<span>', '</span>' ),
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
     * @return void
     */
    public function get_unlimited_access_section_description() {
        if ( $this->has_custom_roles ) {
            // show header
            echo '<table class="form-table"><tr><th class="lp_font">';
            esc_html_e( 'WordPress User Role', 'laterpay' );
            echo '</th><td>';
            esc_html_e( 'Receives unlimited access to:', 'laterpay' );
            echo '</td></tr></table>';
        } else {
            // tell the user that he needs to have at least one custom role defined
            echo '<h4>';
            esc_html_e( 'Please add a custom role first.', 'laterpay' );
            echo '</h4>';
        }
    }

    /**
     * Generic method to render input fields.
     *
     * @param array $field array of field params
     *
     * @return void
     */
    public function get_input_field_markup( $field = null ) {

        if ( $field && isset( $field['name'] ) ) {
            $option_value = get_option( $field['name'] );
            $field_value  = isset( $field['value'] ) ? $field['value'] : get_option( $field['name'], '' );
            $type         = isset( $field['type'] ) ? $field['type'] : 'text';
            $classes      = isset( $field['classes'] ) ? $field['classes'] : array();

            // clean 'class' data
            if ( ! is_array( $classes ) ) {
                $classes = array( $classes );
            }
            $classes = array_unique( $classes );

            if ( $type === 'text' ) {
                $classes[] = 'regular-text';
            }

            if ( isset( $field['label'] ) ) {
                echo '<label>';
            }

            echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $field['name'] ) . '" value="' . esc_attr( $field_value ) . '"';

            // add id, if set
            if ( isset( $field['id'] ) ) {
                echo ' id="' . esc_attr( $field['id'] ) . '"';
            }

            if ( isset( $field['label'] ) ) {
                echo ' style="margin-right:5px;"';
            }

            // add classes, if set
            if ( ! empty( $classes ) ) {
                echo ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
            }

            // add checked property, if set
            if ( 'checkbox' === $type ) {
                echo $option_value ? ' checked' : '';
            }

            // add disabled property, if set
            if ( isset( $field['disabled'] ) && $field['disabled'] ) {
                echo ' disabled';
            }

            // add onclick support
            if ( isset( $field['onclick'] ) && $field['onclick'] ) {
                echo ' onclick="' . esc_attr( $field['onclick'] ) . '"';
            }

            echo '>';

            if ( isset( $field['appended_text'] ) ) {
                echo '<dfn class="lp_appended-text">' . esc_html( $field['appended_text'] ) . '</dfn>';
            }
            if ( isset( $field['label'] ) ) {
                echo esc_html( $field['label'] );
                echo '</label>';
            }

            // Add Modal of input if data was provided.
            if ( isset( $field['tooltip'] ) && isset( $field['modal'] ) && isset( $field['title'] ) ) {
                $span_style = ( ! empty( $field['modal']['style'] ) ) ? safecss_filter_attr( $field['modal']['style'] ) : '';

                echo '<span data-icon="m" class="lp_option_icon" id="' . esc_attr( $field['modal']['id'] ) . '" title="' . esc_attr( $field['title'] ) . '" style="' . esc_attr( $span_style ) . '"></span>';
                echo '<div id="lp_' . esc_attr( $field['modal']['id'] ) . '" style="display:none;">';
                echo '<p>' . wp_kses( $field['modal']['message'], [ 'br' => [] ] ) . '</p>';
                echo '<button type="button" class="button button-primary lp_mt- lp_mb- lp_js_info_close">' . esc_html__( 'Close' ) . '</button>';
                echo '</div>';
            }
        }
    }

    /**
     * Generic method to render select fields.
     *
     * @param array $field array of field params
     *
     * @return void
     */
    public function get_select_field_markup( $field = null ) {

        if ( $field && isset( $field['name'] ) ) {
            $field_value = isset( $field['value'] ) ? $field['value'] : get_option( $field['name'] );
            $options     = isset( $field['options'] ) ? (array) $field['options'] : array();
            $classes     = isset( $field['class'] ) ? $field['class'] : array();
            if ( ! is_array( $classes ) ) {
                $classes = array( $classes );
            }

            if ( isset( $field['label'] ) ) {
                echo '<label>';
            }
            // remove duplicated classes
            $classes = array_unique( $classes );

            echo '<select name="' . esc_attr( $field['name'] ) . '"';

            if ( isset( $field['id'] ) ) {
                echo ' id="' . esc_attr( $field['id'] ) . '"';
            }

            if ( isset( $field['disabled'] ) && $field['disabled'] ) {
                echo ' disabled';
            }

            if ( ! empty( $classes ) ) {
                echo ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
            }

            echo '>';

            foreach ( $options as $option ) {
                if ( ! is_array( $option ) ) {
                    $option_value = $option_text = $option;
                } else {
                    $option_value = isset( $option['value'] ) ? $option['value'] : '';
                    $option_text  = isset( $option['text'] ) ? $option['text'] : '';
                }
                $selected = '';
                if ( absint( $field_value ) === absint( $option_value ) ) {
                    $selected = 'selected';
                }
                echo '<option value="' . esc_attr( $option_value ) . '" ' . esc_attr( $selected ) . '>' . esc_html( $option_text ) . '</option>';
            }

            echo '</select>';
            if ( isset( $field['appended_text'] ) ) {
                echo '<dfn class="lp_appended-text">' . esc_html( $field['appended_text'] ) . '</dfn>';
            }
            if ( isset( $field['label'] ) ) {
                echo esc_html( $field['label'] );
                echo '</label>';
            }
        }
    }

    /**
     * Render the inputs for the unlimited access section.
     *
     * @param array $field array of field parameters
     *
     * @return void
     */
    public function get_unlimited_access_markup( $field = null ) {
        $role       = isset( $field['role'] ) ? $field['role'] : null;
        $categories = isset( $field['categories'] ) ? $field['categories'] : array();
        $unlimited  = get_option( 'laterpay_unlimited_access' ) ? get_option( 'laterpay_unlimited_access' ) : array();

        $count = 1;

        if ( $role ) {
            foreach ( $categories as $id => $name ) {
                $need_default   = ! isset( $unlimited[ $role ] ) || ! $unlimited[ $role ];
                $is_none_or_all = in_array( $id, array( 'none', 'all' ), true );
                $is_selected    = ! $need_default ? in_array( (string) $id, $unlimited[ $role ], true ) : false;

                echo '<input type="checkbox" ';
                echo 'id="lp_category--' . esc_attr( $role . $count ) . '"';
                echo 'class="lp_category-access-input';
                echo $is_none_or_all ? ' lp_global-access" ' : '" ';
                echo 'name="laterpay_unlimited_access[' . esc_attr( $role ) . '][]"';
                echo 'value="' . esc_attr( $id ) . '" ';

                if ( $is_selected || ( $need_default && $id === 'none' ) ) {
                    echo 'checked';
                }

                echo '>';
                echo '<label class="lp_category-access-label';
                echo $is_none_or_all ? ' lp_global-access" ' : '" ';
                echo 'for="lp_category--' . esc_attr( $role . $count ) . '">';
                echo esc_html__( $name, 'laterpay' );
                echo '</label>';

                $count += 1;
            }
        }
    }

    /**
     * Validate unlimited access inputs before saving.
     *
     * @param $input
     *
     * @return array $valid array of valid values
     */
    public function validate_unlimited_access( $input ) {
        $valid = array();
        $args  = array(
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
                    if ( ! in_array( ( string ) $category->term_id, $data, true ) ) {
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
                if ( in_array( 'all', $data, true ) && in_array( 'none', $data, true ) && count( $data ) === 2 ) {
                    // unset option 'all', if option 'all' and option 'none' are selected at the same time
                    unset( $data[ array_search( 'all', $data, true ) ] );
                } elseif ( count( $data ) > 1 ) {
                    // unset option 'all', if at least one category is selected
                    if ( array_search( 'all', $data, true ) !== false ) {
                        foreach ( $data as $key => $option ) {
                            if ( ! in_array( $option, array( 'none', 'all' ), true ) ) {
                                unset( $data[ $key ] );
                            }
                        }
                    }

                    // unset all categories, if option 'none' is selected
                    if ( array_search( 'none', $data, true ) !== false ) {
                        foreach ( $data as $key => $option ) {
                            if ( ! in_array( $option, array( 'none', 'all' ), true ) ) {
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
     * Get LaterPay API options array.
     *
     * @return array
     */
    public static function get_laterpay_api_options() {
        return array(
            array(
                'value'       => '0',
                'text'        => esc_html__( 'Do nothing', 'laterpay' ),
                'description' => esc_html__( 'No user can access premium content while the LaterPay API is not responding.', 'laterpay' ),
            ),
            array(
                'value'       => '1',
                'text'        => esc_html__( 'Give full access', 'laterpay' ),
                'description' => esc_html__( 'All users have full access to premium content in order to not disappoint paying users.', 'laterpay' ),
            ),
            array(
                'value'       => '2',
                'text'        => esc_html__( 'Hide premium content', 'laterpay' ),
                'description' => esc_html__( 'Premium content is hidden from users. Direct access would be blocked.', 'laterpay' ),
            ),
        );
    }

    /**
     * Method to render ga fields.
     *
     * @param array $fields array of field params.
     *
     * @return void
     */
    public function get_ga_field_markup( $fields = null ) {

        if ( ! empty( $fields ) && is_array( $fields ) ) {
            foreach ( $fields as $field ) {
                if ( $field && isset( $field['parent_name'] ) ) {
                    $option_value = get_option( $field['parent_name'] );
                    $field_value  = isset( $option_value[ $field['name'] ] ) ? $option_value[ $field['name'] ] : '';
                    $type         = isset( $field['type'] ) ? $field['type'] : 'text';
                    $classes      = isset( $field['classes'] ) ? $field['classes'] : array();

                    // clean 'class' data.
                    if ( ! is_array( $classes ) ) {
                        $classes = array( $classes );
                    }
                    $classes = array_unique( $classes );

                    // add class if type is text.
                    if ( 'text' === $type ) {
                        $classes[] = 'regular-text';
                    }

                    // add label if set.
                    if ( isset( $field['label'] ) ) {
                        echo '<label>';
                    }

                    echo '<input type="' . esc_attr( $type ) . '" name="' . esc_attr( $field['parent_name'] . '[' . $field['name'] . ']' ) . '" value="' . esc_attr( $field_value ) . '"';

                    // add id, if set.
                    if ( isset( $field['id'] ) ) {
                        echo ' id="' . esc_attr( $field['id'] ) . '"';
                    }

                    if ( isset( $field['label'] ) ) {
                        echo ' style="margin-right:5px;"';
                    }

                    // add classes, if set.
                    if ( ! empty( $classes ) ) {
                        echo ' class="' . esc_attr( implode( ' ', $classes ) ) . '"';
                    }


                    // add checked property, if set.
                    if ( 'checkbox' === $type ) {
                        echo $field_value ? ' checked' : '';
                    }

                    // add disabled property, if set.
                    if ( isset( $field['disabled'] ) && $field['disabled'] ) {
                        echo ' disabled';
                    }

                    // add disabled property, if set.
                    if ( isset( $field['readonly'] ) && $field['readonly'] ) {
                        echo ' readonly';
                    }

                    // add onclick support.
                    if ( isset( $field['onclick'] ) && $field['onclick'] ) {
                        echo ' onclick="' . esc_attr( $field['onclick'] ) . '"';
                    }

                    echo '>';

                    // Display Auto Detected Text.
                    if ( 'text' === $type ) {
                        if ( isset( $field['show_notice'] ) && 1 === $field['show_notice'] ) {
                            echo '<span style="font-style: italic;font-weight: 600;">(' . esc_html__( 'auto detected', 'laterpay' ) . ')</span>';
                        }

                        // Update option and remove value.
                        $this->update_auto_detection_value( 0 );
                    }

                    // add extra text if set.
                    if ( isset( $field['appended_text'] ) ) {
                        echo '<dfn class="lp_appended-text">' . esc_html( $field['appended_text'] ) . '</dfn>';
                    }

                    if ( isset( $field['label'] ) ) {
                        echo esc_html( $field['label'] );
                        echo '</label>';
                    }

                    // add support for modal.
                    if ( isset( $field['modal'] ) ) {
                        echo '<div id="' . esc_attr( $field['modal']['id'] ) . '" style="display:none;">';
                        echo '<p>' . wp_kses( $field['modal']['message'], [ 'br' => [] ] ) . '</p>';
                        echo '<button class="lp_js_disableTracking button button-primary lp_mt- lp_mb-">' .
                             esc_html( $field['modal']['saveText'] ) . '</button>';
                        echo '<button type="button" class="button button-secondary lp_mt- lp_mb- lp_js_ga_cancel">' . esc_html( $field['modal']['cancelText'] ) . '</button>';
                        echo '</div>';
                    }
                }
            }
        }
    }

    /**
     * Get User Tracking Data.
     *
     * @return array
     */
    public function get_ga_tracking_value() {
        return get_option( 'laterpay_user_tracking_data', array() );
    }

    /**
     * Update option value fro User Tracking Data.
     *
     * @param int $status Status to set for auto detected property.
     *
     * @return void
     */
    public function update_auto_detection_value( $status ) {

        $user_tracking = $this->get_ga_tracking_value();

        if ( 0 === $status ) {
            unset( $user_tracking['auto_detected'] );
            update_option( 'laterpay_user_tracking_data', $user_tracking );
        }
    }

}
