import search from "../../assets/components/search";
import urlApiSingle from "../../assets/components/urlApiSingle";

$(function () {
    const searchWrapper = $(".arrivals-timetable-search__bottom-results");
    const initWrapper = $(".arrivals-timetable__table-flights");
    const loader = $(".loader");
    const dateSwitcherLeft = $(".date-switcher__left");
    const dateSwitcherRight = $(".date-switcher__right");
    const dateSwitcherView = $(".date-switcher__view");
    const dates = $('select[name="flightDate"] option');
    const datesSelect = $('select[name="flightDate"]');
    const searchInput = $('input[name="search"]');
    const searchFiltersElement = $(".arrivals-timetable-search__bottom");
    const datesSelectSearch = $('select[name="flightDateSearch"]');
    let switcher = 0;
    const flightTypeInput = $('input[name="flightsInit"]');
    const loadMore = ".load-more";

    const now = () => {
        const today = new Date();
        const formattedDate =
            today.getFullYear() +
            "-" +
            String(today.getMonth() + 1).padStart(2, "0") +
            "-" +
            String(today.getDate()).padStart(2, "0");

        return formattedDate;
    };
    const request = async (term = "", isSearch = false, isLoadMore = false) => {
        loader.show();

        // Get Params

        const urlParams = new URL(window.location.href);
        const getParams = urlParams.searchParams;

        let flightsType, flightDate, search, queryType, offset;

        if (!isSearch) {
            flightsType = getParams.get("flightType") ?? "arrival";
            flightDate = getParams.get("flightDate") ?? now();
            search = getParams.get("search") ?? "";
            queryType = "query";

            if (isLoadMore) {
                offset = $(".flight").length ?? 0;
            } else {
                offset = 0;
            }
        } else {
            const flightTypeInputSearch = $(
                'input[name="flightsSearch"]:checked'
            );
            flightsType = flightTypeInputSearch.val();
            flightDate = datesSelectSearch.val();
            search = searchInput.val();
            queryType = "search";
            offset = 0;
        }

        try {
            const requestParams = new URLSearchParams();

            requestParams.append("term", search);
            requestParams.append("flightType", flightsType);
            requestParams.append("flightDate", flightDate);
            requestParams.append("_wpnonce", theme.restNonce);
            requestParams.append("queryType", queryType);
            requestParams.append("offset", offset);

            const request = await fetch(
                `${theme.searchRestUrl}?${requestParams.toString()}`,
                {
                    method: "GET",
                }
            );

            const response = await request.json();

            loader.hide();

            if (response?.success === true) {
                if (!isSearch) {
                    if (isLoadMore) {
                        $(loadMore).remove();
                        initWrapper.append(response?.data);
                    } else {
                        initWrapper.html(response?.data);
                    }
                } else {
                    searchWrapper.html(response?.data);
                }
            }
        } catch (e) {
            console.error(e.message);
        }
    };

    const loadMoreAction = (e) => {
        e.preventDefault();
        request("", false, true);
    };

    const flightTypeFilter = (e) => {
        urlApiSingle("flightType", $(e.currentTarget).val());
        request("");
    };

    const flightDateFilter = (e) => {
        urlApiSingle("flightDate", $(e.currentTarget).val());
        request("");
    };

    const searchFiltersOpen = (e) => {
        searchFiltersElement.stop(true, true).slideDown();
    };

    const searchFiltersClose = (e) => {
        const isInsideFilters =
            $(e.target).closest(searchFiltersElement).length > 0;
        const isInsideInput = $(e.target).closest(searchInput).length > 0;

        if (!isInsideFilters && !isInsideInput) {
            searchFiltersElement.stop(true, true).slideUp();
        }
    };

    const dateSwitcher = (e) => {
        const limit = dates.length - 1;
        const direction = $(e.currentTarget).data("direction");

        if (direction === "left" && switcher > 0) {
            switcher--;
        } else if (direction === "right" && switcher < limit) {
            switcher++;
        } else {
            return;
        }

        const selectedDate = dates.eq(switcher);
        datesSelect.val(selectedDate.val()).change();
        dateSwitcherView.text(selectedDate.text());
    };

    // Call after search

    search(request, false);

    // Call on init

    request("");
    $("body").on("click", loadMore, loadMoreAction);
    searchInput.on("focus", searchFiltersOpen);
    $(document).on("click", searchFiltersClose);
    datesSelect.on("change", flightDateFilter);
    flightTypeInput.on("change", flightTypeFilter);
    dateSwitcherLeft.on("click", dateSwitcher);
    dateSwitcherRight.on("click", dateSwitcher);
});
