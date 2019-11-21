/**
 * This file adds editing feature for Dynamic Access Block.
 */

import { Component } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, RadioControl, SelectControl, TextControl } from '@wordpress/components';
import { showNotice } from '../helpers';

// Edit Component Class.
class Edit extends Component {
	render() {
		const {
			attributes: {
				accessBehaviour,
				purchaseRequirement,
				content,
				subscriptionIds,
				subscriptionSelectionType,
				timePassIds,
				timePassSelectionType,
			},
			setAttributes,
			className,
		} = this.props;

		// Block specific settings to handle Dynamic Access Content.
		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Dynamic Access Settings', 'laterpay' ) }>
					<RadioControl
						label={ __( 'Access Behaviour', 'laterpay' ) }
						selected={ accessBehaviour }
						options={ [
							{ label: __( 'Show contents after purchase', 'laterpay' ), value: 'show' },
							{ label: __( 'Hide contents after purchase', 'laterpay' ), value: 'hide' },
						] }
						onChange={ ( newAccessBehaviour ) => setAttributes( { accessBehaviour: newAccessBehaviour } ) }
					/>

					<SelectControl
						label={ __( 'Purchase requirement', 'laterpay' ) }
						value={ purchaseRequirement }
						options={ [
							{ value: 'any', label: __( 'Any Purchase', 'laterpay' ) },
							{ value: 'specific', label: __( 'Specific Time Passes or Subscriptions', 'laterpay' ) },
						] }
						onChange={ ( newPurchaseRequirement ) => setAttributes( { purchaseRequirement: newPurchaseRequirement } ) }
					/>

					{
						'specific' === purchaseRequirement && (
							<SelectControl
								label={ __( 'Time Passes to select', 'laterpay' ) }
								value={ timePassSelectionType }
								options={ [
									{ value: 'none', label: __( 'None', 'laterpay' ) },
									{ value: 'all', label: __( 'All', 'laterpay' ) },
									{ value: 'multiple', label: __( 'Specific', 'laterpay' ) },
								] }
								onChange={ ( newTimePassSelection ) => setAttributes( { timePassSelectionType: newTimePassSelection } ) }
							/>
						)
					}
					{
						( 'specific' === purchaseRequirement && 'multiple' === timePassSelectionType ) && (
							<TextControl
								label={ __( "Enter Time Pass ID's manually separated by comma", 'laterpay' ) }
								help={ __( 'To find the Time Pass or Subscription ID, navigate to the Paywall tab. The ID is the number located to the left of the Time Pass or Subscription.', 'laterpay' ) }
								value={ timePassIds }
								onChange={ ( newTimePassIds ) => setAttributes( { timePassIds: newTimePassIds } ) }
							/>
						)
					}

					{
						'specific' === purchaseRequirement && (
							<SelectControl
								label={ __( 'Subscriptions to select', 'laterpay' ) }
								value={ subscriptionSelectionType }
								options={ [
									{ value: 'none', label: __( 'None', 'laterpay' ) },
									{ value: 'all', label: __( 'All', 'laterpay' ) },
									{ value: 'multiple', label: __( 'Specific', 'laterpay' ) },
								] }
								onChange={ ( newsubscriptionSelection ) => setAttributes( { subscriptionSelectionType: newsubscriptionSelection } ) }
							/>
						)
					}
					{
						( 'specific' === purchaseRequirement && 'multiple' === subscriptionSelectionType ) && (
							<TextControl
								label={ __( "Enter Subscription ID's manually separated by comma", 'laterpay' ) }
								help={ __( 'To find the Time Pass or Subscription ID, navigate to the Paywall tab. The ID is the number located to the left of the Time Pass or Subscription.', 'laterpay' ) }
								value={ subscriptionIds }
								onChange={ ( newSubscriptionIds ) => setAttributes( { subscriptionIds: newSubscriptionIds } ) }
							/>
						)
					}
					{
						(
							'specific' === purchaseRequirement &&
							'all' === timePassSelectionType &&
							'all' === subscriptionSelectionType
						) && (
							showNotice( 'error', __( "All cannot be used for both 'Time Passes' and 'Subscriptions' at the same time.", 'laterpay' ) )
						)
					}
				</PanelBody>
			</InspectorControls>
		);

		// Dynamic Access content editor markup.
		const blockOutput = (
			<div className={ className } >
				<RichText
					tagName="div"
					multiline="p"
					placeholder={ __( 'Add content for Dynamic Access', 'laterpay' ) }
					value={ content }
					onChange={ ( newContent ) => setAttributes( { content: newContent } ) }
				/>
			</div>
		);

		return [
			inspectorControls,
			blockOutput,
		];
	}
}

export default Edit;
