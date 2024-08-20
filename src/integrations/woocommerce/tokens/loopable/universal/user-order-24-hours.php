<?php
namespace Uncanny_Automator\Integrations\Woocommerce\Tokens\Loopable\Universal;

use Uncanny_Automator\Services\Loopable\Loopable_Token_Collection;
use Uncanny_Automator\Services\Loopable\Universal_Loopable_Token;
use Uncanny_Automator\Woocommerce_Helpers;

/**
 * User_Order_24_Hours
 *
 * @package Uncanny_Automator\Integrations\Woocommerce\Tokens\Loopable
 */
class User_Order_24_Hours extends Universal_Loopable_Token {

	/**
	 * Register loopable token.
	 *
	 * @return void
	 */
	public function register_loopable_token() {

		$child_tokens = Woocommerce_Helpers::get_timed_orders_loopable_tokens();

		$this->set_id( 'USER_ORDERS_TWENTY_FOUR_HOURS' );
		$this->set_name( _x( "User's orders in the past 24 hours", 'Woo', 'uncanny-automator' ) );
		$this->set_log_identifier( '#Order ID: {{ORDER_ID}} - {{DATE_CREATED}}' );
		$this->set_child_tokens( $child_tokens );

	}

	/**
	 * Hydrate the tokens.
	 *
	 * @param mixed $args
	 * @return Loopable_Token_Collection
	 */
	public function hydrate_token_loopable( $args ) {

		$loopable = new Loopable_Token_Collection();

		$user_id = absint( $args['user_id'] ?? 0 );

		$orders = (array) Woocommerce_Helpers::get_user_orders( $user_id, '24_hours' );

		foreach ( $orders as $order ) {
			if ( false !== $order ) {
				$loopable->create_item(
					array(
						'ORDER_ID'     => $order['order_id'],
						'DATE_CREATED' => $order['date_created'],
						'TOTAL'        => $order['total'],
						'STATUS'       => $order['status'],
						'ITEMS'        => $order['items'],
					)
				);
			}
		}

		return $loopable;

	}

}