import Panzoom from "@panzoom/panzoom";

$(function () {

    var panzooms = {};

    function centerAt(x, y, mapIndex) {
        var currentPanzoom = panzooms[mapIndex];
        if (!currentPanzoom) {
            return
        }

        var dur = 200;

        currentPanzoom.zoom(2.5, { duration: dur, animate: true });

        setTimeout(function () {
            currentPanzoom.pan(
                x,
                y,
                { animate: true }
            );
        }, dur + 10);
    }

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

    function goToFloorWithoutMobile(sectionElement, targetFloor, mapIndex) {
        sectionElement.find('.airport-map-floor-btn').removeClass('current-floor-btn');
        sectionElement.find('[data-target-floor-idx="' + targetFloor + '"]').addClass('current-floor-btn');
        sectionElement.find('.is-active-cbs').removeClass('is-active-cbs');
        sectionElement.find('[data-cbs-floor="' + targetFloor + '"]').addClass('is-active-cbs');
        sectionElement.find('.airport-guide-cb-' + targetFloor + '').addClass('is-active-cbs');
        sectionElement.find('.airport-map-active-floor').removeClass('airport-map-active-floor');
        sectionElement.find('[data-floor-idx="' + targetFloor + '"]').addClass('airport-map-active-floor');
        window.airportMaps[mapIndex].currentFloor = parseInt(targetFloor);
    }

    function executePotentiallyDelayedAction(mapIndex, action) {
        var currentPanzoom = panzooms[mapIndex];
        var shouldDelay = false;

        var zoomDur = 400;

        if (currentPanzoom) {
            if (currentPanzoom.getScale() > 1 || currentPanzoom.getScale() < 1) {
                shouldDelay = true;
            }
            currentPanzoom.pan(
                0,
                0,
                { animate: true }
            );
            setTimeout(function () {
                currentPanzoom.zoom(1, { animate: true });
            }, zoomDur * .1);
        }

        if (shouldDelay) {
            setTimeout(function () {
                action();
            }, zoomDur * 1.1)
        } else {
            action();
        }
    }

    function goToFloor(sectionElement, targetFloor, mapIndex) {
        executePotentiallyDelayedAction(mapIndex, function () {
            goToFloorWithoutMobile(sectionElement, targetFloor, mapIndex);
        });
    }

    function toggleGroupHighlight(sectionElement, groupButton, floorData) {
        var isActive = groupButton.hasClass('highlighted-sidebar-item');
        sectionElement.find('.airport-map-group').removeClass('highlighted-map-group');
        sectionElement.find('.has-target-group').removeClass('highlighted-sidebar-item');

        if (!isActive) {
            var targetSelector = groupButton.attr('data-target-group-class');
            sectionElement.find('.' + targetSelector).addClass('highlighted-map-group');
            groupButton.addClass('highlighted-sidebar-item');

            var pannable = sectionElement.find('.airport-map-pannable').eq(0)[0];
            var pannableW = pannable.clientWidth;
            var pannableH = pannable.clientHeight;

            var groupItems = sectionElement.find('.' + targetSelector).find('>.airport-map-shape-wrap');

            var x = 0;
            var y = 0;
            groupItems.each(function () {
                var gElement = $(this).eq(0)[0];
                x += (parseFloat($(this).eq(0).attr('data-original-x')) * pannableW / floorData.width) + gElement.clientWidth / 2;
                y += (parseFloat($(this).eq(0).attr('data-original-y')) * pannableH / floorData.height) + gElement.clientHeight / 2;
            });
            x = x / groupItems.length;
            y = y / groupItems.length;

            var targetX = (-x + pannableW / 2);
            var targety = (-y + pannableH / 2);
            centerAt(targetX, targety, 0);
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
                var isActive = groupButton.hasClass('highlighted-sidebar-item');
                var currentFloor = window.airportMaps[i].currentFloor;
                var currentFloorData = window.airportMaps[i].floorsData[currentFloor];

                if (parseInt(targetFloor) == currentFloor) {
                    toggleGroupHighlight(sectionElement, groupButton, currentFloorData);
                } else {
                    // NOTE: this will first disable the item before changing the floor
                    // hopefully that will make for a better ux
                    if (isActive) {
                        toggleGroupHighlight(sectionElement, groupButton, currentFloorData);
                    }
                    goToFloor(sectionElement, targetFloor, i);
                    setTimeout(function () {
                        toggleGroupHighlight(sectionElement, groupButton, currentFloorData);
                    }, 500);
                }
            });

            sectionElement.find('.airport-map-guide-cb').change(function () {
                var checkbox = this;
                var guideTargetSelector = $(checkbox).attr('data-target-guide-class');
                var guideTarget = sectionElement.find('.' + guideTargetSelector);

                executePotentiallyDelayedAction(i, function () {
                    if (checkbox.checked) {
                        guideTarget.addClass('is-guide-visible');
                    } else {
                        guideTarget.removeClass('is-guide-visible');
                    }
                });


            });

            sectionElement.find('.airport-map-floor-btn').on('click', function () {
                var targetFloor = $(this).attr('data-target-floor-idx');
                goToFloor(sectionElement, targetFloor, i);
            });

            sectionElement.find('.airport-map-search').on('input', function (e) {
                debouncedHandleSearch(sectionElement, e, flatCategories);
            });

            if (window.innerWidth < 767) {
                panzooms[i] = {};

                sectionElement.find('.airport-map-pannable').each(function () {
                    panzooms[i] = Panzoom(this, { contain: 'outside', startScale: 1.0 });

                    var isFirstTouch = true;

                    $(this).on('touchend', function () {
                        // NOTE: this is just to make it clear that map can be zoomed
                        // without this scrolling is blocked
                        if (isFirstTouch) {
                            isFirstTouch = false;
                            panzooms[i].zoom(1.5, { animate: true });
                        }
                    });
                });
            }
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
