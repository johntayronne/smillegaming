<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinUnexpectedResponse extends \Exception {

	public function __construct() {
		parent::__construct();
		$this->message = __( 'There are no products to import within Kinguin API response.', 'kinguin' );
	}

}
