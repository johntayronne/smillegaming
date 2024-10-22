<?php
/**
 * WooCommerce product additional data tabs class.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Admin\Product;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class DataTabs {
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
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
	}



	/**
	 * Integrate with WordPress admin product actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'product_assets' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_data_tabs' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'details_tab_content' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'system_requirements_tab_content' ) );
		add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_data_tab_inputs' ) );

        add_action( 'woocommerce_product_options_pricing', array( $this, 'kinguin_price_before_margin'), 10, 0 );
        add_filter( 'gettext',  array( $this, 'kinguin_change_backend_product_regular_price'), 100, 3 );
	}



	/**
	 * Admin product assets
	 *
	 * @return void
	 */
	public function product_assets() {
		$current_screen = get_current_screen();
		if ( is_a( $current_screen, 'WP_Screen' ) && 'product' === $current_screen->id ) {
			wp_enqueue_style(
				'kinguin-admin-product',
				$this->plugin_info->get_plugin_url() . '/assets/css/kinguin-admin-product.css'
			);
		}
	}



	/**
	 * Add WooCommerce Product data tabs: offers and details
	 *
	 * @param array $product_data_tabs WC Product data tabs configuration.
	 *
	 * @return array
	 */
	public function add_product_data_tabs( $product_data_tabs ) {

		global $post;
		if ( metadata_exists( 'post', $post->ID, '_kinguinId' ) ) {
			$product_data_tabs['kinguin_details']     = array(
				'label'  => __( 'Kinguin details', 'kinguin' ),
				'target' => 'kinguin_details',
			);
			$product_data_tabs['system_requirements'] = array(
				'label'  => __( 'System requirements', 'kinguin' ),
				'target' => 'kinguin_system_requirements',
			);
		}

		return $product_data_tabs;
	}



	/**
	 * Kinguin game details tab
	 */
	public function details_tab_content() {
		global $post;
		if ( metadata_exists( 'post', $post->ID, '_kinguinId' ) ) {
			?>
			<div id="kinguin_details" class="panel woocommerce_options_panel kinguin_panel">
				<?php
				woocommerce_wp_text_input(
					array(
						'id'                => '_kinguinId',
						'label'             => __( 'Kinguin ID', 'kinguin' ),
						'description'       => __( 'Product ID', 'woocommerce' ),
						'type'              => 'number',
						'custom_attributes' => array( 'readonly' => 'readonly' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => '_kinguinPrice',
						'label'       => __( 'Price', 'kinguin' ),
						'description' => __( 'EUR', 'woocommerce' ),
						'type'        => 'text',
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => '_steam',
						'label'       => __( 'STEAM', 'kinguin' ),
						'description' => __( 'Steam app ID', 'woocommerce' ),
						'type'        => 'number',
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => '_ageRating',
						'label'       => __( 'Age rating', 'kinguin' ),
						'description' => __( 'Age rating (PEGI or ESRB)', 'woocommerce' ),
						'type'        => 'text',
					)
				);
				woocommerce_wp_select(
					array(
						'id'      => '_regionId',
						'label'   => __( 'Region limitations', 'kinguin' ),
						'options' => $this->get_region(),
					)
				);
				woocommerce_wp_textarea_input(
					array(
						'id'    => '_activationDetails',
						'label' => __( 'Activation details', 'kinguin' ),
					)
				);
				?>
			</div>
			<?php
		}
	}



	/**
	 * Save product data tab inputs
	 *
	 * @param int $post_id Post ID.
	 */
	public function save_product_data_tab_inputs( $post_id ) {
        if( isset( $_POST['_kinguinPrice'] ) ) {
            update_post_meta( $post_id, '_kinguinPrice', sanitize_text_field($_POST['_kinguinPrice'] ) );
        }
        if( isset( $_POST['_steam'] ) ) {
            update_post_meta( $post_id, '_steam', sanitize_text_field( $_POST['_steam'] ) );
        }
        if( isset( $_POST['_ageRating'] ) ) {
            update_post_meta( $post_id, '_ageRating', sanitize_text_field( $_POST['_ageRating'] ) );
        }
        if( isset( $_POST['_regionId'] ) ) {
            update_post_meta( $post_id, '_regionId', sanitize_text_field( $_POST['_regionId'] ) );
        }
        if( isset( $_POST['_activationDetails'] ) ) {
            update_post_meta( $post_id, '_activationDetails', sanitize_textarea_field( $_POST['_activationDetails'] ) );
        }
	}



	/**
	 * Kinguin system requirements tab
	 */
	public function system_requirements_tab_content() {
		global $post;
		if ( metadata_exists( 'post', $post->ID, '_kinguinId' ) ) {
			$system_requirements = get_post_meta( $post->ID, '_systemRequirements', true );
			$system_requirements = $system_requirements ?? array_filter( $system_requirements );
			?>
			<div id="kinguin_system_requirements" class="panel woocommerce_options_panel kinguin_panel">
				<?php if ( $system_requirements ) : ?>
					<div class="kinguin_panel--requirements requirements">
						<?php foreach ( $system_requirements as $system ) : ?>
							<div class="system">
								<h1>
									<?php echo esc_html( $system['system'] ); ?>
								</h1>
								<ul>
								<?php foreach ( $system['requirement'] as $requirement ) : ?>
									<li>
										<?php echo esc_html( $requirement ); ?>
									</li>
								<?php endforeach; ?>
								</ul>
							</div>
						<?php endforeach; ?>
					</div>
				<?php else : ?>
					<div class="kinguin_panel--no-requirements">
						<?php esc_html_e( 'There are no system requirements for this product', 'kinguin' ); ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		}
	}


    /**
     * Add custom price field with value before margin
     */
    public function kinguin_price_before_margin() {
        global $post;
        if ( metadata_exists( 'post', $post->ID, '_kinguinId' ) ) {
            $product = wc_get_product($post->ID);
            woocommerce_wp_text_input(array(
                'custom_attributes' => array('readonly' => 'readonly'),
                'id' => 'wholesaler_price',
                'placeholder' => $product->get_price(),
                'class' => 'wc_input_price short',
                'label' => __('Price after margin', 'kinguin') . ' (' . get_woocommerce_currency_symbol() . ')',
            ));
        }
    }


    /**
     * Add custom title for price
     */
    public function kinguin_change_backend_product_regular_price( $translated_text, $text, $domain ) {
        global $pagenow, $post_type, $post;

            if (is_admin() && in_array($pagenow, ['post.php', 'post-new.php'])
                && 'product' === $post_type && 'Regular price' === $text && 'woocommerce' === $domain) {
                if ( metadata_exists( 'post', $post->ID, '_kinguinId' ) ) {
                    $translated_text = __('Kinguin price', 'kinguin');
                }
            }

        return $translated_text;
    }

}
