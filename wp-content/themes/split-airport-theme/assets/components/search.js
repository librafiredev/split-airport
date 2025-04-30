import urlApiSingle from "./urlApiSingle";

const _this = {
    $dom: {
        searchInput: $('input[name="search"]'),
        loaderSearch: $(".loader-search"),
    },

    vars: {
        searchTimeout: null,
        callback: undefined,
    },

    init: function (callback, urlParams = true) {
        _this.vars.callback = callback;
        _this.vars.urlParams = urlParams;
        _this.$dom.searchInput.on("keyup", _this.search);
    },

    search: function (e) {
        e.preventDefault();
        _this.$dom.loaderSearch.show();
        const term = $(e.currentTarget).val();

        const currentSearch = $(e.currentTarget);
        clearInterval(_this.vars.searchTimeout);
        _this.vars.searchTimeout = setTimeout(() => {
            const searchValue = currentSearch.val();

            if (_this.vars.urlParams) {
                if (searchValue != "") {
                    urlApiSingle("search", searchValue);
                } else {
                    urlApiSingle("search", searchValue, true);
                }
            }

            _this.vars.callback(term, true);
        }, 800);
    },
};

export default _this.init;
