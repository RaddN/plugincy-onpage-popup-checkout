(function ($) {
    "use strict";

    var deactivateUrl = "";

    function getConfig() {
        return typeof onepaqucFeedback === "object" ? onepaqucFeedback : {};
    }

    function showFeedbackModal(url) {
        deactivateUrl = url;
        $("#onepaquc-plugin-deactivation-feedback").show();
    }

    function hideFeedbackModal() {
        $("#onepaquc-plugin-deactivation-feedback").hide();
    }

    $(document).on("click", "a[href*='action=deactivate']", function (event) {
        var config = getConfig();
        var href = $(this).attr("href") || "";
        var pluginBasename = config.pluginBasename || "";
        var pluginSlug = config.pluginSlug || "";
        var row = $(this).closest("tr");
        var matchesSlug = pluginSlug && row.attr("data-slug") === pluginSlug;
        var matchesPlugin = pluginBasename && (
            row.attr("data-plugin") === pluginBasename ||
            href.indexOf(encodeURIComponent(pluginBasename)) > -1
        );

        if (!matchesSlug && !matchesPlugin) {
            return;
        }

        event.preventDefault();
        showFeedbackModal(href);
    });

    $(document).on("submit", "#onepaquc-deactivation-feedback-form", function (event) {
        event.preventDefault();

        var config = getConfig();
        var reason = $("input[name='reason']:checked").val();
        var otherReason = $("textarea[name='other_reason']").val();

        if (reason === "other" && otherReason) {
            reason = otherReason;
        }

        $(this).find("button.btn.btn-primary").text("Deactivating...");

        $.ajax({
            url: config.ajaxUrl,
            type: "POST",
            data: {
                action: "onepaquc_send_deactivation_feedback",
                reason: reason || "no-reason-provided",
                nonce: config.nonce
            }
        }).always(function () {
            window.setTimeout(function () {
                window.location.href = deactivateUrl;
            }, 500);
        });
    });

    $(document).on("change", "input[name='reason']", function () {
        if ($(this).val() === "other") {
            $(".other-reason-container").slideDown(300);
        } else {
            $(".other-reason-container").slideUp(300);
        }
    });

    $(document).on("click", ".close-button", hideFeedbackModal);

    $(document).on("click", ".feedback-overlay", function (event) {
        if (event.target === this) {
            hideFeedbackModal();
        }
    });

    $(document).on("keyup", function (event) {
        if (event.keyCode === 27) {
            hideFeedbackModal();
        }
    });
})(jQuery);
