import {
    searchFiltersOpen,
    searchFiltersClose,
    styleDateSelect
} from "../../assets/components/searchUtils";
import request from "../../assets/components/flightsUpdateRequest";
import search from "../../assets/components/search";
import "../../assets/components/searchEvents";

$(function () {
    const searchInput = $('input[name="search"]');
    search(request, false);
    searchInput.on("focus", searchFiltersOpen);
    $(document).on("click", searchFiltersClose);

    // Style date search 

    styleDateSelect()
});
