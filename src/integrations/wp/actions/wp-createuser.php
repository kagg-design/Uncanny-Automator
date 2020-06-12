<?php

namespace Uncanny_Automator;
/**
 * Class WP_CREATEUSER
 * @package Uncanny_Automator
 */
class WP_CREATEUSER {

	/**
	 * Integration code
	 * @var string
	 */
	public static $integration = 'WP';

	private $action_code;
	private $action_meta;
	private $key_generated;
	private $key;

	/**
	 * Set up Automator action constructor.
	 */
	public function __construct() {
		$this->action_code   = 'CREATEUSER';
		$this->action_meta   = 'USERNAME';
		$this->key_generated = false;
		$this->key           = null;
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
			/* translators: Action - WordPress */
			'sentence'           => sprintf( __( 'Create the user {{username:%1$s}}', 'uncanny-automator' ), $this->action_meta ),
			/* translators: Action - WordPress */
			'select_option_name' => __( 'Create a {{user}}', 'uncanny-automator' ),
			'priority'           => 10,
			'accepted_args'      => 1,
			'execution_function' => array( $this, 'create_user' ),
			// very last call in WP, we need to make sure they viewed the page and didn't skip before is was fully viewable
			'options_group'      => [
				$this->action_meta => [
					/* translators: Username field */
					$uncanny_automator->helpers->recipe->field->text_field( 'USERNAME', __( 'Username', 'uncanny-automator' ), true, 'text', '', true, __( 'The user\'s login username. Only alphanumeric, _, space, ., -, @', 'uncanny-automator' ) ),
					/* translators: Email field */
					$uncanny_automator->helpers->recipe->field->text_field( 'EMAIL', __( 'Email', 'uncanny-automator' ), true, 'text', '', true, __( 'The user email address.', 'uncanny-automator' ) ),
					/* translators: First Name field */
					$uncanny_automator->helpers->recipe->field->text_field( 'FIRSTNAME', __( 'First name', 'uncanny-automator' ), true, 'text', '', false, __( 'The user\'s first name.', 'uncanny-automator' ) ),
					/* translators: Last Name field */
					$uncanny_automator->helpers->recipe->field->text_field( 'LASTNAME', __( 'Last name', 'uncanny-automator' ), true, 'text', '', false, __( 'The user\'s last name.', 'uncanny-automator' ) ),
					/* translators: Website field */
					$uncanny_automator->helpers->recipe->field->text_field( 'WEBSITE', __( 'Website', 'uncanny-automator' ), true, 'text', '', false, __( 'The user URL.', 'uncanny-automator' ) ),
					/* translators: Password field */
					$uncanny_automator->helpers->recipe->field->text_field( 'PASSWORD', __( 'Password', 'uncanny-automator' ), true, 'text', '', false, __( 'The user password. Leave blank to get password will get automatically generated.', 'uncanny-automator' ) ),
					/* translators: Role field */
					$uncanny_automator->helpers->recipe->wp->options->wp_user_roles(),
					/* translators: Send User Notification Name field */
					$uncanny_automator->helpers->recipe->field->text_field( 'SENDREGEMAIL', __( 'Send user notification', 'uncanny-automator' ), true, 'checkbox', '', false, __( 'Send the new user an email about their account.', 'uncanny-automator' ) ),
					[
						'input_type'        => 'repeater',
						'option_code'       => 'USERMETA_PAIRS',
						/* translators: User Meta field */
						'label'             => __( 'User meta', 'uncanny-automator' ),
						'description'       => __( 'The user meta values keyed by their user meta key.', 'uncanny-automator' ),
						'required'          => false,
						'fields'            => [
							[
								'input_type'      => 'text',
								'option_code'     => 'meta_key',
								'label'           => __( 'Key', 'uncanny-automator' ),
								'supports_tokens' => true,
								'required'        => true,
							],
							[
								'input_type'      => 'text',
								'option_code'     => 'meta_value',
								'label'           => __( 'Value', 'uncanny-automator' ),
								'supports_tokens' => true,
								'required'        => true,
							],
						],
						'add_row_button'    => __( 'Add pair', 'uncanny-automator' ),
						'remove_row_button' => __( 'Remove pair', 'uncanny-automator' ),
					],
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
	 * @param $args
	 */
	public function create_user( $user_id, $action_data, $recipe_id, $args ) {


		global $uncanny_automator;

		// Username is mandatory. Return error its not valid.
		if ( isset( $action_data['meta']['USERNAME'] ) ) {
			$username = $uncanny_automator->parse->text( $action_data['meta']['USERNAME'], $recipe_id, $user_id, $args );
			if ( ! validate_username( $username ) ) {
				$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, sprintf( __( 'Invalid username:  %1$s }', 'uncanny-automator' ), $username ) );
			}
		} else {
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, __( 'Username was not set.', 'uncanny-automator' ) );

			return;
		}

		// Email is mandatory. Return error its not valid.
		if ( isset( $action_data['meta']['EMAIL'] ) ) {
			$email = $uncanny_automator->parse->text( $action_data['meta']['EMAIL'], $recipe_id, $user_id, $args );
			if ( ! is_email( $email ) ) {
				$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, sprintf( __( 'Invalid email:  %1$s }', 'uncanny-automator' ), $email ) );
			}
		} else {
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, __( 'Username was not set.', 'uncanny-automator' ) );

			return;
		}

		$userdata = array(
			'user_login' => $username,   //(string) The user's login username.
			'user_email' => $email,   //(string) The user email address.
		);

		if ( isset( $action_data['meta']['PASSWORD'] ) && ! empty( $action_data['meta']['PASSWORD'] ) ) {
			$userdata['user_pass'] = $uncanny_automator->parse->text( $action_data['meta']['PASSWORD'], $recipe_id, $user_id, $args );
		} else {
			$userdata['user_pass'] = wp_generate_password();
		}

		if ( isset( $action_data['meta']['WEBSITE'] ) && ! empty( $action_data['meta']['WEBSITE'] ) ) {
			$userdata['user_url'] = $uncanny_automator->parse->text( $action_data['meta']['WEBSITE'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['FIRSTNAME'] ) && ! empty( $action_data['meta']['FIRSTNAME'] ) ) {
			$userdata['first_name'] = $uncanny_automator->parse->text( $action_data['meta']['FIRSTNAME'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['LASTNAME'] ) && ! empty( $action_data['meta']['LASTNAME'] ) ) {
			$userdata['last_name'] = $uncanny_automator->parse->text( $action_data['meta']['LASTNAME'], $recipe_id, $user_id, $args );
		}

		if ( isset( $action_data['meta']['WPROLE'] ) && ! empty( $action_data['meta']['WPROLE'] ) ) {
			$userdata['role'] = $action_data['meta']['WPROLE'];
		}

		$user_id = wp_insert_user( $userdata );

		if ( is_wp_error( $user_id ) ) {
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, __( 'Failed to create a user.', 'uncanny-automator' ) );

			return;
		}

		$failed_meta_updates = [];


		if ( isset( $action_data['meta']['USERMETA_PAIRS'] ) && ! empty( $action_data['meta']['USERMETA_PAIRS'] ) ) {
			$fields = json_decode( $action_data['meta']['USERMETA_PAIRS'], true );

			foreach ( $fields AS $meta ) {
				if ( isset( $meta['meta_key'] ) && ! empty( $meta['meta_key'] ) && isset( $meta['meta_value'] ) && ! empty( $meta['meta_value'] ) ) {
					$key = $uncanny_automator->parse->text( $meta['meta_key'], $recipe_id, $user_id, $args );
					$value = $uncanny_automator->parse->text( $meta['meta_value'], $recipe_id, $user_id, $args );
					update_user_meta( $user_id, $key, $value );
				} else {
					$failed_meta_updates[ $meta['meta_key'] ] = $meta['meta_value'];
				}
			}
		}

		if ( ! empty( $failed_meta_updates ) ) {
			$failed_keys = "'" . implode( "','", array_keys( $failed_meta_updates ) ) . "'";
			$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id, sprintf( __( 'meta keys failed to update: %1$s', 'uncanny-automator' ), $failed_keys ) );
		}

		wp_new_user_notification( $user_id, NULL, 'both' );

		$uncanny_automator->complete->action( $user_id, $action_data, $recipe_id );
	}

}
