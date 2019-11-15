import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Icon from '../icons';
import Edit from './edit';

/**
 * Register Contribution Block.
 */
registerBlockType( 'laterpay/contribution', {
	title: __( 'LaterPay Contribution', 'laterpay' ),
	icon: Icon.heart,
	category: 'laterpay-blocks',
	keywords: [
		__( 'Contribution', 'laterpay' ),
	],
	attributes: {
		campaignName: {
			type: 'string',
		},
		campaignThankYouPage: {
			type: 'string',
		},
		contributionType: {
			type: 'string',
			default: 'multiple',
		},
		allowCustomAmount: {
			type: 'boolean',
			default: true,
		},
		singleContribution: {
			type: 'object',
			default: {
				amount: '0.00',
				revenue: 'ppu',
				revenueDisable: true,
			},
		},
		multipleContribution: {
			type: 'object',
			default: {
				amountOne: '0.00',
				revenueOne: 'ppu',
				revenueDisableOne: true,
				amountTwo: '0.00',
				revenueTwo: 'ppu',
				revenueDisableTwo: true,
				amountThree: '0.00',
				revenueThree: 'ppu',
				revenueDisableThree: true,
				amountFour: '0.00',
				revenueFour: 'ppu',
				revenueDisableFour: true,
				amountFive: '0.00',
				revenueFive: 'ppu',
				revenueDisableFive: true,
			},
		},
		selectedAmount: {
			type: 'integer',
			default: 1,
		},
	},
	edit: Edit,
	save() {
		return null;
	},
} );
