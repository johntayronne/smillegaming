<?php
namespace WPDesk\ILKinguin\Admin\Exceptions;

defined( 'ABSPATH' ) || exit;

/**
 *
 *
 * @package WPDesk\ILKinguin
 */
class KinguinKeysEmailFailed extends \Exception {

	public function __construct() {
		parent::__construct();
		//$this->message = __( 'Email with keys cannot be send.', 'kinguin' );
		$this->message = sprintf( '%s <a target="_blank" href="%s">kinguin-checkout-flow-log</a> %s %s',
                                    __( "Email with keys cannot be send. Check ", "kinguin" ),
                                    get_home_url() . '/wp-admin/admin.php?page=wc-status&tab=logs',
                                    __( "file", "kinguin" ),
                                    __( "for details", "kinguin" )
                                );
	}

}
