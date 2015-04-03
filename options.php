<?php

if (is_admin()) {
    add_action( 'admin_menu', 'wv_add_layer_options_menu' );
    add_action( 'admin_init', 'wv_register_layer_options_settings' );
    add_action( 'admin_init', 'wv_add_sample_options_menu' );
}

function wv_add_sample_options_menu() {

}

function wv_add_layer_options_menu() {
    add_submenu_page(
        'edit.php?post_type=watershedview_layer',
        'WatershedView Layer Options',
        'Layer Options',
        'manage_options',
        'wv-layer-options',
        'wv_manage_layer_options'
    );
}

function wv_manage_layer_options() {
    if ( !current_user_can( 'manage_options' ) )  {
   		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
   	}

    ?>
<div class="wrap">
<?php screen_icon(); ?>
    <h2>WatershedView Layer Options</h2>
    <script>
        jQuery(document).ready(function() {
            var addIndex = <?php echo count(get_option('wv_layer_options')['name_translations']); ?>;
            jQuery('#wv_add_translation').click(function() {
                var tr = jQuery('<tr></tr>'),
                    td1 = jQuery('<td><input type="text" name="wv_layer_options[name_translations][' +
                        (addIndex) +
                        '][name_in_shapefile]" /></td>'),
                    td2 = jQuery('<td><input type="text" name="wv_layer_options[name_translations][' +
                        (addIndex++) +
                        '][name_on_website]" /></td>');
                tr.append(td1).append(td2);
                jQuery(this).prevAll('table').append(tr);
            });
        });
    </script>
    <form method="post" action="options.php">

<?php
settings_fields( 'wv_layer_option_group' );
do_settings_sections( 'wv_layer_options' );
print '<br /><br /><button id="wv_add_translation" type="button">Add New Translation</button>';
submit_button();
?>
    </form>
</div>
<?php
}

function wv_register_layer_options_settings() {
    register_setting(
        'wv_layer_option_group',
        'wv_layer_options',
        'wv_layer_options_sanitize'
    );

    add_settings_section(
        'wv_layer_options_name_translations',
        'WatershedView Layer Name Translations',
        'wv_print_section_info',
        'wv_layer_options'
    );

    $layer_options = get_option( 'wv_layer_options' );
    foreach ( $layer_options['name_translations'] as $i => $translation ) {
        add_settings_field(
            'name_translation_'.$i,
            null,
            'wv_layer_options_name_in_shapefile_callback',
            'wv_layer_options',
            'wv_layer_options_name_translations',
            array( 'index' => $i, 'translation' => $translation )
        );
    }

}

function wv_layer_options_sanitize( $input ) {
    return $input; //TODO: actually sanitize!
}

function wv_print_section_info() {
    print 'Define Layer Info Translations Below:<br />';
    print 'Valid values for "Name on Website" are: ';
    print '<ul>';
    print '<li>Name</li>';
    print '<li>Photos</li>';
    print '<li>Area</li>';
    print '<li>Acres</li>';
    print '<li>Address</li>';
    print '<li>ZIP</li>';
    print '<li>Website</li>';
    print '<li>Source</li>';
    print '<li>LID Elements</li>';
    print '<li>Hours of Operation</li>';
    print '<li>Phone Number</li>';
    print '<li>Notes</li>';
    print '<li>Location</li>';
    print '</ul>';
}

function wv_layer_options_name_in_shapefile_callback($args) {
    $index = $args['index'];
    $translation = $args['translation'];
    if ($index === 0) {
        print '<tr><th>Name in Shapefile</th><th>Name on Website</th></tr>';
    }
    print '<tr>';
    printf(
        '<td><input type="text" name="wv_layer_options[name_translations][%s][name_in_shapefile]" value="%s" /></td>',
        $index,
        $translation['name_in_shapefile']
    );
    printf(
        '<td><input type="text" name="wv_layer_options[name_translations][%s][name_on_website]" value="%s" /></td>',
        $index,
        $translation['name_on_website']
    );
    print '</tr>';
}