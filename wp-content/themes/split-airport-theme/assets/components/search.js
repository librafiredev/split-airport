import urlApiSingle from "./urlApiSingle";

const _this = {
    $dom: {
        searchInput: $('input[name="search"]'),
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
        const term = $(e.currentTarget).val();

        // Term needs to be at least 3 chars

        if (term.length < 3 && !term) {
            clearInterval(_this.vars.searchTimeout);
            return false;
        }

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
        }, 1200);
    },
};

export default _this.init;
