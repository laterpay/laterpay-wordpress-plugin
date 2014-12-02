<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<div class="lp_page wp-core-ui">

    <div id="lp_js_flashMessage" class="lp_flashMessage" style="display:none;">
        <p></p>
    </div>

    <div class="lp_navigation lp_u_relative">
        <?php if ( ! $laterpay['plugin_is_in_live_mode'] ): ?>
            <a href="<?php echo add_query_arg( array( 'page' => $laterpay['admin_menu']['account']['url'] ), admin_url( 'admin.php' ) ); ?>" class="lp_pluginModeIndicator lp_u_absolute" data-icon="h">
                <h2><?php _e( '<strong>Test</strong> mode', 'laterpay' ); ?></h2>
                <span><?php _e( 'Earn money in <i>live mode</i>', 'laterpay' ); ?></span>
            </a>
        <?php endif; ?>
        <?php echo $laterpay['top_nav']; ?>
    </div>

    <div class="lp_pagewrap">
        <div class="lp_row lp_u_clearfix">
            <h2><?php _e( 'Preview of Paid Content', 'laterpay' ); ?></h2>
            <form id="laterpay_paid_content_preview_form" method="post">
                <input type="hidden" name="form"    value="paid_content_preview">
                <input type="hidden" name="action"  value="laterpay_appearance">
                <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                <label class="lp_u_left">
                    <input type="radio"
                            name="paid_content_preview"
                            value="1"
                            class="lp_js_togglePreviewMode lp_js_styleInput"
                            <?php if ( $laterpay['show_teaser_content_only'] ): ?>checked<?php endif; ?>/>
                    <?php _e( 'Teaser content only', 'laterpay' ); ?>
                    <div class="lp_previewMode-1"></div>
                </label>
                <label class="lp_u_left">
                    <input type="radio"
                            name="paid_content_preview"
                            value="0"
                            class="lp_js_togglePreviewMode lp_js_styleInput"
                            <?php if ( ! $laterpay['show_teaser_content_only'] ): ?>checked<?php endif; ?>/>
                    <?php _e( 'Teaser content + full content, covered by overlay', 'laterpay' ); ?>
                    <div class="lp_previewMode-2"></div>
                </label>
            </form>
        </div>
        <hr class="lp_u_m-1-0 lp_u_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Enabled Post Types', 'laterpay' ); ?></h2>
            <dfn class="lp_u_block lp_u_m-b025"><?php _e( 'Check all built-in and custom post types, for which you want to enable purchases with LaterPay.', 'laterpay' ); ?></dfn>
            <form id="laterpay_enabled_post_types_form" method="post">
                <input type="hidden" name="form"    value="enabled_post_types">
                <input type="hidden" name="action"  value="laterpay_appearance">
                <?php wp_nonce_field('laterpay_form'); ?>
                <?php
                    $enabled_post_types = $config->get( 'content.enabled_post_types' );
                    $all_post_types     = get_post_types( array( 'public' => true ), 'objects' );
                    foreach ( $all_post_types as $slug => $post_type ) {
                ?>
                    <label for="supported_post_type_<?php echo $slug; ?>" class="lp_u_block lp_u_m-b025">
                        <input type="checkbox"
                               id="supported_post_type_<?php echo $slug; ?>"
                               name="enabled_post_types[]"
                               value="<?php echo $slug; ?>"
                               class="lp_js_styleInput"
                               <?php echo ( in_array( $slug, $enabled_post_types ) ) ? ' checked="checked" ' : ''; ?>
                        />
                        <?php echo $post_type->labels->name; ?>
                    </label>
                <?php
                    }
                ?>
            </form>
        </div>
        <hr class="lp_u_m-1-0 lp_u_m-b3">

        <div class="lp_row lp_u_clearfix lp_u_m-b1">
            <h2><?php _e( 'Rating of Purchased Content', 'laterpay' ); ?></h2>
            <img class="lp_uiElementPreview lp--large lp_u_left lp_u_m-t05 lp_u_m-r2" src="<?php echo $config->get( 'image_url' ) . 'content-rating-2x.png'; ?>">
            <div class="lp_u_m-t2">
                <?php _e( 'Content rating is', 'laterpay' ); ?><div class="lp_toggle">
                    <form id="lp_js_laterpayRatingsForm" method="post">
                        <input type="hidden" name="form"    value="ratings">
                        <input type="hidden" name="action"  value="laterpay_appearance">
                        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field('laterpay_form'); } ?>
                        <label class="lp_toggle_label">
                            <input type="checkbox"
                                   name="enable_ratings"
                                   id="lp_js_enableRatingsToggle"
                                   class="lp_toggle_input"
                                   <?php if ( $laterpay['is_rating_enabled'] ): ?>checked<?php endif; ?>>
                            <span class="lp_toggle_text" data-on="<?php _e( 'on', 'laterpay' ); ?>" data-off="<?php _e( 'off', 'laterpay' ); ?>"></span>
                            <span class="lp_toggle_handle"></span>
                        </label>
                    </form>
                </div>
            </div>
            <dfn class="lp_u_block">
                <?php _e( 'The opinion of others has a strong influence on buying decisions.', 'laterpay' ); ?><br>
                <?php _e( 'Content rating lets users rate your content on a five star scale after purchasing.', 'laterpay' ); ?><br>
                <?php _e( 'These ratings will be displayed to users who have not purchased that content yet as a quality indicator.', 'laterpay' ); ?>
            </dfn>
        </div>
        <hr class="lp_u_m-1-0 lp_u_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Offer of Paid Content within (Free) Posts', 'laterpay' ); ?></h2>
            <h3><?php _e( 'Offer of Additional Paid Content', 'laterpay' ); ?></h3>
            <dfn class="lp_u_clearfix">
                <?php _e( 'Insert shortcode [laterpay_premium_download] into a post to render a box for selling additional paid content.', 'laterpay' ); ?>
            </dfn>
            <code class="lp_codeSnippet lp--shownAbove lp_u_block">
                <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
                <?php _e( '[laterpay_premium_download target_post_id="<dfn>127</dfn>" target_post_title="<dfn>Event video footage</dfn>" content_type="<dfn>video</dfn>" teaser_image_path="<dfn>/uploads/images/concert-video-still.jpg</dfn>" heading_text="<dfn>Video footage of concert</dfn>" description_text="<dfn>Full HD video of the entire concert, including behind the scenes action.</dfn>"]', 'laterpay' ) ?>
            </code>
            <table class="lp_u_m-b1">
                <tr>
                    <td class="lp_u_pd-l0">
                        <img class="lp_uiElementPreview lp--large" src="<?php echo $config->get( 'image_url' ) . 'shortcode-2x.png'; ?>">
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td>
                                    <pre>target_post_id</pre>
                                </td>
                                <td>
                                    <?php _e( 'The ID of the post that contains the paid content.', 'laterpay'); ?><br>
                                    <dfn data-icon="n"><?php _e( 'Page IDs are unique within a WordPress blog and should thus be used instead of the target_post_title.<br> If both target_post_id and target_post_title are provided, the target_post_title will be ignored.', 'laterpay'); ?></dfn>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>target_post_title</pre>
                                </td>
                                <td>
                                    <?php _e( 'The title of the post that contains the paid content.', 'laterpay'); ?><br>
                                    <dfn data-icon="n"><?php _e( 'Changing the title of the linked post requires updating the shortcode accordingly.', 'laterpay'); ?></dfn>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <pre>content_type</pre>
                                </td>
                                <td>
                                    <?php _e( 'Content type of the linked content.', 'laterpay'); ?><br>
                                    <?php _e( 'Choose between \'audio\', \'video\', \'text\', \'gallery\', and \'file\' to display the corresponding default teaser image provided by the plugin.', 'laterpay'); ?>
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
            <dfn class="lp_u_clearfix">
                <?php _e( 'Enclose multiple [laterpay_premium_download] shortcodes in a [laterpay_box_wrapper] shortcode to align them in a three-column layout.', 'laterpay' ); ?>
            </dfn>
            <code class="lp_codeSnippet lp--shownAbove lp_u_block">
                <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
                <?php _e( '[laterpay_box_wrapper]<dfn>[laterpay_premium_download &hellip;][laterpay_premium_download &hellip;]</dfn>[/laterpay_box_wrapper]', 'laterpay' ) ?>
            </code>
            <img class="lp_uiElementPreview lp--large lp_u_m-t05" src="<?php echo $config->get( 'image_url' ) . 'shortcode-alignment-2x.png'; ?>">
        </div>
        <hr class="lp_u_m-1-0 lp_u_m-b3">

        <div class="lp_row">
            <h2><?php _e( 'Integration Into Theme', 'laterpay' ); ?></h2>
        </div>

        <div class="lp_row lp_u_clearfix lp_u_m-b1">
            <h3><?php _e( 'Position of LaterPay Purchase Button', 'laterpay' ); ?></h3>
            <dfn class="lp_u_clearfix">
                <?php _e( 'Call action \'laterpay_purchase_button\' in your theme to render the LaterPay purchase button in the location of your choice.', 'laterpay' ); ?><br>
                <?php _e( 'When not using this action, the purchase button is rendered in its default location between post title and post content.', 'laterpay' ); ?>
            </dfn>
            <img class="lp_uiElementPreview lp_u_left lp_u_m-0 lp_u_m-t1" src="data:image/png;base64,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">
            <code class="lp_codeSnippet lp_u_block">
                <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_purchase_button' ); ?>" ); ?>
            </code>
        </div>

        <div class="lp_row lp_u_clearfix lp_u_m-b1">
            <h3><?php _e( 'Display of LaterPay Invoice Balance', 'laterpay' ); ?></h3>
            <dfn class="lp_u_clearfix">
                <?php _e( 'Call action \'laterpay_invoice_indicator\' in your theme to show your users their LaterPay invoice balance.', 'laterpay' ); ?><br>
                <?php _e( 'The LaterPay invoice indicator is served by LaterPay. Its styling can not be changed.', 'laterpay' ); ?>
            </dfn>
            <img class="lp_uiElementPreview lp_u_left lp_u_m-0 lp_u_m-t1" src="data:image/png;base64,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">
            <code class="lp_codeSnippet lp_u_block">
                <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_invoice_indicator' ); ?>" ); ?>
            </code>
        </div>

        <div class="lp_row lp_u_clearfix lp_u_m-b1">
            <h3 id="lp_timePassAppearance"><?php _e( 'Display of Time Passes', 'laterpay' ); ?></h3>
            <dfn class="lp_u_clearfix">
                <?php _e( 'Call action \'laterpay_time_passes\' in your theme to show your users the available time passes.', 'laterpay' ); ?><br>
                <?php _e( 'The default width for time passes is 308px. By adding the parameter \'small\', they will be displayed with a width of 224px.', 'laterpay' ); ?>
            </dfn>
            <img class="lp_uiElementPreview lp_u_left lp_u_m-0 lp_u_m-t1" src="data:image/png;base64,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
">
            <code class="lp_codeSnippet lp_u_block">
                <div class="lp_triangle lp_outerTriangle"><div class="lp_triangle"></div></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_time_passes' ); ?>" ); ?>
                <div class="lp_u_center lp_u_m-1"><?php _e( 'or', 'laterpay' ); ?></div>
                <?php echo htmlspecialchars( "<?php do_action( 'laterpay_time_passes', 'small' ); ?>" ); ?>
            </code>
        </div>
    </div>

</div>
