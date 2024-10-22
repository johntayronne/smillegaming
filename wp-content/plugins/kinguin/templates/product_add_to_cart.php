<?php
/**
 * The Template for add to cart form
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 *
 * @var object $product           WooCommerce product object.
 * @var string $original_name     Product name.
 * @var string $release_date      Release date.
 * @var bool   $steam             Steam
 * @var string $age_rating        Age rating.
 * @var string $metacritic_score  Metacritic score.
 * @var string $region_limitation Regional limitations.
 * @var array  $attributes        Array of product attributes.
 * @var string $shop_page_url     Shop page url.
 * @var string $price_html        Product price html.
 */

defined( 'ABSPATH' ) || exit;
?>

<form id="kinguin_product_add_to_cart" class="kinguin_product_add_to_cart add-to-cart" action="<?php the_permalink( $product->get_id() ); ?>" method="post" enctype="multipart/form-data">
	<div class="add-to-cart__product">
		<h2><?php echo esc_html( $original_name ); ?></h2>
		<div class="wrapper details">
			<?php if ( $release_date ) : ?>
				<dl>
					<dt><?php esc_html_e( 'Release date', 'kinguin' ); ?></dt>
					<dd><?php echo esc_html( $release_date ); ?></dd>
				</dl>
			<?php endif; ?>
			<?php if ( $steam ) : ?>
				<dl>
					<dt><?php esc_html_e( 'STEAM', 'kinguin' ); ?></dt>
					<dd><?php echo esc_html( $steam ? __( 'Yes', 'kinguin' ) : '' ); ?></dd>
				</dl>
			<?php endif; ?>
			<?php if ( $age_rating ) : ?>
				<dl>
					<dt><?php esc_html_e( 'Age rating', 'kinguin' ); ?></dt>
					<dd><?php echo esc_html( $age_rating ); ?></dd>
				</dl>
			<?php endif; ?>
			<?php if ( $metacritic_score ) : ?>
				<dl>
					<dt><?php esc_html_e( 'Metacritic score', 'kinguin' ); ?></dt>
					<dd><?php echo esc_html( $metacritic_score ); ?></dd>
				</dl>
			<?php endif; ?>
			<?php if ( $region_limitation ) : ?>
				<dl>
					<dt><?php esc_html_e( 'Regional limitations', 'kinguin' ); ?></dt>
					<dd><?php echo esc_html( $region_limitation ); ?></dd>
				</dl>
			<?php endif; ?>
			<?php if ( $attributes ) : ?>
				<?php foreach ( $attributes as $attribute ) : ?>
					<?php if ( $attribute->is_taxonomy() ) : ?>
						<dl>
							<dt><?php echo esc_html( wc_attribute_label( $attribute->get_name() ) ); ?></dt>
							<dd>
								<?php foreach ( $attribute->get_terms() as $term ) : ?>
									<a href="<?php echo esc_url( add_query_arg( substr( $attribute->get_taxonomy(), 3 ), $term->slug, $shop_page_url ) ); ?>">
										<?php echo esc_html( $term->name ); ?>
									</a>
								<?php endforeach; ?>
							</dd>
						</dl>
					<?php endif; ?>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</div>
	<div class="add-to-cart__summary">
        <?php if( $product->get_stock_quantity() > 0 ) { ?>
		<button class="add_to_cart_button button alt" name="add-to-cart" type="submit" value="<?php echo esc_attr( $product->get_id() ); ?>">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32"><path d="M10 24a3 3 0 1 0 3 3 3 3 0 0 0-3-3zm0 4a1 1 0 1 1 1-1 1 1 0 0 1-1 1zm12-4a3 3 0 1 0 3 3 3 3 0 0 0-3-3zm0 4a1 1 0 1 1 1-1 1 1 0 0 1-1 1zM6.78 6l-.43-1.73A3 3 0 0 0 3.44 2H2v2h1.44a1 1 0 0 1 1 .76L8 19.24h0l.37 1.49A3 3 0 0 0 11.31 23H25v-2H11.31a1 1 0 0 1-1-.76l-.12-.5L25 16.25a3 3 0 0 0 2.23-2.19l2-8.06zm18.55 7.58a1 1 0 0 1-.75.73L9.73 17.8 7.28 8h19.44z"/></svg>
			<?php echo wp_kses_post( $price_html ); ?>
			<?php esc_html_e( 'Buy now!', 'kinguin' ); ?>
		</button>
        <?php } else { ?>
            <p class="kinguin out-of-stock">
                <?php esc_html_e( 'Out of stock', 'woocommerce' ); ?>
            </p>
        <?php } ?>
	</div>
</form>
