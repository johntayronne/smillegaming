<?php
/**
 * Admin: New Creative View
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Creatives
 * @copyright   Copyright (c) 2014, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

$qrcode_default_colors = affiliatewp_get_qrcode_default_colors();

?>
<div class="wrap">

	<h2><?php esc_html_e( 'New Creative', 'affiliate-wp' ); ?></h2>

	<form method="post" id="affwp_add_creative">

		<?php
		/**
		 * Fires at the top of the new-creative admin screen.
		 *
		 * @since 1.0
		 */
		do_action( 'affwp_new_creative_top' );
		?>

		<p><?php esc_html_e( 'Use this screen to add a new creative, such as a text link or image banner.', 'affiliate-wp' ); ?></p>

		<table class="form-table" data-current-context="image">

			<tr class="form-row form-required" data-row="name">

				<th scope="row">
					<label for="name"><?php esc_html_e( 'Name', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="name" id="name" required class="regular-text" />
					<p class="description"><?php esc_html_e( 'The name of this creative. Use this to briefly describe the creative to your affiliates.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<?php

			/**
			 * Fires before description.
			 *
			 * @since 2.12.0
			 */
			do_action( 'affwp_new_before_description' );

			?>

			<tr class="form-row form-required" data-row="description">

				<th scope="row">
					<label for="description"><?php esc_html_e( 'Description', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<textarea name="description" rows="5" cols="50" id="description" class="large-text"></textarea>
					<p class="description"><?php esc_html_e( 'An optional description for this creative. Use this to provide additional information about the creative to your affiliates.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required" data-row="type">
				<th scope="row">
					<label for="type"><?php esc_html_e( 'Type', 'affiliate-wp' ); ?></label>
				</th>
				<td>
					<select name="type" id="type">
						<?php foreach ( affwp_get_creative_types() as $creative_type => $label ) : ?>

							<?php
							$show_education = 'qr_code' === $creative_type && ! affwp_can_access_pro_features();
							$education_name = $show_education
								? esc_html__( 'QR Code Creatives', 'affiliate-wp' )
								: '';
							$education_utm  = $show_education
								? esc_html__( 'qr-code-creative-type', 'affiliate-wp' )
								: '';
							?>

							<option
								value="<?php echo esc_attr( $creative_type ); ?>"
								class="<?php echo esc_attr( affiliatewp_get_pro_feature_option_classes( 'qr_code', $creative_type, '' ) ); ?>"

								<?php

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content already escaped.
								echo affiliatewp_tag_attr( 'data-name', $education_name );

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content already escaped.
								echo affiliatewp_tag_attr( 'data-utm-content', $education_utm );

								?>
							>
								<?php echo esc_html( $label ); ?>
							</option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select the type of the creative.', 'affiliate-wp' ); ?></p>
				</td>
			</tr>

			<tr class="form-row form-required" data-row="image">

				<th scope="row">
					<label for="image"><?php esc_html_e( 'Image', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input id="image" name="image" type="text" required class="upload_field regular-text" />
					<input class="upload_image_button button-secondary" type="button" value="Choose Image" />
					<p class="description"><?php esc_html_e( 'Select your image. You can also enter an image URL if your image is hosted elsewhere.', 'affiliate-wp' ); ?></p>

					<div id="preview_image"></div>
				</td>

			</tr>

			<tr class="form-row form-required" data-row="text">

				<th scope="row">
					<label for="text" data-context="text_link"><?php esc_html_e( 'Text', 'affiliate-wp' ); ?></label>
					<label for="text" data-context="image"><?php esc_html_e( 'Alt Text', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="text" id="text" class="regular-text" maxlength="255" />
					<p class="description" data-context="text_link"><?php esc_html_e( 'Text for this creative.', 'affiliate-wp' ); ?></p>
					<p class="description" data-context="image"><?php esc_html_e( "Enter descriptive text for the image's alternative text (alt text).", 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row form-required" data-row="url">

				<th scope="row">
					<label for="url"><?php esc_html_e( 'URL', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="url" id="url" class="regular-text" />
					<p class="description"><?php esc_html_e( 'The URL this creative should link to. Based on your Referral Settings, the affiliate&#8217;s ID or username will be automatically appended.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row" data-row="qr_preview">

				<th scope="row">
					<label><?php esc_html_e( 'QR Code', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<?php if ( affwp_can_access_pro_features() ) : ?>

						<div id="qr-code">
							<p><strong><?php esc_html_e( 'Preview', 'affiliate-wp' ); ?></strong></p>
							<div id="qr-preview"></div>
						</div>

						<div>

							<label for="qrcode-url"><strong><?php esc_html_e( 'URL', 'affiliate-wp' ); ?></strong></label>
							<input
								type="text"
								name="url"
								id="qrcode-url"
								placeholder="<?php echo esc_url_raw( home_url() ); ?>"
								value=""
								class="regular-text affwp-qrcode-url-field"
							>
							<p class="description"><?php esc_html_e( 'The URL the QR code should link to. Based on your Referral Settings, the affiliate&#8217;s ID or username will be automatically appended.', 'affiliate-wp' ); ?></p>

						</div>

						<div id="qrcode-color-pickers">

							<div class="qrcode-color-picker-container" data-default-color="<?php echo esc_attr( $qrcode_default_colors['code'] ); ?>">
								<label for="qrcode-code-color"><strong><?php esc_html_e( 'Code Color', 'affiliate-wp' ); ?></strong></label>
								<input id="qrcode-code-color" type="text" class="affwp-color-picker" value="<?php echo esc_attr( $qrcode_default_colors['code'] ); ?>" name="qrcode_code_color" />
							</div>

							<div class="qrcode-color-picker-container" data-default-color="<?php echo esc_attr( $qrcode_default_colors['bg'] ); ?>">
								<label for="qrcode-bg-color"><strong><?php esc_html_e( 'Background Color', 'affiliate-wp' ); ?></strong></label>
								<input id="qrcode-bg-color" type="text" class="affwp-color-picker" value="<?php echo esc_attr( $qrcode_default_colors['bg'] ); ?>" name="qrcode_bg_color" />
							</div>

						</div>

						<p class="qrcode-disclaimer description" style="display:none;">
							<strong><?php esc_html_e( 'Remember to test the QR code with your new colors to ensure it remains scannable.', 'affiliate-wp' ); ?></strong>
						</p>

					<?php endif; ?>

				</td>

			</tr>

			<?php affwp_creative_privacy_toggle(); ?>

			<?php

			/**
			 * Fires before status row.
			 *
			 * @since 2.13.0
			 */
			do_action( 'affwp_new_creative_before_status' );

			?>

			<tr class="form-row form-required" data-row="status">

				<th scope="row">
					<label for="status"><?php esc_html_e( 'Status', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<select name="status" id="status">
						<?php foreach ( affwp_get_creative_statuses() as $status_key => $label ) : ?>

							<?php
							$show_education = 'scheduled' === $status_key && ! affwp_can_access_pro_features();
							$education_name = $show_education
								? esc_html__( 'Scheduled Creatives', 'affiliate-wp' )
								: '';
							$education_utm  = $show_education
								? esc_html__( 'scheduled-creatives', 'affiliate-wp' )
								: '';
							?>

							<option
								value="<?php echo esc_attr( $status_key ); ?>"
								class="<?php echo esc_attr( affiliatewp_get_pro_feature_option_classes( 'scheduled', $status_key, '' ) ); ?>"
								<?php

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content already escaped.
								echo affiliatewp_tag_attr( 'data-name', $education_name );

								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Content already escaped.
								echo affiliatewp_tag_attr( 'data-utm-content', $education_utm );

								?>
							>
								<?php echo esc_html( $label ); ?>
							</option>

						<?php endforeach; ?>

					</select>
					<p class="description"><?php esc_html_e( 'Select the status of the creative. A creative can be Active, Inactive, or Scheduled.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row affwp-schedule-creatives-setting affwp-hidden" data-row="schedule">

				<th scope="row">
					<label><?php esc_html_e( 'Schedule', 'affiliate-wp' ); ?></label>
				</th>

				<td class="schedule-creative-date-fields">
					<?php if ( true === affwp_is_upgrade_required( 'pro' ) ) : ?>
						<div class="affwp-upgrade-setting-cta">
							<p>
								<?php
								esc_html_e( 'Scheduling allows you to set start and end dates for your creatives, giving you more flexibility and control over your affiliate campaigns.', 'affiliate-wp' )
								?>
							</p>
							<h4>
								<a href="<?php echo esc_url( affwp_admin_upgrade_link( 'affiliatewp-creatives-new', 'Upgrade to AffiliateWP Pro' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade to AffiliateWP Pro to Unlock Creative Scheduling', 'affiliate-wp' ); ?></a>
							</h4>
						</div>
					<?php else : ?>
						<p class="description"><?php esc_html_e( "Set a specific time frame for your creative's visibility.", 'affiliate-wp' ); ?></p>
					<?php endif; ?>
					<div>
						<input type="text" class="affwp-schedule-creative-datepicker" autocomplete="off" name="start_date"
							placeholder="<?php esc_html_e( 'mm/dd/yyyy', 'affiliate-wp' ); ?>"
							<?php echo esc_attr( true === affwp_is_upgrade_required( 'pro' ) ? 'disabled' : '' ); ?> />
						<p class="description"><?php esc_html_e( 'Start date.', 'affiliate-wp' ); ?></p>
					</div>
					<div>
						<input type="text" class="affwp-schedule-creative-datepicker" autocomplete="off" name="end_date"
							placeholder="<?php esc_html_e( 'mm/dd/yyyy', 'affiliate-wp' ); ?>"
							<?php echo esc_attr( true === affwp_is_upgrade_required( 'pro' ) ? 'disabled' : '' ); ?>/>
						<p class="description"><?php esc_html_e( 'End date.', 'affiliate-wp' ); ?></p>
					</div>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="notes"><?php esc_html_e( 'Notes', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<textarea name="notes" rows="5" cols="50" id="notes" class="large-text"></textarea>
					<p class="description"><?php esc_html_e( 'Enter any notes for this creative. Notes are only visible to an affiliate manager.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

		</table>

		<?php
		/**
		 * Fires at the bottom of the new-creative admin screen.
		 *
		 * @since 1.0
		 */
		do_action( 'affwp_new_creative_bottom' );

		?>

		<input type="hidden" name="affwp_action" value="add_creative" />

		<?php submit_button( __( 'Add Creative', 'affiliate-wp' ) ); ?>

	</form>

</div>