<?php
/**
 * Main Plugin Class
 *
 * This class handles the core functionality of the plugin including:
 * - Auto-generate and save REAL ratings data to database (posts and products)
 * - Data generation with consistent seeding (same post/product ID = same data)
 * - Threshold checking to prevent overwriting existing data
 * - Automatic generation when posts/products are viewed or saved
 *
 * @package KKSR_Data_Faker
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class KKSR_Data_Faker
 *
 * Core class that manages data simulation functionality.
 * Uses Singleton pattern to ensure only one instance exists.
 *
 * @since 3.0.0
 */
class KKSR_Data_Faker {

	/**
	 * Default Configuration Constants
	 *
	 * These values are used when the user hasn't configured
	 * custom settings through the admin panel.
	 */
	const DEFAULT_MIN_VOTES = 100;   // Minimum fake vote/review count.
	const DEFAULT_MAX_VOTES = 500;   // Maximum fake vote/review count.
	const DEFAULT_MIN_STARS = 3.0;   // Minimum star rating (1.0-5.0).
	const DEFAULT_MAX_STARS = 5.0;   // Maximum star rating (1.0-5.0).

	/**
	 * Singleton Instance
	 *
	 * @since 3.0.0
	 * @var   KKSR_Data_Faker|null
	 */
	private static $instance = null;

	/**
	 * Plugin Settings
	 *
	 * Stores user-configured settings loaded from database.
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	private $settings = array();

	/**
	 * Data Cache
	 *
	 * Caches simulation data per post ID to avoid repeated calculations.
	 * Format: [ post_id => simulation_data_array ]
	 *
	 * @since 3.0.0
	 * @var   array
	 */
	private $data_cache = array();

	/**
	 * Get Singleton Instance
	 *
	 * Ensures only one instance of this class exists in memory.
	 *
	 * @since  3.0.0
	 * @return KKSR_Data_Faker Single instance of the class.
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
	 * Use get_instance() instead.
	 *
	 * @since 3.0.0
	 */
	private function __construct() {
		$this->load_settings();
		$this->init_hooks();
	}

	/**
	 * Load Settings from Database
	 *
	 * Retrieves user settings from wp_options table and merges
	 * with default values as fallback.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private function load_settings() {
		// Default settings used as fallback.
		$defaults = array(
			'min_votes'        => self::DEFAULT_MIN_VOTES,
			'max_votes'        => self::DEFAULT_MAX_VOTES,
			'min_stars'        => self::DEFAULT_MIN_STARS,
			'max_stars'        => self::DEFAULT_MAX_STARS,
			'threshold_votes'  => 100,
		);

		// Get saved settings and merge with defaults.
		$saved_settings = get_option( 'kksr_faker_settings', array() );
		$this->settings = wp_parse_args( $saved_settings, $defaults );
	}

	/**
	 * Initialize WordPress Hooks
	 *
	 * Registers filters and actions used by the plugin.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	private function init_hooks() {
		// Auto-generate and save real data when post is viewed.
		add_action( 'wp', array( $this, 'auto_generate_data' ) );
		
		// Also generate when post is saved/updated.
		add_action( 'save_post', array( $this, 'generate_on_save' ), 10, 1 );
		
		// Load translation files.
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
	}

	/**
	 * Load Plugin Text Domain
	 *
	 * Loads translation files for internationalization.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'kksr-data-faker',
			false,
			dirname( plugin_basename( KKSR_FAKER_PLUGIN_FILE ) ) . '/languages'
		);
	}

	/**
	 * Get Setting Value
	 *
	 * Retrieves a specific setting value with optional default.
	 *
	 * @since  3.0.0
	 * @param  string $key     Setting key to retrieve.
	 * @param  mixed  $default Default value if key doesn't exist.
	 * @return mixed           Setting value or default.
	 */
	public function get_setting( $key, $default = '' ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
	}

	/**
	 * Generate Simulation Data
	 *
	 * Generates fake data for a given post/product. The algorithm:
	 * 1. Check cache for existing data
	 * 2. Verify post type is enabled
	 * 3. Get real sales and votes
	 * 4. Check if thresholds are exceeded
	 * 5. Generate consistent fake numbers using post ID as seed
	 * 6. Combine real + fake data
	 * 7. Cache and return result
	 *
	 * @since  3.0.0
	 * @param  int $post_id Post ID to generate data for.
	 * @return array|bool   Simulation data array or false if disabled.
	 */
	/**
	 * Auto Generate Data
	 *
	 * Automatically generates and saves real data to database when post/product is viewed.
	 * Only generates if existing votes are below threshold.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function auto_generate_data() {
		// Only on singular post or product pages.
		if ( ! is_singular( array( 'post', 'product' ) ) ) {
			return;
		}

		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		// Only generate if no data exists yet (existing_votes = 0).
		// Once data exists (even fake), don't regenerate to avoid overwriting real votes.
		$existing_votes = (int) get_post_meta( $post_id, '_kksr_count_default', true );

		// If data already exists, don't generate (protect existing data including real votes).
		if ( $existing_votes > 0 ) {
			return;
		}

		// Generate and save data only if no data exists.
		$this->generate_and_save_data( $post_id );
	}

	/**
	 * Generate and Save Data
	 *
	 * Generates random data and saves it to database as real metadata.
	 * Only generates if existing votes are below threshold.
	 * Works for both posts and products.
	 *
	 * @since  3.0.0
	 * @param  int $post_id Post/Product ID to generate data for.
	 * @return array|bool   Generated data array or false if disabled.
	 */
	public function generate_and_save_data( $post_id, $force = false ) {
		// Only for posts and products.
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, array( 'post', 'product' ), true ) ) {
			return false;
		}

		// Check threshold - don't overwrite if votes >= threshold.
		$existing_votes = (int) get_post_meta( $post_id, '_kksr_count_default', true );
		$threshold_votes = $this->get_setting( 'threshold_votes', 100 );

		// If existing votes >= threshold, don't generate (protect real data).
		if ( $existing_votes >= $threshold_votes ) {
			return false;
		}

		// If not forcing and data already exists, don't regenerate (protect existing data including real votes).
		if ( ! $force && $existing_votes > 0 ) {
			return false;
		}

		// Generate data using post ID as random seed.
		// This ensures same post always gets same numbers (consistency).
		mt_srand( $post_id );
		
		// Get user-configured ranges.
		$min_votes = $this->get_setting( 'min_votes', self::DEFAULT_MIN_VOTES );
		$max_votes = $this->get_setting( 'max_votes', self::DEFAULT_MAX_VOTES );
		$min_stars = (float) $this->get_setting( 'min_stars', self::DEFAULT_MIN_STARS );
		$max_stars = (float) $this->get_setting( 'max_stars', self::DEFAULT_MAX_STARS );
		
		// Generate random values within configured ranges.
		$casts = mt_rand( $min_votes, $max_votes );
		
		// Generate random float for star rating.
		$random_star_raw = $min_stars + ( mt_rand() / mt_getrandmax() ) * ( $max_stars - $min_stars );
		$avg = round( $random_star_raw, 1 ); // Round to 1 decimal.

		// Reset random seed.
		mt_srand();
		
		// Calculate total score (ratings).
		$ratings = $casts * $avg;

		// Save to database as REAL metadata.
		update_post_meta( $post_id, '_kksr_count_default', $casts );
		update_post_meta( $post_id, '_kksr_avg_default', $avg );
		update_post_meta( $post_id, '_kksr_ratings_default', $ratings );

		// Also save alternative keys for compatibility.
		update_post_meta( $post_id, '_kksr_casts', $casts );
		update_post_meta( $post_id, '_kksr_avg', $avg );
		update_post_meta( $post_id, '_kksr_ratings', $ratings );

		return array(
			'casts'   => $casts,
			'avg'     => $avg,
			'ratings' => $ratings,
		);
	}

	/**
	 * Generate Data on Post/Product Save
	 *
	 * Generates and saves data when a post or product is saved or updated.
	 * Only generates if existing votes are below threshold.
	 *
	 * @since  3.0.0
	 * @param  int $post_id Post/Product ID being saved.
	 * @return void
	 */
	public function generate_on_save( $post_id ) {
		// Only for posts and products.
		$post_type = get_post_type( $post_id );
		if ( ! in_array( $post_type, array( 'post', 'product' ), true ) ) {
			return;
		}

		// Skip autosaves and revisions.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Only generate if no data exists yet (existing_votes = 0).
		// Once data exists, don't regenerate to avoid overwriting real votes.
		$existing_votes = (int) get_post_meta( $post_id, '_kksr_count_default', true );

		// If data already exists, don't generate (protect existing data including real votes).
		if ( $existing_votes > 0 ) {
			return;
		}

		// Generate and save data only if no data exists.
		$this->generate_and_save_data( $post_id );
	}

	/**
	 * Generate Data for All Posts and Products on Activation
	 *
	 * Generates and saves data for all existing posts and products when plugin is activated.
	 * Only generates for items with votes below threshold.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public static function generate_all_posts_on_activation() {
		// Check if constant is defined (should be defined in main plugin file).
		if ( ! defined( 'KKSR_FAKER_PLUGIN_DIR' ) ) {
			return;
		}
		
		// Get instance (class should already be loaded by activation callback).
		$faker = self::get_instance();
		
		// Get all published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		// Generate data for each post (only if below threshold).
		foreach ( $posts as $post ) {
			$faker->generate_and_save_data( $post->ID );
		}

		// Get all published products (if WooCommerce is active).
		if ( post_type_exists( 'product' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				)
			);

			// Generate data for each product (only if below threshold).
			foreach ( $products as $product ) {
				$faker->generate_and_save_data( $product->ID );
			}
		}
	}

	/**
	 * Regenerate All Data with New Settings
	 *
	 * Regenerates data for all posts and products with votes below threshold.
	 * Used when settings are updated to apply new min/max ranges.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function regenerate_all_data() {
		// Reload settings from database to get latest values.
		$this->load_settings();

		// Get all published posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		// Regenerate data for each post (force regenerate if below threshold).
		foreach ( $posts as $post ) {
			$this->generate_and_save_data( $post->ID, true );
		}

		// Get all published products (if WooCommerce is active).
		if ( post_type_exists( 'product' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				)
			);

			// Regenerate data for each product (force regenerate if below threshold).
			foreach ( $products as $product ) {
				$this->generate_and_save_data( $product->ID, true );
			}
		}
	}
}
