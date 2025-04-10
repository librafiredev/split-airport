const searchFiltersElement = $(".arrivals-timetable-search__bottom");
const searchInput = $('input[name="search"]');

const searchFiltersOpen = () => {
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

export {searchFiltersOpen, searchFiltersClose};