<?php
/**
 * Administrator import products page.
 *
 * @package WPDesk\ILKinguin
 *
 * @var bool $memory_limit_error Low memory limit error.
 * @var bool $connection_status  Current connection status.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap">
	<h1 class="wp-heading-inline">
		<?php echo esc_html( get_admin_page_title() ); ?>
	</h1>
	<section class="import-setup <?php echo ( $memory_limit_error ? 'import-error' : '' ); ?>" id="kinguin-import-setup">

		<header>
			<h2>
				<?php esc_attr_e( 'Import games from Kinguin', 'kinguin' ); ?>
			</h2>
            <?php if ( ! $memory_limit_error ) : ?>
                <p>
                    <?php esc_attr_e( 'This tool allows you to import games from Kinguin into your Woocommerce shop.', 'kinguin' ); ?><br>
                </p>
            <?php endif; ?>
		</header>

		<div class="import-setup__begin">
			<div class="message">
                <?php if ( ! $memory_limit_error ) : ?>
                <p>
                    <?php esc_attr_e( 'Due to huge Kinguin games library (above 32.000) this process is rather long and take easily couple of hours. However its duration is heavily dependent of Your server configuration - it is quicker on servers with greater RAM amount. We recommend to import products during low site traffic like for e.g on the night time. You can always interrupt this process and it will automatically resumes where You left.', 'kinguin' ); ?>
                </p>
                <p>
                    <?php esc_attr_e( 'Click - Import Products - to begin with product import.', 'kinguin' ); ?>
                </p>
                <?php else : ?>
                    <?php esc_attr_e( 'Unfortunately the amount of memory limit is set too low to import such huge number products efficiently. Please contact with Your server administrator to increase memory limit to at least 160 MB. Remember that the higher memory limit allow You to get Kinguin products quicker into Your store.', 'kinguin' ); ?>
                <?php endif; ?>
			</div>
		</div>

        <div class="import-setup__process">
            <dl>
                <dt>
                    <?php esc_attr_e( 'Create cache directory', 'kinguin' ); ?>
                </dt>
                <dd>
                    <span class="status_cache_dir"></span>
                </dd>
                <dt>
                    <?php esc_attr_e( 'Importing products sets from Kinguin', 'kinguin' ); ?>
                </dt>
                <dd>
                    <span class="status_cache"></span>
                </dd>
            </dl>
            <div class="progress">
                <progress id="import-progress" max="0" value="0"></progress>
            </div>
            <dl>
                <dt>
                    <span class="kinguin-dynamic-text">
                        <?php esc_attr_e( 'Creating products from cached files', 'kinguin' ); ?>
                    </span>
                </dt>
                <dd>
                    <span class="status_import"></span>
                </dd>
            </dl>
            <div class="progress-db progress-infinite">
                <div class="progress-bar-kinguin">
                </div>
            </div>
        </div>

        <div class="import-setup__done">
            <span class="dashicons dashicons-yes-alt"></span>
            <h3><?php esc_html_e( 'Done!', 'kinguin' ); ?></h3>
            <p><?php esc_html_e( 'Products are imported and ready to sell.', 'kinguin' ); ?><br>
				<?php esc_html_e( 'Thank You and good luck!', 'kinguin' ); ?></p>
        </div>

		<footer>
            <button class="button button-primary start-import" type="submit" <?php disabled( $memory_limit_error || ! $connection_status ); ?> >
                <?php esc_html_e( 'Import Products', 'kinguin' ); ?>
            </button>
		</footer>

	</section>
</div>
