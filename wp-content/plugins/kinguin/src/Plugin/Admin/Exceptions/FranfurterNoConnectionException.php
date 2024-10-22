<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

class FrankfurterNoConnectionException extends \Exception {

	public function __construct() {
		parent::__construct();
		$this->message = __( 'No connection', 'kinguin' );
	}

}
