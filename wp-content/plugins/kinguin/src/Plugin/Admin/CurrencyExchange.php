<?php
/**
 * Currency exchange class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin;

defined( 'ABSPATH' ) || exit;

use WPDesk\ILKinguin\Admin\Exceptions\FrankfurterStatusCodeException;
use WPDesk\ILKinguin\Admin\Exceptions\FrankfurterNoConnectionException;
use WPDesk\ILKinguin\Admin\Exceptions\FrankfurterUnsupportedCurrencyException;

class CurrencyExchange {

	/**
	 * Get latest currency rate from frankfurter.app for given currency
	 *
	 * @param string $currency Currency three letters code (ISO 4217).
	 *
	 * @return array $current_rate
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\FrankfurterStatusCodeException   Exception for respond with other status code than 200.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\FrankfurterNoConnectionException Exception for broken connection.
	 *
	 * @see https://www.frankfurter.app/
	 */
	public function get( string $currency ) {
		$response = wp_remote_get(
			esc_url( 'https://api.frankfurter.app/latest?from=EUR&to=' . urlencode( $currency ) ),
			array(
				'headers' => array(
					'Accept-Charset' => 'application/json',
				),
			)
		);
		if ( ! is_wp_error( $response ) ) {
			$status_code = wp_remote_retrieve_response_code( $response );
			$body        = json_decode( wp_remote_retrieve_body( $response ), true );

			if ( 200 === $status_code ) {
				return $body;
			} else {
				throw new FrankfurterStatusCodeException( $body->type, $status_code );
			}
		} else {
			throw new FrankfurterNoConnectionException();
		}
	}



	/**
	 * Save current currency rates to kinguin_currency_rate option.
	 *
	 * @param array $current_rate Response from frankfurter.
	 */
	public function save_rates( $current_rate ) {
		update_option( 'kinguin_currency_rate', $current_rate );
	}



	/**
	 * Get saved currency rate from wp_options.
	 *
	 * @return array|false
	 */
	public function get_rates() {
		return get_option( 'kinguin_currency_rate', false );
	}



	/**
	 * Get currency rate from wp_options or request new rate if currency does not exist.
	 *
	 * @param string $currency Currency three letters code (ISO 4217).
	 *
	 * @return float
	 */
	public function get_currency_rate( string $currency ) : float {
		if ( 'EUR' === $currency ) {
			return 1;
		}
		try {
			if ( $this->get_rates() && isset( $this->get_rates()['rates'][ $currency ] ) ) {
				return (float) $this->get_rates()['rates'][ $currency ];
			} else {
				$current_rate = $this->get( $currency );
				$this->save_rates( $current_rate );
				if ( isset( $current_rate['rates'][ $currency ] ) ) {
                    return (float) $current_rate['rates'][ $currency ];
                } else {
                    throw new FrankfurterUnsupportedCurrencyException();
                }
			}
		} catch ( \Exception $e ) {
            \wc_get_logger()->debug( 'Kinguin error get_currency_rate: ', array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $e->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
		}
	}

}

