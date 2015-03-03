<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php
    // plugin menu pointer
    if ( in_array( LaterPay_Controller_Admin::ADMIN_MENU_POINTER, $laterpay['pointers'] ) ):
        $pointer_content = '<h3>' . __( 'Welcome to LaterPay', 'laterpay' ) . '</h3>';
        $pointer_content .= '<p>' . __( 'Set the most appropriate settings for you.', 'laterpay' ) . '</p>';
?>
    <script>
        jQuery(document).ready(function($) {
            if (typeof(jQuery().pointer) !== 'undefined') {
                jQuery('#toplevel_page_laterpay-plugin')
                .pointer({
                    content : '<?php echo $pointer_content; ?>',
                    position: {
                        edge: 'left',
                        align: 'middle'
                    },
                    close: function() {
                        jQuery.post( ajaxurl, {
                            pointer: '<?php echo LaterPay_Controller_Admin::ADMIN_MENU_POINTER; ?>',
                            action: 'dismiss-wp-pointer'
                        });
                    }
                })
                .pointer('open');
            }
        });
    </script>
<?php endif; ?>
<?php
    // add / edit post page - pricing box pointer
    if ( in_array( LaterPay_Controller_Admin::POST_PRICE_BOX_POINTER, $laterpay['pointers'] ) ):
        $pointer_content = '<h3>' . __( 'Set a Price for this Post', 'laterpay' ) . '</h3>';
        $pointer_content .= '<p>' . __( 'Set an <strong>individual price</strong> for this post here.<br>You can also apply <strong>advanced pricing</strong> by defining how the price changes over time.', 'laterpay' ) . '</p>';
?>
    <script>
        jQuery(document).ready(function($) {
            if (typeof(jQuery().pointer) !== 'undefined') {
                jQuery('#lp_postPricing')
                .pointer({
                    content: '<?php echo $pointer_content; ?>',
                    position: {
                        edge: 'top',
                        align: 'middle'
                    },
                    close: function() {
                        jQuery.post( ajaxurl, {
                            pointer: '<?php echo LaterPay_Controller_Admin::POST_PRICE_BOX_POINTER; ?>',
                            action: 'dismiss-wp-pointer'
                        });
                    }
                })
                .pointer('open');
            }
        });
    </script>
<?php endif; ?>
<?php
    // add / edit post page - teaser content pointer
    if ( in_array( LaterPay_Controller_Admin::POST_TEASER_CONTENT_POINTER, $laterpay['pointers'] ) ):
    $pointer_content = '<h3>' . __( 'Add Teaser Content', 'laterpay' ) . '</h3>';
    $pointer_content .= '<p>' . __( 'You´ll give your users a better impression of what they´ll buy, if you preview some text, images, or video from the actual post.', 'laterpay' ) . '</p>';
?>
    <script>
        jQuery(document).ready(function($) {
            if (typeof(jQuery().pointer) !== 'undefined') {
                jQuery('#lp_postTeaser')
                .pointer({
                    content: '<?php echo $pointer_content; ?>',
                    position: {
                        edge: 'bottom',
                        align: 'left'
                    },
                    close: function() {
                        jQuery.post( ajaxurl, {
                            pointer: '<?php echo LaterPay_Controller_Admin::POST_TEASER_CONTENT_POINTER; ?>',
                            action: 'dismiss-wp-pointer'
                        });
                    }
                })
                .pointer('open');
            }
        });
    </script>
<?php endif; ?>
