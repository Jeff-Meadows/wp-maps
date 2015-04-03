<?php
/*
/**********************************************************************
* Watershed Poster Map Category Template 
* 
* File name:   
*      category-watershedview-poster-maps.php
* Brief:       
*      Category page for displaying the poster maps
* Author:      
*      Jeff Meadows
* Author URI:
*      http://jeffreymeadows.com
* Contact:
*      jrmeadows2@gmail.com   
***********************************************************************/ 

$themedir = get_stylesheet_directory_uri();

wp_deregister_script('fancybox');
wp_register_script('fancybox', "$themedir/assets/fancybox/jquery.fancybox.js", array('jquery'));
wp_enqueue_script('fancybox');
wp_deregister_style('fancybox');
wp_register_style('fancybox', "$themedir/assets/fancybox/jquery.fancybox.css");
wp_enqueue_style('fancybox');

wp_deregister_script('ddpowerzoomer');
wp_register_script('ddpowerzoomer', "$themedir/assets/js/ddpowerzoomer.js", array('jquery'));
wp_enqueue_script('ddpowerzoomer');

get_header();
?>
	<style>
		#container #map {
			margin: 2em auto;
			text-align: center;
		}
		#container #map img {
			margin: 1em auto;
		}
		.ddpowerzoomer-magnifier {
			z-index: 9001;
		}
		.poster-map {
			float: left;
			padding: 0 2em 2em;
			padding-bottom: 0;
			margin: 0 2em 2em;
		}
		.poster-map p {
			margin: 0;
		}
		.poster-map img {
			margin: 1em;
			border: 1px solid gray;
			-moz-box-shadow: 2px 2px 2px #aaa;
			-webkit-box-shadow: 2px 2px 2px #aaa;
			box-shadow: 2px 2px 2px #aaa;
		}
		.poster-map h6 {
			margin: 1em auto;
		}
	</style>

	<div id="container">
		<div id="map">
	<?php 
	global $query_string;
	query_posts($query_string.'&order=ASC'); 
	while(have_posts()) : the_post(); ?>
			<div class="poster-map">
				<?php the_content(); ?>
				<h6><?php the_title(); ?></h6>
			</div>
	<?php endwhile; ?>
		</div>
	</div>
	<script>
		jQuery(document).ready(function($) {
			$('#map a[href$=".jpg"]').fancybox({
				arrows: false,
				afterShow: function() {
					$('.fancybox-image').addpowerzoom({
						magnifiersize: [150, 150]
					});
				},
                beforeClose: function() {
                    $('.fancybox-image').removepowerzoom({
                    });
                }
			});
            $(window).resize(function($) {
                $('.fancybox-image').addpowerzoom({
           			magnifiersize: [150, 150],
                    defaultpower: ddpowerzoomer.activeimage.info.power.current
           		});
            });
		});
	</script>
<?php get_footer(); ?>