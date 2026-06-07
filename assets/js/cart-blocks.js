(function (window) {
    "use strict";

    var params = window.onepaquc_wc_cart_params || {};

    function parseSettingFlag(value, fallbackValue) {
        if (typeof value === "undefined" || value === null || value === "") {
            return fallbackValue;
        }

        if (typeof value === "boolean") {
            return value;
        }

        if (typeof value === "number") {
            return value === 1;
        }

        return ["1", "true", "yes", "on"].indexOf(String(value).toLowerCase()) !== -1;
    }

    var blocksQtyEnabled = parseSettingFlag(params.blocks_quantity_control, true);
    var blocksLinkEnabled = parseSettingFlag(params.blocks_link_product, false);

    if (!blocksQtyEnabled && !blocksLinkEnabled) {
        return;
    }

    var blocksCheckoutApi = window.wc && window.wc.blocksCheckout ? window.wc.blocksCheckout : null;
    if (!blocksCheckoutApi) {
        return;
    }

    var registerCheckoutFilters = blocksCheckoutApi.registerCheckoutFilters || blocksCheckoutApi.__experimentalRegisterCheckoutFilters;
    if (typeof registerCheckoutFilters !== "function") {
        return;
    }

    function escapeHtml(value) {
        return String(value || "")
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    function normalizeQty(value, min, max, step) {
        var numeric = parseFloat(value);
        if (!Number.isFinite(numeric)) {
            numeric = min;
        }

        numeric = Math.max(min, numeric);

        if (Number.isFinite(max) && max > 0) {
            numeric = Math.min(max, numeric);
        }

        if (Number.isFinite(step) && step > 0) {
            numeric = min + Math.round((numeric - min) / step) * step;
        }

        return Math.max(min, numeric);
    }

    function supportsBlocksFeatures(args) {
        return !!(args && args.context === "summary" && args.cartItem);
    }

    function canRenderBlocksQtyControl(cartItem) {
        if (!blocksQtyEnabled || !cartItem || !cartItem.key) {
            return false;
        }

        if (cartItem.sold_individually) {
            return false;
        }

        var limits = cartItem.quantity_limits || {};
        return limits.editable !== false;
    }

    function buildBlocksQtyControl(cartItem) {
        var limits = cartItem.quantity_limits || {};
        var min = parseFloat(limits.minimum);
        var max = parseFloat(limits.maximum);
        var step = parseFloat(limits.multiple_of);
        var quantity = parseFloat(cartItem.quantity);
        var key = escapeHtml(cartItem.key);

        if (!Number.isFinite(min)) {
            min = 1;
        }
        if (!Number.isFinite(step) || step <= 0) {
            step = 1;
        }
        if (!Number.isFinite(max) || max <= 0) {
            max = null;
        }

        quantity = normalizeQty(quantity, min, max, step);
        var itemName = String(cartItem.name || "");
        var stripName = itemName.replace(/<[^>]*>/g, "").trim();
        var safeName = escapeHtml(stripName);
        var maxAttr = Number.isFinite(max) ? ' max="' + max + '"' : "";

        return "" +
            '<span class="checkout-quantity-control onepaquc-block-qty-wrap" data-cart-item="' + key + '">' +
            '<button type="button" class="checkout-qty-btn checkout-qty-minus" data-cart-item="' + key + '" aria-label="Decrease quantity for ' + safeName + '">-</button>' +
            '<input type="number" name="cart[' + key + '][qty]" class="checkout-qty-input" data-cart-item="' + key + '" data-last-value="' + quantity + '" value="' + quantity + '" min="' + min + '"' + maxAttr + ' step="' + step + '">' +
            '<button type="button" class="checkout-qty-btn checkout-qty-plus" data-cart-item="' + key + '" aria-label="Increase quantity for ' + safeName + '">+</button>' +
            "</span>";
    }

    function blocksItemNameFilter(defaultValue, extensions, args) {
        if (!supportsBlocksFeatures(args)) {
            return defaultValue;
        }

        var cartItem = args.cartItem || {};
        var itemNameHtml = String(defaultValue || "");

        if (blocksLinkEnabled && cartItem.permalink && !/<a[\s>]/i.test(itemNameHtml)) {
            itemNameHtml = '<a class="onepaquc-block-product-link" href="' + escapeHtml(cartItem.permalink) + '">' + itemNameHtml + "</a>";
        }

        if (canRenderBlocksQtyControl(cartItem)) {
            itemNameHtml += buildBlocksQtyControl(cartItem);
        }

        return itemNameHtml;
    }

    function blocksCartItemClassFilter(defaultValue, extensions, args) {
        var className = String(defaultValue || "");
        if (!supportsBlocksFeatures(args) || !canRenderBlocksQtyControl(args.cartItem)) {
            return className;
        }

        if (className.indexOf("onepaquc-has-custom-qty-controls") !== -1) {
            return className;
        }

        return (className ? className + " " : "") + "onepaquc-has-custom-qty-controls";
    }

    var filters = {
        itemName: blocksItemNameFilter
    };

    if (blocksQtyEnabled) {
        filters.cartItemClass = blocksCartItemClassFilter;
    }

    registerCheckoutFilters("onepaquc-checkout-features", filters);
})(window);
