<?php
/**
 * Frontend Class
 *
 * Handles all frontend functionality including:
 * - JavaScript injection for UI updates
 * - Star rating visual fixes
 * - Sales counter updates
 * - Structured data modification
 *
 * @package KKSR_Data_Faker
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class KKSR_Frontend
 *
 * Manages frontend display and JavaScript injection for the plugin.
 * Uses Singleton pattern to ensure single instance.
 *
 * @since 3.0.0
 */
class KKSR_Frontend {

	/**
	 * Singleton Instance
	 *
	 * @since 3.0.0
	 * @var   KKSR_Frontend|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance
	 *
	 * @since  3.0.0
	 * @return KKSR_Frontend Single instance of the class.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 *
	 * Private constructor to prevent direct instantiation.
	 * Registers frontend hooks.
	 *
	 * @since 3.0.0
	 */
	private function __construct() {
		// Frontend script no longer needed - data is saved to database.
		// KKSR will automatically read and display the real data.
		// add_action( 'wp_footer', array( $this, 'inject_frontend_script' ) );
	}

	/**
	 * Inject Frontend JavaScript
	 *
	 * DEPRECATED: This method is no longer used.
	 * Data is now saved directly to database, so KKSR automatically reads and displays it.
	 * No JavaScript injection needed.
	 *
	 * @since  3.0.0
	 * @deprecated 3.0.0 No longer needed - data is saved to database
	 * @return void
	 */
	public function inject_frontend_script() {
		// Method deprecated - data is now saved to database.
		// KKSR will automatically read and display the real data.
		return;
	}
}
