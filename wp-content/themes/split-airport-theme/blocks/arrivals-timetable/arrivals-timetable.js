import search from "../../assets/components/search";
import urlApiSingle from "../../assets/components/urlApiSingle";
import {
    searchFiltersOpen,
    searchFiltersClose,
    styleDateSelect,
} from "../../assets/components/searchUtils";
import request from "../../assets/components/flightsUpdateRequest";
import "../../assets/components/searchEvents";

$(function () {
    const dateSwitcherLeft = $(".date-switcher__left");
    const dateSwitcherRight = $(".date-switcher__right");
    const dateSwitcherView = $(".date-switcher__view");
    const dates = $('select[name="flightDate"] option');
    const datesSelect = $('select[name="flightDate"]');
    const searchInput = $('input[name="search"]');
    const flightTypeInput = $('input[name="flightsInit"]');
    const loadMore = ".load-more";
    const earlierFlights = $(".arrivals-timetable__earlier");

    const loadMoreAction = (e) => {
        e.preventDefault();
        request("", false, true);
    };

    const flightTypeFilter = (e) => {
        urlApiSingle("flightType", $(e.currentTarget).val());
        const tableTypeTitle = $(".flight-type");

        if ($(e.currentTarget).val() === "arrival") {
            tableTypeTitle.text(theme.FlightTypeTableStingArrival);
            const columnHeadingGate = $(`.arrivals-timetable__table-name.gate`);
            columnHeadingGate.remove();

            // Change search type

            $('[name="flightsSearch"][value="arrival"]').prop("checked", true);
        } else {
            tableTypeTitle.text(theme.FlightTypeTableStingDeparture);
            const flightColumn = $(
                `.arrivals-timetable__table-name.flight__expected`
            );
            const columnHeadingGate = `<span class="arrivals-timetable__table-name gate">${theme.gateTableString}</span>`;
            flightColumn.after(columnHeadingGate);

            // Change search type

            $('[name="flightsSearch"][value="departure"]').prop("checked", true);
  
        }

        request("");
    };

    const earlierFlightsAction = (e) => {
        e.preventDefault();
        $(e.currentTarget).toggleClass("active");

        if ($(e.currentTarget).hasClass("active")) {
            urlApiSingle("earlierFlights", "show");
            $(e.currentTarget)
                .find("span")
                .html(theme.earlierFlightsButtonBack);
        } else {
            urlApiSingle("earlierFlights", "show", true);
            $(e.currentTarget)
                .find("span")
                .html(theme.earlierFlightsButtonShow);
        }

        request("");
    };

    const flightDateFilter = (e) => {
        urlApiSingle("earlierFlights", "show", true);
        earlierFlights.removeClass("active");
        earlierFlights.find("span").html(theme.earlierFlightsButtonShow);

        if (
            $(e.currentTarget).find("option:selected").data("istoday") == true
        ) {
            earlierFlights.show();
        } else {
            earlierFlights.hide();
        }

        // Search date change 

        $('[name="flightDateSearch"]').val($(e.currentTarget).val());

        const select2Render = $('.arrivals-timetable-search .arrivals-timetable-search__date .select2-selection__rendered');
        select2Render.attr('title', $(e.currentTarget).find("option:selected").text())
        select2Render.html($(e.currentTarget).find("option:selected").text())

        urlApiSingle("flightDate", $(e.currentTarget).val());
        request("");
    };

    const dateSwitcher = (e) => {
        const limit = dates.length - 1;
        const direction = $(e.currentTarget).data("direction");

        let switcher = datesSelect[0].selectedIndex;

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

    // Style select

    styleDateSelect();

    $("body").on("click", loadMore, loadMoreAction);
    searchInput.on("focus", searchFiltersOpen);
    $(document).on("click", searchFiltersClose);
    datesSelect.on("change", flightDateFilter);
    flightTypeInput.on("change", flightTypeFilter);
    dateSwitcherLeft.on("click", dateSwitcher);
    dateSwitcherRight.on("click", dateSwitcher);
    earlierFlights.on("click", earlierFlightsAction);
});
