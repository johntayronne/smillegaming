<?php

use WPDesk\ILKinguin\Common\GalleryFromMeta;

defined( 'ABSPATH' ) || exit;

// Note: `wc_get_gallery_image_html` was added in WC 3.3.2 and did not exist prior. This check protects against theme overrides being used on older versions of WC.
if ( ! function_exists( 'wc_get_gallery_image_html' ) ) {
    return;
}

global $product;

$external_image = GalleryFromMeta::get_instance();

$image_urls = $external_image->get_product_gallery_url( $product->get_id() );

foreach ( $image_urls as $image_url ) {
    if ( ! $image_url ) {
        continue;
    }

    echo $external_image->get_gallery_single_image( $image_url); // phpcs:disable WordPress.XSS.EscapeOutput.OutputNotEscaped
}