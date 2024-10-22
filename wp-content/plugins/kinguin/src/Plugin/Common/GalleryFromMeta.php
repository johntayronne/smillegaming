<?php
/**
 * Email with keys.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Common;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinKeysEmailFailed;

defined( 'ABSPATH' ) || exit;

class GalleryFromMeta {
	use Configuration;

    protected static $instance;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    protected function __construct() {

        add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

        // show the product image in shop list.
        add_filter( 'woocommerce_product_get_image', array( $this, 'get_image' ), 10, 6 );

        // show the gallery images in single product page.
        add_filter( 'woocommerce_single_product_image_thumbnail_html', array( $this, 'thumbnail_html' ), 10, 2 );
        add_filter( 'wc_get_template', array( $this, 'get_template' ), 10, 5 );
    }

    public function add_meta_boxes() {

        add_meta_box(
            'uyond_product_gallery_urls',
            'Kinguin Gallery images',
            array( $this, 'echo_product_gallery_url_box' ),
            'product',
            'side',
            'default'
        );
    }


    public function echo_product_gallery_url_box( $post ) {
        wp_nonce_field( 'uyond_product_gallery_url_metabox_nonce', 'uyond_product_gallery_url_nonce' );

        $gallery_urls = $this->get_product_gallery_url( $post->ID );

        if( ! empty($gallery_urls) ) {

            foreach ($gallery_urls as $i => $gallery_url) {

                if ($gallery_url) {
                    ?>
                    <img style="max-width: 50%;" src="<?php echo esc_url($gallery_url); ?>"/>
                    <?php
                }
            }
        }

    }


    public function get_product_img_url( $id ) {

        $value = wp_get_attachment_image_src( get_post_thumbnail_id( $id ), 'single-post-thumbnail' );
        
        //error_log(print_r( $value, true));

        if( is_array($value) && isset($value[0]) && !empty($value[0]) ) {
            return $value[0];
        }

        return '';
    }


    public function get_product_gallery_url( $id ) {

        $return = array();

        $screenshots_arr = get_post_meta( $id, '_screenshots', true );

        if( ! empty($screenshots_arr) && is_array($screenshots_arr) ) {
            foreach ($screenshots_arr as $img_data) {
                if( isset($img_data['url']) && ! empty($img_data['url']) ) {
                    $return[] = $img_data['url'];
                }
            }
        }

        return $return;
    }

    public function get_image( $html, $product, $woosize, $attr, $placeholder, $image ) {
        $img_url = $this->get_product_img_url( $product->get_id() );

        return '<img width="260" height="300" src="' . esc_url( $img_url ) . '" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="" loading="lazy" />';
    }

    public function get_template( $template, $template_name, $args, $template_path, $default_path ) {
        if ( 'single-product/product-thumbnails.php' === $template_name ) {
            $plugin_info = new WPDesk_Plugin_Info();
            $template = KINGUIN_PLUGIN_DIR . $plugin_info->get_plugin_dir() . '/templates/single-product/product-thumbnails.php';
        }

        return $template;
    }

    public function get_gallery_single_image( $img_url ) {
        return sprintf(
            '<div data-thumb="%1$s" data-thumb-alt="" class="woocommerce-product-gallery__image">
                    <a href="%1$s">
                    <img width="600" 
                        height="auto" 
                        src="%1$s" 
                        class="wp-post-image" 
                        alt="" 
                        loading="lazy" 
                        title="" 
                        data-caption="" 
                        data-src="%1$s" 
                        data-large_image="%1$s" 
                        data-large_image_width="600" 
                        data-large_image_height="600" />
                    </a>
                    </div>',
            $img_url
        );
    }

    public function thumbnail_html( $html, $post_thumbnail_id ) {
        global $product;
        $product_id = $product->get_id();
        //$product_name = $product->get_name();
        $img_url = $this->get_product_img_url( $product_id );

        if ( '' === $img_url ) {
            return $html;
        }

        return $this->get_gallery_single_image( $img_url );
    }

}