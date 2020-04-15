<?php

namespace Uncanny_Automator;

/**
 * Class LD_LESSONDONE
 * @package uncanny_automator
 */
class LD_LESSONDONE {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'LD';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		$this->trigger_code = 'LESSONDONE';
		$this->trigger_meta = 'LDLESSON';
		$this->define_trigger();

	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		global $uncanny_automator;
		
		$args = [
			'post_type'      => 'sfwd-courses',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',
		];
		
		$options                = $uncanny_automator->helpers->recipe->options->wp_query( $args, TRUE, 'course' );
		$course_relevant_tokens = [
			'LDCOURSE'     => __( 'Course Title', 'uncanny-automator' ),
			'LDCOURSE_ID'  => __( 'Course ID', 'uncanny-automator' ),
			'LDCOURSE_URL' => __( 'Course URL', 'uncanny-automator' ),
		];
		$relevant_tokens        = [
			$this->trigger_meta          => __( 'Lesson Title', 'uncanny-automator' ),
			$this->trigger_meta . '_ID'  => __( 'Lesson ID', 'uncanny-automator' ),
			$this->trigger_meta . '_URL' => __( 'Lesson URL', 'uncanny-automator' ),
		];

		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			/* Translators: 1:Lessons 2:Number of times*/
			'sentence'            => sprintf( __( 'User completes {{a lesson:%1$s}} {{a number of:%2$s}} times', 'uncanny-automator' ), $this->trigger_meta, 'NUMTIMES' ),
			'select_option_name'  => __( 'User completes {{a lesson}}', 'uncanny-automator' ),
			'action'              => 'learndash_lesson_completed',
			'priority'            => 10,
			'accepted_args'       => 1,
			'validation_function' => array( $this, 'lesson_completed' ),
			'options'             => [
				$uncanny_automator->helpers->recipe->options->number_of_times(),
			],
			'options_group'       => [
				$this->trigger_meta => [
					$uncanny_automator->helpers->recipe->field->select_field_ajax(
						'LDCOURSE',
						__( 'Select a Course', 'uncanny-automator' ),
						$options,
						'',
						'',
						false,
						true,
						[
							'target_field' => $this->trigger_meta,
							'endpoint'     => 'select_lesson_from_course_LESSONDONE',
						],
						$course_relevant_tokens
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->trigger_meta, __( 'Select a Lesson', 'uncanny-automator' ), [ '' => __( 'Select a Course Above', 'uncanny-automator' ) ], false, false, false, $relevant_tokens),
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * Validation function when the trigger action is hit
	 *
	 * @param $data
	 */
	public function lesson_completed( $data ) {

		if ( empty( $data ) ) {
			return;
		}

		global $uncanny_automator;

		$user   = $data['user'];
		$lesson = $data['lesson'];
		$course = $data['course'];

		$args = [
			'code'    => $this->trigger_code,
			'meta'    => $this->trigger_meta,
			'post_id' => $lesson->ID,
			'user_id' => $user->ID,
		];

		$args = $uncanny_automator->maybe_add_trigger_entry( $args, false );
		if ( $args ) {
			foreach ( $args as $result ) {
				if ( true === $result['result'] ) {
					$uncanny_automator->insert_trigger_meta(
						[
							'user_id'        => $user->ID,
							'trigger_id'     => $result['args']['trigger_id'],
							'meta_key'       => 'LDCOURSE',
							'meta_value'     => $course->ID,
							'trigger_log_id' => $result['args']['get_trigger_id'],
							'run_number'     => $result['args']['run_number'],
						]
					);
					$uncanny_automator->maybe_trigger_complete( $result['args'] );
				}
			}
		}
	}
}
