<?php

namespace Uncanny_Automator;

/**
 * Class Add_Wpsp_Integration
 * @package Uncanny_Automator
 */
class Add_Wpsp_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WPSIMPLEPAY';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $plugin
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $plugin ) {

		if ( self::$integration === $plugin ) {
			if ( defined( 'SIMPLE_PAY_VERSION' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * et the directories that the auto loader will run in
	 *
	 * @param $directory
	 *
	 * @return array
	 */
	public function add_integration_directory_func( $directory ) {

		$directory[] = dirname( __FILE__ ) . '/helpers';
		$directory[] = dirname( __FILE__ ) . '/actions';
		$directory[] = dirname( __FILE__ ) . '/triggers';
		$directory[] = dirname( __FILE__ ) . '/tokens';

		return $directory;
	}

	/**
	 * Register the integration by pushing it into the global automator object
	 */
	public function add_integration_func() {



		Automator()->register->integration( self::$integration, array(
			'name'     => 'WP Simple Pay',
			'icon_svg' => Utilities::automator_get_integration_icon( __DIR__ . '/img/wp-simple-pay-icon.svg' ),
		) );
	}

}
