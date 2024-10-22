<?php
/**
 * Main admin class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Product\Attributes;
use WPDesk\ILKinguin\Admin\Product\DataTabs;
use WPDesk\ILKinguin\Admin\Order\MetaBoxDetails;
use WPDesk\ILKinguin\Admin\Order\ActionsSelect;

defined( 'ABSPATH' ) || exit;

class MainAdmin {
    use Configuration;

	/**
	 * Plugin object details
	 *
	 * @var WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	private $plugin_info;

	/**
	 * Admin settings page object.
	 *
	 * @var SettingsPage $settings Admin settings page object.
	 */
	private $settings;

	/**
	 * Admin import page object.
	 *
	 * @var Import $import Admin import page object.
	 */
	private $import;



	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
	}



	/**
	 * Initialize admin components
	 */
	public function init() {
		$this->settings = new SettingsPage();
		$this->settings->set_plugin_info( $this->plugin_info );
		$this->settings->hooks();
		$this->import = new Import();
		$this->import->set_plugin_info( $this->plugin_info );
		$this->import->hooks();

		( new Attributes() )->hooks();
		( new DataTabs( $this->plugin_info ) )->hooks();
		( new MetaBoxDetails( $this->plugin_info ) )->hooks();
		( new ActionsSelect() )->hooks();
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'admin_menu_items' ) );
        add_action( 'before_delete_post', array( $this, 'kinguin_delete_thumbnail') );
        add_action( 'admin_notices', array( $this, 'webhook_admin_warning' ) );
	}


	/**
	 * Add admin menu items
	 */
	public function admin_menu_items() {

		// Icon.
		$icon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 216.32 164.78"><path d="M211,198.5l13.24-125L162,116.11l-46.37-82.4L68,110.89,8,73.41,20.12,198.5Z" transform="translate(-7.95 -33.71)" style="fill: white;"/></svg>';
		$icon = 'data:image/svg+xml;base64,' . base64_encode( $icon );

		// Add menu items.
		add_menu_page(
			__( 'Import products', 'kinguin' ),
			__( 'Import products', 'kinguin' ),
			'manage_options',
			'kinguin-import',
			array( $this->import, 'render_import_page' ),
			$icon,
			58
		);
		add_submenu_page(
			'kinguin-import',
			__( 'Settings', 'kinguin' ),
			__( 'Settings', 'kinguin' ),
			'manage_options',
			'kinguin-settings',
			array( $this->settings, 'render_settings_page' )
		);

		// Rename primary admin item position.
		global $menu;
		$position_to_rename = array_filter(
			$menu,
			function( $item ) {
				if ( in_array( 'kinguin-import', $item, true ) ) {
					return $item;
				}
			},
			true
		);
		$menu[ key( $position_to_rename ) ][0] = 'Kinguin';
		return $menu;
	}


    /**
     * Remove product image if deleting products to save disk quota
     */
	public function kinguin_delete_thumbnail( $pid ) {

        if( 'product' == get_post_type( $pid ) ) {
            $product = new \WC_product( $pid );
            $product_id = $product->get_id();
            $sku_label = explode('-', $product->get_sku())[0];

            if ( $product_id && $sku_label === 'kinguin' ) {
				
				delete_transient('existing_kinguin_ids');
				
                $product_thumbnail = get_post_thumbnail_id( $product_id );
                wp_delete_attachment($product_thumbnail, true);
            }
        }

    }


    public function webhook_admin_warning() {
        $class = 'notice notice-warning';
        $message = __( 'Kinguin: You have enabled product updates via webhook, but it seems that webhook is not active.', 'kinguin' );
        $message2 = __( "Please check if webhook option is also active in ", "kinguin" );
        $message3 = __( "Kinguin dashboard", "kinguin" );
        $url = "https://kinguin.net/integration/";

        $message_chunks = sprintf(
            '<details><summary>%1$s</summary>%2$s</details>',
            esc_html__( 'Additional context', 'woocommerce' ),
            $message
        );

        if( $this->is_webhook_import_enabled() ) {
            $cache_key_webhook_status = 'kinguin_webhook_is_working';
            $kinguin_webhook_is_working = get_transient( $cache_key_webhook_status );
            if( empty( $kinguin_webhook_is_working ) ) {

                printf('<div class="%1$s"><p>%2$s</p><p>%3$s<a target="_blank" href="%4$s">%5$s</a></p></div>',
                    esc_attr( $class ),
                    esc_html( $message ),
                    esc_html( $message2 ),
                    esc_url( $url ),
                    esc_html( $message3 )
                );
            }
        }
    }

}
