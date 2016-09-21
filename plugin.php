<?php

/**
 * Adds a live/staging link to the admin bar.
 *
 * @package WordPress
 * @subpackage SJF_Add_Staging_Link
 * @since SJF_Add_Staging_Link 0.1
 * 
 * Plugin Name: SJF Add Staging Link
 * Description: Adds a live/staging link to the admin bar.
 * Author: Scott Fennell
 * Version: 0.1
 * Author URI: http://scottfennell.org
 * Network: TRUE
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as 
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
	
// Peace out if you're trying to access this up front.
if( ! defined( 'ABSPATH' ) ) { exit; }

// Watch out for plugin naming collisions.
if( defined( 'SJF_ADD_STAGING_LINK_PATH' ) ) { exit; }

// A slug for our plugin.
define( 'SJF_ADD_STAGING_LINK', 'sjf_add_staging_link' );

// Establish a value for plugin version to bust file caches.
define( 'SJF_ADD_STAGING_LINK_VERSION', '0.1' );

// A constant to define the paths to our plugin folders.
define( 'SJF_ADD_STAGING_LINK_FILE', __FILE__ );
define( 'SJF_ADD_STAGING_LINK_PATH', trailingslashit( plugin_dir_path( SJF_ADD_STAGING_LINK_FILE ) ) );

// A constant to define the urls to our plugin folders.
define( 'SJF_ADD_STAGING_LINK_URL', trailingslashit( plugin_dir_url( SJF_ADD_STAGING_LINK_FILE ) ) );

require_once( SJF_ADD_STAGING_LINK_PATH . 'inc/class.sjf_add_staging_link_bootstrap.php' );
