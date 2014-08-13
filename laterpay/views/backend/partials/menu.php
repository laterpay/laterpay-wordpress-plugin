<?php if ( ! defined( 'ABSPATH' ) ) { exit; } ?>

<ul class="tabs">
    <?php $num = 0; ?>
    <?php foreach ( $menu as $page ): ?>
        <?php
            $slug = ! $num ? $plugin_page : $page['url'];
            if ( $activated === '' ) { // never activated before
                $slug = $plugin_page;
            }
        ?>
        <li <?php if ( $current_page == $page['url'] || ( ! $num && $current_page == $plugin_page ) ): ?>class="current"<?php endif; ?>>
            <a href="<?php echo add_query_arg(array('page' => $slug), admin_url('admin.php'));?>">
                <?php echo $page['title']; ?>
            </a>
        </li>
        <?php $num++ ?>
    <?php endforeach; ?>
</ul>
