<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flash-message" class="lp_flash-message" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_p-rel">
        <?php if ( ! $plugin_is_in_live_mode ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $admin_menu['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_plugin-mode-indicator lp_p-abs" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $top_nav; ?>
    </div>

    <div class="lp_pagewrap">
        <div class="lp_row lp_fl-clearfix">
            <h2><?php _e( 'Preview of Paid Content', 'laterpay' ); ?></h2>
            <form id="laterpay_paid_content_preview_form" method="post">
                <input type="hidden" name="form"    value="paid_content_preview">
                <input type="hidden" name="action"  value="laterpay_appearance">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                <label class="lp_fl-left">
                    <input type="radio"
                            name="paid_content_preview"
                            value="1"
                            class="lp_js_toggle-preview-mode lp_js_style-input"
                            <?php if ( $show_teaser_content_only ): ?>checked<?php endif; ?>/>
                    <?php _e( 'Teaser content only', 'laterpay' ); ?>
                    <div class="lp_preview-mode-1"></div>
                </label>
                <label class="lp_fl-left">
                    <input type="radio"
                            name="paid_content_preview"
                            value="0"
                            class="lp_js_toggle-preview-mode lp_js_style-input"
                            <?php if ( ! $show_teaser_content_only ): ?>checked<?php endif; ?>/>
                    <?php _e( 'Teaser content + full content, covered by overlay', 'laterpay' ); ?>
                    <div class="lp_preview-mode-2"></div>
                </label>
            </form>
        </div>
        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Enabled Post Types', 'laterpay' ); ?></h2>
            <dfn class="lp_d-block lp_m-b025"><?php _e( 'Check all built-in and custom post types, for which you want to enable purchases with LaterPay.', 'laterpay' ); ?></dfn>
            <form id="laterpay_enabled_post_types_form" method="post">
                <input type="hidden" name="form"    value="enabled_post_types">
                <input type="hidden" name="action"  value="laterpay_appearance">
                <?php wp_nonce_field('laterpay_form'); ?>
                <?php
                    $enabled_post_types = $config->get( 'content.enabled_post_types' );
                    $all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
                    foreach ( $all_post_types as $slug => $post_type ) {
                ?>
                    <label for="supported_post_type_<?php echo $slug; ?>" class="lp_d-block lp_m-b025">
                        <input type="checkbox"
                               id="supported_post_type_<?php echo $slug; ?>"
                               name="enabled_post_types[]"
                               value="<?php echo $slug; ?>"
                               class="lp_js_style-input"
                               <?php echo ( in_array( $slug, $enabled_post_types ) ) ? ' checked="checked" ' : ''; ?>
                        />
                        <?php echo $post_type->labels->name; ?>
                    </label>
                <?php
                    }
                ?>
            </form>
        </div>
        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Offer of Paid Content within (Free) Posts', 'laterpay' ); ?></h2>
            <h3><?php _e( 'Offer of Additional Paid Content', 'laterpay' ); ?></h3>
            <dfn class="lp_fl-clearfix">
                <?php _e( 'Insert shortcode [laterpay_premium_download] into a post to render a box for selling additional paid content.', 'laterpay' ); ?>
            </dfn>
            <code class="lp_code-snippet lp_shown-above lp_d-block">
                <div class="lp_triangle lp_outer-triangle"><div class="lp_triangle"></div></div>
                <?php _e( '[laterpay_premium_download target_page_id="<dfn>127</dfn>" target_page_title="<dfn>Event video footage</dfn>" content_type="<dfn>video</dfn>" teaser_image_path="<dfn>/uploads/images/concert-video-still.jpg</dfn>" heading_text="<dfn>Video footage of concert</dfn>" description_text="<dfn>Full HD video of the entire concert, including behind the scenes action.</dfn>"]', 'laterpay' ) ?>
            </code>
            <table class="lp_m-b1">
                <tr>
                    <td class="lp_pd-l0">
                        <img class="lp_ui-element-preview-large" src="<?php echo $config->get( 'image_url' ) . 'shortcode-2x.png'; ?>">
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <pre>target_page_id</pre>
                                </td>
                                <td>
                                    <?php _e( 'The ID of the page that contains the paid content.', 'laterpay'); ?><br>
                                    <dfn data-icon="n"><?php _e( 'Page IDs are unique within a WordPress blog and should thus be used instead of the target_page_title.<br> If both target_page_id and target_page_title are provided, the target_page_title will be ignored.', 'laterpay'); ?></dfn>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>target_page_title</pre>
                                </td>
                                <td>
                                    <?php _e( 'The title of the page that contains the paid content.', 'laterpay'); ?><br>
                                    <dfn data-icon="n"><?php _e( 'Changing the title of the linked post requires updating the shortcode accordingly.', 'laterpay'); ?></dfn>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>content_type</pre>
                                </td>
                                <td>
                                    <?php _e( 'Content type of the linked content.', 'laterpay'); ?><br>
                                    <?php _e( 'Choose between \'music\', \'video\', \'text\', \'gallery\', and \'file\' to display the corresponding default teaser image provided by the plugin.', 'laterpay'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>teaser_image_path</pre>
                                </td>
                                <td>
                                    <?php _e( 'Path to a 300 x 300 px image that should be used instead of the default LaterPay teaser image.', 'laterpay'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>heading_text</pre>
                                </td>
                                <td>
                                    <?php _e( 'Text that should be displayed as heading in the box rendered by the shortcode. The heading is limited to one line.', 'laterpay'); ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>description_text</pre>
                                </td>
                                <td>
                                    <?php _e( 'Text that provides additional information on the paid content.', 'laterpay'); ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
        <div class="lp_row">
            <h3><?php _e( 'Alignment of Additional Paid Content Boxes', 'laterpay' ); ?></h3>
            <dfn class="lp_fl-clearfix">
                <?php _e( 'Enclose multiple [laterpay_premium_download] shortcodes in a [laterpay_box_wrapper] shortcode to align them in a three-column layout.', 'laterpay' ); ?>
            </dfn>
            <code class="lp_code-snippet lp_shown-above lp_d-block">
                <div class="lp_triangle lp_outer-triangle"><div class="lp_triangle"></div></div>
                <?php _e( '[laterpay_box_wrapper]<dfn>[laterpay_premium_download &hellip;][laterpay_premium_download &hellip;]</dfn>[/laterpay_box_wrapper]', 'laterpay' ) ?>
            </code>
            <img class="lp_ui-element-preview-large lp_m-t05" src="<?php echo $config->get( 'image_url' ) . 'shortcode-alignment-2x.png'; ?>">
        </div>
        <hr class="lp_m-1-0 lp_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Integration Into Theme', 'laterpay' ); ?></h2>
        </div>

        <div class="lp_row lp_fl-clearfix lp_m-b1">
            <h3><?php _e( 'Position of LaterPay Purchase Button', 'laterpay' ); ?></h3>
            <dfn class="lp_fl-clearfix">
                <?php _e( 'Call action \'laterpay_purchase_button\' in your theme to render the LaterPay purchase button in the location of your choice.', 'laterpay' ); ?><br>
                <?php _e( 'When not using this action, the purchase button is rendered in its default location between post title and post content.', 'laterpay' ); ?>
            </dfn>
            <img class="lp_ui-element-preview-small lp_fl-left lp_m-0 lp_m-t1" src="data:image/png;base64,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">
            <code class="lp_code-snippet lp_d-block">
                <div class="lp_triangle lp_outer-triangle"><div class="lp_triangle"></div></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_purchase_button' ); ?>" ); ?>
            </code>
        </div>

        <div class="lp_row lp_fl-clearfix lp_m-b1">
            <h3><?php _e( 'Display of LaterPay Invoice Balance', 'laterpay' ); ?></h3>
            <dfn class="lp_fl-clearfix">
                <?php _e( 'Call action \'laterpay_invoice_indicator\' in your theme to show your users their LaterPay invoice balance.', 'laterpay' ); ?><br>
                <?php _e( 'The LaterPay invoice indicator is served by LaterPay. Its styling can not be changed.', 'laterpay' ); ?>
            </dfn>
            <img class="lp_ui-element-preview-small lp_fl-left lp_m-0 lp_m-t1" src="data:image/png;base64,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">
            <code class="lp_code-snippet lp_d-block">
                <div class="lp_triangle lp_outer-triangle"><div class="lp_triangle"></div></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_invoice_indicator' ); ?>" ); ?>
            </code>
        </div>
    </div>

</div>
