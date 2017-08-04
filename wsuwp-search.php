<?php
/*
Plugin Name: WSU Search
Version: 0.8.0
Plugin URI: https://web.wsu.edu
Description: Connects to WSU's Elasticsearch instance.
Author: washingtonstateuniversity, jeremyfelt
Author URI: https://web.wsu.edu
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

// This plugin uses namespaces and requires PHP 5.3 or greater.
if ( version_compare( PHP_VERSION, '5.3', '<' ) ) {
	add_action( 'admin_notices', create_function( '',
		"echo '<div class=\"error\"><p>" . __( 'WSUWP Search requires PHP 5.3 to function properly. Please upgrade PHP or deactivate the plugin.', 'wsuwp-search' ) . "</p></div>';" ) );
	return;
} else {
	include_once __DIR__ . '/includes/wsu-search.php';

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		require_once( dirname( __FILE__ ) . '/includes/class-wp-cli-wsu-search-command.php' );
	}
}
