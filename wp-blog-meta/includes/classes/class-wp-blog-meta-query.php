<?php
/**
 * Site API: WP_Blog_Meta_Query class
 *
 * @package Plugins/Sites/Queries
 * @since 1.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Core class used for querying by blog meta.
 *
 * @since 1.0.0
 *
 * @see WP_Blog_Meta_Query::__construct() for accepted arguments.
 */
class WP_Blog_Meta_Query {

	/**
	 * Array of columns frow `wp_blogs` to prefix
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	private $columns = array(
		'blog_id',
		'site_id',
		'domain',
		'path',
		'registered',
		'public',
		'archived',
		'mature',
		'spam',
		'deleted'
	);

	/**
	 * Pointer to WordPress Database object
	 *
	 * @since 1.0.0
	 *
	 * @var WPDB
	 */
	private $db;

	/**
	 * Setup hooks to modify WP_Site_Query
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->db = $GLOBALS['wpdb'];

		add_action( 'parse_site_query', array( $this, 'parse_site_query' ) );
		add_action( 'pre_get_sites',    array( $this, 'pre_get_sites'    ) );
		add_action( 'sites_clauses',    array( $this, 'sites_clauses'    ), 10, 2 );
	}

	/**
	 * Parse the site query for meta_query argument
	 *
	 * @since 0.1.0
	 *
	 * @param WP_Site_Query $site_query
	 */
	public function parse_site_query( $site_query ) {

		// Add empty meta_query
		$site_query->query_var_defaults['meta_query'] = '';

		// Reparse the query vars with `meta_query` added
		$site_query->query_vars = wp_parse_args( $site_query->query_vars, $site_query->query_var_defaults );
	}

	/**
	 * Make sure meta_query is set in default query variables
	 *
	 * @since 1.0.0.
	 *
	 * @param WP_Site_Query $site_query
	 */
	public function pre_get_sites( $site_query ) {
		if ( ! isset( $site_query->query_var_defaults['meta_query'] ) ) {
			$site_query->query_var_defaults['meta_query'] = '';
		}
	}

	/**
	 * Maybe add where & join for meta_query clauses
	 *
	 * @since 0.1.0
	 *
	 * @global type $wpdb
	 *
	 * @param array $clauses
	 * @param WP_Site_Query $site_query
	 *
	 * @return array
	 */
	public function sites_clauses( $clauses, $site_query ) {

		// Look out for an unset 'join' clause
		if ( ! isset( $clauses['join'] ) ) {
			$clauses['join'] = '';
		}

		// Loop for meta query
		$meta_query = $site_query->query_vars['meta_query'];
		if ( ! empty( $meta_query ) && is_array( $meta_query ) ) {
			$site_query->meta_query = new WP_Meta_Query( $meta_query );
			$meta_clauses           = $site_query->meta_query->get_sql( 'blog', 'b', 'blog_id', $site_query );

			// Concatenate query clauses
			$clauses['join']  .= $meta_clauses['join'];
			$clauses['where'] .= $meta_clauses['where'];

			// Mutate clauses
			$clauses['join']    = $this->mutate_join( $clauses['join'] );
			$clauses['join']    = $this->mutate_columns( $clauses['join']    );
			$clauses['fields']  = $this->mutate_columns( $clauses['fields']  );
			$clauses['where']   = $this->mutate_columns( $clauses['where']   );
			$clauses['orderby'] = $this->mutate_columns( $clauses['orderby'] );
		}

		// Return possibly modified clauses
		return $clauses;
	}

	/**
	 * Add table name to join section
	 *
	 * @since 1.0.0
	 *
	 * @param string $join
	 *
	 * @return string
	 */
	public function mutate_join( $join = '' ) {
		return "b{$join}";
	}

	/**
	 * Add table name to `wp_blogs` columns
	 *
	 * @since 1.0.1
	 *
	 * @param string $section
	 *
	 * @return string
	 */
	public function mutate_columns( $section = '' ) {

		// Replace full-word database table
		$section = preg_replace( "/\b{$this->db->blogs}\b/", 'b', $section );

		// Replace full-word column names
		foreach ( $this->columns as $column ) {
			$section = preg_replace( "/\b{$column}\b/", "b.{$column}", $section );
		}

		// Clean-up maybe broken MySQL
		$section = str_replace( "{$this->db->blogmeta}.b.", "{$this->db->blogmeta}.", $section );
		$section = str_replace(  'b.b.', 'b.', $section );

		// Return maybe-replaced section of the database query
		return $section;
	}
}
new WP_Blog_Meta_Query();
