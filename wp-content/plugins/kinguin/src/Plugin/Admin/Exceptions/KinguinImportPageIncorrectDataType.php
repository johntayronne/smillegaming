<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinImportPageIncorrectDataType extends \Exception {

	public function __construct() {
		parent::__construct();
		$this->message = __( 'Import page number is not numeric value.', 'kinguin' );
	}

}
