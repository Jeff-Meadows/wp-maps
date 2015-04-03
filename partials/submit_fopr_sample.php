<?php
require __DIR__.'/../../../../wp-blog-header.php';
if (!is_user_logged_in()) {
    $user_login = 'FOPR Sample Submitter';
    $user = get_user_by('login', $user_login);
    $user_id = $user->ID;
    wp_set_current_user($user_id, $user_login);
    wp_set_auth_cookie($user_id);
    do_action('wp_login', $user_login);
}

$location = $_GET['location'];
$redirect = 'Location: http://watershedview.com/wp-admin/post-new.php?post_type=fopr_sample';
$successful_submission = $_GET['successful_submission'];
if (isset($location)) $redirect .= '&location='.$location;
if (isset($successful_submission)) $redirect .= '&successful_submission=1';
header($redirect);