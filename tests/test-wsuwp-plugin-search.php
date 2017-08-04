<?php

class WSUWP_Search_Tests extends WP_UnitTestCase {
	public function test_wsuwp_search_get_index_url_default() {
		$this->assertEquals( 'https://elastic.wsu.edu/wsu-web/page/', \WSU\Search\get_index_url() );
	}

	public function test_wsuwp_search_get_index_url_default_local() {
		add_filter( 'wsuwp_search_development', '__return_true' );
		$index_url = \WSU\Search\get_index_url();
		remove_filter( 'wsuwp_search_development', '__return_true' );

		$this->assertEquals( 'https://elastic.wsu.edu/wsu-web-dev/page/', $index_url );
	}

	public function test_wsuwp_search_get_index_url_allowed_restricted_site() {
		$existing_option = get_option( 'blog_public', 1 );
		update_option( 'blog_public', 2 );
		update_option( 'index_private_site', 1 );
		$home_url = get_option( 'home' );

		$private_index = 'https://elastic.wsu.edu/' . md5( $home_url ) . '/page/';
		$get_index_url = \WSU\Search\get_index_url();

		// Reset the `blog_option` option to its previous value.
		update_option( 'blog_option', $existing_option );
		delete_option( 'index_private_site' );

		$this->assertEquals( $private_index, $get_index_url );
	}

	public function test_wsuwp_search_get_index_url_denied_restricted_site() {
		$existing_option = get_option( 'blog_public', 1 );
		update_option( 'blog_public', 2 );

		$get_index_url = \WSU\Search\get_index_url();

		// Reset the `blog_option` option to its previous value.
		update_option( 'blog_option', $existing_option );

		$this->assertFalse( $get_index_url );
	}

	public function test_wsuwp_search_get_index_url_restricted_site_local() {
		$existing_option = get_option( 'blog_public', 1 );
		update_option( 'blog_public', 2 );
		update_option( 'index_private_site', 1 );
		$home_url = get_option( 'home' );

		$private_index = 'https://elastic.wsu.edu/' . md5( $home_url ) . '-dev/page/';

		add_filter( 'wsuwp_search_development', '__return_true' );
		$get_index_url = \WSU\Search\get_index_url();
		remove_filter( 'wsuwp_search_development', '__return_true' );

		// Reset the `blog_option` option to its previous value.
		update_option( 'blog_option', $existing_option );
		delete_option( 'index_private_site' );

		$this->assertEquals( $private_index, $get_index_url );
	}

	public function test_wsuwp_search_get_index_url_denied_restricted_site_local() {
		$existing_option = get_option( 'blog_public', 1 );
		update_option( 'blog_public', 2 );

		add_filter( 'wsuwp_search_development', '__return_true' );
		$get_index_url = \WSU\Search\get_index_url();
		remove_filter( 'wsuwp_search_development', '__return_true' );

		// Reset the `blog_option` option to its previous value.
		update_option( 'blog_option', $existing_option );

		$this->assertFalse( $get_index_url );
	}
}
