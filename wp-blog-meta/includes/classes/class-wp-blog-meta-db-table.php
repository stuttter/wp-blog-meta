<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Main WP Blog Meta class
 *
 * This class facilitates the following functionality:
 *
 * - Creates & maintains the `wp_blogmeta` table
 * - Deletes all meta for sites when sites are deleted
 * - Adds `wp_blogmeta` to the main database object when appropriate
 *
 * @since 1.0.0
 */
final class WP_Blog_Meta_DB {

	/**
	 * @var string Plugin version
	 */
	public $version = '1.0.0';

	/**
	 * @var string Database version
	 */
	public $db_version = 201609020001;

	/**
	 * @var string Database version key
	 */
	public $db_version_key = 'wpdb_blog_meta_version';

	/**
	 * @var object Database object (usually $GLOBALS['wpdb'])
	 */
	private $db = false;

	/** Methods ***************************************************************/

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Activation hook
		register_activation_hook( __FILE__, array( $this, 'activate' ) );

		// Setup plugin
		$this->db = $GLOBALS['wpdb'];

		// Force table on to the global database object
		add_action( 'init',           array( $this, 'add_table_to_db_object' ) );
		add_action( 'switch_to_blog', array( $this, 'add_table_to_db_object' ) );
		add_action( 'delete_blog',    array( $this, 'delete_blog'            ) );

		// Check if DB needs upgrading
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'admin_init' ) );
		}
	}

	/**
	 * Modify the database object and add the table to it
	 *
	 * This is necessary to do directly because WordPress does have a mechanism
	 * for manipulating them safely. It's pretty fragile, but oh well.
	 *
	 * @since 1.0.0
	 */
	public function add_table_to_db_object() {
		if ( ! isset( $this->db->blogmeta ) ) {
			$this->db->blogmeta          = "{$this->db->base_prefix}blogmeta";
			$this->db->ms_global_tables[] = 'blogmeta';
		}
	}

	/**
	 * Administration area hooks
	 *
	 * @since 1.0.0
	 */
	public function admin_init() {
		$this->upgrade_database();
	}

	/**
	 * Activation hook
	 *
	 * Handles both single & multi site installations
	 *
	 * @since 1.0.0
	 *
	 * @param   bool    $network_wide
	 */
	public function activate() {
		$this->upgrade_database();
	}

	/**
	 * Create the database table
	 *
	 * @since 1.0.0
	 *
	 * @param  int $old_version
	 */
	private function upgrade_database( $old_version = 0 ) {

		// Get current version
		$old_version = get_network_option( -1, $this->db_version_key );

		// Bail if no upgrade needed
		if ( version_compare( (int) $old_version, $this->db_version, '>=' ) ) {
			return;
		}

		// Create meta table
		$this->create_table();

		// Update the DB version
		update_network_option( -1, $this->db_version_key, $this->db_version );
	}

	/**
	 * Create the table
	 *
	 * @since 1.0.0
	 */
	private function create_table() {

		$charset_collate = '';
		if ( ! empty( $this->db->charset ) ) {
			$charset_collate = "DEFAULT CHARACTER SET {$this->db->charset}";
		}

		if ( ! empty( $this->db->collate ) ) {
			$charset_collate .= " COLLATE {$this->db->collate}";
		}

		// Check for `dbDelta`
		if ( ! function_exists( 'dbDelta' ) ) {
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		}

		$sql = array();
		$max_index_length = 191;

		// Relationship meta
		$sql[] = "CREATE TABLE {$this->db->blogmeta} (
			id bigint(20) NOT NULL AUTO_INCREMENT PRIMARY KEY,
			blog_id bigint(20) NOT NULL,
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			KEY blog_id (blog_id),
			KEY meta_key (meta_key({$max_index_length}))
		) {$charset_collate};";

		dbDelta( $sql );

		// Make doubly sure the global database object is modified
		$this->add_table_to_db_object();
	}

	/**
	 * Clear meta for a site when it's deleted
	 *
	 * @since 1.0.0
	 *
	 * @param int $site_id Site being deleted
	 */
	public function delete_blog( $site_id = 0 ) {
		$this->db->delete( $this->db->blogmeta, array(
			'blog_id' => $site_id
		), array( '%d' ) );
	}
}

/**
 * Load the DB as early as possible, but after WordPress core is included
 *
 * @since 1.0.0
 */
function wp_blog_meta_db() {
	new WP_Blog_Meta_DB();
}
add_action( 'muplugins_loaded', 'wp_blog_meta_db', -PHP_INT_MAX );
