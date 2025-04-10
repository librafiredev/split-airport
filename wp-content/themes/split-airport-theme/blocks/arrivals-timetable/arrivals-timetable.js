import search from "../../assets/components/search";
import urlApiSingle from "../../assets/components/urlApiSingle";
import { searchFiltersOpen, searchFiltersClose } from "../../assets/components/searchUtils";
import request from "../../assets/components/flightsUpdateRequest";
import flightPopup from "../../assets/components/flightPopup";

$(function () {
  
    const dateSwitcherLeft = $(".date-switcher__left");
    const dateSwitcherRight = $(".date-switcher__right");
    const dateSwitcherView = $(".date-switcher__view");
    const dates = $('select[name="flightDate"] option');
    const datesSelect = $('select[name="flightDate"]');
    const searchInput = $('input[name="search"]');
    const flightTypeInput = $('input[name="flightsInit"]');
    const loadMore = ".load-more";
    
    const loadMoreAction = (e) => {
        e.preventDefault();
        request("", false, true);
    };

    const flightTypeFilter = (e) => {
        urlApiSingle("flightType", $(e.currentTarget).val());
        const tableTypeTitle = $('.flight-type');

        if($(e.currentTarget).val() === 'arrival') {
            tableTypeTitle.text(theme.FlightTypeTableStingArrival);
        }

        else {
            tableTypeTitle.text(theme.FlightTypeTableStingDeparture);
        }
       
        request("");
    };

    const flightDateFilter = (e) => {
        urlApiSingle("flightDate", $(e.currentTarget).val());
        request("");
    };

    const dateSwitcher = (e) => {
        const limit = dates.length - 1;
        const direction = $(e.currentTarget).data("direction");

        let switcher = datesSelect[0].selectedIndex

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

    // Init Flight popup

    flightPopup.init()

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





