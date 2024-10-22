<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinWebHookOrderUnsupportedEvent extends \Exception {

	/**
	 * KinguinOrderNotExists constructor.
	 *
	 * @param int    $code    Error code.
	 * @param string $message Error message.
	 * @param string $body    Longer error explanation.
	 */
	public function __construct( int $code, string $message ) {
		parent::__construct();
		$this->code   = $code;
		$this->message = $message;
	}

}
