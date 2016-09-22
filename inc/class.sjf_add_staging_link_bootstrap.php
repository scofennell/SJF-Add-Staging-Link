<?php

/**
 * A class for managing plugin dependencies and loading the plugin.
 *
 * @package WordPress
 * @subpackage SJF_Add_Staging_Link
 * @since SJF_Add_Staging_Link 0.1
 */

// This has to occur in the global scope.
new SJF_Add_Staging_Link_Bootstrap;

class SJF_Add_Staging_Link_Bootstrap {

	function __construct() {

		add_action( 'plugins_loaded', array( $this, 'load' ), 1 );

	}
	
	/**
	 * Load our plugin files.
	 * 
	 * @return boolean Returns TRUE upon require_once()'ing.
	 */
	function load() {

		// For each php file in the inc/ folder, require it.
		foreach( glob( SJF_ADD_STAGING_LINK_PATH . 'inc/*.php' ) as $filename ) {

			require_once( $filename );

		}

		return TRUE;

	}

}