<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinMissingApiKeyException extends \Exception {

	public function __construct() {
		parent::__construct();
		$this->message = __( 'Missing API key. Please provide it in the settings tab.', 'kinguin' );
	}

}
