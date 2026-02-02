<?php
/**
 * Main Plugin Class
 *
 * This class handles the core functionality of the plugin including:
 * - Traffic-based ratings increment (posts and products)
 * - Traffic-based sales increment (products only)
 * - Visitor tracking with cooldown period
 * - Session-based F5 prevention
 *
 * @package KKSR_Data_Faker
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class KKSR_Data_Faker
 *
 * Core class that manages traffic-based data increment functionality.
 * Uses Singleton pattern to ensure only one instance exists.
 *
 * @since 4.0.0
 */
class KKSR_Data_Faker {

	/**
	 * Default Configuration Constants
	 */
	const DEFAULT_COOLDOWN_DAYS = 7;

	/**
	 * Database table name
	 *
	 * @var string
	 */
	private $table_name;

	/**
	 * Singleton Instance
	 *
	 * @var KKSR_Data_Faker|null
	 */
	private static $instance = null;

	/**
	 * Plugin Settings
	 *
	 * @var array
	 */
	private $settings = array();

	/**
	 * Get Singleton Instance
	 *
	 * @return KKSR_Data_Faker
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . 'kksr_visitor_log';
		
		$this->load_settings();
		$this->init_hooks();
	}

	/**
	 * Load Settings from Database
	 */
	private function load_settings() {
		$defaults = array(
			// Ratings settings
			'rating_auto_increment'  => true,
			'rating_cooldown_days'   => self::DEFAULT_COOLDOWN_DAYS,
			'rating_threshold'       => 100,
			'rating_min_stars'       => 4,  // Min stars (1-5)
			'rating_max_stars'       => 5,  // Max stars (1-5)
			
			// Sales settings
			'sales_auto_increment'   => true,
			'sales_cooldown_days'    => self::DEFAULT_COOLDOWN_DAYS,
			'sales_threshold'        => 50,
		);

		$saved_settings = get_option( 'kksr_faker_settings', array() );
		$this->settings = wp_parse_args( $saved_settings, $defaults );
	}

	/**
	 * Initialize WordPress Hooks
	 */
	private function init_hooks() {
		// Track visitor views
		add_action( 'wp', array( $this, 'track_visitor_view' ) );
	}

	/**
	 * Initialize Session
	 *
	 * Start session early to prevent headers already sent errors.
	 */
	public function init_session() {
		if ( ! session_id() ) {
			session_start();
		}
	}

	/**
	 * Get Setting Value
	 *
	 * @param string $key     Setting key.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_setting( $key, $default = '' ) {
		return isset( $this->settings[ $key ] ) ? $this->settings[ $key ] : $default;
	}

	/**
	 * Track Visitor View
	 *
	 * Main function called on page view.
	 * Only tracks on singular post/product pages.
	 */
	public function track_visitor_view() {
		// Only track on single post/product pages
		if ( ! is_singular() ) {
			return;
		}

		// Start session only when needed
		if ( ! session_id() && ! headers_sent() ) {
			session_start();
		}
		
		$post_id = get_the_ID();
		if ( ! $post_id ) {
			return;
		}

		$post_type = get_post_type( $post_id );
		$visitor_hash = $this->get_visitor_hash();
		
		if ( ! $visitor_hash ) {
			return;
		}

		// Track ratings for posts and products
		if ( in_array( $post_type, array( 'post', 'product' ) ) ) {
			$this->track_rating_view( $post_id, $post_type, $visitor_hash );
		}

		// Track sales for products only
		if ( $post_type === 'product' ) {
			$this->track_sales_view( $post_id, $visitor_hash );
		}

		// Close session to unlock the file and satisfy Site Health
		if ( session_id() ) {
			session_write_close();
		}
	}

	/**
	 * Track Rating View
	 *
	 * @param int    $post_id      Post ID.
	 * @param string $post_type    Post type.
	 * @param string $visitor_hash Visitor hash.
	 */
	private function track_rating_view( $post_id, $post_type, $visitor_hash ) {
		if ( ! $this->get_setting( 'rating_auto_increment', true ) ) {
			return;
		}

		if ( $this->should_increment( $visitor_hash, $post_id, 'rating' ) ) {
			$this->increment_rating_count( $post_id );
			$this->log_visitor( $visitor_hash, $post_id, $post_type, 'rating' );
			$this->mark_session_viewed( $post_id, 'rating' );
		}
	}

	/**
	 * Track Sales View
	 *
	 * @param int    $product_id   Product ID.
	 * @param string $visitor_hash Visitor hash.
	 */
	private function track_sales_view( $product_id, $visitor_hash ) {
		if ( ! $this->get_setting( 'sales_auto_increment', true ) ) {
			return;
		}

		if ( $this->should_increment( $visitor_hash, $product_id, 'sales' ) ) {
			$this->increment_sales_count( $product_id );
			$this->log_visitor( $visitor_hash, $product_id, 'product', 'sales' );
			$this->mark_session_viewed( $product_id, 'sales' );
		}
	}

	/**
	 * Get Visitor Hash
	 *
	 * @return string|false
	 */
	private function get_visitor_hash() {
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';
		$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		
		if ( empty( $ip ) || empty( $user_agent ) ) {
			return false;
		}

		return md5( $ip . $user_agent );
	}

	/**
	 * Should Increment
	 *
	 * @param string $visitor_hash Visitor hash.
	 * @param int    $object_id    Object ID.
	 * @param string $data_type    Data type (rating/sales).
	 * @return bool
	 */
	private function should_increment( $visitor_hash, $object_id, $data_type ) {
		// Check session (prevent F5)
		if ( $this->is_session_viewed( $object_id, $data_type ) ) {
			return false;
		}

		// Check threshold
		$threshold_key = $data_type . '_threshold';
		$threshold = (int) $this->get_setting( $threshold_key, 100 );
		
		if ( $data_type === 'rating' ) {
			$current = (int) get_post_meta( $object_id, '_kksr_count_default', true );
		} else {
			$current = (int) get_post_meta( $object_id, 'total_sales', true );
		}
		
		if ( $current >= $threshold ) {
			return false;
		}

		// Check cooldown
		global $wpdb;
		
		$cooldown_key = $data_type . '_cooldown_days';
		$cooldown_days = (int) $this->get_setting( $cooldown_key, self::DEFAULT_COOLDOWN_DAYS );
		$cooldown_date = gmdate( 'Y-m-d H:i:s', strtotime( "-{$cooldown_days} days" ) );
		
		$last_view = $wpdb->get_var( $wpdb->prepare(
			"SELECT last_view_time FROM {$this->table_name} 
			WHERE visitor_hash = %s AND object_id = %d AND data_type = %s",
			$visitor_hash,
			$object_id,
			$data_type
		) );

		if ( ! $last_view || $last_view < $cooldown_date ) {
			return true;
		}

		return false;
	}

	/**
	 * Increment Rating Count
	 *
	 * Increments rating count with random stars and calculates new average.
	 * Updates all KKSR meta fields for full compatibility.
	 *
	 * @param int $post_id Post ID.
	 */
	private function increment_rating_count( $post_id ) {
		// Get current values
		$current_count = (int) get_post_meta( $post_id, '_kksr_count_default', true );
		$current_avg = (float) get_post_meta( $post_id, '_kksr_avg_default', true );
		
		// Random stars between min-max
		$min_stars = (int) $this->get_setting( 'rating_min_stars', 4 );
		$max_stars = (int) $this->get_setting( 'rating_max_stars', 5 );
		$new_stars = rand( $min_stars, $max_stars );
		
		// Calculate new average
		// Formula: (current_avg * current_count + new_stars) / (current_count + 1)
		$current_total = $current_avg * $current_count;
		$new_total = $current_total + $new_stars;
		$new_count = $current_count + 1;
		$new_avg = $new_count > 0 ? $new_total / $new_count : $new_stars;
		
		// Update main fields
		update_post_meta( $post_id, '_kksr_count_default', $new_count );
		update_post_meta( $post_id, '_kksr_avg_default', $new_avg );
		update_post_meta( $post_id, '_kksr_ratings_default', $new_total );  // Total stars
		update_post_meta( $post_id, '_kksr_casts', $new_count );  // Vote count
		
		// Update legacy fields for backward compatibility
		update_post_meta( $post_id, '_kksr_avg', $new_avg );
		update_post_meta( $post_id, '_kksr_ratings', $new_total );
	}

	/**
	 * Increment Sales Count
	 *
	 * @param int $product_id Product ID.
	 */
	private function increment_sales_count( $product_id ) {
		$current = (int) get_post_meta( $product_id, 'total_sales', true );
		$new_count = $current + 1;
		
		update_post_meta( $product_id, 'total_sales', $new_count );
	}

	/**
	 * Log Visitor
	 *
	 * @param string $visitor_hash Visitor hash.
	 * @param int    $object_id    Object ID.
	 * @param string $object_type  Object type.
	 * @param string $data_type    Data type.
	 */
	private function log_visitor( $visitor_hash, $object_id, $object_type, $data_type ) {
		global $wpdb;
		
		$now = current_time( 'mysql', 1 );
		
		$wpdb->replace(
			$this->table_name,
			array(
				'visitor_hash'   => $visitor_hash,
				'object_id'      => $object_id,
				'object_type'    => $object_type,
				'data_type'      => $data_type,
				'last_view_time' => $now,
			),
			array( '%s', '%d', '%s', '%s', '%s' )
		);
	}

	/**
	 * Check if Session Viewed
	 *
	 * @param int    $object_id Object ID.
	 * @param string $data_type Data type.
	 * @return bool
	 */
	private function is_session_viewed( $object_id, $data_type ) {
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		
		$key = $data_type . '_' . $object_id;
		return isset( $_SESSION['kksr_viewed'][ $key ] );
	}

	/**
	 * Mark Session Viewed
	 *
	 * @param int    $object_id Object ID.
	 * @param string $data_type Data type.
	 */
	private function mark_session_viewed( $object_id, $data_type ) {
		if ( ! isset( $_SESSION ) ) {
			session_start();
		}
		
		if ( ! isset( $_SESSION['kksr_viewed'] ) ) {
			$_SESSION['kksr_viewed'] = array();
		}
		
		$key = $data_type . '_' . $object_id;
		$_SESSION['kksr_viewed'][ $key ] = time();
	}

	/**
	 * Create Database Table
	 */
	public static function create_visitor_table() {
		global $wpdb;
		
		$table_name = $wpdb->prefix . 'kksr_visitor_log';
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
			id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
			visitor_hash VARCHAR(64) NOT NULL,
			object_id BIGINT UNSIGNED NOT NULL,
			object_type VARCHAR(20) NOT NULL,
			data_type VARCHAR(20) NOT NULL,
			last_view_time DATETIME NOT NULL,
			UNIQUE KEY unique_visitor_object (visitor_hash, object_id, data_type),
			KEY idx_object (object_id, data_type)
		) {$charset_collate};";
		
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}
}
