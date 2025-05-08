$(function () {

    $('.map-sidebar-level-1>li>.map-sidebar-btn').on('click', function () {
        $(this).siblings('ul').eq(0).stop().slideToggle();
        $(this).toggleClass('map-sidebar-open-sub');
    });

});
