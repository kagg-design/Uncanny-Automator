<?php
namespace Uncanny_Automator;

use \Uncanny_Automator\Api_Server as Automator_Client;

/**
 * This class acts like a Sub Client to our API Client.
 *
 * Most of the public methods in this class corresponds to the API Actions.
 *
 * @since 4.11
 */
class Zoho_Campaigns_Actions {

	protected $client = null;
	protected $auth   = null;

	const API_ENDPOINT = 'v2/zoho-campaigns';

	/**
	 * Ensures access token is refreshed whenever Zoho_Campaigns_Actions object in created.
	 *
	 * @param Automator_Client $api_client Instance of Api_Server.
	 * @param Zoho_Campaigns_Client_Auth Instance of authentication class.
	 *
	 * @return self
	 */
	public function __construct( Automator_Client $api_client, Zoho_Campaigns_Client_Auth $auth ) {

		$this->client = $api_client;
		$this->auth   = $auth;

		// Refresh the token before anything else. Making sure we have fresh access token. Only if it expires.
		$this->auth->maybe_refresh_token( $this->client );

		return $this;

	}

	/**
	 * Adds a list to Zoho Campaign.
	 *
	 * @param array $args The Payload.
	 * @param array $action_data The action data array.
	 *
	 * @return array The response.
	 */
	public function list_add( $args = array(), $action_data ) {

		$args = wp_parse_args(
			$args,
			array(
				'list_name'   => '',
				'signup_form' => '',
				'email_ids'   => '',
			)
		);

		// Throws an Exception if args is invalid.
		$this->validate_list_add( $args );

		$body = array_merge(
			$args,
			array(
				'action' => 'list_add',
			)
		);

		return $this->request( $body, $action_data );

	}

	/**
	 * Subscribe a contact to Zoho Campaign.
	 *
	 * @param array $args The Payload.
	 * @param array $action_data The action data array.
	 *
	 * @return array The response.
	 */
	public function contact_list_sub( $args = array(), $action_data ) {

		$args = wp_parse_args(
			$args,
			array(
				'list_key' => '',
				'contact'  => '',
				'topic_id' => '',
			)
		);

		if ( ! filter_var( $args['contact'], FILTER_VALIDATE_EMAIL ) ) {
			throw new \Exception( 'Invalid email address format. Check token value.', 400 );
		}

		$body = array_merge(
			$args,
			array(
				'action' => 'contact_list_sub',
			)
		);

		return $this->request( $body, $action_data );

	}

	/**
	 * Unsubscribe a contact from Zoho Campaign.
	 *
	 * @param array $args The Payload.
	 * @param array $action_data The action data array.
	 *
	 * @return array The response.
	 */
	public function contact_list_unsub( $args = array(), $action_data ) {

		$args = wp_parse_args(
			$args,
			array(
				'list_key' => '',
				'contact'  => '',
			)
		);

		// @todo Move to validator class e.g. $this->validator->validate_email( $args['contact'] );
		if ( ! filter_var( $args['contact'], FILTER_VALIDATE_EMAIL ) ) {
			throw new \Exception( 'Invalid email address format. Check token value.', 400 );
		}

		$body = array_merge(
			$args,
			array(
				'action' => 'contact_list_unsub',
			)
		);

		return $this->request( $body, $action_data );

	}

	/**
	 * Unsubscribe the contract.
	 *
	 * @param array $args The Payload.
	 * @param array $action_data The action data array.
	 *
	 * @return array The response.
	 */
	public function contact_donotmail_move( $contact = '', $action_data = null ) {

		if ( ! filter_var( $contact, FILTER_VALIDATE_EMAIL ) ) {
			throw new \Exception( 'Invalid email address format. Check token value.', 400 );
		}

		$body['action']  = 'contact_donotmail_move';
		$body['contact'] = $contact;

		return $this->request( $body, $action_data );

	}

	protected function request( $body, $action_data = null ) {

		$body['access_token'] = $this->auth->get_access_token();

		$params = array(
			'endpoint' => self::API_ENDPOINT,
			'body'     => $body,
			'action'   => $action_data,
			'timeout'  => 45,
		);

		$response = $this->client::api_call( $params );

		$this->handle_zoho_campaigns_errors( $response );

		return $response;

	}

	/**
	 * Handle common errors.
	 *
	 * @param array $response The client response.
	 *
	 * @throws Exception
	 *
	 * @return array The response.
	 */
	protected function handle_zoho_campaigns_errors( $response ) {

		$is_error = isset( $response['data']['status'] ) && 'error' === $response['data']['status'];

		if ( ! $is_error ) {
			return true;
		}

		// Throws a nicer error message if there is.
		if ( isset( $response['data']['message'] ) && isset( $response['data']['code'] ) ) {

			// Throw error from Zoho if there are any.
			throw new \Exception( 'Zoho Campaigns API has responded with error code ' . $response['data']['code'] . ': ' . $response['data']['message'], 400 );

		}

		// Otherwise, throw anything is useful.
		throw new \Exception( wp_json_encode( $response['data'] ), 400 );

	}

	/**
	 * Validates adding of list.
	 *
	 * @param array $args The body payload.
	 *
	 * @throws Exception When there is an error.
	 *
	 * @return void
	 */
	protected function validate_list_add( $args = array() ) {

		if ( empty( $args['list_name'] ) ) {
			throw new \Exception( 'Error: parameter `list name` is empty. Check token value.', 400 );
		}

		if ( empty( $args['signup_form'] ) ) {
			throw new \Exception( 'Error: parameter `signup_form` is empty.', 400 );
		}

		if ( empty( $args['email_ids'] ) ) {
			throw new \Exception( 'Error: parameter `email_ids` is empty.', 400 );
		}

	}

	/**
	 * Fetches all list and send as JSON.
	 *
	 * @return array
	 */
	public function wp_ajax_handler_lists_fetch() {

		$err_message = '';
		$success     = true;
		$options     = array();

		try {

			$response = $this->request( array( 'action' => 'list_fetch' ), null );

			if ( empty( $response['data']['list_of_details'] ) || ! is_array( $response['data']['list_of_details'] ) ) {
				throw new \Exception( 'Unable to find any list from the connected Zoho Campaign account.', 404 );
			}

			foreach ( $response['data']['list_of_details'] as $list ) {
				$options[] = array(
					'text'  => $list['listname'],
					'value' => $list['listkey'],
				);
			}
		} catch ( \Exception $e ) {

			$success     = false;
			$err_message = $e->getCode() . ': ' . $e->getMessage();

		}

		$response = array(
			'success' => $success,
			'error'   => $err_message,
			'options' => $options,
		);

		return $response;

	}

	/**
	 * Fetches all topics and send as JSON.
	 *
	 * @return array
	 */
	public function wp_ajax_handler_topics_fetch() {

		$err_message = '';
		$success     = true;
		$options     = array();

		try {

			$response = $this->request( array( 'action' => 'topic_fetch' ), null );

			foreach ( $response['data']['topicDetails'] as $topic ) {

				$options[] = array(
					'text'  => $topic['topicName'],
					'value' => $topic['topicId'],
				);
			}
		} catch ( \Exception $e ) {

			$success     = false;
			$err_message = $e->getCode() . ': ' . $e->getMessage();

		}

		$response = array(
			'success' => $success,
			'error'   => $err_message,
			'options' => $options,
		);

		return $response;

	}

}