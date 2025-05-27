$(function () {
    function initWarnings() {
        if ($('.site-warning-items-inner').length === 0) {
            return;
        }

        let hasWarningOverflow = $('.site-warning-items-inner').first()[0].offsetHeight > $('.site-warning-items').first()[0].offsetHeight

        if (hasWarningOverflow) {
            $('.site-warning-wrap').addClass('has-overflow');
        }
    }

    initWarnings();

    function toggleOpeningWarnings() {
        $('.site-warning-wrap').toggleClass('open');
    }

    $('.site-warning-expand').on('click', function () {
        toggleOpeningWarnings();
    });

    $('.site-warning-overlay').on('click', function () {
        toggleOpeningWarnings();
    });

    let totalWarnings = $('.site-warning-item').length;
    let currentWarning = 0;

    function changeIndex(moveBy) {
        currentWarning = Math.max(Math.min(currentWarning + moveBy, totalWarnings - 1), 0);
        $('.site-warning-controls-current').text(currentWarning + 1);

        $('.site-warning-item').each(function (i) {
            if (i == currentWarning) {
                $(this).addClass('current-warning');
            } else {
                $(this).removeClass('current-warning');
            }
        });


        let currentWarningType = $('.site-warning-item.current-warning').attr('data-warning');
        $('.shared-warning').attr('data-warning', currentWarningType);

    }

    function initWarningControls() {
        // NOTE: this is just in case number is not updated in php
        changeIndex(0);

        if (totalWarnings <= 1) {
            return
        }

        $('.site-warning-wrap').addClass('has-controls');
        $('.site-warning-controls-total').text(totalWarnings);
    }

    $('.site-warning-prev').on('click', function () {
        changeIndex(-1);
    });

    $('.site-warning-next').on('click', function () {
        changeIndex(1);
    });

    initWarningControls();

});