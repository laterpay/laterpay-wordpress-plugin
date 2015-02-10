<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<ul class="lp_navigation_tabs">
    <?php $num = 0; ?>
    <?php foreach ( $laterpay['menu'] as $page ): ?>
        <?php $slug = ! $num ? $laterpay['plugin_page'] : $page['url']; ?>
        <?php $is_current_page = $laterpay['current_page'] == $page['url'] || ( ! $num && $laterpay['current_page'] == $laterpay['plugin_page'] ); ?>
        <li<?php if ( $is_current_page ): ?> class="lp_current"<?php endif; ?>>
            <a href="<?php echo add_query_arg( array( 'page' => $slug ), admin_url( 'admin.php' ) ); ?>"
                class="lp_u_block"<?php if ( $is_current_page ): ?> rel="prefetch"<?php endif; ?>>
                <?php echo $page['title']; ?>
            </a>
        </li>
        <?php $num++ ?>
    <?php endforeach; ?>
</ul>
