const _this = {
    $dom: {
        flight: ".flight",
        searchFlight: ".search-data__flight",
        popup: $(".flight-popup-wrapper"),
        closeButton: ".flight-popup-close-btn",
        loader: $(".flight-popup-wrapper .loader"),
    },

    vars: {},

    init: function () {
        $("body").on("click", _this.$dom.flight, _this.openPopup);
        $("body").on("click", _this.$dom.searchFlight, _this.openPopup);
        $("body").on("click", _this.$dom.closeButton, function () {
            history.go(-1);
        });
        $(window).on("popstate", _this.closePopup);
    },

    openPopup: function (e) {
        const ID = $(e.currentTarget).data("id");
        _this.$dom.popup.addClass("open");
        const url = new URL(window.location.href);
        url.searchParams.set("flightInfo", "true");
        window.history.pushState({}, "", url);
        _this.request(ID);
    },

    closePopup: function () {
        if (_this.$dom.popup.hasClass("open")) {
            const popupInner = $(".flight-popup");
            _this.$dom.popup.removeClass("open");
            const url = new URL(window.location.href);
            url.searchParams.delete("flightInfo");
            window.history.replaceState({}, "", url);
            popupInner.remove();
        }
    },

    request: async function (ID) {
        _this.$dom.loader.show();

        try {
            const requestParams = new URLSearchParams();
            requestParams.append("ID", ID);
            requestParams.append("_wpnonce", theme.restNonce);

            const request = await fetch(
                `${theme.flightRestUrl}?${requestParams.toString()}`,
                {
                    method: "GET",
                }
            );

            const response = await request.json();

            _this.$dom.loader.hide();

            if (response.success === true) {
                setTimeout(() => {
                    _this.$dom.popup.append(response.data);
                }, 100);
            }
        } catch (e) {
            console.error(e.message);
        }
    },
};

export default _this;
