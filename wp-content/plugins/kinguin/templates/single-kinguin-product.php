<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.6.4
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

	<?php
		/**
		 * before_kinguin_product_content hook.
		 */
		do_action( 'before_kinguin_product_content' );
	?>

		<?php while ( have_posts() ) : ?>
			<?php the_post(); ?>

			<?php
				/**
				 * kinguin_product_content hook.
				 *
				 * @hooked WPDesk\ILKinguin\Frontend\ProductView::product_title()
				 */
				do_action( 'kinguin_product_content' );
			?>

		<?php endwhile; // end of the loop. ?>

	<?php
		/**
		 * after_kinguin_product_content hook.
		 *
		 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'after_kinguin_product_content' );
	?>

<?php
get_footer( 'shop' );