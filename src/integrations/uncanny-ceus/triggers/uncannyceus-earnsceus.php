<?php

namespace Uncanny_Automator;

/**
 * Class UC_CODEREDEEMED
 * @package Uncanny_Automator
 */
class UNCANNYCEUS_EARNSCEUS {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'UNCANNYCEUS';

	private $trigger_code;
	private $trigger_meta;

	/**
	 * Set up Automator trigger constructor.
	 */
	public function __construct() {
		// The hook is only available on or after CEU version 3.0.7
		$version = \uncanny_ceu\Utilities::get_version();
		if ( version_compare( $version, '3.0.6', '>' ) ) {

			// Ths trigger is running through a crob job .. We need to let is pass through cron checks
			add_filter( 'uap_run_automator_actions', [ $this, 'maybe_allow_triggers_to_actionify', 10, 2 ] );

			$this->trigger_code = 'EARNSCEUS';
			$this->trigger_meta = 'AMOUNTSCEUS';
			$this->define_trigger();
		}
	}

	public function maybe_allow_triggers_to_actionify( $run_automator_actions, $REQUEST ) {

		if ( false === $run_automator_actions ) {

			$next_crons_jobs = wp_get_ready_cron_jobs();

			foreach ( $next_crons_jobs as $cron_job ) {
				if ( isset( $cron_job['uo_ceu_scheduled_learndash_course_completed'] ) ) {
					return true;
				}
			}
		}

		return $run_automator_actions;
	}

	/**
	 * Define and register the trigger by pushing it into the Automator object
	 */
	public function define_trigger() {

		$credit_designation_label_plural = get_option( 'credit_designation_label_plural', __( 'CEUs', 'uncanny-ceu' ) );

		global $uncanny_automator;
		$trigger = array(
			'author'              => $uncanny_automator->get_author_name( $this->trigger_code ),
			'support_link'        => $uncanny_automator->get_author_support_link( $this->trigger_code ),
			'integration'         => self::$integration,
			'code'                => $this->trigger_code,
			'meta'                => $this->trigger_meta,
			/* translators: Logged-in trigger - Uncanny CEUs. 1. Credit designation label (plural) */
			'sentence'            => sprintf( esc_attr__( 'The total number of %1$s earned by a user is greater than or equal to {{a specific number:%2$s}}', 'uncanny-automator' ), $credit_designation_label_plural, $this->trigger_meta ),
			/* translators: Logged-in trigger - Uncanny CEUs. 1. Credit designation label (plural) */
			'select_option_name'  => sprintf( esc_attr__( 'The total number of %1$s earned by a user is greater than or equal to {{a specific number}}', 'uncanny-automator' ), $credit_designation_label_plural ),
			'action'              => 'ceus_after_updated_user_ceu_record',
			'priority'            => 20,
			'accepted_args'       => 7,
			'validation_function' => array( $this, 'updated_user_ceu_record' ),
			'options'             => [
				[
					'option_code'     => $this->trigger_meta,
					/* translators: Uncanny CEUs. 1. Credit designation label (plural) */
					'label'           => sprintf( esc_attr__( 'Number of %1$s', 'uncanny-automator' ), $credit_designation_label_plural ),
					'input_type'      => 'int',
					'validation_type' => 'integer',
					'required' => true
				],
			],
		);

		$uncanny_automator->register->trigger( $trigger );

		return;
	}

	/**
	 * @param $current_user
	 * @param $is_manual_creation
	 * @param $completion_date
	 * @param $current_course_id
	 * @param $current_course_title
	 * @param $course_slug
	 * @param $ceu_value
	 */
	public function updated_user_ceu_record( $current_user, $is_manual_creation, $completion_date, $current_course_id, $current_course_title, $course_slug, $ceu_value ) {

		global $uncanny_automator;

		// The class contains all ceu creation code
		$ceu_shortcodes = \uncanny_ceu\Utilities::get_class_instance( 'CeuShortcodes' );

		$atts       = [ 'user-id' => $current_user->ID ];
		$total_ceus = absint( $ceu_shortcodes->uo_ceu_total( $atts ) );
		$ceu_value  = absint( $ceu_value );

		if ( ! $total_ceus || ! $ceu_value ) {
			return;
		}

		// Get all recipes that have this trigger
		$recipes = $uncanny_automator->get->recipes_from_trigger_code( $this->trigger_code );
		// Get the specific WPFFORUMID meta data from the recipes
		$require_ceu_amount = $uncanny_automator->get->meta_from_recipes( $recipes, $this->trigger_meta );
		$matched_recipe_ids = [];

		// Loop through recipe
		foreach ( $recipes as $recipe_id => $recipe ) {
			foreach ( $recipe['triggers'] as $trigger ) {

				$trigger_id = $trigger['ID'];
				$ceu_amount = $require_ceu_amount[ $recipe_id ][ $trigger_id ];

				if ( $total_ceus >= absint( $ceu_amount ) ) {
					$matched_recipe_ids[] = [
						'recipe_id'  => $recipe_id,
						'trigger_id' => $trigger_id,
					];
				}
			}
		}

		if ( ! empty( $matched_recipe_ids ) ) {
			foreach ( $matched_recipe_ids as $matched_recipe_id ) {
				$pass_args = [
					'code'             => $this->trigger_code,
					'meta'             => $this->trigger_meta,
					'user_id'          => $current_user->ID,
					'recipe_to_match'  => $matched_recipe_id['recipe_id'],
					'trigger_to_match' => $matched_recipe_id['trigger_id'],
					'ignore_post_id'   => true,
					'is_signed_in'     => true,
				];

				$args = $uncanny_automator->maybe_add_trigger_entry( $pass_args, false );

				if ( $args ) {
					foreach ( $args as $result ) {
						if ( true === $result['result'] ) {
							$uncanny_automator->maybe_trigger_complete( $result['args'] );
						}
					}
				}
			}

		}
	}
}