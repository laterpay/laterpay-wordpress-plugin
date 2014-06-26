<div class="lp-page wp-core-ui">

    <div id="message" style="display:none;">
        <p></p>
    </div>

    <div class="tabs-area">
        <?php if ( get_option('laterpay_plugin_mode_is_live') == 0 ): ?>
            <a href="#account" id="plugin-mode-indicator" data-icon="h">
                <h2><?php _e('<strong>Test</strong> mode', 'laterpay'); ?></h2>
                <span><?php _e('Earn money in <i>live mode</i>', 'laterpay'); ?></span>
            </a>
        <?php endif; ?>
        <ul class="tabs">
            <?php if ( get_option('laterpay_activate') == '0' ): ?>
                <li id="get-started-tab"><a href="#get_started"><?php _e('Get Started', 'laterpay'); ?></a></li>
            <?php endif; ?>
            <li><a href="#pricing"><?php _e('Pricing', 'laterpay'); ?></a></li>
            <li class="current"><a href="#appearance"><?php _e('Appearance', 'laterpay'); ?></a></li>
            <li><a href="#account"><?php _e('Account', 'laterpay'); ?></a></li>
        </ul>
    </div>

    <div class="lp-wrap">
        <div class="lp-form-row clearfix">
            <h2><?php _e('Preview of Paid Content', 'laterpay'); ?></h2>
            <form id="teaser_content_only" method="post">
                <input type="hidden" name="form"    value="teaser_content_only">
                <input type="hidden" name="action"  value="appearance">
                <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                <label class="left">
                    <input type="radio"
                            name="teaser_content_only"
                            value="1"
                            class="styled"
                            <?php if ( get_option('laterpay_teaser_content_only') == 1 ): ?>checked<?php endif; ?>/>
                    <?php _e('Teaser content only', 'laterpay'); ?>
                    <div class="preview-mode-1"></div>
                </label>
                <label class="left">
                    <input type="radio"
                            name="teaser_content_only"
                            value="0"
                            class="styled"
                            <?php if ( get_option('laterpay_teaser_content_only') == 0 ): ?>checked<?php endif; ?>/>
                    <?php _e('Teaser content + full content, covered by overlay', 'laterpay'); ?>
                    <div class="preview-mode-2"></div>
                </label>
            </form>
        </div>
        <hr>
        <div class="lp-form-row">
            <h2><?php _e('LaterPay Invoice Indicator', 'laterpay'); ?></h2>
            <dfn class="clearfix">
                <?php _e('Insert this HTML snippet into your theme to show your users their LaterPay invoice balance.', 'laterpay'); ?><br>
                <?php _e('The LaterPay invoice indicator is served by LaterPay. Its styling can not be changed.', 'laterpay'); ?>
            </dfn>
            <img src="<?php echo LATERPAY_ASSET_PATH; ?>/img/invoice-indicator.png" class="invoice-indicator-preview">
            <code class="invoice-snippet">
                <div class="triangle outer-triangle"><div class="triangle"></div></div>
                <?php echo htmlspecialchars("<iframe id='laterpay-invoice-indicator' width='110' height='30' frameborder='0' scrolling='no' src='$balance_url'></iframe>"); ?>
            </code>
        </div>
    </div>

</div>
