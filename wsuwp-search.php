<?php
/*
Plugin Name: WSU Search
Version: 0.5.3
Plugin URI: https://web.wsu.edu
Description: Connects to Search
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The core plugin class.
require dirname( __FILE__ ) . '/includes/class-wsu-search.php';

add_action( 'after_setup_theme', 'WSUWP_Search' );
/**
 * Start things up.
 *
 * @return \WSUWP_Search
 */
function WSUWP_Search() {
	return WSUWP_Search::get_instance();
}
