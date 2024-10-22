<?php
/**
 * Get keys from Kinguin API.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Common;

use WPDesk\ILKinguin\Admin\KinguinAPI;
use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class GetKeys {
	use Configuration;

	/**
	 * WooCommerce order obj.
	 *
	 * @var \WC_Order WooCommerce order obj.
	 */
	private $order;



	/**
	 * KeysEmail constructor.
	 *
	 * @param \WC_Order $order WooCommerce order obj.
	 */
	public function __construct( \WC_Order $order ) {
		$this->order = $order;
	}



	/**
	 * Get Kinguin orderId.
	 *
	 * @return string
	 */
	private function get_kinguin_order_id() : string {
		$order_details = $this->order->get_meta( '_kinguin_order' );

        if ( ! $order_details &&  'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
            $order_details = isset( get_post_meta( $this->order->get_id() )['_kinguin_order'][0] )
                ? get_post_meta( $this->order->get_id() )['_kinguin_order'][0]
                : [];
        }


		return isset( $order_details['orderId'] ) ? $order_details['orderId'] : '';
	}



	/**
	 * Get keys for single Kinguin order ID.
	 *
	 * @return object
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException No Api Key provided.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException    Exception for any other status code than 200.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException  No connection.
	 */
	public function get() {
		try {
			return ( new KinguinAPI() )->get( $this->get_api_url() . '/v2/order/' . $this->get_kinguin_order_id() . '/keys' );

		} catch ( \Exception $e ) {
            \wc_get_logger()->debug( 'Kinguin get action error: ', array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( 'Kinguin get action error: ', array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
			throw $e;
		}
	}



	/**
	 * Save keys to order post meta
	 *
	 * @param mixed $keys Kinguin keys.
	 */
	public function save_keys_to_order_meta( $keys ) {

        $keys_saved = false;
        $order = wc_get_order( $this->order->get_id() );
        if ( $order && !is_wp_error($order) ) {
            if ($order->get_meta('_kinguin_keys', $keys)) {
                $keys_saved = true;
            }
        }
		
		if ( ! $keys_saved ) {            
            \wc_get_logger()->debug('Kinguin save keys to meta: ', array('source' => 'kinguin-checkout-log' ));
            \wc_get_logger()->debug(print_r( $keys, true ), array('source' => 'kinguin-checkout-log' ));
        }
        

		update_post_meta( $this->order->get_id(), '_kinguin_keys', $keys );

        if( 'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
            $order = wc_get_order( $this->order->get_id() );
            if ( $order && !is_wp_error($order) ) {
                $order->update_meta_data( '_kinguin_keys', $keys );
                $order->save();
            }
        }        

	}



	/**
	 * Set Kinguin keys for the order.
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException
	 */
	public function request_game_keys_from_api() {
		try {
			$keys = $this->get();
			$this->save_keys_to_order_meta( $keys );
		} catch ( \Exception $e ) {
            \wc_get_logger()->debug( 'Kinguin error during getting and saving keys (set): ', array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( 'Kinguin error during getting and saving keys (set): ', array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
			throw $e;
		}
	}

}