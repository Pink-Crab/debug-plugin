<?php

declare(strict_types=1);

/**
 * Initlaises the gateway.
 *
 * @return void
 */
function recurring_the_fail_init() {
	class Gin0115_Recurring_Then_Fail_Gateway extends \WC_Payment_Gateway {

		public const GATEWAY_ID = 'gin0115_recurring_then_fail';

		public function __construct() {

			$this->id                 = self::GATEWAY_ID;
			$this->icon               = 'https://avatars.githubusercontent.com/u/28779094?v=4';
			$this->has_fields         = true;
			$this->method_title       = 'Recurring Then Fail Payments';
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
			$this->title = '[TEST] Recurring Then Fail';

			// This action hook saves the settings
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		}

		/**
		 * Registers the form fields used in settings
		 *
		 * @return void
		 */
		public function init_form_fields() {
			$this->form_fields = array(
				'fail_on_next' => array(
					'title'       => 'Fail on next',
					'label'       => 'Fail on next payment',
					'type'        => 'checkbox',
					'description' => '',
					'default'     => 'no',
				),
			);
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
			// Check if set to fail next time.
			$settings = get_option(
				'woocommerce_gin0115_recurring_then_fail_settings',
				array(
					'fail_on_next' => 'no',
					'enabled'      => 'yes',
				)
			);

			// If set and set to fail, return false;
			if ( array_key_exists( 'fail_on_next', $settings ) && $settings['fail_on_next'] === 'yes' ) {
				return false;
			}

			// If it doesnt exist or is set and not set to fail.
			if ( ! array_key_exists( 'fail_on_next', $settings ) || $settings['fail_on_next'] === 'no' ) {

				// Sets the new options to fail next time.
				$options = array(
					'enabled'      => 'yes',
					'fail_on_next' => 'yes',
				);
				update_option( 'woocommerce_gin0115_recurring_then_fail_settings', $options );
				return true;
			}

			// Just fallback to true.
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

