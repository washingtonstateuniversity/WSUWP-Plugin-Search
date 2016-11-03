<?php
/*
Plugin Name: WSU Search
Version: 0.7.0
Plugin URI: https://web.wsu.edu
Description: Connects to WSU's Elasticsearch instance.
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// The core plugin class.
require dirname( __FILE__ ) . '/includes/class-wsu-search.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( dirname( __FILE__ ) . '/includes/class-wp-cli-wsu-search-command.php' );
}

add_action( 'after_setup_theme', 'WSUWP_Search' );
/**
 * Start things up.
 *
 * @return \WSUWP_Search
 */
function WSUWP_Search() {
	return WSUWP_Search::get_instance();
}
