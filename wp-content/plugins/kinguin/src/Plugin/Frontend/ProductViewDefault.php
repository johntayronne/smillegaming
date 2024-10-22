<?php
/**
 * Frontend product view.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Frontend;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Configuration;

defined('ABSPATH') || exit;

class ProductViewDefault
{
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
    public function __construct(WPDesk_Plugin_Info $plugin_info = null)
    {
        $this->plugin_info = $plugin_info;
    }


    /**
     * Integrate with WordPress admin actions and filters.
     *
     * @return void
     */
    public function hooks()
    {
        add_filter('woocommerce_display_product_attributes', array($this, 'default_theme_extra_product_information'), 10, 2);
        add_filter('wc_product_sku_enabled', array( $this, 'hide_sku_for_kinguin_products' ) );
        /*add_action('woocommerce_before_single_product_summary', array( $this, 'woocommerce_before_single_product_summary' ) );*/
        add_filter('woocommerce_product_tabs', array($this, 'add_product_tabs'), 9999);
    }

    /*public function woocommerce_before_single_product_summary () {

        global $product;

        if( ! $this->is_kinguin_product( $product ) ) return;

        add_filter('wc_product_sku_enabled', '__return_false');
    }*/


    public function add_product_tabs($tabs)
    {
        global $product;

        if (!$product) return;

        if( ! $this->is_kinguin_product( $product ) ) return $tabs;

        $requirements = get_post_meta( $product->get_id(), '_systemRequirements', true );
        if( ! empty($requirements) && is_array($requirements) ) {
            $tabs['sys_req'] = array(
                'title' => __('System requirements', 'kinguin'),
                'priority' => 1, // TAB SORTING (DESC 10, ADD INFO 20, REVIEWS 30)
                'callback' => array($this, 'tab_system_requirements_content'),
            );
        }

        $details = get_post_meta( $product->get_id(), '_activationDetails', true );
        if ( ! empty( $details ) ) {
            $tabs['act_det'] = array(
                'title' => __('Activation details', 'kinguin'),
                'priority' => 2,
                'callback' => array($this, 'tab_activation_details_content'),
            );
        }

        return $tabs;
    }

    public function tab_system_requirements_content()
    {
        global $product;

        if ( ! $product ) return;

        $requirements = get_post_meta( $product->get_id(), '_systemRequirements', true );
        if( ! empty($requirements) && is_array($requirements) ) { ?>
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
        <?php }

    }


    public function tab_activation_details_content()
    {
        global $product;

        if ( ! $product ) return;

        $details = get_post_meta( $product->get_id(), '_activationDetails', true );
        if ( ! empty( $details ) ) { ?>
            <div class="wrapper">
                <?php echo wp_kses_post( nl2br( $details ) ); ?>
            </div>
        <?php }
    }


    public function hide_sku_for_kinguin_products($enabled)
    {
        global $product;

        if ( ! $product ) return;

        if ( ! is_admin() && is_product() ) {

            $sku_label = explode('-', $product->get_sku())[0];

            if ($sku_label === 'kinguin') {
                return false;
            }
        }

        return $enabled;
    }


    /**
     * Show aaditional data in product attributes table
     *
     * @param array $product_attributes .
     * @param object $product .
     * @return mixed
     */
    public function default_theme_extra_product_information($product_attributes, $product)
    {

        if( ! $this->is_kinguin_product( $product ) ) return $product_attributes;

        $product_id = $product->get_id();

        $release_date = get_post_meta($product_id, '_releaseDate', true);
        if (trim($release_date) != '') {
            $product_attributes['kng_release_date'] = array(
                'label' => __('Release date', 'kinguin'),
                'value' => esc_attr($release_date)
            );
        }

        $steam = (bool)get_post_meta($product_id, '_steam', true);
        if ($steam) {
            $product_attributes['kng_steam'] = array(
                'label' => __('Steam', 'kinguin'),
                'value' => __('Yes', 'kinguin')
            );
        }

        $age_rating = get_post_meta($product_id, '_ageRating', true);
        if (trim($age_rating) != '') {
            $product_attributes['kng_age_rating'] = array(
                'label' => __('Age rating', 'kinguin'),
                'value' => esc_attr($age_rating)
            );
        }

        $metacritic_score = get_post_meta($product_id, '_metacriticScore', true);
        if (trim($metacritic_score) != '') {
            $product_attributes['kng_metacritic_score'] = array(
                'label' => __('Metacritic score', 'kinguin'),
                'value' => esc_attr($metacritic_score)
            );
        }

        $region_limitation = get_post_meta($product_id, '_regionId', true);
        if (trim($region_limitation) != '') {
            $product_attributes['kng_region_limitation'] = array(
                'label' => __('Regional limitations', 'kinguin'),
                'value' => esc_attr($region_limitation)
            );
        }


        return $product_attributes;
    }


    private function is_kinguin_product( $product ) {

        $sku_label = explode('-', $product->get_sku())[0];

        if ($sku_label === 'kinguin') {
            return true;
        }

        return false;
    }
}
