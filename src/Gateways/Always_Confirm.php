<?php

namespace GQ_DEBUGGING\Gateways;

function Gin0115_Always_Confirm_Gateway() {
	class Gin0115_Always_Confirm_Gateway extends \WC_Payment_Gateway {
		public function __construct() {

			// details
			$this->id                 = 'always_confirm';
			$this->icon               = false;
			$this->has_fields         = false;
			$this->method_title       = '[DEBUG] Always Confirm';
			$this->method_description = 'Debugging gateway that always confirms as payment made.';
			$this->supports           = array(
				'products',
				'subscriptions',
				'refunds',
			);

			// settings
			$this->title       = '[DEBUG] Always Confirm';
			$this->description = 'Debugging gateway that always confirms as payment made.';
		}

		/**
		 * Confirm payemnt.
		 *
		 * @return void
		 */
		public function validate_fields() {
			return true;
		}

		/**
		 * Process order payment
		 *
		 * @param int $order_id
		 * @return void
		 */
		public function process_payment( int $order_id ) {
			$order = wc_get_order( $order_id );
			$order->payment_complete();
			wc_reduce_stock_levels( $order_id );
			$order->add_order_note( 'Order paid with [DEBUG] Always Confirm gateway.', true );
			WC()->cart->empty_cart();

			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
	}
}
