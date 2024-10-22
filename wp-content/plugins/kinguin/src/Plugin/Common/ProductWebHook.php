<?php
/**
 * Product WebHook class.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Common;

use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinWebHookIncorrectSecret;
use WPDesk\ILKinguin\Admin\KinguinAPI;
use WPDesk\ILKinguin\Admin\Product\InsertUpdate;

defined( 'ABSPATH' ) || exit;

class ProductWebHook {
	use Configuration;



	/**
	 * Register product update route.
	 */
	public function register_route() {
		register_rest_route(
			'kinguin/v1',
			'/products/update',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'update_product' ),
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
	 * @see https://github.com/kinguinltdhk/Kinguin-eCommerce-API/blob/master/features/Webhooks.md#product-updated-webhook
	 */
	public function update_product( \WP_REST_Request $request ) {

		if ( $request->is_json_content_type() ) {
			try {
				if ( $this->check_secret( $request->get_header( 'X-Event-Secret' ) ) ) {
					$body     = json_decode( $request->get_body(), true );
					$response = $this->event_handler( $body );
				}
			} catch ( \Exception $e ) {
				$response = array(
					'status'        => $e->getCode(),
					'response'      => $e->getMessage(),
				);
                //\wc_get_logger()->debug( 'Error for webhook: ', array( 'source' => 'kinguin-debug-log' ) );
                //\wc_get_logger()->debug( print_r( $e->getCode(), true), array( 'source' => 'kinguin-debug-log' ) );
                //\wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
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
		if ( $secret === $this->get_products_webhook_secret() ) {
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
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException No Api Key provided.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException  No connection.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException    Exception for any other status code than 200.
	 */
	private function event_handler( array $body ) {
		$product = $this->request_product( $body['productId'] );
		$post = new InsertUpdate();
		$post->set_currency_rate();
		if ( $post->manage_webhook( $product ) ) {
			return array(
				'status'   => 200,
				'response' => 'Product updated'
			);
		} else {
			return array(
				'status'   => 404,
				'response' => 'Product not found'
			);
		}
	}



	/**
	 * Request product object from Kinguin API
	 *
	 * @param string $product_id Kinguin productId.
	 *
	 * @return array Kinguin product.
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException
	 *
	 * @see https://github.com/kinguinltdhk/Kinguin-eCommerce-API/tree/master/api/products/v2#get-product
	 */
	private function request_product( $product_id ) {
		try {
			$product = ( new KinguinAPI() )->get( $this->get_api_url() . '/v2/products/' . $product_id );
			$product = json_decode( json_encode( $product ), true, 512, JSON_OBJECT_AS_ARRAY );
			return $product;
		} catch ( \Exception $error ) {
            \wc_get_logger()->debug( 'Kinguin request_product error: ', array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $error->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
			throw $error;
		}
	}

}