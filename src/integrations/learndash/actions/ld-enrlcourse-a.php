<?php

namespace Uncanny_Automator;

/**
 * Class LD_ENRLCOURSE_A
 * @package uncanny_automator
 */
class LD_ENRLCOURSE_A {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'ENRLCOURSE-A';
		$this->action_meta = 'LDCOURSE';
		$this->define_action();
	}

	/**
	 * Define and register the action by pushing it into the Automator object
	 */
	public function define_action() {

		global $uncanny_automator;

		$action = array(
			'author'             => $uncanny_automator->get_author_name(),
			'support_link'       => $uncanny_automator->get_author_support_link(),
			'integration'        => self::$integration,
			'code'               => $this->action_code,
			/* Translators: 1:Courses*/
			'sentence'           => sprintf( __( 'Enroll user in {{a course:%1$s}}', 'uncanny-automator' ), $this->action_meta ),
			'select_option_name' => __( 'Enroll user in {{a course}}', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'enroll_in_course' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->learndash->options->all_ld_courses( null, 'LDCOURSE', false ),
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
	public function enroll_in_course( $user_id, $action_data, $recipe_id ) {

		global $uncanny_automator;

		if ( ! function_exists( 'ld_update_course_access' ) ) {
			$error_message = 'The function ld_update_course_access does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$course_id = $action_data['meta'][ $this->action_meta ];

		//Enroll to New Course
		ld_update_course_access( $user_id, $course_id );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
