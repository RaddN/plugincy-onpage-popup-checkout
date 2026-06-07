(function ($) {
    "use strict";

    function onReady(callback) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", callback);
            return;
        }

        callback();
    }

    function findByName(name, scope) {
        var root = scope || document;
        var fields = root.querySelectorAll("input, select, textarea, button");

        for (var i = 0; i < fields.length; i++) {
            if (fields[i].name === name) {
                return fields[i];
            }
        }

        return null;
    }

    function getCheckboxByName(name, scope) {
        var root = scope || document;
        var fields = root.querySelectorAll('input[type="checkbox"]');

        for (var i = 0; i < fields.length; i++) {
            if (fields[i].name === name) {
                return fields[i];
            }
        }

        return null;
    }

    function normalizeFields(fields) {
        if (!fields) {
            return [];
        }

        if (fields instanceof Element) {
            return [fields];
        }

        return Array.prototype.slice.call(fields);
    }

    function toggleDisabledClass(isDisabled, fields) {
        normalizeFields(fields).forEach(function (field) {
            if (!field) {
                return;
            }

            field.classList.toggle("disabled", !!isDisabled);

            if (field.matches("input, textarea")) {
                field.readOnly = !!isDisabled;
            }
        });
    }

    function isColorDark(color) {
        if (!color) {
            return false;
        }

        var hex = String(color).replace("#", "").toLowerCase();
        if (hex.length === 3) {
            hex = hex.split("").map(function (char) {
                return char + char;
            }).join("");
        }

        if (!/^[0-9a-f]{6}$/.test(hex)) {
            return false;
        }

        var r = parseInt(hex.slice(0, 2), 16);
        var g = parseInt(hex.slice(2, 4), 16);
        var b = parseInt(hex.slice(4, 6), 16);

        return ((0.299 * r + 0.587 * g + 0.114 * b) / 255) < 0.5;
    }

    function showDirectCheckoutWarning(highlightSection, message) {
        var popup = document.getElementById("rmenu-enable-atc-popup");

        if (highlightSection) {
            highlightSection.classList.add("onepaquc-field-warning");
        }

        if (!popup) {
            popup = document.createElement("div");
            popup.id = "rmenu-enable-atc-popup";
            popup.className = "onepaquc-warning-popup";
            document.body.appendChild(popup);
        }

        popup.textContent = message;
        popup.classList.add("is-visible");

        window.clearTimeout(popup._onepaqucTimer);
        popup._onepaqucTimer = window.setTimeout(function () {
            popup.classList.remove("is-visible");
        }, 5000);
    }

    function removeDirectCheckoutWarning(highlightSection) {
        if (highlightSection) {
            highlightSection.classList.remove("onepaquc-field-warning");
        }
    }

    function checkColors(backgroundField, textField, showWarning) {
        if (!backgroundField || !textField || showWarning === false) {
            return;
        }

        var backgroundIsDark = isColorDark(backgroundField.value);
        var textIsDark = isColorDark(textField.value);

        if (backgroundIsDark && textIsDark) {
            showDirectCheckoutWarning(
                backgroundField.closest(".rmenu-settings-row") || backgroundField.closest("tr"),
                "Warning: Both background and text colors are dark. This may affect readability."
            );
            return;
        }

        if (!backgroundIsDark && !textIsDark) {
            showDirectCheckoutWarning(
                textField.closest(".rmenu-settings-row") || textField.closest("tr"),
                "Warning: Both background and text colors are light. This may affect readability."
            );
            return;
        }

        removeDirectCheckoutWarning(backgroundField.closest(".rmenu-settings-row") || backgroundField.closest("tr"));
        removeDirectCheckoutWarning(textField.closest(".rmenu-settings-row") || textField.closest("tr"));
    }

    function bindMasterToggle(config) {
        var scope = config.scope ? document.querySelector(config.scope) : document;
        var master = getCheckboxByName(config.master, scope);

        if (!master) {
            return;
        }

        var fields = Array.prototype.slice.call(document.querySelectorAll(config.fields || ""));
        var excluded = config.exclude || [];

        fields = fields.filter(function (field) {
            return excluded.indexOf(field.name) === -1;
        });

        function update() {
            var extraDisabled = typeof config.extraDisabled === "function" && config.extraDisabled();
            toggleDisabledClass(!master.checked || !!extraDisabled, fields);
        }

        master.addEventListener("change", update);
        update();
    }

    function bindNestedTabs(rootSelector) {
        var root = document.querySelector(rootSelector);
        if (!root) {
            return;
        }

        var tabs = Array.prototype.slice.call(root.querySelectorAll(".rmenu-settings-tab-item"));
        var contents = Array.prototype.slice.call(root.querySelectorAll(":scope > .tab-content"));

        function activate(tab) {
            var targetId = tab.getAttribute("data-tab");
            tabs.forEach(function (item) {
                item.classList.toggle("active", item === tab);
            });
            contents.forEach(function (content) {
                content.classList.toggle("active", content.id === targetId);
            });
        }

        tabs.forEach(function (tab) {
            tab.addEventListener("click", function () {
                activate(tab);
            });
        });

        activate(root.querySelector(".rmenu-settings-tab-item.active") || tabs[0]);
    }

    function bindRowsForSelect(selectSelector, rows, predicate) {
        var select = document.querySelector(selectSelector);
        if (!select) {
            return;
        }

        function update() {
            var show = predicate(select.value);
            rows.forEach(function (rowSelector) {
                var row = document.querySelector(rowSelector);
                if (row) {
                    row.style.display = show ? "flex" : "none";
                }
            });
        }

        select.addEventListener("change", update);
        update();
    }

    function initCheckoutMethodDependency() {
        var checkoutMethod = document.getElementById("rmenu-checkout-method");
        var stickyCart = getCheckboxByName("rmenu_enable_sticky_cart");

        if (!checkoutMethod || !stickyCart) {
            return;
        }

        var sideCartOption = checkoutMethod.querySelector('option[value="side_cart"]');
        if (!sideCartOption) {
            return;
        }

        function update() {
            var row = checkoutMethod.closest(".rmenu-settings-row") || checkoutMethod.closest("tr");
            sideCartOption.disabled = !stickyCart.checked;
            if (!stickyCart.checked && checkoutMethod.value === "side_cart") {
                showDirectCheckoutWarning(row, "Enable Sticky Cart to use the Side Cart Slide-in checkout method.");
                return;
            }

            removeDirectCheckoutWarning(row);
        }

        stickyCart.addEventListener("change", update);
        checkoutMethod.addEventListener("change", update);
        update();
    }

    function initVariationLayoutDependency() {
        var layout = document.querySelector('select[name="rmenu_variation_layout"]');
        var title = getCheckboxByName("rmenu_show_variation_title");

        if (!layout || !title) {
            return;
        }

        function update() {
            toggleDisabledClass(layout.value === "combine", title);
        }

        layout.addEventListener("change", update);
        update();
    }

    function initDirectButtonControls() {
        bindRowsForSelect('select[name="rmenu_wc_checkout_width"]', ["#rmenu-checkout-custom-width-row"], function (value) {
            return value === "custom";
        });

        var icon = document.querySelector('select[name="rmenu_wc_checkout_icon"]');
        var iconRow = document.getElementById("rmenu-checkout-icon-position-row");
        if (icon && iconRow) {
            var updateIconRow = function () {
                iconRow.style.display = icon.value === "none" ? "none" : "flex";
            };
            icon.addEventListener("change", updateIconRow);
            updateIconRow();
        }

        var background = document.querySelector('input[name="rmenu_wc_checkout_color"]');
        var text = document.querySelector('input[name="rmenu_wc_checkout_text_color"]');
        if (background && text) {
            var updateColors = function () {
                checkColors(background, text, true);
            };
            background.addEventListener("change", updateColors);
            text.addEventListener("change", updateColors);
        }
    }

    function initQuickViewControls() {
        var displayType = document.querySelector('div#tab-7 select[name="rmenu_quick_view_display_type"]');
        var buttonText = document.querySelector('div#tab-7 input[name="rmenu_quick_view_button_text"]');
        var icon = document.querySelector('div#tab-7 select[name="rmenu_quick_view_button_icon"]');

        function updateDisplayType() {
            if (buttonText && displayType) {
                toggleDisabledClass(displayType.value === "icon", buttonText);
            }
        }

        if (displayType) {
            displayType.addEventListener("change", updateDisplayType);
            updateDisplayType();
        }

        if (icon) {
            icon.addEventListener("change", function () {
                if (icon.value === "none") {
                    showDirectCheckoutWarning(
                        icon.closest(".rmenu-settings-row") || icon.closest("tr"),
                        "No icon is selected for icon display mode."
                    );
                }
            });
        }

        var detailsToggle = document.getElementById("view_details_checkbox");
        var detailsText = document.getElementById("view_details_text");
        if (detailsToggle && detailsText) {
            var updateDetails = function () {
                detailsText.disabled = !detailsToggle.checked;
            };
            detailsToggle.addEventListener("change", updateDetails);
            updateDetails();
        }
    }

    function initAddToCartControls() {
        bindRowsForSelect('select[name="rmenu_add_to_cart_width"]', ["#rmenu-atc-custom-width-row"], function (value) {
            return value === "custom";
        });

        var icon = document.querySelector('select[name="rmenu_add_to_cart_icon"]');
        var iconRow = document.getElementById("rmenu-atc-icon-position-row");
        if (icon && iconRow) {
            var updateIconRow = function () {
                iconRow.style.display = icon.value === "none" ? "none" : "flex";
            };
            icon.addEventListener("change", updateIconRow);
            updateIconRow();
        }

        var catalogDisplay = document.querySelector('select[name="rmenu_add_to_cart_catalog_display"]');
        if (catalogDisplay) {
            catalogDisplay.addEventListener("change", function () {
                var fields = Array.prototype.slice.call(document.querySelectorAll("div#tab-8 input, div#tab-8 select, div#tab-8 textarea"))
                    .filter(function (field) {
                        return field.name !== "rmenu_add_to_cart_catalog_display" && field.name !== "rmenu_enable_custom_add_to_cart";
                    });
                toggleDisabledClass(catalogDisplay.value === "hide", fields);
            });
            catalogDisplay.dispatchEvent(new Event("change"));
        }

        var ajaxToggle = getCheckboxByName("rmenu_enable_ajax_add_to_cart");
        if (ajaxToggle) {
            var ajaxFields = Array.prototype.slice.call(document.querySelectorAll("#add_to_cart_behave input, #add_to_cart_behave select, #add_to_cart_notification input, #add_to_cart_notification select"))
                .filter(function (field) {
                    return field.name !== "rmenu_enable_ajax_add_to_cart";
                });
            var updateAjaxFields = function () {
                toggleDisabledClass(!ajaxToggle.checked, ajaxFields);
            };
            ajaxToggle.addEventListener("change", updateAjaxFields);
            updateAjaxFields();
        }
    }

    function initTrustBadges() {
        var enabled = getCheckboxByName("onepaquc_trust_badges_enabled");
        if (!enabled) {
            return;
        }

        var fields = Array.prototype.slice.call(document.querySelectorAll("div#tab-6 table:first-of-type input, div#tab-6 table:first-of-type select, div#tab-6 table:first-of-type textarea"))
            .filter(function (field) {
                return field.name !== "onepaquc_trust_badges_enabled";
            });

        var update = function () {
            toggleDisabledClass(!enabled.checked, fields);
        };

        enabled.addEventListener("change", update);
        update();
    }

    function initLicenseEye() {
        var input = document.getElementById("onepaquc_license_key");
        var eye = document.getElementById("onepaquc_license_eye");

        if (!input || !eye) {
            return;
        }

        eye.addEventListener("click", function () {
            var visible = input.type !== "text";
            input.type = visible ? "text" : "password";
            eye.classList.toggle("dashicons-visibility", !visible);
            eye.classList.toggle("dashicons-visibility-alt", visible);
            eye.title = visible ? "Hide license key" : "Show license key";
        });
    }

    onReady(function () {
        window.getCheckboxByName = getCheckboxByName;
        window.toggleDisabledClass = toggleDisabledClass;
        window.showDirectCheckoutWarning = showDirectCheckoutWarning;
        window.removeDirectCheckoutWarning = removeDirectCheckoutWarning;
        window.checkColors = checkColors;

        bindMasterToggle({
            master: "onpage_checkout_enable",
            scope: "div#tab-2",
            fields: "div#tab-2 table:nth-of-type(1) input",
            exclude: ["onpage_checkout_enable"]
        });
        bindMasterToggle({
            master: "rmenu_enable_sticky_cart",
            scope: "div#tab-9",
            fields: "div#tab-9 input, div#tab-9 select",
            exclude: ["rmenu_enable_sticky_cart"]
        });
        bindMasterToggle({
            master: "rmenu_variation_show_archive",
            scope: "div#tab-4",
            fields: "div#tab-4 table.variable-product-table input, div#tab-4 table.variable-product-table select",
            exclude: ["rmenu_variation_show_archive"]
        });
        bindMasterToggle({
            master: "rmenu_add_direct_checkout_button",
            scope: "div#tab-4",
            fields: "div#tab-4 input, div#tab-4 select",
            exclude: ["rmenu_add_direct_checkout_button"]
        });
        bindMasterToggle({
            master: "rmenu_enable_quick_view",
            scope: "div#tab-7",
            fields: "div#tab-7 input, div#tab-7 select, div#tab-7 textarea",
            exclude: ["rmenu_enable_quick_view"]
        });
        bindMasterToggle({
            master: "rmenu_enable_custom_add_to_cart",
            scope: "div#tab-8",
            fields: "div#tab-8 input, div#tab-8 select, div#tab-8 textarea",
            exclude: ["rmenu_enable_custom_add_to_cart", "rmenu_add_to_cart_catalog_display"],
            extraDisabled: function () {
                var display = document.querySelector('select[name="rmenu_add_to_cart_catalog_display"]');
                return display && display.value === "hide";
            }
        });

        bindNestedTabs("#tab-4");
        bindNestedTabs("#tab-7");
        bindNestedTabs("#tab-8");

        initCheckoutMethodDependency();
        initVariationLayoutDependency();
        initDirectButtonControls();
        initQuickViewControls();
        initAddToCartControls();
        initTrustBadges();
        initLicenseEye();
    });
})(jQuery);
