<?php
/**
 * WooCommerce order details.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin\Order;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class MetaBoxDetails {
	use Configuration;

	/**
	 * Plugin object details
	 *
	 * @var WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	private $plugin_info;



	/**
	 * Order meta box constructor
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'order_meta_box_assets' ) );
		add_action( 'add_meta_boxes', array( $this, 'register_meta_box' ) );
	}



	/**
	 * WooCommerce order meta box assets.
	 */
	public function order_meta_box_assets() {
		$current_screen = get_current_screen();
		if ( is_a( $current_screen, 'WP_Screen' ) ) {
		    if( 'shop_order' === $current_screen->id || 'woocommerce_page_wc-orders' === $current_screen->id ) {
                wp_enqueue_style(
                    'kinguin-admin-order',
                    $this->plugin_info->get_plugin_url() . '/assets/css/kinguin-admin-order.css'
                );
            }
		}
	}



	/**
	 * Register WooCommerce order with Kinguin order details.
	 */
	public function register_meta_box() {

        $current_screen = get_current_screen();
        if ( is_a( $current_screen, 'WP_Screen' ) ) {
            if ('shop_order' === $current_screen->id || 'woocommerce_page_wc-orders' === $current_screen->id) {
                add_meta_box(
                    'kinguin_order',
                    __('Kinguin Order Details', 'kinguin'),
                    array($this, 'meta_box_content'),
                    '',
                    'advanced',
                    'default'
                );
            }
        }

	}



	/**
	 * Render metabox content.
	 *
	 * @param $post
	 */
	public function meta_box_content( $post ) {

        $order_id = isset( $_GET['id'] ) ? sanitize_text_field( $_GET['id'] ) : $post->ID;

		$kinguin_order = $this->get_order_details( $order_id );
		$date_format   = get_option( 'date_format' );
		$time_format   = get_option( 'time_format' );
		$kinguin_keys  = get_post_meta( $order_id, '_kinguin_keys' );
        if ( ! $kinguin_keys &&  'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
            $order_details = isset( get_post_meta( $order_id )['_kinguin_keys'][0] )
                ? get_post_meta( $order_id )['_kinguin_keys'][0]
                : [];
        }

		$error         = isset( $kinguin_order['error'] );
		$error_code    = $kinguin_order['error_code'] ?? '';
		$error_message = $kinguin_order['error_message'] ?? '';
		if ( $kinguin_order && ! $error ) {
			include_once $this->plugin_info->get_plugin_dir() . '/src/Plugin/Admin/templates/order_details_template.php';
		} elseif ( $kinguin_order && $error ) {
			include_once $this->plugin_info->get_plugin_dir() . '/src/Plugin/Admin/templates/order_details_error_template.php';
		} else {
			include_once $this->plugin_info->get_plugin_dir() . '/src/Plugin/Admin/templates/no_order_details_template.php';
		}
	}



	/**
	 * Get Kinguin order details (response form API while new order created).
	 *
	 * @param int $order_id WooCommerce order id/post id
	 *
	 * @return array|false
	 */
	private function get_order_details( int $order_id ) {
	    $order_details = [];

        $order_details = get_post_meta( $order_id, '_kinguin_order', true );

        if ( ! $order_details &&  'yes' === get_option('woocommerce_custom_orders_table_enabled') ) {
            $order_details = isset( get_post_meta( $order_id )['_kinguin_order'][0] )
                ? get_post_meta( $order_id )['_kinguin_order'][0]
                : [];
        }

		return $order_details;
	}

}