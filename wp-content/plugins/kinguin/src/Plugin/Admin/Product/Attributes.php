<?php
/**
 * Product attributes.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin\Product;

defined( 'ABSPATH' ) || exit;

class Attributes {

	/**
	 * Array of existing attributes slugs
	 *
	 * @var array $existing_attributes_tax .
	 */
	private $existing_attributes_tax;



	/**
	 * Attributes class constructor.
	 */
	public function __construct() {
		$this->existing_attributes_tax = wp_list_pluck( wc_get_attribute_taxonomies(), 'attribute_name' );
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'developers_attribute' ) );
		add_action( 'admin_init', array( $this, 'publishers_attribute' ) );
		add_action( 'admin_init', array( $this, 'genres_attribute' ) );
		add_action( 'admin_init', array( $this, 'platform_attribute' ) );
		add_action( 'admin_init', array( $this, 'languages_attribute' ) );
	}



	/**
	 * Register developers product attribute taxonomy
	 */
	public function developers_attribute() {
		if ( ! in_array( 'developers', $this->existing_attributes_tax, true ) ) {
			wc_create_attribute(
				array(
					'slug'         => 'developers',
					'name'         => __( 'Developers', 'kinguin' ),
					'type'         => 'select',
					'orderby'      => 'menu_order',
					'has_archives' => false,
				)
			);
		}
	}



	/**
	 * Register publishers product attribute taxonomy
	 */
	public function publishers_attribute() {
		if ( ! in_array( 'publishers', $this->existing_attributes_tax, true ) ) {
			wc_create_attribute(
				array(
					'slug'         => 'publishers',
					'name'         => __( 'Publishers', 'kinguin' ),
					'type'         => 'select',
					'orderby'      => 'menu_order',
					'has_archives' => false,
				)
			);
		}
	}



	/**
	 * Register genres product attribute taxonomy
	 */
	public function genres_attribute() {
		if ( ! in_array( 'genres', $this->existing_attributes_tax, true ) ) {
			wc_create_attribute(
				array(
					'slug'         => 'genres',
					'name'         => __( 'Genres', 'kinguin' ),
					'type'         => 'select',
					'orderby'      => 'menu_order',
					'has_archives' => false,
				)
			);
		}
	}



	/**
	 * Register platform product attribute taxonomy
	 */
	public function platform_attribute() {
		if ( ! in_array( 'platform', $this->existing_attributes_tax, true ) ) {
			wc_create_attribute(
				array(
					'slug'         => 'platform',
					'name'         => __( 'Platform', 'kinguin' ),
					'type'         => 'select',
					'orderby'      => 'menu_order',
					'has_archives' => false,
				)
			);
		}
	}



	/**
	 * Register languages product attribute taxonomy
	 */
	public function languages_attribute() {
		if ( ! in_array( 'languages', $this->existing_attributes_tax, true ) ) {
			wc_create_attribute(
				array(
					'slug'         => 'languages',
					'name'         => __( 'Languages', 'kinguin' ),
					'type'         => 'select',
					'orderby'      => 'menu_order',
					'has_archives' => false,
				)
			);
		}
	}

}
