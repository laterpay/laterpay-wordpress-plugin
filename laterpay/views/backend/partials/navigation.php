<?php
if ( ! defined( 'ABSPATH' ) ) {
    // prevent direct access to this file
    exit;
}
?>

<ul class="lp_navigation_tabs">
<?php $num = 0; ?>
<?php foreach ( $laterpay[ 'menu' ] as $page ) : ?>
    <?php if ( ! current_user_can( $page[ 'cap' ] ) ) :
        continue;
    endif;
    $is_current_page    = false;
    $current_page_class = '';
    if ( $laterpay[ 'current_page' ] === $page[ 'url' ]
         || ( ! $num && $laterpay[ 'current_page' ] === $laterpay[ 'plugin_page' ] )
    ) :
        $is_current_page    = true;
        $current_page_class = 'lp_is-current';
    endif;
    ?>
    <li class="lp_navigation_tabs__item <?php echo $current_page_class; ?>">
        <?php echo LaterPay_Helper_View::get_admin_menu_link( $page ); ?>
        <?php if ( isset( $page[ 'submenu' ] ) ) : ?>
            <ul class="lp_navigation_tabs__submenu">
                <li class="lp_navigation_tabs__item">
                    <?php echo LaterPay_Helper_View::get_admin_menu_link( $page[ 'submenu' ] ); ?>
                </li>
            </ul>
        <?php endif; ?>
    </li>
    <?php $num ++ ?>
<?php endforeach; ?>
</ul>
