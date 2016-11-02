<?php

/**
 * Class WSU_Search
 */
class WSUWP_Search {
	/**
	 * @var WSUWP_Search
	 */
	private static $instance;

	/**
	 * @var string The base URL used to add pages from WordPress to our index.
	 */
	var $index_api_url = 'https://elastic.wsu.edu';

	/**
	 * Maintain and return the one instance. Initiate hooks when
	 * called the first time.
	 *
	 * @since 0.6.0
	 *
	 * @return \WSUWP_Search
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new WSUWP_Search();
			self::$instance->setup_hooks();
		}
		return self::$instance;
	}

	/**
	 * Setup hooks to include.
	 *
	 * @since 0.6.0
	 */
	public function setup_hooks() {
		add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );
		add_action( 'before_delete_post', array( $this, 'remove_post_from_index' ), 10, 1 );
	}

	/**
	 * Determine what URL should be used to access the ES index.
	 *
	 * @since 0.6.0
	 *
	 * @return string URL to the ES index.
	 */
	public function get_index_url() {
		$public_status = absint( get_option( 'blog_public', 0 ) );
		$index_status = absint( get_option( 'index_private_site', 0 ) );

		$public_status = apply_filters( 'wsuwp_search_public_status', $public_status );

		if ( 1 !== $public_status && 1 === $index_status ) {
			$home_url = get_option( 'home' );
			$index_slug = '/' . md5( $home_url );
		} elseif ( 1 !== $public_status && 1 !== $index_status ) {
			// Private sites must explicitly support search at this time.
			return false;
		} else {
			$index_slug = '/wsu-web';
		}

		// Append '-dev' to the index slug when a development environment has been flagged.
		if ( apply_filters( 'wsuwp_search_development', false ) ) {
			$index_slug .= '-dev';
		}

		$index_slug .= '/page/';

		return $this->index_api_url . $index_slug;
	}

	/**
	 * When a post is saved, ensure that the most recent version is updated in the index. If this
	 * does not yet exist in the index, then create the document and log the generated UUID.
	 *
	 * @param int     $post_id ID of the post being saved.
	 * @param WP_Post $post    The entire post object.
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return null;
		}

		if ( ! in_array( $post->post_type, array( 'post', 'page' ), true ) ) {
			return null;
		}

		if ( ! $this->get_index_url() ) {
			return null;
		}

		if ( 'publish' !== $post->post_status ) {
			$this->remove_post_from_index( $post_id );
			return null;
		}

		// HTTP request arguments.
		$args = array();
		// Data to be sent as JSON to Elasticsearch.
		$data = array();

		$search_id = get_post_meta( $post->ID, '_wsusearch_doc_id', true );

		// If this document already has an ID, we'll PUT to update it. If not, we'll POST a new document.
		if ( $search_id ) {
			$args['method'] = 'PUT';
			$request_url = $this->get_index_url() . $this->_sanitize_es_id( $search_id );
		} else {
			$args['method'] = 'POST';
			$request_url = $this->get_index_url();
		}

		$data['title'] = $post->post_title;
		$data['date'] = $post->post_date;
		$data['modified'] = $post->post_modified;
		$data['author'] = get_the_author();
		$data['content'] = $post->post_content;
		$data['url'] = get_permalink( $post->ID );
		$data['generator'] = apply_filters( 'wsusearch_schema_generator', 'wsuwp' );
		$data['post_type'] = $post->post_type;

		// Information about the site and network this came from.
		$data['site_id'] = get_current_blog_id();

		// Store the hostname - e.g. home.wsu.edu - as a field.
		$home_url = parse_url( trailingslashit( get_home_url() ) );
		$data['hostname'] = $home_url['host'];
		$data['site_url'] = $home_url['host'];

		// Only attach path if it isn't empty.
		if ( '/' !== $home_url['path'] ) {
			$data['site_url'] .= $home_url['path'];
		}

		if ( is_multisite() ) {
			$data['network_id'] = get_current_network_id();
		}

		// Map each registered public taxonomy to the Elasticsearch document.
		$taxonomies = get_taxonomies( array( 'public' => true ) );

		// Don't index post format.
		if ( isset( $taxonomies['post_format'] ) ) {
			unset( $taxonomies['post_format'] );
		}

		foreach ( $taxonomies as $taxonomy ) {
			$post_terms = wp_get_object_terms( $post->ID, $taxonomy, array( 'fields' => 'slugs' ) );
			if ( ! is_wp_error( $post_terms ) ) {
				if ( 'post_tag' === $taxonomy ) {
					$data['university_tag'] = $post_terms;
				} elseif ( 'wsuwp_university_category' === $taxonomy ) {
					$data['university_category'] = $post_terms;
				} elseif ( 'wsuwp_university_location' === $taxonomy ) {
					$data['university_location'] = $post_terms;
				} elseif ( 'category' === $taxonomy ) {
					$data['site_category'] = $post_terms;
				} else {
					$data[ $taxonomy ] = $post_terms;
				}
			}
		}

		$args['body'] = wp_json_encode( $data );

		// wp_remote_retrieve_body handles a possible WP_Error from wp_remote_post.
		$response = wp_remote_post( $request_url, $args );
		$response = wp_remote_retrieve_body( $response );

		if ( ! empty( $response ) ) {
			$response_data = json_decode( $response );
			if ( isset( $response_data->_id ) ) {
				update_post_meta( $post->ID, '_wsusearch_doc_id', $this->_sanitize_es_id( $response_data->_id ) );
			}
		}
	}

	/**
	 * When a post is deleted, delete it from Elasticsearch as well.
	 *
	 * @param int $post_id ID of the post being deleted.
	 *
	 * @return null
	 */
	public function remove_post_from_index( $post_id ) {
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return null;
		}

		$search_id = get_post_meta( $post_id, '_wsusearch_doc_id', true );

		// This document has not yet been saved, no need to delete.
		if ( empty( $search_id ) ) {
			return null;
		}

		$request_url = $this->get_index_url() . $this->_sanitize_es_id( $search_id );

		// Make a request to delete the existing document from Elasticsearch.
		$response = wp_remote_request( $request_url, array( 'method' => 'DELETE' ) );

		if ( ! is_wp_error( $response ) ) {
			delete_post_meta( $post_id, '_wsusearch_doc_id' );
		}
	}

	/**
	 * Sanitize the key returned from Elasticsearch. It should be a-z, A-Z, -, and _ only.
	 *
	 * @param string $id UUID returned from ES, or currently in use for a page.
	 *
	 * @return string sanitized string.
	 */
	private function _sanitize_es_id( $id ) {
		return preg_replace( '/[^a-zA-Z0-9_\-]/', '', $id );
	}
}
