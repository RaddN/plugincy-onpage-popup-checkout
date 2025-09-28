/* globals window */
(function (blocks, element, components, blockEditor) {
    const { createElement: el, Fragment } = element;
    const { PanelBody, ToggleControl, TextControl } = components;
    const { InspectorControls } = blockEditor;

    const BLOCK_NAME = 'wc/one-page-checkout';

    blocks.registerBlockType(BLOCK_NAME, {
        title: 'One-Page Checkout',
        icon: 'onepaquc_one_page_cart',
        category: 'plugincy',
        attributes: {
            auto_add:     { type: 'boolean', default: true },
            clear_cart:   { type: 'boolean', default: false },
            product_id:   { type: 'string',  default: '' }, // keep string to preserve empty state
            variation_id: { type: 'string',  default: '' },
            qty:          { type: 'number',  default: 1 }
        },

        edit: function ({ attributes, setAttributes }) {
            const { auto_add, clear_cart, product_id, variation_id, qty } = attributes;

            // Build a human-readable shortcode for the editor preview box
            const shortcode =
                '[onepaquc_checkout' +
                (auto_add ? '' : ' auto_add="no"') +
                (clear_cart ? ' clear_cart="yes"' : '') +
                (product_id ? ` product_id="${product_id}"` : '') +
                (variation_id ? ` variation_id="${variation_id}"` : '') +
                (qty && qty !== 1 ? ` qty="${qty}"` : '') +
                ']';

            // Small helpers to coerce numeric-only inputs (but still allow empty)
            const onlyDigitsOrEmpty = (val) => (val.replace ? val.replace(/[^\d]/g, '') : val);
            const toQty = (val) => {
                const num = parseInt(onlyDigitsOrEmpty(val), 10);
                return Number.isFinite(num) && num > 0 ? num : 1;
            };

            return el(
                Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: 'Checkout Settings', initialOpen: true },
                        el(ToggleControl, {
                            label: 'Auto add to cart',
                            checked: !!auto_add,
                            onChange: (v) => setAttributes({ auto_add: !!v })
                        }),
                        el(ToggleControl, {
                            label: 'Clear cart before adding',
                            checked: !!clear_cart,
                            onChange: (v) => setAttributes({ clear_cart: !!v })
                        }),
                        el(TextControl, {
                            label: 'Product ID',
                            type: 'text',
                            placeholder: 'e.g. 123',
                            value: product_id,
                            onChange: (v) => setAttributes({ product_id: onlyDigitsOrEmpty(v) })
                        }),
                        el(TextControl, {
                            label: 'Variation ID (optional)',
                            type: 'text',
                            placeholder: 'e.g. 456',
                            value: variation_id,
                            onChange: (v) => setAttributes({ variation_id: onlyDigitsOrEmpty(v) })
                        }),
                        el(TextControl, {
                            label: 'Quantity',
                            type: 'number',
                            min: 1,
                            value: qty,
                            onChange: (v) => setAttributes({ qty: toQty(v) })
                        })
                    )
                ),

                // Canvas preview (editor only)
                el(
                    'div',
                    {
                        className: 'onepaquc-opc-editor-preview',
                        style: {
                            border: '1px dashed #c3c4c7',
                            padding: '16px',
                            borderRadius: '6px',
                            background: '#fff'
                        }
                    },
                    el('h3', { style: { marginTop: 0, marginBottom: '6px' } }, 'Checkout (Preview)'),
                    el(
                        'p',
                        { style: { marginTop: 0, color: '#555' } },
                        'The checkout form will render on the front end when the cart is not empty.'
                    ),
                    el(
                        'div',
                        {
                            style: {
                                marginTop: '10px',
                                padding: '12px',
                                background: '#f6f7f7',
                                borderRadius: '4px',
                                border: '1px solid #e0e0e0',
                                fontFamily: 'monospace'
                            }
                        },
                        el('em', null, shortcode)
                    )
                )
            );
        },

        // Dynamic block: render via PHP (see render_callback below)
        save: function () { return null; }
    });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor);

