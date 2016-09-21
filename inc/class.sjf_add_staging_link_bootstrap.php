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

		add_action( 'after_setup_theme', array( $this, 'load' ) );

		add_action( 'network_admin_notices', array( $this, 'warn' ) );

	}

	/**
	 * Without all of its dependencies, this plugin throws some admin notices.
	 */
	function warn( $echo = TRUE ) {

		$out = '';

		$messages = array();

		$message_count = count( $messages );
		if( ! empty( $message_count ) ) {
			foreach( $messages as $message ) {
				$out .= "
					<div class='error notice is-dismissible'><p>$message</p></div>
				";
			}
		}

		if( empty( $out ) ) { return FALSE; }

		if( $echo ) {

			echo $out;

		}

		return TRUE;

	}
	
	/**
	 * If this plugin does not have all of its dependencies, it refuses to load its files.
	 * 
	 * @return boolean Returns FALSE if it's missing dependencies, else TRUE.
	 */
	function load() {

		// For each php file in the inc/ folder, require it.
		foreach( glob( SJF_Add_Staging_Link_PATH . 'inc/*.php' ) as $filename ) {

			require_once( $filename );

		}

		return TRUE;

	}

}