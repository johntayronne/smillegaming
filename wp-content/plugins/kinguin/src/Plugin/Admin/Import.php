<?php
/**
 * Products import from Kinguin API class
 *
 * @package WPDesk\ILKinguin
 */
namespace WPDesk\ILKinguin\Admin;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinCacheDirError;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinUnexpectedResponse;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinImportPageIncorrectDataType;
use WPDesk\ILKinguin\Admin\Product\InsertUpdate;

defined( 'ABSPATH' ) || exit;

class Import {
	use Configuration;

	/**
	 * Plugin object details
	 *
	 * @var WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	private $plugin_info;

	/**
	 * Number of items per page
	 * Max accepted by Kinguin API is 100
	 *
	 * @var int $limit .
	 */
	public $limit;

	/**
	 * Not enough memory error.
	 *
	 * @var bool $low_memory_limit_error
	 */
	public $low_memory_limit_error;


	/**
	 * Set plugin info object
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function set_plugin_info( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info            = $plugin_info;
		$this->limit                  = 1;
		$this->low_memory_limit_error = false;
		$this->set_limit();
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_enqueue_scripts', array( $this, 'products_import_assets' ) );
		add_action( 'wp_ajax_set_cache', array( $this, 'set_cache' ) );
		add_action( 'wp_ajax_import_products_to_cache', array( $this, 'import_products_to_cache' ) );
		add_action( 'wp_ajax_import_products_to_woocommerce', array( $this, 'import_products_to_woocommerce' ) );
	}



	/**
	 * Admin import assets
	 *
	 * @return void
	 */
	public function products_import_assets() {
		$current_screen = get_current_screen();
		if ( is_a( $current_screen, 'WP_Screen' ) && 'toplevel_page_kinguin-import' === $current_screen->id ) {
			wp_enqueue_style(
				'kinguin-admin-import',
				$this->plugin_info->get_plugin_url() . '/assets/css/kinguin-admin-import.css'
			);
			wp_enqueue_script(
				'kinguin-products-import',
				$this->plugin_info->get_plugin_url() . '/assets/js/kinguin-products-import.js',
				array(),
                bin2hex( random_bytes( 16 ) ), //$this->plugin_info->get_version(),
				true
			);
			wp_localize_script(
				'kinguin-products-import',
				'kinguin',
				array(
					'ajax_url'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce'  => wp_create_nonce( 'gZ57^Am7!6' ),
					'limit'       => $this->limit,
					'page'        => $this->get_last_imported_page(),
					'total'       => $this->get_last_imported_page(),
					'cacheDir'    => $this->get_cache_dir(),
					'cachedFiles' => $this->get_cached_files(),
					'notice'      => __('Filter settings have been changed - please update this page (or close / re-open)', 'kinguin'),
				)
			);
		}
	}



	/**
	 * Get server memory limit.
	 *
	 * @return string Memory limit.
	 */
	public function get_memory_limit() : string {
		return ini_get('memory_limit');
	}



	/**
	 * Check server memory limit and set number of imported products at once (per page).
	 */
    public function set_limit() {
        $memory_limit = $this->get_memory_limit();

        if ( $memory_limit ) {

            $memory_limit = $this->return_bytes($memory_limit);

            if ( is_numeric( $memory_limit ) ) {
                // between 256 Mb and 512 Mb
                if ( $memory_limit >= 268435456 && $memory_limit < 536870912 ) {
                    //$this->limit = 25;
                    $this->limit = 1;
                }
                // between 160 Mb and 256 Mb
                if ( $memory_limit >= 167772160 && $memory_limit < 268435456 ) {
                    //$this->limit = 20;
                    $this->limit = 1;
                }
                // lower than 160 Mb
                if ( $memory_limit < 167772160 ) {
                    $this->low_memory_limit_error = true;
                }
            }

        } else {
            // try to import slowly
            $this->limit = 15;
        }
    }



	/**
	 * AJAX action creates cache directory.
	 */
	public function set_cache() {

		check_ajax_referer( 'gZ57^Am7!6', 'nonce' );

		try {
			$this->is_cache_dir();
			wp_send_json_success();
		} catch ( KinguinCacheDirError $e ) {
			wp_send_json_error( $e->getMessage() );
		}
	}



	/**
	 * Checks whenever cache dir exists, if not it creates it
	 *
	 * @return bool true for directory created or if already exists
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinCacheDirError Error while creating cache dir.
	 */
	private function is_cache_dir() : bool {
		$cache_dir = $this->get_cache_dir();
		if ( ! is_dir( $cache_dir ) ) {
			if ( false === mkdir( $cache_dir, 0755, false ) ) {
				throw new KinguinCacheDirError();
			}
		}
		return true;
	}



	/**
	 * Get list of files stored in the cache dir
	 *
	 * @return array
	 */
	private function get_cached_files() : array {
		$files = array();
		if ( is_dir( $this->get_cache_dir() ) ) {
			$files = scandir( $this->get_cache_dir(), SCANDIR_SORT_ASCENDING );
			$files = preg_grep( '/^.*\.(json)$/i', $files );
			sort( $files, SORT_NATURAL );
		}
		return $files;
	}



	/**
	 * Count existing files in the cache dir.
	 *
	 * @return int
	 */
	private function count_cached_files() : int {
		return count( $this->get_cached_files() );
	}



	/**
	 * Get last imported page.
	 *
	 * @return int
	 */
	private function get_last_imported_page() : int {
		$files = $this->get_cached_files();
		if ( $files ) {
			return $this->extract_page_from_file( end( $files ) );
		} else {
			return 1;
		}
	}



	/**
	 * Extract page number from given file name
	 *
	 * @param string $file Filename.
	 *
	 * @return int Page number.
	 */
	private function extract_page_from_file( string $file ) : int {
		return (int) preg_replace( '/[^0-9]/', '', $file );
	}



	/**
	 * AJAX action creates single cache file.
	 */
	public function import_products_to_cache() {

		check_ajax_referer( 'gZ57^Am7!6', 'nonce' );

		if ( isset( $_POST ) ) {
            $post_sanitized = filter_var_array($_POST, FILTER_SANITIZE_STRING);
			$param = array_map( 'esc_attr', $post_sanitized );
		} else {
			wp_send_json_error( __( 'Access denied.', 'kinguin' ) );
		}

		try {
			if ( is_numeric( $param['page'] ) ) {
				$progress = $this->create_cache( (int) $param['page'] );
				wp_send_json_success( $progress );
			} else {
				throw new KinguinImportPageIncorrectDataType();
			}
		} catch ( \Exception $error ) {
			wp_send_json_error( $error->getMessage() );
		}
	}



	/**
	 * Create single cache file per API page.
	 *
	 * @param int $page      API page to import
	 *
	 * @return array         Progress status in percent (max is 50 percent, becose of 2 step import).
	 *
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinMissingApiKeyException Exception for no api key.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinNoConnectionException  Exception for broken connection.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinStatusCodeException    Exception for respond with other status code than 200.
	 * @throws \WPDesk\ILKinguin\Admin\Exceptions\KinguinUnexpectedResponse     Exception for response without results (products to import).
	 */
    private function create_cache( int $page ) : array {
        try {
            $import_products = new KinguinAPI();
            $filter_param = $this->get_filter_preset();
            $products        = $import_products->get( $this->get_api_url() . '/v1/products?page=' . $page . '&limit=' . $this->limit . $filter_param);

            if ( property_exists( $products, 'results' ) ) {
                $file  = $this->get_cache_dir()  . 'kinguin-' . $page . '.json';
                $cache = fopen( $file, 'w' );
                fwrite( $cache, json_encode( $products->results ) );
                fclose( $cache );
                chmod( $file, 0644 );
                return array(
                    'page'     => $page,
                    'file'     => 'kinguin-' . $page . '.json',
                    'of'       => ceil( $products->item_count / $this->limit ),
                    'total'    => $products->item_count,
                    'limit'    => $this->limit
                );
            } else {
                throw new KinguinUnexpectedResponse();
            }
        } catch ( \Exception $error ) {
            throw $error;
        }
    }



	/**
	 * AJAX import products from json file to WooCommerce
	 */
	public function import_products_to_woocommerce() {

		check_ajax_referer( 'gZ57^Am7!6', 'nonce' );

		if ( isset( $_POST ) ) {
            $post_sanitized = filter_var_array($_POST, FILTER_SANITIZE_STRING);
			$param = array_map( 'esc_attr', $post_sanitized );
		} else {
			wp_send_json_error( __( 'Access denied.', 'kinguin' ) );
		}

		// Page number
		$page = $this->extract_page_from_file( $param['file'] );

		// Read json file
		$request  = wp_safe_remote_get( $this->get_cache_url() . $param['file'] );
		if ( 200 === wp_remote_retrieve_response_code( $request ) ) {
			$products = json_decode( wp_remote_retrieve_body( $request ), true );
			if ( is_array( $products ) ) {
                wp_delete_file( $this->get_cache_dir() . $param['file'] );
				$import = new InsertUpdate();
				$import->set_currency_rate();

				delete_transient('existing_kinguin_ids');

				// manual import
                foreach ($products as $key => $product) {
                    $import->manage($product);
                }
			}
		}

		$result = array(
			'page' => $page
		);

		wp_send_json_success( $result );

	}



	/**
	 * Render settings page from template
	 *
	 * @return void
	 */
	public function render_import_page() {
		$memory_limit_error = $this->low_memory_limit_error;
		$connection_status  = $this->get_connection_status();
		include_once $this->plugin_info->get_plugin_dir() . '/src/Plugin/Admin/templates/import_template.php';
	}



    /**
     * Convert memory limit value to bytes
     *
     * @return void
     */
    public function return_bytes($size_str)
    {
        $val = trim($size_str);
        $last_symbol = strtolower($val[strlen($val)-1]);
        switch ($last_symbol)
        {
            case 'm': return (int)$val * 1048576;
            case 'k': return (int)$val * 1024;
            case 'g': return (int)$val * 1073741824;
            default: return $val;
        }
    }

}
