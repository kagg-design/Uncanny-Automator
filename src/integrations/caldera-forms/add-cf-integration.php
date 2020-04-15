<?php

namespace Uncanny_Automator;

/**
 * Class Add_Cf_Integration
 * @package uncanny_automator
 */
class Add_Cf_Integration {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'CF';

	/**
	 * Add_Integration constructor.
	 */
	public function __construct() {

		// Add directories to auto loader
		add_filter( 'uncanny_automator_integration_directory', [ $this, 'add_integration_directory_func' ], 11 );
		
		// Add code, name and icon set to automator
		add_action( 'uncanny_automator_add_integration', [ $this, 'add_integration_func' ] );

		// Verify is the plugin is active based on integration code
		add_filter( 'uncanny_automator_maybe_add_integration', [
			$this,
			'plugin_active',
		], 30, 2 );
	}

	/**
	 * Only load this integration and its triggers and actions if the related plugin is active
	 *
	 * @param $status
	 * @param $code
	 *
	 * @return bool
	 */
	public function plugin_active( $status, $code ) {

		if ( self::$integration === $code ) {
			if ( class_exists( 'Caldera_Forms' ) ) {
				$status = true;
			} else {
				$status = false;
			}
		}

		return $status;
	}

	/**
	 * Set the directories that the auto loader will run in
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
		global $uncanny_automator;

		$uncanny_automator->register->integration( self::$integration, array(
			'name'        => 'Caldera Forms',
			'icon_16'     => Utilities::get_integration_icon( 'integration-calderaforms-icon-16.png' ),
			'icon_32'     => Utilities::get_integration_icon( 'integration-calderaforms-icon-32.png' ),
			'icon_64'     => Utilities::get_integration_icon( 'integration-calderaforms-icon-64.png' ),
			'logo'        => Utilities::get_integration_icon( 'integration-calderaforms.png' ),
			'logo_retina' => Utilities::get_integration_icon( 'integration-calderaforms@2x.png' ),
		) );
	}
}
