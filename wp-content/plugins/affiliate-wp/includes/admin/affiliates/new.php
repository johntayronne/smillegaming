<?php
$default_rate            = affiliate_wp()->settings->get( 'referral_rate', 20 );
$default_rate            = affwp_abs_number_round( $default_rate );
$user_id                 = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : '';
$user                    = ! empty( $user_id ) && ! affwp_is_affiliate( $user_id ) ? get_userdata( $user_id ) : false;
$disabled                = disabled( (bool) $user, false, false );
$dynamic_coupons_enabled = affiliate_wp()->settings->get( 'dynamic_coupons' );
?>
<div class="wrap">

	<h2><?php _e( 'New Affiliate', 'affiliate-wp' ); ?></h2>

	<form method="post" id="affwp_add_affiliate">

		<?php
		/**
		 * Fires at the top of the new-affiliate admin screen, just inside of the form element.
		 *
		 * @since 1.0
		 */
		do_action( 'affwp_new_affiliate_top' );
		?>

		<?php if ( $user ): ?>
			<p>
				<?php
				/* translators: 1: user login, 2: user email */
				printf( __( 'Use this form to add %1$s (%2$s) as a new affiliate.', 'affiliate-wp' ), esc_attr( $user->user_login ), esc_attr( $user->user_email ) );
				?>
			</p>
		<?php else: ?>
			<p>
				<?php
				/* translators: URL to create a new user */
				printf( __( 'Use this form to create a new affiliate account. Each affiliate is tied directly to a user account, so if the user account for the affiliate does not yet exist, <a href="%s" target="_blank">create one</a>.', 'affiliate-wp' ), admin_url( 'user-new.php' ) );
				?>
			</p>
		<?php endif; ?>

		<table class="form-table">

			<tr class="form-row form-required">

				<th scope="row">
					<label for="user_name"><?php _e( 'User login name', 'affiliate-wp' ); ?></label>
				</th>

				<?php if ( $user ): ?>
					<td>
						<input type="text" name="user_name" id="user_name" value="<?php echo esc_attr( $user->user_login ); ?>" readonly="readonly"/>
					</td>
				<?php else: ?>
					<td>
						<span class="affwp-ajax-search-wrap">
							<input type="text" name="user_name" id="user_name" class="affwp-user-search affwp-enable-on-complete" data-affwp-status="bypass" autocomplete="off"/>
						</span>
						<p class="search-description description"><?php _e( 'Begin typing the name of the affiliate to perform a search for their associated user account.', 'affiliate-wp' ); ?></p>
					</td>
				<?php endif; ?>

			</tr>

			<tr class="form-row hidden affwp-user-email-wrap">

				<th scope="row">
					<label for="user_email"><?php _e( 'User email', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input type="text" name="user_email" id="user_email" class="affwp-user-email"/>
					<p class="description"><?php _e( 'Enter an email address for the new user.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row hidden affwp-user-pass-wrap">

				<th scope="row">
					<label for="user_email"><?php _e( 'User Password', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<p class="description"><?php _e( 'The password will be auto-generated and can be reset by the user or an administrator after the account is created.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="status"><?php _e( 'Affiliate Status', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<select name="status" id="status" <?php echo $disabled; ?>>
						<?php
						$statuses = affwp_get_affiliate_statuses();

						// Exclude rejected for new affiliates, as it's used exclusively in the approval process.
						unset( $statuses['rejected'] );
						?>

						<?php foreach ( $statuses as $status => $label ) : ?>
							<option value="<?php echo esc_attr( $status ); ?>"><?php echo esc_html( $label ); ?></option>
						<?php endforeach; ?>
					</select>
					<p class="description"><?php _e( 'The status assigned to the affiliate&#8217;s account.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="rate_type_default"><?php _e( 'Referral Rate Type', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<fieldset id="rate_type">
						<legend class="screen-reader-text"><?php _e( 'Referral Rate Type', 'affiliate-wp' ); ?></legend>
						<label for="rate_type_default">
							<input type="radio" name="rate_type" id="rate_type_default" value="" checked="checked" <?php echo $disabled; ?> />
							<?php _e( 'Site Default', 'affiliate-wp' ); ?>
						</label>
						<br/>
						<?php foreach ( affwp_get_affiliate_rate_types() as $key => $type ) :
							$value = esc_attr( $key ); ?>
							<label for="rate_type_<?php echo $value; ?>">
								<input type="radio" name="rate_type" id="rate_type_<?php echo $value; ?>" value="<?php echo $value; ?>"  <?php echo $disabled; ?>> <?php echo esc_html( $type ); ?>
							</label>
							<br/>
						<?php endforeach; ?>
						<p class="description"><?php _e( 'The affiliate&#8217;s referral rate type. These settings may be overridden by a chosen affiliate group, but once removed these settings will take precedence.', 'affiliate-wp' ); ?></p>
					</fieldset>
				</td>

			</tr>

			<tr class="form-row affwp-hidden">

				<th scope="row">
					<?php _e( 'Flat Rate Referral Basis', 'affiliate-wp' ); ?>
				</th>

				<td>
					<fieldset id="flat_rate_basis">
						<legend class="screen-reader-text"><?php _e( 'Flat Rate Referral Basis', 'affiliate-wp' ); ?></legend>
						<?php foreach ( affwp_get_affiliate_flat_rate_basis_types() as $key => $type ) :
							$value = esc_attr( $key ); ?>
							<label for="rate_type_<?php echo $value; ?>">
								<input type="radio" name="flat_rate_basis" id="rate_type_<?php echo $value; ?>" value="<?php echo $value; ?>" <?php echo $disabled; ?>> <?php echo esc_html( $type ); ?>
							</label>
							<br/>
						<?php endforeach; ?>
						<p class="description"><?php _e( 'The affiliate&#8217;s flat rate referral basis.', 'affiliate-wp' ); ?></p>
					</fieldset>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="rate"><?php _e( 'Referral Rate', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<input class="regular-text" type="number" name="rate" id="rate" step="0.01" min="0" max="999999999" placeholder="<?php echo esc_attr( $default_rate ); ?>" <?php echo $disabled; ?>/>
					<p class="description"><?php _e( 'The affiliate&#8217;s referral rate, such as 20 for 20%. If left blank, the site default will be used.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row">

				<th scope="row">
					<label for="payment_email"><?php _e( 'Payment Email', 'affiliate-wp' ); ?></label>
				</th>

				<?php if ( $user ): ?>
					<td>
						<input class="regular-text" type="text" name="payment_email" id="payment_email" />
						<p class="description"><?php _e( 'Affiliate&#8217;s payment email for systems such as PayPal, Moneybookers, or others. Leave blank to use the affiliate&#8217;s user email.', 'affiliate-wp' ); ?></p>
					</td>
				<?php else: ?>
					<td>
						<input class="regular-text" type="text" name="payment_email" id="payment_email" disabled="disabled" />
						<p class="description"><?php _e( 'Affiliate&#8217;s payment email for systems such as PayPal, Moneybookers, or others. Leave blank to use the affiliate&#8217;s user email.', 'affiliate-wp' ); ?></p>
					</td>
				<?php endif; ?>

			</tr>

			<?php
			/**
			 * Fires at the 8th position of the new affiliate form.
			 *
			 * @since 2.13.0
			 */
			do_action( 'affwp_new_affiliate_after_status' );
			?>

			<tr class="form-row">

				<th scope="row">
					<label for="notes"><?php esc_html_e( 'Notes', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<textarea name="notes" rows="5" cols="50" id="notes" class="large-text"></textarea>
					<p class="description"><?php esc_html_e( 'Enter any notes for this affiliate. Notes are only visible to an affiliate manager.', 'affiliate-wp' ); ?></p>
				</td>

			</tr>

			<tr class="form-row" id="affwp-welcome-email-row">

				<th scope="row">
					<label for="welcome_email"><?php _e( 'Welcome Email', 'affiliate-wp' ); ?></label>
				</th>

				<td>
					<label class="description">
						<input type="checkbox" name="welcome_email" id="welcome_email" value="1" <?php echo $disabled; ?>/>
						<?php _e( 'Send welcome email after registering affiliate?', 'affiliate-wp' ); ?>
					</label>
				</td>

			</tr>

			<?php if ( affwp_dynamic_coupons_is_setup() ) : ?>

				<tr class="form-row" id="affwp-affiliate-coupon-row">

					<th scope="row">
						<label for="dynamic_coupon"><?php _e( 'Dynamic Coupon', 'affiliate-wp' ); ?></label>
					</th>

					<td>
						<label class="description">
							<input type="checkbox" name="dynamic_coupon" id="dynamic_coupon" value="1" <?php echo $disabled; ?> <?php checked( $dynamic_coupons_enabled, true ); ?> />
							<?php _e( 'Create dynamic coupon for affiliate?', 'affiliate-wp' ); ?>
						</label>
					</td>

				</tr>

			<?php endif; ?>

			<?php
			/**
			 * Fires at the end of the new-affiliate admin screen form area, below form fields.
			 *
			 * @since 1.0
			 */
			do_action( 'affwp_new_affiliate_end' );
			?>

		</table>

		<?php
		/**
		 * Fires at the bottom of the new-affiliate admin screen, prior to the submit button.
		 *
		 * @since 1.0
		 */
		do_action( 'affwp_new_affiliate_bottom' );
		?>

		<input type="hidden" name="affwp_action" value="add_affiliate" />

		<?php
		$atts = array();

		if ( empty( $_REQUEST['user_id'] ) ) {
			$atts['disabled'] = true;
		}

		submit_button( __( 'Add Affiliate', 'affiliate-wp' ), 'primary', 'submit', true, $atts );
		?>

	</form>

</div>
