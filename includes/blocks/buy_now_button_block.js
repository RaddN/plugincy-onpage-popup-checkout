/* globals window */
(function (blocks, element, components, blockEditor) {
    const { createElement: el, Fragment } = element;
    const {
        TextControl,
        ToggleControl,
        SelectControl,
        PanelBody,
        TabPanel,
        __experimentalNumberControl: NumberControl
    } = components;
    const { InspectorControls } = blockEditor;

    // --- Helpers -------------------------------------------------------------

    const ICONS = {
        cart: `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
     viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon">
  <circle cx="9" cy="21" r="1"></circle>
  <circle cx="20" cy="21" r="1"></circle>
  <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
</svg>`,
        checkout: `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
     viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon">
  <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
  <line x1="1" y1="10" x2="23" y2="10"></line>
</svg>`,
        arrow: `
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
     viewBox="0 0 24 24" fill="none" stroke="currentColor"
     stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="rmenu-icon">
  <line x1="5" y1="12" x2="19" y2="12"></line>
  <polyline points="12 5 19 12 12 19"></polyline>
</svg>`
    };

    function parseInlineStyle(str) {
        if (!str) return {};
        const map = {
            'background-color': 'backgroundColor',
            'border-color': 'borderColor',
            'border-radius': 'borderRadius',
            'text-align': 'textAlign',
            'color': 'color',
            'padding': 'padding',
            'margin': 'margin',
            'display': 'display',
            'gap': 'gap'
        };
        const out = {};
        str.split(';').forEach(rule => {
            const [kRaw, vRaw] = rule.split(':');
            if (!kRaw || !vRaw) return;
            const k = kRaw.trim().toLowerCase();
            const v = vRaw.trim();
            const key = map[k];
            if (key) out[key] = v;
        });
        return out;
    }

    function buttonStyleFromAttributes(attrs) {
        // Base visual so it looks like a button in the editor
        const base = {
            cursor: 'pointer',
            textAlign: 'center',
            display: 'inline-flex',
            alignItems: 'center',
            gap: '6px',
            padding: '10px 14px',
            borderRadius: '4px',
            textDecoration: 'none',
            borderWidth: '1px',
            borderStyle: 'solid',
            backgroundColor: '#000000',
            color: '#ffffff',
            borderColor: '#000000'
        };

        // Icon layout tweaks
        const pos = attrs.icon_position || 'left';
        if (pos === 'top') base.flexDirection = 'column';
        else if (pos === 'bottom') base.flexDirection = 'column-reverse';
        else base.flexDirection = 'row'; // left/right handled by children order

        // Merge user inline style (support a few common properties)
        const user = parseInlineStyle(attrs.style);
        return Object.assign(base, user);
    }

    function getIconHTML(attrs) {
        const type = (attrs.icon === '' ? 'cart' : attrs.icon); // fallback to 'cart' if using plugin default
        if (type === 'none' || !ICONS[type]) return '';
        return ICONS[type];
    }

    function makePreviewButton(attrs, setAttributes) {
        const iconHTML = getIconHTML(attrs);
        const label = attrs.text && attrs.text.trim() ? attrs.text : 'Buy Now';
        const pos = attrs.icon_position || 'left';
        const cls =
            'button single_add_to_cart_button direct-checkout-button opqcfw-btn alt-style onepaquc-checkout-btn' +
            ' ' + (pos ? `icon-position-${pos}` : 'icon-position-left') +
            (attrs.class ? ` ${attrs.class}` : '');

        const styleObj = buttonStyleFromAttributes(attrs);

        // Data attributes for parity with front-end structure (for feel; no real action in editor)
        const dataAttrs = {
            'data-product-id': attrs.product_id || 123,
            'data-product-type': (attrs.variation_id || attrs.detect_variation) ? 'variable' : 'simple',
            'data-quantity': Math.max(1, parseInt(attrs.qty || 1, 10)),
            'data-title': 'Preview Product'
        };
        if (attrs.variation_id) dataAttrs['data-variation-id'] = attrs.variation_id;

        // Build children order based on icon position
        const iconSpan = iconHTML
            ? el('span', { className: 'onepaquc-icon', dangerouslySetInnerHTML: { __html: iconHTML } })
            : null;
        const textSpan = el('span', { className: 'onepaquc-label' }, label);

        let children;
        if (pos === 'right') children = [textSpan, iconSpan].filter(Boolean);
        else if (pos === 'top' || pos === 'bottom') children = [iconSpan, textSpan].filter(Boolean);
        else children = [iconSpan, textSpan].filter(Boolean); // left/default

        return el(
            'a',
            Object.assign(
                {
                    href: '#checkout-popup',
                    className: cls,
                    style: styleObj,
                    onClick: (e) => e.preventDefault(), // don’t navigate in editor
                    title: 'Preview – Buy Now'
                },
                dataAttrs
            ),
            children
        );
    }

    // --- Block ----------------------------------------------------------------

    blocks.registerBlockType('wc/buy-btn', {
        title: 'Buy Now Button',
        icon: 'onepaquc_buy_btn',
        category: 'plugincy',
        attributes: {
            // Product / Variation
            product_id:       { type: 'number',  default: null },
            variation_id:     { type: 'number',  default: null },
            detect_product:   { type: 'boolean', default: true },
            detect_variation: { type: 'boolean', default: false },

            // UI
            text:             { type: 'string',  default: '' },
            qty:              { type: 'number',  default: 1 },
            icon:             { type: 'string',  default: '' },
            icon_position:    { type: 'string',  default: '' },
            class:            { type: 'string',  default: '' },
            style:            { type: 'string',  default: '' },

            // Behavior
            show_for:         { type: 'string',  default: '' },
            force:            { type: 'boolean', default: false },
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
                            className: 'onepaquc-tabs',
                            tabs: [
                                { name: 'general', title: 'General' },
                                { name: 'ui',      title: 'UI' },
                                { name: 'behavior',title: 'Behavior' }
                            ]
                        },
                        function (tab) {
                            if (tab.name === 'general') {
                                return el(
                                    Fragment,
                                    {},
                                    el(PanelBody, { title: 'Product', initialOpen: true },
                                        el(NumberControl, {
                                            label: 'Product ID',
                                            value: attributes.product_id ?? '',
                                            min: 1,
                                            onChange: (v) => setAttributes({ product_id: v === '' ? null : parseInt(v, 10) })
                                        }),
                                        el(NumberControl, {
                                            label: 'Variation ID',
                                            value: attributes.variation_id ?? '',
                                            min: 1,
                                            onChange: (v) => setAttributes({ variation_id: v === '' ? null : parseInt(v, 10) })
                                        }),
                                        el(ToggleControl, {
                                            label: 'Detect product from context',
                                            checked: !!attributes.detect_product,
                                            onChange: (v) => setAttributes({ detect_product: !!v })
                                        }),
                                        el(ToggleControl, {
                                            label: 'Auto-pick variation (default/first in-stock)',
                                            checked: !!attributes.detect_variation,
                                            onChange: (v) => setAttributes({ detect_variation: !!v })
                                        })
                                    )
                                );
                            }
                            if (tab.name === 'ui') {
                                return el(
                                    Fragment,
                                    {},
                                    el(PanelBody, { title: 'Button UI', initialOpen: true },
                                        el(TextControl, {
                                            label: 'Text',
                                            value: attributes.text,
                                            onChange: (v) => setAttributes({ text: v }),
                                            placeholder: 'Buy Now'
                                        }),
                                        el(NumberControl, {
                                            label: 'Quantity',
                                            min: 1,
                                            value: attributes.qty,
                                            onChange: (v) => setAttributes({ qty: Math.max(1, parseInt(v || 1, 10)) })
                                        }),
                                        el(SelectControl, {
                                            label: 'Icon',
                                            value: attributes.icon,
                                            options: [
                                                { label: '(Use plugin default)', value: '' },
                                                { label: 'None',    value: 'none' },
                                                { label: 'Cart',    value: 'cart' },
                                                { label: 'Checkout',value: 'checkout' },
                                                { label: 'Arrow',   value: 'arrow' },
                                            ],
                                            onChange: (v) => setAttributes({ icon: v })
                                        }),
                                        el(SelectControl, {
                                            label: 'Icon position',
                                            value: attributes.icon_position,
                                            options: [
                                                { label: '(Use plugin default)', value: '' },
                                                { label: 'Left',   value: 'left' },
                                                { label: 'Right',  value: 'right' },
                                                { label: 'Top',    value: 'top' },
                                                { label: 'Bottom', value: 'bottom' },
                                            ],
                                            onChange: (v) => setAttributes({ icon_position: v })
                                        }),
                                        el(TextControl, {
                                            label: 'Extra CSS classes',
                                            value: attributes.class,
                                            onChange: (v) => setAttributes({ class: v }),
                                            placeholder: 'my-btn another-class'
                                        }),
                                        el(TextControl, {
                                            label: 'Inline style',
                                            value: attributes.style,
                                            onChange: (v) => setAttributes({ style: v }),
                                            placeholder: 'border-radius:8px; background-color:#111; color:#fff;'
                                        })
                                    )
                                );
                            }
                            if (tab.name === 'behavior') {
                                return el(
                                    PanelBody,
                                    { title: 'Behavior', initialOpen: true },
                                    el(TextControl, {
                                        label: 'Show for product types (comma-separated)',
                                        value: attributes.show_for,
                                        onChange: (v) => setAttributes({ show_for: v }),
                                        placeholder: 'simple,variable'
                                    }),
                                    el(ToggleControl, {
                                        label: 'Force display',
                                        checked: !!attributes.force,
                                        onChange: (v) => setAttributes({ force: !!v })
                                    })
                                );
                            }
                        }
                    )
                ),
                el('div', { className: 'onepaquc-buy-btn-block-preview' },
                    makePreviewButton(attributes, setAttributes)
                )
            );
        },

        // Dynamic block (server-rendered), nothing saved to post content
        save: function () {
            return null;
        }
    });
})(window.wp.blocks, window.wp.element, window.wp.components, window.wp.blockEditor);
