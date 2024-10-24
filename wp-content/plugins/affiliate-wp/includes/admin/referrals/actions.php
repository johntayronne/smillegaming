<?php
/**
 * Admin: Referrals Action Callbacks
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Referrals
 * @copyright   Copyright (c) 2021, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */

/**
 * Process the add referral request
 *
 * @since 1.2
 * @return void|false
 */
function affwp_process_add_referral( $data ) {

	if ( ! is_admin() ) {
		return false;
	}

	$errors = array();

	if ( ! current_user_can( 'manage_referrals' ) ) {
		wp_die( __( 'You do not have permission to manage referrals', 'affiliate-wp' ), __( 'Error', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $data['affwp_add_referral_nonce'], 'affwp_add_referral_nonce' ) ) {
		wp_die( __( 'Security check failed', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( false === affwp_get_affiliate( $data['user_name'] ) ) {
		$errors['invalid_affiliate'] = true;
	}

	if ( $data['amount'] < 0 ) {
		$errors['invalid_amount'] = true;
	}

	if ( empty( $errors ) ) {

		if ( affwp_add_referral( $data ) ) {
			wp_safe_redirect( affwp_admin_url( 'referrals', array(
				'affwp_notice' => 'referral_added'
			) ) );
			exit;
		} else {
			wp_safe_redirect( affwp_admin_url( 'referrals', array(
				'affwp_notice' => 'referral_add_failed'
			) ) );
			exit;
		}

	} else {

		if ( isset( $errors['invalid_affiliate'] ) ) {

			wp_safe_redirect( affwp_admin_url( 'referrals', array(
				'action'       => 'add_referral',
				'affwp_notice' => 'referral_add_invalid_affiliate'
			) ) );
			exit;

		}

		if ( isset( $errors['invalid_amount'] ) ){

			wp_safe_redirect( affwp_admin_url( 'referrals', array(
				'action'       => 'add_referral',
				'affwp_notice' => 'referral_invalid_amount'
			) ) );
			exit;

		}

		wp_safe_redirect( affwp_admin_url( 'referrals', array(
			'affwp_notice' => 'referral_add_failed'
		) ) );
		exit;

	}

}
add_action( 'affwp_add_referral', 'affwp_process_add_referral' );

/**
 * Process the update referral request
 *
 * @since 1.2
 * @return void
 */
function affwp_process_update_referral( $data ) {

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! current_user_can( 'manage_referrals' ) ) {
		wp_die( __( 'You do not have permission to manage referrals', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $data['affwp_edit_referral_nonce'], 'affwp_edit_referral_nonce' ) ) {
		wp_die( __( 'Security check failed', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( isset( $data['amount'] ) && $data['amount'] < 0 ) {
		$errors['invalid_amount'] = true;
	}

	if ( empty( $errors ) ) {

		if ( affiliate_wp()->referrals->update_referral( $data['referral_id'], $data ) ) {
			wp_safe_redirect( affwp_admin_url( 'referrals', array( 'affwp_notice' => 'referral_updated' ) ) );
			exit;
		} else {
			wp_safe_redirect( affwp_admin_url( 'referrals', array( 'affwp_notice' => 'referral_update_failed' ) ) );
			exit;
		}

	} else {

		if ( isset( $errors['invalid_amount'] ) ){
			wp_safe_redirect( affwp_admin_url( 'referrals', array(
				'referral_id'  => $data['referral_id'],
				'action'       => 'edit_referral',
				'affwp_notice' => 'referral_invalid_amount'
			) ) );
			exit;
		}

		wp_safe_redirect( affwp_admin_url( 'referrals', array( 'affwp_notice' => 'referral_update_failed' ) ) );
		exit;

	}

}
add_action( 'affwp_process_update_referral', 'affwp_process_update_referral' );

/**
 * Process the delete referral request
 *
 * @since 1.7
 * @return void
 */
function affwp_process_delete_referral( $data ) {

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! current_user_can( 'manage_referrals' ) ) {
		wp_die( __( 'You do not have permission to manage referrals', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $data['_wpnonce'], 'affwp_delete_referral_nonce' ) ) {
		wp_die( __( 'Security check failed', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( affwp_delete_referral( $data['referral_id'] ) ) {
		wp_safe_redirect( affwp_admin_url( 'referrals', array( 'affwp_notice' => 'referral_deleted' ) ) );
		exit;
	} else {
		wp_safe_redirect( affwp_admin_url( 'referrals', array( 'affwp_notice' => 'referral_delete_failed' ) ) );
		exit;
	}

}
add_action( 'affwp_process_delete_referral', 'affwp_process_delete_referral' );

/**
 * Process the delete payout request
 *
 * @since 2.1.12
 * @return void
 */
function affwp_process_delete_payout( $data ) {

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! current_user_can( 'manage_payouts' ) ) {
		wp_die( __( 'You do not have permission to manage payouts', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $data['_wpnonce'], 'affwp_delete_payout_nonce' ) ) {
		wp_die( __( 'Security check failed', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( affwp_delete_payout( $data['payout_id'] ) ) {
		wp_safe_redirect( affwp_admin_url( 'payouts', array( 'affwp_notice' => 'payout_deleted' ) ) );
		exit;
	} else {
		wp_safe_redirect( affwp_admin_url( 'payouts', array( 'affwp_notice' => 'payout_delete_failed' ) ) );
		exit;
	}

}
add_action( 'affwp_process_delete_payout', 'affwp_process_delete_payout' );

/**
 * Process the referral payout file generation
 *
 * @since 1.0
 * @return void
 */
function affwp_generate_referral_payout_file( $data ) {

	$export = new Affiliate_WP_Referral_Payout_Export;

	if ( ! empty( $data['user_name'] ) && $affiliate = affwp_get_affiliate( $data['user_name'] ) ) {
		$export->affiliate_id = $affiliate->ID;
	}

	$export->date = array(
		'start' => $data['from'],
		'end'   => $data['to']
	);
	$export->export();

}
add_action( 'affwp_generate_referral_payout', 'affwp_generate_referral_payout_file' );

/**
 * Adds a 'holding-period' class to the "Pay Now via PayPal" link if the referral is within the holding period.
 * This class triggers a confirmation modal before payment. It can be extended to other addon's actions if needed.
 *
 * @since 2.27.0
 *
 * @param array $actions The existing referral actions.
 * @param object $referral The referral object.
 *
 * @return array The modified referral actions.
 */
function affwp_add_holding_period_class_to_actions( $actions, $referral ) {

	// Bail if the referral is not within the holding period.
	if ( ! affwp_is_referral_within_holding_period( $referral ) ) {
		return $actions;
	}

	// Loop through the actions.
	foreach ( $actions as $action => $link ) {

		// Check if the action is the "Pay Now via PayPal" link.
		if ( strpos( $link, 'affwp-pay-now-via-paypal' ) !== false ) {

			// Add a holding period class to the link.
			$actions[ $action ] = str_replace( 'class="', 'class="affwp-holding-period ', $link );
			break;
		}

	}

	return $actions;
}
add_filter( 'affwp_referral_row_actions', 'affwp_add_holding_period_class_to_actions', 11, 2 );
