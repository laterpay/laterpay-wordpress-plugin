import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Icon from '../icons';
import Edit from './edit';

/**
 * Register Premium Download Box Block.
 */
registerBlockType( 'laterpay/premium-download-box', {
	title: __( 'LaterPay Premium Download Box', 'laterpay' ),
	icon: Icon.premiumBox,
	category: 'laterpay-blocks',
	keywords: [
		__( 'Premium Download Box', 'laterpay' ),
	],
	attributes: {
		mediaID: {
			type: 'integer',
			default: 0,
		},
		mediaIcon: {
			type: 'string',
		},
		mediaName: {
			type: 'string',
		},
		mediaHeading: {
			type: 'string',
			default: __( 'Additional Premium Content', 'laterpay' ),
		},
		mediaDescription: {
			type: 'string',
		},
		mediaType: {
			type: 'string',
			default: 'auto',
		},
		mediaTeaserID: {
			type: 'integer',
			default: 0,
		},
		mediaTeaserImage: {
			type: 'string',
		},
	},
	edit: Edit,
	save() {
		return null;
	},
} );
