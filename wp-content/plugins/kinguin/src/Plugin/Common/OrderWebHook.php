<?php
/**
 * Order WebHook class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Common;

use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinOrderNoExists;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderMissingDetails;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderUnsupportedStatus;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderUnsupportedEvent;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookIncorrectSecret;

defined( 'ABSPATH' ) || exit;

class OrderWebHook {
	use Configuration;



	/**
	 * Register order status update route.
	 */
	public function register_route() {
		register_rest_route(
			'kinguin/v1',
			'/order/update',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_order_status' ),
				'args'                => array(),
				'permission_callback' => function() {
					return true;
				}
			)
		);
	}



	/**
	 * Validate response and send Rest API response
	 *
	 * @param \WP_REST_Request $request Incoming request with order status update.
	 *
	 * @see https://github.com/kinguinltdhk/Kinguin-eCommerce-API/blob/master/features/Webhooks.md
	 */
	public function update_order_status( \WP_REST_Request $request ) {

		if ( $request->is_json_content_type() ) {

			try {
				if ( $this->check_secret( $request->get_header( 'X-Event-Secret' ) ) ) {
					$body     = json_decode( $request->get_body(), true );
					$response = $this->event_handler( $request->get_header( 'X-Event-Name' ), $body );
				}
			} catch ( \Exception $e ) {
				$response = array(
					'status'        => $e->getCode(),
					'response'      => $e->getMessage(),
				);
                \wc_get_logger()->debug( 'Error for update_order_status: ', array( 'source' => 'kinguin-debug-log' ) );
                \wc_get_logger()->debug( print_r( $e->getCode(), true), array( 'source' => 'kinguin-debug-log' ) );
                \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
			}

		} else {
			$response = array(
				'status'        => 406,
				'response'      => 'Not Acceptable',
			);
		}

		return new \WP_REST_Response( $response ? $response : null, 204 );
	}



	/**
	 * Check and validate X-Event-Secret value from request header.
	 *
	 * @param string|null $secret X-Event-Secret value.
	 *
	 * @return bool
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookIncorrectSecret Incorrect X-Event-Secret value.
	 */
	private function check_secret( $secret ) : bool {
		if ( $secret === $this->get_orders_webhook_secret() ) {
			return true;
		} else {
			throw new KinguinWebHookIncorrectSecret( 401, 'Incorrect X-Event-Secret' );
		}
	}



	/**
	 * Event switcher
	 *
	 * @param string $event Available int this method: order.complete, order.status.
	 * @param array $body Response body.
	 *
	 * @return array
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException        No Api Key provided.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException         No connection.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinOrderNoExists                 Missing order exception.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException           Exception for any other status code than 200.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderMissingDetails    Exception for missing Kinguin order details.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderUnsupportedEvent  Exception for unsupported event.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderUnsupportedStatus Exception for unsupported status.
	 */
	private function event_handler( string $event, array $body ) : array {

		$order = $this->get_order( $body['orderExternalId'] );

		switch ( $event ) {
			case 'order.complete' :
				$response = $this->set_order_status( 'completed', $order, $body['updatedAt'] );
				break;
			case 'order.status' :
				if ( in_array( $body['status'], array( 'processing', 'completed', 'canceled', 'refunded' ) ) ) {
					$response = $this->set_order_status( $body['status'], $order, $body['updatedAt'] );
				} else {
					throw new KinguinWebHookOrderUnsupportedStatus( 405, 'Given status is not supported' );
				}
				break;
			default :
				throw new KinguinWebHookOrderUnsupportedEvent( 405, 'Missing or invalid event name' );
		}

		return $response;

	}



	/**
	 * Get order by its ID
	 *
	 * @param string $order_id Order ID.
	 *
	 * @return \WC_Order
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinOrderNoExists Missing order exception.
	 */
	private function get_order( string $order_id ) : \WC_Order {
		$order = wc_get_order( $order_id );
		if ( is_a( $order, 'WC_Order' ) ) {
			return $order;
		} else {
			throw new KinguinOrderNoExists( 404, 'Order with such ID does not exists' );
		}
	}


	/**
	 * Set order status complete
	 *
	 * @param string    $status New order status from Api.
	 * @param \WC_Order $order WooCommerce order.
	 * @param string    $updated Last update date time.
	 *
	 * @return array
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookOrderMissingDetails Exception for missing Kinguin order details.
	 */
	private function set_order_status( string $status, \WC_Order $order, string $updated ) : array {

        $current_order_status = $order->get_status();

		$order_details = $order->get_meta( '_kinguin_order' );

        if ( ! $order_details &&  'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
            $order_details = isset( get_post_meta( $order->get_id() )['_kinguin_order'][0] )
                ? get_post_meta( $order->get_id() )['_kinguin_order'][0]
                : null;
        }

		if ( $order_details ) {
			$order_details['status']    = $status;
			$order_details['updatedAt'] = $updated;

			if ( 'completed' === $status ) {
				try {
					( new GetKeys( $order ) )->request_game_keys_from_api();
				} catch ( \Exception $e ) {
                    \wc_get_logger()->debug( 'Kinguin error during getting and saving keys (get): ' . $order->get_id(), array( 'source' => 'kinguin-checkout-log' ) );
                    \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-checkout-log' ) );
                    \wc_get_logger()->debug( 'Kinguin error during getting and saving keys (get): ' . $order->get_id(), array( 'source' => 'kinguin-debug-log' ) );
                    \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
					throw $e;
				}
			}

			$order->update_meta_data( '_kinguin_order', $order_details );
			$order->save();

			if ( 'completed' === $status ) {
			    if( 'completed' === $current_order_status || 'processing' === $current_order_status ) {
			        $this->send_keys_and_change_order_status( $order );
                }
			}

			return array(
				'status'   => 200,
				'response' => 'Order is set to ' . $status
			);

		} else {
            \wc_get_logger()->debug( 'Kinguin error : ' . $order->get_id(), array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r( 'Missing details for given order', true), array( 'source' => 'kinguin-checkout-log' ) );
			throw new KinguinWebHookOrderMissingDetails( 404, 'Missing details for given order' );
		}
	}


	public function kinguin_send_keys_only_on_paid_order( $order_id ) {

        if ( ! $order_id ) {
            return;
        }

        global $product;
        $order = wc_get_order( $order_id );

        if ( $order->get_status() === 'processing' || $order->get_status() === 'completed' ) {

            $kinguin_virtual_order = null;
            if ( count( $order->get_items() ) > 0 ) {
                foreach( $order->get_items() as $item ) {
                    if ( 'line_item' == $item['type'] ) {
						$product_id = $item->get_product_id();
						
                        $_product = wc_get_product( $product_id );
                        if( $_product && ! is_wp_error( $_product ) ) {
                            if ( ! $_product->is_virtual() ) {
                                // once we find one non-virtual product, break out of the loop
                                $kinguin_virtual_order = false;
                                break;
                            } else {
                                $sku_label = explode('-', $_product->get_sku())[0];
                                if ($sku_label === 'kinguin') {
                                    $kinguin_virtual_order = true;
                                }
                            }
                        }
                    }
                }
            }
            // if all are virtual kinguin products, send keys and mark as completed
            if ( $kinguin_virtual_order ) {

                $this->send_keys_and_change_order_status( $order );
            }
        }

    }


    public function send_keys_and_change_order_status( $order ) {

        if ( ! $order ) {
            return;
        }

        $current_order_status = $order->get_status();

        if( ! $order->get_meta( '_kinguin_keys_sent' ) ) {
            try {
                $mail = new KeysEmail( $order );
                if( ! empty( $mail->get_mail_body() ) ) {

                    $mail->send();
                    $order->add_order_note(sprintf(__('Games keys has been sent to %s', 'kinguin'), $mail->get_customer_email()));

                    update_post_meta($order->get_id(), '_kinguin_keys_sent', 1);

                    if ('yes' === get_option('woocommerce_custom_orders_table_enabled')) {
                        if ($order && !is_wp_error($order)) {
                            $order->update_meta_data('_kinguin_keys_sent', 1);
                            $order->save();
                        }
                    }

                    if ('completed' !== $current_order_status) {
                        $order->update_status('wc-completed');
                    }
                }

            } catch (\Exception $e) {
                \wc_get_logger()->debug( 'Error for send_keys_and_change_order_status: ', array( 'source' => 'kinguin-debug-log' ) );
                \wc_get_logger()->debug( print_r( 'Emails do not work', true), array( 'source' => 'kinguin-debug-log' ) );
            }
        }

    }

}