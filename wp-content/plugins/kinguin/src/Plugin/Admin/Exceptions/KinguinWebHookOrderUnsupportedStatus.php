<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinWebHookOrderUnsupportedStatus extends \Exception {

	/**
	 * KinguinOrderNotExists constructor.
	 *
	 * @param int    $code    Error code.
	 * @param string $message Error message.
	 */
	public function __construct( int $code, string $message ) {
		parent::__construct();
		$this->code   = $code;
		$this->message = $message;
	}

}
