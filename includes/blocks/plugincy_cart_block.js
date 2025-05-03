(function (blocks, element, components, blockEditor) {
    const { Fragment } = element;
    const { TextControl, SelectControl, RangeControl, PanelBody, TabPanel, ColorPicker } = components;
    const { InspectorControls } = blockEditor;
    const el = element.createElement;

    blocks.registerBlockType('wc/checkout-block', {
        title: 'Plugincy Cart',
        icon: 'onepaquc_cart',
        category: 'plugincy',
        attributes: {
            // General
            cartIcon: {
                type: 'string',
                default: 'cart'
            },
            drawerPosition: {
                type: 'string',
                default: 'right'
            },
            productTitleTag: {
                type: 'string',
                default: 'h4'
            },
            // Style - Cart
            cartIconSize: {
                type: 'number',
                default: 24
            },
            cartIconColor: {
                type: 'string',
                default: '#000000'
            },
            // Style - Drawer
            drawerWidth: {
                type: 'number',
                default: 400
            },
            drawerBackground: {
                type: 'string',
                default: '#ffffff'
            },
            drawerPadding: {
                type: 'number',
                default: 20
            },
            drawerMargin: {
                type: 'number',
                default: 0
            },
            // Style - Product Image
            productImageWidth: {
                type: 'number',
                default: 80
            },
            productImageHeight: {
                type: 'number',
                default: 80
            },
            // Style - Product Title
            productTitleFontSize: {
                type: 'number',
                default: 16
            },
            productTitleLineHeight: {
                type: 'number',
                default: 1.5
            },
            productTitleColor: {
                type: 'string',
                default: '#333333'
            },
            // Style - Product Price
            productPriceFontSize: {
                type: 'number',
                default: 14
            },
            productPriceLineHeight: {
                type: 'number',
                default: 1.4
            },
            productPriceColor: {
                type: 'string',
                default: '#666666'
            },
            // Style - Quantity
            quantityWidth: {
                type: 'number',
                default: 80
            },
            quantityHeight: {
                type: 'number',
                default: 40
            },
            quantityPadding: {
                type: 'number',
                default: 5
            },
            // Style - Remove Button
            removeButtonSize: {
                type: 'number',
                default: 16
            },
            removeButtonPadding: {
                type: 'number',
                default: 5
            },
            // Style - Subtotal
            subtotalFontSize: {
                type: 'number',
                default: 18
            },
            subtotalLineHeight: {
                type: 'number',
                default: 1.5
            },
            subtotalColor: {
                type: 'string',
                default: '#333333'
            },
            subtotalPadding: {
                type: 'number',
                default: 10
            },
            subtotalMargin: {
                type: 'number',
                default: 20
            },
            // Style - Checkout Button
            checkoutBtnBackground: {
                type: 'string',
                default: '#333333'
            },
            checkoutBtnFontSize: {
                type: 'number',
                default: 16
            },
            checkoutBtnLineHeight: {
                type: 'number',
                default: 1.5
            },
            checkoutBtnColor: {
                type: 'string',
                default: '#ffffff'
            }
        },
        
        edit: function (props) {
            const { attributes, setAttributes } = props;
            
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
                                }
                            ]
                        },
                        (tab) => {
                            if (tab.name === 'general') {
                                return el(
                                    Fragment,
                                    {},
                                    el(
                                        PanelBody,
                                        {
                                            title: 'General Settings',
                                            initialOpen: true
                                        },
                                        el(
                                            SelectControl,
                                            {
                                                label: 'Cart Icon',
                                                value: attributes.cartIcon,
                                                options: [
                                                    { label: 'Cart', value: 'cart' },
                                                    { label: 'Shopping Bag', value: 'shopping-bag' },
                                                    { label: 'Basket', value: 'basket' }
                                                ],
                                                onChange: (value) => setAttributes({ cartIcon: value })
                                            }
                                        ),
                                        el(
                                            SelectControl,
                                            {
                                                label: 'Drawer Position',
                                                value: attributes.drawerPosition,
                                                options: [
                                                    { label: 'Left', value: 'left' },
                                                    { label: 'Right', value: 'right' }
                                                ],
                                                onChange: (value) => setAttributes({ drawerPosition: value })
                                            }
                                        ),
                                        el(
                                            SelectControl,
                                            {
                                                label: 'Product Title Tag',
                                                value: attributes.productTitleTag,
                                                options: [
                                                    { label: 'H1', value: 'h1' },
                                                    { label: 'H2', value: 'h2' },
                                                    { label: 'H3', value: 'h3' },
                                                    { label: 'H4', value: 'h4' },
                                                    { label: 'H5', value: 'h5' },
                                                    { label: 'H6', value: 'h6' },
                                                    { label: 'P', value: 'p' },
                                                    { label: 'DIV', value: 'div' }
                                                ],
                                                onChange: (value) => setAttributes({ productTitleTag: value })
                                            }
                                        )
                                    )
                                );
                            } else if (tab.name === 'style') {
                                return el(
                                    Fragment,
                                    {},
                                    // Cart Icon Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Cart Icon',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Icon Size',
                                                value: attributes.cartIconSize,
                                                min: 12,
                                                max: 50,
                                                onChange: (value) => setAttributes({ cartIconSize: value })
                                            }
                                        ),
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.cartIconColor,
                                                    onChangeComplete: (value) => setAttributes({ cartIconColor: value.hex })
                                                }
                                            )
                                        )
                                    ),
                                    
                                    // Drawer Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Drawer',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Width (px)',
                                                value: attributes.drawerWidth,
                                                min: 200,
                                                max: 800,
                                                onChange: (value) => setAttributes({ drawerWidth: value })
                                            }
                                        ),
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Background Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.drawerBackground,
                                                    onChangeComplete: (value) => setAttributes({ drawerBackground: value.hex })
                                                }
                                            )
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Padding (px)',
                                                value: attributes.drawerPadding,
                                                min: 0,
                                                max: 50,
                                                onChange: (value) => setAttributes({ drawerPadding: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Margin (px)',
                                                value: attributes.drawerMargin,
                                                min: 0,
                                                max: 50,
                                                onChange: (value) => setAttributes({ drawerMargin: value })
                                            }
                                        )
                                    ),
                                    
                                    // Product Image Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Product Image',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Width (px)',
                                                value: attributes.productImageWidth,
                                                min: 40,
                                                max: 200,
                                                onChange: (value) => setAttributes({ productImageWidth: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Height (px)',
                                                value: attributes.productImageHeight,
                                                min: 40,
                                                max: 200,
                                                onChange: (value) => setAttributes({ productImageHeight: value })
                                            }
                                        )
                                    ),
                                    
                                    // Product Title Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Product Title',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Font Size (px)',
                                                value: attributes.productTitleFontSize,
                                                min: 10,
                                                max: 30,
                                                onChange: (value) => setAttributes({ productTitleFontSize: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Line Height',
                                                value: attributes.productTitleLineHeight,
                                                min: 1,
                                                max: 3,
                                                step: 0.1,
                                                onChange: (value) => setAttributes({ productTitleLineHeight: value })
                                            }
                                        ),
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.productTitleColor,
                                                    onChangeComplete: (value) => setAttributes({ productTitleColor: value.hex })
                                                }
                                            )
                                        )
                                    ),
                                    
                                    // Product Price Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Product Price',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Font Size (px)',
                                                value: attributes.productPriceFontSize,
                                                min: 10,
                                                max: 30,
                                                onChange: (value) => setAttributes({ productPriceFontSize: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Line Height',
                                                value: attributes.productPriceLineHeight,
                                                min: 1,
                                                max: 3,
                                                step: 0.1,
                                                onChange: (value) => setAttributes({ productPriceLineHeight: value })
                                            }
                                        ),
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.productPriceColor,
                                                    onChangeComplete: (value) => setAttributes({ productPriceColor: value.hex })
                                                }
                                            )
                                        )
                                    ),
                                    
                                    // Quantity Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Quantity',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Width (px)',
                                                value: attributes.quantityWidth,
                                                min: 40,
                                                max: 150,
                                                onChange: (value) => setAttributes({ quantityWidth: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Height (px)',
                                                value: attributes.quantityHeight,
                                                min: 20,
                                                max: 80,
                                                onChange: (value) => setAttributes({ quantityHeight: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Padding (px)',
                                                value: attributes.quantityPadding,
                                                min: 0,
                                                max: 20,
                                                onChange: (value) => setAttributes({ quantityPadding: value })
                                            }
                                        )
                                    ),
                                    
                                    // Remove Button Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Remove Button',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Size (px)',
                                                value: attributes.removeButtonSize,
                                                min: 10,
                                                max: 30,
                                                onChange: (value) => setAttributes({ removeButtonSize: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Padding (px)',
                                                value: attributes.removeButtonPadding,
                                                min: 0,
                                                max: 20,
                                                onChange: (value) => setAttributes({ removeButtonPadding: value })
                                            }
                                        )
                                    ),
                                    
                                    // Subtotal Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Subtotal',
                                            initialOpen: false
                                        },
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Font Size (px)',
                                                value: attributes.subtotalFontSize,
                                                min: 12,
                                                max: 36,
                                                onChange: (value) => setAttributes({ subtotalFontSize: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Line Height',
                                                value: attributes.subtotalLineHeight,
                                                min: 1,
                                                max: 3,
                                                step: 0.1,
                                                onChange: (value) => setAttributes({ subtotalLineHeight: value })
                                            }
                                        ),
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.subtotalColor,
                                                    onChangeComplete: (value) => setAttributes({ subtotalColor: value.hex })
                                                }
                                            )
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Padding (px)',
                                                value: attributes.subtotalPadding,
                                                min: 0,
                                                max: 40,
                                                onChange: (value) => setAttributes({ subtotalPadding: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Margin (px)',
                                                value: attributes.subtotalMargin,
                                                min: 0,
                                                max: 40,
                                                onChange: (value) => setAttributes({ subtotalMargin: value })
                                            }
                                        )
                                    ),
                                    
                                    // Checkout Button Styles
                                    el(
                                        PanelBody,
                                        {
                                            title: 'Checkout Button',
                                            initialOpen: false
                                        },
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Background Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.checkoutBtnBackground,
                                                    onChangeComplete: (value) => setAttributes({ checkoutBtnBackground: value.hex })
                                                }
                                            )
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Font Size (px)',
                                                value: attributes.checkoutBtnFontSize,
                                                min: 12,
                                                max: 24,
                                                onChange: (value) => setAttributes({ checkoutBtnFontSize: value })
                                            }
                                        ),
                                        el(
                                            RangeControl,
                                            {
                                                label: 'Line Height',
                                                value: attributes.checkoutBtnLineHeight,
                                                min: 1,
                                                max: 3,
                                                step: 0.1,
                                                onChange: (value) => setAttributes({ checkoutBtnLineHeight: value })
                                            }
                                        ),
                                        el(
                                            'div',
                                            { className: 'color-control' },
                                            el('label', {}, 'Text Color'),
                                            el(
                                                ColorPicker,
                                                {
                                                    color: attributes.checkoutBtnColor,
                                                    onChangeComplete: (value) => setAttributes({ checkoutBtnColor: value.hex })
                                                }
                                            )
                                        )
                                    )
                                );
                            }
                        }
                    )
                ),
                el(
                    'div',
                    { className: 'plugincy-cart-block-preview' },
                    el(
                        'p',
                        {},
                        `[plugincy_cart drawer="${attributes.drawerPosition}" cart_icon="${attributes.cartIcon}" product_title_tag="${attributes.productTitleTag}"]`
                    )
                )
            );
        },
        
        save: function () {
            return "[woocommerce_checkout]"; // Will be replaced by the shortcode processor
        },
    });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor);