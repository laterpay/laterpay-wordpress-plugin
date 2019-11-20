/**
 * Dynamic Access Block Registration.
 */

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import Icon from '../icons';
import Edit from './edit';

/**
 * Register Dynamic Access Block.
 */
registerBlockType( 'laterpay/dynamic-access', {
	title: __( 'LaterPay Dynamic Access', 'laterpay' ),
	icon: Icon.dynamicAccess,
	category: 'laterpay-blocks',
	description: __( 'Use this block to show or hide the content in the block based on which LaterPay product the user has purchased.', 'laterpay' ),
	keywords: [
		__( 'Time Pass Access', 'laterpay' ),
		__( 'Subscription Access', 'laterpay' ),
	],
	attributes: {
		accessBehaviour: {
			type: 'string',
			default: 'show',
		},
		purchaseRequirement: {
			type: 'string', // Type of purchase required.
			default: 'any',
		},
		content: {
			type: 'string',
		},
		timePassSelectionType: {
			type: 'string',
			default: 'none',
		},
		timePassIds: {
			type: 'string',
		},
		subscriptionSelectionType: {
			type: 'string',
		},
		subscriptionIds: {
			type: 'string',
		},
	},
	edit: Edit,
	save() {
		return null;
	},
} );
