/**
 * Contribution Block Registration.
 */

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
			type: 'string', // Name of Campaign.
		},
		campaignThankYouPage: {
			type: 'string', // Redirection page after purchase.
		},
		contributionType: {
			type: 'string', // Type of Contribution dialog.
			default: 'multiple',
		},
		allowCustomAmount: {
			type: 'boolean', // If multiple contribution, allow custom?.
			default: true,
		},
		singleContribution: {
			type: 'object', // Single contribution configuration.
			default: {
				amount: '0.00',
				revenue: 'ppu',
				revenueDisable: true,
			},
		},
		multipleContribution: {
			type: 'object', // Multiple contribution configuration.
			default: {
				amountOne: '1.00',
				revenueOne: 'ppu',
				revenueDisableOne: true,
				amountTwo: '2.00',
				revenueTwo: 'ppu',
				revenueDisableTwo: true,
				amountThree: '5.00',
				revenueThree: 'sis',
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
			type: 'integer', // If Multiple contribution is selected, default button selection..
			default: 3,
		},
	},
	edit: Edit,
	save() {
		return null;
	},
} );
