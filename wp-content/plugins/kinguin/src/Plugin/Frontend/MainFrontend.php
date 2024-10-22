<?php
/**
 * Main frontend class.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Frontend;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinProductDoesNotExists;
use WPDesk\ILKinguin\Admin\KinguinAPI;
use WPDesk\ILKinguin\Admin\Product\InsertUpdate;
use WPDesk\ILKinguin\Common\GalleryFromMeta;

defined( 'ABSPATH' ) || exit;

class MainFrontend {

    use Configuration;

	/**
	 * Plugin object details
	 *
	 * @var WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	private $plugin_info;



	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
	}



	/**
	 *
	 */
	public function init() {
	    if( get_option( 'kinguin_product_view', true ) ) {
            ( new ProductView( $this->plugin_info ) )->hooks();
        } else {
            GalleryFromMeta::get_instance();
            ( new ProductViewDefault( $this->plugin_info ) )->hooks();
        }
		( new UserAccountKeys( $this->plugin_info ) )->hooks();
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
	    // place order on Kinguin hook

		//add_action( 'woocommerce_checkout_order_created', array( new NewOrder(), 'new_order_placed' ) );
		//add_action( 'woocommerce_store_api_checkout_order_processed', array( new NewOrder(), 'new_order_placed' ) );

		add_action( 'pre_get_posts', array( $this, 'product_filter' ) );
        add_action( 'woocommerce_archive_description', array( $this, 'kinguin_filter_search_results_title' ) );

        add_action( 'woocommerce_add_to_cart', array( $this, 'check_product_on_add_to_cart' ), 10, 6 );

	}


	/**
	 * Filter shop for terms
	 *
	 * @param array $query Main query.
	 */
	public function product_filter( $query ) {
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'product' ) ) {
			$attributes = wc_get_attribute_taxonomies();
			if ( $attributes ) {
				$attributes = wp_list_pluck( $attributes, 'attribute_name' );
				$params     = filter_var_array($_GET, FILTER_SANITIZE_STRING);

				if ( isset( $params ) ) {
					foreach ( $params as $param => $value ) {
						if ( in_array( $param, $attributes ) ) {
							$query->set(
								'tax_query',
								array(
									array(
										'taxonomy' => 'pa_' . $param,
										'field'    => 'slug',
										'terms'    => $value
									)
								)
							);
						}
					}
				}
			}
		}
	}


    /**
     * Show title for filtered results on shop archive page
     *
     */
	public function kinguin_filter_search_results_title() {
        if ( ! is_admin() && is_post_type_archive( 'product' ) ) {
            $attributes = wc_get_attribute_taxonomies();
            if ( $attributes ) {
                $attributes = wp_list_pluck( $attributes, 'attribute_name' );

                $params = array_filter(
                    $_GET,
                    function( $param ) {
                        return sanitize_text_field( $param );
                    }
                );

                if ( isset( $params ) ) {
                    foreach ( $params as $param => $value ) {
                        if ( in_array($param, $attributes ) ) {
                            echo '<h2>' . __( 'All products that meet the criteria', 'kinguin' ) . ' "' . $param . ' - ' . ucfirst( $value ) . '":</h2>';
                        }
                    }
                }
            }
        }
    }

    public function check_product_on_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {

        $kinguin_productId = get_post_meta( $product_id, '_productId', true );

        $wc_product = wc_get_product( $product_id );

        $sku_label = explode('-', $wc_product->get_sku() )[0] ;

        if( $kinguin_productId && $sku_label === 'kinguin' ) {

            try {
                $product = ( new KinguinAPI() )->get( $this->get_api_url() . '/v2/products/' . $kinguin_productId );
                $product = json_decode( json_encode( $product ), true, 512, JSON_OBJECT_AS_ARRAY );

                \wc_get_logger()->debug( 'Kinguin check product on add to cart : ', array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( 'name: ' . print_r($product['name'], true), array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( 'price: ' . print_r($product['price'], true), array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( 'qty: ' . print_r($product['qty'], true), array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( 'updatedAt: ' . print_r($product['updatedAt'], true), array( 'source' => 'kinguin-checkout-log' ) );

                if( ! isset($product['qty']) || empty($product['qty']) || (int) $product['qty'] <= 0 ) {

                    $this->set_product_outofstock( $product_id );

                } else {

                    $post = new InsertUpdate();
                    $post->set_currency_rate();

                    $product_updated = $post->update( $product_id, $product );
                    if( $product_updated ) {
                        // update cart
                        if( is_object( WC()->cart )) {
                            WC()->cart->calculate_totals();
                        }
                    }
                }

            } catch ( \Exception $error ) {
                // set product out of stock
                $this->set_product_outofstock( $product_id );

                \wc_get_logger()->debug( 'Kinguin product out-of-stock: ', array( 'source' => 'kinguin-checkout-log' ) );
                \wc_get_logger()->debug( print_r( $product_id, true), array( 'source' => 'kinguin-checkout-log' ) );

                throw new KinguinProductDoesNotExists( 'Sorry, but this product has just been sold out.' );
            }

        }
    }


    public function set_product_outofstock( $product_id ) {

        $out_of_stock_staus = 'outofstock';

        // 1. Updating the stock quantity
        update_post_meta($product_id, '_stock', 0);

        // 2. Updating the stock quantity
        update_post_meta( $product_id, '_stock_status', wc_clean( $out_of_stock_staus ) );

        // 3. Updating post term relationship
        wp_set_post_terms( $product_id, 'outofstock', 'product_visibility', true );

        // And finally (optionally if needed)
        wc_delete_product_transients( $product_id ); // Clear/refresh the cache

    }


    /**
     * Update WooCommerce product
     *
     * @param array $product Kinguin single product from API response.
     * @param int $product_id.
     */
//    public function set_product_outstock( array $product, $product_id) {
//        $post = array(
//            'post_title'   => $product['name'],
//            'post_date'    => date('Y-m-d H:i:s'),
//            'post_content' => $product['description'] ?? '',
//            'post_status'  => 'publish',
//            'post_author'  => get_current_user_id(),
//            'post_type'    => 'product',
//            'post_name'    => sanitize_title( $product['originalName'] ?? $product['name'] ),
//            'meta_input'   => array(
//                '_virtual'       => 'yes',
//                '_manage_stock'  => 'yes',
//                '_stock'         => '',
//                '_stock_status'  => '',
//                '_price'         => $product['price'] ? $this->convert_price( (float) $product['price'] ) : '',
//                '_regular_price' => $product['price'] ? $this->convert_price( (float) $product['price'] ) : '',
//                '_kinguinPrice'  => $product['price'] ?? '',
//                '_sku'           => 'kinguin-' . $product['kinguinId'],
//            ),
//        );
//
//        // Images.
//        if ( isset( $product['images'] ) && ! empty( array_filter( $product['images'] ) ) ) {
//            if ( isset( $product['images']['screenshots'] ) ) {
//                $post['meta_input']['_screenshots'] = $product['images']['screenshots'];
//            }
//        }
//
//        // Videos.
//        if ( isset( $product['videos'] ) && ! empty( array_filter( $product['videos'] ) ) ) {
//            $post['meta_input']['_videos'] = $product['videos'];
//        }
//
//        // Add product meta.
//        $metas = $this->set_product_meta(
//            $product,
//            array(
//                'kinguinId',
//                'productId',
//                'originalName',
//                'releaseDate',
//                'cheapestOfferId',
//                'isPreorder',
//                'metacriticScore',
//                'regionId',
//                'activationDetails',
//                'systemRequirements',
//                'ageRating',
//                'steam',
//                'updatedAt',
//            )
//        );
//        if ( $metas ) {
//            $post = array_merge_recursive( $post, $metas );
//        }
//
//        // Product tags.
//        if ( isset( $product['tags'] ) ) {
//            $post['tax_input']['product_tag'] = $this->get_terms_ids( $product['tags'], 'product_tag' );
//        }
//
//        // Add product attributes.
//        $attributes = $this->set_product_attributes(
//            $product,
//            array(
//                'developers',
//                'publishers',
//                'genres',
//                'platform',
//                'languages',
//            )
//        );
//        if ( $attributes ) {
//            $post = array_merge_recursive( $post, $attributes );
//        }
//
//        $post['ID'] = $product_id;
//        wp_update_post( $post );
//    }

}
