/**
 * Premium Download Box Block Registration.
 */

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';

import Icon from '../icons';
import Edit from './edit';

/**
 * Register Premium Download Box Block.
 */
registerBlockType( 'laterpay/premium-download-box', {
	title: __( 'Laterpay Premium Download Box', 'laterpay' ),
	icon: Icon.premiumBox,
	category: 'laterpay-blocks',
	keywords: [
		__( 'Premium Download Box', 'laterpay' ),
	],
	attributes: {
		mediaID: {
			type: 'integer', // Selected Media ID.
			default: 0,
		},
		mediaIcon: {
			type: 'string', // Preview of selected media if image.
		},
		mediaName: {
			type: 'string', // Selected media name.
		},
		mediaHeading: {
			type: 'string', // Premium Download Box Heading.
			default: __( 'Additional Premium Content', 'laterpay' ),
		},
		mediaDescription: {
			type: 'string', // Premium Download Box Description.
		},
		mediaType: {
			type: 'string', // Type of downloadable content.
			default: 'auto',
		},
		mediaTeaserID: {
			type: 'integer', // Teaser media ID if selected.
			default: 0,
		},
		mediaTeaserImage: {
			type: 'string', // Preview for selected Teaser media.
		},
		mediaPrice: {
			type: 'string', // Media Pricing.
		},
	},
	edit: Edit,
	save() {
		return null;
	},
} );
