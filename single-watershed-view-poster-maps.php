<?php
/*
/**********************************************************************
* Watershed Poster Map Category Template 
* 
* File name:   
*      single-watershedview-poster-map.php
* Brief:       
*      Category page for displaying a poster map
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
			max-width: 1040px;
			text-align: center;
		}
		#container #map img {
			margin: 1em auto;
		}
		.ddpowerzoomer-magnifier {
			z-index: 9001;
		}
	</style>

	<div id="container">
		<div id="map">
		<?php if(have_posts()) : the_post(); ?>
			<h3><?php the_title(); ?></h3>
			<?php the_content(); ?>
		<?php endif; ?>
		<div>Click on the map to view, or right-click and select Save As to download.</div>
		</div>
	</div>
	
	<script>
		$(document).ready(function() {
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