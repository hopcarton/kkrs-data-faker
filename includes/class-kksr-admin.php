<?php
/**
 * Admin Class
 *
 * Handles all administrative functionality including:
 * - Settings page creation and rendering
 * - Settings registration with WordPress Settings API
 * - Input sanitization and validation
 * - Admin CSS enqueuing
 *
 * @package KKSR_Data_Faker
 * @since   3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class KKSR_Admin
 *
 * Manages the admin interface and settings for the plugin.
 * Uses Singleton pattern to ensure single instance.
 *
 * @since 3.0.0
 */
class KKSR_Admin {

	/**
	 * Singleton Instance
	 *
	 * @since 3.0.0
	 * @var   KKSR_Admin|null
	 */
	private static $instance = null;

	/**
	 * Get Singleton Instance
	 *
	 * @since  3.0.0
	 * @return KKSR_Admin Single instance of the class.
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
	 * Registers all admin hooks and filters.
	 *
	 * @since 3.0.0
	 */
	private function __construct() {
		// Add settings page to admin menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		
		// Register settings with WordPress.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		
		// Enqueue admin CSS.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );
		
		// Add "Settings" link to plugins page.
		add_filter( 'plugin_action_links_' . plugin_basename( KKSR_FAKER_PLUGIN_FILE ), array( $this, 'add_action_links' ) );
	}

	/**
	 * Add Plugin Action Links
	 *
	 * Adds a "Settings" link to the plugin row on the plugins page.
	 *
	 * @since  3.0.0
	 * @param  array $links Existing plugin action links.
	 * @return array        Modified links array with Settings link added.
	 */
	public function add_action_links( $links ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=kksr-data-faker' ) . '">' . __( 'Settings', 'kksr-data-faker' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}

	/**
	 * Add Admin Menu
	 *
	 * Registers settings page under Settings menu in WordPress admin.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'KKSR Data Faker Settings', 'kksr-data-faker' ), // Page title.
			__( 'KKSR Faker', 'kksr-data-faker' ),                // Menu title.
			'manage_options',                                      // Capability required.
			'kksr-data-faker',                                     // Menu slug.
			array( $this, 'render_settings_page' )                 // Callback function.
		);
	}

	/**
	 * Register Settings
	 *
	 * Registers all plugin settings, sections, and fields with WordPress Settings API.
	 * Organizes settings into two sections: Ratings and General.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function register_settings() {
		register_setting(
			'kksr_faker_settings_group',
			'kksr_faker_settings',
			array( $this, 'sanitize_settings' )
		);

		// Rating Settings Section
		add_settings_section(
			'kksr_faker_rating_section',
			__( 'Rating Simulation Settings', 'kksr-data-faker' ),
			array( $this, 'render_rating_section' ),
			'kksr-data-faker'
		);

		add_settings_field( 'min_votes', __( 'Minimum Vote Count', 'kksr-data-faker' ), array( $this, 'render_number_field' ), 'kksr-data-faker', 'kksr_faker_rating_section', array( 'field' => 'min_votes', 'description' => __( 'Minimum number of votes/reviews to generate', 'kksr-data-faker' ) ) );
		add_settings_field( 'max_votes', __( 'Maximum Vote Count', 'kksr-data-faker' ), array( $this, 'render_number_field' ), 'kksr-data-faker', 'kksr_faker_rating_section', array( 'field' => 'max_votes', 'description' => __( 'Maximum number of votes/reviews to generate', 'kksr-data-faker' ) ) );
		add_settings_field( 'min_stars', __( 'Minimum Star Rating', 'kksr-data-faker' ), array( $this, 'render_number_field' ), 'kksr-data-faker', 'kksr_faker_rating_section', array( 'field' => 'min_stars', 'description' => __( 'Minimum star rating (1.0 - 5.0)', 'kksr-data-faker' ), 'step' => '0.1', 'min' => '1.0', 'max' => '5.0' ) );
		add_settings_field( 'max_stars', __( 'Maximum Star Rating', 'kksr-data-faker' ), array( $this, 'render_number_field' ), 'kksr-data-faker', 'kksr_faker_rating_section', array( 'field' => 'max_stars', 'description' => __( 'Maximum star rating (1.0 - 5.0)', 'kksr-data-faker' ), 'step' => '0.1', 'min' => '1.0', 'max' => '5.0' ) );

		// General Settings Section
		add_settings_section(
			'kksr_faker_general_section',
			__( 'General Settings', 'kksr-data-faker' ),
			array( $this, 'render_general_section' ),
			'kksr-data-faker'
		);

		add_settings_field( 'threshold_votes', __( 'Real Votes Threshold', 'kksr-data-faker' ), array( $this, 'render_number_field' ), 'kksr-data-faker', 'kksr_faker_general_section', array( 'field' => 'threshold_votes', 'description' => __( 'Posts with votes equal to or greater than this number will not be overwritten (default: 100)', 'kksr-data-faker' ) ) );
	}

	/**
	 * Render Rating Section Description
	 *
	 * Displays introductory text for the rating settings section.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function render_rating_section() {
		echo '<p>' . esc_html__( 'Configure the range for generated ratings and votes (KK Star Ratings for posts only).', 'kksr-data-faker' ) . '</p>';
	}

	/**
	 * Render General Section Description
	 *
	 * Displays introductory text for the general settings section.
	 *
	 * @since 3.0.0
	 * @return void
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'General plugin settings and thresholds.', 'kksr-data-faker' ) . '</p>';
	}

	/**
	 * Render Number Input Field
	 *
	 * Generates HTML for a number input field in the settings form.
	 *
	 * @since  3.0.0
	 * @param  array $args {
	 *     Field configuration arguments.
	 *
	 *     @type string $field       Field name/key.
	 *     @type string $description Help text for the field.
	 *     @type string $step        Input step value (default: 1).
	 *     @type string $min         Minimum allowed value (default: 0).
	 *     @type string $max         Maximum allowed value (default: 10000).
	 * }
	 * @return void
	 */
	public function render_number_field( $args ) {
		$settings = get_option( 'kksr_faker_settings', array() );
		$field    = $args['field'];
		$value    = isset( $settings[ $field ] ) ? $settings[ $field ] : '';
		$step     = isset( $args['step'] ) ? $args['step'] : '1';
		$min      = isset( $args['min'] ) ? $args['min'] : '0';
		$max      = isset( $args['max'] ) ? $args['max'] : '10000';
		
		printf(
			'<input type="number" name="kksr_faker_settings[%s]" value="%s" step="%s" min="%s" max="%s" class="regular-text" />',
			esc_attr( $field ),
			esc_attr( $value ),
			esc_attr( $step ),
			esc_attr( $min ),
			esc_attr( $max )
		);

		if ( isset( $args['description'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['description'] ) );
		}
	}

	/**
	 * Render Checkbox Field
	 *
	 * Generates HTML for a checkbox input field in the settings form.
	 *
	 * @since  3.0.0
	 * @param  array $args {
	 *     Field configuration arguments.
	 *
	 *     @type string $field       Field name/key.
	 *     @type string $description Label text for the checkbox.
	 * }
	 * @return void
	 */
	public function render_checkbox_field( $args ) {
		$settings = get_option( 'kksr_faker_settings', array() );
		$field    = $args['field'];
		$value    = isset( $settings[ $field ] ) ? $settings[ $field ] : 0;
		$checked  = checked( 1, $value, false );

		printf(
			'<label><input type="checkbox" name="kksr_faker_settings[%s]" value="1" %s /> %s</label>',
			esc_attr( $field ),
			$checked,
			esc_html( $args['description'] )
		);
	}

	/**
	 * Sanitize Settings
	 *
	 * Sanitizes and validates user input from settings form.
	 * Ensures data integrity before saving to database.
	 *
	 * Validation Rules:
	 * - Numeric fields: Cast to absolute integer
	 * - Float fields: Cast to float, clamped between 1.0-5.0
	 * - Checkboxes: Convert to 1 or 0
	 * - Range validation: Min must be less than Max
	 *
	 * @since  3.0.0
	 * @param  array $input Raw user input from form.
	 * @return array        Sanitized settings array.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Sanitize integer fields.
		$numeric_fields = array( 'min_votes', 'max_votes', 'threshold_votes' );
		foreach ( $numeric_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$sanitized[ $field ] = absint( $input[ $field ] ); // Convert to absolute integer.
			}
		}

		// Sanitize float fields (star ratings).
		$float_fields = array( 'min_stars', 'max_stars' );
		foreach ( $float_fields as $field ) {
			if ( isset( $input[ $field ] ) ) {
				$value                = floatval( $input[ $field ] );
				$sanitized[ $field ] = max( 1.0, min( 5.0, $value ) ); // Clamp between 1.0-5.0.
			}
		}

		// Validate: Minimum must be less than Maximum.
		if ( isset( $sanitized['min_votes'], $sanitized['max_votes'] ) && $sanitized['min_votes'] >= $sanitized['max_votes'] ) {
			add_settings_error(
				'kksr_faker_settings',
				'invalid_votes_range',
				__( 'Minimum vote count must be less than maximum vote count.', 'kksr-data-faker' ),
				'error'
			);
		}

		if ( isset( $sanitized['min_stars'], $sanitized['max_stars'] ) && $sanitized['min_stars'] >= $sanitized['max_stars'] ) {
			add_settings_error(
				'kksr_faker_settings',
				'invalid_stars_range',
				__( 'Minimum star rating must be less than maximum star rating.', 'kksr-data-faker' ),
				'error'
			);
		}

		return $sanitized;
	}

	/**
	 * Render Settings Page
	 *
	 * Outputs the HTML for the plugin settings page.
	 * Includes an instructional notice and the settings form.
	 *
	 * @since  3.0.0
	 * @return void
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'kksr-data-faker' ) );
		}
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			
			<div class="kksr-faker-notice">
				<h3><?php esc_html_e( 'How it works:', 'kksr-data-faker' ); ?></h3>
				<ul>
					<li><?php esc_html_e( 'Data is saved to wp_postmeta with keys: _kksr_count_default, _kksr_avg_default, _kksr_ratings_default.', 'kksr-data-faker' ); ?></li>
					<li><strong><?php esc_html_e( 'Real data protection: If a post already has votes equal to or greater than the real votes threshold, the plugin will NOT overwrite the data.', 'kksr-data-faker' ); ?></strong></li>
				</ul>
			</div>

			<?php settings_errors( 'kksr_faker_settings' ); ?>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'kksr_faker_settings_group' );
				do_settings_sections( 'kksr-data-faker' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Enqueue Admin Scripts and Styles
	 *
	 * Loads CSS file only on the plugin's settings page.
	 * This prevents unnecessary asset loading on other admin pages.
	 *
	 * @since  3.0.0
	 * @param  string $hook Current admin page hook suffix.
	 * @return void
	 */
	public function admin_enqueue_scripts( $hook ) {
		// Only load on our settings page.
		if ( 'settings_page_kksr-data-faker' !== $hook ) {
			return;
		}
		
		wp_enqueue_style(
			'kksr-faker-admin',              // Handle.
			KKSR_FAKER_PLUGIN_URL . 'assets/admin.css', // Source.
			array(),                         // Dependencies.
			KKSR_FAKER_VERSION               // Version for cache busting.
		);
	}
}

