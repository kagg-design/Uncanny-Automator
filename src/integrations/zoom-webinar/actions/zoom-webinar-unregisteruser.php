<?php

namespace Uncanny_Automator;

/**
 * Class ZOOM_WEBINAR_UNREGISTERUSER
 *
 * @package Uncanny_Automator
 */
class ZOOM_WEBINAR_UNREGISTERUSER {

	/**
	 * Integration code
	 *
	 * @var string
	 */
	public static $integration = 'ZOOMWEBINAR';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'ZOOMWEBINARUNREGISTERUSER';
		$this->action_meta = 'ZOOMWEBINAR';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		$action = array(
			'author'             => Automator()->get_author_name( $this->action_code ),
			'support_link'       => Automator()->get_author_support_link( $this->action_code, 'knowledge-base/zoom/' ),
			'is_pro'             => false,
			//'is_deprecated'      => true,
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			'sentence'           => sprintf( __( 'Remove the user from {{a webinar:%1$s}}', 'uncanny-automator' ), $this->action_meta ),
			'select_option_name' => __( 'Remove the user from {{a webinar}}', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'zoom_webinar_unregister_user' ),
			'options_callback'   => array( $this, 'load_options' ),
		);

		Automator()->register->action( $action );
	}

	/**
	 * load_options
	 *
	 * @return void
	 */
	public function load_options() {
		return array(
			'options' => array(
				Automator()->helpers->recipe->zoom_webinar->get_webinars( null, $this->action_meta ),
			),
		);
	}

	/**
	 * Validation function when the action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function zoom_webinar_unregister_user( $user_id, $action_data, $recipe_id, $args ) {

		try {

			if ( empty( $user_id ) ) {
				throw new \Exception( __( 'User was not found.', 'uncanny-automator' ) );
			}

			$webinar_key = Automator()->parse->text( $action_data['meta'][ $this->action_meta ], $recipe_id, $user_id, $args );

			if ( empty( $webinar_key ) ) {
				throw new \Exception( __( 'Webinar was not found.', 'uncanny-automator' ) );
			}

			$webinar_key = str_replace( '-objectkey', '', $webinar_key );
			
			$user  = get_userdata( $user_id );
			$email = $user->user_email;

			$result = Automator()->helpers->recipe->zoom_webinar->unregister_user( $email, $webinar_key, $action_data );

			Automator()->complete_action( $user_id, $action_data, $recipe_id );

		} catch ( \Exception $e ) {
			$action_data['do-nothing']           = true;
			$action_data['complete_with_errors'] = true;
			Automator()->complete_action( $user_id, $action_data, $recipe_id, $e->getMessage() );
		}
	}
}
