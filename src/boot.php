<?php

namespace Uncanny_Automator;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * This class autoloads all files within specified directories
 * and runs EDD plugin licensing and updater
 *
 * Class Boot
 * @package Uncanny_Automator
 */
class Boot {

	/**
	 * The instance of the class
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      Boot
	 */
	private static $instance = null;

	/**
	 * The directories that are auto loaded and initialized
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array
	 */
	private static $auto_loaded_directories = null;

	/**
	 * class constructor
	 */
	private function __construct() {

		// We need to check if spl auto loading is available when activating plugin
		// Plugin will not activate if SPL extension is not enabled by throwing error
		if ( ! extension_loaded( 'SPL' ) ) {
			trigger_error( esc_html__( 'Please contact your hosting company to update to php version 5.3+ and enable spl extensions.', 'uncanny-automator' ), E_USER_ERROR );
		}

		spl_autoload_register( array( $this, 'require_class_files' ) );

		// Initialize all classes in given directories
		$this->auto_initialize_classes();

		// Load same script for free and pro
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
		// Load script front-end
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_script' ] );

		/*Weekly delete logs from /wp-content/*/
		//add_action( 'admin_init', array( $this, 'schedule_clear_debug_logs' ) );
		//add_action( 'weekly_remove_debug_logs', array( $this, 'remove_weekly_log_action_data' ) );
		
		add_action( 'rest_api_init', [ $this, 'uo_register_api' ] );
	}

	/**
	 * Set a weekly schedule to remove debug logs
	 */
	public function schedule_clear_debug_logs() {
		if ( false === as_next_scheduled_action( 'weekly_remove_debug_logs' ) ) {
			as_schedule_recurring_action( strtotime( 'midnight tonight' ), ( 7 * DAY_IN_SECONDS ), 'weekly_remove_debug_logs' );
		}
	}

	/**
	 * A callback to run when the 'weekly_remove_debug_logs' scheduled action is run.
	 */
	public function remove_weekly_log_action_data() {
		if ( ! Utilities::get_debug_mode() ) {
			$files = glob( WP_CONTENT_DIR . '/uo-*.log' );
			if ( $files ) {
				foreach ( $files as $file ) {
					unlink( $file );
				}
			}
		}
	}

	/**
	 * Licensing page styles
	 *
	 * @param $hook
	 */
	function scripts( $hook ) {

		if ( strpos( $hook, 'uncanny-automator-license-activation' ) ) {

			wp_enqueue_style( 'uap-admin-license', Utilities::get_css( 'admin/license.css' ), array(), Utilities::get_version() );

		}

	}
	
	/**
	 * Enqueue script
	 *
	 */
	public function enqueue_script() {
		global $wpdb;
		if ( is_user_logged_in() ) {
			// check if there is any closure published
			$check_closure = $wpdb->get_col( "SELECT ID FROM {$wpdb->posts} WHERE post_type LIKE 'uo-closure' AND post_status LIKE 'publish' LIMIT 1" );
			if ( ! empty( $check_closure ) ) {
				$user_id   = wp_get_current_user()->ID;
				$api_setup = [
					'root'              => esc_url_raw( rest_url() . AUTOMATOR_REST_API_END_POINT . 'uoa_redirect/' ),
					'nonce'             => \wp_create_nonce( 'wp_rest' ),
					'user_id'           => $user_id,
					'client_secret_key' => md5( 'l6fsX3vAAiJbSXticLBd' . $user_id ),
				];
				wp_register_script( 'uoapp-client', Utilities::get_js( 'uo-sseclient.js' ), [], '2.1.0' );
				wp_localize_script( 'uoapp-client', 'uoAppRestApiSetup', $api_setup );
				wp_enqueue_script( 'uoapp-client' );
			}
		}
	}

	/**
	 * Creates singleton instance of Boot class and defines which directories are auto loaded
	 *
	 * @param array $auto_loaded_directories
	 *
	 * @return Boot
	 * @since 1.0.0
	 *
	 */
	public static function get_instance( $auto_loaded_directories = [ 'core/classes', 'core/extensions' ] ) {

		if ( null === self::$instance ) {

			// Define directories were the auto loader looks for files and initializes them
			self::$auto_loaded_directories = $auto_loaded_directories;

			// Lets boot up!
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * SPL Auto Loader functions
	 *
	 * @param string $class Any
	 *
	 * @since 1.0.0
	 *
	 */
	private function require_class_files( $class ) {

		// Remove Class's namespace eg: my_namespace/MyClassName to MyClassName
		$class = str_replace( __NAMESPACE__, '', $class );
		$class = str_replace( '\\', '', $class );

		// Replace _ with - eg. eg: My_Class_Name to My-Class-Name
		$class_to_filename = str_replace( '_', '-', $class );

		// Create file name that will be loaded from the classes directory eg: My-Class-Name to my-class-name.php
		$file_name = strtolower( $class_to_filename ) . '.php';


		// Check each directory
		foreach ( self::$auto_loaded_directories as $directory ) {

			$file_path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $file_name;

			// Does the file exist
			if ( file_exists( $file_path ) ) {

				// File found, require it
				require_once( $file_path );

				// You can cannot have duplicate files names. Once the first file is found, the loop ends.
				return;
			}
		}

	}

	/**
	 * Looks through all defined directories and modifies file name to create new class instance.
	 *
	 * @since 1.0.0
	 *
	 */
	private function auto_initialize_classes() {

		// Check each directory
		foreach ( self::$auto_loaded_directories as $directory ) {

			// Get all files in directory
			$files = scandir( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $directory );

			// remove parent directory, sub directory, and silence is golden index.php
			$files = array_diff( $files, array( '..', '.', 'index.php' ) );

			// Loop through all files in directory to create class names from file name
			foreach ( $files as $file ) {

				// Load only php files
				$file_parts = pathinfo( $file );
				if ( key_exists( 'extension', $file_parts ) && 'php' !== $file_parts['extension'] ) {
					continue;
				}

				// Remove file extension my-class-name.php to my-class-name
				$file_name = str_replace( '.php', '', $file );

				// Split file name on - eg: my-class-name to array( 'my', 'class', 'name')
				$class_to_filename = explode( '-', $file_name );

				// Make the first letter of each word in array upper case - eg array( 'my', 'class', 'name') to array( 'My', 'Class', 'Name')
				$class_to_filename = array_map( function ( $word ) {
					return ucfirst( $word );
				}, $class_to_filename );

				// Implode array into class name - eg. array( 'My', 'Class', 'Name') to MyClassName
				$class_name = implode( '_', $class_to_filename );

				$class = __NAMESPACE__ . '\\' . $class_name;

				// We way want to include some class with the autoloader but not initialize them ex. interface class
				$skip_classes = apply_filters( 'Skip_class_initialization', array(), $directory, $files, $class, $class_name );
				if ( in_array( $class_name, $skip_classes ) ) {
					continue;
				}

				$path = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $file;
				//$contents = file_get_contents( $path );
				//var_dump( $contents );

				// On plugin activation,
				// 1. collect all comments from every file loaded
				// 2. collect all add_shortcode, apply_filters, and do_actions


				$some_param    = array();
				$another_param = '';

				// ex
				/*
				 * The first line is the title
				 *
				 * The next line is the description and can be mulitple lines and even html
				 * entities. <br> The '@see the_hook' must be present to make the connection.
				 *
				 * @see the_hook
				 * @since version 1.0
				 * @access plugin | module | general  // not everyone needs to se all filters.... maybe we can categories them depending on if its a module and depend file, core plugin architecture file, and not making have to @access tag
				 * @param array $some_param Then the description at the end
				 * @param string $another_param Then the description at the end
				 */
				do_action( 'the_hook', array( $this, 'the_hook_function' ), $some_param, $another_param );

				//regex101
				// regex mulitline comments:  ^\s\/\*\*?[^!][.\s\t\S\n\r]*?\*\/    <<-- tested first
				// regex multiline comments: (?<!\/)\/\*((?:(?!\*\/).|\s)*)\*\/    <<-- found another https://regex101.com/r/nW6hU2/1
				// regex add_shortcode line functions: ^.*\badd_shortcode\b.*$
				// regex add_shortcode line functions: ^.*\bdo_action\b.*$
				// regex add_shortcode line functions: ^.*\bapply_filters\b.*$

				if ( class_exists( $class ) ) {
					Utilities::add_class_instance( $class, new $class );
				}
			}
		}

	}


	/**
	 * Make clone magic method private, so nobody can clone instance.
	 *
	 * @since 1.0.0
	 */
	function __clone() {
	}

	/**
	 * Make sleep magic method private, so nobody can serialize instance.
	 *
	 * @since 1.0.0
	 */
	function __sleep() {
	}

	/**
	 * Make wakeup magic method private, so nobody can unserialize instance.
	 *
	 * @since 1.0.0
	 */
	function __wakeup() {

	}
	
	public function uo_register_api() {
		register_rest_route( AUTOMATOR_REST_API_END_POINT, '/uoa_redirect/', array(
			'methods'  => 'POST',
			'callback' => [ $this, 'send_feedback' ]
		) );
	}
	
	public function send_feedback( $request ) {
		// check if its a valid request.
		$data = $request->get_params();
		if ( isset( $data['user_id'] ) && isset( $data['client_secret'] ) && $data['client_secret'] == md5( 'l6fsX3vAAiJbSXticLBd' . $data['user_id'] ) ) {
			$user_id      = $data['user_id'];
			$redirect_url = get_option( 'UO_REDIRECTURL_' . $user_id, '' );
			// Send a simple message at random intervals.
			if ( ! empty( $redirect_url ) ) {
				delete_option( 'UO_REDIRECTURL_' . $user_id );
				return new \WP_REST_Response( array('redirect_url' => $redirect_url), 201 );
			}
		}
		return new \WP_REST_Response( array('redirect_url' => ''), 201 );
	}
}





