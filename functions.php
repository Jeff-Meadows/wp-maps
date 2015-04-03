<?php

include_once __DIR__."/options.php";

add_action( 'after_switch_theme', 'watershedview_activated');
function watershedview_activated() {
    wp_insert_term('Watershed View Slider Posts', 'category');
    wp_insert_term('Watershed View Poster Maps', 'category');
    update_option( 'wv_layer_options', array(
        'name_translations' => array( array(
            'name_in_shapefile' => 'Zip Code',
            'name_on_website' => 'ZIP'
        ))
    ) );
    $admin_role = get_role('administrator');

    foreach (['colgan', 'fopr'] as $role_name) {
        add_role(
            $role_name.'_sample_qc',
            ($role_name === 'colgan' ? 'Colgan':'FOPR').' Sample QC',
            array(
                'edit_'.$role_name.'_sample' => true,
                'read_'.$role_name.'_sample' => true,
                'delete_'.$role_name.'_sample' => true,
                'edit_'.$role_name.'_samples' => true,
                'edit_others_'.$role_name.'_samples' => true,
                'publish_'.$role_name.'_samples' => true,
                'read_private_'.$role_name.'_samples' => true,
                'delete_'.$role_name.'_samples' => true,
                'delete_private_'.$role_name.'_samples' => true,
                'delete_published_'.$role_name.'_samples' => true,
                'delete_others_'.$role_name.'_samples' => true,
                'create_'.$role_name.'_sample' => true,
                'edit_private_'.$role_name.'_samples' => true,
                'edit_published_'.$role_name.'_samples' => true,
                'read' => true,
            )
        );

        add_role(
            $role_name.'_submitter',
                ($role_name === 'colgan' ? 'Colgan':'FOPR').' Sample Submitter',
            array(
                'create_'.$role_name.'_sample' => true,
                'edit_'.$role_name.'_sample' => true,
                'edit_'.$role_name.'_samples' => true,
                'read_'.$role_name.'_sample' => true,
                'read_private_'.$role_name.'_samples' => true,
                'read' => true,
            )
        );

        $admin_role->add_cap('edit_'.$role_name.'_sample');
        $admin_role->add_cap('read_'.$role_name.'_sample');
        $admin_role->add_cap('delete_'.$role_name.'_sample');
        $admin_role->add_cap('edit_'.$role_name.'_samples');
        $admin_role->add_cap('edit_others_'.$role_name.'_samples');
        $admin_role->add_cap('publish_'.$role_name.'_samples');
        $admin_role->add_cap('read_private_'.$role_name.'_samples');
        $admin_role->add_cap('delete_'.$role_name.'_samples');
        $admin_role->add_cap('delete_private_'.$role_name.'_samples');
        $admin_role->add_cap('delete_published_'.$role_name.'_samples');
        $admin_role->add_cap('delete_others_'.$role_name.'_samples');
        $admin_role->add_cap('create_'.$role_name.'_sample');
        $admin_role->add_cap('edit_private_'.$role_name.'_samples');
        $admin_role->add_cap('edit_published_'.$role_name.'_samples');
    }

}

add_action('init', 'watershedview_create_layer_type');
function watershedview_create_layer_type() {
    register_post_type(
        'watershedview_layer',
        array(
            'labels' => array(
                'name' => __('Layers'),
                'singular_name' => __('Layer')
            ),
            'public' => true,
            'description' => __('A single layer belonging to a watershed view map.'),
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields'),
            'map_meta_cap' => true
        )
    );

    register_taxonomy('watershedview_layer_parent',
        'watershedview_layer',
        array(
            'labels' => array(
                'name' => 'Layer Parent Map Pages',
                'singular_name' => 'Layer Parent Map Page'
            ),
            'public' => true,
            'show_admin_column' => true
        )
    );

    $args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'meta_query' => array ( array (
            'key' => '_wp_page_template',
            'value' => 'watershed-map.php'
        ) )
    );
    $the_query = new WP_Query($args);
    while ($the_query->have_posts()) {
        $the_query->the_post();
        wp_insert_term(get_the_title(),
            'watershedview_layer_parent',
            array('parent' => $the_query->post->ID)
        );
    }
    wp_reset_postdata();
}

add_action( 'restrict_manage_posts', 'wv_taxonomy_filter_restrict_manage_posts' );
function wv_taxonomy_filter_restrict_manage_posts() {
    global $typenow;

    if ($typenow !== 'watershedview_layer' ) return;

    $post_types = get_post_types( array( '_builtin' => false ) );
    if ( in_array( $typenow, $post_types ) ) {
        $filters = get_object_taxonomies( $typenow );
        foreach ($filters as $tax_slug ) {
            $tax_obj = get_taxonomy( $tax_slug );
            wp_dropdown_categories( array(
                'show_option_all'   =>  __('Show All '.$tax_obj->label ),
                'taxonomy'          =>  $tax_slug,
                'name'              =>  $tax_obj->name,
                'orderby'           =>  'name',
                'selected'          =>  $_GET[$tax_slug],
                'hierarchical'      =>  $tax_obj->hierarchical,
                'show_count'        =>  false,
                'hide_empty'        =>  true
            ) );
        }
    }
}

add_action('init', 'wv_create_sample_type');
function wv_create_sample_type() {
    register_post_type(
        'colgan_sample',
        array(
            'labels' => array(
                'name' => __('Colgan Creek Data Samples'),
                'singular_name' => __('Colgan Creek Data Sample')
            ),
            'public' => true,
            'description' => __('A single data sample taken from a location around Colgan Creek.'),
            'supports' => array( 'custom-fields' ),
            'map_meta_cap' => true,
            'capability_type' => 'colgan_sample',
        )
    );
    register_post_type(
        'fopr_sample',
        array(
            'labels' => array(
                'name' => __('Petaluma River Data Samples'),
                'singular_name' => __('Petaluma River Data Sample')
            ),
            'public' => true,
            'description' => __('A single data sample taken from a location around Petaluma River.'),
            'supports' => array( 'custom-fields' ),
            'map_meta_cap' => true,
            'capability_type' => 'fopr_sample',
        )
    );
}

add_filter('default_content', 'wv_default_content', 10 ,2);
function wv_default_content($content, $post) {
    if ($post->post_type !== 'colgan_sample' && $post->post_type !== 'fopr_sample') return;
    if (isset($_REQUEST['location'])) {
        update_post_meta($post->ID, 'location', $_REQUEST['location'], 'Location 1');
    }
}

add_filter('redirect_post_location', 'wv_redirect_post_location', 10, 2);
function wv_redirect_post_location($location, $post_id) {
    $user = wp_get_current_user();
    if ($user->user_login === 'Colgan Sample Submitter') {
        delete_post_meta($post_id, '_edit_lock');
        wp_logout();
        return "http://watershedview.com/wp-content/themes/watershedview/partials/submit_colgan_creek_sample.php";
    } else if ($user->user_login === 'colgan_qc') {
        return 'http://watershedview.com/wp-admin/edit.php?post_status=pending&post_type=colgan_sample';
    }
    if ($user->user_login === 'FOPR Sample Submitter') {
        delete_post_meta($post_id, '_edit_lock');
        wp_logout();
        return "http://watershedview.com/wp-content/themes/watershedview/partials/submit_fopr_sample.php?successful_submission=1";
    } else if ($user->user_login === 'fopr_qc') {
        return 'http://watershedview.com/wp-admin/edit.php?post_status=pending&post_type=fopr_sample';
    }
    return $location;
}

add_action('admin_notices', 'wv_show_successful_submission_notice');
function wv_show_successful_submission_notice() {
    $user = wp_get_current_user();
    if ($user->user_login === 'FOPR Sample Submitter') {
        if ($_GET['successful_submission']) {
            echo '<div class="updated fade">Sample successfully submitted.</div>';
        }
    }
}

add_filter('save_post', 'wv_add_colgan_sample_title');
function wv_add_colgan_sample_title($post_id) {
	// If this is a revision, get real post ID
	if ( $parent_id = wp_is_post_revision( $post_id ) )
		$post_id = $parent_id;
    if (get_post_type($post_id) === 'colgan_sample') {
        $date = get_post_meta($post_id, 'sample_date', true);
        $location = get_post_meta($post_id, 'location', true);
        $post_title = $location.' - '.$date;
		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post', 'wv_add_colgan_sample_title' );

		// update the post, which calls save_post again
		wp_update_post( array( 'ID' => $post_id, 'post_title' => $post_title ) );

		// re-hook this function
		add_action( 'save_post', 'wv_add_colgan_sample_title' );
    }
}
add_filter('save_post', 'wv_add_fopr_sample_title');
function wv_add_fopr_sample_title($post_id) {
	// If this is a revision, get real post ID
	if ( $parent_id = wp_is_post_revision( $post_id ) )
		$post_id = $parent_id;
    if (get_post_type($post_id) === 'fopr_sample') {
        $date = get_post_meta($post_id, 'sample_date', true);
        $location = get_post_meta($post_id, 'location', true);
        $post_title = $location.' - '.$date;
		// unhook this function so it doesn't loop infinitely
		remove_action( 'save_post', 'wv_add_fopr_sample_title' );

		// update the post, which calls save_post again
		wp_update_post( array( 'ID' => $post_id, 'post_title' => $post_title ) );

		// re-hook this function
		add_action( 'save_post', 'wv_add_fopr_sample_title' );
    }
}

add_action('transition_post_status', 'wv_transition_post_status', 10, 3);
function wv_transition_post_status($new_status, $old_status, $post) {
    if ($new_status !== 'pending') return;
    $headers = "MIME-Version: 1.0\n"
            . "Content-Type: text/html; charset=\"" . get_option('blog_charset') . "\"\n";

    $message = "To review the data sample, please visit ".get_edit_post_link( $post->ID, '' )."\n";
    $message .= 'To view all pending data samples, please visit http://watershedview.com/wp-admin/edit.php?post_status=pending&post_type=';
    if ($post->post_type === 'colgan_sample') {
        $message .= 'colgan_sample';
        $email = 'tu_qc@watershedview.com';
        $title = 'New Colgan Creek Data Sample Awaiting Review';

    } elseif ($post->post_type === 'fopr_sample') {
        $message .= 'fopr_sample';
        $email = 'fopr_qc@watershedview.com';
        $title = 'New Petaluma River Data Sample Awaiting Review';
    } else return;
    @wp_mail(
        $email,
        $title,
        $message,
        $headers
    );
}

$wv_footer_infos = array();

//make sure we can upload json, geojson, and topojson files in "Add Media"
add_filter('upload_mimes', 'watershedview_add_json_upload');
function watershedview_add_json_upload($existing_mimes=array()) {
    $existing_mimes['json'] = "application/json";
    $existing_mimes['geojson'] = "application/json";
    $existing_mimes['topojson'] = "application/json";

    return $existing_mimes;
}

// set up category specific single.php templates
add_filter('single_template', 'watershedview_single_template');
function watershedview_single_template($t) {
    foreach( (array) get_the_category() as $cat ) {
        $cat_filename = get_stylesheet_directory().'/single-'.$cat->slug.'.php';
        if ( file_exists($cat_filename) ) return $cat_filename;
    }
    return $t;
}

// Removes Masonry enqueued by Twenty Thirteen to handle vertical alignment of footer widgets.
function slbd_dequeue_masonry() {
    wp_dequeue_script( 'jquery-masonry' );
}
add_action( 'wp_print_scripts', 'slbd_dequeue_masonry' );

/**
 * Count number of widgets in a sidebar
 * Used to add classes to widget areas so widgets can be displayed one, two, three or four per row
 */
function slbd_count_widgets( $sidebar_id ) {
    // If loading from front page, consult $_wp_sidebars_widgets rather than options
    // to see if wp_convert_widget_settings() has made manipulations in memory.
    global $_wp_sidebars_widgets;
    if ( empty( $_wp_sidebars_widgets ) ) :
        $_wp_sidebars_widgets = get_option( 'sidebars_widgets', array() );
    endif;

    $sidebars_widgets_count = $_wp_sidebars_widgets;

    if ( isset( $sidebars_widgets_count[ $sidebar_id ] ) ) :
        $widget_count = count( $sidebars_widgets_count[ $sidebar_id ] );
        $widget_classes = 'widget-count-' . count( $sidebars_widgets_count[ $sidebar_id ] );
        if ( $widget_count % 4 == 0 || $widget_count > 6 ) :
            // Four widgets er row if there are exactly four or more than six
            $widget_classes .= ' per-row-4';
        elseif ( $widget_count >= 3 ) :
            // Three widgets per row if there's three or more widgets
            $widget_classes .= ' per-row-3';
        elseif ( 2 == $widget_count ) :
            // Otherwise show two widgets per row
            $widget_classes .= ' per-row-2';
        endif;

        return $widget_classes;
    endif;
}

//register sponsor links sidebar
add_action('widgets_init', 'watershedview_register_widget');
function watershedview_register_widget() {
    register_sidebar(array(
        'name' => 'Sponsors Sidebar',
        'id' => 'watershedview_sponsors_sidebar',
        'before_widget' => '<div class="wv-logo wv-sponsor">',
        'after_widget' => '</div>'
    ));
    register_sidebar(array(
        'name' => 'Affiliates Sidebar',
        'id' => 'watershedview_affiliates_sidebar',
        'before_widget' => '<div class="wv-logo wv-affiliate">',
        'after_widget' => '</div>'
    ));
    register_sidebar(array(
        'name' => 'Map Info Sidebar',
        'id' => 'watershedview_map_info_sidebar',
        'before_widget' => '<span class="wv-info">',
        'after_widget' => '</span>'
    ));
    register_sidebar(array(
        'name' => 'Watershedview Info Sidebar',
        'id' => 'watershedview_info_sidebar',
        'before_widget' => '<span class="wv-info">',
        'after_widget' => '</span>'
    ));
}
