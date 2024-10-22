<?php
/**
 * Frontend user account class.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Frontend;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class UserAccountKeys {
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
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'init', array( $this, 'add_keys_tab_endpoint' ) );
		add_filter( 'query_vars', array( $this, 'kinguin_keys_query_vars' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'tab_assets' ) );
		add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_items' ) );
		add_filter( 'woocommerce_get_endpoint_url', array( $this, 'change_keys_tab_menu_items_url' ), 10, 2 );
		add_action( 'woocommerce_account_kinguin-keys_endpoint', array( $this, 'tab_content' ) );
	}



	/**
	 * Add kinguin keys tab to WP endpoints.
	 *
	 * @retrun void
	 */
	public function add_keys_tab_endpoint() {
		add_rewrite_endpoint( 'game-keys', EP_ROOT | EP_PAGES );
	}



	/**
	 * Add kinguin keys tab query vars.
	 *
	 * @param array $vars WP query variables.
	 *
	 * @return array
	 */
	public function kinguin_keys_query_vars( $vars ) {
		$vars[] = 'game-keys';
		return $vars;
	}



	/**
	 * User account tab assets
	 *
	 * @return void
	 */
	public function tab_assets() {
		if ( is_account_page() ) {
			wp_enqueue_style( 'kinguin-keys', $this->plugin_info->get_plugin_url() . '/assets/css/kinguin-keys.css', array(), $this->plugin_info->get_version(), false );
		}
	}



	/**
	 * Add 'Games keys' item to the WooCommerce user account menu.
	 *
	 * @param array $menu_items User account menu items.
	 *
	 * @return array
	 */
	function add_account_menu_items( $menu_items ){
		$menu_items[ 'game-keys' ] = __( 'Games keys', 'kinguin' );
		return $menu_items;
	}



	/**
	 * An ugly way to replace default item url that unfortunately cause 404 error when permalinks are enabled.
	 *
	 * @param string $url      Endpoint url.
	 * @param string $endpoint Endpoint name.
	 *
	 * @return string $url
	 */
	function change_keys_tab_menu_items_url( $url, $endpoint ) {
		if ( $endpoint == 'game-keys' ) {
			$url = home_url() . '?page_id=' . get_option( 'woocommerce_myaccount_page_id' ) . '&game-keys';
		}
		return $url;
	}



	/**
	 * Get all current user orders with games keys.
	 *
	 * @return array
	 */
	public function get_orders_with_keys() : array {
		return wc_get_orders(
			array(
				'customer_id' => get_current_user_id(),
				'limit'        => -1,
				'orderby'      => 'date',
				'order'        => 'DESC',
				'meta_key'     => '_kinguin_keys',
				'meta_compare' => 'EXISTS',
			)
		);
	}



	/**
	 * Games keys tab content.
	 *
	 * @return void
	 */
	public function tab_content() {
		$orders       = $this->get_orders_with_keys();
		$date_format  = get_option('date_format');
		$time_format  = get_option('time_format');
		if ( $orders ) {
			include_once $this->plugin_info->get_plugin_dir() . '/templates/user-account-tab/games-list.php';
		} else {
			include_once $this->plugin_info->get_plugin_dir() . '/templates/user-account-tab/no-games.php';
		}
	}

}