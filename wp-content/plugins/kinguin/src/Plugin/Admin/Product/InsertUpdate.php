<?php
/**
 * Product import/update class.
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin\Product;

use WPDesk\ILKinguin\Admin\Configuration;

defined( 'ABSPATH' ) || exit;

class InsertUpdate {
	use Configuration;

	/**
	 * Currency rate.
	 *
	 * @var float $currency_rate Currency rate.
	 */
	private $currency_rate;



	/**
	 * Set currency rate for currency conversion
	 * Base currency is EURO
	 */
	public function set_currency_rate() {
		$this->currency_rate = $this->get_currency_rate( get_woocommerce_currency() );
	}



	/**
	 * Convert price from EURO to shop selected currency.
	 *
	 * @param float $price Kinguin price in EURO.
	 */
	private function convert_price( $price ) {
		return (float) number_format( $price * $this->currency_rate, 2, '.', '' );
	}


    /**
     * Manage Kinguin products
     * Add or insert given product object
     *
     * @param array $product Kinguin single product from API response.
     * @return int|bool
     */
    public function manage_webhook( array $product ) {

        $webhook_is_working = '1';
        $cache_key_webhook_status = 'kinguin_webhook_is_working';
        $cache_key = 'existing_kinguin_ids';

        if( $this->is_webhook_import_enabled() ) {

            set_transient( $cache_key_webhook_status, $webhook_is_working, 7200 );

            //\wc_get_logger()->debug( 'webhook_import_enabled', array( 'source' => 'kinguin-test-log' ) );

            $save_to_log = get_option('kinguin_webhook_log', false);

            //\wc_get_logger()->debug( 'save to log? ' . $save_to_log, array( 'source' => 'kinguin-test-log' ) );

            if ( get_option( 'kinguin_import_only_existing', false ) ) {

                //\wc_get_logger()->debug( 'kinguin_import_only_existing', array( 'source' => 'kinguin-test-log' ) );

                $existing_kinguin_ids = get_transient( $cache_key );

                if ( empty( $existing_kinguin_ids ) ) {

                    //\wc_get_logger()->debug( 'transient empty', array( 'source' => 'kinguin-test-log' ) );

                    global $wpdb;

                    $query = "SELECT pm1.meta_value
                      FROM $wpdb->posts p
                      JOIN $wpdb->postmeta pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_productId'
                      JOIN $wpdb->postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_sku' AND pm2.meta_value LIKE 'kinguin-%'
                      WHERE p.post_type = 'product'
                      ";

                    $existing_meta_values = $wpdb->get_results($query, ARRAY_A);

                    $existing_kinguin_ids = [];

                    if (!empty($existing_meta_values)) {
                        foreach ($existing_meta_values as $value) {
                            if (isset($value['meta_value']) && !empty($value['meta_value'])) {
                                $existing_kinguin_ids[] = $value['meta_value'];
                            }
                        }
                    }

                    $expiration = 24 * 3600;
                    set_transient( $cache_key, $existing_kinguin_ids, $expiration );
                }

                if ( is_array($existing_kinguin_ids)
                    && in_array( $product['productId'], $existing_kinguin_ids ) ) {

                    //\wc_get_logger()->debug( 'yes, product exists', array( 'source' => 'kinguin-test-log' ) );

                    $post_id = $this->get_post_id( $product['productId'] );

                    return $this->update($post_id, $product, $save_to_log );
                }

            } else {

                if ( $this->check_filter_conditions($product) ) {

                    $post_id = $this->get_post_id( $product['productId'] );
                    if ($post_id) {
                        return $this->update($post_id, $product, $save_to_log);
                    }
                    return $this->insert( $product, $save_to_log );
                }

            }
        }

    }


	/**
	 * Manage Kinguin products
	 * Add or insert given product object
	 *
	 * @param array $product Kinguin single product from API response.
     * @return int|bool
	 */
	public function manage( array $product) {

        $post_id = $this->get_post_id( $product['productId'] );
        if ($post_id) {
            // if kinguin product already exists
            return $this->update($post_id, $product);
        }
        // or create new
        return $this->insert( $product );
	}



	/**
	 * Check if given product id exists in WooCommerce products
	 *
	 * @param string $product_id Kinguin productId.
	 *
	 * @return int|bool
	 */
	private function get_post_id( string $product_id ) {
		global $wpdb;

		$query = "SELECT $wpdb->posts.ID 
					FROM $wpdb->posts, $wpdb->postmeta 
				   WHERE $wpdb->posts.ID = $wpdb->postmeta.post_id 
					 AND $wpdb->postmeta.meta_key = '_productId' 
				     AND $wpdb->postmeta.meta_value = '%s' 
				ORDER BY $wpdb->posts.ID ASC";

		$posts = $wpdb->get_results( $wpdb->prepare( $query, $product_id ), ARRAY_N );

		if ( is_array( reset( $posts ) ) ) {
			return (int) reset( $posts )[0];
		} else {
			return false;
		}
	}



	/**
	 * Create new WooCommerce product
	 *
	 * @param array $product Kinguin single product from API response.
	 * @param bool $is_new_item flag to check if operation update or insert.
	 */
	public function prepare_post( array $product , bool $is_new_item = null) {


		$post = array(
			'post_title'   => $product['name'],
            'post_date'    => date('Y-m-d H:i:s'),
			'post_content' => $product['description'] ?? '',
			'post_status'  => 'publish',
			'post_author'  => get_current_user_id(),
			'post_type'    => 'product',
			'post_name'    => sanitize_title( $product['originalName'] ?? $product['name'] ),
			'meta_input'   => array(
				'_virtual'       => 'yes',
				'_manage_stock'  => 'yes',
				'_stock'         => $product['qty'] ?? '',
				'_stock_status'  => $product['qty'] ? 'instock' : '',
				'_price'         => $product['price'] ? $this->convert_price( (float) $product['price'] ) : '',
				'_regular_price' => $product['price'] ? $this->convert_price( (float) $product['price'] ) : '',
				'_kinguinPrice'  => $product['price'] ?? '',
				'_sku'           => 'kinguin-' . $product['kinguinId'],
			),
		);

		// Images.
		if ( isset( $product['images'] ) && ! empty( array_filter( $product['images'] ) ) ) {
		    if( $is_new_item ) { // save only images for new products, avoid duplicates during update
                if (isset($product['images']['cover']['url']) || isset($product['images']['cover']['thumbnail'])) {
                    $attachment_id = $this->attach_cover(($product['images']['cover']['url'] ?? $product['images']['cover']['thumbnail']));
                    if (is_int($attachment_id)) {
                        $post['meta_input']['_thumbnail_id'] = $attachment_id;
                    }
                }
            }
			if ( isset( $product['images']['screenshots'] ) ) {
				$post['meta_input']['_screenshots'] = $product['images']['screenshots'];
			}

            if ( isset( $product['images']['cover']['url'] ) ) {
                $post['meta_input']['_kinguin_cover'] = $product['images']['cover']['url'];
            }
		}

		// Videos.
		if ( isset( $product['videos'] ) && ! empty( array_filter( $product['videos'] ) ) ) {
			$post['meta_input']['_videos'] = $product['videos'];
		}

		// Add product meta.
		$metas = $this->set_product_meta(
			$product,
			array(
				'kinguinId',
				'productId',
				'originalName',
				'releaseDate',
				'cheapestOfferId',
				'isPreorder',
				'metacriticScore',
				'regionId',
				'activationDetails',
				'systemRequirements',
				'ageRating',
				'steam',
				'updatedAt',
			)
		);
		if ( $metas ) {
			$post = array_merge_recursive( $post, $metas );
		}

		// Product tags.
		if ( isset( $product['tags'] ) ) {
			$post['tax_input']['product_tag'] = $this->get_terms_ids( $product['tags'], 'product_tag' );
		}

		// Add product attributes.
		$attributes = $this->set_product_attributes(
			$product,
			array(
				'developers',
				'publishers',
				'genres',
				'platform',
				'languages',
			)
		);
		if ( $attributes ) {
			$post = array_merge_recursive( $post, $attributes );
		}

		return $post;
	}



	/**
	 * Create new WooCommerce product
	 *
	 * @param array $product Kinguin single product from API response.
	 * @param bool $save_to_log.
     * @return int|string post ID
	 */
	public function insert( array $product, bool $save_to_log = null ) {

	    $post = $this->prepare_post( $product, 1 );

        $post_id = wp_insert_post( $post );

        if( $save_to_log ) {
            $this->save_wc_log( $post );
        }

        if ( ! is_wp_error( $post_id ) ) {

            if( $save_to_log ) {
                \wc_get_logger()->debug('Kinguin webhook - new product CREATED (ID: ' . $post_id . ')' . PHP_EOL, array('source' => 'kinguin-webhook-log'));
            }

            // assign main category
            $kinguin_main_category_id = $this->get_kinguin_main_category( $product );
            $res = wp_set_object_terms( $post_id, (int) $kinguin_main_category_id, 'product_cat', false );

            // assign subcategories
            if ( ! is_wp_error( $res ) ) {
                $this->kinguin_set_subcategory( $product, (int) $kinguin_main_category_id, $post_id );
            }

            return $post_id;
        } else {

            \wc_get_logger()->debug( 'Kinguin webhook - error during creation of product: '
                . $product['kinguinId'] . ' - ' . $product['name'] . PHP_EOL, array( 'source' => 'kinguin-webhook-log' ) );
            \wc_get_logger()->debug( 'Kinguin webhook - error during creation of product: '
                . $product['kinguinId'] . ' - ' . $product['name'] . PHP_EOL, array( 'source' => 'kinguin-debug-log' ) );
        }
	}

	private function save_wc_log( $post ) {

        \wc_get_logger()->debug( 'Kinguin webhook - for product: ' . $post['meta_input']['_kinguinId'], array( 'source' => 'kinguin-webhook-log' ) );
        \wc_get_logger()->debug( 'Name: ' . $post['post_title'], array( 'source' => 'kinguin-webhook-log' ) );
        \wc_get_logger()->debug( 'Qty: ' . $post['meta_input']['_stock'], array( 'source' => 'kinguin-webhook-log' ) );
        \wc_get_logger()->debug( 'Price: ' . $post['meta_input']['_price'], array( 'source' => 'kinguin-webhook-log' ) );
        \wc_get_logger()->debug( 'Kinguin productId: ' . $post['meta_input']['_productId'], array( 'source' => 'kinguin-webhook-log' ) );
        if( count($post['meta_input']['_cheapestOfferId']) === 1) {
            \wc_get_logger()->debug('cheapestOfferId: ' . $post['meta_input']['_cheapestOfferId'][0], array('source' => 'kinguin-webhook-log'));
        } else {
            \wc_get_logger()->debug('cheapestOfferId: ' . print_r($post['meta_input']['_cheapestOfferId'], true), array('source' => 'kinguin-webhook-log'));
        }

    }


	public function check_filter_conditions( array $product ) {
        $filter = get_option( 'kinguin_settings_import' );

        if( !empty( $filter['kinguinId'] ) ) {
            $arr_ids = explode(',', $filter['kinguinId'] );
            if( !empty( $arr_ids ) ) {
                foreach( $arr_ids as $id) {
                    if(!empty( $id ) ) {
                        if( $id != $product['kinguinId'] ) {
                            return false;
                        }
                    }
                }
            }
        }
        if(!empty($filter['name'])) {
            if(strpos(strtolower($product['name']), $filter['name']) === false ) {
                return false;
            }
        }
        if(!empty($filter['priceFrom'])) {
            if( $product['price'] < $filter['priceFrom'] ) {
                return false;
            }
        }
        if(!empty($filter['priceTo'])) {
            if( $product['price'] > $filter['priceTo'] ) {
                return false;
            }
        }
        if(!empty($filter['isPreorder'])) {
            if( $product['isPreorder'] !== false && $filter['isPreorder'] !== 'yes') {
                return false;
            }
            if( $product['isPreorder'] !== true && $filter['isPreorder'] !== 'no') {
                return false;
            }
        }
        if(!empty($filter['languages']) && !empty($product['languages']) ) {
            if( !in_array($filter['languages'], $product['languages']) ) {
                return false;
            }
        }
        if(!empty($filter['regionId'])) {
            if( $filter['regionId'] != $product['regionId'] )  {
                return false;
            }
        }
        if(!empty($filter['tags'])) {

            $if_at_least_one_common = array_intersect($filter['tags'], $product['tags']);

            if (empty($if_at_least_one_common)) {
                return false;
            }
        }
        if(!empty($filter['genre']) && !empty($product['genres'])) {
            $if_at_least_one_common = array_intersect($filter['genre'], $product['genres']);

            if (empty($if_at_least_one_common)) {
                return false;
            }
        }
        if(!empty($filter['platforms'])) {
            if( !in_array($product['platforms'], $filter['platforms']) ) {
                return false;
            }
        }
        return true;
    }



	/**
	 * Update existing WooCommerce product
	 *
	 * @param int $post_id product ID.
	 * @param array $product Kinguin single product from API response.
	 * @param bool $save_to_log.
     * @return int|string post ID
	 */
	public function update( int $post_id, array $product, bool $save_to_log = null ) {

		$post       = $this->prepare_post( $product );

        if( $save_to_log ) {
            $this->save_wc_log( $post );
        }

		$post['ID'] = $post_id;
		$post_id = wp_update_post( $post );
		if ( ! is_wp_error( $post_id ) ) {

            if( $save_to_log ) {
                \wc_get_logger()->debug('Kinguin webhook - product UPDATED (ID: ' . $post_id . ')' . PHP_EOL, array('source' => 'kinguin-webhook-log'));
            }
			return $post_id;
		} else {
            \wc_get_logger()->debug( 'Kinguin webhook - error during update of product: '
                . $product['kinguinId'] . ' - ' . $product['name'] . ', ID in store ' . $post_id . PHP_EOL, array( 'source' => 'kinguin-webhook-log' ) );
        }
	}




	/**
	 * Download cover image to media library
	 *
	 * @param string $url Url address of cover to be downloaded.
	 *
	 * @return false
	 */
	private function attach_cover( string $url ) {

		require_once ABSPATH . 'wp-admin/includes/image.php';
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$temp_file = download_url( $url, 5 );

		if ( ! is_wp_error( $temp_file ) ) {

			$file = array(
				'name'     => basename( $url ),
				'type'     => function_exists('mime_content_type') ? mime_content_type( $temp_file ) : 'image/jpeg',
				'tmp_name' => $temp_file,
				'error'    => 0,
				'size'     => filesize( $temp_file ),
			);

			$results = wp_handle_sideload(
				$file,
				array(
					'test_form' => false,
					'test_size' => true,
				)
			);

			if ( empty( $results['error'] ) ) {

				$attachement_id = wp_insert_attachment(
					array(
						'guid'           => $results['url'],
						'post_mime_type' => $results['type'],
						'post_title'     => pathinfo( $url, PATHINFO_FILENAME ),
						'post_content'   => '',
						'post_status'    => 'inherit',
					),
					$results['file']
				);

				if ( ! is_wp_error( $attachement_id ) ) {
					$data = wp_generate_attachment_metadata( $attachement_id, $results['file'] );
					wp_update_attachment_metadata( $attachement_id, $data );
				}

				return $attachement_id;
			}
		}
	}



	/**
	 * Add product post meta before insert.
	 *
	 * @param array    $product Single Kinguin product object from API response.
	 * @param string[] $metas   Product metas to insert.
	 *
	 * @return array
	 */
	private function set_product_meta( array $product, array $metas ) : array {
		$post = array();
		foreach ( $metas as $meta ) {
			if ( isset( $product[ $meta ] ) ) {
				$post['meta_input'][ '_' . $meta ] = $product[ $meta ];
			}
		}
		return $post;
	}



	/**
	 * Create product attributes for given taxonomies and prepare $post to insert.
	 *
	 * @param array    $product    Single Kinguin product object from API response.
	 * @param string[] $attributes Product attributes (taxonomies) to insert.
	 *
	 * @return array
	 */
	private function set_product_attributes( array $product, array $attributes ) : array {

		$post     = array();
		$position = 0;

		foreach ( $attributes as $attribute ) {
			if ( isset( $product[ $attribute ] ) ) {
				$ids = $this->get_terms_ids( $product[ $attribute ], 'pa_' . $attribute );
				if ( ! empty( $ids ) ) {
					$post['meta_input']['_product_attributes'][ 'pa_' . $attribute ] = array(
						'name'         => 'pa_' . $attribute,
						'value'        => '',
						'position'     => $position,
						'is_visible'   => 1,
						'is_variation' => 0,
						'is_taxonomy'  => 1,
					);

					$post['tax_input'][ 'pa_' . $attribute ] = $ids;
					$position ++;
				}
			}
		}
		return $post;
	}



	/**
	 * Get product tags ids
	 *
	 * @param string[] $names    Array of terms to look for and insert.
	 * @param string   $taxonomy Taxonomy name.
	 *
	 * @return array $ids Array of tags ids.
	 */
	private function get_terms_ids( $names, string $taxonomy ) : array {

		global $wpdb;

		$ids = array();

		if ( is_string( $names ) ) {
			$names = array( $names );
		}

		foreach ( $names as $name ) {
			$query = "SELECT $wpdb->terms.term_id
			    FROM $wpdb->terms, $wpdb->term_taxonomy 
		       WHERE $wpdb->terms.term_id = $wpdb->term_taxonomy.term_id 
			     AND $wpdb->terms.name = '%s' 
		         AND $wpdb->term_taxonomy.taxonomy = '%s' 
	    	ORDER BY $wpdb->terms.term_id ASC";

			$terms = $wpdb->get_results( $wpdb->prepare( $query, $name, $taxonomy ), ARRAY_N );

			if ( ! empty( reset( $terms ) ) ) {
				$ids[] = (int) reset( $terms )[0];
			} else {
				$term = wp_insert_term( $name, $taxonomy );
				if ( ! is_wp_error( $term ) ) {
					$ids[] = $term['term_id'];
				}
			}
		}

		return $ids;
	}


    /**
     * Get ID of main category for Kinguin product
     *
     * @param array $product Kinguin single product from API response.
     * @return int Product Category ID.
     */
    public function get_kinguin_main_category( array $product ) {

        // map main category based on platform
        $mapped_main_category = $this->define_main_category( $product );

        $id = term_exists( $mapped_main_category['slug'], 'product_cat' );
        if( $id == NULL )
        {
            $id = wp_insert_term(
                $mapped_main_category['term'],
                'product_cat',
                array(
                    'description'=> $mapped_main_category['term'],
                    'slug' => $mapped_main_category['slug']
                )
            );
            if ( ! is_wp_error( $id ) ) {
                return $id['term_id'];
            }

        } else {
            return $id['term_id'];
        }

    }


    /**
     * Map main category for product based on platform, tag
     *
     * @param array $product Kinguin single product from API response.
     * @return array Product Category slug and Category name.
     */
    public function define_main_category( $product )
    {
        if ( in_array( 'prepaid', $product['tags'] ) ) {
            return array( 'term' => 'Prepaid', 'slug' => 'prepaid' );

        } else if ( in_array('software', $product['tags'] ) ) {
            return array( 'term' => 'Software', 'slug' => 'software' );

        } else if ( array_key_exists( $product['platform'], $this->get_default_categories() ) ) {
            $slug = $this->get_default_categories()[$product['platform']];
            return array( 'term' => $product['platform'], 'slug' => $slug );

        } else if ( array_key_exists( $product['platform'], $this->get_pc_platforms() ) ) {
            return array( 'term' => 'PC', 'slug' => 'pc-platform' );

        } else {
            // it will be 'Others' category
            return array( 'term' => 'Others', 'slug' => 'others-platforms' );
        }
    }


    /**
     * Assign sub-categories 2 and 3 level
     *
     * @param array $product Kinguin single product from API response.
     * @param int  parent category ID.
     * @param int|string post ID
     * @return void
     */
    public function kinguin_set_subcategory( $product, $parent_cat_id, $post_id ) {

        // PC categories proccessing
        if ( array_key_exists( $product['platform'], $this->get_pc_platforms() )
            && !in_array( 'prepaid', $product['tags'] )
            && !in_array( 'software', $product['tags'] )
        ) {

            $id = term_exists( $product['platform'], 'product_cat', $parent_cat_id );
            if( $id == NULL )
            {

                $slug = $this->get_pc_platforms()[$product['platform']];

                $id = wp_insert_term(
                    $product['platform'],
                    'product_cat',
                    array(
                        'description'=> $product['platform'],
                        'slug' => $slug,
                        'parent'=> $parent_cat_id
                    )
                );
            }
            $res =  wp_set_object_terms( $post_id, (int) $id['term_id'], 'product_cat',true );

            if( ! empty( $product['genres'] ) && ! is_wp_error( $res ) ) {
                foreach( $product['genres'] as $key => $genre ) {

                    // WordPress does not allow child categories/terms to have same name even tough they have a different parent
                    $slug = strtolower( $product['platform'] ) . '-' . $this->get_genre_slug()[$genre];

                    $sub_id = term_exists( $slug, 'product_cat', (int) $id['term_id'] );
                    if( $sub_id == NULL )
                    {
                        $genre = $this->fix_genre_cat_name( $genre );

                        $sub_id = wp_insert_term(
                            $genre,
                            'product_cat',
                            array(
                                'description'=> $genre,
                                'slug' => $slug,
                                'parent'=> (int) $id['term_id']
                            )
                        );
                    }
                    wp_set_object_terms( $post_id, (int) $sub_id['term_id'], 'product_cat',true );
                }
            }

        } else if ( array_key_exists( $product['platform'], $this->get_default_categories() )
            && !in_array( 'prepaid', $product['tags'] )
            && !in_array( 'software', $product['tags'] )
        ) {
            // assign genre-subcategories for main categories except PC
            if( ! empty( $product['genres'] ) ) {
                foreach( $product['genres'] as $key => $genre ) {
                    // WordPress does not allow child categories/terms to have same name even tough they have a different parent
                    $slug = strtolower( $product['platform'] ) . '-' . $this->get_genre_slug()[$genre];
                    $id = term_exists( $slug, 'product_cat', $parent_cat_id );
                    if( $id == NULL )
                    {
                        $genre = $this->fix_genre_cat_name( $genre );

                        $id = wp_insert_term(
                            $genre,
                            'product_cat',
                            array(
                                'description'=> $genre,
                                'slug' => $slug,
                                'parent'=> $parent_cat_id
                            )
                        );
                    }
                    wp_set_object_terms( $post_id, (int) $id['term_id'], 'product_cat',true );
                }
            }
        }

    }

    /**
     * Return corrected Category name for frontend
     *
     * @param string  Genre name.
     * @return string  Category name.
     */
    public function fix_genre_cat_name( $genre ) {
        if( array_key_exists( $genre, $this->rename_genre_for_category() ) ) {
            return $this->rename_genre_for_category()[$genre];
        }
        return $genre;
    }


}
