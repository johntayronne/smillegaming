<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class FrankfurterUnsupportedCurrencyException extends \Exception {

	public function __construct() {
		parent::__construct();
		$this->message = __( 'Kinguin: Unsupported currency for automatic rate conversion', 'kinguin' );
	}

}
