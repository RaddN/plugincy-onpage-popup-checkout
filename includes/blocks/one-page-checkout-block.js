(function (blocks, element, components, blockEditor) {
    const { Fragment } = element;
    const { TextControl, SelectControl, RangeControl, PanelBody, TabPanel, ColorPicker, ToggleControl } = components;
    const { InspectorControls } = blockEditor;
    const el = element.createElement;
    
    blocks.registerBlockType('plugincy/one-page-checkout', {
        title: 'Plugincy One Page Checkout',
        icon: 'onepaquc_one_page_cart',
        category: 'plugincy',
        keywords: [
            'Checkout',
            'One Page Checkout',
            'Plugincy',
        ],
        attributes: {
            product_ids: {
                type: 'string',
                default: '',
            },
            template: {
                type: 'string',
                default: 'product-tabs',
            },
            // Style attributes
            borderRadius: {
                type: 'number',
                default: 4,
            },
            boxShadow: {
                type: 'boolean',
                default: false,
            },
            primaryColor: {
                type: 'string',
                default: '#4CAF50',
            },
            secondaryColor: {
                type: 'string',
                default: '#2196F3',
            },
            buttonStyle: {
                type: 'string',
                default: 'filled',
            },
            spacing: {
                type: 'number',
                default: 15,
            },
        },

        edit: function(props) {
            const { attributes, setAttributes } = props;
            const { 
                product_ids, 
                template, 
                borderRadius, 
                boxShadow, 
                primaryColor, 
                secondaryColor, 
                buttonStyle, 
                spacing 
            } = attributes;

            // Template options
            const templateOptions = [
                { label: 'Product Table', value: 'product-table' },
                { label: 'Product List', value: 'product-list' },
                { label: 'Product Single', value: 'product-single' },
                { label: 'Product Slider', value: 'product-slider' },
                { label: 'Product Accordion', value: 'product-accordion' },
                { label: 'Product Tabs', value: 'product-tabs' },
                { label: 'Pricing Table', value: 'pricing-table' },
            ];

            // Button style options
            const buttonStyleOptions = [
                { label: 'Filled', value: 'filled' },
                { label: 'Outlined', value: 'outlined' },
                { label: 'Text Only', value: 'text' },
            ];

            return el(
                Fragment,
                {},
                el(
                    InspectorControls,
                    {},
                    el(
                        TabPanel,
                        {
                            className: 'plugincy-tabs',
                            activeClass: 'active-tab',
                            tabs: [
                                {
                                    name: 'general',
                                    title: 'General',
                                    className: 'tab-general',
                                },
                                {
                                    name: 'style',
                                    title: 'Style',
                                    className: 'tab-style',
                                },
                            ],
                        },
                        (tab) => {
                            if (tab.name === 'general') {
                                return el(
                                    PanelBody,
                                    { initialOpen: true },
                                    el(
                                        TextControl,
                                        {
                                            label: 'Product IDs',
                                            help: 'Enter product IDs separated by commas (e.g., 152,153,151,142)',
                                            value: product_ids,
                                            onChange: (value) => setAttributes({ product_ids: value }),
                                        }
                                    ),
                                    el(
                                        SelectControl,
                                        {
                                            label: 'Template',
                                            value: template,
                                            options: templateOptions,
                                            onChange: (value) => setAttributes({ template: value }),
                                        }
                                    )
                                );
                            } else if (tab.name === 'style') {
                                return el(
                                    PanelBody,
                                    { initialOpen: true },
                                    el(
                                        RangeControl,
                                        {
                                            label: 'Border Radius (px)',
                                            value: borderRadius,
                                            onChange: (value) => setAttributes({ borderRadius: value }),
                                            min: 0,
                                            max: 50,
                                        }
                                    ),
                                    el(
                                        ToggleControl,
                                        {
                                            label: 'Enable Box Shadow',
                                            checked: boxShadow,
                                            onChange: () => setAttributes({ boxShadow: !boxShadow }),
                                        }
                                    ),
                                    el(
                                        'div',
                                        { className: 'plugincy-color-option' },
                                        el('label', {}, 'Primary Color'),
                                        el(
                                            ColorPicker,
                                            {
                                                color: primaryColor,
                                                onChangeComplete: (value) => setAttributes({ primaryColor: value.hex }),
                                                disableAlpha: true,
                                            }
                                        )
                                    ),
                                    el(
                                        'div',
                                        { className: 'plugincy-color-option' },
                                        el('label', {}, 'Secondary Color'),
                                        el(
                                            ColorPicker,
                                            {
                                                color: secondaryColor,
                                                onChangeComplete: (value) => setAttributes({ secondaryColor: value.hex }),
                                                disableAlpha: true,
                                            }
                                        )
                                    ),
                                    el(
                                        SelectControl,
                                        {
                                            label: 'Button Style',
                                            value: buttonStyle,
                                            options: buttonStyleOptions,
                                            onChange: (value) => setAttributes({ buttonStyle: value }),
                                        }
                                    ),
                                    el(
                                        RangeControl,
                                        {
                                            label: 'Element Spacing (px)',
                                            value: spacing,
                                            onChange: (value) => setAttributes({ spacing: value }),
                                            min: 0,
                                            max: 50,
                                        }
                                    )
                                );
                            }
                        }
                    )
                ),
                // Block Preview
                el(
                    'div',
                    { className: 'plugincy-block-preview' },
                    el('p', { className: 'plugincy-shortcode-preview' },
                        '[plugincy_one_page_checkout product_ids="' + product_ids + '" template="' + template + '"]'
                    )
                )
            );
        },

        save: function() {
            // Return null as this is a dynamic block
            // The rendering will be handled by PHP in the server
            return '[plugincy_one_page_checkout product_ids="" template=""]';
        },
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);

