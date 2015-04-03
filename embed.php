<?php
if (isset($_GET['embed']) && $_GET['embed'] === 'watershed-classroom') : ?>
    <style>
        .wv-header-tab-bar, .wv-header-toggle, .wv-header-colorbar {
            background-color: #1d1d1d;
            color: #777!important;
        }
    </style>
<?php endif; if (isset($_GET['embed']) && ($_GET['embed'] !== 'colgankids' && $_GET['embed'] !== 'rrwa' )) : ?>
    <style>
        #map {
            top: 10px;
        }
    </style>
<?php endif;
