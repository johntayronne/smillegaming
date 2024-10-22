<?php
/**
 * Plugin main class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use ILKinguinVendor\WPDesk_Plugin_Info;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use ILKinguinVendor\WPDesk\PluginBuilder\Plugin\Deactivateable;

use WPDesk\ILKinguin\Common\CRON;
use WPDesk\ILKinguin\Common\GalleryFromMeta;
use WPDesk\ILKinguin\Common\OrderWebHook;
use WPDesk\ILKinguin\Common\ProductWebHook;
use WPDesk\ILKinguin\Common\ProductMargin;
use WPDesk\ILKinguin\Admin\MainAdmin;
use WPDesk\ILKinguin\Frontend\MainFrontend;
use WPDesk\ILKinguin\Frontend\NewOrder;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package WPDesk\ILKinguin
 */
class Plugin extends AbstractPlugin implements LoggerAwareInterface, HookableCollection, Deactivateable {
	use LoggerAwareTrait;
	use HookableParent;

	protected $plugin_info;
	protected $cron;
	protected $margin;
	//protected $gallery;



	/**
	 * Plugin constructor.
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		parent::__construct( $plugin_info );
		$this->setLogger( new NullLogger() );

		$this->plugin_info = $plugin_info;
		$this->cron        = new CRON();
		$this->margin      = new ProductMargin();

		if( false ) {
            GalleryFromMeta::get_instance();
        }

	}



	/**
	 * Initializes plugin external state.
	 *
	 * The plugin internal state is initialized in the constructor and the plugin should be internally consistent after creation.
	 * The external state includes hooks execution, communication with other plugins, integration with WC etc.
	 *
	 * @return void
	 */
	public function init() {

		parent::init();

		$this->cron->hooks();
		$this->cron->schedule_update_product_prices_task();

        $this->margin->hooks();

		if ( is_admin() ) {
			$admin = new MainAdmin( $this->plugin_info );
			$admin->init();
			$admin->hooks();
		} else {
			$frontend = new MainFrontend( $this->plugin_info );
			$frontend->init();
			$frontend->hooks();
		}

	}



	/**
	 * Integrate with WordPress and with other plugins using action/filter system.
	 *
	 * @return void
	 */
	public function hooks() {
		parent::hooks();
		add_action( 'rest_api_init', array( new OrderWebHook(), 'register_route' ) );
		add_action( 'rest_api_init', array( new ProductWebHook(), 'register_route' ) );

        add_action( 'woocommerce_order_status_changed', array( new NewOrder(), 'new_order_placed' ), 20 );
        add_action( 'woocommerce_order_status_changed', array( new OrderWebHook(), 'kinguin_send_keys_only_on_paid_order' ), 999 );
	}



	/**
	 * Plugin deactivation
	 *
	 * @return void
	 */
	public function deactivate() {
		$this->cron->remove_update_product_prices_task();
	}

}
