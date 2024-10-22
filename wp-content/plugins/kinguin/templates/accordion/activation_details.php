<?php
/**
 * The Template for accordion tab with activation details.
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 *
 * @var string $details Activation details.
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="accordion__item accordion--activation_details">
	<div class="accordion__item__name">
		<h2>
			<?php esc_html_e( 'Activation details', 'kinguin' ); ?>
		</h2>
	</div>
	<div class="accordion__item__content">
		<div class="wrapper">
			<?php echo wp_kses_post( nl2br( $details ) ); ?>
		</div>
	</div>
</div>
