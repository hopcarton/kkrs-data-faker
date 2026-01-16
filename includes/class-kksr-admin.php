<?php
/**
 * Admin Interface Class
 *
 * Handles admin settings page with tabbed interface for ratings and sales.
 *
 * @package KKSR_Data_Faker
 * @since   4.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class KKSR_Admin
 */
class KKSR_Admin {

	/**
	 * Singleton Instance
	 */
	private static $instance = null;

	/**
	 * Get Instance
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
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add Admin Menu
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( 'KKSR Data Faker', 'kksr-data-faker' ),
			__( 'KKSR Faker', 'kksr-data-faker' ),
			'manage_options',
			'kksr-data-faker',
			array( $this, 'render_settings_page' ),
			'dashicons-star-filled',
			80
		);
	}

	/**
	 * Register Settings
	 */
	public function register_settings() {
		register_setting(
			'kksr_faker_settings_group',
			'kksr_faker_settings',
			array( $this, 'sanitize_settings' )
		);
	}

	/**
	 * Sanitize Settings
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();

		// Ratings settings
		$sanitized['rating_auto_increment'] = isset( $input['rating_auto_increment'] ) ? (bool) $input['rating_auto_increment'] : false;
		$sanitized['rating_cooldown_days'] = isset( $input['rating_cooldown_days'] ) ? absint( $input['rating_cooldown_days'] ) : 7;
		$sanitized['rating_threshold'] = isset( $input['rating_threshold'] ) ? absint( $input['rating_threshold'] ) : 100;
		$sanitized['rating_min_stars'] = isset( $input['rating_min_stars'] ) ? absint( $input['rating_min_stars'] ) : 4;
		$sanitized['rating_max_stars'] = isset( $input['rating_max_stars'] ) ? absint( $input['rating_max_stars'] ) : 5;

		// Sales settings
		$sanitized['sales_auto_increment'] = isset( $input['sales_auto_increment'] ) ? (bool) $input['sales_auto_increment'] : false;
		$sanitized['sales_cooldown_days'] = isset( $input['sales_cooldown_days'] ) ? absint( $input['sales_cooldown_days'] ) : 7;
		$sanitized['sales_threshold'] = isset( $input['sales_threshold'] ) ? absint( $input['sales_threshold'] ) : 50;

		// Validate cooldown (minimum 1 day)
		if ( $sanitized['rating_cooldown_days'] < 1 ) {
			$sanitized['rating_cooldown_days'] = 1;
		}
		if ( $sanitized['sales_cooldown_days'] < 1 ) {
			$sanitized['sales_cooldown_days'] = 1;
		}
		
		// Validate stars range (1-5)
		if ( $sanitized['rating_min_stars'] < 1 ) $sanitized['rating_min_stars'] = 1;
		if ( $sanitized['rating_min_stars'] > 5 ) $sanitized['rating_min_stars'] = 5;
		if ( $sanitized['rating_max_stars'] < 1 ) $sanitized['rating_max_stars'] = 1;
		if ( $sanitized['rating_max_stars'] > 5 ) $sanitized['rating_max_stars'] = 5;
		
		// Ensure min <= max
		if ( $sanitized['rating_min_stars'] > $sanitized['rating_max_stars'] ) {
			$temp = $sanitized['rating_min_stars'];
			$sanitized['rating_min_stars'] = $sanitized['rating_max_stars'];
			$sanitized['rating_max_stars'] = $temp;
		}

		return $sanitized;
	}

	/**
	 * Render Settings Page
	 */
	public function render_settings_page() {
		// Get current settings
		$settings = get_option( 'kksr_faker_settings', array() );
		
		// Get active tab
		$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'ratings';
		
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'KKSR Data Faker Settings', 'kksr-data-faker' ); ?></h1>
			
			<!-- Tab Navigation -->
			<h2 class="nav-tab-wrapper">
				<a href="?page=kksr-data-faker&tab=ratings" class="nav-tab <?php echo $active_tab == 'ratings' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Ratings', 'kksr-data-faker' ); ?>
				</a>
				<a href="?page=kksr-data-faker&tab=sales" class="nav-tab <?php echo $active_tab == 'sales' ? 'nav-tab-active' : ''; ?>">
					<?php esc_html_e( 'Sales', 'kksr-data-faker' ); ?>
				</a>
			</h2>
			
			<?php
			if ( $active_tab == 'ratings' ) {
				$this->render_ratings_tab( $settings );
			} else {
				$this->render_sales_tab( $settings );
			}
			?>
		</div>
		<?php
	}

	/**
	 * Render Ratings Tab
	 */
	private function render_ratings_tab( $settings ) {
		$rating_auto = isset( $settings['rating_auto_increment'] ) ? $settings['rating_auto_increment'] : true;
		$rating_cooldown = isset( $settings['rating_cooldown_days'] ) ? $settings['rating_cooldown_days'] : 7;
		$rating_threshold = isset( $settings['rating_threshold'] ) ? $settings['rating_threshold'] : 100;
		$rating_min_stars = isset( $settings['rating_min_stars'] ) ? $settings['rating_min_stars'] : 4;
		$rating_max_stars = isset( $settings['rating_max_stars'] ) ? $settings['rating_max_stars'] : 5;
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'kksr_faker_settings_group' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="rating_auto_increment"><?php esc_html_e( 'Auto Increment', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" id="rating_auto_increment" name="kksr_faker_settings[rating_auto_increment]" value="1" <?php checked( $rating_auto, true ); ?> />
							<?php esc_html_e( 'Automatically increment rating count on unique visitor views', 'kksr-data-faker' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Enable to automatically +1 rating count when unique visitors view posts/products.', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="rating_cooldown_days"><?php esc_html_e( 'Cooldown Period (Days)', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<input type="number" id="rating_cooldown_days" name="kksr_faker_settings[rating_cooldown_days]" value="<?php echo esc_attr( $rating_cooldown ); ?>" min="1" step="1" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Number of days before same visitor can increment the rating count again.', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="rating_threshold"><?php esc_html_e( 'Protection Threshold', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<input type="number" id="rating_threshold" name="kksr_faker_settings[rating_threshold]" value="<?php echo esc_attr( $rating_threshold ); ?>" min="0" step="1" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Stop incrementing when rating count reaches this number. Set 0 to disable.', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="rating_min_stars"><?php esc_html_e( 'Minimum Stars', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<input type="number" id="rating_min_stars" name="kksr_faker_settings[rating_min_stars]" value="<?php echo esc_attr( $rating_min_stars ); ?>" min="1" max="5" step="1" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Minimum stars for fake votes (1-5).', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="rating_max_stars"><?php esc_html_e( 'Maximum Stars', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<input type="number" id="rating_max_stars" name="kksr_faker_settings[rating_max_stars]" value="<?php echo esc_attr( $rating_max_stars ); ?>" min="1" max="5" step="1" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Maximum stars for fake votes (1-5).', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php
			// Hidden fields to preserve sales settings
			$sales_auto = isset( $settings['sales_auto_increment'] ) ? $settings['sales_auto_increment'] : true;
			$sales_cooldown = isset( $settings['sales_cooldown_days'] ) ? $settings['sales_cooldown_days'] : 7;
			$sales_threshold = isset( $settings['sales_threshold'] ) ? $settings['sales_threshold'] : 50;
			?>
			<input type="hidden" name="kksr_faker_settings[sales_auto_increment]" value="<?php echo $sales_auto ? '1' : '0'; ?>" />
			<input type="hidden" name="kksr_faker_settings[sales_cooldown_days]" value="<?php echo esc_attr( $sales_cooldown ); ?>" />
			<input type="hidden" name="kksr_faker_settings[sales_threshold]" value="<?php echo esc_attr( $sales_threshold ); ?>" />
			<input type="hidden" name="kksr_faker_settings[rating_min_stars]" value="<?php echo esc_attr( $rating_min_stars ); ?>" />
			<input type="hidden" name="kksr_faker_settings[rating_max_stars]" value="<?php echo esc_attr( $rating_max_stars ); ?>" />

			<?php submit_button( __( 'Save Settings', 'kksr-data-faker' ) ); ?>
		</form>
		<?php
	}

	/**
	 * Render Sales Tab
	 */
	private function render_sales_tab( $settings ) {
		$sales_auto = isset( $settings['sales_auto_increment'] ) ? $settings['sales_auto_increment'] : true;
		$sales_cooldown = isset( $settings['sales_cooldown_days'] ) ? $settings['sales_cooldown_days'] : 7;
		$sales_threshold = isset( $settings['sales_threshold'] ) ? $settings['sales_threshold'] : 50;
		?>
		<form method="post" action="options.php">
			<?php settings_fields( 'kksr_faker_settings_group' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="sales_auto_increment"><?php esc_html_e( 'Auto Increment', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<label>
							<input type="checkbox" id="sales_auto_increment" name="kksr_faker_settings[sales_auto_increment]" value="1" <?php checked( $sales_auto, true ); ?> />
							<?php esc_html_e( 'Automatically increment sales count on unique visitor views', 'kksr-data-faker' ); ?>
						</label>
						<p class="description">
							<?php esc_html_e( 'Enable to automatically +1 sales count when unique visitors view products.', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="sales_cooldown_days"><?php esc_html_e( 'Cooldown Period (Days)', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<input type="number" id="sales_cooldown_days" name="kksr_faker_settings[sales_cooldown_days]" value="<?php echo esc_attr( $sales_cooldown ); ?>" min="1" step="1" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Number of days before same visitor can increment the sales count again.', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>

				<tr>
					<th scope="row">
						<label for="sales_threshold"><?php esc_html_e( 'Protection Threshold', 'kksr-data-faker' ); ?></label>
					</th>
					<td>
						<input type="number" id="sales_threshold" name="kksr_faker_settings[sales_threshold]" value="<?php echo esc_attr( $sales_threshold ); ?>" min="0" step="1" class="small-text" />
						<p class="description">
							<?php esc_html_e( 'Stop incrementing when sales count reaches this number. Set 0 to disable.', 'kksr-data-faker' ); ?>
						</p>
					</td>
				</tr>
			</table>

			<?php
			// Hidden fields to preserve rating settings
			$rating_auto = isset( $settings['rating_auto_increment'] ) ? $settings['rating_auto_increment'] : true;
			$rating_cooldown = isset( $settings['rating_cooldown_days'] ) ? $settings['rating_cooldown_days'] : 7;
			$rating_threshold = isset( $settings['rating_threshold'] ) ? $settings['rating_threshold'] : 100;
			$rating_min_stars = isset( $settings['rating_min_stars'] ) ? $settings['rating_min_stars'] : 4;
			$rating_max_stars = isset( $settings['rating_max_stars'] ) ? $settings['rating_max_stars'] : 5;
			?>
			<input type="hidden" name="kksr_faker_settings[rating_auto_increment]" value="<?php echo $rating_auto ? '1' : '0'; ?>" />
			<input type="hidden" name="kksr_faker_settings[rating_cooldown_days]" value="<?php echo esc_attr( $rating_cooldown ); ?>" />
			<input type="hidden" name="kksr_faker_settings[rating_threshold]" value="<?php echo esc_attr( $rating_threshold ); ?>" />
			<input type="hidden" name="kksr_faker_settings[rating_min_stars]" value="<?php echo esc_attr( $rating_min_stars ); ?>" />
			<input type="hidden" name="kksr_faker_settings[rating_max_stars]" value="<?php echo esc_attr( $rating_max_stars ); ?>" />

			<?php submit_button( __( 'Save Settings', 'kksr-data-faker' ) ); ?>
		</form>
		<?php
	}
}
