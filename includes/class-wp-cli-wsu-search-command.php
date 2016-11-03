<?php
/**
 * Implements the wsues command.
 */
class WSUES_Command extends WP_CLI_Command {

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
	function reindex( $args ) {
		list( $type ) = $args;

		if ( 'all' === $type ) {
			$wsuwp_search = WSUWP_Search::get_instance();

			$response = WP_CLI::launch_self( 'post list --post_type=page,post', array(), array( 'format' => 'json' ), false, true );
			$posts = json_decode( $response->stdout );

			foreach ( $posts as $post ) {
				$post = get_post( $post->ID );
				$wsuwp_search->update_indexed_post( $post->ID, $post );
				WP_CLI::success( 'Reindex complete: ' . $post->ID . ' ' . $post->post_title );
			}

			WP_CLI::success( "reindex all" );
		} else if ( 0 === absint( $type ) ) {
			WP_CLI::error( "Please provide an object ID. $type" );
		} else {
			$wsuwp_search = WSUWP_Search::get_instance();
			$post = get_post( $type );

			if ( $post ) {
				$wsuwp_search->update_indexed_post( $type, $post );
			} else {
				WP_CLI::error( "Not a valid post object." );
			}

			WP_CLI::success( "reindexed a single object" );
		}
	}
}

WP_CLI::add_command( 'wsues', 'WSUES_Command' );
