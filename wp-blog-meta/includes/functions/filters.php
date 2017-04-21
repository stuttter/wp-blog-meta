<?php

/**
 * When calling the clean_site_cache function, 
 * also clean blog meta
 *
 * @since 2.0.0
 *
 * @return string
 */
function wp_blog_meta_clean_site_cache( $blog_id ) {
	wp_cache_delete( $blog_id, 'blog_meta' );
}
add_action( 'clean_site_cache', 'wp_blog_meta_clean_site_cache' , 10, 1 );