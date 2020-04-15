<?php

namespace Uncanny_Automator;

/**
 * Class GP_AWARDACHIEVEMENT_A
 * @package uncanny_automator
 */
class GP_AWARDACHIEVEMENT_A {
	
	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'GP';
	
	private $action_code;
	private $action_meta;
	private $quiz_list;
	
	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code = 'AWARDACHIEVEMENT';
		$this->action_meta = 'GPACHIEVEMENT';
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
			/* Translators: 1:Lessons*/
			'sentence'           => sprintf( __( 'Award {{an Achievement:%1$s}}', 'uncanny-automator' ), $this->action_meta ),
			'select_option_name' => __( 'Award {{an Achievement}}', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'award_an_achievement' ),
			'options_group'      => [
				$this->action_meta => [
					$uncanny_automator->helpers->recipe->gamipress->options->list_gp_award_types(
						__( 'Select an Achievement Type', 'uncanny-automator' ),
						'GPAWARDTYPES',
						[
							'token'        => false,
							'is_ajax'      => true,
							'target_field' => $this->action_meta,
							'endpoint'     => 'select_achievements_from_types_AWARDACHIEVEMENT',
						]
					),
					$uncanny_automator->helpers->recipe->field->select_field( $this->action_meta, __( 'Select a Award', 'uncanny-automator' ), [ '' => __( 'Select a Type Above', 'uncanny-automator' ) ] ),
				],
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
	public function award_an_achievement( $user_id, $action_data, $recipe_id ) {

		global $uncanny_automator;

		$achievement_id = $action_data['meta'][ $this->action_meta ];
		gamipress_award_achievement_to_user( absint( $achievement_id ), absint( $user_id ), get_current_user_id() );

		$uncanny_automator->complete_action( $user_id, $action_data, $recipe_id );
	}

}
