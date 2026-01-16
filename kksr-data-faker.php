<?php

/**
 * Plugin Name:       KKSR Data Faker
 * Plugin URI:        https://github.com/MaiSyDat/kksr-data-faker
 * Description:       Traffic-based ratings and sales counter. Automatically increments KK Star Ratings and WooCommerce sales based on unique visitor views with configurable cooldown period.
 * Version:           4.0.0
 * Author:            MaiSyDat
 * Author URI:        https://hupuna.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       kksr-data-faker
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Requires PHP:      7.2
 * Requires Plugins:  kk-star-ratings
 *
 * @package KKSR_Data_Faker
 */

// Exit if accessed directly - Security measure to prevent direct file access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plugin Constants
 * 
 * Define core constants used throughout the plugin for consistency
 * and easy maintenance.
 */
define( 'KKSR_FAKER_VERSION', '4.0.0' );
define( 'KKSR_FAKER_PLUGIN_FILE', __FILE__ );
define( 'KKSR_FAKER_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'KKSR_FAKER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Load Required Files
 *
 * Loads all necessary class files for the plugin to function.
 * Files are loaded in a specific order to ensure dependencies are met.
 *
 * @since 3.0.0
 * @return void
 */
function kksr_faker_load_files() {
	// Core functionality - Must be loaded first.
	require_once KKSR_FAKER_PLUGIN_DIR . 'includes/class-kksr-data-faker.php';
	
	// Admin interface - Settings page and configuration.
	require_once KKSR_FAKER_PLUGIN_DIR . 'includes/class-kksr-admin.php';
	
	// Frontend display - JavaScript injection and UI updates (Single post pages).
	require_once KKSR_FAKER_PLUGIN_DIR . 'includes/class-kksr-frontend.php';
}

/**
 * Initialize Plugin
 *
 * Main initialization function that bootstraps the plugin.
 * This function is hooked to 'plugins_loaded' to ensure WordPress
 * core is fully loaded before we initialize.
 *
 * @since 3.0.0
 * @return void
 */
function kksr_faker_init() {
	// Load all required class files.
	kksr_faker_load_files();

	// Initialize core class (handles metadata filtering and data generation).
	KKSR_Data_Faker::get_instance();

	// Initialize admin class only in WordPress admin area.
	// This prevents unnecessary code execution on the frontend.
	if ( is_admin() ) {
		KKSR_Admin::get_instance();
	}

	// Initialize frontend class only on public-facing pages.
	// This handles JavaScript injection and UI updates.
	if ( ! is_admin() ) {
		KKSR_Frontend::get_instance(); // Single post pages only.
	}
}

/**
 * Hook Initialization
 * 
 * We use 'plugins_loaded' hook to ensure all WordPress core functions
 * and other plugins are loaded before our plugin initializes.
 * Priority: 10 (default)
 */
add_action( 'plugins_loaded', 'kksr_faker_init' );

/**
 * Register Activation Hook
 *
 * Creates visitor log table when plugin is activated.
 *
 * @since 4.0.0
 */
register_activation_hook( KKSR_FAKER_PLUGIN_FILE, 'kksr_faker_activation' );

/**
 * Activation Hook Callback
 *
 * Creates database table for visitor tracking.
 * Does NOT reset existing data - preserves ratings and sales.
 *
 * @since 4.0.0
 * @return void
 */
function kksr_faker_activation() {
	// Load class files first.
	require_once KKSR_FAKER_PLUGIN_DIR . 'includes/class-kksr-data-faker.php';
	
	// Create visitor log table.
	KKSR_Data_Faker::create_visitor_table();
	
	// Do NOT reset counters - keep existing data!
	// Plugin will increment from current numbers.
}
