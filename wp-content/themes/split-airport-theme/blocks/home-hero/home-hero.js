import {
    searchFiltersOpen,
    searchFiltersClose,
} from "../../assets/components/searchUtils";
import request from "../../assets/components/flightsUpdateRequest";
import search from "../../assets/components/search";

$(function () {
    const searchInput = $('input[name="search"]');
    search(request, false);
    searchInput.on("focus", searchFiltersOpen);
    $(document).on("click", searchFiltersClose);
});
