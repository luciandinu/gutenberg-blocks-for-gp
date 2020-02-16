<?php
/*
Plugin Name: Gutenberg Blocks for GP
Plugin URI:
Description:
Version: 0.1
Author: Lucian Dinu
Author URI:
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

/* HIDE this html element with class: menu-icon-lazyblocks */
add_action('admin_head', 'custom_admin_css');
function custom_admin_css(){
    echo '
    <style>
    .menu-icon-lazyblocks{
        display:none;
    }
    </style>
    ';
}

// Define path and URL to the LZB plugin.
define( 'MY_LZB_PATH',  plugin_dir_path( __FILE__ ) . '/inc/lzb/' );
define( 'MY_LZB_URL',  plugin_dir_url( __FILE__ ) . '/inc/lzb/' );

// Include the LZB plugin.
require_once MY_LZB_PATH . 'lazy-blocks.php';

// Customize the url setting to fix incorrect asset URLs.
add_filter( 'lzb/plugin_url', 'my_lzb_url' );
function my_lzb_url( $url ) {
    return MY_LZB_URL;
}


// GUTENBERG BLOCKS
define( 'MY_BLOCKS_PATH',  plugin_dir_path( __FILE__ ) . '/inc/blocks/' );

require_once MY_BLOCKS_PATH . 'gigi.php';
