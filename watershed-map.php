<?php
/*
Template Name: Watershed Map Page
*/
/**********************************************************************
 * Watershed Map Page Template
 *
 * File name:
 *      watershed-map.php
 * Brief:
 *      Map page for displaying a watershed map
 * Author:
 *      Jeff Meadows
 * Author URI:
 *      http://jeffreymeadows.com
 * Contact:
 *      jrmeadows2@gmail.com
 ***********************************************************************/

show_admin_bar(false);

$themedir = get_stylesheet_directory_uri();

wp_deregister_script('jquery-ui');
wp_register_script( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js', array('jquery'));
wp_enqueue_script( 'jquery-ui' );

wp_register_style('jquery-ui-smoothness', '//code.jquery.com/ui/1.10.0/themes/smoothness/jquery-ui.css');
wp_enqueue_style('jquery-ui-smoothness');

wp_deregister_script('google-maps');
wp_register_script( 'google-maps', '//maps.googleapis.com/maps/api/js?sensor=false');
wp_enqueue_script( 'google-maps' );

wp_register_script('google-maps-geojson', "$themedir/assets/js/GeoJSON.js", array('google-maps'));
wp_enqueue_script('google-maps-geojson');

wp_register_script('marker-manager', "$themedir/assets/js/markermanager.js", array('jquery', 'google-maps'));
wp_enqueue_script('marker-manager');

wp_register_script('topojson', "$themedir/assets/js/topojson.js");
wp_enqueue_script('topojson');

wp_register_style('watershedview-map', "$themedir/watershed-map.css");
wp_enqueue_style('watershedview-map');

wp_register_script('watershedview-map', "$themedir/watershed-map.js", array('jquery', 'google-maps', 'marker-manager'));
wp_enqueue_script('watershedview-map');

get_header();
?>

    <div><?php echo get_field('additional_footer_info'); ?></div>
    <div id="container"<?php if (isset($_GET['embed'])) echo ' class="embed"'; ?>'>
		<div id="explanation">
            <h3><a href="#">Layers</a></h3>
            <div id="explanation-controls">
                <ul id="explanation-layers" class="explanation-layers">
                    <?php
                    $map_title = get_the_title();
                    $args = array(
                        'post_type' => 'watershedview_layer',
                        'post_status' => 'publish',
                        'watershedview_layer_parent' => $map_title,
                    );
                    global $query_string;
                    $qs_backup = $query_string;
                    wp_reset_query();
                    query_posts($query_string); if (have_posts()) the_post();
                    $query_string_new = 'posts_per_page=100&section_name='.get_field('layer_section_name');
                    query_posts($query_string_new);
                    while (have_posts()) : the_post(); ?>
                        <li data-post-id="<?php echo $post->ID; ?>"
                            data-map="<?php echo $map_title; ?>"
                            data-json-location="<?php echo get_field('layer_data'); ?>"
                            data-json-is-topojson="<?php echo get_field('layer_is_topojson'); ?>"
                            data-layer-name="<?php echo get_field('layer_name'); ?>"
                            data-layer-hide-nameless-features="<?php echo get_field('layer_hide_nameless_features'); ?>"
                            data-merge-same-names="<?php echo get_field('layer_merge_same_names'); ?>"
                                <?php $no_alpha = get_field('dont_alphabetize_features'); if ($no_alpha) : ?>
                                    data-dont-alphabetize-features="<?php echo $no_alpha; ?>"
                                <?php endif ?>
                                <?php $poly_fill = get_field('polygon_fill_color'); if ($poly_fill) : ?>
                                    data-polygon-fill="<?php echo $poly_fill; ?>"
                                <?php endif ?>
                                <?php $poly_opacity = get_field('polygon_fill_opacity'); if ($poly_opacity) : ?>
                                    data-polygon-opacity="<?php echo $poly_opacity; ?>"
                                <?php endif ?>
                                <?php $line_color = get_field('line_color'); if ($line_color) : ?>
                                    data-line-color="<?php echo $line_color; ?>"
                                <?php endif ?>
                                <?php $line_width = get_field('line_stroke_width'); if ($line_width) : ?>
                                    data-line-width="<?php echo $line_width; ?>"
                                <?php endif ?>
                                <?php $line_dash = get_field('line_dashing'); if ($line_dash) : ?>
                                    data-line-dash="<?php echo $line_dash; ?>"
                                <?php endif ?>
                                <?php $layer_z = get_field('layer_z'); if ($layer_z) : ?>
                                    data-layer-z="<?php echo $layer_z; ?>"
                                <?php endif ?>
                                <?php $layer_show_popups = get_field('layer_show_popups'); if ($layer_show_popups) : ?>
                                    data-layer-show-popups="<?php echo $layer_show_popups; ?>"
                                <?php endif ?>
                                <?php $layer_category = get_field('layer_category'); if ($layer_category) : ?>
                                    data-layer-category="<?php echo $layer_category; ?>"
                                <?php endif ?>
                                <?php $min_zoom = get_field('min_zoom'); if ($min_zoom) : ?>
                                    data-min-zoom="<?php echo $min_zoom; ?>"
                                <?php endif ?>
                            data-layer-enabled="<?php echo get_field('layer_enabled'); ?>">
                            <div>
							<span>
								<input id="layer-toggle-<?php echo $post->ID; ?>" class="layer-toggle" type="checkbox" value="<?php echo $post->ID; ?>" checked="<?php if(!get_field('layer_enabled')) echo "checked"; ?>" disabled="true" />
							</span> <span onclick="javascript:document.getElementById('layer-toggle-<?php echo $post->ID; ?>').click();">
								<img height="18" width="18" src="<?php $icon_image = get_field('layer_icon'); echo $icon_image['url']; ?>" />
							</span> <span id="layer-loading-<?php echo $post->ID; ?>" class="layer-loading">
								<img src="<?php echo $themedir; ?>/assets/img/ajax_loading.gif" />
							</span> <span class="layer-toggle-name" data-index="<?php echo $post->ID; ?>">
								<label for="layer-toggle-<?php echo $post->ID; ?>">
                                    <?php echo get_field('layer_name'); ?>
                                </label>
							</span>
                            </div>
                        </li>
                    <?php endwhile; ?>
                </ul>
                <ul id="explanation-all">
                    <li>
                        <span>
                            <input id="explanation-layers-all" type="checkbox" value="all" />
                        </span> <span>
                            <label style="display: inline" for="explanation-layers-all">Show/hide all</label>
                        </span>
                    </li>
                </ul>
            </div>
            <h3><a href="#">Details</a></h3>
            <div id="explanation-details">
                <ul id="explanation-details-tabs">
                    <?php
                    wp_reset_query();
                    query_posts($query_string_new);
                    while (have_posts()) : the_post(); ?>
                        <li id="explanation-details-tab-<?php echo $post->ID; ?>"><a href="#explanation-details-layer-<?php echo $post->ID; ?>">
                                <img src="<?php $icon_image = get_field('layer_icon'); echo $icon_image['url']; ?>" height="18" width="18">
                            </a></li>
                    <?php endwhile; ?>
                </ul>
                <div id="explanation-details-wrapper" class="clearfix"></div>
                <?php
                wp_reset_query();
                query_posts($query_string_new);
                while (have_posts()) : the_post(); ?>
                    <div id="explanation-details-layer-<?php echo $post->ID; ?>" class="explanation-details-layer-nopad">
                        <div class="explanation-details-layer-fixed">
                            <div class="explanation-details-layer-description-title"><?php echo get_the_title(); ?></div>
                            <?php $layer_description = get_field('layer_description'); if ($layer_description) : ?>
                                <div class="explanation-details-layer-description-toggle-container">
                                    <div class="explanation-details-layer-description-toggle"><i class="icon-chevron-up"></i></div>
                                </div>
                                <div class="explanation-details-layer-description">
                                    <?php echo $layer_description; ?>
                                </div>
                            <?php endif ?>
                        </div>
                        <div class="explanation-details-layer-holder clearfix">
                            <div class="explanation-details-layer-description-title explanation-details-description-hidden"><?php echo get_the_title(); ?></div>
                            <?php $layer_description = get_field('layer_description'); if ($layer_description) : ?>
                                <div class="explanation-details-layer-description explanation-details-description-hidden"><?php echo $layer_description; ?></div>
                            <?php endif ?>
                            <span class="explanation-details-layer-details"></span>
                            <?php $layer_description = get_field('layer_description_bottom'); if ($layer_description) : ?>
                                <div class="explanation-details-layer-description"><?php echo $layer_description; ?></div>
                            <?php endif ?>
                        </div>
                    </div>
                <?php endwhile;
                wp_reset_query(); ?>
            </div>
        </div>
        <div id="wv-header">
            <?php if (isset($_GET['embed'])) include "partials/tabbed_sidebars.php"; ?>
        </div>
        <div class="map-wrapper">
            <div id="map"></div>
            <div id="map-popup-static">
                <div id="map-popup-static-close">x</div>
                <div id="map-popup-static-container"> </div>
            </div>
        </div>
	</div>
	
	<?php query_posts($qs_backup); if (have_posts()) the_post();
else echo "<!--no post!-->" ?>
	
	<script>
        var googleMapTileType = <?php echo get_field('google_map_tile_type'); ?>,
                mapCenterLongitude = <?php echo get_field('map_center_latitude'); ?>,
                mapCenterLatitude = <?php echo get_field('map_center_longitude'); ?>,
                mapZoom = <?php echo get_field('map_zoom'); ?>;
        var geoJsonPropertyTranslations = <?php echo json_encode(get_option('wv_layer_options')['name_translations']) ?>;

    </script>
<?php get_footer(); ?>