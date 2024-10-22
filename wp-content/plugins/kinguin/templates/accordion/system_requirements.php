<?php
/**
 * The Template for accordion tab with system requirements.
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 *
 * @var array $requirements System requirements.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="accordion__item accordion--system_requirements">
	<div class="accordion__item__name">
		<h2>
			<?php esc_html_e( 'System requirements', 'kinguin' ); ?>
		</h2>
	</div>
	<div class="accordion__item__content">
		<div class="wrapper">
			<?php foreach ( $requirements as $requirement ) : ?>
				<div class="system">
					<?php echo esc_html( $requirement['system'] ); ?>
				</div>
				<div class="requirement">
					<?php foreach ( $requirement['requirement'] as $params ) : ?>
						<?php $param = explode( ':', $params ); ?>
						<?php if ( 2 === count( $param ) ) : ?>
							<dl>
								<dt><?php echo esc_html( trim( $param[0] ) ); ?></dt>
								<dd><?php echo esc_html( trim( $param[1] ) ); ?></dd>
							</dl>
						<?php else : ?>
							<div>
								<?php echo esc_html( $params ); ?>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

