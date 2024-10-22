<?php
/**
 * Email with keys.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Common;

use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinKeysEmailFailed;

defined( 'ABSPATH' ) || exit;

class KeysEmail {
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
		add_action( 'wp_mail_failed', array( $this, 'mail_failure' ), 10, 1 );
	}



	/**
	 * Get customer email address where the keys will be sent.
	 *
	 * @return string Email address.
	 */
	public function get_customer_email() : string {
		return $this->order->get_billing_email();
	}


	/**
	 * Get email subject.
	 *
	 * @return string Email subject.
	 */
	private function get_mail_subject() : string {
		return apply_filters( 'kinguin_email_subject', __( 'Your games keys', 'kinguin' ) );
	}



	/**
	 * Prepare body with games keys table.
	 *
	 * @return string
	 */
	public function get_mail_body() : string {

		$body = $this->get_email_message();
		$keys = $this->order->get_meta( '_kinguin_keys' );
		
		if( ! $keys ) {
		    return '';
        }

		$body .= '<br>';
		$body .= '<table>
			<tr>
    			<th>'. __( 'Name', 'kinguin' ) . '</th>
    			<th>'. __( 'Key', 'kinguin' ) . '</th>
  			</tr>';

		foreach ( $keys as $key ) {
			$body .= '<tr>
    			<td>' . esc_html( $key->name ) . '</td>
    			<td>'. esc_html( $key->serial ) . '</td>
  			</tr>';
		}

		$body .= '</table> ';

		return $body;
	}



	/**
	 * Prepare mail header.
	 *
	 * @return array
	 */
	private function get_mail_headers() : array {
		$headers[] = sprintf( 'From: %s <%s>', get_option( 'woocommerce_email_from_name', true ), get_option( 'woocommerce_email_from_address', true ) );
		$headers[] = 'Content-Type: text/html';
		$headers[] = 'charset=UTF-8';
		return $headers;
	}



	/**
	 * Send email with games keys to customer.
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinKeysEmailFailed
	 */
	public function send() {
		$status = wp_mail(
			$this->get_customer_email(),
			$this->get_mail_subject(),
			$this->get_mail_body(),
			$this->get_mail_headers()
		);
		
		$is_email_sent = $status ? 'SUCCESS' : 'FAIL';
		
		\wc_get_logger()->debug( 'Kinguin send Keys to buyer: ', array( 'source' => 'kinguin-checkout-log' ) );
		\wc_get_logger()->debug( 'To email: ' . print_r($this->get_customer_email(), true), array( 'source' => 'kinguin-checkout-log' ) );
		\wc_get_logger()->debug( 'Subject: ' . print_r($this->get_mail_subject(), true), array( 'source' => 'kinguin-checkout-log' ) );
        \wc_get_logger()->debug( 'Email body: ' . print_r($this->get_mail_body(), true), array( 'source' => 'kinguin-checkout-log' ) );
		\wc_get_logger()->debug( 'Email sent result: ' . print_r($is_email_sent, true), array( 'source' => 'kinguin-checkout-log' ) );

		
		if ( ! $status ) {
			throw new KinguinKeysEmailFailed();
		}
	}
	
	
	public function mail_failure($wp_error) {
        \wc_get_logger()->debug( 'WP_MAIL Failure when trying to send keys: ', array( 'source' => 'kinguin-checkout-log' ) );
        if(is_object($wp_error) && isset($wp_error->errors)) {
            \wc_get_logger()->debug( 'Error: ' . print_r( $wp_error->errors, true ), array( 'source' => 'kinguin-checkout-log' ) );
        }
        
    }

}