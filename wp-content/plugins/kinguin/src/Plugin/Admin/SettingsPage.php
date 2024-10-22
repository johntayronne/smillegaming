<?php
/**
 * Admin settings page.
 *
 * @package WPDesk\ILKinguin
 */

namespace WPDesk\ILKinguin\Admin;

use ILKinguinVendor\WPDesk_Plugin_Info;
use WPDesk\ILKinguin\Admin\Exceptions\KinguinUnexpectedResponse;
use WPDesk\ILKinguin\Frontend\ProductView;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin class. The most important flow decisions are made here.
 *
 * @package WPDesk\ILKinguin
 */
class SettingsPage {
	use Configuration;

	/**
	 * Plugin object details
	 *
	 * @var WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	private $plugin_info;



	/**
	 * Set plugin info object
	 *
	 * @param WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function set_plugin_info( WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
	}



	/**
	 * Integrate with WordPress admin actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		add_action( 'admin_init', array( $this, 'register_plugin_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'kinguin_import_settings' ) );
    }


    /**
     * Enqueue styles and script for import filter tab.
     *
     * @return void
     */
    public function kinguin_import_settings() {
        $current_screen = get_current_screen();
        if ( is_a( $current_screen, 'WP_Screen' ) && 'import-products_page_kinguin-settings' === $current_screen->id ) {
            if( isset($_GET['page']) && 'kinguin-settings' === $_GET['page']
            ) {
                wp_enqueue_style('kinguin_import_settings', $this->plugin_info->get_plugin_url() . '/assets/css/kinguin-import-settings.css', array(), $this->plugin_info->get_version(), false);
                wp_enqueue_script('kinguin_import_settings-js', $this->plugin_info->get_plugin_url() . '/assets/js/kinguin-import-settings.js', array(), $this->plugin_info->get_version(), false);
            }
        }
    }



	/**
	 * Create instance of WP SettingsPage API
	 *
	 * @see https://developer.wordpress.org/plugins/settings/settings-api/
	 */
	public function register_plugin_settings() {

	    /* API section start */
        add_settings_section(
            'kinguin_api_settings_section',
            __( 'API Options', 'kinguin' ),
            array( $this, 'api_options_callback'),
            'kinguin_settings'
        );

        register_setting(
		    'kinguin_settings',
            'kinguin_api_key',
            array( $this, 'validate_api_key' )
        );

		add_settings_section( 'kinguin_settings', '', false, 'kinguin_settings' );

		add_settings_field(
			'kinguin_api_key',
			__( 'API Key', 'kinguin' ),
			function() {
				?>
				<input class="regular-text" type="text" name="kinguin_api_key" value="<?php echo esc_attr( $this->get_api_key() ); ?>">
				<p class="description">
					<?php esc_html_e( 'Provide Kinguin API key. You can obtain it from Your integration account.', 'kinguin' ); ?>
				</p>
				<ul>
					<li><?php esc_html_e( 'Dashboard', 'kinguin' ); ?> <a href="https://www.kinguin.net/integration/" target="_blank">https://www.kinguin.net/integration/</a></li>
                </ul>
				<?php
			},
			'kinguin_settings',
			'kinguin_api_settings_section'
		);

		register_setting( 'kinguin_settings', 'kinguin_environment' );
		add_settings_field(
			'kinguin_environment',
			__( 'API Environment', 'kinguin' ),
			function() {
				?>
				<select name="kinguin_environment" class="regular-text">
					<option value="production" <?php selected( 'production', $this->get_environment() ); ?>><?php esc_attr_e( 'Production', 'kinguin' ); ?></option>
					<option value="sandbox" <?php selected( 'sandbox', $this->get_environment() ); ?>><?php esc_attr_e( 'Sandbox', 'kinguin' ); ?></option>
				</select>
				<p class="description">
					<?php esc_html_e( 'Select sandbox if you want to test Kinguin integration.', 'kinguin' ); ?>
				</p>
				<?php
			},
			'kinguin_settings',
			'kinguin_api_settings_section'
		);

        register_setting( 'kinguin_settings', 'kinguin_products_webhook_secret' );
        add_settings_field(
            'kinguin_products_webhook_secret',
            __( 'Products Webhook', 'kinguin' ),
            function() {
                ?>
                <div style="margin-bottom: 5px;">
                    <input id="kinguin_products_webhook_url" class="regular-text" type="text" value="<?php echo esc_attr( get_rest_url( null, '/kinguin/v1/products/update' ) ) ?>" readonly>
                </div>
                <label for="kinguin_products_webhook_url">
                    <?php esc_html_e( 'Webhook Url', 'kinguin' ); ?>
                </label>
                <p class="description" style="margin-bottom: 15px;">
                    <?php esc_html_e( 'Copy Webhook Url into Product Update webhook url field within your Kinguin store configuration.', 'kinguin' ); ?>
                </p>
                <div style="margin-bottom: 5px;">
                    <input class="regular-text" id="kinguin_products_webhook_secret" name="kinguin_products_webhook_secret" type="text" value="<?php echo esc_attr( $this->get_products_webhook_secret() ); ?>">
                </div>
                <label for="kinguin_products_webhook_secret">
                    <?php esc_html_e( 'Secret', 'kinguin' ); ?>
                </label>
                <p class="description">
                    <?php esc_html_e( 'Copy Webhook Secret into Product Update secret field within your Kinguin store configuration.', 'kinguin' ); ?>
                </p>
                <?php
            },
            'kinguin_settings',
            'kinguin_api_settings_section'
        );

		register_setting( 'kinguin_settings', 'kinguin_orders_webhook_secret' );
		add_settings_field(
			'kinguin_orders_webhook_secret',
			__( 'Orders Webhooks', 'kinguin' ),
			function() {
				?>
                <div style="margin-bottom: 5px;">
                    <input id="kinguin_orders_webhook_url" class="regular-text" type="text" value="<?php echo esc_attr( get_rest_url( null, '/kinguin/v1/order/update' ) ) ?>" readonly style="margin-bottom: 5px;">
                </div>
                <label for="kinguin_orders_webhook_url">
					<?php esc_html_e( 'Webhook Url', 'kinguin' ); ?>
                </label>
                <p class="description" style="margin-bottom: 15px;">
	                <?php esc_html_e( 'Copy Webhook Url into both Order Complete and Order Status Change webhook url field within your Kinguin store configuration.', 'kinguin' ); ?>
                </p>
                <div style="margin-bottom: 5px;">
                    <input class="regular-text" id="kinguin_orders_webhook_secret" name="kinguin_orders_webhook_secret" type="text" value="<?php echo esc_attr( $this->get_orders_webhook_secret() ); ?>">
                </div>
                <label for="kinguin_orders_webhook_secret">
					<?php esc_html_e( 'Secret', 'kinguin' ); ?>
                </label>
                <p class="description">
					<?php esc_html_e( 'Copy Webhook Secret into both Order Complete and Order Status Change secret field within your Kinguin store configuration.', 'kinguin' ); ?>
                </p>
				<?php
			},
			'kinguin_settings',
			'kinguin_api_settings_section'
		);


        register_setting( 'kinguin_settings', 'kinguin_enable_webhook_import' );
        add_settings_field(
            'kinguin_enable_webhook_import',
            __( 'Enable Auto Import via Webhook', 'kinguin' ),
            function() {
                ?>
                <div class="kinguin-webhook-import-switch">
                    <label class="switch" for="kinguin_enable_webhook_import">
                        <input
                            type="checkbox"
                            id="kinguin_enable_webhook_import"
                            name="kinguin_enable_webhook_import"
                            value="1"
                            <?php echo checked( 1, $this->is_webhook_import_enabled(), false ) ?>
                        >
                        <span class="slider"></span>
                    </label>
                </div>
                <p class="description">
                    <?php esc_html_e( 'Enable this only if you want to make import in background in automatiс mode.', 'kinguin' ); ?>
                </p>
                <p class="description">
                    <?php esc_html_e( 'Product update webhook must be active in your Kinguin API dashboard.', 'kinguin' ); ?>
                </p><br>
                <?php if( get_option( 'kinguin_enable_webhook_import', false ) ) {
                    $cache_key_webhook_status = 'kinguin_webhook_is_working';
                    $kinguin_webhook_is_working = get_transient( $cache_key_webhook_status );
                    if( empty( $kinguin_webhook_is_working ) ) { ?>
                        <p class="description kinguin-alert">
                            <?php echo sprintf( '%s<a target="_blank" href="%s">%s</a>',
                                __( "You have enabled product updates via webhook, but it seems that webhook is not active. Please check if webhook option is also active in ", "kinguin" ),
                                esc_url( "https://kinguin.net/integration/" ),
                                __( "Kinguin dashboard", "kinguin" )
                            ); ?>
                        </p><br>
                    <?php
                    }
                };?>
                <div class="description <?php echo empty( $kinguin_webhook_is_working ) ? "kinguin-alert" : "";?>">
                    <p class="description">
                        <b>
                            <?php esc_html_e( 'Please note:', 'kinguin' ); ?>
                        </b>
                        <?php esc_html_e( 'Updating products via a webhook creates a significant load on the hosting (server).', 'kinguin' ); ?>
                    </p>
                    <p class="description">
                        <?php esc_html_e( 'Webhook in the Kinguin dashboard may be deactivated automatically, because we have a mechanism that disables the webhook and deactivates it when there are many requests to the client\'s website and there is an error (there is no status 200).', 'kinguin' ); ?>
                    </p>
                </div>
                <?php
            },
            'kinguin_settings',
            'kinguin_api_settings_section'
        );

        register_setting( 'kinguin_settings', 'kinguin_import_only_existing' );
        add_settings_field(
            'kinguin_import_only_existing',
            __( '', 'kinguin' ),
            function() {
                ?>
                <div class="kinguin-webhook-additional-settings">
                    <label for="kinguin_import_only_existing">
                        <input
                                type="checkbox"
                                id="kinguin_import_only_existing"
                                name="kinguin_import_only_existing"
                                value="1"
                            <?php echo checked( 1, get_option( 'kinguin_import_only_existing', false ), true ) ?>
                        >
                        <?php esc_html_e( 'Update via webhook only existing products', 'kinguin' ); ?>
                    </label>
                </div>
                <?php
            },
            'kinguin_settings',
            'kinguin_api_settings_section'
        );

        register_setting( 'kinguin_settings', 'kinguin_webhook_log' );
        add_settings_field(
            'kinguin_webhook_log',
            __( '', 'kinguin' ),
            function() {
                ?>
                <div class="kinguin-webhook-additional-settings">
                    <label for="kinguin_webhook_log">
                        <input
                                type="checkbox"
                                id="kinguin_webhook_log"
                                name="kinguin_webhook_log"
                                value="1"
                            <?php echo checked( 1, get_option( 'kinguin_webhook_log', false ), true ) ?>
                        >
                        <?php echo sprintf( '%s <a target="_blank" href="%s">kinguin-webhook.log</a> %s (%s)',
                            __( "Register webhook activity into ", "kinguin" ),
                            get_home_url() . '/wp-admin/admin.php?page=wc-status&tab=logs',
                            __( "file", "kinguin" ),
                            __( "WooCommerce > Status > Logs", "kinguin" )
                        ); ?>
                    </label>
                </div>
                <?php
            },
            'kinguin_settings',
            'kinguin_api_settings_section'
        );
        /* API section end */






        /* Sales section start */
        add_settings_section(
            'kinguin_sales_settings_section',
            __( 'Sales Options', 'kinguin' ),
            array( $this, 'sales_options_callback'),
            'kinguin_sales_settings'
        );

        register_setting( 'kinguin_settings_sales', 'kinguin_product_margin' );
        add_settings_field(
            'kinguin_product_margin',
            __( 'Choose margin type', 'kinguin' ),
            array( $this, 'kinguin_product_margin_callback'),
            'kinguin_sales_settings',
            'kinguin_sales_settings_section'
        );


        register_setting( 'kinguin_settings_sales', 'kinguin_product_margin_val' );
        add_settings_field(
            'kinguin_product_margin_val',
            __( 'Set margin value', 'kinguin' ),
            function() {
                ?>
                <div style="margin-bottom: 5px;">
                    <input class="regular-text kinguin-margin-qty" id="kinguin_product_margin_val" name="kinguin_product_margin_val" type="number" step="0.01" value="<?php echo esc_attr( $this->get_margin_value() ); ?>">
                </div>
                <p class="description">
                    <?php esc_html_e( 'Value can be positive or negative', 'kinguin' ); ?>
                </p>
                <?php
            },
            'kinguin_sales_settings',
            'kinguin_sales_settings_section'
        );


        register_setting( 'kinguin_settings_sales', 'kinguin_discount_code' );
        add_settings_field(
            'kinguin_discount_code',
            __( 'Have discount code from manager?', 'kinguin' ),
            function() {
                ?>
                <div style="margin-bottom: 5px;">
                    <input class="regular-text" id="kinguin_discount_code" name="kinguin_discount_code" type="text" value="<?php echo esc_attr( $this->get_discount_code() ); ?>">
                </div>
                <p class="description">
                    <?php esc_html_e( 'You can ask your business manager for additional discount', 'kinguin' ); ?>
                </p>
                <?php
            },
            'kinguin_sales_settings',
            'kinguin_sales_settings_section'
        );


		register_setting( 'kinguin_settings_sales', 'kinguin_email_message' );
		add_settings_field(
			'kinguin_email_message',
			__( 'Email message', 'kinguin' ),
			function() {
				wp_editor(
				    $this->get_email_message(),
                    'kinguin_email_message',
                    array(
                        'media_buttons'    => false,
	                    'drag_drop_upload' => false,
	                    'textarea_rows'    => 10,
	                    'teeny'            => true
                    )
                );
				?>
                <p class="description">
					<?php esc_html_e( 'Message send to customers with games keys.', 'kinguin' ); ?>
                </p>
				<?php
			},
			'kinguin_sales_settings',
			'kinguin_sales_settings_section'
		);
        /* Sales section end */


        /* Product view section start */
        add_settings_section(
            'kinguin_product_view_settings_section',
            __( 'Product View Options', 'kinguin' ),
            array( $this, 'product_view_settings_callback'),
            'kinguin_product_view_settings'
        );

        register_setting( 'kinguin_product_view_settings', 'kinguin_product_view' );
        add_settings_field(
            'kinguin_product_view',
            __( 'Use specific design for Product pages', 'kinguin' ),
            function() {
                ?>
                <div class="kinguin-product_view">
                    <label for="kinguin_product_view">
                        <input
                                type="checkbox"
                                id="kinguin_product_view"
                                name="kinguin_product_view"
                                value="1"
                            <?php echo checked( 1, get_option( 'kinguin_product_view', true ), true ) ?>
                        >
                        <?php echo esc_html_e('', 'kinguin') ?>
                    </label>
                </div>
                <?php
            },
            'kinguin_product_view_settings',
            'kinguin_product_view_settings_section'
        );
        /* Product view section end */


        /* Import section start */
        add_settings_section(
            'kinguin_import_settings_section',
            __( 'Import Options', 'kinguin' ),
            array( $this, 'import_options_callback'),
            'kinguin_import_settings'
        );


        register_setting( 'kinguin_settings_import', 'kinguin_settings_import', array( $this, 'kinguin_delete_cache') );
        add_settings_field(
            'kinguin_settings_import',
            __( 'Filter settings', 'kinguin' ),
            array( $this, 'kinguin_settings_import_callback'),
            'kinguin_import_settings',
            'kinguin_import_settings_section'
        );

	}

    public function import_options_callback() {
        echo '<style>
                th[scope="row"] {
                    display: none;
                } 
              </style>';

        echo '
                <div class="kinguin-import-filter-meta-box-wrap -grid">
                <p>' . esc_html_e( 'Set up filter. Or reset settings (don\'t forget Save button at the end).', 'kinguin' ) . '</p>                        
                </div>
                
                <div class="kinguin-import-filter-meta-box-wrap -grid">
                <p class="kinguin-reset-filter">
                    <input type="button" name="kinguin-reset-filter" id="kinguin-reset-filter" class="button button-kinguin-reset-filter" value="Reset filter settings">
                </p>
                </div>
            ';
    }


    public function product_view_settings_callback() {
        echo '<p>' . esc_html_e( '', 'kinguin' ) . '</p>';
    }

    public function sales_options_callback() {
        echo '<p>' . esc_html_e( 'Set up additional sale settings:', 'kinguin' ) . '</p>';
    }


    public function api_options_callback() {

        echo '<p>' . esc_html_e( 'Set up API settings:', 'kinguin' ) . '</p>';
    }


	/**
	 * Validate user settings.
	 *
	 * @param array $input .
	 *
	 * @return array $valid Array of settings stored within wp_options.
	 */
	public function validate_api_key( $input ) {

        $connection = ( new KinguinAPI() )->check_connection( $input );
        if ( $connection ) {
            add_settings_error(
                'kinguin_settings',
                esc_attr( 'settings_updated' ),
                __( 'Successfully connected with Kinguin API.', 'kinguin' ),
                'success'
            );
        } else {
            add_settings_error(
                'kinguin_settings',
                esc_attr( 'settings_updated' ),
                __( 'Connection unsuccessful with Kinguin API. Please check Your API Key and selected API Environment.', 'kinguin' ),
                'error'
            );
        }
		update_option( 'kinguin_connection_status', $connection );

		return $input;
	}



	/**
	 * Render settings page from template
	 *
	 * @return void
	 */
	public function render_settings_page() {
		include_once $this->plugin_info->get_plugin_dir() . '/src/Plugin/Admin/templates/settings_template.php';
	}


    /**
     * We need check if files exist form previous interrupted import
     * and delete its if filter setting was re-saved
     *
     */
	public function kinguin_delete_cache( $input ) {

        if ( is_dir( $this->get_cache_dir() ) ) {
            $files = scandir( $this->get_cache_dir(), SCANDIR_SORT_ASCENDING );
            $files = preg_grep( '/^.*\.(json)$/i', $files );

            if( !empty($files) ) {
                foreach ($files as $file) {
                    wp_delete_file( $this->get_cache_dir() . $file );
                }
            }
        }
        return $input;
    }



    /**
     * Render settings block for margin
     *
     */
    public function kinguin_product_margin_callback() {

        $options = get_option( 'kinguin_product_margin' );

        if( isset( $options['kinguin_margin_type'] ) ) { ?>

            <input type="radio" id="kinguin_product_margin_fix" name="kinguin_product_margin[kinguin_margin_type]"
                   value="1" <?php echo ' ' . checked(1, $options['kinguin_margin_type'], false); ?> />

            <label for="kinguin_product_margin_fix">
                <?php echo esc_html_e('Flat fee', 'kinguin') ?>
                (<?php echo esc_html(get_woocommerce_currency_symbol()) ?>)
            </label>

            <input type="radio" id="kinguin_product_margin_percent" name="kinguin_product_margin[kinguin_margin_type]"
                   value="2" <?php echo ' ' .  checked(2, $options['kinguin_margin_type'], false); ?> />

            <label for="kinguin_product_margin_percent">
                <?php echo esc_html_e('Percent', 'kinguin') ?>
            </label>

        <?php } else { ?>

            <input type="radio" id="kinguin_product_margin_fix" name="kinguin_product_margin[kinguin_margin_type]"
                   value="1" checked />

            <label for="kinguin_product_margin_fix">
                <?php echo esc_html_e('Flat fee', 'kinguin') ?>
                (<?php echo esc_html(get_woocommerce_currency_symbol()) ?>)
            </label>

            <input type="radio" id="kinguin_product_margin_percent" name="kinguin_product_margin[kinguin_margin_type]"
                   value="2" />

            <label for="kinguin_product_margin_percent">
                <?php echo esc_html_e('Percent', 'kinguin') ?>
            </label>

        <?php }
    }


    /**
     * Render filter settings block
     *
     */
    public function kinguin_settings_import_callback() {
        $options = get_option( 'kinguin_settings_import' );
        $regions = $this->get_regions();
        $genres = $this->get_genres();
        $tags = $this->get_filter_tags();
        $languages = $this->get_filter_languages();
        $platforms = $this->get_platforms();
        $merchants = $this->get_merchants();
        $index = 1;
        ?>

        <div class="kinguin-import-filter-meta-box-wrap">
            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Kinguin ID', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by KinguinID', 'kinguin') ?>
                    </p>
                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-fields-wrap" style="margin-bottom: 5px;">
                            <input class="kinguin-import-filter-input kinguin-long-input" id="kinguin_filter_kinguin_id" name="kinguin_settings_import[kinguinId]" type="text" value="<?php echo isset( $options['kinguinId'] ) ? esc_attr( $options['kinguinId']) : ''; ?>">
                        </div>
                        <p class="description">
                            <?php esc_html_e( 'Comma separated list of product ID', 'kinguin' ); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

	    <div class="kinguin-import-filter-meta-box-wrap -grid">

            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Name', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by name (minimum 3 characters)', 'kinguin') ?>
                        </p>
                    <div class="kinguin-filter-fields">
                        <div style="margin-bottom: 5px;">
                            <input class="kinguin-import-filter-input kinguin-name-input" id="kinguin_filter_name" name="kinguin_settings_import[name]" type="text" value="<?php echo isset( $options['name'] ) ? esc_attr( $options['name'] ) : ''; ?>">
                        </div>
                        <p class="description">
                            <?//php esc_html_e( 'Option description', 'kinguin' ); ?>
                        </p>
                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Price', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by price', 'kinguin') ?>
                    </p>
                    <div class="kinguin-filter-fields">
                        <div style="margin-bottom: 5px;">
                            <label for="kinguin_settings_import[name]">
                                <?php esc_html_e( 'Price from:', 'kinguin' ); ?>
                            </label>
                            <input class="kinguin-import-filter-input kinguin-price-range-qty" id="kinguin_filter_price_from" name="kinguin_settings_import[priceFrom]" type="text" value="<?php echo isset($options['priceFrom']) ? esc_attr( $options['priceFrom'] ) : ''; ?>">

                            <label for="kinguin_settings_import[name]">
                                <?php esc_html_e( 'Price to:', 'kinguin' ); ?>
                            </label>
                            <input class="kinguin-import-filter-input kinguin-price-range-qty" id="kinguin_filter_price_to" name="kinguin_settings_import[priceTo]" type="text" value="<?php echo isset($options['priceTo']) ? esc_attr( $options['priceTo'] ) : ''; ?>">

                        </div>
                        <p class="description">
                            <?//php esc_html_e( 'Option description', 'kinguin' ); ?>
                        </p>
                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e( 'Pre-Order', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by pre-order status', 'kinguin') ?>
                        ( <a href="#" id="kinguin-reset-preorder">
                            <?php esc_html_e('Reset', 'kinguin') ?>
                        </a> )
                    </p>

                    <div class="kinguin-filter-fields">
                        <div style="margin-bottom: 5px;">

                            <input class="kinguin-import-filter-input" type="radio" id="kinguin_filter_preorder-yes" name="kinguin_settings_import[isPreorder]" value="yes" <?php echo ' ' . checked( 'yes', isset($options['isPreorder']) ? $options['isPreorder'] : '', false ); ?> />
                            <label for="kinguin_filter_preorder-yes">
                                <?php esc_html_e('Yes', 'kinguin') ?>
                            </label>
                            <input class="kinguin-import-filter-input" type="radio" id="kinguin_filter_preorder-no" name="kinguin_settings_import[isPreorder]" value="no" <?php echo ' ' . checked( 'no', isset($options['isPreorder']) ? $options['isPreorder'] : '', false ); ?> />
                            <label for="kinguin_filter_preorder-no">
                                <?php esc_html_e('No', 'kinguin') ?>
                            </label>
                        </div>

                        <p class="description">
                            <?php esc_html_e( 'The pre-order keys are delivered on the day of the game release or earlier, provided the merchant of your choice uploads the keys sooner', 'kinguin' ); ?>
                        </p>

                        <div class="hidden-activepreorder-option" style="display:none">
                            <input type="checkbox"
                                   class="hidden-activepreorder-option kinguin-import-filter-input"
                                   id="kinguin_filter_active_preorder"
                                   name="kinguin_settings_import[activePreorder]"
                                   value="yes"
                                <?php echo checked( 'yes', isset( $options['activePreorder'] ) ? $options['activePreorder'] : 0, false ) ?>
                            />
                            <label for="kinguin_filter_active_preorder">
                                <?php esc_html_e('Only active PRE-ORDER', 'kinguin') ?>
                            </label>
                        </div>

                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Languages', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by language', 'kinguin') ?>
                        ( <a href="#" id="kinguin-reset-language">
                            <?php esc_html_e('Reset', 'kinguin') ?>
                        </a> )

                    </p>
                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-field-checkbox" data-name="" data-type="checkbox">
                            <div class="kinguin-filter-input">
                                <ul class="kinguin-filter-checkbox-list kinguin-c-list">
                                    <?php foreach ($languages as $key=>$value) {
                                        echo '
                                        <li class="kinguin-li-list">                                            
                                            <input 
                                                type="radio"
                                                class="kinguin-import-filter-input" 
                                                id="kinguin_filter_' . esc_attr( $index ) . '" 
                                                name="kinguin_settings_import[languages]" 
                                                value="' . esc_attr( $value ) . '" 
                                                ' .  checked( esc_attr( $value ) , isset( $options['languages'] ) ? $options['languages'] : '', false ) . '
                                            >
                                            <label class="kinguin_filter_label" for="kinguin_filter_' . esc_attr( $index ) . '">
                                            ' . esc_html( $value ) . '
                                            </label>
                                          
                                        </li>';
                                        $index++;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Regions', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by region', 'kinguin') ?>
                        ( <a href="#" id="kinguin-reset-region">
                            <?php esc_html_e('Reset', 'kinguin') ?>
                        </a> )
                    </p>
                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-field-checkbox" data-name="" data-type="checkbox">
                            <div class="kinguin-filter-input">
                                <ul class="kinguin-filter-checkbox-list kinguin-c-list">
                                    <?php foreach ($regions as $key=>$value) {
                                        echo '
                                        <li class="kinguin-li-list">                                            
                                            <input 
                                                type="radio" 
                                                class="kinguin-import-filter-input"
                                                id="kinguin_filter_' . esc_attr( $index ) . '" 
                                                name="kinguin_settings_import[regionId]" 
                                                value="' . esc_attr( $key ) . '" 
                                                ' . checked( esc_attr( $key ), isset( $options['regionId'] ) ? $options['regionId'] : 0, false ) . '
                                            >
                                            <label class="kinguin_filter_label" for="kinguin_filter_' . esc_attr( $index ) . '">
                                                ' . esc_html( $value ) . '
                                            </label>
                                        </li>';
                                        $index++;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Tags', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by tags', 'kinguin') ?>
                    </p>
                    <div class="kinguin_check_all">
                        <input id="choose_all_tags" type="checkbox" name="selectAll" class="kinguin-checkbox-toggle">
                            <label for="choose_all_tags">
                            <?php esc_html_e('Select all / Unselect all', 'kinguin') ?>
                        </label>
                    </div>


                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-field-checkbox" data-name="" data-type="checkbox">
                            <div class="kinguin-filter-input">
                                <ul class="kinguin-filter-checkbox-list kinguin-c-list">
                                    <?php foreach ($tags as $key=>$value) {
                                        echo '
                                        <li class="kinguin-li-list">                                            
                                            <input 
                                                type="checkbox" 
                                                class="kinguin-import-filter-input kinguin-tags"
                                                id="kinguin_filter_' . esc_attr( $index ) . '" 
                                                name="kinguin_settings_import[tags][' . esc_attr( $key ) . ']" 
                                                value="' . esc_attr( $value ) . '" 
                                                ' . checked( esc_attr( $value ), isset( $options['tags'][$key] ) ? $options['tags'][$key] : '', false ) . '
                                            >
                                            <label class="kinguin_filter_label" for="kinguin_filter_' . esc_attr( $index ) . '">
                                            ' . esc_html( $value ) . '
                                            </label>
                                        </li>';
                                        $index++;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>





            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Genres', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by genre', 'kinguin') ?>
                    </p>
                    <div class="kinguin_check_all">
                        <input id="choose_all_genres" type="checkbox" name="selectAll" class="kinguin-checkbox-toggle">
                        <label for="choose_all_genres">
                               <?php esc_html_e('Select all / Unselect all', 'kinguin') ?>
                        </label>
                    </div>


                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-field-checkbox" data-name="" data-type="checkbox">
                            <div class="kinguin-filter-input">
                                <ul class="kinguin-filter-checkbox-list kinguin-c-list">
                                    <?php foreach ($genres as $key=>$value) {
                                        echo '
                                        <li class="kinguin-li-list">
                                            <input 
                                                type="checkbox" 
                                                id="kinguin_filter_' . esc_attr( $index ) . '"
                                                class="kinguin-import-filter-input kinguin-genre"  
                                                name="kinguin_settings_import[genre][' . esc_attr( $key ) . ']" 
                                                value="' . esc_attr( $value ) . '" 
                                                ' . checked( esc_attr( $value ), isset( $options['genre'][$key] ) ? $options['genre'][$key] : 0, false ) . '
                                            >
                                            <label class="kinguin_filter_label" for="kinguin_filter_' . esc_attr( $index ) . '">                                                
                                                ' . esc_html( $value ) . '
                                            </label>
                                        </li>';
                                        $index++;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Platforms', 'kinguin') ?>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <?php esc_html_e('Filter by platforms', 'kinguin') ?>
                    </p>
                    <div class="kinguin_check_all">
                        <input id="choose_all_platforms" type="checkbox" name="selectAll" class="kinguin-checkbox-toggle">
                        <label for="choose_all_platforms">
                            <?php esc_html_e('Select all / Unselect all', 'kinguin') ?>
                        </label>
                    </div>


                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-field-checkbox" data-name="" data-type="checkbox">
                            <div class="kinguin-filter-input">
                                <ul class="kinguin-filter-checkbox-list kinguin-c-list">
                                    <?php foreach ($platforms as $key=>$value) {
                                        echo '
                                        <li class="kinguin-li-list">                                            
                                            <input 
                                                type="checkbox" 
                                                class="kinguin-import-filter-input kinguin-platform"
                                                id="kinguin_filter_' . esc_attr( $index ) . '" 
                                                name="kinguin_settings_import[platform][' . esc_attr( $key ) . ']" 
                                                value="' . esc_attr( $value ) . '" 
                                                ' . checked( esc_attr( $value ), isset( $options['platform'][$key] ) ? $options['platform'][$key] : '', false ) . '
                                            >
                                            <label class="kinguin_filter_label" for="kinguin_filter_' . esc_attr( $index ) . '">
                                            ' . esc_html( $value ) . '
                                            </label>
                                        </li>';
                                        $index++;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            <div id="" class="kinguin_postbox postbox">
                <div class="postbox-header">
                    <h2 class="kinguin-hndle">
                        <?php esc_html_e('Merchants', 'kinguin') ?><b> *</b>
                    </h2>
                    <div class="kinguin-handle-actions hide-if-no-js">
                        <button type="button" class="handlediv kinguin_handlediv" aria-expanded="false">
                            <span class="toggle-indicator" aria-hidden="true"></span>
                        </button>
                    </div>
                </div>
                <div class="inside">
                    <p class="kinguin-filter-box-text">
                        <b>*</b> - <?php esc_html_e('option availability might depend on your permission within Kinguin API', 'kinguin') ?>
                        ( <a href="#" id="kinguin-reset-merchant">
                            <?php esc_html_e('Reset', 'kinguin') ?>
                        </a> )
                    </p>
                    <div class="kinguin-filter-fields">
                        <div class="kinguin-filter-field-checkbox" data-name="" data-type="checkbox">
                            <div class="kinguin-filter-input">
                                <ul class="kinguin-filter-checkbox-list kinguin-c-list">
                                    <?php foreach ($merchants as $key=>$value) {
                                        echo '
                                        <li class="kinguin-li-list">                                            
                                            <input 
                                                type="radio" 
                                                class="kinguin-import-filter-input"
                                                id="kinguin_filter_' . esc_attr( $index ) . '" 
                                                name="kinguin_settings_import[merchantName]" 
                                                value="' . esc_attr( $value ) . '" 
                                                ' .  checked( esc_attr( $value ), isset( $options['merchantName'] ) ? $options['merchantName'] : 0, false ) . '
                                            >
                                            <label class="kinguin_filter_label" for="kinguin_filter_' . esc_attr( $index ) . '">
                                            ' . esc_html( $value ) . '
                                            </label>
                                          
                                        </li>';
                                        $index++;
                                    } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


        </div>
    <?php }

}
