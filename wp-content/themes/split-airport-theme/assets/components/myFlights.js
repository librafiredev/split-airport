const _this = {
    $dom: {
        followButton: ".follow-flight",
        trigger: ".my-flights-btn.view-trigger",
        close: ".my-flights-modal-wrapper .custom-modal-close-btn, .my-flights-modal-wrapper .custom-modal-close-area",
        removeFlight: ".my-flight-item-remove-btn",
        myFlightsWrapper: $(".my-flights-root"),
    },

    vars: {},

    init: function () {
        $("body").on("click", _this.$dom.followButton, _this.followAction);
        $("body").on("click", _this.$dom.trigger, _this.openView);
        $("body").on("click", _this.$dom.close, _this.closeView);
        $("body").on("click", _this.$dom.removeFlight, _this.followAction);
        _this.nextDayDeleteFlightsAction();
    },

    followAction: async function (e) {
        e.preventDefault();
        e.stopPropagation();

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
                if (
                    $(e.currentTarget).hasClass("my-flight-item-remove-btn") ||
                    $(e.currentTarget).text() === theme.unfollowButtonText
                ) {
                    $(e.currentTarget).closest(".my-flight-item").remove();

                    $(".my-flights-btn-item").replaceWith(
                        response.data.smallView
                    );

                    const viewItems = $(".my-flights-modal-wrapper__items");

                    if (!response.data.smallView) {
                        viewItems.html(
                            `<p class="no-items-my-flight">${theme.noMyFlights}</p>`
                        );
                    }

                    if ($(e.currentTarget).text() === theme.unfollowButtonText) {
                        $(e.currentTarget).text(theme.followButtonText);
                        const flight = $(`.flight[data-id="${flightID}"]`);
                        flight.remove();
                        $('.flight-popup-close-btn').trigger('click');
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

    nextDayDeleteFlightsAction: async function () {
        const today = new Date().toISOString().split("T")[0];
        const lastCheckDate = localStorage.getItem("lastFlightCheckDate");

        if (lastCheckDate !== today) {
            try {
                const data = new FormData();

                data.append("_wpnonce", theme.restNonce);

                const request = await fetch(theme.checkMyFlightsRestUrl, {
                    body: data,
                    method: "POST",
                });

                const response = await request.json();

                if (response.success === true) {
                    _this.$dom.myFlightsWrapper.html(
                        response.data.myFlightsHTML
                    );

                    localStorage.setItem("lastFlightCheckDate", today);
                }
            } catch (e) {
                console.error(e.message);
            }
        }
    },

    openView: function () {
        const modal = $(".my-flights-modal-wrapper");

        if (!modal.hasClass("open")) {
            modal.addClass("open");
        }
    },

    closeView: function () {
        const modal = $(".my-flights-modal-wrapper");

        if (modal.hasClass("open")) {
            modal.removeClass("open");
        }
    },
};

export default _this;
