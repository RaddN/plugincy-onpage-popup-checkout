(function (window, document) {
    "use strict";

    var params = window.onepaquc_wc_cart_params || {};
    var cartItemsByClassKey = {};
    var enhancementQueued = false;

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

    function getCartItemClassKey(cartItem) {
        if (!cartItem || !cartItem.key) {
            return "";
        }

        return String(cartItem.key).replace(/[^A-Za-z0-9_-]/g, "-");
    }

    function cacheCartItem(cartItem) {
        var classKey = getCartItemClassKey(cartItem);
        if (!classKey) {
            return "";
        }

        cartItemsByClassKey[classKey] = {
            key: String(cartItem.key),
            name: String(cartItem.name || ""),
            permalink: cartItem.permalink ? String(cartItem.permalink) : "",
            quantity: cartItem.quantity,
            quantity_limits: cartItem.quantity_limits || {},
            sold_individually: !!cartItem.sold_individually
        };

        return classKey;
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

    function getPlainItemName(cartItem, nameElement) {
        var itemName = cartItem && cartItem.name ? String(cartItem.name) : "";
        var plainName = itemName.replace(/<[^>]*>/g, "").trim();

        if (!plainName && nameElement) {
            plainName = String(nameElement.textContent || "").trim();
        }

        return plainName;
    }

    function createBlocksQtyControl(cartItem, nameElement) {
        var limits = cartItem.quantity_limits || {};
        var min = parseFloat(limits.minimum);
        var max = parseFloat(limits.maximum);
        var step = parseFloat(limits.multiple_of);
        var quantity = parseFloat(cartItem.quantity);
        var key = String(cartItem.key || "");

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
        var plainName = getPlainItemName(cartItem, nameElement);
        var wrap = document.createElement("span");
        var minus = document.createElement("button");
        var input = document.createElement("input");
        var plus = document.createElement("button");

        wrap.className = "checkout-quantity-control onepaquc-block-qty-wrap";
        wrap.setAttribute("data-cart-item", key);

        minus.type = "button";
        minus.className = "checkout-qty-btn checkout-qty-minus";
        minus.setAttribute("data-cart-item", key);
        minus.setAttribute("aria-label", "Decrease quantity for " + plainName);
        minus.textContent = "-";

        input.type = "number";
        input.name = "cart[" + key + "][qty]";
        input.className = "checkout-qty-input";
        input.setAttribute("data-cart-item", key);
        input.setAttribute("data-last-value", quantity);
        input.value = quantity;
        input.min = min;
        if (Number.isFinite(max)) {
            input.max = max;
        }
        input.step = step;

        plus.type = "button";
        plus.className = "checkout-qty-btn checkout-qty-plus";
        plus.setAttribute("data-cart-item", key);
        plus.setAttribute("aria-label", "Increase quantity for " + plainName);
        plus.textContent = "+";

        wrap.appendChild(minus);
        wrap.appendChild(input);
        wrap.appendChild(plus);

        return wrap;
    }

    function blocksItemNameFilter(defaultValue, extensions, args) {
        if (!supportsBlocksFeatures(args)) {
            return defaultValue;
        }

        cacheCartItem(args.cartItem);
        scheduleBlocksEnhancement();

        // Woo Blocks reuses itemName in screen-reader labels. Returning markup here
        // leaks escaped HTML into accessible text, so visual enhancements are added
        // after render by enhanceBlocksCartItems().
        return defaultValue;
    }

    function blocksCartItemClassFilter(defaultValue, extensions, args) {
        var className = String(defaultValue || "");
        if (!supportsBlocksFeatures(args)) {
            return className;
        }

        var cartItem = args.cartItem || {};
        var classKey = cacheCartItem(cartItem);
        if (!classKey || (!blocksLinkEnabled && !canRenderBlocksQtyControl(cartItem))) {
            return className;
        }

        className = addClassName(className, "onepaquc-has-block-enhancements");
        className = addClassName(className, "onepaquc-cart-item-key-" + classKey);

        if (canRenderBlocksQtyControl(cartItem)) {
            className = addClassName(className, "onepaquc-has-custom-qty-controls");
        }

        scheduleBlocksEnhancement();

        return className;
    }

    function addClassName(className, nextClassName) {
        var current = " " + String(className || "") + " ";
        if (current.indexOf(" " + nextClassName + " ") !== -1) {
            return className;
        }

        return (className ? className + " " : "") + nextClassName;
    }

    function getRowClassKey(row) {
        var classes = row && row.classList ? row.classList : [];
        for (var i = 0; i < classes.length; i++) {
            if (classes[i].indexOf("onepaquc-cart-item-key-") === 0) {
                return classes[i].replace("onepaquc-cart-item-key-", "");
            }
        }

        return "";
    }

    function getProductNameElement(row) {
        if (!row || typeof row.querySelector !== "function") {
            return null;
        }

        return row.querySelector(".wc-block-components-product-name");
    }

    function ensureProductLink(nameElement, cartItem) {
        if (!blocksLinkEnabled || !nameElement || !cartItem || !cartItem.permalink) {
            return;
        }

        var existingLink = nameElement.querySelector("a");
        if (existingLink) {
            existingLink.classList.add("onepaquc-block-product-link");
            return;
        }

        var link = document.createElement("a");
        link.className = "onepaquc-block-product-link";
        link.href = cartItem.permalink;

        while (nameElement.firstChild) {
            if (
                nameElement.firstChild.nodeType === 1 &&
                nameElement.firstChild.classList.contains("onepaquc-block-qty-wrap")
            ) {
                break;
            }
            link.appendChild(nameElement.firstChild);
        }

        nameElement.insertBefore(link, nameElement.firstChild);
    }

    function ensureQtyControl(nameElement, cartItem) {
        if (!nameElement || !cartItem) {
            return;
        }

        var existingControl = nameElement.querySelector(".onepaquc-block-qty-wrap");
        if (!canRenderBlocksQtyControl(cartItem)) {
            if (existingControl) {
                existingControl.parentNode.removeChild(existingControl);
            }
            return;
        }

        if (!existingControl) {
            nameElement.appendChild(createBlocksQtyControl(cartItem, nameElement));
            return;
        }

        var input = existingControl.querySelector(".checkout-qty-input");
        if (!input || document.activeElement === input) {
            return;
        }

        var limits = cartItem.quantity_limits || {};
        input.value = normalizeQty(cartItem.quantity, parseFloat(limits.minimum) || 1, parseFloat(limits.maximum), parseFloat(limits.multiple_of) || 1);
        input.setAttribute("data-last-value", input.value);
    }

    function enhanceBlocksCartItems() {
        if (!document || !document.querySelectorAll) {
            return;
        }

        var rows = document.querySelectorAll(".onepaquc-has-block-enhancements");
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            var classKey = getRowClassKey(row);
            var cartItem = classKey ? cartItemsByClassKey[classKey] : null;
            var nameElement = getProductNameElement(row);

            if (!cartItem || !nameElement) {
                continue;
            }

            ensureProductLink(nameElement, cartItem);
            ensureQtyControl(nameElement, cartItem);
        }
    }

    function scheduleBlocksEnhancement() {
        if (enhancementQueued) {
            return;
        }

        enhancementQueued = true;
        var callback = function () {
            enhancementQueued = false;
            enhanceBlocksCartItems();
        };

        if (window.requestAnimationFrame) {
            window.requestAnimationFrame(callback);
        } else {
            window.setTimeout(callback, 0);
        }
    }

    function initBlocksEnhancer() {
        scheduleBlocksEnhancement();

        if (!window.MutationObserver || !document.body) {
            return;
        }

        var observer = new window.MutationObserver(function () {
            scheduleBlocksEnhancement();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    }

    var filters = {
        itemName: blocksItemNameFilter
    };

    filters.cartItemClass = blocksCartItemClassFilter;

    registerCheckoutFilters("onepaquc-checkout-features", filters);

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initBlocksEnhancer);
    } else {
        initBlocksEnhancer();
    }
})(window, document);
