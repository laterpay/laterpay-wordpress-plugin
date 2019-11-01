import {Component} from '@wordpress/element';
import {__} from '@wordpress/i18n';
import {
	AlignmentToolbar,
	BlockControls,
	InspectorControls,
	PanelColorSettings,
	ContrastChecker
} from '@wordpress/block-editor';
import {PanelBody, RadioControl, TextControl} from '@wordpress/components';

// Edit Component Class.
class Edit extends Component {
	// Render purchase button.
	render() {
		const {
			attributes: {
				alignment,
				purchaseType,
				buttonText,
				purchaseId,
				buttonBackgroundColor,
				buttonTextColor,
			},
			setAttributes,
			className,
		} = this.props;

		// Block specific settings to handle display attributes of Purchase Button.
		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={__('Purchase Button Settings', 'laterpay')}>
					<RadioControl
						label={__('Type Of Purchase', 'laterpay')}
						selected={purchaseType}
						options={[
							{label: __('TimePass', 'laterpay'), value: 'tp'},
							{label: __('Subscription', 'laterpay'), value: 'sub'},
						]}
						onChange={(newPurchaseType) => setAttributes({purchaseType: newPurchaseType})}
					/>

					<TextControl
						label={__('TimePass / Subscription ID', 'laterpay')}
						help={__('Enter ID for selected purchase type.', 'laterpay')}
						value={purchaseId}
						onChange={(newPurchaseId) => setAttributes({purchaseId: newPurchaseId})}
					/>

					<TextControl
						label={__('Purchase Button Text', 'laterpay')}
						help={__('Enter purchase button text, defaults to item revenue.', 'laterpay')}
						value={buttonText}
						onChange={(newButtonText) => setAttributes({buttonText: newButtonText})}
					/>

					<PanelColorSettings
						title={__('Button Color', 'laterpay')}
						colorSettings={[
							{
								value   : buttonBackgroundColor,
								onChange: (newButtonBackgroundColor) => {
									setAttributes({buttonBackgroundColor: newButtonBackgroundColor});
								},
								label   : __('Background Color'),
							},
						]}
					/>

					<PanelColorSettings
						title={__('Button Text Color', 'laterpay')}
						colorSettings={[
							{
								value   : buttonTextColor,
								onChange: (newButtonTextColor) => {
									setAttributes({buttonTextColor: newButtonTextColor});
								},
								label   : __('Text Color'),
							},
						]}
					/>

					<ContrastChecker
						{...{
							isLargeText    : true,
							textColor      : buttonTextColor,
							backgroundColor: buttonBackgroundColor,
						}}
					/>
				</PanelBody>
			</InspectorControls>
		);

		// Block Control to handle alignment of Purchase Button.
		const blockControls = (
			<BlockControls>
				<AlignmentToolbar
					value={alignment}
					onChange={(newAlignment) => setAttributes({alignment: newAlignment})}
				/>
			</BlockControls>
		);

		// Purchase button markup.
		const blockOutput = (
			<div className={className} style={{textAlign: alignment}}>
				<button className="lp_purchase-overlay__purchase" style={
					{
						backgroundColor: buttonBackgroundColor,
						color          : buttonTextColor,
					}}>
					<span data-icon="b" />
					<span className="lp_purchase-button__text">{buttonText ? buttonText : __('Buy Now, Pay Later', 'laterpay')}</span>
				</button>
			</div>
		);

		return [
			inspectorControls,
			blockControls,
			blockOutput,
		];
	}
}

export default Edit;
