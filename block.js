(function (blocks, element) {
    const el = element.createElement;

    blocks.registerBlockType('wc/checkout-block', {
        title: 'Plugincy Checkout',
        icon: 'cart',
        category: 'widgets',
        edit: function () {
            return el('p', {}, '[woocommerce_checkout]');
        },
        save: function () {
            return "[woocommerce_checkout]"; // Rendered by PHP
        },
    });
})(window.wp.blocks, window.wp.element);