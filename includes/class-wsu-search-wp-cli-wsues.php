<?php

namespace WSU\Search\WP_CLI;

/**
 * Implements the wp reindex command.
 */
class WSUES extends \WP_CLI_Command {

	/**
	 * Reindexes an object or all objects.
	 *
	 * ## OPTIONS
	 *
	 * <type>
	 * : The type of reindex to perform. "all" or a post ID.
	 *
	 * ## EXAMPLES
	 *
	 *     wp reindex all
	 *     wp reindex 5
	 */
	public function reindex( $args ) {
		list( $type ) = $args;

		if ( 'all' === $type ) {
			$response = \WP_CLI::launch_self( 'post list --post_type=page,post', array(), array(
				'format' => 'json',
			), false, true );

			$posts = json_decode( $response->stdout );

			foreach ( $posts as $post ) {
				$post = get_post( $post->ID );
				\WSU\Search\update_indexed_post( $post->ID, $post );
				\WP_CLI::success( 'Reindex complete: ' . $post->ID . ' ' . $post->post_title );
			}

			\WP_CLI::success( 'Reindexed all.' );
		} elseif ( 0 === absint( $type ) ) {
			\WP_CLI::error( 'Please provide an object ID.' );
		} else {
			$post = get_post( $type );

			if ( $post ) {
				\WSU\Search\update_indexed_post( $type, $post );
			} else {
				\WP_CLI::error( 'Not a valid post object.' );
			}

			\WP_CLI::success( 'Reindex complete: ' . $type . ' ' . $post->post_title );
		}
	}
}

\WP_CLI::add_command( 'wsues', '\WSU\Search\WP_CLI\WSUES' );
