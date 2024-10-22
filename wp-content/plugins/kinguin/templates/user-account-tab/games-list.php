<?php
/**
 * The Template for user account games keys.
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 *
 * @var array $orders       User orders with kinguin keys.
 * @var string $date_format WordPress date format.
 * @var string $time_format WordPress time format.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="kinguin-keys-list">
    <?php foreach( $orders as $order ) : ?>
        <div class="order">
            <h4 class="order__id">
                <a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
                    <?php esc_html_e( 'Order', 'kinguin' ); ?>
                    <?php echo esc_html( $order->get_id() ); ?>
                </a>
            </h4>
            <time class="order__date" datetime="<?php echo esc_html( $order->get_date_created() ) ?>">
                <?php echo esc_html( ( new DateTime( $order->get_date_created() ) )->format( $date_format . ' ' . $time_format ) ); ?>
            </time>
            <div class="order__items">
            <?php foreach ( $order->get_meta('_kinguin_keys' ) as $keys ) :?>
                <?php
                    $product_url = '';
                    foreach( $order->get_items() as $product ) {
                        if ( $product->get_name() === $keys->name && $product->get_product_id() != 0 ) {
                            $product_url = get_permalink( $product->get_product_id() );
                        }
                    }
                ?>
                <div class="item">
                    <div class="item__name">
	                    <?php esc_html_e( 'Name', 'kinguin' ); ?>:
                        <?php echo ( ! empty( $product_url ) ? '<a href="' . esc_url( $product_url ) . '">' . esc_html( $keys->name ) . '</a>' : esc_html( $keys->name ) ); ?>
                    </div>
                    <div class="item__serial">
                    <?php if ( 'text/plain' === $keys->type ) : ?>
                        <input type="text" readonly value="<?php echo esc_attr( $keys->serial ); ?>">
                    <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
