<?php

namespace Uncanny_Automator;

/**
 * Class GEN_USERROLE
 * @package uncanny_automator
 */
class WP_USERROLE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'USERROLE';
		$this->action_meta = 'WPROLE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name( $this->action_code ),
			'support_link'       => $uncanny_automator->get_author_support_link( $this->action_code ),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* Translators: 1:Roles*/
			'sentence'           => sprintf( __( 'Change user role to {{another role:%1$s}}', 'uncanny-automator' ), $this->action_meta ),
			'select_option_name' => __( 'Change user role to another {{role}}', 'uncanny-automator' ),
			'priority'           => 11,
			'accepted_args'      => 3,
			'execution_function' => array( $this, 'user_role' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->wp->options->wp_user_roles(),
			],
		);

		$uncanny_automator->register->action( $action );
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $user_id
	 * @param $action_data
	 * @param $recipe_id
	 */
	public function user_role( $user_id, $action_data, $recipe_id ) {

		global $uncanny_automator;

		$role = $action_data['meta'][ $this->action_meta ];

		$user_obj   = new \WP_User( (int) $user_id );
		$user_roles = $user_obj->roles;
		if ( ! in_array( 'administrator', $user_roles ) ) {
			$user_obj->set_role( $role );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
		} else {
			$error_message = __( 'For security, the change role action cannot be applied to Administrators.', 'uncanny-automator' );
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );
		}
	}
}
