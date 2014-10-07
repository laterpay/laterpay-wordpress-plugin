<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<ul class="lp_nav-tabs">
    <?php $num = 0; ?>
    <?php foreach ( $menu as $page ): ?>
        <?php
            $slug = ! $num ? $plugin_page : $page['url'];
        ?>
        <li<?php if ( $current_page == $page['url'] || ( ! $num && $current_page == $plugin_page ) ): ?> class="lp_current"<?php endif; ?>>
            <a href="<?php echo add_query_arg(array('page' => $slug), admin_url('admin.php'));?>" class="lp_d-block"<?php if ( $current_page == $page['url'] || ( ! $num && $current_page == $plugin_page ) ): ?> rel="prefetch"<?php endif; ?>>
                <?php echo $page['title']; ?>
            </a>
        </li>
        <?php $num++ ?>
    <?php endforeach; ?>
</ul>
