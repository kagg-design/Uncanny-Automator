<?php

namespace Uncanny_Automator;

/**
 * Class Tutorlms_Helpers
 *
 * @package Uncanny_Automator
 */
class Tutorlms_Helpers {
	/**
	 * @var Tutorlms_Helpers
	 */
	public $options;

	/**
	 * @var \Uncanny_Automator_Pro\Tutorlms_Pro_Helpers
	 */
	public $pro;

	/**
	 * @param Tutorlms_Helpers $options
	 */
	public function setOptions( Tutorlms_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param \Uncanny_Automator_Pro\Tutorlms_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Tutorlms_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}

	/**
	 * Creates options for trigger.
	 *
	 * @since 2.4.0
	 */
	public function all_tutorlms_lessons( $label = null, $option_code = 'TUTORLMSLESSON', $any_option = false ) {

		if ( ! $label ) {
			$label = __( 'Lesson', 'uncanny-automator' );
		}

		// post query arguments.
		$args = [
			'post_type'      => \tutor()->lesson_post_type,
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',

		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, __( 'Any lesson', 'uncanny-automator' ) );

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
			'relevant_tokens' => [
				$option_code          => __( 'Lesson title', 'uncanny-automator' ),
				$option_code . '_ID'  => __( 'Lesson ID', 'uncanny-automator' ),
				$option_code . '_URL' => __( 'Lesson URL', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_all_tutorlms_lessons', $option );

	}

	/**
	 * Creates options for trigger.
	 *
	 * @since 2.4.0
	 */
	public function all_tutorlms_courses( $label = null, $option_code = 'TUTORLMSCOURSE', $any_option = false ) {

		if ( ! $label ) {
			$label = __( 'Course', 'uncanny-automator' );
		}

		// post query arguments.
		$args = [
			'post_type'      => \tutor()->course_post_type,
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',

		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, __( 'Any course', 'uncanny-automator' ) );

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
			'relevant_tokens' => [
				$option_code          => __( 'Course title', 'uncanny-automator' ),
				$option_code . '_ID'  => __( 'Course ID', 'uncanny-automator' ),
				$option_code . '_URL' => __( 'Course URL', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_all_tutorlms_courses', $option );

	}

	/**
	 * Creates options for trigger.
	 *
	 * @since 2.4.0
	 */
	public function all_tutorlms_quizzes( $label = null, $option_code = 'TUTORLMSQUIZ', $any_option = false ) {

		if ( ! $label ) {
			$label = __( 'Quiz', 'uncanny-automator' );
		}

		// post query arguments.
		$args = [
			'post_type'      => 'tutor_quiz',
			'posts_per_page' => 999,
			'orderby'        => 'title',
			'order'          => 'ASC',
			'post_status'    => 'publish',

		];

		global $uncanny_automator;
		$options = $uncanny_automator->helpers->recipe->options->wp_query( $args, $any_option, __( 'Any quiz', 'uncanny-automator' ) );

		$option = [
			'option_code'     => $option_code,
			'label'           => $label,
			'input_type'      => 'select',
			'required'        => true,
			// to setup example, lets define the value the child will be based on
			'current_value'   => false,
			'validation_type' => 'text',
			'options'         => $options,
			'relevant_tokens' => [
				$option_code          => __( 'Quiz title', 'uncanny-automator' ),
				$option_code . '_ID'  => __( 'Quiz ID', 'uncanny-automator' ),
				$option_code . '_URL' => __( 'Quiz URL', 'uncanny-automator' ),
			],
		];

		return apply_filters( 'uap_option_all_tutorlms_quizzes', $option );

	}

	/**
	 * Calculates percentage scored.
	 *
	 * @param object $attempt The quiz attempt object.
	 *
	 * @return int
	 * @since 2.4.0
	 */
	public function get_percentage_scored( $attempt ) {
		return number_format( ( $attempt->earned_marks * 100 ) / $attempt->total_marks );
	}

	/**
	 * Retrieves required percentage.
	 *
	 * @param object $attempt The quiz attempt object.
	 *
	 * @return int
	 * @since 2.4.0
	 */
	public function get_percentage_required( $attempt ) {
		return (int) \tutor_utils()->get_quiz_option( $attempt->quiz_id, 'passing_grade', 0 );
	}

	/**
	 * Checks if a quiz attempt was successful.
	 *
	 * @param $attempt object.
	 *
	 * @since 2.4.0
	 */
	public function was_quiz_attempt_successful( $attempt ) {

		// if the earned grade is less than or equal to zero, they failed.
		if ( $attempt->earned_marks <= 0 ) {
			return false;
		}

		// return pass or fail based on whether the required score was met.
		return ( $this->get_percentage_scored( $attempt ) >= $this->get_percentage_required( $attempt ) );
	}
}
