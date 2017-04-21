<?php

/**
 * Plugin Name: WP Blog Meta
 * Plugin URI:  http://wordpress.org/plugins/wp-blog-meta/
 * Author:      John James Jacoby
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: A global, joinable meta-data table for your WordPress Multisite sites
 * Version:     2.0.0
 * Text Domain: wp-blog-meta
 * Domain Path: /assets/lang/
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Load immediately, to get ahead of everything else
_wp_blog_meta();

/**
 * Wrapper for includes and setting up the database table
 *
 * @since 1.0.0
 */
function _wp_blog_meta() {

	// Get the plugin path
	$plugin_path = wp_blog_meta_get_plugin_path();

	// Classes
	require_once $plugin_path . 'includes/classes/class-wp-blog-meta-query.php';
	require_once $plugin_path . 'includes/classes/class-wp-blog-meta-db-table.php';

	// Functions
	require_once $plugin_path . 'includes/functions/metadata.php';
	require_once $plugin_path . 'includes/functions/transients.php';
	require_once $plugin_path . 'includes/functions/filters.php';

	// Register database table
	if ( empty( $GLOBALS['wpdb']->blogmeta ) ) {
		$GLOBALS['wpdb']->blogmeta           = "{$GLOBALS['wpdb']->base_prefix}blogmeta";
		$GLOBALS['wpdb']->ms_global_tables[] = 'blogmeta';
	}

	// Register global cache group
	if ( function_exists( 'wp_cache_add_global_groups' ) ) {
		wp_cache_add_global_groups( array( 'blog_meta', 'blog-transient' ) );
	}
}

/**
 * Return the path to the plugin's root file
 *
 * @since 1.0.0
 *
 * @return string
 */
function wp_blog_meta_get_plugin_file() {
	return __FILE__;
}

/**
 * Return the path to the plugin's directory
 *
 * @since 1.0.0
 *
 * @return string
 */
function wp_blog_meta_get_plugin_path() {
	return dirname( wp_blog_meta_get_plugin_file() ) . '/wp-blog-meta/';
}

/**
 * Return the plugin's URL
 *
 * @since 1.0.0
 *
 * @return string
 */
function wp_blog_meta_get_plugin_url() {
	return plugin_dir_url( wp_blog_meta_get_plugin_file() ) . 'wp-blog-meta/';
}

/**
 * Return the asset version
 *
 * @since 1.0.0
 *
 * @return int
 */
function wp_blog_meta_get_asset_version() {
	return 201609100001;
}
