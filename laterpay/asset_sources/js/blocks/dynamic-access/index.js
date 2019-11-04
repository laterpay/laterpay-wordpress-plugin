import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

/**
 * Register Dynamic Access Block.
 */
registerBlockType( 'laterpay/dynamic-access', {
	title: __( 'LaterPay Dynamic Access', 'laterpay' ),
	icon: 'unlock',
	category: 'laterpay-blocks',
	keywords: [
		__( 'TimePass Access', 'laterpay' ),
		__( 'Subscription Access', 'laterpay' ),
	],
	attributes: {
		accessBehaviour: {
			type: 'string',
			default: 'show',
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
