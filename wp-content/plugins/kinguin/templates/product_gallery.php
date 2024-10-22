<?php
/**
 * The Template for product/screenshots gallery
 *
 * @package     WPDesk\ILKinguin
 * @version     1.0.0
 *
 * @var string[] $photos     Full size images urls.
 * @var string[] $thumbnails Thumbails url.
 */

defined( 'ABSPATH' ) || exit;
?>

<section class="kinguin_product_gallery">
	<div id="kinguin_product_gallery_fulls" class="swiper-container gallery__fulls">
		<div class="swiper-wrapper">
			<?php foreach ( $photos as $photo ) : ?>
				<a class="swiper-slide glightbox" href="<?php echo esc_url( $photo ); ?>" data-type="image">
					<img src="<?php echo esc_url( $photo ); ?>" />
				</a>
			<?php endforeach; ?>
		</div>
		<button class="swiper-button swiper-button-next"></button>
		<button class="swiper-button swiper-button-prev"></button>
	</div>
	<div id="kinguin_product_gallery_thumbs" class="swiper-container gallery__thumbs">
		<div class="swiper-wrapper">
			<?php foreach ( $thumbnails as $thumbnail ) : ?>
				<picture class="swiper-slide">
					<img src="<?php echo esc_url( $thumbnail ); ?>" />
				</picture>
			<?php endforeach; ?>
		</div>
		<button class="swiper-button swiper-button-next"></button>
		<button class="swiper-button swiper-button-prev"></button>
	</div>
</section>
