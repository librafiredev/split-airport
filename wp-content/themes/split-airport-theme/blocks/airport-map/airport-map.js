$(function () {

    function initSidebarAccordions() {
        $('.map-sidebar-level-1>li>.map-sidebar-btn').on('click', function () {
            $(this).siblings('ul').eq(0).stop().slideToggle();
            $(this).toggleClass('map-sidebar-open-sub');
        });
    }

    function debounce(func, timeout = 300) {
        let timer;
        return (...args) => {
            clearTimeout(timer);
            timer = setTimeout(() => { func.apply(this, args); }, timeout);
        };
    }

    function handleSidebarSearch(sectionElement, e, flatCategories) {
        var inputValue = e.target.value;

        if (inputValue) {
            sectionElement.addClass('has-active-search');
        } else {
            sectionElement.removeClass('has-active-search');
        }

        let matches = flatCategories.filter((item) => {
            if (item.label) {
                const regex = new RegExp('.*' + inputValue + '.*', 'gi');

                const found = (item.label).match(regex);

                if (found) {
                    return true;
                }
            }
            return false
        });

        sectionElement.find('.map-sidebar-searchable').removeClass('found-item');

        matches.forEach(function (item) {
            var searchableEl = sectionElement.find('.' + item.html_class);
            searchableEl.addClass('found-item');
            var optionalItemToOpen = searchableEl.closest('.map-sidebar-level-1').find('>li>.map-sidebar-btn');

            if (optionalItemToOpen && inputValue) {
                optionalItemToOpen.addClass('map-sidebar-open-sub');
                optionalItemToOpen.siblings('ul').eq(0).stop().slideDown();
            }
        });

        if (!matches.length) {
            sectionElement.find('.airport-map-no-results').removeClass('hidden-no-results');
            sectionElement.find('.airport-map-search-term').text(inputValue);
        } else {
            sectionElement.find('.airport-map-no-results').addClass('hidden-no-results');
        }
    }

    const debouncedHandleSearch = debounce((sectionElement, event, someData) => {
        handleSidebarSearch(sectionElement, event, someData);
    }, 300);

    function goToFloor(sectionElement, targetFloor, mapIndex) {
        sectionElement.find('.airport-map-floor-btn').removeClass('current-floor-btn');
        sectionElement.find('[data-target-floor-idx="' + targetFloor + '"]').addClass('current-floor-btn');
        sectionElement.find('.airport-map-active-floor').removeClass('airport-map-active-floor');
        sectionElement.find('[data-floor-idx="' + targetFloor + '"]').addClass('airport-map-active-floor');
        window.airportMaps[mapIndex].currentFloor = parseInt(targetFloor);
    }

    function highlightGroup(sectionElement, groupButton) {
        var isActive = groupButton.hasClass('highlighted-sidebar-item');
        sectionElement.find('.airport-map-group').removeClass('highlighted-map-group');
        sectionElement.find('.has-target-group').removeClass('highlighted-sidebar-item');

        if (!isActive) {
            var targetSelector = groupButton.attr('data-target-group-class');
            sectionElement.find('.' + targetSelector).addClass('highlighted-map-group');
            groupButton.addClass('highlighted-sidebar-item');
        }
    }

    function initInteractables() {
        $('.airport-map-wrapper').each(function (i) {
            var sectionElement = $(this);
            $(this).addClass('map-initialized');

            var categories = window.airportMaps[i].categories;
            function flattenNestedObjects(prev, a) {
                if (a.children) {
                    return [a, ...a.children.reduce(flattenNestedObjects, prev)];
                }

                return [...prev, a];
            }

            var flatCategories = categories.reduce(flattenNestedObjects, []);

            sectionElement.find('.has-target-group').on('click', function () {
                var groupButton = $(this);
                var targetFloor = groupButton.attr('data-target-floor');
                if (parseInt(targetFloor) == window.airportMaps[i].currentFloor) {
                    highlightGroup(sectionElement, groupButton);

                } else {
                    goToFloor(sectionElement, targetFloor, i);
                    setTimeout(function () {
                        highlightGroup(sectionElement, groupButton);
                    }, 500);
                }
            });

            sectionElement.find('.airport-map-floor-btn').on('click', function () {
                var targetFloor = $(this).attr('data-target-floor-idx');
                goToFloor(sectionElement, targetFloor, i);
            });

            sectionElement.find('.airport-map-search').on('input', function (e) {
                debouncedHandleSearch(sectionElement, e, flatCategories);
            });

        });
    }

    function init() {
        if ($('.airport-map-wrapper').length < 1) {
            return;
        }

        if ($('.airport-map-wrapper').eq(0).hasClass('map-initialized')) {
            return;
        }

        // NOTE: all of the maps are going to be initialized by one script through a loop
        $('.airport-map-wrapper').eq(0).addClass('map-initialized');

        initInteractables();
        initSidebarAccordions();
    }

    init();

});
