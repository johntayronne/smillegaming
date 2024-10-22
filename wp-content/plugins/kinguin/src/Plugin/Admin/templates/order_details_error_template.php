<?php
/**
 * Kinguin Order Details metabox
 *
 * @package WPDesk\ILKinguin
 *
 * @var array       $kinguin_order Kinguin order details.
 * @var string      $date_format   WordPress date format.
 * @var string      $time_format   WordPress time format.
 * @var array|false $kinguin_keys  Games keys.
 * @var bool        $error         Order error.
 * @var string      $error_code    Exception error code.
 * @var string      $error_message Exception error message.
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="kinguin-order-details error-details">
    <h3><?php esc_html_e( 'Error', 'kinguin' ); ?> <strong><?php echo esc_html( $error_code ); ?></strong></h3>
    <p>
        <?php echo esc_html( $error_message ); ?>
    </p>
</div>
