<?php
/**
 * Administrator settings page.
 *
 * @package WPDesk\ILKinguin
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
	</h1>

    <?php settings_errors(); ?>

    <?php
    $active_tab = '';
    if( isset( $_GET[ 'tab' ] ) ) {
        $active_tab = $_GET[ 'tab' ];
    } else if( $active_tab == 'import_options' ) {
        $active_tab = 'import_options';
    } else if( $active_tab == 'sales' ) {
        $active_tab = 'sales';
    } else {
        $active_tab = 'api_options';
    } ?>

    <h2 class="nav-tab-wrapper">
        <a href="?page=kinguin-settings&tab=api_options" class="nav-tab <?php echo $active_tab == 'api_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'API Options', 'kinguin' ); ?></a>
        <a href="?page=kinguin-settings&tab=import_options" class="nav-tab <?php echo $active_tab == 'import_options' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Import Options', 'kinguin' ); ?></a>
        <a href="?page=kinguin-settings&tab=sales" class="nav-tab <?php echo $active_tab == 'sales' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Sales Options', 'kinguin' ); ?></a>
        <a href="?page=kinguin-settings&tab=product_view" class="nav-tab <?php echo $active_tab == 'product_view' ? 'nav-tab-active' : ''; ?>"><?php _e( 'Product View Options', 'kinguin' ); ?></a>
    </h2>

	<form action="options.php" method="post">

        <?php

				if( $active_tab == 'api_options' ) {

					settings_fields( 'kinguin_settings' );
					do_settings_sections( 'kinguin_settings' );

				} elseif( $active_tab == 'import_options' ) {

					settings_fields( 'kinguin_settings_import' );
					do_settings_sections( 'kinguin_import_settings' );

				} elseif( $active_tab == 'product_view' ) {

                    settings_fields( 'kinguin_product_view_settings' );
                    do_settings_sections( 'kinguin_product_view_settings' );

                } else {

					settings_fields( 'kinguin_settings_sales' );
					do_settings_sections( 'kinguin_sales_settings' );

				} ?>

		<?php submit_button(); ?>

	</form>
</div>
