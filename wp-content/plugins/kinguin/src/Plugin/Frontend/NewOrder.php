<?php
/**
 * New order class.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Frontend;

use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Admin\KinguinAPI;

defined( 'ABSPATH' ) || exit;

class NewOrder {
    use Configuration;

    /**
     * WooCommerce order ID.
     *
     * @var int .
     */
    private $order_id;

    /**
     * Array of Kinguin products to post with order to API.
     *
     * @var array .
     * @see https://github.com/kinguinltdhk/Kinguin-eCommerce-API/blob/master/api/order/v2/README.md
     */
    private $products = array();



    /**
     * Check order for Kinguin products if there are any post them to API.
     *
     * @param int       $order_id WooCommerce order ID.
     * @param \WC_Order $order    WooCommerce order object.
     */
    public function new_order_placed( $order_id )
    {

        $order = wc_get_order( $order_id );

        if ( ! $order || is_wp_error($order) ) return;

        $this->order_id = $order_id;
        $this->check_products( $order->get_items() );
        $this->check_discount_code();

        $order_status = $order->get_status();

        if( 'completed' === $order_status || 'processing' === $order_status ) {

            $kinguin_order = get_post_meta( $order_id, '_kinguin_order', true );
            if ( ! $kinguin_order &&  'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
                $kinguin_order = isset( get_post_meta( $order_id )['_kinguin_order'][0] )
                    ? get_post_meta( $order_id )['_kinguin_order'][0]
                    : [];
            }


            // Check if kinuin order already exists
            if( is_array( $kinguin_order ) && isset( $kinguin_order['orderId'] ) )  {

                \wc_get_logger()->debug(
                    'Check if kinuin order already exists for Woo order #: '
                    . $this->order_id
                    . ' - TRUE. Do not create order.',
                    array('source' => 'kinguin-checkout-log')
                );
                return;
            }

            \wc_get_logger()->debug(
                'Before creating order at Kinguin for Woo order #: ' . $this->order_id,
                array('source' => 'kinguin-checkout-log')
            );

            $this->place_order_at_kinguin();
        }

    }



    /**
     * Check each WooCommerce order item if it has _cheapestOfferId post meta
     * and prepare array of products for Kinguin API request.
     *
     * @param array $items Order items.
     */
    private function check_products( array $items ) {
        foreach ( $items as $item ) {
            $product    = $item->get_product();
            $offer_id   = $product->get_meta( '_cheapestOfferId', true );

            if ( $offer_id ) {
                $this->products[] = array(
                    'productId' => $product->get_meta( '_productId', true ),
                    'qty'       => $item->get_quantity(),
                    'price'     => $product->get_meta( '_kinguinPrice', true ),
                    'name'      => $product->get_name(),
                    'keyType'   => 'text',
                    'offerId'   => is_array( $offer_id ) ? $offer_id[0] : $offer_id,
                );
            }
        }
    }



    /**
     * Post order to Kinguin API
     */
    private function place_order_at_kinguin() {
        if ( empty( $this->products ) ) {
            return;
        }

        $body = array();

        if( $this->check_discount_code() ) {
            $body = array(
                'products'        => $this->products,
                'couponCode' => $this->check_discount_code(),
                'orderExternalId' => $this->order_id

            );
        } else {
            $body = array(
                'products'        => $this->products,
                'orderExternalId' => $this->order_id,
            );
        }

        try {

            \wc_get_logger()->debug( 'Kinguin post order: ', array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r($body, true), array( 'source' => 'kinguin-checkout-log' ) );


            $api = new KinguinAPI();
            $kinguin_order = $api->post( $this->get_api_url() . '/v2/order', $body );
            add_post_meta( $this->order_id, '_kinguin_order', $kinguin_order );

            \wc_get_logger()->debug( 'Kinguin API order response: ', array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r( $kinguin_order, true ), array( 'source' => 'kinguin-checkout-log' ) );

            if( 'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
                $order = wc_get_order( $this->order_id );
                if ( $order && ! is_wp_error( $order ) ) {
                    $order->update_meta_data('_kinguin_order', $kinguin_order );
                    $order->save();
                }
            }

        } catch ( \Exception $error ) {
            add_post_meta(
                $this->order_id,
                '_kinguin_order',
                array(
                    'error' => true,
                    'error_code' => $error->getCode(),
                    'error_message' => $error->getMessage(),
                )
            );

            if( 'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
                $order = wc_get_order( $this->order_id );
                if ( $order && is_object( $order ) ) {
                    $order->update_meta_data('_kinguin_order', array(
                        'error' => true,
                        'error_code' => $error->getCode(),
                        'error_message' => $error->getMessage(),
                    ) );
                    $order->save();
                }
            }

            \wc_get_logger()->debug( 'Error Kinguin for order: ' . $this->order_id, array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r( $error->getCode(), true), array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( print_r( $error->getMessage(), true), array( 'source' => 'kinguin-checkout-log' ) );
            \wc_get_logger()->debug( 'Error Kinguin for order: ' . $this->order_id, array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $error->getCode(), true), array( 'source' => 'kinguin-debug-log' ) );
            \wc_get_logger()->debug( print_r( $error->getMessage(), true), array( 'source' => 'kinguin-debug-log' ) );
        }
    }

    /**
     * Check discount code if exists
     *
     */
    private function check_discount_code() {
        $discount_code = $this->get_discount_code();
        if( !empty($discount_code) ) {
            return $discount_code;
        }
        return false;
    }

}