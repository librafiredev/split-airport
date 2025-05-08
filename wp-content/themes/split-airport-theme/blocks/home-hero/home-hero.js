import {
    searchFiltersOpen,
    searchFiltersClose,
    styleDateSelect
} from "../../assets/components/searchUtils";
import request from "../../assets/components/flightsUpdateRequest";
import search from "../../assets/components/search";
import flightPopup from "../../assets/components/flightPopup";
import  "../../assets/components/searchEvents";

$(function () {
    const searchInput = $('input[name="search"]');
    search(request, false);
    searchInput.on("focus", searchFiltersOpen);
    $(document).on("click", searchFiltersClose);

    // Init Flight popup

    flightPopup.init();

    // Style date search 

    styleDateSelect()
});
