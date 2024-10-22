<?php
/**
 * CRON actions class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Common;

use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class CRON {
	use Configuration;

	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'kinguin_update_prices', array( $this, 'update_product_prices' ) );
	}



	/**
	 * Add product prices update task to cron.
	 */
	public function schedule_update_product_prices_task() {
		if ( ! wp_next_scheduled( 'kinguin_update_prices' ) ) {
			wp_schedule_event( time(), 'daily', 'kinguin_update_prices' );
		}
	}



	/**
	 * Add product prices update task to cron.
	 */
	public function remove_update_product_prices_task() {
		wp_clear_scheduled_hook( 'kinguin_update_prices' );
	}



	/**
	 * Update product prices.
	 */
	public function update_product_prices() {
		$this->get_currency_rate( get_woocommerce_currency() );
	}

}
