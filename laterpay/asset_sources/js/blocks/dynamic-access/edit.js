/**
 * This file adds editing feature for Dynamic Access Block.
 */

const { Component } = wp.element;
const { __ } = wp.i18n;
const { InspectorControls, RichText } = wp.blockEditor;
const { PanelBody, RadioControl, SelectControl, TextControl } = wp.components;
import { showNotice } from '../helpers';

// Edit Component Class.
class Edit extends Component {
	render() {
		const {
			attributes: {
				accessBehaviour,
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
						help={ __( 'Show/Hide content based on User access', 'laterpay' ) }
						options={ [
							{ label: __( 'Show', 'laterpay' ), value: 'show' },
							{ label: __( 'Hide', 'laterpay' ), value: 'hide' },
						] }
						onChange={ ( newAccessBehaviour ) => setAttributes( { accessBehaviour: newAccessBehaviour } ) }
					/>
					<SelectControl
						label={ __( 'TimePasses to select', 'laterpay' ) }
						value={ timePassSelectionType }
						options={ [
							{ value: 'none', label: __( 'None', 'laterpay' ) },
							{ value: 'all', label: __( 'All', 'laterpay' ) },
							{ value: 'multiple', label: __( 'Specific', 'laterpay' ) },
						] }
						onChange={ ( newTimePassSelection ) => setAttributes( { timePassSelectionType: newTimePassSelection } ) }
					/>
					{
						'multiple' === timePassSelectionType ? (
							<TextControl
								label={ __( "Enter TimePass ID's manually separated by comma", 'laterpay' ) }
								help={ __( 'Ex: 13,42 will check if user has access too TimePass with ID 13 or 42', 'laterpay' ) }
								value={ timePassIds }
								onChange={ ( newTimePassIds ) => setAttributes( { timePassIds: newTimePassIds } ) }
							/>
						) : ( '' )
					}
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
					{
						'multiple' === subscriptionSelectionType ? (
							<TextControl
								label={ __( "Enter TimePass ID's manually separated by comma", 'laterpay' ) }
								help={ __( 'Ex: 13,42 will check if user has access to Subscription with ID 13 or 42', 'laterpay' ) }
								value={ subscriptionIds }
								onChange={ ( newSubscriptionIds ) => setAttributes( { subscriptionIds: newSubscriptionIds } ) }
							/>
						) : ( '' )
					}
					{
						( 'all' === timePassSelectionType && 'all' === subscriptionSelectionType ) ? (
							showNotice( 'error', __( "All cannot be used for both 'timepasses' and 'subscriptions' at the same time.", 'laterpay' ) )
						) : ( '' )
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
