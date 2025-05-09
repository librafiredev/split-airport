import "select2";

const searchFiltersElement = $(".arrivals-timetable-search__bottom");
const searchInput = $('input[name="search"]');
const dateSelectSearch = $('select[name="flightDateSearch"]');
const dateSelect = $('select[name="flightDate"]');

const styleDateSelect = () => {
    dateSelectSearch.select2({
        minimumResultsForSearch: Infinity
    });
    dateSelect.select2({
        minimumResultsForSearch: Infinity
    });
};

const searchFiltersOpen = () => {
    searchFiltersElement.stop(true, true).slideDown();
    $('.arrivals-timetable-search').addClass('search-dropdown-focused');
};

const searchFiltersClose = (e) => {
    const isInsideFilters =
        $(e.target).closest(searchFiltersElement).length > 0;
    const isInsideInput = $(e.target).closest(searchInput).length > 0;

    if (!isInsideFilters && !isInsideInput) {
        searchFiltersElement.stop(true, true).slideUp();
        $('.arrivals-timetable-search').removeClass('search-dropdown-focused');
    }
};

export { searchFiltersOpen, searchFiltersClose, styleDateSelect };
