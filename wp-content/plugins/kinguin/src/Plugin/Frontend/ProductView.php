<?php
/**
 * Frontend product view.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Frontend;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class ProductView {
	use Configuration;

	/**
	 * Plugin object details
	 *
	 * @var WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	private $plugin_info;



	/**
	 * Plugin constructor.
	 *
	 * @param null|WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info = null ) {
		$this->plugin_info = $plugin_info;
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'wp_enqueue_scripts', array( $this, 'single_product_assets' ), 99 );
		add_filter( 'template_include', array( $this, 'single_product_template' ), 99 );
		add_action( 'kinguin_product_content', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_title' ), 10 );
		add_action( 'kinguin_product_content', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_gallery' ), 20 );
		add_action( 'kinguin_product_content', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_add_to_cart' ), 30 );
		add_action( 'kinguin_product_content', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_accordion' ), 40 );
		add_action( 'kinguin_product_accordion', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_description' ), 10 );
		add_action( 'kinguin_product_accordion', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_system_requirements' ), 30 );
		add_action( 'kinguin_product_accordion', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_activation_details' ), 40 );
		add_action( 'kinguin_product_accordion', array( 'WPDesk\ILKinguin\Frontend\ProductView', 'product_reviews' ), 50 );
		add_action( 'kinguin_product_content', 'woocommerce_output_related_products', 300 );
	}



	/**
	 * Single product assets
	 */
	public function single_product_assets() {
		if ( is_product() && metadata_exists( 'post', get_the_ID(), '_kinguinId' ) && $this->plugin_info ) {
			wp_enqueue_style( 'kinguin-product', $this->plugin_info->get_plugin_url() . '/assets/css/kinguin-product.css', array(), $this->plugin_info->get_version(), false );
			wp_enqueue_script( 'swiper', $this->plugin_info->get_plugin_url() . '/assets/js/swiper-bundle.min.js', false, '6.7.0', true );
			wp_enqueue_script( 'glightbox', $this->plugin_info->get_plugin_url() . '/assets/js/glightbox.min.js', false, '3.2.3', true );
			wp_enqueue_script( 'kinguin-accordion', $this->plugin_info->get_plugin_url() . '/assets/js/kinguin-accordion.js', false, $this->plugin_info->get_version(), true );
			wp_enqueue_script( 'kinguin-product', $this->plugin_info->get_plugin_url() . '/assets/js/kinguin-product.js', array( 'swiper', 'glightbox', 'kinguin-accordion' ), $this->plugin_info->get_version(), true );
		}
	}



	/**
	 * Replace default single product template
	 * You may also copy /templates/single-kinguin-product.php into your theme catalog to override it for further customization
	 *
	 * @param string $template Path to template file.
	 */
	public function single_product_template( $template ) {
		if ( is_product() && metadata_exists( 'post', get_the_ID(), '_kinguinId' ) && $this->plugin_info ) {
			$user_template = locate_template( 'single-kinguin-product.php' );
			if ( $user_template ) {
				$template = $user_template;
			} else {
				$template = $this->plugin_info->get_plugin_dir() . '/templates/single-kinguin-product.php';
			}
		}
		return $template;
	}



	/**
	 * Display product title.
	 */
	public static function product_title() {
		the_title( '<h1 class="kinguin_product_title">', '</h1>' );
	}



	/**
	 * Display Kinguin screenshots gallery.
	 */
	public static function product_gallery() {

		global $post;
		global $kinguin_plugin_dir;

		$images = get_post_meta( $post->ID, '_screenshots', true );
		if ( ! empty( $images ) ) {
			$photos     = array_column( $images, 'url' );
			$thumbnails = array_column( $images, 'thumbnail' );
			include $kinguin_plugin_dir . '/templates/product_gallery.php';
		}
	}



	/**
	 * Display add to cart form with offer selection.
	 */
	public static function product_add_to_cart() {
		global $kinguin_plugin_dir;
		global $product;
		global $post;
		$attributes        = $product->get_attributes();
		$age_rating        = get_post_meta( $post->ID, '_ageRating', true );
		$metacritic_score  = get_post_meta( $post->ID, '_metacriticScore', true );
		$region_limitation = get_post_meta( $post->ID, '_regionId', true );
		if ( $region_limitation ) {
			$region_limitation = ( new ProductView )->get_region( (int) $region_limitation );
		}
		$release_date = get_post_meta( $post->ID, '_releaseDate', true );
		if ( $release_date ) {
			$date         = new \DateTime( $release_date );
			$date_format  = get_option( 'date_format' );
			$release_date = $date->format( $date_format );
		}
		$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
		$steam         = (bool) get_post_meta( $post->ID, '_steam', true );
		$price_html    = $product->get_price_html();
		$original_name = get_post_meta( $post->ID, '_originalName', true );
		include $kinguin_plugin_dir . '/templates/product_add_to_cart.php';
	}



	/**
	 * Display product accordion with product details.
	 */
	public static function product_accordion() {
		global $kinguin_plugin_dir;
		include $kinguin_plugin_dir . '/templates/product_accordion.php';
	}



	/**
	 * Add product description tab to accordion.
	 */
	public static function product_description() {
		if ( ! empty( get_the_content() ) ) {
			global $kinguin_plugin_dir;
			include $kinguin_plugin_dir . '/templates/accordion/description.php';
		}
	}



	/**
	 * Add system requirements tab to accordion.
	 */
	public static function product_system_requirements() {

		global $post;
		global $kinguin_plugin_dir;

		$requirements = get_post_meta( $post->ID, '_systemRequirements', true );
		if ( ! empty( $requirements ) ) {
			include $kinguin_plugin_dir . '/templates/accordion/system_requirements.php';
		}
	}



	/**
	 * Add activation details tab to accordion.
	 */
	public static function product_activation_details() {

		global $post;
		global $kinguin_plugin_dir;

		$details = get_post_meta( $post->ID, '_activationDetails', true );
		if ( ! empty( $details ) ) {
			include $kinguin_plugin_dir . '/templates/accordion/activation_details.php';
		}
	}



	/**
	 * Add product reviews tab to accordion.
	 */
	public static function product_reviews() {
		global $kinguin_plugin_dir;
		if ( wc_review_ratings_enabled() ) {
			include $kinguin_plugin_dir . '/templates/accordion/reviews.php';
		}
	}
}
