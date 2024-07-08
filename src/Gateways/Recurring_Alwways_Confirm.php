<?php

declare(strict_types=1);

/**
 * Initlaises the gateway.
 *
 * @return void
 */
function recurring_init() {
	class Gin0115_Recurring_Gateway extends \WC_Payment_Gateway {

		public const GATEWAY_ID = 'gin0115_recurring';

		public function __construct() {

			$this->id                 = self::GATEWAY_ID;
			$this->icon               = 'https://avatars.githubusercontent.com/u/28779094?v=4';
			$this->has_fields         = true;
			$this->method_title       = 'Recurring Payments';
			$this->method_description = 'A gateway to mock Recurring Payments';

			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products',
				'subscriptions',
			);

			// Method with all the options fields
			$this->init_form_fields();

			// Load the settings.
			$this->init_settings();
			$this->title           = 'Gin0115 Test  Recurring Gateway (WILL NOT CHARGE)';
			$this->description     = $this->get_option( 'description' );
			$this->enabled         = $this->get_option( 'enabled' );
			$this->testmode        = 'yes' === $this->get_option( 'testmode' );
			$this->private_key     = $this->testmode ? $this->get_option( 'test_private_key' ) : $this->get_option( 'private_key' );
			$this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );

			// We need custom JavaScript to obtain a token
			add_action( 'wp_enqueue_scripts', array( $this, 'payment_scripts' ) );

		}

		/**
		 * Registers the form fields used in settings
		 *
		 * @return void
		 */
		public function init_form_fields() {
		}

		/**
		 * Form fields displayed at checkout.
		 *
		 * @return void
		 */
		public function payment_fields() {
			echo 'This gateway will allow you pay for a subscription style product.';
		}

		/*
		* Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
		*/
		public function payment_scripts() {

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
		 * Processes the payment
		 *
		 * @param int $order_id
		 * @return array{result:string,redirect:string}
		 */
		public function process_payment( $order_id ) {
			$order = wc_get_order( $order_id );
			$order->payment_complete();
			wc_reduce_stock_levels( $order_id );
			$order->add_order_note( 'Hey, your order is paid! Thank you!', true );
			WC()->cart->empty_cart();

			// Redirect to the thank you page
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);

		}

		public function webhook() {}
	}
}

