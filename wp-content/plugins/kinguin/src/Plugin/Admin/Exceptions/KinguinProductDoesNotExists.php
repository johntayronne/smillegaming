<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinProductDoesNotExists extends \Exception {

	/**
	 * KinguinProductDoesNotExists constructor.
	 *
	 * @param string $message Error message.
	 */
	public function __construct( string $message ) {
		parent::__construct();
		$this->message = $message;
	}

}
