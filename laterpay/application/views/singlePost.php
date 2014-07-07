<?php if ( $post_content_cached && !RequestHelper::isAjax() ): ?>
    <span id="laterpay-post-content" post-id="<?php echo $post_id; ?>"></span>
    <script type="text/javascript">
    (function($){
        $('#laterpay-post-content').hide();
        var post_id = $('#laterpay-post-content').attr('post-id');
        $.get(
            lpVars.getArticleUrl,
            {
                id              : post_id,
                show_statistic  : true
            },
            function(html) {
                $('#laterpay-post-content').before(html);
                $('#laterpay-post-content').remove();
                lpShowStatistic();
            }
        );
    })(jQuery);
    </script>
<?php else: ?>
    <?php if ( current_user_can('manage_options') && (!RequestHelper::isAjax() || $can_show_statistic) && LATERPAY_ACCESS_LOGGING_ENABLED && $is_premium_content ): ?>
        <div id="statistics"<?php if ( $hide_statistics_pane ) echo ' class="hidden"'; ?>>
            <form id="laterpay_hide_statistics_form" method="post">
                <input type="hidden" name="form"    value="hide_statistics_pane">
                <input type="hidden" name="action"  value="admin">
                <input type="hidden" name="hide_statistics_pane"  value="<?php echo $hide_statistics_pane;?>">
                <?php if ( function_exists('wp_nonce_field') ) wp_nonce_field('laterpay_form'); ?>
            </form>
            <a href="#" id="toggle-laterpay-statistics-pane" data-icon="l"></a>
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
                        <span class="bar" data-max="1"><?php echo ViewHelper::getDaysStatisticAsString($last30DaysBuyers, 'percentage', ';'); ?></span>
                        <span class="background-bar">1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1;1</span>
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
                    <!-- <?php _e('Preview a short excerpt from the paid post:', 'laterpay'); ?> -->
                    <?php echo StringHelper::truncate(
                            $content,
                            StringHelper::determine_number_of_words($content),
                            array(
                                'html'  => true,
                                'words' => true
                            )
                        ); ?>
                    <br>
                    <?php _e('Thanks for reading this short excerpt from the paid post! Fancy buying it to read all of it?', 'laterpay'); ?>
                </div>
                <div class="laterpay-overlay-text">
                    <div class="laterpay-benefits">
                        <header>
                            <h2>
                                <span data-icon="a"></span>
                                <?php _e('Read Now, Pay Later', 'laterpay'); ?>
                            </h2>
                        </header>
                        <ul class="clearfix">
                            <li class="laterpay-buy-now">
                                <h3><?php _e('Buy Now', 'laterpay'); ?></h3>
                                <p>
                                    <?php _e('Just agree to pay later.<br>No upfront registration and payment.', 'laterpay'); ?>
                                </p>
                            </li>
                            <li class="laterpay-use-immediately">
                                <h3><?php _e('Read Immediately', 'laterpay'); ?></h3>
                                <p>
                                    <?php _e('Get immediate access to your purchase.<br>are only buying this article, not a subscription.', 'laterpay'); ?>
                                </p>
                            </li>
                            <li class="laterpay-pay-later">
                                <h3><?php _e('Pay Later', 'laterpay'); ?></h3>
                                <p>
                                    <?php _e('Buy with LaterPay until you reach a total of 5 Euro.<br>Only then do you have to register and pay.', 'laterpay'); ?>
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
                            powered by<span data-icon="a"></span> beta
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>

    <?php endif; ?>
<?php endif; ?>
