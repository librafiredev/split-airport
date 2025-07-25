const _this = {

    $dom: {
        itemBtns: $('.sa-sidebar-item-btn'),
    },

    init: function () {
        _this.$dom.itemBtns.each(function () {
            var targetBlockSelector = '.' + $(this).attr('data-target-block');

            $(this).on('click', function () {
                _this.$dom.itemBtns.removeClass('is-active');
                $(this).addClass('is-active');

                $('.sa-global-block').removeClass('is-active');
                $(targetBlockSelector).addClass('is-active');
            });
        });
    }

}

export default _this;