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
     * - target_page_title: the title of the page that contains the paid content
     * - target_page_id: the WordPress id of the page that contains the paid content
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
     * [laterpay_premium_download target_page_title="Event video footage"]
     * or:
     * [laterpay_premium_download target_page_id="734"]
     *
     * Advanced example:
     * [laterpay_premium_download target_page_id="734" heading_text="Video footage of concert"
     * description_text="Full HD video of the entire concept, including behind the scenes action."
     * teaser_image_path="/uploads/images/concert-video-still.jpg"]
     *
     * @param array $atts
     *
     * @return string $html
     */
    public function render_premium_download_box( $atts ) {
        $a = shortcode_atts(array(
                                'target_page_title' => '',
                                'target_page_id'    => '',
                                'target_page_title' => '',
                                'heading_text'      => __( 'Additional Premium Content', 'laterpay' ),
                                'description_text'  => '',
                                'content_type'      => '',
                                'teaser_image_path' => '',
                            ), $atts);

        $error_reason = '';

        // get URL for target page
        $page = null;
        if ( $a[ 'target_page_id' ] !== '' ) {
            $page = get_post( absint( $a[ 'target_page_id' ] ) );
        }
        // target_page_id was provided, but didn't work
        if ( $page === null && $a[ 'target_page_id' ] !== '' ) {
            $error_reason = sprintf(
                                    __( 'We couldn\'t find a page for target_page_id="%s" on this site.', 'laterpay' ),
                                    absint( $a[ 'target_page_id' ] )
                                    );
        }
        if ( $page === null && $a[ 'target_page_title' ] !== '' ) {
            $page = get_page_by_title( $a['target_page_title'], OBJECT, $this->config->get( 'content.allowed_post_types' ) );
        }
        // target_page_title was provided, but didn't work (no invalid target_page_id was provided)
        if ( $page === null && $error_reason == '' ) {
            $error_reason = sprintf(
                                    __( 'We couldn\'t find a page for target_page_title="%s" on this site.', 'laterpay' ),
                                    esc_html( $a[ 'target_page_title' ] )
                                    );
        }
        if ( $page === null ) {
            $error_message  = '<div class="laterpay-shortcode-error">';
            $error_message .= __( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= $error_reason;
            $error_message .= '</div>';

            return $error_message;
        }
        $page_id = $page->ID;

        // don't render the shortcode, if the target page has a post type for which LaterPay is disabled
        if ( ! in_array( $page->post_type, $this->config->get( 'content.allowed_post_types' ) ) )  {
            $error_message  = '<div class="laterpay-shortcode-error">';
            $error_message .= __( 'Problem with inserted shortcode:', 'laterpay' ) . '<br>';
            $error_message .= __( 'LaterPay has been disabled for the post type of the target page.', 'laterpay' );
            $error_message .= '</div>';

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
        $price      = LaterPay_Helper_View::format_number( LaterPay_Helper_Pricing::get_post_price( $page_id ), 2 );
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

        // build the HTML for the teaser box
        if ( $image_path != '' ) {
            $html = "<div class=\"laterpay-premium-file-link\" style=\"background-image:url($image_path)\">";
        } else {
            $html = "<div class=\"laterpay-premium-file-link $content_type\">";
        }
        $html .= "    <a href=\"$page_url\" class=\"laterpay-premium-file-button\" data-icon=\"b\">$price_tag</a>";
        $html .= '    <div class="details">';
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
     * [laterpay_box_wrapper][laterpay_premium_download target_page_title="Vocabulary list"][laterpay_premium_download target_page_title="Excercises"][/laterpay_box_wrapper]
     *
     * @param  array   $atts
     * @param  string  $content
     *
     * @return string
     */
    function render_premium_download_box_wrapper( $atts, $content = null ) {
        return '<div class="laterpay-premium-file-link-wrapper">' . do_shortcode( $content ) . '</div>';
    }

}
