<?php
/*
Plugin Name: Disable plugins when doing local dev
Description: If the WP_LOCAL_DEV constant is true, disables plugins that you specify
Version: 0.1
License: GPL version 2 or any later version
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

/*
Plugin Name: Disable plugins when doing local dev
Description: If the WP_LOCAL_DEV constant is true, disables plugins that you specify
Version: 0.1
License: GPL version 2 or any later version
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

class YWAM_Disable_Plugins_When_Local_Dev {
	static $instance;
	private $disabled = array();

	/**
	 * Sets up the options filter, and optionally handles an array of plugins to disable
	 * @param array $disables Optional array of plugin filenames to disable
	 */
	public function __construct( Array $disables = NULL) {
		// Handle what was passed in
		if ( is_array( $disables ) ) {
			foreach ( $disables as $disable )
				$this->disable( $disable );
		}

		// Add the filters
		add_filter( 'option_active_plugins', array( $this, 'do_disabling' ) );
		add_filter( 'site_option_active_sitewide_plugins', array( $this, 'do_network_disabling' ) );

		// Allow other plugins to access this instance
		self::$instance = $this;
	}

	/**
	 * Adds a filename to the list of plugins to disable
	 */
	public function disable( $file ) {
		$this->disabled[] = $file;
	}

	/**
	 * Hooks in to the option_active_plugins filter and does the disabling
	 * @param array $plugins WP-provided list of plugin filenames
	 * @return array The filtered array of plugin filenames
	 */
	public function do_disabling( $plugins ) {
		if ( count( $this->disabled ) ) {
			foreach ( (array) $this->disabled as $plugin ) {
				$key = array_search( $plugin, $plugins );
				if ( false !== $key )
					unset( $plugins[$key] );
			}
		}
		return $plugins;
	}
	
	/**
	 * Hooks in to the site_option_active_sitewide_plugins filter and does the disabling
	 *
	 * @param array $plugins
	 *
	 * @return array
	 */
	public function do_network_disabling( $plugins ) {

		if ( count( $this->disabled ) ) {
			foreach ( (array) $this->disabled as $plugin ) {

				if( isset( $plugins[$plugin] ) )
					unset( $plugins[$plugin] );
			}
		}

		return $plugins;
	}
}

/* Begin customization */
if( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
	new YWAM_Disable_Plugins_When_Local_Dev( array( 
		'wp-super-cache/wp-cache.php',
		'bwp-minify/bwp-minify.php',
		'akismet/akismet.php'
	));
	/*
		For programmatic disabling, you can initialize the object (e.g. as $_localdev) then do:
		$_localdev->disable( 'vaultpress.php' );
	*/
}