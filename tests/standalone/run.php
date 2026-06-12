<?php
/**
 * Self-contained test runner.
 *
 * Runs without PHPUnit or a WordPress install by stubbing the small slice of
 * the WordPress API the plugin touches at load time. It verifies:
 *
 *   1. The pure booking logic in WP_Booking_System_Luca_Helpers.
 *   2. That the whole plugin boots with no fatal errors and registers its
 *      shortcodes, blocks and AJAX handlers.
 *
 * Usage: php tests/standalone/run.php
 *
 * @package WP_Booking_System_Luca
 */

error_reporting( E_ALL );
ini_set( 'display_errors', '1' );

$plugin_dir = dirname( __DIR__, 2 );

/* --------------------------------------------------------------------------
 * Tiny assertion framework.
 * ------------------------------------------------------------------------ */
$tests_run    = 0;
$tests_failed = 0;

/**
 * Assert a condition is true.
 *
 * @param bool   $condition Condition.
 * @param string $message   Description.
 */
function check( $condition, $message ) {
	global $tests_run, $tests_failed;
	$tests_run++;
	if ( $condition ) {
		echo "  \033[32mPASS\033[0m  {$message}\n";
	} else {
		$tests_failed++;
		echo "  \033[31mFAIL\033[0m  {$message}\n";
	}
}

/**
 * Assert two values are equal (loose, with type-aware float compare).
 *
 * @param mixed  $expected Expected.
 * @param mixed  $actual   Actual.
 * @param string $message  Description.
 */
function check_equals( $expected, $actual, $message ) {
	$ok = is_float( $expected ) || is_float( $actual )
		? abs( (float) $expected - (float) $actual ) < 0.00001
		: $expected === $actual;
	check( $ok, $message . ( $ok ? '' : ' (expected ' . var_export( $expected, true ) . ', got ' . var_export( $actual, true ) . ')' ) );
}

/* --------------------------------------------------------------------------
 * Minimal WordPress stubs needed to load the plugin.
 * ------------------------------------------------------------------------ */
define( 'ABSPATH', $plugin_dir . '/' );
define( 'DAY_IN_SECONDS', 86400 );

$GLOBALS['_wpbsl_test'] = array(
	'shortcodes' => array(),
	'actions'    => array(),
	'blocks'     => array(),
	'options'    => array(),
);

function plugin_dir_path( $f ) { return rtrim( dirname( $f ), '/' ) . '/'; }
function plugin_dir_url( $f ) { return 'http://example.test/wp-content/plugins/' . basename( dirname( $f ) ) . '/'; }
function plugin_basename( $f ) { return basename( dirname( $f ) ) . '/' . basename( $f ); }
function plugins_url( $p = '', $f = '' ) { return 'http://example.test' . $p; }
function untrailingslashit( $s ) { return rtrim( $s, '/' ); }
function register_activation_hook( $f, $cb ) {}
function register_deactivation_hook( $f, $cb ) {}
function add_action( $h, $cb, $p = 10, $a = 1 ) { $GLOBALS['_wpbsl_test']['actions'][ $h ] = $cb; }
function add_filter( $h, $cb, $p = 10, $a = 1 ) {}
function add_shortcode( $t, $cb ) { $GLOBALS['_wpbsl_test']['shortcodes'][ $t ] = $cb; }
function register_block_type( $name, $args = array() ) { $GLOBALS['_wpbsl_test']['blocks'][ $name ] = $args; return true; }
function is_admin() { return true; }
function load_plugin_textdomain() { return true; }
function __( $s, $d = null ) { return $s; }
function esc_html__( $s, $d = null ) { return $s; }
function esc_html_e( $s, $d = null ) { echo $s; }
function esc_attr( $s ) { return $s; }
function esc_url( $s ) { return $s; }
function register_widget( $c ) {}
function wp_register_script( $h, $s = '', $d = array(), $v = false, $f = false ) {}
function wp_register_style( $h, $s = '', $d = array(), $v = false ) {}
function wp_enqueue_script( $h, $s = '', $d = array(), $v = false, $f = false ) {}
function wp_enqueue_style( $h, $s = '', $d = array(), $v = false ) {}
function wp_localize_script( $h, $o, $l ) {}
function get_option( $k, $d = false ) { return array_key_exists( $k, $GLOBALS['_wpbsl_test']['options'] ) ? $GLOBALS['_wpbsl_test']['options'][ $k ] : $d; }
function update_option( $k, $v ) { $GLOBALS['_wpbsl_test']['options'][ $k ] = $v; return true; }
function add_option( $k, $v ) { $GLOBALS['_wpbsl_test']['options'][ $k ] = $v; return true; }
function wp_create_nonce( $a = -1 ) { return 'nonce'; }
function admin_url( $p = '' ) { return 'http://example.test/wp-admin/' . $p; }
function get_bloginfo( $k = '' ) { return 'Test Site'; }
function wp_json_encode( $d ) { return json_encode( $d ); }
function shortcode_atts( $defaults, $atts ) { return array_merge( $defaults, (array) $atts ); }

class WP_Widget {
	public $id_base;
	public $id;
	public function __construct( $id_base = '', $name = '', $opts = array() ) { $this->id_base = strtolower( $id_base ); }
	public function get_field_id( $f ) { return $f; }
	public function get_field_name( $f ) { return $f; }
}

class wpdb_stub {
	public $prefix = 'wp_';
	public $insert_id = 0;
	public function get_charset_collate() { return ''; }
	public function prepare( $q, ...$a ) { return $q; }
	public function query( $q ) { return true; }
	public function insert( $t, $d, $f = null ) { $this->insert_id = 123; return 1; }
	public function update( $t, $d, $w, $df = null, $wf = null ) { return 1; }
	public function delete( $t, $w, $wf = null ) { return 1; }
	public function get_row( $q ) { return null; }
	public function get_results( $q ) { return array(); }
	public function get_var( $q ) { return 0; }
}
$GLOBALS['wpdb'] = new wpdb_stub();

/* --------------------------------------------------------------------------
 * 1. Helper unit tests (pure logic — the heart of the booking system).
 * ------------------------------------------------------------------------ */
require $plugin_dir . '/includes/class-wp-booking-system-luca-helpers.php';

echo "\nHelpers: nights & pricing\n";
check_equals( 1, WP_Booking_System_Luca_Helpers::calculate_nights( '2026-06-01', '2026-06-02' ), 'one night between consecutive days' );
check_equals( 7, WP_Booking_System_Luca_Helpers::calculate_nights( '2026-06-01', '2026-06-08' ), 'seven nights for a week' );
check_equals( 1, WP_Booking_System_Luca_Helpers::calculate_nights( '2026-06-08', '2026-06-01' ), 'reversed dates floor to one night' );
check_equals( 1, WP_Booking_System_Luca_Helpers::calculate_nights( 'garbage', 'also-bad' ), 'invalid dates floor to one night' );

// 2 adults @50 + 1 kid @25 over 3 nights = (100 + 25) * 3 = 375.
check_equals( 375.0, WP_Booking_System_Luca_Helpers::calculate_price( '2026-06-01', '2026-06-04', 2, 1, 50, 25 ), '2 adults + 1 kid x 3 nights = 375.00' );
// 1 adult @120.5 over 2 nights = 241.0.
check_equals( 241.0, WP_Booking_System_Luca_Helpers::calculate_price( '2026-06-01', '2026-06-03', 1, 0, 120.5, 60 ), 'fractional nightly rate rounds correctly' );
check_equals( 0.0, WP_Booking_System_Luca_Helpers::calculate_price( '2026-06-01', '2026-06-04', 0, 0, 50, 25 ), 'zero guests cost nothing' );

echo "\nHelpers: date & range validation\n";
check( WP_Booking_System_Luca_Helpers::is_valid_date( '2026-06-11' ), 'valid Y-m-d date accepted' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_date( '2026-13-40' ), 'impossible date rejected' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_date( '11-06-2026' ), 'wrong format rejected' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_date( '' ), 'empty date rejected' );
check( WP_Booking_System_Luca_Helpers::is_valid_range( '2026-06-01', '2026-06-05' ), 'check-out after check-in is a valid range' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_range( '2026-06-05', '2026-06-05' ), 'same day is not a valid range' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_range( '2026-06-05', '2026-06-01' ), 'check-out before check-in rejected' );

echo "\nHelpers: token, capacity & status\n";
check( WP_Booking_System_Luca_Helpers::is_valid_token( str_repeat( 'a', 64 ) ), '64-char hex token accepted' );
check( WP_Booking_System_Luca_Helpers::is_valid_token( bin2hex( random_bytes( 32 ) ) ), 'generated token shape accepted' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_token( str_repeat( 'a', 63 ) ), 'too-short token rejected' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_token( str_repeat( 'z', 64 ) ), 'non-hex token rejected' );
check( WP_Booking_System_Luca_Helpers::exceeds_capacity( 8, 3, 10 ), '11 guests exceed capacity of 10' );
check( ! WP_Booking_System_Luca_Helpers::exceeds_capacity( 6, 4, 10 ), 'exactly 10 guests are within capacity' );
check( WP_Booking_System_Luca_Helpers::is_valid_status( 'confirmed' ), 'confirmed is a valid status' );
check( ! WP_Booking_System_Luca_Helpers::is_valid_status( 'deleted' ), 'unknown status rejected' );

echo "\nHelpers: stay length & booking window (entry options)\n";
// Use a fixed reference date so the window tests are deterministic.
$ref = strtotime( '2026-06-01' );
check_equals( 9, WP_Booking_System_Luca_Helpers::days_until( '2026-06-10', $ref ), 'days_until counts whole days ahead' );
check_equals( -1, WP_Booking_System_Luca_Helpers::days_until( '2026-05-31', $ref ), 'days_until is negative for past dates' );
check( WP_Booking_System_Luca_Helpers::meets_stay_length( '2026-06-01', '2026-06-04', 2, 7 ), '3 nights satisfies min 2 / max 7' );
check( ! WP_Booking_System_Luca_Helpers::meets_stay_length( '2026-06-01', '2026-06-02', 2, 7 ), '1 night fails a 2-night minimum' );
check( ! WP_Booking_System_Luca_Helpers::meets_stay_length( '2026-06-01', '2026-06-15', 2, 7 ), '14 nights fails a 7-night maximum' );
check( WP_Booking_System_Luca_Helpers::meets_stay_length( '2026-06-01', '2026-06-30', 2, 0 ), 'max 0 means no upper limit on nights' );
check( WP_Booking_System_Luca_Helpers::is_within_booking_window( '2026-06-08', 7, 365, $ref ), '7 days out meets a 7-day minimum notice' );
check( ! WP_Booking_System_Luca_Helpers::is_within_booking_window( '2026-06-03', 7, 365, $ref ), '2 days out fails a 7-day minimum notice' );
check( ! WP_Booking_System_Luca_Helpers::is_within_booking_window( '2027-06-01', 0, 90, $ref ), 'a year out fails a 90-day booking window' );
check( WP_Booking_System_Luca_Helpers::is_within_booking_window( '2026-12-01', 0, 0, $ref ), 'max 0 means no upper bound on the window' );

/* --------------------------------------------------------------------------
 * 2. Boot smoke test — load the full plugin and assert registrations.
 * ------------------------------------------------------------------------ */
echo "\nPlugin boot & registration\n";
require $plugin_dir . '/wp-booking-system.php';
$instance = wp_booking_system_luca();

check( $instance instanceof WP_Booking_System_Luca, 'main instance constructed without fatal errors' );
check( $instance->database instanceof WP_Booking_System_Luca_Database, 'database subsystem initialised' );
check( $instance->frontend instanceof WP_Booking_System_Luca_Frontend, 'frontend subsystem initialised' );
check( $instance->email instanceof WP_Booking_System_Luca_Email, 'email subsystem initialised' );

$shortcodes = $GLOBALS['_wpbsl_test']['shortcodes'];
check( isset( $shortcodes['wp_booking_form_luca'] ), 'booking form shortcode registered' );
check( isset( $shortcodes['wp_booking_manage_luca'] ), 'manage booking shortcode registered' );
check( isset( $shortcodes['wp_booking_calendar_luca'] ), 'calendar shortcode registered' );

$actions = $GLOBALS['_wpbsl_test']['actions'];
foreach ( array( 'wp_ajax_wpbsl_submit_booking', 'wp_ajax_nopriv_wpbsl_submit_booking', 'wp_ajax_wpbsl_cancel_booking', 'wp_ajax_wpbsl_update_status' ) as $hook ) {
	check( isset( $actions[ $hook ] ), "AJAX handler hooked: {$hook}" );
}

// Blocks register on the WordPress `init` action; fire it to register them.
if ( isset( $actions['init'] ) ) {
	call_user_func( $actions['init'] );
}
$blocks = $GLOBALS['_wpbsl_test']['blocks'];
check( isset( $blocks['wp-booking-system/calendar'] ), 'calendar block registered' );
check( isset( $blocks['wp-booking-system/form'] ), 'booking form block registered' );

check( isset( $actions['phpmailer_init'] ), 'phpmailer_init hooked for SMTP support' );
check( isset( $actions['wp_ajax_wpbsl_send_test_email'] ), 'test-email AJAX handler hooked' );

/* --------------------------------------------------------------------------
 * 3. SMTP configuration logic (PHPMailer wiring).
 * ------------------------------------------------------------------------ */
echo "\nEmail: SMTP / PHPMailer configuration\n";

// Minimal PHPMailer test double exposing the bits configure_phpmailer touches.
class WPBSL_FakePHPMailer {
	public $Host = '';
	public $Port = 25;
	public $SMTPAuth = false;
	public $SMTPSecure = '';
	public $SMTPAutoTLS = true;
	public $Username = '';
	public $Password = '';
	public $is_smtp = false;
	public function isSMTP() { $this->is_smtp = true; }
}

$email = $instance->email;

// Disabled: PHPMailer must be left untouched (default mail transport).
$GLOBALS['_wpbsl_test']['options']['wpbsl_smtp_enabled'] = 0;
$pm = new WPBSL_FakePHPMailer();
$email->configure_phpmailer( $pm );
check( false === $pm->is_smtp, 'SMTP disabled leaves PHPMailer on default transport' );

// Enabled but no host: still must not switch to SMTP (avoids broken sends).
$GLOBALS['_wpbsl_test']['options']['wpbsl_smtp_enabled'] = 1;
$GLOBALS['_wpbsl_test']['options']['wpbsl_smtp_host'] = '';
$pm = new WPBSL_FakePHPMailer();
$email->configure_phpmailer( $pm );
check( false === $pm->is_smtp, 'SMTP enabled without a host falls back safely' );

// Enabled + Gmail TLS config: PHPMailer wired correctly.
$GLOBALS['_wpbsl_test']['options'] = array_merge(
	$GLOBALS['_wpbsl_test']['options'],
	array(
		'wpbsl_smtp_enabled'    => 1,
		'wpbsl_smtp_host'       => 'smtp.gmail.com',
		'wpbsl_smtp_port'       => 587,
		'wpbsl_smtp_encryption' => 'tls',
		'wpbsl_smtp_auth'       => 1,
		'wpbsl_smtp_username'   => 'host@gmail.com',
		'wpbsl_smtp_password'   => 'app-password',
	)
);
$pm = new WPBSL_FakePHPMailer();
$email->configure_phpmailer( $pm );
check( true === $pm->is_smtp, 'Gmail config switches PHPMailer to SMTP' );
check_equals( 'smtp.gmail.com', $pm->Host, 'SMTP host applied' );
check_equals( 587, $pm->Port, 'SMTP port applied' );
check_equals( 'tls', $pm->SMTPSecure, 'TLS encryption applied' );
check( true === $pm->SMTPAuth, 'SMTP auth enabled' );
check_equals( 'host@gmail.com', $pm->Username, 'SMTP username applied' );
check_equals( 'app-password', $pm->Password, 'SMTP password applied' );

// SSL on 465.
$GLOBALS['_wpbsl_test']['options']['wpbsl_smtp_encryption'] = 'ssl';
$GLOBALS['_wpbsl_test']['options']['wpbsl_smtp_port'] = 465;
$pm = new WPBSL_FakePHPMailer();
$email->configure_phpmailer( $pm );
check_equals( 'ssl', $pm->SMTPSecure, 'SSL encryption applied' );

// Encryption "none" disables auto-TLS and secure transport.
$GLOBALS['_wpbsl_test']['options']['wpbsl_smtp_encryption'] = 'none';
$pm = new WPBSL_FakePHPMailer();
$email->configure_phpmailer( $pm );
check_equals( '', $pm->SMTPSecure, 'encryption "none" clears SMTPSecure' );
check( false === $pm->SMTPAutoTLS, 'encryption "none" disables SMTPAutoTLS' );

/* --------------------------------------------------------------------------
 * Summary.
 * ------------------------------------------------------------------------ */
echo "\n----------------------------------------\n";
$passed = $tests_run - $tests_failed;
echo "Ran {$tests_run} checks: {$passed} passed, {$tests_failed} failed.\n";

exit( $tests_failed > 0 ? 1 : 0 );
