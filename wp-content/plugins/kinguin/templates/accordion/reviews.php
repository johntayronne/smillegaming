<?php
/**
 * The Template for accordion tab with product reviews.
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 *
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="accordion__item accordion--reviews">
	<div class="accordion__item__name">
		<h2>
			<?php esc_html_e( 'Reviews', 'kinguin' ); ?>
		</h2>
	</div>
	<div class="accordion__item__content">
		<div class="wrapper">
			<?php comments_template( 'woocommerce/single-product-reviews' ); ?>
		</div>
	</div>
</div>
