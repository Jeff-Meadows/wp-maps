<?php if (isset($_GET['embed']) && ($_GET['embed'] === 'colgankids' || $_GET['embed'] === 'rrwa' )) { } else { ?>
<div id="wv-header-tab-bar" class="wv-header-dropdown" data-tab="false">
    <?php if (isset($_GET['embed']) && $_GET['embed'] === 'watershed-classroom') : ?>
        <div id="wv-header-infobar" class="wv-header-tab-bar">
            <?php dynamic_sidebar( 'watershedview_info_sidebar' ); ?>
        </div>
        <div id="wv-header-map-infobar" class="wv-header-tab-bar">
            <?php //dynamic_sidebar( 'watershedview_map_info_sidebar' ); ?>
            <?php
            while (have_posts()) : the_post();
                switch ($post->ID) {
                    case 22: //Russian River
                        $postId = '364';
                        break;
                    case 20: //Colgan Creek
                        $postId = '366';
                        break;
                    case 24: //Petaluma River
                        $postId = '343';
                        break;
                }
                ?>
            <span class="wv-info">
                <?php echo do_shortcode('[content_block id='.$postId.']'); ?>
            </span>
            <?php endwhile;
            wp_reset_query();
            ?>
        </div>
    <?php endif; ?>
    <div id="wv-header-affiliatebar" class="wv-header-tab-bar">
        <?php dynamic_sidebar( 'watershedview_affiliates_sidebar' ); ?>
    </div>
    <div id="wv-header-sponsorbar" class="wv-header-tab-bar">
        <?php dynamic_sidebar( 'watershedview_sponsors_sidebar' ); ?>
    </div>
</div>
<div id="wv-header-colorbar" class="wv-header-colorbar">
    <?php if(isset($_GET['embed'])) : ?>
        <div id="wv-header-infobar-toggle" class="wv-header-toggle">Disclaimer <i class="icon-chevron-down"></i></div>
        <div id="wv-header-map-infobar-toggle" class="wv-header-toggle">Watershed Info<i class="icon-chevron-down"></i></div>
    <?php endif; ?>
    <div id="wv-header-affiliatebar-toggle" class="wv-header-toggle">Affiliated Agencies <i class="icon-chevron-down"></i></div>
    <div id="wv-header-sponsorbar-toggle" class="wv-header-toggle">Sponsor Agencies <i class="icon-chevron-down"></i></div>
</div>
<?php }
