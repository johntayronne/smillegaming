/**
 * AffiliateWP Multi-Currency admin
 *
 * @since  2.26.1
 * @author Darvin da Silveira <ddasilveira@awesomeomotive.com>
 */

'use strict';

/* eslint-disable no-console, no-undef, jsdoc/no-undefined-types */
affiliatewp.attach(
	'multiCurrencySettings',
	/**
	 * Multi-Currency Component.
	 *
	 * @since 2.26.1
	 */
	{
		/**
		 * Populated dynamically via wp_add_inline_script().
		 */
		data: {},

		/**
		 * Initiate select2 in the given elements.
		 *
		 * @since 2.26.1
		 * @param {jQuery} $elements
		 */
		initSelect2( $elements ) {
			$elements.each( function() {
				const $self = jQuery( this );

				$self.select2( affiliatewp.multiCurrencySettings.data.currency_select2_settings );

				affiliatewp.multiCurrencySettings.bindSelect2Events( $self );
			} );
		},

		/**
		 * Bind events to the select2 currency element.
		 *
		 * @since 2.26.1
		 * @param {jQuery} $select2Element
		 */
		bindSelect2Events( $select2Element ) {
			affiliatewp.multiCurrencySettings.addPlaceholderToSelect2SearchInput( $select2Element );
			affiliatewp.multiCurrencySettings.onSelect2ChangeCurrency( $select2Element );
		},

		/**
		 * Add a custom placeholder to a select2 element.
		 *
		 * @since 2.26.1
		 * @param {jQuery} $select2Element
		 */
		addPlaceholderToSelect2SearchInput( $select2Element ) {
			$select2Element.one( 'select2:open', function() {
				jQuery( '.select2-search__field' ).prop( 'placeholder', affiliatewp.multiCurrencySettings.data.currency_select2_settings.placeholder );
			} );
		},

		/**
		 * Retrieve an exchange rate number easier for humans to read.
		 *
		 * @since 2.26.1
		 * @param {string} exchangeRate
		 * @return {string} The new exchange rate.
		 */
		formatExchangeRate( exchangeRate ) {
			return parseFloat( exchangeRate ).toFixed( 4 );
		},

		/**
		 * Retrieve a cached exchange rate for a currency code.
		 *
		 * @since 1.0.0
		 * @param {string} currency The currency code, like USD, BRL.
		 */
		getExchangeRate( currency ) {
			return affiliatewp?.multiCurrency?.data?.exchangeRates.hasOwnProperty( currency )
				? affiliatewp.multiCurrency.data.exchangeRates[ currency ]
				: null;
		},

		/**
		 * Add a custom event to a select2 element to update the exchange rate on change.
		 *
		 * @since 2.26.1
		 * @param {jQuery} $select2Element
		 */
		onSelect2ChangeCurrency( $select2Element ) {
			$select2Element.on( 'select2:select', function() {
				const $self = jQuery( this );
				const $row = $self.closest( '.affwp-multi-currency-row' );
				const currency = $self.val();
				const exchangeRate = affiliatewp.multiCurrencySettings.getExchangeRate( currency );
				const $exchangeRateField = $row.find( '.affwp-multi-currency-field-exchange-rate' );

				$exchangeRateField.val( exchangeRate );

				if ( $exchangeRateField.attr( 'type' ) === 'number' ) {
					$exchangeRateField.focus();
				}

				$row.find( '.affwp-multi-currency-exchange-rate' ).text( affiliatewp.multiCurrencySettings.formatExchangeRate( exchangeRate ) );
				$row.find( '.affwp-multi-currency-currency' ).text( currency );
			} );
		},

		/**
		 * Toggle the remove button visibility.
		 *
		 * The remove button should be visible only if we have three or more tiers on the screen,
		 * since this is a multi-tier system, we always need more than one tier configured.
		 *
		 * @since 2.26.1
		 */
		toggleRemoveButtonVisibility() {
			const $removeTier = jQuery( '.affwp-remove-exchange-rate' );

			// Determine if the remove tier button should be shown or hidden.
			if ( jQuery( '.affwp-multi-currency-row' ).length >= 2 ) {
				$removeTier.css( 'display', 'block' );
				return;
			}

			$removeTier.css( 'display', 'none' );
		},

		/**
		 * Initiate the Tiers repeater in the Settings screen.
		 *
		 * @since 2.26.1
		 */
		initRepeater() {
			// The table body jQuery object.
			const $root = jQuery( '#affwp-multi-currency-rows' );

			// Add a new button jQuery object.
			const $addNewButton = jQuery( '#affwp-new-exchange-rate' );

			// Tracks the total number of rows added so far.
			let total = $root.find( '.affwp-multi-currency-row' ).length;

			const getRowTemplate = function( fields ) {
				// Copy the row HTML template.
				let row = affiliatewp.multiCurrencySettings.data.rowTemplate;

				// Replace all the {{var}} found in the HTML template.
				Object.entries( fields ).forEach( ( [ key, value ] ) => {
					row = row.replace( new RegExp( `{{\\b${ key }\\b}}`, 'g' ), value );
				} );

				return row;
			};

			$addNewButton.on( 'click', function( e ) {
				e.preventDefault();

				const exchangeRate = affiliatewp.multiCurrencySettings.getExchangeRate(
					affiliatewp.multiCurrencySettings.data.firstAvailableCurrencyCode
				);

				const method = jQuery( 'select[name="affwp_settings[multi_currency_rates_update_method]"]' ).val();

				const $row = jQuery( getRowTemplate(
					{
						index: total,
						exchange_rate: exchangeRate,
						exchange_rate_formatted: affiliatewp.multiCurrencySettings.formatExchangeRate( exchangeRate ),
						currency: affiliatewp.multiCurrencySettings.data.firstAvailableCurrencyCode,
						currency_options: '',
					}
				) );

				$row.find( '.affwp-multi-currency-exchange-rate' ).toggle( 'manual' !== method );

				$row.find( '.affwp-multi-currency-field-exchange-rate' ).attr(
					'type',
					'manual' === method
						? 'number'
						: 'hidden'
				);

				$root.append( $row );

				// Update total of rows.
				total = $root.find( '.affwp-multi-currency-row' ).length;

				affiliatewp.multiCurrencySettings.initSelect2( $row.find( 'select' ) );

				affiliatewp.multiCurrencySettings.toggleRemoveButtonVisibility();

				// Focus on the input field in the new row.
				$root.find( '.affwp-multi-currency-row' ).find( 'input' ).focus();
			} );

			// Remove row.
			jQuery( document ).on( 'click', '.affwp-remove-exchange-rate', function( e ) {
				e.preventDefault();

				// Remove the row.
				jQuery( this ).parent().remove();

				affiliatewp.multiCurrencySettings.toggleRemoveButtonVisibility();
			} );

			affiliatewp.multiCurrencySettings.toggleRemoveButtonVisibility();

			// Bind select2 events to existent items.
			$root.find( 'select' ).each( function() {
				affiliatewp.multiCurrencySettings.bindSelect2Events( jQuery( this ) );
			} );
		},
	}
);
