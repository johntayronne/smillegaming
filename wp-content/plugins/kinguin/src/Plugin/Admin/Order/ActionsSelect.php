<?php
/**
 * WooCommerce order actions.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin\Order;

use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Common\GetKeys;
use WPDesk\ILKinguin\Common\KeysEmail;

defined( 'ABSPATH' ) || exit;

class ActionsSelect {
	use Configuration;



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'woocommerce_order_actions', array( $this, 'register_actions' ) );
		add_action( 'woocommerce_order_action_get_kinguin_keys', array( $this, 'get_kinguin_keys_action' ) );
		add_action( 'woocommerce_order_action_send_kinguin_keys', array( $this, 'send_kinguin_keys_action' ) );
	}



	/**
	 * Register get & send keys for WooCommerce order actions.
	 *
	 * @param array $actions Order actions.
	 *
	 * @return array
	 */
	public function register_actions( $actions ) {

		global $theorder; // Current WC_Order.

		if ( $theorder->get_meta( '_kinguin_order' ) ) {
			if ( $theorder->get_meta( '_kinguin_keys' ) ) {
				$actions['send_kinguin_keys'] = __( 'Send Kinguin keys via e-mail', 'kinguin' );
			} else {
				$actions['get_kinguin_keys'] = __( 'Get Kinguin keys', 'kinguin' );
			}
		}

		return $actions;
	}



	/**
	 * Get games keys from Kinguin API and save to post meta.
	 *
	 * @param \WC_Order $order Current WooCommerce order obj.
	 *
	 * @return void
	 */
	public function get_kinguin_keys_action( $order ) {

		$order_details = $order->get_meta( '_kinguin_order' );

        if ( ! $order_details &&  'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
            $order_details = isset( get_post_meta( $order->get_id() )['_kinguin_order'][0] )
                ? get_post_meta( $order->get_id() )['_kinguin_order'][0]
                : [];
        }

		if ( isset($order_details['status']) && 'completed' === $order_details['status'] ) {
			try {
				( new GetKeys( $order ) )->request_game_keys_from_api();
				$order->add_order_note( sprintf(__( 'Keys downloaded manually by %s', 'kinguin' ), wp_get_current_user()->display_name ) );
			} catch ( \Exception $e ) {
				\WC_Admin_Notices::add_custom_notice( 'get_kinguin_keys', $e->getMessage() );
                \wc_get_logger()->debug( 'Error for Keys manual download: ', array( 'source' => 'kinguin-debug-log' ) );
                \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
			}
		}
	}



	/**
	 * Send games keys to customer email.
	 *
	 * @param \WC_Order $order Current WooCommerce order obj.
	 *
	 * @return void
	 */
	public function send_kinguin_keys_action( $order ) {
		try {
			$mail = new KeysEmail( $order );
			$mail->send();
			$order->add_order_note( sprintf( __( 'Games keys has been sent to %s', 'kinguin' ), $mail->get_customer_email() ) );
		} catch ( \Exception $e ) {
			\WC_Admin_Notices::add_custom_notice( 'send_kinguin_keys', $e->getMessage() );
            \wc_get_logger()->debug( 'Error for send_kinguin_keys_action: ', array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
		}
	}

}