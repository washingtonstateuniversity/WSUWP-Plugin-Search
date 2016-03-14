<?php

class WSUWP_Search_Tests extends WP_UnitTestCase {
	public function test_wsuwp_search_get_index_url_default() {
		$this->assertEquals( 'https://elastic.wsu.edu/wsu-web/page/', WSUWP_Search()->get_index_url() );
	}

	public function test_wsuwp_search_get_index_url_default_local() {
		add_filter( 'wsuwp_search_development', '__return_true' );
		$index_url = WSUWP_Search()->get_index_url();
		remove_filter( 'wsuwp_search_development', '__return_true' );

		$this->assertEquals( 'https://elastic.wsu.edu/wsu-web-dev/page/', WSUWP_Search()->get_index_url() );
	}

	public function test_wsuwp_search_get_index_url_restricted_site() {
		$existing_option = get_option( 'blog_public', 1 );
		update_option( 'blog_public', 2 );
		$home_url = get_option( 'home' );

		$private_index = 'https://elastic.wsu.edu/' . md5( $home_url ) . '/page/';
		$get_index_url = WSUWP_Search()->get_index_url();

		// Reset the `blog_option` option to its previous value.
		update_option( 'blog_option', $existing_option );

		$this->assertEquals( $private_index, $get_index_url );
	}

	public function test_wsuwp_search_get_index_url_restricted_site_local() {
		$existing_option = get_option( 'blog_public', 1 );
		update_option( 'blog_public', 2 );
		$home_url = get_option( 'home' );

		$private_index = 'https://elastic.wsu.edu/' . md5( $home_url ) . '-dev/page/';

		add_filter( 'wsuwp_search_development', '__return_true' );
		$get_index_url = WSUWP_Search()->get_index_url();
		remove_filter( 'wsuwp_search_development', '__return_true' );

		// Reset the `blog_option` option to its previous value.
		update_option( 'blog_option', $existing_option );

		$this->assertEquals( $private_index, $get_index_url );
	}
}
