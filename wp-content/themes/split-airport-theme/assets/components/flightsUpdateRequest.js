const searchWrapper = $(".arrivals-timetable-search__bottom-results");
const initWrapper = $(".arrivals-timetable__table-flights");
const loader = $(".loader");
const datesSelectSearch = $('select[name="flightDateSearch"]');
const loadMore = ".load-more";

const request = async (term = "", isSearch = false, isLoadMore = false) => {
    loader.show();

    // Get Params

    const urlParams = new URL(window.location.href);
    const getParams = urlParams.searchParams;

    let flightsType,
        flightDate,
        search,
        queryType,
        offset,
        destination,
        earlierFlights,
        airline;

    if (!isSearch) {
        flightsType = getParams.get("flightType") || "arrival";
        flightDate = getParams.get("flightDate") || "";
        search = getParams.get("search") || "";
        destination = getParams.get("destination") || "";
        airline = getParams.get("airlineCompany") || "";
        earlierFlights = getParams.get("earlierFlights") || "";
        queryType = "query";

        if (isLoadMore) {
            offset = $(".flight").length || 0;
        } else {
            offset = 0;
        }
    } else {
        const flightTypeInputSearch = $('input[name="flightsSearch"]:checked');
        flightsType = flightTypeInputSearch.val();
        flightDate = datesSelectSearch.val();
        search = term;
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
        requestParams.append("destination", destination || "");
        requestParams.append("airlineCompany", airline || "");
        if (earlierFlights === "show") {
            requestParams.append("earlierFlights", earlierFlights);
        }

        requestParams.append("offset", offset || 0);

        const request = await fetch(
            `${theme.searchRestUrl}?${requestParams.toString()}`,
            {
                method: "GET",
            }
        );

        const response = await request.json();

        loader.hide();

        if (response.success === true) {
            if (!isSearch) {
                if (isLoadMore) {
                    $(loadMore).remove();
                    initWrapper.append(response.data);
                } else {
                    initWrapper.html(response.data);
                }
            } else {
                searchWrapper.html(response.data);
            }
        }
    } catch (e) {
        console.error(e.message);
    }
};

export default request;
