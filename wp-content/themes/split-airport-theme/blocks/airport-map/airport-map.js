import Panzoom from "@panzoom/panzoom";

$(function () {

    var panzooms = {};
    var marginForError = .1;
    var zoomOnHighlight = 2.5;
    var zoomDur = 400;

    function simpleNormalize(input, currentStart, currentEnd, newStart, newEnd) {
        const deviderRaw = (currentEnd - currentStart);
        // NOTE: this will make it less precise/correct but it will prevent issues with "infinity/dividing by 0"
        const devider = !deviderRaw ? 0.001 : deviderRaw;
        return newStart + (input - currentStart) * (newEnd - newStart) / devider;
    }

    function centerAt(x, y, mapIndex, zoom) {
        var currentPanzoom = panzooms[mapIndex];
        if (!currentPanzoom) {
            return
        }

        var dur = 350;

        currentPanzoom.zoom(zoom, { duration: dur, animate: true });

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
            var btn = $(this);
            var submenu = btn.siblings('ul').eq(0);
            submenu.stop().slideToggle();
            btn.toggleClass('map-sidebar-open-sub');
            if (btn.hasClass('map-sidebar-open-sub')) {
                var sidebar = btn.closest('.map-sidebar-level-0');
                setTimeout(function () {
                    var targetScroll = btn.offset().top + sidebar.scrollTop() - sidebar.offset().top;

                    if (sidebar.scrollTop() + sidebar.height() < targetScroll + submenu.height()) {
                        sidebar[0].scrollTo({
                            left: 0, top: targetScroll, behavior: 'smooth',
                        });
                    }
                }, 400);
            }

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
                optionalItemToOpen.siblings('ul').each(function () {
                    $(this).children().show();
                    $(this).slideDown();
                });
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

    function isApproxZoomed(currentPanzoom) {
        return currentPanzoom.getScale() > (1 + marginForError) || currentPanzoom.getScale() < (1 - marginForError);
    }

    function executePotentiallyDelayedAction(mapIndex, action) {
        var currentPanzoom = panzooms[mapIndex];
        var shouldDelay = false;



        if (currentPanzoom) {
            if (isApproxZoomed(currentPanzoom)) {
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

    function highlightGroup(sectionElement, groupButton) {
        var targetSelector = groupButton.attr('data-target-group-class');
        sectionElement.find('.' + targetSelector).addClass('highlighted-map-group');
        groupButton.addClass('highlighted-sidebar-item');
    }

    function toggleGroupHighlight(sectionElement, groupButton, floorData, shouldDelayHighlight) {
        var isActive = groupButton.hasClass('highlighted-sidebar-item');
        sectionElement.find('.airport-map-group').removeClass('highlighted-map-group');
        sectionElement.find('.has-target-group').removeClass('highlighted-sidebar-item');

        if (!isActive) {
            var targetSelector = groupButton.attr('data-target-group-class');
            var pannable = sectionElement.find('.airport-map-pannable').eq(0)[0];
            var pannableW = pannable.clientWidth;
            var pannableH = pannable.clientHeight;

            var groupItems = sectionElement.find('.' + targetSelector).find('>.airport-map-shape-wrap');

            var minX = null;
            var maxX = null;
            var minY = null;
            var maxY = null;
            groupItems.each(function () {
                var gElement = $(this).eq(0)[0];
                var originalX = (parseFloat($(this).eq(0).attr('data-original-x')) * pannableW / floorData.width) + gElement.clientWidth / 2;
                var originalY = (parseFloat($(this).eq(0).attr('data-original-y')) * pannableH / floorData.height) + gElement.clientHeight / 2;

                if (minX == null) {
                    minX = originalX;
                    maxX = originalX;
                    minY = originalY;
                    maxY = originalY;
                } else {
                    minX = Math.min(originalX, minX);
                    maxX = Math.max(originalX, maxX);
                    minY = Math.min(originalY, minY);
                    maxY = Math.max(originalY, maxY);
                }
            });

            var x = ((minX + maxX) / 2);
            var y = ((minY + maxY) / 2);

            var targetX = (-x + pannableW / 2);
            var targety = (-y + pannableH / 2);

            var zoomScale = zoomOnHighlight;

            if (Math.abs(minX - maxX) > pannableW / (zoomOnHighlight + .1) || Math.abs(minY - maxY) > pannableH / (zoomOnHighlight + .1)) {
                zoomScale = 1.2;
            }

            centerAt(targetX, targety, 0, zoomScale);

            if (shouldDelayHighlight) {
                setTimeout(function () {
                    highlightGroup(sectionElement, groupButton);
                }, zoomDur + 300);
            } else {
                highlightGroup(sectionElement, groupButton);
            }
        }
    }

    function removeHighlights(sectionElement) {
        sectionElement.find('.airport-map-group').removeClass('highlighted-map-group');
        sectionElement.find('.has-target-group').removeClass('highlighted-sidebar-item');
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
                var currentFloor = window.airportMaps[i].currentFloor;
                var currentFloorData = window.airportMaps[i].floorsData[currentFloor];
                var currentPanzoom = panzooms[i];

                removeHighlights(sectionElement);

                setTimeout(function () {
                    var shouldGoToFloor = parseInt(targetFloor) != currentFloor;
                    var shouldDeplayHighlight = parseInt(targetFloor) != currentFloor;
                    var shouldWaitForScroll = false;

                    if (panzooms[i]) {
                        shouldDeplayHighlight = shouldDeplayHighlight || isApproxZoomed(currentPanzoom);

                        shouldWaitForScroll = true;
                    }

                    if (shouldWaitForScroll) {
                        var scrollDelay = simpleNormalize(Math.abs($(window).scrollTop() - sectionElement.offset().top), 0, 2000, 1, 1200);
                        scrollDelay = Math.max(scrollDelay, 1);
                        window.scrollTo({
                            left: 0, top: sectionElement.find('.airport-map-main').offset().top, behavior: 'smooth',
                        });
                        if (shouldGoToFloor) {
                            setTimeout(function () {
                                goToFloor(sectionElement, targetFloor, i);
                            }, scrollDelay);
                            scrollDelay += 800;
                        }
                        setTimeout(function () {
                            toggleGroupHighlight(sectionElement, groupButton, currentFloorData, shouldDeplayHighlight);
                        }, scrollDelay);
                    } else {
                        if (shouldGoToFloor) {
                            goToFloor(sectionElement, targetFloor, i);
                        }
                        toggleGroupHighlight(sectionElement, groupButton, currentFloorData, shouldDeplayHighlight);
                    }
                }, 10);
            });

            sectionElement.find('.airport-map-pannable').on('click', function () {
                removeHighlights(sectionElement);
            });

            sectionElement.find('.airport-map-guide-cb').on('change', function () {
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
                    var currentPanzoom = panzooms[i];

                    if (!isApproxZoomed(currentPanzoom)) {
                        $(this).on('touchend', function () {
                            // NOTE: this is just to make it clear that map can be zoomed
                            // without this scrolling is blocked
                            if (isFirstTouch) {
                                isFirstTouch = false;
                                panzooms[i].zoom(1.5, { animate: true });
                            }
                        });
                    }
                });
            }
        });

        $(document).on('keypress', function (e) {
            if (e.originalEvent.key === '/') {
                if ($(this).find('.airport-map-search').eq(0)[0] != document.activeElement) {
                    e.preventDefault();
                    $(this).find('.airport-map-search').eq(0).trigger('focus');
                }
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
