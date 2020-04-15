<?php


namespace Uncanny_Automator;


/**
 * Class Zapier_Helpers
 * @package Uncanny_Automator
 */
class Zapier_Helpers {

	/**
	 * @var Zapier_Helpers
	 */
	public $options;
	/**
	 * @var Zapier_Helpers
	 */
	public $pro;

	/**
	 * Zapier_Pro_Helpers constructor.
	 */
	public function __construct() {

		add_action( 'wp_ajax_nopriv_sendtest_zp_webhook', array( $this, 'sendtest_webhook' ) );
		add_action( 'wp_ajax_sendtest_zp_webhook', array( $this, 'sendtest_webhook' ) );
	}

	/**
	 * @param Zapier_Helpers $options
	 */
	public function setOptions( Zapier_Helpers $options ) {
		$this->options = $options;
	}

	/**
	 * @param \Uncanny_Automator_Pro\Zapier_Pro_Helpers $pro
	 */
	public function setPro( \Uncanny_Automator_Pro\Zapier_Pro_Helpers $pro ) {
		$this->pro = $pro;
	}


	/**
	 * @param $_POST
	 */
	public function sendtest_webhook() {

		global $uncanny_automator;

		$uncanny_automator->utilities->ajax_auth_check( $_POST );

		$key_values = [];
		$values     = (array) $uncanny_automator->uap_sanitize( $_POST['values'], 'mixed' );
		// Sanitizing webhook key pairs
		$pairs          = [];
		$webhook_fields = isset( $_POST['values']['WEBHOOK_FIELDS'] ) ? $_POST['values']['WEBHOOK_FIELDS'] : [];
		if ( ! empty( $webhook_fields ) ) {
			foreach ( $webhook_fields as $key_index => $pair ) {
				$pairs[] = [
					'KEY'   => sanitize_text_field( $pair['KEY'] ),
					'VALUE' => sanitize_text_field( $pair['VALUE'] ),
				];
			}
		}
		$values['WEBHOOK_FIELDS'] = $pairs;
		$request_type             = 'POST';
		if ( isset( $values['WEBHOOKURL'] ) ) {
			$webhook_url = $values['WEBHOOKURL'];

			if ( empty( $webhook_url ) ) {
				wp_send_json( [
					'type'    => 'error',
					'message' => __( 'Please enter a valid webhook url.', 'uncanny-automator' ),
				] );
			}

			for ( $i = 1; $i <= ZAPIER_SENDWEBHOOK::$number_of_keys; $i ++ ) {
				$key                = $values[ 'KEY' . $i ];
				$value              = $values[ 'VALUE' . $i ];
				$key_values[ $key ] = $value;
			}

			$fields_string = http_build_query( $key_values );

		} elseif ( isset( $values['WEBHOOK_URL'] ) ) {
			$webhook_url = $values['WEBHOOK_URL'];

			if ( empty( $webhook_url ) ) {
				wp_send_json( [
					'type'    => 'error',
					'message' => __( 'Please enter a valid webhook url.', 'uncanny-automator' ),
				] );
			}

			if ( ! isset( $values['WEBHOOK_FIELDS'] ) || empty( $values['WEBHOOK_FIELDS'] ) ) {
				wp_send_json( [
					'type'    => 'error',
					'message' => __( 'Please enter a valid fields.', 'uncanny-automator' ),
				] );
			}
			$fields = $values['WEBHOOK_FIELDS'];

			for ( $i = 0; $i <= count( $fields ); $i ++ ) {
				$key                = $fields[ $i ]['KEY'];
				$value              = $fields[ $i ]['VALUE'];
				$key_values[ $key ] = $value;
			}

			if ( 'POST' === (string) $values['ACTION_EVENT'] || 'CUSTOM' === (string) $values['ACTION_EVENT'] ) {
				$request_type = 'POST';
			} elseif ( 'GET' === (string) $values['ACTION_EVENT'] ) {
				$request_type = 'GET';
			} elseif ( 'PUT' === (string) $values['ACTION_EVENT'] ) {
				$request_type = 'PUT';
			}
		}

		if ( $key_values && ! is_null( $webhook_url ) ) {

			$args = array(
				'method'   => $request_type,
				'body'     => $key_values,
				'timeout'  => '30',
				'blocking' => false,
			);

			$response = wp_remote_request( $webhook_url, $args );

			if ( $response instanceof \WP_Error ) {
				/* Translators: 1:Webhook URL*/
				$error_message = sprintf( __( 'Error in webhook (%s) response found.', 'uncanny-automator' ), $webhook_url );
				wp_send_json( [
					'type'    => 'error',
					'message' => $error_message,
				] );
			}

			/* Translators: 1:Webhook URL*/
			$success_message = sprintf( __( 'Successfully sent data on %s.', 'uncanny-automator' ), $webhook_url );

			wp_send_json( array(
				'type'    => 'success',
				'message' => $success_message,
			) );
		}
	}
}