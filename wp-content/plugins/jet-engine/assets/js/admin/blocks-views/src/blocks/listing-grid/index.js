import GroupedSelectControl from "components/grouped-select-control.js";

const { __ } = wp.i18n;
const {
	registerBlockType
} = wp.blocks;
const {
	InspectorControls,
	ColorPalette,
	RichText,
	Editable,
	MediaUpload,
	ServerSideRender
} = wp.editor;
const {
	PanelColor,
	IconButton,
	TextControl,
	TextareaControl,
	SelectControl,
	ToggleControl,
	PanelBody,
	RangeControl,
	CheckboxControl,
	ExternalLink,
	Disabled,
	G,
	Path,
	Circle,
	Rect,
	SVG
} = wp.components;

const GIcon = <SVG width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg"><Rect x="1" y="16" width="18" height="18" rx="3" fill="#6F8BFF" stroke="#162B40" stroke-width="2"></Rect><Rect x="2" y="38" width="16" height="2" rx="1" fill="#162B40"></Rect><Rect x="2" y="42" width="16" height="2" rx="1" fill="#162B40"></Rect><path d="M2 47C2 46.4477 2.44772 46 3 46H9C9.55228 46 10 46.4477 10 47C10 47.5523 9.55228 48 9 48H3C2.44772 48 2 47.5523 2 47Z" fill="#162B40"></path><Rect x="24" y="38" width="16" height="2" rx="1" fill="#162B40"></Rect><Rect x="24" y="42" width="16" height="2" rx="1" fill="#162B40"></Rect><path d="M24 47C24 46.4477 24.4477 46 25 46H31C31.5523 46 32 46.4477 32 47C32 47.5523 31.5523 48 31 48H25C24.4477 48 24 47.5523 24 47Z" fill="#162B40"></path><Rect x="46" y="38" width="16" height="2" rx="1" fill="#162B40"></Rect><Rect x="46" y="42" width="16" height="2" rx="1" fill="#162B40"></Rect><path d="M46 47C46 46.4477 46.4477 46 47 46H53C53.5523 46 54 46.4477 54 47C54 47.5523 53.5523 48 53 48H47C46.4477 48 46 47.5523 46 47Z" fill="#162B40"></path><Rect x="23" y="16" width="18" height="18" rx="3" fill="white" stroke="#162B40" stroke-width="2"></Rect><Rect x="45" y="16" width="18" height="18" rx="3" fill="white" stroke="#162B40" stroke-width="2"></Rect></SVG>;

registerBlockType( 'jet-engine/listing-grid', {
	title: __( 'Listing Grid' ),
	icon: GIcon,
	category: 'layout',
	attributes: {
		lisitng_id: {
			type: 'string',
			default: '',
		},
		columns: {
			type: 'number',
			default: 3,
		},
		columns_tablet: {
			type: 'number',
			default: 3,
		},
		columns_mobile: {
			type: 'number',
			default: 1,
		},
		is_archive_template: {
			type: 'boolean',
			default: false,
		},
		post_status: {
			type: 'array',
			default: ['publish'],
		},
		posts_num: {
			type: 'number',
			default: 6,
		},
		not_found_message: {
			type: 'string',
			default: __( 'No data was found' ),
		},
		custom_posts_query: {
			type: 'string',
			default: '',
		},
		hide_widget_if: {
			type: 'string',
			default: '',
		},
	},
	className: 'jet-listing-grid',
	edit: class extends wp.element.Component {
		render() {

			const props          = this.props;
			const attributes     = props.attributes;
			const listingOptions = window.JetEngineListingData.listingOptions;
			const hideOptions    = window.JetEngineListingData.hideOptions;
			const conditions     = {}

			function inArray( needle, highstack ) {
				return 0 <= highstack.indexOf( needle );
			};

			return [
				props.isSelected && (
					<InspectorControls
						key={ 'inspector' }
					>
						<PanelBody title={ __( 'General' ) }>
							<SelectControl
								label={ __( 'Listing' ) }
								value={ attributes.lisitng_id }
								options={ listingOptions }
								onChange={ newValue => {
									props.setAttributes( { lisitng_id: newValue } );
								}}
							/>
							<TextControl
								type="number"
								label={ __( 'Columns Number' ) }
								value={ attributes.columns }
								min={ `0` }
								max={ `6` }
								onChange={ newValue => {
									props.setAttributes( { columns: Number(newValue) } );
								} }
							/>
							<TextControl
								type="number"
								label={ __( 'Columns Number(Tablet)' ) }
								value={ attributes.columns_tablet }
								min={ `0` }
								max={ `6` }
								onChange={ newValue => {
									props.setAttributes( { columns_tablet: Number(newValue) } );
								} }
							/>
							<TextControl
								type="number"
								label={ __( 'Columns Number(Mobile)' ) }
								value={ attributes.columns_mobile }
								min={ `0` }
								max={ `6` }
								onChange={ newValue => {
									props.setAttributes( { columns_mobile: Number(newValue) } );
								} }
							/>
							<ToggleControl
								label={ __( 'Use as Archive Template' ) }
								checked={ attributes.is_archive_template }
								onChange={ () => {
									props.setAttributes({ is_archive_template: ! attributes.is_archive_template });
								} }
							/>
							<SelectControl
								multiple={true}
								label={ __( 'Status' ) }
								value={ attributes.post_status }
								options={ [
									{
										value: 'publish',
										label: __( 'Publish' ),
									},
									{
										value: 'future',
										label: __( 'Future' ),
									},
									{
										value: 'draft',
										label: __( 'Draft' ),
									},
									{
										value: 'pending',
										label: __( 'Pending Review' ),
									},
									{
										value: 'private',
										label: __( 'Private' ),
									},
								] }
								onChange={ newValue => {
									props.setAttributes( { post_status: newValue } );
								}}
							/>
							<TextControl
								type="number"
								label={ __( 'Posts number' ) }
								value={ attributes.posts_num }
								min={ `1` }
								max={ `1000` }
								onChange={ newValue => {
									props.setAttributes( { posts_num: Number(newValue) } );
								} }
							/>
							<TextControl
								type="text"
								label={ __( 'Not found message' ) }
								value={ attributes.not_found_message }
								onChange={ newValue => {
									props.setAttributes( { not_found_message: newValue } );
								} }
							/>
						</PanelBody>
						<PanelBody
							title={ __( 'Query Settings' ) }
							initialOpen={ false }
						>
							<TextareaControl
								type="text"
								label={ __( 'Set Posts Query' ) }
								value={ attributes.custom_posts_query }
								onChange={ newValue => {
									props.setAttributes( { custom_posts_query: newValue } );
								} }
							/>

							<p>
								<ExternalLink href="https://crocoblock.com/wp-query-generator/">{ __( 'Generate Posts Query' ) }</ExternalLink>
							</p>
							<p>
								<ExternalLink href="https://crocoblock.com/knowledge-base/articles/jetengine-macros-guide/">{ __( 'Macros Guide' ) }</ExternalLink>
							</p>
						</PanelBody>
						<PanelBody
							title={ __( 'Block Visibility' ) }
							initialOpen={ false }
						>
							<SelectControl
								label={ __( 'Hide block if' ) }
								value={ attributes.hide_widget_if }
								options={ hideOptions }
								onChange={ newValue => {
									props.setAttributes( { hide_widget_if: newValue } );
								}}
							/>
						</PanelBody>
					</InspectorControls>
				),
				<Disabled>
					<ServerSideRender
						block="jet-engine/listing-grid"
						attributes={ attributes }
					/>
				</Disabled>
			];
		}
	},
	save: props => {
		return null;
	}
} );