<?php
/**
 * The Header for our theme.
 *
 * Displays all of the <head> section and everything up till <div id="main">
 *
 */
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <!--[if IE 7]>
    <html class="ie ie7" <?php language_attributes(); ?>>
    <![endif]-->
    <!--[if IE 8]>
    <html class="ie ie8" <?php language_attributes(); ?>>
    <![endif]-->
    <!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
    <!--<![endif]-->
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>">
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>; charset=<?php bloginfo('charset'); ?>" />
        <meta name="author" content="Jeff Meadows" />
        <meta name="generator" content="WordPress <?php bloginfo('version'); ?>" />
        <meta name="description" content="<?php bloginfo( 'description' ); ?>">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge" />
        <title><?php wp_title( '|', true, 'right' ); ?></title>
        <link rel="profile" href="http://gmpg.org/xfn/11">
        <link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
        <!--[if lt IE 9]>
        <script src="<?php echo get_template_directory_uri(); ?>/js/html5.js"></script>
        <script>
        </script>
        <![endif]-->
        <?php
        $themedir = get_stylesheet_directory_uri();
        wp_deregister_script('jquery');
        wp_register_script( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js');
        wp_enqueue_script( 'jquery' );

        wp_register_style('font-awesome', "$themedir/assets/font-awesome/css/font-awesome.min.css");
        wp_enqueue_style('font-awesome');
        //wp_deregister_script('jquery-ui');
        //wp_register_script( 'jquery-ui', '//ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js', 'jquery');
        //wp_enqueue_script( 'jquery-ui' );

        //wp_deregister_script('bootstrap');
        //wp_register_script('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js', 'jquery');
        //wp_enqueue_script('bootstrap');

        //wp_register_style('bootstrap', '//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css');
        //wp_enqueue_style('bootstrap');

        ?>
        <link rel="shortcut icon" href="<?php echo $themedir; ?>/assets/ico/favicon.ico">
        <!--[if gt IE 7 ]><!-->
        <?php wp_head(); ?>
        <?php include "embed.php"; ?>
        <!--<![endif]-->
    </head>

<body <?php body_class(); ?>>
    <script>
        (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
        })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

        ga('create', 'UA-42807216-2', 'watershedview.com');
        ga('send', 'pageview');

    </script>
    <!--[if gt IE 7 ]><!-->
    <script>
        $(document).ready(function() {
            var analytics = {
                trackSponsorClick: function(url) {
                    ga('send', 'event', 'outbound', 'click', url, {'hitCallback':
                        function () {
                        }
                    });
                },
                trackTabClick: function(tabName, show) {
                    ga('send', 'event', 'Tabs', show ? 'Show' : 'Hide', tabName);
                }
            };

            //set up analytics tracking for outbound sponsor links
            $('.wv-logo a').each(function(i, a) {
                $(a).click(function(e) {
                    analytics.trackSponsorClick($(a).attr('href'));
                });
            });


            function registerTab(bar) {
                $('#' + bar + '-toggle').click(function () {
                    var $sponsors = $('#' + bar), $bar = $('#wv-header-tab-bar'), $toggles = $('.wv-header-toggle i');
                    $('.wv-header-tab-bar').hide();
                    $toggles.removeClass();
                    $sponsors.show();
                    if ($bar.data('tab') === bar) {
                        analytics.trackTabClick(bar, false);
                        $toggles.addClass('icon-chevron-down');
                        $bar.slideUp(function () {
                            $bar.data('tab', false);
                        });
                    } else {//if ($bar.data('tab') === false) {
                        analytics.trackTabClick(bar, true);
                        $toggles.addClass('icon-plus');
                        $('#' + bar + '-toggle i').removeClass('icon-plus').addClass('icon-chevron-up');
                        $bar.slideDown(function () {
                            $bar.data('tab', bar);
                        });
                    }
                });
            }
            registerTab('wv-header-sponsorbar');
            registerTab('wv-header-affiliatebar');
            registerTab(('wv-header-infobar'));
            registerTab(('wv-header-map-infobar'));
        });
    </script>
<div id="page" class="hfeed">
<?php if(!isset($_GET['embed'])) : ?>
    <header id="masthead" role="banner">
        <?php include "partials/tabbed_sidebars.php"; ?>
        <div id="wv-header-titlebar" class="wv-header-titlebar">
            <h1><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
                    <img src="<?php echo $themedir; ?>/assets/img/wv_logo.png" height="72" width="72" /><?php bloginfo( 'name' ); ?>
                </a></h1>
            <h2><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home">
                    <?php bloginfo( 'description' ); ?>
                </a></h2>
        </div>
        <div id="navbar" class="wv-header-menubar">
            <nav id="site-navigation" class="navigation main-navigation" role="navigation">
                <h3 class="menu-toggle"><?php _e( 'Menu', 'twentythirteen' ); ?></h3>
                <a class="screen-reader-text skip-link" href="#content" title="<?php esc_attr_e( 'Skip to content', 'twentythirteen' ); ?>"><?php _e( 'Skip to content', 'twentythirteen' ); ?></a>
                <?php wp_nav_menu( array( 'theme_location' => 'primary', 'menu_class' => 'nav-menu' ) ); ?>
            </nav><!-- #site-navigation -->
        </div><!-- #navbar -->
    </header><!-- #masthead -->
<?php endif; ?>