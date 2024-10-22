<?php
/**
 * The Template for accordion tab with product description.
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="accordion__item accordion--description">
	<div class="accordion__item__name">
		<h2>
			<?php esc_html_e( 'Description', 'kinguin' ); ?>
		</h2>
	</div>
	<div class="accordion__item__content">
		<div class="wrapper">
			<?php the_content(); ?>
		</div>
	</div>
</div>
