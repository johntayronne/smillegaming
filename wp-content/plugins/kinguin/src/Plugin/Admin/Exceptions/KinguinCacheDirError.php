<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinCacheDirError extends \Exception {

	public function __construct() {
		parent::__construct();
		$this->message = __( 'Cache directory cannot be created. Product import cannot proceed.', 'kinguin' );
	}

}
