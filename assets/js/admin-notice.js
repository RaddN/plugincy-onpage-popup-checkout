(function ($) {
    "use strict";

    $(document).on("click", ".onepaquc-ny-dismiss-trigger", function (event) {
        event.preventDefault();
        event.stopPropagation();
        $(".onepaquc-ny-dismiss-menu").toggleClass("active");
    });

    $(document).on("click", ".onepaquc-ny-dismiss-menu button", function (event) {
        event.preventDefault();

        var hours = $(this).data("hours");
        var $notice = $(this).closest(".notice-info");

        $(".onepaquc-ny-dismiss-menu").removeClass("active");
        $notice.fadeOut(300);

        if (typeof onepaqucNyNotice === "undefined") {
            return;
        }

        $.ajax({
            url: onepaqucNyNotice.ajaxUrl,
            type: "POST",
            data: {
                action: "onepaquc_dismiss_ny_notice",
                hours: hours,
                nonce: onepaqucNyNotice.nonce
            }
        });
    });

    $(document).on("click", function () {
        $(".onepaquc-ny-dismiss-menu").removeClass("active");
    });
})(jQuery);
