<?php

/**
 * Manage site custom fields.
 *
 * ## EXAMPLES
 *
 *     # Get a list of super-admins
 *     $ wp site meta get 1 site_admins
 *     array (
 *       0 => 'supervisor',
 *     )
 */
class Blog_Meta_Command extends \WP_CLI\CommandWithMeta {
	protected $meta_type = 'blog';

	/**
	 * Check that the site ID exists
	 *
	 * @param int
	 */
	protected function check_object_id( $object_id ) {
		$term = get_site( $object_id );
		if ( ! $term ) {
			WP_CLI::error( "Could not find the site with ID {$object_id}." );
		}
		return $term->blog_id;
	}
}

WP_CLI::add_command( 'site meta', 'Blog_Meta_Command', array(
	'before_invoke' => function () {
		if ( !is_multisite() ) {
			WP_CLI::error( 'This is not a multisite install.' );
		}
	}
) );

