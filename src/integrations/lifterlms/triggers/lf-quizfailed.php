<?php

namespace Uncanny_Automator;

/**
 * Class LF_QUIZFAILED
 * @package uncanny_automator
 */
class LF_QUIZFAILED {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LF';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LFQUIZFAILED';
		$this->trigger_meta = 'LFQUIZ';
		$this->define_trigger();
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* Translators: 1:Courses 2:Number of times*/
			'sentence'            => sprintf( __( 'User fails {{a/any quiz:%1$s}} {{a number of:%2$s}} times', 'uncanny-automator' ), $this->trigger_meta, 'NUMTIMES' ),
			'select_option_name'  => __( 'User fails {{a/any quiz}}', 'uncanny-automator' ),
			'action'              => 'lifterlms_quiz_failed',
			'priority'            => 20,
			'accepted_args'       => 3,
			'validation_function' => array( $this, 'lf_quiz_failed' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->lifterlms->options->all_lf_quizs( null, $this->trigger_meta ),
				$uncanny_automator->helpers->recipe->options->number_of_times(),
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param string $user_id   .
	 * @param string $quiz_id   .
	 * @param object $quiz_obj  .
	 */
	public function lf_quiz_failed( $user_id, $quiz_id, $quiz_obj ) {

		if ( empty( $user_id ) ) {
			return;
		}

		global $uncanny_automator;

		$args = [
			'code'           => $this->trigger_code,
			'meta'           => $this->trigger_meta,
			'post_id'        => intval( $quiz_id ),
			'user_id'        => $user_id,
		];

		$uncanny_automator->maybe_add_trigger_entry( $args );
	}
}
