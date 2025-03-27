$(function () {

    function updateHeaderScroll() {
        if (window.scrollY > 10) {
            $('.site-header').addClass('sticky');
        } else {
            $('.site-header').removeClass('sticky');
        }
    }

    $(window).on('scroll', function () {
        updateHeaderScroll();
    });

    updateHeaderScroll();

});