$(function () {

    $('.request-doc-modal-btn').on('click', function () {
        $(this).closest('.procurement-table-wrapper').find('.request-doc-modal-wrapper').addClass('open');
    });

    $('.custom-modal-close-btn, .custom-modal-close-area').on('click', function () {
        $(this).closest('.custom-modal-wrapper').removeClass('open');
    });

});