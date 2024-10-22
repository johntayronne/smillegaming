<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 * Allegro Response Handler exception class
 *
 * @package WPDesk\ILKinguin
 */
class FrankfurterStatusCodeException extends \Exception {

	/**
	 * AllegroResponseHandlerException constructor.
	 *
	 * @param object $body Response body object.
	 * @param int    $code Error code.
	 */
	public function __construct( string $message, int $code ) {
		parent::__construct();
		$this->message = $message;
		$this->code    = $code;
	}

}
