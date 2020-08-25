/**
 * Purchase Button Block Registration.
 */

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Icons from '../icons';
import Edit from './edit';

/**
 * Register Purchase Button Block.
 */
registerBlockType( 'laterpay/sub-pass-purchase-button', {
	title: __( 'Laterpay Purchase Button', 'laterpay' ),
	icon: Icons.logo,
	category: 'laterpay-blocks',
	keywords: [
		__( 'Time Pass Button', 'laterpay' ),
		__( 'Subscription Button', 'laterpay' ),
	],
	attributes: {
		alignment: {
			type: 'string', // Select button alignment type..
			default: 'left',
		},
		purchaseType: {
			type: 'string', // Select type of Purchase i.e Subscription / Time Pass.
			default: 'tp',
		},
		purchaseId: {
			type: 'string', // Subscription / Time Pass ID.
		},
		buttonBackgroundColor: {
			type: 'string', // Button background color.
			default: '#00aaa2',
		},
		buttonTextColor: {
			type: 'string', // Button text color.
			default: '#ffffff',
		},
		buttonText: {
			type: 'string', // Button text.
		},
	},
	edit: Edit,
	save() {
		return null;
	},
} );
