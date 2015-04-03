<?php
/**
 * The sidebar containing the footer widget area.
 */

if ( is_active_sidebar( 'sidebar-1' ) ) : ?>
	<div id="secondary" class="sidebar-container" role="complementary">
		<div class="widget-area flexible-widget-area <?php echo slbd_count_widgets( 'sidebar-1' ); ?>">
			<?php dynamic_sidebar( 'sidebar-1' ); ?>
		</div><!-- .widget-area -->
	</div><!-- #secondary -->
<?php endif; ?>