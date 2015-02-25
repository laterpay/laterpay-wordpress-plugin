<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<ul class="lp_navigation_tabs">
    <?php $num = 0; ?>
    <?php foreach ( $laterpay['menu'] as $page ): ?>
        <?php $slug = ! $num ? $laterpay['plugin_page'] : $page['url']; ?>
        <?php $is_current_page = $laterpay['current_page'] == $page['url'] || ( ! $num && $laterpay['current_page'] == $laterpay['plugin_page'] ); ?>
        <li class="lp_navigation_tabs__item<?php if ( $is_current_page ): ?> lp_is-current<?php endif; ?>">
            <a href="<?php echo add_query_arg( array( 'page' => $slug ), admin_url( 'admin.php' ) ); ?>"
                class="lp_u_block"<?php if ( $is_current_page ): ?> rel="prefetch"<?php endif; ?>>
                <?php echo $page['title']; ?>
            </a>
            <?php if ( isset( $page['submenu'] ) ): ?>
                <ul class="lp_navigation_tabs__submenu">
                    <li class="lp_navigation_tabs__item">
                        <a href="<?php echo add_query_arg( array( 'page' => $laterpay['plugin_page'] ), admin_url( 'admin.php' ) ); ?>"
                            <?php if ( isset( $page['submenu']['id'] ) ): ?>id="<?php echo $page['submenu']['id']; ?>"<?php endif; ?>
                            class="lp_u_block"
                            <?php if ( isset( $page['submenu']['data'] ) ): ?>data="<?php echo htmlspecialchars( json_encode( $page['submenu']['data'] ), ENT_QUOTES ); ?>"<?php endif; ?>>
                            <?php echo $page['submenu']['title']; ?>
                        </a>
                    </li>
                </ul>
            <?php endif; ?>
        </li>
        <?php $num++ ?>
    <?php endforeach; ?>
</ul>
