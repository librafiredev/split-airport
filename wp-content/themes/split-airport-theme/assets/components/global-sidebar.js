const _this = {

    $dom: {
        itemBtns: $('.sa-sidebar-item-btn'),
        mobileSelectBtn: $('.sa-global-current-block-mobile'),
    },

    init: function () {
        _this.$dom.itemBtns.each(function () {
            var targetBlockSelector = '.' + $(this).attr('data-target-block');

            $(this).on('click', function () {
                var currentValueLabel = $(this).text();

                _this.$dom.itemBtns.removeClass('is-active');
                $(this).addClass('is-active');

                $('.sa-global-block').removeClass('is-active');
                $(targetBlockSelector).addClass('is-active');

                $(this).closest('.sa-global-sidebar-items-wrap').find('.sa-global-current-block-mobile .sa-current-text').text(currentValueLabel);
                $(this).closest('.sa-global-sidebar-items-wrap').removeClass('is-open');
            });
        });

        _this.$dom.mobileSelectBtn.on('click', function () {
            $(this).closest('.sa-global-sidebar-items-wrap').toggleClass('is-open');
        });
    }

}

export default _this;