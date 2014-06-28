<?php if ( current_user_can('manage_options') && !RequestHelper::isAjax() && LATERPAY_ACCESS_LOGGING_ENABLED && $is_premium_content ): ?>
    <div id="statistics">
        <h2 data-icon="a"><?php _e('Statistics for this Post', 'laterpay'); ?></h2>
        <div class="totals">
            <ul>
                <li>
                    <big><?php if ( isset($total[$currency]) ) { $aux = $total[$currency]['sum']; } else { $aux = 0; }; echo ViewHelper::formatNumber($aux, 2); ?><small><?php echo $currency; ?></small></big>
                    <small><?php _e('Total Revenue', 'laterpay'); ?></small>
                </li>
                <li>
                    <big><?php if ( isset($total[$currency]) ) { $aux = $total[$currency]['quantity']; } else { $aux = 0; }; echo $aux; ?></big>
                    <small><?php _e('Total Sales', 'laterpay'); ?></small>
                </li>
            </ul>
        </div>
        <div class="separator">
            <ul>
                <li><p><?php _e('Last 30 days', 'laterpay'); ?></p><hr></li>
                <li><p><?php _e('Today', 'laterpay'); ?></p><hr></li>
            </ul>
        </div>
        <div class="details">
            <ul>
                <li>
                    <span class="bar"><?php if ( isset($last30DaysRevenue[$currency]) ) { $aux = $last30DaysRevenue[$currency]; } else { $aux = array(); }; echo ViewHelper::getDaysStatisticAsString($aux, 'sum', ';'); ?></span>
                </li>
                <li>
                    <big><?php if ( isset($todayRevenue[$currency]) ) { $aux = $todayRevenue[$currency]['sum']; } else { $aux = 0; }; echo ViewHelper::formatNumber($aux, 2); ?><small><?php echo $currency; ?></small></big>
                    <small><?php _e('Revenue', 'laterpay'); ?></small>
                </li>
            </ul>
        </div>
        <div class="details">
            <ul>
                <li>
                    <span class="bar"><?php echo ViewHelper::getDaysStatisticAsString($last30DaysBuyers, 'percentage', ';'); ?></span>
                </li>
                <li>
                    <big><?php echo ViewHelper::formatNumber($todayBuyers, 1); ?><small>%</small></big>
                    <small><?php _e('Buyers', 'laterpay'); ?></small>
                </li>
            </ul>
        </div>
        <div class="details">
            <ul>
                <li>
                    <span class="bar"><?php echo ViewHelper::getDaysStatisticAsString($last30DaysVisitors, 'quantity', ';'); ?></span>
                </li>
                <li>
                    <big><?php echo $todayVisitors; ?></big>
                    <small><?php _e('Visitors', 'laterpay'); ?></small>
                </li>
            </ul>
        </div>
        <div id="plugin-visibility">
            <?php _e('Preview post as', 'laterpay'); ?> <strong><?php _e('Admin', 'laterpay'); ?></strong>
            <div class="switch">
                <form id="plugin_mode" method="post">
                    <input type="hidden" name="form"    value="post_page_preview">
                    <input type="hidden" name="action"  value="admin">
                    <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
                    <label class="switch-label">
                        <input type="checkbox"
                                name="preview_post_checkbox"
                                id="preview-post-toggle"
                                class="switch-input"
                                <?php if ( $preview_post_as_visitor == 1 ): ?>checked<?php endif; ?>>
                        <input type="hidden"
                                name="preview_post"
                                id="preview_post_hidden_input"
                                value="<?php if ( $preview_post_as_visitor == 1 ) { echo 1; } else { echo 0; } ?>">
                        <span class="switch-text" data-on="" data-off=""></span>
                        <span class="switch-handle"></span>
                    </label>
                </form>
            </div>
            <strong><?php _e('Visitor', 'laterpay'); ?></strong>
        </div>
    </div>
<?php endif; ?>

<?php // post is free or was already bought by user: ?>
<?php if ( (!$is_premium_content || $access == true) && !$preview_post_as_visitor ): ?>

    <?php echo $content; ?>

<?php // post is restricted: ?>
<?php else: ?>

    <p><?php echo $teaser_content; ?></p>

    <?php // preview only the teaser content -> add purchase link after teaser content ?>
    <?php if ( $teaser_content_only ): ?>

        <a class="laterpay-purchase-link" href="<?php echo $link; ?>" data-icon="b" post-id="<?php echo $post_id; ?>" data-preview-as-visitor="<?php echo $preview_post_as_visitor; ?>"><?php echo sprintf(__('Buy now for %s<small>%s</small> and pay later', 'laterpay'), ViewHelper::formatNumber($price, 2), $currency); ?></a>

    <?php // preview the teaser content plus real content, covered by overlay -> add concealed real content and purchase button ?>
    <?php else: ?>

        <div id="laterpay-paid-content" class="laterpay-paid-content">
            <div id="laterpay-full-content" class="laterpay-full-content">
                <?php echo StringHelper::truncate(
                        $content,
                        LATERPAY_PAID_CONTENT_PREVIEW_WORD_COUNT,
                        array(
                            'html'  => true,
                            'words' => true
                        )
                    ); ?>
            </div>
            <div class="laterpay-overlay-text">
                <div class="laterpay-benefits">
                    <h2><?php _e('Read Now, Pay Later', 'laterpay'); ?></h2>
                    <p><?php _e('Just agree to pay later and read instantly.', 'laterpay'); ?></p>
                    <ul class="clearfix">
                        <li>
                            <h3 class="logo-laterpay-icon" data-icon="b"><?php _e('Just pay later', 'laterpay'); ?></h3>
                            <p>
                                <?php _e('We ask you to pay only once all your LaterPay purchases reach 5 €.', 'laterpay'); ?>
                            </p>
                        </li>
                        <li>
                            <h3 class="icon-no-subscription" data-icon="i"><?php _e('No subscription', 'laterpay'); ?></h3>
                            <p>
                                <?php _e('There is no subscription or additional fee. You buy only this article.', 'laterpay'); ?>
                            </p>
                        </li>
                        <li>
                            <h3 class="icon-register-later" data-icon="j"><?php _e('No upfront<br>registration', 'laterpay'); ?></h3>
                            <p>
                                <?php _e('You get instant access to your purchase. Just register when you pay for the first time.', 'laterpay'); ?>
                            </p>
                        </li>
                    </ul>
                    <a href="<?php echo $link; ?>"
                        class="laterpay-purchase-link laterpay-purchase-button"
                        data-icon="b"
                        post-id="<?php echo $post_id; ?>"
                        data-preview-as-visitor="<?php echo $preview_post_as_visitor; ?>"
                        title="<?php _e('Buy now with LaterPay', 'laterpay'); ?>"><?php echo sprintf(__('%s<small>%s</small>', 'laterpay'), ViewHelper::formatNumber($price, 2), $currency); ?></a>
                    <div class="powered-by">
                        powered by<span data-icon="a"></span>
                    </div>
                </div>
            </div>
        </div>

    <?php endif; ?>

<?php endif; ?>
