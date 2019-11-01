import {__} from '@wordpress/i18n';
import {registerBlockType} from '@wordpress/blocks';

import Edit from './edit';

/**
 * Register Purchase Button Block.
 */
registerBlockType('laterpay/sub-pass-purchase-button', {
	title     : __('LaterPay Purchase Button', 'laterpay'),
	icon      : 'cart',
	category  : 'widgets',
	keywords: [
		__("TimePass Button", "laterpay"),
		__("Subscription Button", "laterpay"),
	],
	attributes: {
		alignment            : {
			type: 'string',
		},
		purchaseType         : {
			type   : 'string', // Select type of Purchase i.e Subscription / Time Pass.
			default: 'tp',
		},
		purchaseId           : {
			type: 'string', // Subscription / Time Pass ID.
		},
		buttonBackgroundColor: {
			type   : 'string', // Button background color.
			default: '#00aaa2',
		},
		buttonTextColor      : {
			type   : 'string', // Button text color.
			default: '#ffffff',
		},
		buttonText           : {
			type: 'string', // Button text.
		},
	},
	edit      : Edit,
	save() {
		return null;
	},
});
