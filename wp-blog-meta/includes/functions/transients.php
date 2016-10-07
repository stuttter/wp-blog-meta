<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Delete a blog transient.
 *
 * @since 3.0.0
 *
 * @see delete_site_transient()
 *
 * @param string $transient Transient name. Expected to not be SQL-escaped.
 * @return bool True if successful, false otherwise
 */
function delete_blog_transient( $transient ) {

	// Use the current blog ID
	$blog_id = get_current_blog_id();

	/**
	 * Fires immediately before a specific blog transient is deleted.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 3.0.0
	 *
	 * @param string $transient Transient name.
	 * @param int    $blog_id   ID of blog.
	 */
	do_action( "delete_blog_transient_{$transient}", $transient, $blog_id );

	// Use object cache
	if ( wp_using_ext_object_cache() ) {
		$result = wp_cache_delete( $transient, 'blog-transient' );

	// Use blogmeta table
	} else {
		$option_timeout = "_blog_transient_timeout_{$transient}";
		$option         = "_blog_transient_{$transient}";
		$result         = delete_blog_meta( $blog_id, $option );

		// Delete timeout
		if ( ! empty( $result ) ) {
			delete_blog_meta( $blog_id, $option_timeout );
		}
	}

	if ( ! empty( $result ) ) {

		/**
		 * Fires after a transient is deleted.
		 *
		 * @since 3.0.0
		 *
		 * @param string $transient Deleted transient name.
		 * @param int    $blog_id   ID of blog.
		 */
		do_action( 'deleted_blog_transient', $transient, $blog_id );
	}

	return $result;
}

/**
 * Get the value of a blog transient.
 *
 * If the transient does not exist, does not have a value, or has expired,
 * then the return value will be false.
 *
 * @since 3.0.0
 *
 * @see get_site_transient()
 *
 * @param string $transient Transient name. Expected to not be SQL-escaped.
 * @return mixed Value of transient.
 */
function get_blog_transient( $transient ) {

	// Use the current blog ID
	$blog_id = get_current_blog_id();

	/**
	 * Filters the value of an existing blog transient.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * Passing a truthy value to the filter will effectively short-circuit retrieval,
	 * returning the passed value instead.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $pre_blog_transient The default value to return if the blog transient does not exist.
	 *                                   Any value other than false will short-circuit the retrieval
	 *                                   of the transient, and return the returned value.
	 * @param string $transient          Transient name.
	 * @param int    $blog_id            ID of blog.
	 */
	$pre = apply_filters( "pre_blog_transient_{$transient}", false, $transient, $blog_id );
	if ( false !== $pre ) {
		return $pre;
	}

	// Use object cache
	if ( wp_using_ext_object_cache() ) {
		$value = wp_cache_get( $transient, 'blog-transient' );

	// Use blogmeta table
	} else {
		$transient_option  = "_blog_transient_{$transient}";
		$transient_timeout = "_blog_transient_timeout_{$transient}";
		$timeout           = get_blog_meta( $blog_id, $transient_timeout, true );

		// Delete options
		if ( ( false !== $timeout ) && ( $timeout < time() ) ) {
			delete_blog_meta( $blog_id, $transient_option  );
			delete_blog_meta( $blog_id, $transient_timeout );
			$value = false;
		}

		if ( ! isset( $value ) ) {
			$value = get_blog_meta( $blog_id, $transient_option, true );
		}
	}

	/**
	 * Filters the value of an existing blog transient.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $value     Value of blog transient.
	 * @param string $transient Transient name.
	 * @param int    $blog_id   ID of blog.
	 */
	return apply_filters( "blog_transient_{$transient}", $value, $transient, $blog_id );
}

/**
 * Set/update the value of a blog transient.
 *
 * You do not need to serialize values, if the value needs to be serialize, then
 * it will be serialized before it is set.
 *
 * @since 3.0.0
 *
 * @see set_site_transient()
 *
 * @param string $transient  Transient name. Expected to not be SQL-escaped. Must be
 *                           40 characters or fewer in length.
 * @param mixed  $value      Transient value. Expected to not be SQL-escaped.
 * @param int    $expiration Optional. Time until expiration in seconds. Default 0 (no expiration).
 * @return bool False if value was not set and true if value was set.
 */
function set_blog_transient( $transient, $value, $expiration = 0 ) {

	// Use the current blog ID
	$blog_id = get_current_blog_id();

	/**
	 * Filters the value of a specific blog transient before it is set.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 3.0.0
	 *
	 * @param mixed  $value     New value of blog transient.
	 * @param string $transient Transient name.
	 * @param int    $blog_id   ID of blog.
	 */
	$value = apply_filters( "pre_set_blog_transient_{$transient}", $value, $transient, $blog_id );

	$expiration = (int) $expiration;

	/**
	 * Filters the expiration for a blog transient before its value is set.
	 *
	 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
	 *
	 * @since 3.0.0
	 *
	 * @param int    $expiration Time until expiration in seconds. Use 0 for no expiration.
	 * @param mixed  $value      New value of blog transient.
	 * @param string $transient  Transient name.
	 * @param int    $blog_id    ID of blog.
	 */
	$expiration = apply_filters( "expiration_of_blog_transient_{$transient}", $expiration, $value, $transient, $blog_id );

	// Use object cache
	if ( wp_using_ext_object_cache() ) {
		$result = wp_cache_set( $transient, $value, 'blog-transient', $expiration );

	// Use blogmeta table
	} else {
		$transient_timeout = "_blog_transient_timeout_{$transient}";
		$option            = "_blog_transient_{$transient}";
		$time              = time() + $expiration;

		// Add
		if ( false === get_blog_meta( $blog_id, $option, true ) ) {
			if ( ! empty( $expiration ) ) {
				add_blog_meta( $blog_id, $transient_timeout, $time );
			}

			$result = add_blog_meta( $blog_id, $option, $value );

		// Update
		} else {
			if ( ! empty( $expiration ) ) {
				update_blog_meta( $blog_id, $transient_timeout, $time );
			}

			$result = update_blog_meta( $blog_id, $option, $value );
		}
	}

	if ( ! empty( $result ) ) {

		/**
		 * Fires after the value for a specific blog transient has been set.
		 *
		 * The dynamic portion of the hook name, `$transient`, refers to the transient name.
		 *
		 * @since 3.0.0
		 *
		 * @param mixed  $value      Site transient value.
		 * @param int    $expiration Time until expiration in seconds.
		 * @param string $transient  Transient name.
		 * @param int    $blog_id    ID of blog.
		 */
		do_action( "set_blog_transient_{$transient}", $value, $expiration, $transient, $blog_id );

		/**
		 * Fires after the value for a blog transient has been set.
		 *
		 * @since 3.0.0
		 *
		 * @param string $transient  The name of the blog transient.
		 * @param mixed  $value      Site transient value.
		 * @param int    $expiration Time until expiration in seconds.
		 * @param int    $blog_id    ID of blog.
		 */
		do_action( 'setted_blog_transient', $transient, $value, $expiration, $blog_id );
	}

	return $result;
}
