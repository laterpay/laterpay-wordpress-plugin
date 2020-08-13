/**
 * Dynamic Access Block Registration.
 */

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { Fragment } from '@wordpress/element';
import Icon from '../icons';
import Edit from './edit';

// Cusom Block Description.
const blockDescription = (
	<Fragment>
		<p>
			{ __( 'Use this block to show or hide the content in the block based on which Laterpay product the user has purchased.', 'laterpay' ) }
		</p>
		<p>
			{ __( 'IMPORTANT: This should not be used to hide the content you would like behind the paywall. That will be done automatically once you have set a price for this article.', 'laterpay' ) }
		</p>
	</Fragment>
);

/**
 * Register Dynamic Access Block.
 */
registerBlockType( 'laterpay/dynamic-access', {
	title: __( 'Laterpay Dynamic Access', 'laterpay' ),
	icon: Icon.dynamicAccess,
	category: 'laterpay-blocks',
	description: blockDescription,
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
