<?php

namespace Uncanny_Automator;

/**
 * Class LF_MARKLESSONDONE
 * @package uncanny_automator
 */
class LF_MARKLESSONDONE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LF';

	private $action_code;
	private $action_meta;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'LFMARKLESSONDONE-A';
		$this->action_meta = 'LFLESSON';
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
			/* Translators: 1:Courses*/
			'sentence'           => sprintf( __( 'Mark {{a lesson:%1$s}} complete', 'uncanny-automator' ), $this->action_meta ),
			'select_option_name' => __( 'Mark {{a lesson}} complete', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'lf_mark_lesson_done' ),
			'options'            => [
				$uncanny_automator->helpers->recipe->lifterlms->options->all_lf_lessons( __( 'Select a Lesson', 'uncanny-automator' ), $this->action_meta, false ),
			],
		);

		$uncanny_automator->register->action( $action );
	}


	/**
	 * Validation function when the action is hit.
	 *
	 * @param string $user_id user id.
	 * @param array  $action_data action data.
	 * @param string $recipe_id recipe id.
	 */
	public function lf_mark_lesson_done( $user_id, $action_data, $recipe_id ) {

		global $uncanny_automator;

		if ( ! function_exists( 'llms_mark_complete' ) ) {
			$error_message = 'The function llms_mark_complete does not exist';
			$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id, $error_message );

			return;
		}

		$lesson_id = $action_data['meta'][ $this->action_meta ];

		// Mark lesson completed.
		llms_mark_complete( $user_id, $lesson_id, 'lesson' );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}
}
