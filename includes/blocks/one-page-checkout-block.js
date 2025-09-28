(function (blocks, element, components, blockEditor) {
    const { Fragment } = element;
    const { TextControl, SelectControl, RangeControl, PanelBody, TabPanel, ColorPicker, ToggleControl } = components;
    const { InspectorControls } = blockEditor;
    const el = element.createElement;
    
    blocks.registerBlockType('plugincy/one-page-checkout', {
        title: 'Multi Product One Page Checkout',
        icon: 'onepaquc_one_page_cart',
        category: 'plugincy',
        keywords: [
            'Checkout',
            'One Page Checkout',
            'Plugincy',
            'WooCommerce',
            'Products',
        ],
        attributes: {
            product_ids: {
                type: 'string',
                default: '',
            },
            category: {
                type: 'string',
                default: '',
            },
            tags: {
                type: 'string',
                default: '',
            },
            attribute: {
                type: 'string',
                default: '',
            },
            terms: {
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
                category,
                tags,
                attribute,
                terms,
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

            // Generate shortcode preview
            const generateShortcode = () => {
                let shortcode = '[plugincy_one_page_checkout';
                
                if (product_ids) shortcode += ` product_ids="${product_ids}"`;
                if (category) shortcode += ` category="${category}"`;
                if (tags) shortcode += ` tags="${tags}"`;
                if (attribute) shortcode += ` attribute="${attribute}"`;
                if (terms) shortcode += ` terms="${terms}"`;
                if (template) shortcode += ` template="${template}"`;
                
                shortcode += ']';
                return shortcode;
            };

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
                                    name: 'products',
                                    title: 'Product Quary',
                                    className: 'tab-products',
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
                                    { 
                                        title: 'Template Settings',
                                        initialOpen: true 
                                    },
                                    el(
                                        SelectControl,
                                        {
                                            label: 'Display Template',
                                            help: 'Choose how products will be displayed on the checkout page',
                                            value: template,
                                            options: templateOptions,
                                            onChange: (value) => setAttributes({ template: value }),
                                        }
                                    )
                                );
                            } else if (tab.name === 'products') {
                                return el(
                                    Fragment,
                                    {},
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'By Product IDs',
                                            initialOpen: true 
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product IDs',
                                                help: 'Enter specific product IDs separated by commas (e.g., 152,153,151,142)',
                                                placeholder: '152,153,151,142',
                                                value: product_ids,
                                                onChange: (value) => setAttributes({ product_ids: value }),
                                            }
                                        )
                                    ),
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'By Category & Tag',
                                            initialOpen: false 
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Categories',
                                                help: 'Enter category slugs separated by commas (e.g., electronics,clothing)',
                                                placeholder: 'electronics,clothing',
                                                value: category,
                                                onChange: (value) => setAttributes({ category: value }),
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Tags',
                                                help: 'Enter tag slugs separated by commas (e.g., featured,sale)',
                                                placeholder: 'featured,sale',
                                                value: tags,
                                                onChange: (value) => setAttributes({ tags: value }),
                                            }
                                        )
                                    ),
                                    el(
                                        PanelBody,
                                        { 
                                            title: ' By Attribute',
                                            initialOpen: false 
                                        },
                                        el(
                                            TextControl,
                                            {
                                                label: 'Product Attribute',
                                                help: 'Enter attribute name (e.g., color, size, brand)',
                                                placeholder: 'color',
                                                value: attribute,
                                                onChange: (value) => setAttributes({ attribute: value }),
                                            }
                                        ),
                                        el(
                                            TextControl,
                                            {
                                                label: 'Attribute Terms',
                                                help: 'Enter attribute terms separated by commas (e.g., red,blue,green)',
                                                placeholder: 'red,blue,green',
                                                value: terms,
                                                onChange: (value) => setAttributes({ terms: value }),
                                            }
                                        )
                                    )
                                );
                            } else if (tab.name === 'style') {
                                return el(
                                    Fragment,
                                    {},
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'Layout & Spacing',
                                            initialOpen: true 
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Border Radius',
                                                help: 'Adjust the rounded corners of elements',
                                                value: borderRadius,
                                                onChange: (value) => setAttributes({ borderRadius: value }),
                                                min: 0,
                                                max: 50,
                                                step: 1,
                                                marks: [
                                                    { value: 0, label: '0' },
                                                    { value: 25, label: '25' },
                                                    { value: 50, label: '50' }
                                                ],
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Element Spacing',
                                                help: 'Control the spacing between elements',
                                                value: spacing,
                                                onChange: (value) => setAttributes({ spacing: value }),
                                                min: 0,
                                                max: 50,
                                                step: 1,
                                                marks: [
                                                    { value: 0, label: '0' },
                                                    { value: 25, label: '25' },
                                                    { value: 50, label: '50' }
                                                ],
                                            }
                                        ),
                                        el(
                                            ToggleControl,
                                            {
                                                label: 'Enable Box Shadow',
                                                help: 'Add subtle shadow effects to elements',
                                                checked: boxShadow,
                                                onChange: () => setAttributes({ boxShadow: !boxShadow }),
                                            }
                                        )
                                    ),
                                    el(
                                        PanelBody,
                                        { 
                                            title: 'Colors & Buttons',
                                            initialOpen: false 
                                        },
                                        el(
                                            'div',
                                            { 
                                                className: 'plugincy-color-option',
                                                style: { marginBottom: '20px' }
                                            },
                                            el('label', { 
                                                style: { 
                                                    display: 'block', 
                                                    marginBottom: '8px',
                                                    fontWeight: '600'
                                                } 
                                            }, 'Primary Color'),
                                            el('p', { 
                                                style: { 
                                                    fontSize: '12px', 
                                                    color: '#666',
                                                    margin: '0 0 8px 0'
                                                } 
                                            }, 'Main theme color for buttons and highlights'),
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
                                            { 
                                                className: 'plugincy-color-option',
                                                style: { marginBottom: '20px' }
                                            },
                                            el('label', { 
                                                style: { 
                                                    display: 'block', 
                                                    marginBottom: '8px',
                                                    fontWeight: '600'
                                                } 
                                            }, 'Secondary Color'),
                                            el('p', { 
                                                style: { 
                                                    fontSize: '12px', 
                                                    color: '#666',
                                                    margin: '0 0 8px 0'
                                                } 
                                            }, 'Accent color for secondary elements'),
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
                                                help: 'Choose the appearance of checkout buttons',
                                                value: buttonStyle,
                                                options: buttonStyleOptions,
                                                onChange: (value) => setAttributes({ buttonStyle: value }),
                                            }
                                        )
                                    )
                                );
                            }
                        }
                    )
                ),
                // Block Preview
                el(
                    'div',
                    { 
                        className: 'plugincy-block-preview',
                        style: {
                            padding: '20px',
                            backgroundColor: '#f8f9fa',
                            border: '1px solid #e9ecef',
                            borderRadius: borderRadius + 'px',
                            margin: '20px 0',
                            boxShadow: boxShadow ? '0 2px 8px rgba(0,0,0,0.1)' : 'none'
                        }
                    },                    
                    el('div', {
                        style: {
                            backgroundColor: '#fff',
                            padding: '15px',
                            borderRadius: (borderRadius - 2) + 'px',
                            border: '1px solid #dee2e6',
                            fontSize: '13px',
                            fontFamily: 'monospace',
                            color: '#495057',
                            lineHeight: '1.4',
                            wordBreak: 'break-all'
                        }
                    }, generateShortcode()),                    
                )
            );
        },

        save: function(props) {
            const { attributes } = props;
            const { 
                product_ids, 
                category,
                tags,
                attribute,
                terms,
                template 
            } = attributes;

            // Generate the shortcode with current attributes
            let shortcode = '[plugincy_one_page_checkout';
            
            if (product_ids) shortcode += ` product_ids="${product_ids}"`;
            if (category) shortcode += ` category="${category}"`;
            if (tags) shortcode += ` tags="${tags}"`;
            if (attribute) shortcode += ` attribute="${attribute}"`;
            if (terms) shortcode += ` terms="${terms}"`;
            if (template) shortcode += ` template="${template}"`;
            
            shortcode += ']';

            // Return the shortcode as static content
            return shortcode;
        },
    });
})(
    window.wp.blocks,
    window.wp.element,
    window.wp.components,
    window.wp.blockEditor
);