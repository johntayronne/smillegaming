<?php
/**
 * Product Margin class.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Common;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class ProductMargin {
    use Configuration;


    /**
     * Integrate with WordPress admin actions and filters.
     *
     * @return void
     */
    public function hooks() {
        add_filter('woocommerce_product_get_price', array( $this, 'kinguin_custom_price' ), 99, 2 );
        add_filter('woocommerce_product_get_regular_price', array( $this, 'kinguin_custom_price' ), 99, 2 );
    }


    /**
     * Add margin to Kinguin price.
     *
     */
    public function kinguin_custom_price( $price, $product ) {
        $margin_option = $this->get_margin_type();
        $value  = $this->get_margin_value();

        if( !empty( $value ) && !empty( $margin_option ) ) {

            $product_id = $product->get_id();
            $sku_label = explode('-', $product->get_sku() )[0] ;
            if ( $product_id && $sku_label === 'kinguin' ) {

                if ($margin_option['kinguin_margin_type'] == 1) {
                    $new_price = (float)$price + (float)$value;
                    return $new_price > 0 ? $new_price : 0;
                } elseif ($margin_option['kinguin_margin_type'] == 2) {
                    $new_price = $price + ($price * (float)$value / 100);
                    return $new_price > 0 ? $new_price : 0;

                } else {
                    return $price;
                }
            }
        }
		
        return $price;
    }


}
