<?php
    if ( ! defined( 'ABSPATH' ) ) {
        // prevent direct access to this file
        exit;
    }
?>

<?php if ( ! $laterpay['user_has_already_voted'] ) : ?>
    <form class="lp_js_ratingForm lp_clearfix" method="post">
        <input type="hidden" name="action" value="laterpay_post_rate_purchased_content">
        <input type="hidden" name="post_id" value="<?php echo $laterpay['post_id']; ?>">
        <?php if ( function_exists( 'wp_nonce_field' ) ) { wp_nonce_field( 'laterpay_form' ); } ?>
        <div class="lp_rating">
            <?php echo __( 'Please rate this post:', 'laterpay' ); ?>
            <input type="radio" name="rating_value" id="lp_rating__input-5" class="lp_rating__input" value="5">
            <label for="lp_rating__input-5" class="lp_rating__star" title="5 <?php echo __( 'stars', 'laterpay' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

            </label>
            <input type="radio" name="rating_value" id="lp_rating__input-4" class="lp_rating__input" value="4">
            <label for="lp_rating__input-4" class="lp_rating__star" title="4 <?php echo __( 'stars', 'laterpay' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

            </label>
            <input type="radio" name="rating_value" id="lp_rating__input-3" class="lp_rating__input" value="3">
            <label for="lp_rating__input-3" class="lp_rating__star" title="3 <?php echo __( 'stars', 'laterpay' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

            </label>
            <input type="radio" name="rating_value" id="lp_rating__input-2" class="lp_rating__input" value="2">
            <label for="lp_rating__input-2" class="lp_rating__star" title="2 <?php echo __( 'stars', 'laterpay' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

            </label>
            <input type="radio" name="rating_value" id="lp_rating__input-1" class="lp_rating__input" value="1">
            <label for="lp_rating__input-1" class="lp_rating__star" title="1 <?php echo __( 'star', 'laterpay' ); ?>">
                <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" x="0px" y="0px" viewBox="0 0 14 14" enable-background="new 0 0 14 14" xml:space="preserve"><polygon class="lp_star__full" points="7,0.4 9.2,4.8 14,5.5 10.5,8.9 11.3,13.7 7,11.4 2.7,13.7 3.5,8.9 0,5.5 4.8,4.8"/><polygon class="lp_star__half" points="7,11.4 7,0.4 4.8,4.8 0,5.4 3.5,8.9 2.7,13.6"/></svg>

            </label>
        </div>
    </form>
<?php endif; ?>
