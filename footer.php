<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the
 * #main and #page div elements.
 *
 */
?>
<?php if(!isset($_GET['embed'])) : ?>
		<footer id="colophon" class="site-footer" role="contentinfo">
			<?php get_sidebar( 'main' ); ?>

			<div class="site-info">
				© 2013 
				<a href="http://econca.com" target="_blank" title="The Environment, Community, and Opportunity Network">ECON</a>.  All rights reserved.
                <br />
                <span class="footer-info">
				<?php global $wv_footer_infos;
				echo implode('  ', $wv_footer_infos);  ?>
                </span>
			</div><!-- .site-info -->
		</footer><!-- #colophon -->
<?php endif; ?>
	</div><!-- #page -->

	<?php wp_footer(); ?>
    <!--<![endif]-->

    <!--[if lte IE 7]>
    <style>
        iframe {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
            border: none;
        }

        * html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
    </style>
    <iframe src="<?php echo get_stylesheet_directory_uri(); ?>/partials/old_ie.php"></iframe>
    <![endif-->
</body>
</html>