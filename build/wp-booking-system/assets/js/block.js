(function(blocks, element, components, blockEditor, i18n) {
	var el = element.createElement;
	var registerBlockType = blocks.registerBlockType;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var __ = i18n.__;

	registerBlockType('wp-booking-system/calendar', {
		title: __('Booking Calendar', 'wp-booking-system-luca'),
		icon: 'calendar-alt',
		category: 'widgets',
		attributes: {
			title: {
				type: 'string',
				default: __('Booking Calendar', 'wp-booking-system-luca')
			}
		},
		edit: function(props) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			return el(
				'div',
				{ className: 'wp-booking-system-calendar-block' },
				[
					el(
						InspectorControls,
						{},
						el(
							PanelBody,
							{
								title: __('Calendar Settings', 'wp-booking-system-luca'),
								initialOpen: true
							},
							el(TextControl, {
								label: __('Title', 'wp-booking-system-luca'),
								value: attributes.title,
								onChange: function(value) {
									setAttributes({ title: value });
								}
							})
						)
					),
					el(
						'div',
						{ className: 'wp-booking-system-calendar-preview', style: { padding: '20px', border: '1px dashed #ccc', borderRadius: '4px', textAlign: 'center' } },
						el('span', { className: 'dashicons dashicons-calendar-alt', style: { fontSize: '48px', color: '#8B0000', marginBottom: '10px', display: 'block' } }),
						el('h3', { style: { margin: '10px 0' } }, attributes.title || __('Booking Calendar', 'wp-booking-system-luca')),
						el('p', { style: { color: '#666', fontStyle: 'italic', margin: 0 } }, 
							__('The booking calendar will appear here on the frontend.', 'wp-booking-system-luca')
						)
					)
				]
			);
		},
		save: function() {
			// Render is handled server-side.
			return null;
		}
	});
})(
	window.wp.blocks,
	window.wp.element,
	window.wp.components,
	window.wp.blockEditor,
	window.wp.i18n
);
