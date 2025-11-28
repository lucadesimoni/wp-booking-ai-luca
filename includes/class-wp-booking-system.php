<?php
/**
 * Main plugin class
 *
 * @package WP_Booking_System
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main WP_Booking_System Class
 */
class WP_Booking_System {

	/**
	 * The single instance of the class.
	 *
	 * @var WP_Booking_System
	 */
	protected static $_instance = null;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.0';

	/**
	 * Plugin file path.
	 *
	 * @var string
	 */
	public $file = '';

	/**
	 * Database instance.
	 *
	 * @var WP_Booking_System_Database
	 */
	public $database = null;

	/**
	 * Admin instance.
	 *
	 * @var WP_Booking_System_Admin
	 */
	public $admin = null;

	/**
	 * Frontend instance.
	 *
	 * @var WP_Booking_System_Frontend
	 */
	public $frontend = null;

	/**
	 * Email instance.
	 *
	 * @var WP_Booking_System_Email
	 */
	public $email = null;

	/**
	 * Main WP_Booking_System Instance.
	 *
	 * @param string $file Plugin file path.
	 * @param string $version Plugin version.
	 * @return WP_Booking_System
	 */
	public static function instance( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	}

	/**
	 * Constructor.
	 *
	 * @param string $file Plugin file path.
	 * @param string $version Plugin version.
	 */
	public function __construct( $file = '', $version = '1.0.0' ) {
		$this->version = $version;
		$this->file    = $file;

		$this->init();
	}

	/**
	 * Initialize the plugin.
	 */
	private function init() {
		// Initialize database.
		$this->database = new WP_Booking_System_Database();

		// Initialize admin.
		if ( is_admin() ) {
			$this->admin = new WP_Booking_System_Admin();
		}

		// Initialize frontend.
		$this->frontend = new WP_Booking_System_Frontend();

		// Initialize AJAX.
		new WP_Booking_System_Ajax();

		// Initialize email.
		$this->email = new WP_Booking_System_Email();

		// Register widget.
		add_action( 'widgets_init', array( $this, 'register_widget' ) );

		// Load plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );
	}

	/**
	 * Register widget.
	 */
	public function register_widget() {
		register_widget( 'WP_Booking_System_Widget' );
	}

	/**
	 * Create database tables on activation.
	 */
	public static function activate() {
		$database = new WP_Booking_System_Database();
		$database->create_tables();
	}

	/**
	 * Load plugin textdomain.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'wp-booking-system', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	}

	/**
	 * Plugin deactivation.
	 */
	public function deactivate() {
		// Clean up if needed.
	}

	/**
	 * Get plugin URL.
	 *
	 * @return string
	 */
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', $this->file ) );
	}

	/**
	 * Get plugin path.
	 *
	 * @return string
	 */
	public function plugin_path() {
		return untrailingslashit( plugin_dir_path( $this->file ) );
	}
}

