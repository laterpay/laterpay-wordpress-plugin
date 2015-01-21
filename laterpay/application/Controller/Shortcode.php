<?php

class LaterPay_Controller_Shortcode extends LaterPay_Controller_Abstract
{

    /**
     * Contains all settings for the plugin.
     *
     * @var LaterPay_Model_Config
     */
    protected $config;

    /**
     * Render a teaser box for selling additional (downloadable) content from the shortcode [laterpay_premium_download].
     * Shortcode [laterpay] is an alias for shortcode [laterpay_premium_download].
     *
     * The shortcode [laterpay_premium_download] accepts various parameters:
     * - target_post_title: the title of the page that contains the paid content
     * - target_post_id: the WordPress id of the page that contains the paid content
     * - heading_text: the text that should be displayed as heading in the teaser box;
     *   restricted to one line
     * - description_text: text that provides additional information on the paid content;
     *   restricted to a maximum of three lines
     * - content_type: choose between 'text', 'music', 'video', 'gallery', or 'file',
     *   to display the corresponding default teaser image provided by the plugin;
     *   can be overridden with a custom teaser image using the teaser_image_path attribute
     * - teaser_image_path: path to an image that should be used instead of the default LaterPay teaser image
     *
     * Basic example:
     * [laterpay_premium_download target_post_title="Event video footage"]
     * or:
     * [laterpay_premium_download target_post_id="734"]
     *
     * Advanced example:
     * [laterpay_premium_download target_post_id="734" heading_text="Video footage of concert"
     * description_text="Full HD video of the entire concept, including behind the scenes action."
     * teaser_image_path="/uploads/images/concert-video-still.jpg"]
     *
     * @param array $atts
     *
     * @return string $html
     */
    public function render_premium_download_box( $atts ) {
        // check if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        // provide default values for empty shortcode attributes
        $a = shortcode_atts( array(
                                'target_post_id'    => '',
                                'target_post_title' => '',
                                'heading_text'      => __( 'Additional Premium Content', 'laterpay' ),
                                'description_text'  => '',
                                'content_type'      => '',
                                'teaser_image_path' => '',
                                // deprecated:
                                'target_page_id'    => '',
                                'target_page_title' => '',
                            ), $atts );

        $deprecated_template = __( '<code>%1$s</code> is deprecated, please use <code>%2$s</code>. <code>%1$s</code> will be removed in the next release.', 'laterpay' );

        // backward compatibility for attribute 'target_page_title'
        if ( ! empty( $a['target_page_title'] ) ) {
            $msg = sprintf( $deprecated_template, 'target_page_title', 'target_post_title' );

            _deprecated_argument(
                __FUNCTION__,
                '0.9.8.3',
                $msg
            );

            $this->logger->warning(
                __METHOD__ . ' - ' . $msg,
                array( 'attrs' => $a )
            );

            if ( empty( $a['target_post_title'] ) ) {
                $a[ 'target_post_title' ] = $a[ 'target_page_title' ];
            }
        }

        // backward compatibility for attribute 'target_page_id'
        if ( ! empty( $a['target_page_id'] ) ) {
            $msg = sprintf( $deprecated_template, 'target_page_id', 'target_post_id' );

            _deprecated_argument(
                __FUNCTION__,
                '0.9.8.3',
                $msg
            );

            $this->logger->warning(
                __METHOD__ . ' - ' . $msg,
                array( 'attrs' => $a )
            );

            if ( empty( $a['target_post_id'] ) ) {
                $a[ 'target_post_id' ] = $a[ 'target_page_id' ];
            }
        }

        $error_reason = '';

        // get URL for target page
        $page = null;
        if ( $a[ 'target_post_id' ] !== '' ) {
            $page = get_post( absint( $a[ 'target_post_id' ] ) );
        }
        // target_post_id was provided, but didn't work
        if ( $page === null && $a[ 'target_post_id' ] !== '' ) {
            $error_reason = sprintf(
                                    __( 'We couldn\'t find a page for target_post_id="%s" on this site.', 'laterpay' ),
                                    absint( $a[ 'target_post_id' ] )
                                    );
        }
        if ( $page === null && $a[ 'target_post_title' ] !== '' ) {
            $page = get_page_by_title( $a['target_post_title'], OBJECT, $this->config->get( 'content.enabled_post_types' ) );
        }
        // target_post_title was provided, but didn't work (no invalid target_post_id was provided)
        if ( $page === null && $error_reason == '' ) {
            $error_reason = sprintf(
                                    __( 'We couldn\'t find a page for target_post_title="%s" on this site.', 'laterpay' ),
                                    esc_html( $a[ 'target_post_title' ] )
                                    );
        }
        if ( $page === null ) {
            $error_message  = '<div class="lp_shortcodeError">';
            $error_message .= __( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= $error_reason;
            $error_message .= '</div>';

            $this->logger->error(
                __METHOD__ . ' - ' . $error_reason,
                array( 'args' => $a, )
            );

            return $error_message;
        }
        $page_id = $page->ID;

        // don't render the shortcode, if the target page has a post type for which LaterPay is disabled
        if ( ! in_array( $page->post_type, $this->config->get( 'content.enabled_post_types' ) ) )  {

            $error_reason = __( 'LaterPay has been disabled for the post type of the target page.', 'laterpay' );

            $error_message  = '<div class="lp_shortcodeError">';
            $error_message .= __( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= $error_reason;
            $error_message .= '</div>';

            $this->logger->error(
                __METHOD__ . ' - ' . $error_reason,
                array( 'args' => $a, )
            );

            return $error_message;
        }

        // check if page has a custom post type
        $custom_post_types      = get_post_types( array( '_builtin' => false ) );
        $custom_types           = array_keys( $custom_post_types );
        $is_custom_post_type    = ! empty( $custom_types ) && in_array( $page->post_type, $custom_types );

        // get the URL of the target page
        if ( $is_custom_post_type ) {
            // getting the permalink of a custom post type requires get_post_permalink instead of get_permalink
            $page_url = get_post_permalink( $page_id );
        } else {
            $page_url = get_permalink( $page_id );
        }

        // get price of content
        $price      = LaterPay_Helper_View::format_number( LaterPay_Helper_Pricing::get_post_price( $page_id ) );
        $currency   = get_option( 'laterpay_currency' );
        $price_tag  = sprintf( __( '%s<small>%s</small>', 'laterpay' ), $price, $currency );

        $content_types = array( 'file', 'gallery', 'audio', 'video', 'text' );

        if ( $a['content_type'] == '' ) {
            // determine $content_type from MIME Type of files attached to post
            $page_mime_type = get_post_mime_type( $page_id );

            switch ( $page_mime_type ) {
                case 'application/zip':
                case 'application/x-rar-compressed':
                case 'application/pdf':
                    $content_type = 'file';
                    break;

                case 'image/jpeg':
                case 'image/png':
                case 'image/gif':
                    $content_type = 'gallery';
                    break;

                case 'audio/vnd.wav':
                case 'audio/mpeg':
                case 'audio/mp4':
                case 'audio/ogg':
                case 'audio/aac':
                case 'audio/aacp':
                    $content_type = 'audio';
                    break;

                case 'video/mpeg':
                case 'video/mp4':
                case 'video/quicktime':
                    $content_type = 'video';
                    break;

                default:
                    $content_type = 'text';
            }
        } else if ( in_array( $a['content_type'], $content_types ) ) {
            $content_type = $a['content_type'];
        } else {
            $content_type = 'text';
        }

        // escape user input
        $image_path     = esc_url( $a['teaser_image_path'] );
        $heading        = esc_attr( $a['heading_text'] );
        $description    = esc_attr( $a['description_text'] );

        $this->logger->info(
            __METHOD__,
            array(
                'image_path'    => $image_path,
                'heading'       => $heading,
                'description'   => $description,
                'price_tag'     => $price_tag,
                'content_type'  => $content_type,
                'content_types' => $content_types,
            )
        );

        // build the HTML for the teaser box
        if ( $image_path != '' ) {
            $html = '<div class="lp_premiumFileBox" style="background-image:url(' . $image_path . ')">';
        } else {
            $html = '<div class="lp_premiumFileBox lp_contentType' . ucfirst( $content_type ) . '">';
        }
        // create a shortcode link
        $html .= $this->get_premium_shortcode_link( $page, $content_type, $page_url, $price_tag );
        $html .= '    <div class="lp_premiumFileDetails">';
        $html .= "        <h3>$heading</h3>";
        if ( $description != '' ) {
            $html .= "    <p>$description</p>";
        }
        $html .= '    </div>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Align multiple teaser boxes in a row when enclosing them in shortcode [laterpay_box_wrapper].
     *
     * Important: Avoid line breaks between the shortcodes as WordPress will replace them with <br> tags
     *
     * Example:
     * [laterpay_box_wrapper][laterpay_premium_download target_post_title="Vocabulary list"][laterpay_premium_download target_post_title="Excercises"][/laterpay_box_wrapper]
     *
     * @param  array   $atts
     * @param  string  $content
     *
     * @return string
     */
    function render_premium_download_box_wrapper( $atts, $content = null ) {
        // check if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }
        return '<div class="lp_premiumFileBox_wrapper lp_u_clearfix">' . do_shortcode( $content ) . '</div>';
    }

    /**
     * Create shortcode link.
     *
     * @param WP_Post   $post
     * @param string    $content_type
     * @param string    $page_url
     * @param string    $price_tag
     *
     * @return string
     */
    private function get_premium_shortcode_link( WP_Post $post, $content_type, $page_url, $price_tag ) {
        $html_button = '';

        $access = LaterPay_Helper_Post::has_access_to_post( $post );

        if ( $access ) {
            // the user has already purchased the item
            switch ( $content_type ) {
                case 'file':
                    $button_label = __( 'Download now', 'laterpay' );
                    break;

                case 'video':
                case 'gallery':
                    $button_label = __( 'Watch now', 'laterpay' );
                    break;

                case 'music':
                case 'audio':
                    $button_label = __( 'Listen now', 'laterpay' );
                    break;

                default:
                    $button_label = __( 'Read now', 'laterpay' );
                    break;
            };

            if ( $post->post_type == 'attachment' ) {
                // render link to purchased attachment
                $button_page_url = LaterPay_Helper_File::get_encrypted_resource_url(
                                                                                    $post->ID,
                                                                                    wp_get_attachment_url( $post->ID ),
                                                                                    $access,
                                                                                    'attachment'
                                                                                );
            } else {
                // render link to purchased post
                $button_page_url = $page_url;
            }
            $html_button =  '<a href="' . $button_page_url . '" ' .
                                'class="lp_purchaseLinkShortcode lp_purchaseLink lp_button" ' .
                                'rel="prefetch" ' .
                                'data-icon="b">' .
                                $button_label .
                            '</a>';
        } else {
            // the user has not purchased the item yet
            $view_args = LaterPay_Helper_Post::the_purchase_button_args( $post );
            if ( is_array( $view_args ) ) {
                $this->assign( 'laterpay', $view_args );
                $html_button = $this->get_text_view( 'frontend/partials/post/shortcode_purchase_button' );
            };
        }

        return $html_button;
    }

    /**
     * [render_time_passes_widget description]
     *
     * @param  [type] $atts [description]
     *
     * @return [type]         [description]
     */
    public function render_time_passes_widget( $atts ) {
        // array( $post_controller, 'the_time_passes_widget'), 10, 3 );
    }

    /**
     * Render gift cards for time passes from shortcode [laterpay_gift_card].
     *
     * The shortcode [laterpay_gift_card] accepts one optional parameter:
     * id: the id of the time pass that a user can buy a gift card for and give to someone else as a present
     * You can find the id of a time pass on the pricing page on the left side of the time pass (e.g. "Pass 3").
     * If no id is provided, the shortcode renders one giftcard for each defined time pass.
     *
     * Example:
     * [laterpay_gift_card id="1"]
     * or:
     * [laterpay_gift_card]
     *
     * @param array $atts
     *
     * @return string $html
     */
    public function render_gift_card( $atts ) {
        // check, if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        $data = shortcode_atts( array(
            'id' => null,
        ), $atts );

        // get a specific time pass, if an ID was provided; otherwise get all time passes
        if ( $data['id'] ) {
            $passes_list = $this->get_passes_list_by_id( $data['id'] );
        } else {
            $passes_list = LaterPay_Helper_Passes::get_all_passes();
        }

        // don't render any gift cards, if there are no time passes
        if ( ! $passes_list ) {
            $error_reason = __( 'Wrong time pass id or no time passes specified.', 'laterpay' );

            $error_message  = '<div class="lp_shortcodeError">';
            $error_message .= __( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= $error_reason;
            $error_message .= '</div>';

            $this->logger->error(
                __METHOD__ . ' - ' . $error_reason,
                array( 'args' => $atts, )
            );

            return $error_message;
        }

        $view_args = array(
            'passes_list'             => $passes_list,
            'standard_currency'       => get_option( 'laterpay_currency' ),
            'preview_post_as_visitor' => LaterPay_Helper_User::preview_post_as_visitor( get_post() ),
            'selected_pass_id'        => $data['id'],
        );
        $this->assign( 'laterpay', $view_args );

        $gift_cards = $this->get_text_view( 'frontend/partials/post/gift/gift_card' );

        return $gift_cards;
    }

    /**
     * Render a form to redeem a gift code for a time pass from shortcode [laterpay_redeem_voucher].
     * The shortcode renders an input and a button.
     * If the user enters his gift code and clicks the 'Redeem' button, a purchase dialog is opened,
     * where the user has to confirm the purchase of the associated time pass for a price of 0.00 Euro.
     * This step is done to ensure that this user accepts the LaterPay terms of use.
     *
     * @return string $html
     */
    public function render_redeem_gift_code( $atts ) {
        // check, if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        $data = shortcode_atts( array(
            'id' => null,
        ), $atts );

        // get a specific time pass, if an ID was provided; otherwise get all time passes
        if ( $data['id'] ) {
            $pass = (array) LaterPay_Helper_Passes::get_time_pass_by_id( $data['id'] );
            if ( ! $pass ) {
                $error_reason = __( 'Wrong time pass id.', 'laterpay' );

                $error_message  = '<div class="lp_shortcodeError">';
                $error_message .= __( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
                $error_message .= $error_reason;
                $error_message .= '</div>';

                $this->logger->error(
                    __METHOD__ . ' - ' . $error_reason,
                    array( 'args' => $atts, )
                );

                return $error_message;
            }
        } else {
            $pass = array();
        }

        $view_args = array(
            'pass_data'               => $pass,
            'standard_currency'       => get_option( 'laterpay_currency' ),
            'preview_post_as_visitor' => LaterPay_Helper_User::preview_post_as_visitor( get_post() ),
        );
        $this->assign( 'laterpay', $view_args );

        $gift_cards = $this->get_text_view( 'frontend/partials/post/gift/redeem_gift' );

        return $gift_cards;
    }

    /**
     * [add_free_codes_to_passes description]
     *
     * @param [type] $passes [description]
     */
    public function add_free_codes_to_passes( $passes, $link = null ) {
        if ( is_array( $passes ) ) {
            foreach ( $passes as $id => $pass ) {
                $pass = (array) $pass;
                // generate voucher code
                $code = LaterPay_Helper_Vouchers::generate_voucher_code();
                // create URL with this code
                $pass_id   = $pass['pass_id'];
                $url       = $link ? $link : get_permalink();
                $url_params = array(
                    'pass_id' => LaterPay_Helper_Passes::get_tokenized_pass( $pass_id ),
                    'voucher' => $code,
                    'is_gift' => true,
                    'next'    => $url,
                );
                $url  = add_query_arg( $url_params, $url );
                $hash = LaterPay_Helper_Pricing::get_hash_by_url( $url );
                $url  = $url .'&hash=' . $hash;

                $pass['url'] = LaterPay_Helper_Vouchers::get_laterpay_purchase_link( $pass_id, null, $code, $url );
                $passes[$id] = $pass;
            }
        }

        return $passes;
    }

    /**
     * TODO: [get_passes_list_by_id description]
     *
     * @param  [type] $id [description]
     *
     * @return [type]     [description]
     */
    public function get_passes_list_by_id( $id ) {
        $passes_list = (array) LaterPay_Helper_Passes::get_time_pass_by_id( $id );
        if ( $passes_list ) {
            $temp_arr = array();
            array_push( $temp_arr, $passes_list );
            $passes_list = $temp_arr;
        }

        return $passes_list;
    }

    /**
     * @hook wp_ajax_laterpay_get_gift_card_actions, wp_ajax_nopriv_laterpay_get_gift_card_actions
     */
    public function ajax_load_gift_action() {
        if ( ! isset( $_GET[ 'action' ] ) || $_GET[ 'action' ] !== 'laterpay_get_gift_card_actions' ) {
            exit;
        }

        if ( ! isset( $_GET[ 'nonce' ] ) || ! wp_verify_nonce( $_GET[ 'nonce' ], $_GET[ 'action' ] ) ) {
            exit;
        }

        if ( ! isset( $_GET[ 'pass_id' ] ) && ! isset( $GET[ 'link' ] ) ) {
            exit;
        }

        $data     = array();
        $pass_ids = $_GET[ 'pass_id' ];

        foreach ( $pass_ids as $pass_id ) {
            $passes       = $pass_id ? $this->get_passes_list_by_id( $pass_id ) : LaterPay_Helper_Passes::get_all_passes();
            $access       = LaterPay_Helper_Post::has_purchased_gift_card();
            $landing_page = get_option( 'laterpay_landing_page');

            // add gift codes with urls to passes
            $passes       = $this->add_free_codes_to_passes( $passes, $_GET[ 'link'] );
            $view_args = array(
                'gift_code'               => is_array( $access ) ? $access['code'] : null,
                'landing_page'            => $landing_page ? $landing_page : home_url(),
                'preview_post_as_visitor' => LaterPay_Helper_User::preview_post_as_visitor( get_post() ),
                'standard_currency'       => get_option( 'laterpay_currency' ),
            );

            foreach ( $passes as $pass ) {
                $has_access = is_array( $access ) && $access['access'] && $access['pass_id'] == $pass['pass_id'];
                $additional_args = array(
                    'pass'       => $pass,
                    'has_access' => $has_access,
                );
                $this->assign( 'laterpay', array_merge( $view_args, $additional_args ) );

                $html = LaterPay_Helper_View::remove_extra_spaces( $this->get_text_view( 'frontend/partials/post/gift/gift_actions' ) );
                $info = array(
                    'html'     => $html,
                    'id'       => $pass['pass_id'],
                    'buy_more' => null,
                );

                if ( $has_access ) {
                    $label = __( 'Buy another gift card', 'laterpay' );
                    $html  = '<a href="#" class="lp_gift-card__buy-another">' . $label . '</a>';
                    $info['buy_more'] = $html;
                }

                if ( ! isset( $data[$pass['pass_id']] ) ) {
                    $data[$pass['pass_id']] = $info;
                }
            }
        }

        wp_send_json(
            array(
                'data' => array_values( $data ),
            )
        );
    }

    /**
     * Render a form to log in to or out of your LaterPay account from shortcode [laterpay_account_links].
     *
     * The shortcode renders an iframe with a link that opens the login dialog from LaterPay.
     * It accepts various parameters:
     * - css: full path to a CSS file for styling the form contained by the iframe
     * - forcelang: locale string to force a specific language for the dialog
     * - show: rendering options for the form as documented on https://laterpay.net/developers/docs/inpage-api#GET/controls/links
     * - next: URL the user is forwarded to after login
     *
     * Basic example:
     * [laterpay_account_links]
     *
     * Advanced example:
     * [laterpay_account_links css="http://assets.yoursite.com/your-styles.css" forcelang="de"]
     *
     * @param array $atts
     *
     * @return string $html
     */
    public function render_account_links( $atts ) {
        // check, if the plugin is correctly configured and working
        if ( ! LaterPay_Helper_View::plugin_is_working() ) {
            return;
        }

        // provide default values for empty shortcode attributes
        $data = shortcode_atts( array(
            'show'      => 'l',
            'css'       => $this->config->get( 'css_url' ) . 'laterpay-account-links.css',
            'next'      => is_singular() ? get_permalink() : home_url(),
            'forcelang' => null,
        ), $atts );

        $view_args = array(
            'show'      => $data['show'],
            'css'       => $data['css'],
            'next'      => $data['next'],
            'forcelang' => $data['forcelang'],
        );
        $this->assign( 'laterpay', $view_args );

        $login = $this->get_text_view( 'frontend/partials/post/account_links_iframe' );

        return $login;
    }
}
