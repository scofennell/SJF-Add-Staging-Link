<?php

/**
 * Our class for adding stuff to the admin menu.
 * 
 * @package WordPress
 * @subpackage SJF_Add_Staging_Link
 * @since SJF_Add_Staging_Link 0.1
 */

new sjf_add_staging_link_admin_menu;

class sjf_add_staging_link_admin_menu {

	public function __construct() {

		add_action( 'admin_bar_menu', array( $this, 'admin_bar_menu' ), 1000 );

	}

	/**
	 * Use the url of a blog to grab the subdomain for the staging version of a blog.
	 * 
	 * @param  string $url The url to a blog.
	 * @return string The subdomain for the staging version of a blog.
	 */
	function get_staging_subdomain() {

		global $wpdb; 

		// This will hopefully hold the subdomain of the staging site.
		$out = '';

		// Grab the db prefix for the whole install, not just this blog.
		$db_prefix = $wpdb -> base_prefix;

		// WP-Engine uses hyperDB, so we haev to reach into that to get the data we need.
		$db_slug = $this -> get_db_slug();
		
		// Don't bother returning anything if the db username is empty.
		if( empty ( $db_slug ) ) { return FALSE; }

		// In staging, databasename includes "snapshot" in it and we don't want to return anything if we're in staging.
		if ( stristr( $db_slug, 'snapshot' ) ) { return FALSE; }

		// Remove the table prefix from the staging name, since that's not going to be in prod.
		if( ! empty( $db_slug ) && ! empty( $db_prefix ) ) {
			if( 0 === strpos( $db_slug, $db_prefix ) ) {
		        $db_slug = substr( $db_slug, strlen( $db_prefix ) );
			}
		}

		// We made it!  Build the subdomain based on WPE naming conventions.
		$out = ".$db_slug.staging.wpengine.com";
		
		return $out;

	}

	/**
	 * Grab the unmapped version of the current blog url.
	 * 
	 * @return string The unmapped version of the current blog url.
	 */
	function get_unmapped_blog_url() {

		$out = '';

		// If it's just a single site install, it's easy.
		if( ! is_multisite() ) {

			// Reach into WPE scripts and grab all the domains.
			global $wpengine_platform_config;
			if( ! is_array( $wpengine_platform_config ) ) { return FALSE; }
			if( ! isset( $wpengine_platform_config['all_domains'] ) ) { return FALSE; }
			$wpe_all_domains = $wpengine_platform_config['all_domains'];

			// Grab the last array member.
			$out = array_pop( $wpe_all_domains );

		} else {

			// We have to go directly to the db in order to dodge the annoying domain mapping plugin.
			global $wpdb;

			// Grab the name of the options table for the current blog.
			$table = $wpdb -> options;

			// This is the name of the column where option values are stored.
			$col_name = 'option_value';

			// Query!  Bam!
			$r = $wpdb -> get_results( " SELECT $col_name FROM $table WHERE option_name = 'siteurl' ", ARRAY_A );

			// If the query was weird, bail.
			if( ! is_array( $r ) ) { return FALSE; }
			if( ! is_array( $r[0] ) ) { return FALSE; }
			if( ! isset( $r[0][ $col_name ] ) ) { return FALSE; }

			// Okay, grab the first result.
			$out = $r[0][ $col_name ];

		}

		return $out;

	}

	/**
	 * Grab this value that I am calling "db_slug".
	 * 
	 * It should almost always just be the db_user, but things are a bit weird
	 * on some installs.
	 * 
	 * @return string The DB slug.
	 */
	function get_db_slug() {
		
		global $wpdb;

		$out = '';

		// WP-Engine uses hyperDB, so we have to reach into that to get the data we need.
		if( isset( $wpdb -> last_used_server ) ) {

			if( isset( $wpdb -> last_used_server['user'] ) ) {
		
				$out = $wpdb -> last_used_server['user'];

			}

		}

		if( empty( $out ) ) {

			if( defined( 'DB_USER' ) ) {
				$out = DB_USER;
			}

		}

		return $out;

	}

	/**
	 * Add a link to the staging version.
	 * 
	 * @param  object $wp_admin_bar The WP admin bar.
	 */
	function admin_bar_menu( $wp_admin_bar ) {

		// Don't bother trying to add a link if it's not multisite.
		if( ! is_multisite() )  { return $wp_admin_bar; }

		// Don't bother trying to add a link to network admin.
		if( is_network_admin() ) { return $wp_admin_bar; }

		// Super admins only.
		if( ! is_super_admin() ) { return $wp_admin_bar; }

		// Grab the unmapped url to the current blog. Surprisingly tricky once domain mapping is on!
		$url = $this -> get_unmapped_blog_url();
		$url = untrailingslashit( $url );

		// Grab the tld.  We'll need to remove it to do some string manip, then re-add it.
		$tld = $this -> get_tld_from_domain_url( $url );

		// Take the url and replace periods with hyphens per WP Engine's current conventions.
		$url = $this -> strip_tld( $url );
		$url = str_replace( '.', '-', $url );

		// It's just easier if we do http.
		$url = str_replace( 'https:', 'http:', $url );

		// Check to make sure we got something back for the staging subdomain.
		if ( $this -> is_staging() ) {
			
			$db_slug = $this -> get_db_slug();

			$live_url = str_replace( '-staging-wpengine-', '', $url );
			$live_url = str_replace( 'http://www.', 'http://', $live_url );
			$live_url = str_replace( "-$db_slug" . 'blogs', '', $live_url );
			$live_url = preg_replace( "/-$db_slug$/", '', $live_url);

			$live_url = str_replace( '-', '.', $live_url );
			$live_url = rtrim( $live_url, '.' );

			// Append the query string to the URL.
			$live_url .= $this -> get_query_string();

			$args = array(
				'id'     => 'staging-site',
				'title'  => esc_attr__( 'Live Site' ),
				'parent' =>	'site-name',
				'href'   => $live_url
			);


		// Else, the staging domain is empty.  Return the live url instead.
		} else {

			// Use the unmapped domain to figure out which staging subbdomain to use.
			$staging_subdomain = $this -> get_staging_subdomain();

			// We don't want to start with www, either.
			//$url = str_replace( '//www-', '//', $url );

			// Append the tld to the URL.
			$url .= $tld;

			$url3 = $url;

			// Append the staging subdomain to the URL.
			$url .= $staging_subdomain;

			$url4 = $url;

			// Append the query string to the URL.
			$url .= $this -> get_query_string();

			// Add a link to the staging site.
			$args = array(
				'id'     => 'staging-site',
				'title'  => esc_attr__( 'Staging Site' ),
				'parent' =>	'site-name',
				'href'   => esc_url( $url )
			);

		}

		$wp_admin_bar -> add_node( $args );

	}

	/**
	 * Grab the portion of a url after the TLD.
	 * 
	 * @return string The portion of a url after the TLD.
	 */
	function get_query_string() {

		$sub_dir = esc_attr( $_SERVER['REQUEST_URI'] );

		return $sub_dir;
		
	}

	/**
	 * Determine if we're working on staging right now.
	 * 
	 * @return boolean TRUE if we are on staging, else FALSE.
	 */
	function is_staging() {
		
		if( stristr( DB_NAME, 'snapshot' ) ) { return TRUE; }

		return FALSE;

	}

	/**
	 * Get the tld from a url (the .org or the .com or whatever it is).
	 * 
	 * @param  string $url A url.
	 * @return string      A tld.
	 */
	function get_tld_from_domain_url( $url ) {

		// Break the url apart at periods.
		$url_arr = explode( '.', $url );

		// Grab the array member after the last period.
		$out = array_pop( $url_arr );

		return $out;

	}

	/**
	 * Get a url without the tld part.
	 * 
	 * @param  string $url A url.
	 * @return string      A url without the tld.
	 */
	function strip_tld( $url ) {

		// Grab the tld.
		$tld = $this -> get_tld_from_domain_url( $url );

		// How long is the tld?
		$tld_len = absint( strlen( $tld ) );

		// How long is the url?
		$url_len = absint( strlen( $url ) );

		// Subtract the tld from 0 so we get a negative offset for string manipulation.
		$substr_len = $url_len - $tld_len;

		// Grab the part of the url before the tld.
		$out = substr( $url, 0, $substr_len );

		return $out;

	}

}