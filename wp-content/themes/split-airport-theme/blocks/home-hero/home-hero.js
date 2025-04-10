import {
    searchFiltersOpen,
    searchFiltersClose,
} from "../../assets/components/searchUtils";
import request from "../../assets/components/flightsUpdateRequest";
import search from "../../assets/components/search";
import flightPopup from "../../assets/components/flightPopup";

$(function () {
    const searchInput = $('input[name="search"]');
    search(request, false);
    searchInput.on("focus", searchFiltersOpen);
    $(document).on("click", searchFiltersClose);

    // Init Flight popup

    flightPopup.init();
});
