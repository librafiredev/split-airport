const _this = {
    $dom: {
        followButton: ".follow-flight",
        modal: $(".my-flights-modal-wrapper"),
        trigger: $(".my-flights-btn.view-trigger"),
        viewItems: $(".my-flights-modal-wrapper__items"),
        close: $(
            ".my-flights-modal-wrapper .custom-modal-close-btn, .my-flights-modal-wrapper .custom-modal-close-area "
        ),
        removeFlight: ".my-flight-item-remove-btn",
    },

    vars: {},

    init: function () {
        $("body").on("click", _this.$dom.followButton, _this.followAction);
        _this.$dom.trigger.on("click", _this.openView);
        _this.$dom.close.on("click", _this.closeView);
        $("body").on("click", _this.$dom.removeFlight, _this.followAction);
    },

    followAction: async function (e) {
        e.preventDefault();

        try {
            const flightID = $(e.currentTarget).data("id");
            const data = new FormData();

            data.append("_wpnonce", theme.restNonce);
            data.append("flightID", flightID);
            data.append("currentLanguage", theme.currentLanguage);

            const request = await fetch(theme.myFlightsRestUrl, {
                body: data,
                method: "POST",
            });

            const response = await request.json();

            if (response.success === true) {
                if ($(e.currentTarget).hasClass("my-flight-item-remove-btn")) {
                    $(e.currentTarget).closest(".my-flight-item").remove();

                    $(".my-flights-btn-item").replaceWith(
                        response.data.smallView
                    );

                    if (!response.data.smallView) {
                        _this.$dom.viewItems.html(
                            `<p>${theme.noMyFlights}</p>`
                        );
                    }
                } else {
                    const buttonText = $(e.currentTarget).text();

                    buttonText === theme.followButtonText
                        ? $(e.currentTarget).text(theme.unfollowButtonText)
                        : $(e.currentTarget).text(theme.followButtonText);
                }
            }
        } catch (e) {
            console.error(e.message);
        }
    },

    openView: function () {
        if (!_this.$dom.modal.hasClass("open")) {
            _this.$dom.modal.addClass("open");
        }
    },

    closeView: function () {
        if (_this.$dom.modal.hasClass("open")) {
            _this.$dom.modal.removeClass("open");
        }
    },
};

export default _this;
