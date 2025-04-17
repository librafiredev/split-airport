$(function () {
    const datesSelectSearch = $('select[name="flightDateSearch"]');
    const flightTypeSearch = $('input[name="flightsSearch"]');
    const search = $('input[name="search"]');

    const triggerSearch = () => {
        search.trigger("keyup");
    };

    datesSelectSearch.on("change", triggerSearch);
    flightTypeSearch.on("change", triggerSearch);
});

