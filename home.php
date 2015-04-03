<?php
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme and one of the
 * two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * For example, it puts together the home page when no home.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 */
$themedir = get_stylesheet_directory_uri();

global $wp_styles;

wp_register_style('leaflet', "$themedir/assets/leaflet/leaflet.css");
wp_enqueue_style('leaflet');
//$wp_styles->add('leaflet-ie', '//cdn.leafletjs.com/leaflet-0.6.4/leaflet.ie.css');
//$wp_styles->add_data('leaflet-ie', 'conditional', 'lte IE 8');
//$wp_styles->enqueue(array('leaflet-ie'));
wp_register_script('leaflet', "$themedir/assets/leaflet/leaflet.js");
wp_enqueue_script('leaflet');
wp_register_script('leaflet-dvf', "$themedir/assets/js/leaflet-dvf.js", array('leaflet'));
wp_enqueue_script('leaflet-dvf');
wp_register_script('quicksand', "$themedir/assets/js/jquery.quicksand.js", array("jquery", "leaflet"));
wp_enqueue_script('quicksand');
wp_register_style('watershedview-home', "$themedir/home.css");
wp_enqueue_style('watershedview-home');
wp_register_script('watershedview-home', "$themedir/home.js", array('jquery', 'leaflet', 'leaflet-dvf', 'quicksand'));
wp_enqueue_script('watershedview-home');

get_header(); ?>

	<div id="container">
		<div id="map">
			<div id="map-overlays" class=".map-overlay-labels"></div>
		</div>
        <div id ="watershed-info-overlays"></div>
		<div id="watershed-overlays">
			<i id="wv-expando-prev" class="icon-chevron-left icon-2x"></i>
			<div id="wv-expando-container">
			<?php
			global $query_string;
			query_posts( 'section_name=watershed_view_slider_posts' );
			$posts_counted = 0;
			if ( have_posts() ) : ?>
				<?php while ( have_posts() && $posts_counted < 3 ) : the_post(); ?>
					<?php
					$category = get_the_category();
					if (in_array('watershed-view-slider-posts', array_map(function($c) { return $c->slug; }, $category))) : ?>
				<div class="wv-expando" data-post-id="<?php echo $post->ID; ?>">
                    <div class="wv-expando-img" style="background-image:url(<?php echo wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' )[0]; ?>)">
                        <?php echo get_the_post_thumbnail($post->ID, 'full'); ?>
       				</div>
					<div class="wv-expando-outer"><div class="wv-expando-inner">
						<h4><?php echo get_the_title(); ?></h4>
						<div class="wv-expando-bar"></div>
						<div class="wv-expando-button">+</div>
						<div class="wv-expando-body">
							<?php echo get_the_content(); ?>
						</div>
					</div></div>
				</div>
					<?php $posts_counted++; ?>
					<?php endif; ?>
				<?php endwhile; ?>
		
			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
			<?php endif; wp_reset_query(); ?>
			</div>
			<i id="wv-expando-next" class="icon-chevron-right icon-2x"></i>
		</div>
		<div id="watershed-overlays-all">
			<?php
			query_posts( 'section_name=watershed_view_slider_posts' );
			if ( have_posts() ) : ?>
				<?php while ( have_posts() ) : the_post(); ?>
					<?php
					$category = get_the_category();
					if (in_array('watershed-view-slider-posts', array_map(function($c) { return $c->slug; }, $category))) : ?>
			<div class="wv-expando" data-post-id="<?php echo $post->ID; ?>">
                <div class="wv-expando-img" style="background-image:url(<?php echo wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'full' )[0]; ?>)">
                    <?php echo get_the_post_thumbnail($post->ID, 'full'); ?>
   				</div>
				<div class="wv-expando-outer"><div class="wv-expando-inner">
					<h4><?php echo get_the_title(); ?></h4>
					<div class="wv-expando-bar"></div>
					<div class="wv-expando-button">+</div>
					<div class="wv-expando-body">
						<?php echo get_the_content(); ?>
					</div>
				</div></div>
			</div>
					<?php endif; ?>
				<?php endwhile; ?>
		
			<?php else : ?>
				<?php get_template_part( 'content', 'none' ); ?>
			<?php endif; wp_reset_query(); ?>
		</div>
	</div>
	<script>
        var layerInfos = <?php
		if ( have_posts() ) :
		    $infos = array();
		    while ( have_posts() ) : the_post();
				$category = get_the_category();
                if (in_array('watershed-view-slider-posts', array_map(function($c) { return $c->slug; }, $category))) :
                    $info = array(
                        "color" => get_post_meta($post->ID, 'poly_color', true),
                        "hoverColor" => get_post_meta($post->ID, 'poly_color_hover', true),
                        "z" => get_post_meta($post->ID, 'poly_z', true),
                        "name" => get_post_meta($post->ID, 'layer_name', true),
                        "description" => get_post_meta($post->ID, 'layer_description', true),
                        "id" => $post->ID,
                        "link" => get_site_url()."?p=".get_post_meta($post->ID, 'layer_link', true),
                        "exclude" => get_post_meta($post->ID, 'layer_exclude', true)
                    );
                    $location = get_post_meta($post->ID, 'geojson', true);
                    if ($location) {
                        $info['location'] = $themedir.$location;
                    }
                    $infos[] = $info;
                endif;
		    endwhile;
		    echo json_encode($infos);
		else : ?>[]<?php endif; wp_reset_query(); ?>,
                themedir = "<?php echo $themedir; ?>";
	</script>

<?php global $wv_footer_infos;
$wv_footer_infos[] = 'Map powered by '
				.'<a href="http://leafletjs.com" target="_blank" title="A JS library for interactive maps">Leaflet</a>, tiles © 2013 ' 
				.'<a href="http://cloudmade.com">CloudMade</a> – Map data ' 
				.'<a href="http://www.openstreetmap.org/copyright">ODbL</a> 2013 ' 
				.'<a href="http://www.openstreetmap.org/">OpenStreetMap.org</a> contributors – ' 
				.'<a href="http://cloudmade.com/website-terms-conditions">Terms of Use</a>.'; ?>

<?php get_footer(); ?>