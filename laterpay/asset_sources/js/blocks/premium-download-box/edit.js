import { __ } from '@wordpress/i18n';
import { Component, Fragment } from '@wordpress/element';
import { InspectorControls, MediaUploadCheck, MediaUpload } from '@wordpress/block-editor';
import { Button, PanelBody, ResponsiveWrapper, SelectControl, TextControl } from '@wordpress/components';

// Allowed type for Downloadable content.
const ALLOWED_MEDIA_TYPES = [
	'application/zip',
	'application/x-rar-compressed',
	'application/pdf',
	'image/jpeg',
	'image/png',
	'image/gif',
	'audio/vnd.wav',
	'audio/mpeg',
	'audio/mp4',
	'audio/ogg',
	'audio/aac',
	'audio/aacp',
	'video/mpeg',
	'video/mp4',
	'video/quicktime',
];

// Edit Component Class.
class Edit extends Component {
	render() {
		const {
			attributes: {
				mediaID,
				mediaIcon,
				mediaName,
				mediaHeading,
				mediaDescription,
				mediaType,
				mediaTeaserID,
				mediaTeaserImage,
			},
			setAttributes,
			className,
		} = this.props;

		const inspectorControls = (
			<InspectorControls>
				<PanelBody title={ __( 'Premium Download Box Settings', 'laterpay' ) }>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => {
								setAttributes( {
									mediaID: media.id,
									mediaIcon: media.type === 'image' ? media.sizes.thumbnail.url : '',
									mediaName: media.filename,
								} );
							} }
							allowedTypes={ ALLOWED_MEDIA_TYPES }
							value={ mediaID }
							render={ ( { open } ) => (
								<Button onClick={ open } className="is-button is-default is-large">
									{ __( 'Select Downloadable Media', 'laterpay' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ mediaIcon && (
						<ResponsiveWrapper
							naturalWidth={ 150 }
							naturalHeight={ 150 }
						>
							<img src={ mediaIcon } alt="" className="lp-premium-preview-img" />
						</ResponsiveWrapper>
					) }
					{
						mediaName && (
							<span className="lp-premium-preview-info">
								{ __( 'File Name: ', 'laterpay' ) + mediaName }
							</span>
						)
					}

					<TextControl
						label={ __( 'Heading text', 'laterpay' ) }
						help={ __( 'Enter heading text for download box.', 'laterpay' ) }
						value={ mediaHeading }
						onChange={ ( newMediaHeading ) => setAttributes( { mediaHeading: newMediaHeading } ) }
					/>

					<TextControl
						label={ __( 'Description text', 'laterpay' ) }
						help={ __( 'Enter description text for download box.', 'laterpay' ) }
						value={ mediaDescription }
						onChange={ ( newMediaDescription ) => setAttributes( { mediaDescription: newMediaDescription } ) }
					/>

					<SelectControl
						label={ __( 'Content Type', 'laterpay' ) }
						value={ mediaType }
						options={ [
							{ value: 'auto', label: __( 'Auto Identify', 'laterpay' ) },
							{ value: 'text', label: __( 'Text', 'laterpay' ) },
							{ value: 'audio', label: __( 'Audio', 'laterpay' ) },
							{ value: 'file', label: __( 'File', 'laterpay' ) },
							{ value: 'video', label: __( 'Video', 'laterpay' ) },
							{ value: 'gallery', label: __( 'Gallery', 'laterpay' ) },
						] }
						onChange={ ( newMediaType ) => setAttributes( { mediaType: newMediaType } ) }
					/>

					<MediaUploadCheck>
						<MediaUpload
							onSelect={ ( media ) => {
								setAttributes( {
									mediaTeaserID: media.id,
									mediaTeaserImage: media.url,
								} );
							} }
							allowedTypes={ [ 'image' ] }
							value={ mediaTeaserID }
							render={ ( { open } ) => (
								<Button onClick={ open } className="is-button is-default is-large">
									{ __( 'Select Teaser Image', 'laterpay' ) }
								</Button>
							) }
						/>
					</MediaUploadCheck>
					{ mediaTeaserImage && (
						<Fragment>
							<ResponsiveWrapper
								naturalWidth={ 150 }
								naturalHeight={ 150 }
							>
								<img src={ mediaTeaserImage } alt="" className="lp-premium-preview-img" />
							</ResponsiveWrapper>
							<Button onClick={ () => {
								setAttributes( { mediaTeaserID: '', mediaTeaserImage: '' } );
							} } className="is-button is-default is-large">
								{ __( 'Clear Teaser Image', 'laterpay' ) }
							</Button>
						</Fragment>
					) }
				</PanelBody>
			</InspectorControls>
		);

		// Set background-image or add content type based on selection.
		const backgroundStyles = mediaTeaserID ? { backgroundImage: `url(${ mediaTeaserImage })` } : {};
		const mediaClass = ( mediaTeaserID && 'auto' !== mediaType ) ? '' : `lp_is-${ mediaType }`;

		const blockOutput = (
			<div
				className={ [ className, 'lp_js_premium-file-box lp_premium-file-box', mediaClass ].join( ' ' ) }
				style={ backgroundStyles }>
				<div className="lp_premium-file-box__details">
					<h3 className="lp_premium-file-box__title">
						{ mediaHeading ? mediaHeading : __( 'Additional Premium Content', 'laterpay' ) }
					</h3>
					<p className="lp_premium-file-box__text">{ mediaDescription }</p>
				</div>
			</div>
		);

		return [
			inspectorControls,
			blockOutput,
		];
	}
}

export default Edit;
