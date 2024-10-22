<?php
/**
 * Kinguin Order Details metabox
 *
 * @package WPDesk\ILKinguin
 *
 * @var array       $kinguin_order Kinguin order details.
 * @var string      $date_format   WordPress date format.
 * @var string      $time_format   WordPress time format.
 * @var array|false $kinguin_keys  Games keys
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="kinguin-order-details">
    <h3><?php esc_html_e( 'Order', 'kinguin' ); ?>: <strong><?php echo esc_html( $kinguin_order['orderId'] ); ?></strong></h3>
    <dl>
        <dt><?php esc_html_e( 'Order status at Kinguin', 'kinguin' ); ?>:</dt>
        <dd>
            <mark class="order-status status-<?php echo sanitize_html_class( $kinguin_order['status'] ) ?>">
                <span><?php echo ucfirst( $kinguin_order['status'] ) ?></span>
            </mark>
        </dd>
        <dt><?php esc_html_e( 'Order created', 'kinguin' ); ?>:</dt>
        <dd><?php echo ( new \DateTime( $kinguin_order['createdAt'] ) )->format( $date_format . ' ' . $time_format ) ; ?></dd>
        <dt><?php esc_html_e( 'Last order update', 'kinguin' ); ?>:</dt>
        <dd><?php echo ( new \DateTime( $kinguin_order['updatedAt'] ) )->format( $date_format . ' ' . $time_format ) ; ?></dd>
        <dt><?php esc_html_e( 'Keys available in user account', 'kinguin' ); ?>:</dt>
        <dd><?php echo ( $kinguin_keys ? __( 'Yes', 'kinguin' ) : __( 'No', 'kinguin' ) ); ?></dd>
    </dl>
    <table>
        <thead>
            <tr>
                <th><?php esc_html_e( 'Name', 'kinguin' ); ?></th>
                <th><?php esc_html_e( 'Price', 'kinguin' ); ?> (€)</th>
                <th><?php esc_html_e( 'Quantity', 'kinguin' ); ?></th>
                <th><?php esc_html_e( 'Total', 'kinguin' ); ?> (€)</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach( $kinguin_order['products'] as $product ) : ?>
            <tr>
                <td><?php echo esc_html( $product['name'] ); ?></td>
                <td><?php echo esc_html( $product['price'] ); ?></td>
                <td><?php echo esc_html( $product['qty'] ); ?></td>
                <td><?php echo esc_html( $product['totalPrice'] ); ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr>
                <td><?php esc_html_e( 'Total', 'kinguin' ); ?></td>
                <td></td>
                <td><?php echo esc_html( $kinguin_order['totalQty'] ); ?></td>
                <td><?php echo esc_html( $kinguin_order['totalPrice'] ); ?></td>
            </tr>
        </tfoot>
    </table>
</div>
