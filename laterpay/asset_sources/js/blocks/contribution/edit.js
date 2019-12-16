/* globals laterPayBlockData */

/**
 * This file adds editing feature for Contribution Dialog.
 */

import { __, sprintf } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, SelectControl, TextControl, CheckboxControl } from '@wordpress/components';
import { showNotice } from '../helpers';
import { PresetButton } from '../components/multi-contribution-button';

// Edit Component Class.
class Edit extends Component {
	render() {
		const currencySettings = typeof laterPayBlockData !== 'undefined' ? laterPayBlockData.currency : {};
		const merchantLocale = typeof laterPayBlockData !== 'undefined' ? laterPayBlockData.locale : '';
		const currencySymbol = typeof laterPayBlockData !== 'undefined' ? laterPayBlockData.symbol : '$';

		const {
			attributes: {
				campaignName,
				dialogHeader,
				dialogDescription,
				campaignThankYouPage,
				contributionType,
				allowCustomAmount,
				singleContribution,
				multipleContribution,
				selectedAmount,
			},
			setAttributes,
			className,
		} = this.props;

		const validatePrice = ( price ) => {
			// strip non-number characters
			price = price.replace( /[^0-9\,\.]/g, '' );

			// convert price to proper float value
			price = parseFloat( price.replace( ',', '.' ) ).toFixed( 2 );

			// prevent non-number prices
			if ( isNaN( price ) ) {
				price = 0;
			}

			// prevent negative prices
			price = Math.abs( price );

			// correct prices outside the allowed range of 0.05 - 149.99
			if ( price > currencySettings.sis_max ) {
				price = currencySettings.sis_max;
			} else if ( price > 0 && price < currencySettings.ppu_min ) {
				price = currencySettings.ppu_min;
			}

			// format price with two digits
			price = price.toFixed( 2 );

			// localize price
			if ( merchantLocale.indexOf( 'de_DE' ) !== -1 ) {
				price = price.replace( '.', ',' );
			}

			return price;
		};

		// Validate the current prices' revenue model, correct if necessary.
		const validateRevenueModel = ( price ) => {
			let revenueSettings = {};

			if ( Math.abs( price ) === 0 || price <= currencySettings.ppu_only_limit ) {
				revenueSettings = {
					revenue: 'ppu',
					disable: true,
				};
			} else if ( price >= currencySettings.ppu_min && price < currencySettings.ppu_max ) {
				revenueSettings = {
					revenue: 'ppu',
					disable: true,
				};
			} else if ( price >= currencySettings.ppu_max ) {
				revenueSettings = {
					revenue: 'sis',
					disable: true,
				};
			}

			if ( price >= currencySettings.sis_min && price < currencySettings.sis_only_limit ) {
				revenueSettings = {
					revenue: 'ppu',
					disable: false,
				};
			}

			return revenueSettings;
		};

		const updateSingleContributionAmount = ( price ) => {
			const newRevenueSettings = validateRevenueModel(
				price,
				singleContribution.revenue
			);
			setAttributes( {
				singleContribution: {
					amount: price,
					revenue: newRevenueSettings.revenue,
					revenueDisable: newRevenueSettings.disable,
				},
			} );
		};

		const updateMultipleContributionAmount = ( price, number ) => {
			let newAmount, newData, newRevenueSettings;

			switch ( number ) {
				case 1:
					newRevenueSettings = validateRevenueModel( price, multipleContribution.revenueOne );
					newAmount = {
						amountOne: price,
						revenueOne: newRevenueSettings.revenue,
						revenueDisableOne: newRevenueSettings.disable,
					};
					newData = Object.assign( {}, multipleContribution, newAmount );
					setAttributes( { multipleContribution: newData } );
					break;
				case 2:
					newRevenueSettings = validateRevenueModel( price, multipleContribution.revenueTwo );
					newAmount = {
						amountTwo: price,
						revenueTwo: newRevenueSettings.revenue,
						revenueDisableTwo: newRevenueSettings.disable,
					};
					newData = Object.assign( {}, multipleContribution, newAmount );
					setAttributes( { multipleContribution: newData } );
					break;
				case 3:
					newRevenueSettings = validateRevenueModel( price, multipleContribution.revenueThree );
					newAmount = {
						amountThree: price,
						revenueThree: newRevenueSettings.revenue,
						revenueDisableThree: newRevenueSettings.disable,
					};
					newData = Object.assign( {}, multipleContribution, newAmount );
					setAttributes( { multipleContribution: newData } );
					break;
				case 4:
					newRevenueSettings = validateRevenueModel( price, multipleContribution.revenueFour );
					newAmount = {
						amountFour: price,
						revenueFour: newRevenueSettings.revenue,
						revenueDisableFour: newRevenueSettings.disable,
					};
					newData = Object.assign( {}, multipleContribution, newAmount );
					setAttributes( { multipleContribution: newData } );
					break;
				case 5:
					newRevenueSettings = validateRevenueModel( price, multipleContribution.revenueFive );
					newAmount = {
						amountFive: price,
						revenueFive: newRevenueSettings.revenue,
						revenueDisableFive: newRevenueSettings.disable,
					};
					newData = Object.assign( {}, multipleContribution, newAmount );
					setAttributes( { multipleContribution: newData } );
					break;
				default:
					break;
			}
		};

		const updateMultipleContributionRevenue = ( revenue, number ) => {
			let newRevenue, newData;

			switch ( number ) {
				case 1:
					newRevenue = { revenueOne: revenue };
					newData = Object.assign( {}, multipleContribution, newRevenue );
					setAttributes( { multipleContribution: newData } );
					break;
				case 2:
					newRevenue = { revenueTwo: revenue };
					newData = Object.assign( {}, multipleContribution, newRevenue );
					setAttributes( { multipleContribution: newData } );
					break;
				case 3:
					newRevenue = { revenueThree: revenue };
					newData = Object.assign( {}, multipleContribution, newRevenue );
					setAttributes( { multipleContribution: newData } );
					break;
				case 4:
					newRevenue = { revenueFour: revenue };
					newData = Object.assign( {}, multipleContribution, newRevenue );
					setAttributes( { multipleContribution: newData } );
					break;
				case 5:
					newRevenue = { revenueFive: revenue };
					newData = Object.assign( {}, multipleContribution, newRevenue );
					setAttributes( { multipleContribution: newData } );
					break;
				default:
					break;
			}
		};

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Contribution Settings', 'laterpay' ) }>
					<TextControl
						label={ __( 'Campaign Name', 'laterpay' ) }
						help={ __( 'Enter Campaign name.', 'laterpay' ) }
						value={ campaignName }
						onChange={ ( newCampaignName ) => setAttributes( { campaignName: newCampaignName } ) }
					/>

					{
						( ! campaignName ) &&
							showNotice( 'error', __( 'Please enter a Campaign Name above.', 'laterpay' ) )
					}

					<TextControl
						label={ __( 'Thank you page (optional)', 'laterpay' ) }
						help={ __( 'Enter Thank you page.', 'laterpay' ) }
						value={ campaignThankYouPage }
						onChange={ ( newThankYouPage ) => setAttributes( { campaignThankYouPage: newThankYouPage } ) }
					/>

					<SelectControl
						label={ __( 'Contribution Type', 'laterpay' ) }
						value={ contributionType }
						options={ [
							{ value: 'multiple', label: __( 'Multiple', 'laterpay' ) },
							{ value: 'single', label: __( 'Single', 'laterpay' ) },
						] }
						onChange={ ( newContributionType ) => setAttributes( { contributionType: newContributionType } ) }
					/>

					{
						'multiple' === contributionType ? (
							<Fragment>
								<TextControl
									label={ __( 'Dialog Header (optional)', 'laterpay' ) }
									placeholder={ __( 'Support the author', 'laterpay' ) }
									value={ dialogHeader }
									onChange={ ( newValue ) => {
										setAttributes( { dialogHeader: newValue } );
									} }
								/>

								<TextControl
									label={ __( 'Dialog Description (optional)', 'laterpay' ) }
									placeholder={ __( 'How much would you like to contribute?', 'laterpay' ) }
									value={ dialogDescription }
									onChange={ ( newValue ) => {
										setAttributes( { dialogDescription: newValue } );
									} }
								/>

								<TextControl
									label={ __( 'Amount One', 'laterpay' ) }
									placeholder={ 0.0 }
									value={ multipleContribution.amountOne }
									onChange={ ( newValue ) => {
										updateMultipleContributionAmount( newValue, 1 );
									} }
								/>

								<SelectControl
									label={ __( 'Revenue Model', 'laterpay' ) }
									value={ multipleContribution.revenueOne }
									disabled={ multipleContribution.revenueDisableOne }
									options={ [
										{ value: 'ppu', label: __( 'Pay Later', 'laterpay' ) },
										{ value: 'sis', label: __( 'Pay Now', 'laterpay' ) },
									] }
									onChange={
										( newRevenue ) => {
											updateMultipleContributionRevenue( newRevenue, 1 );
										}
									}
								/>

								<TextControl
									label={ __( 'Amount Two', 'laterpay' ) }
									placeholder={ 0.0 }
									value={ multipleContribution.amountTwo }
									onChange={ ( newValue ) => {
										updateMultipleContributionAmount( newValue, 2 );
									} }
								/>

								<SelectControl
									label={ __( 'Revenue Model', 'laterpay' ) }
									value={ multipleContribution.revenueTwo }
									disabled={ multipleContribution.revenueDisableTwo }
									options={ [
										{ value: 'ppu', label: __( 'Pay Later', 'laterpay' ) },
										{ value: 'sis', label: __( 'Pay Now', 'laterpay' ) },
									] }
									onChange={
										( newRevenue ) => {
											updateMultipleContributionRevenue( newRevenue, 2 );
										}
									}
								/>

								<TextControl
									label={ __( 'Amount Three', 'laterpay' ) }
									placeholder={ 0.0 }
									value={ multipleContribution.amountThree }
									onChange={ ( newValue ) => {
										updateMultipleContributionAmount( newValue, 3 );
									} }
								/>

								<SelectControl
									label={ __( 'Revenue Model', 'laterpay' ) }
									value={ multipleContribution.revenueThree }
									disabled={ multipleContribution.revenueDisableThree }
									options={ [
										{ value: 'ppu', label: __( 'Pay Later', 'laterpay' ) },
										{ value: 'sis', label: __( 'Pay Now', 'laterpay' ) },
									] }
									onChange={
										( newRevenue ) => {
											updateMultipleContributionRevenue( newRevenue, 3 );
										}
									}
								/>

								<TextControl
									label={ __( 'Amount Four', 'laterpay' ) }
									placeholder={ 0.0 }
									value={ multipleContribution.amountFour }
									onChange={ ( newValue ) => {
										updateMultipleContributionAmount( newValue, 4 );
									} }
								/>

								<SelectControl
									label={ __( 'Revenue Model', 'laterpay' ) }
									value={ multipleContribution.revenueFour }
									disabled={ multipleContribution.revenueDisableFour }
									options={ [
										{ value: 'ppu', label: __( 'Pay Later', 'laterpay' ) },
										{ value: 'sis', label: __( 'Pay Now', 'laterpay' ) },
									] }
									onChange={
										( newRevenue ) => {
											updateMultipleContributionRevenue( newRevenue, 4 );
										}
									}
								/>

								<TextControl
									label={ __( 'Amount Five', 'laterpay' ) }
									placeholder={ 0.0 }
									value={ multipleContribution.amountFive }
									onChange={ ( newValue ) => {
										updateMultipleContributionAmount( newValue, 5 );
									} }
								/>

								<SelectControl
									label={ __( 'Revenue Model', 'laterpay' ) }
									value={ multipleContribution.revenueFive }
									disabled={ multipleContribution.revenueDisableFive }
									options={ [
										{ value: 'ppu', label: __( 'Pay Later', 'laterpay' ) },
										{ value: 'sis', label: __( 'Pay Now', 'laterpay' ) },
									] }
									onChange={
										( newRevenue ) => {
											updateMultipleContributionRevenue( newRevenue, 5 );
										}
									}
								/>

								<SelectControl
									label={ __( 'Default Selected Amount', 'laterpay' ) }
									value={ selectedAmount }
									options={ [
										{ value: 1, label: __( 'Amount One', 'laterpay' ) },
										{ value: 2, label: __( 'Amount Two', 'laterpay' ) },
										{ value: 3, label: __( 'Amount Three', 'laterpay' ) },
										{ value: 4, label: __( 'Amount Four', 'laterpay' ) },
										{ value: 5, label: __( 'Amount Five', 'laterpay' ) },
									] }
									onChange={
										( newDefaultAmount ) => {
											setAttributes( { selectedAmount: parseInt( newDefaultAmount ) } );
										}
									}
								/>

								<CheckboxControl
									heading={ __( ' Allow custom contribution amount', 'laterpay' ) }
									label={ __( 'Check to allow custom contribution', 'laterpay' ) }
									checked={ allowCustomAmount }
									onChange={ ( newAllowCustomAmount ) => setAttributes( { allowCustomAmount: newAllowCustomAmount } ) }
								/>
							</Fragment>
						) : (
							<Fragment>
								<TextControl
									label={ __( 'Amount', 'laterpay' ) }
									help={ __( 'Enter Contribution amount.', 'laterpay' ) }
									placeholder={ 0.0 }
									value={ singleContribution.amount }
									onChange={ ( newValue ) => {
										updateSingleContributionAmount( newValue );
									} }
								/>

								<SelectControl
									label={ __( 'Revenue Model', 'laterpay' ) }
									value={ singleContribution.revenue }
									disabled={ singleContribution.revenueDisable }
									options={ [
										{ value: 'ppu', label: __( 'Pay Later', 'laterpay' ) },
										{ value: 'sis', label: __( 'Pay Now', 'laterpay' ) },
									] }
									onChange={
										( newSingleRevenue ) => setAttributes( {
											singleContribution: {
												amount: singleContribution.amount,
												revenue: newSingleRevenue,
												revenueDisable: singleContribution.revenueDisable,
											},
										} )
									}
								/>
							</Fragment>
						)
					}

				</PanelBody>
			</InspectorControls>
		);

		const singleAmount = validatePrice( singleContribution.amount );
		const singleRevenue = singleContribution.revenue;

		const singleButtonText = 'ppu' === singleRevenue ?
			sprintf( '%s %s%s %s', __( 'Contribute', 'laterpay' ), currencySymbol, singleAmount, __( 'now, Pay Later', 'laterpay' ) ) :
			sprintf( '%s %s%s %s', __( 'Contribute', 'laterpay' ), currencySymbol, singleAmount, __( 'now', 'laterpay' ) );

		const currentSelectedAmount = parseInt( selectedAmount );

		const blockOutput = (
			<div className={ className }>
				{
					'multiple' === contributionType ? (
						<Fragment>
							<div className="lp-multiple-wrapper">
								<div className="lp-dialog-wrapper">
									<div className="lp-dialog">
										<div className="lp-header-wrapper">
											<div className="lp-header-padding" />
											<div className="lp-header-text">
												<span>{ dialogHeader }</span>
											</div>
										</div>
										<div className="lp-body-wrapper">
											<div>
												<span className="lp-amount-text">{ dialogDescription }</span>
											</div>
											<div className="lp-amount-presets-wrapper">
												{
													multipleContribution.amountOne &&
													<PresetButton amount={ currencySymbol + validatePrice( multipleContribution.amountOne ) } isSelected={ currentSelectedAmount === 1 } />
												}
												{
													multipleContribution.amountTwo &&
													<PresetButton amount={ currencySymbol + validatePrice( multipleContribution.amountTwo ) } isSelected={ currentSelectedAmount === 2 } />
												}
												{
													multipleContribution.amountThree &&
													<PresetButton amount={ currencySymbol + validatePrice( multipleContribution.amountThree ) } isSelected={ currentSelectedAmount === 3 } />
												}
												{
													( '0.00' !== multipleContribution.amountFour && '0' !== multipleContribution.amountFour && multipleContribution.amountFour ) &&
													<PresetButton amount={ currencySymbol + validatePrice( multipleContribution.amountFour ) } isSelected={ currentSelectedAmount === 4 } />
												}
												{
													( '0.00' !== multipleContribution.amountFive && '0' !== multipleContribution.amountFive && multipleContribution.amountFive ) &&
													<PresetButton amount={ currencySymbol + validatePrice( multipleContribution.amountFive ) } isSelected={ currentSelectedAmount === 5 } />
												}
											</div>
											{
												true === allowCustomAmount &&
													<div className="lp-custom-amount-wrapper">
														<div className="lp-custom-amount">
															<label htmlFor="lp_custom_amount_input" className="lp-custom-amount-label">
																<span className="lp-custom-amount-text">{ __( 'Custom Amount', 'laterpay' ) }:</span>
															</label>
															<div className="lp-custom-input-wrapper">
																<input className="lp-custom-amount-input" type="text" disabled="disabled" />
																<i>{ currencySymbol }</i>
															</div>
														</div>
													</div>
											}
											<div className="lp-dialog-button-wrapper">
												<div className="lp-button-wrapper">
													<div data-url="" className="lp-button lp-contribution-button">
														<div className="lp-cart" />
														<div className="lp-link">
															{ __( 'Contribute now', 'laterpay' ) }
														</div>
													</div>
												</div>
											</div>
										</div>
										<div className="lp-powered-by">
											<span>{ __( 'Powered by', 'laterpay' ) }</span>
											<span data-icon="a" className="lp-powered-by-link" />
										</div>
									</div>
								</div>
							</div>
						</Fragment>
					) : (
						<Fragment>
							<div className="lp-dialog-single-button-wrapper">
								<div className="lp-button-wrapper">
									<div className="lp-button">
										<div className="lp-cart" />
										<div className="lp-link lp-link-single">{ singleButtonText }</div>
									</div>
								</div>
							</div>
						</Fragment>
					)
				}
			</div>
		);

		return [
			inspectorControls,
			blockOutput,
		];
	}
}

export default Edit;
