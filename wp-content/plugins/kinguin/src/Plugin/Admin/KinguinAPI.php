<?php
/**
 * Kinguin API class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin;

use WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException;
use WPDesk\ILKinguin\Frontend\ProductView;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package WPDesk\ILKinguin
 */
class KinguinAPI {
	use Configuration;

	/**
	 * Get request from Kinguin API
	 *
	 * @param string $url         Resource url.
	 *
	 * @return object
	 *
	 * @throws KinguinMissingApiKeyException Exception for no api key.
	 * @throws KinguinStatusCodeException    Exception for respond with other status code than 200.
	 * @throws KinguinNoConnectionException  Exception for broken connection.
	 */
	public function get( string $url, bool $associative = null ) {

		if ( empty( $this->get_api_key() ) ) {
			throw new KinguinMissingApiKeyException();
		}

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-Api-Key'    => $this->get_api_key(),
				),
			)
		);
		if ( ! is_wp_error( $response ) ) {

			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = json_decode( wp_remote_retrieve_body( $response ) );

			if ( 200 === $status_code ) {
				return $body;
			} else {
			    $exception_message = $body->type ? $body->type : 'API responded with error code';
                //\wc_get_logger()->debug( 'Kinguin wp_remote_get error: ', array( 'source' => 'kinguin-debug-log' ) );
                //\wc_get_logger()->debug( print_r( $status_code, true), array( 'source' => 'kinguin-debug-log' ) );
                //\wc_get_logger()->debug( print_r( $exception_message, true), array( 'source' => 'kinguin-debug-log' ) );
				throw new KinguinStatusCodeException( $exception_message, $status_code );
			}

		} else {
			throw new KinguinNoConnectionException();
		}

	}



	/**
	 * Post request to Kinguin API
	 *
	 * @param string $url  Resource url.
	 * @param array  $body Post body.
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException    Exception for any other code than 200.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException  Exception for no connection.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException Exception for missing API key.
	 */
	public function post( string $url, array $body ) {

		if ( empty( $this->get_api_key() ) ) {
			throw new KinguinMissingApiKeyException();
		}

		$body = wp_json_encode( $body );

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-Api-Key'    => $this->get_api_key(),
				),
				'body'    => $body,
			)
		);

		if ( ! is_wp_error( $response ) ) {

			$status_code   = wp_remote_retrieve_response_code( $response );
			$response_body = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( 201 === $status_code ) {
				return $response_body;
			} else {
                \wc_get_logger()->debug( 'Kinguin POST method code: ', array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( print_r($status_code, true), array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( print_r($response_body['detail'], true), array( 'source' => 'kinguin-checkout-log' ) );

				throw new KinguinStatusCodeException( $response_body['detail'], $status_code );
			}

		} else {
            \wc_get_logger()->debug( 'Kinguin POST method WP error: ', array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r($response, true), array( 'source' => 'kinguin-checkout-log' ) );

			throw new KinguinNoConnectionException();
		}

	}



	/**
	 * Check connection with Kinguin API with given api key.
	 *
	 * @param string $api_key Kinguin API key.
	 *
	 * @return bool
	 */
	public function check_connection( $api_key ) : bool {
		$response = wp_remote_get(
			$this->get_api_url() . '/v1/products?page=1&limit=20',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
					'X-Api-Key'    => $api_key,
				),
			)
		);

		if ( ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response ) ) {
			return true;
		} else {
			return false;
		}

	}
}
