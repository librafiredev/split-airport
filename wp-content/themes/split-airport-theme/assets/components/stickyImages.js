const _this = {
    blocks: [],
    visibilityTreshholds: [],
    replacementImages: [],
    imgSelector: '.image-accordion__image img, .image-content__image img, .image-box__image img',
    innerSelector: '.image-accordion__left, .image-content__left, .image-box__left',
    heightCarrierItems: [],
    scrollOffset: .5,
    isRendered: false,
    isInitialized: false,

    shouldHandleSticky: () => {
        if ($(window).width() < 767) {
            return false;
        }

        return true;
    },

    updateCurrentImageVisibility: () => {
        if (!_this.shouldHandleSticky()) {
            return;
        }

        _this.visibilityTreshholds.forEach(function (tresholds, i) {
            if (tresholds.length > 0) {
                tresholds.forEach(function (treshold, j) {
                    if (($(window).scrollTop() + $(window).height() * _this.scrollOffset) > treshold || j == 0) {
                        _this.replacementImages[i][j].addClass("visible-sticky");
                    } else {
                        _this.replacementImages[i][j].removeClass("visible-sticky");
                    }
                });
            }
        });
    },

    updateStickyElementVariables: () => {
        if (!_this.shouldHandleSticky()) {
            return;
        }

        _this.visibilityTreshholds = [];

        _this.blocks.forEach(function (block) {
            var tresholds = [];

            if (block.type != 'no-images') {
                var imagesContainer = block.imagesContainer;
                var stickyCarrier = block.items[0].node;

                var itemNum = block.items.length;
                var lastItem = block.items[itemNum - 1];

                var innerItem = lastItem.node.find(_this.innerSelector).eq(0);
                var containerHeight = (innerItem.offset().top + innerItem.height()) - stickyCarrier.offset().top;

                imagesContainer.css({ height: containerHeight + 'px' });

                block.items.forEach(function (item) {
                    var img = item.node.find(_this.imgSelector);
                    tresholds.push(img.offset().top);
                });
            }

            _this.visibilityTreshholds.push(tresholds);
        });
    },

    renderStickyElements: () => {
        if (!_this.shouldHandleSticky()) {
            return;
        }

        if (_this.isRendered) {
            return;
        }

        _this.isRendered = true;

        _this.blocks.forEach(function (block, idx) {
            var replacementImages = [];

            if (block.type != 'no-images') {
                var stickyCarrier = block.items[0].node;

                stickyCarrier.addClass('has-sticky-images');
                var imagesWrap = $('<div class="sticky-images-wrap"></div>');
                var imagesContainer = $('<div class="container"></div>');
                _this.blocks[idx].imagesContainer = imagesContainer;
                var stickyWrapColumn = $('<div class="sticky-wrap-column ' + block.type + '"></div>');
                var stickyImageWrap = $('<div class="sticky-image-wrap"></div>');

                block.items.forEach(function (item, i) {
                    var img = item.node.find(_this.imgSelector);
                    var imgSrc = img.attr('src');

                    var rImg = $('<img class="sticky-image" />');
                    rImg.attr('src', imgSrc);
                    if (i == 0) {
                        setTimeout(function () {
                            rImg.addClass("visible-sticky");
                        }, 300);
                    }
                    replacementImages.push(rImg);
                    stickyImageWrap.append(rImg);
                });

                stickyWrapColumn.append(stickyImageWrap);
                imagesContainer.append(stickyWrapColumn);
                imagesWrap.append(imagesContainer);
                stickyCarrier.append(imagesWrap);
            }

            _this.replacementImages.push(replacementImages);

        });

        _this.updateStickyElementVariables();

        setTimeout(function () {
            _this.updateCurrentImageVisibility();
        }, 300);
    },

    setupStickyBlocks: () => {
        var currentBlockType = 'no-images';

        var blocksContainer = $('section.image-accordion, section.image-content, section.image-box').eq(0).parent();

        if (!_this.shouldHandleSticky()) {
            return;
        }

        if (blocksContainer.hasClass('sticky-images-initialized')) {
            return;
        }

        if (_this.isInitialized) {
            return;
        }

        _this.isInitialized = true;

        _this.blocks = [];

        blocksContainer.addClass('sticky-images-initialized');

        blocksContainer.children().each(function () {
            var prevBlockType = currentBlockType;
            currentBlockType = 'no-images';

            if (!$(this).has(_this.imgSelector).length) {
                currentBlockType = 'no-images';
            } else if ($(this).hasClass('image-content')) {
                currentBlockType = 'right-images';
            } else if ($(this).hasClass('image-accordion') || $(this).hasClass('image-box')) {
                currentBlockType = 'left-images';
            }

            var itemData = {
                node: $(this),
            };

            if (currentBlockType != prevBlockType || !_this.blocks.length) {
                _this.blocks.push({
                    type: currentBlockType,
                    items: [itemData],
                });
            } else {
                _this.blocks[_this.blocks.length - 1].items.push(itemData);
            }

        });

        _this.renderStickyElements();
    },

    init: () => {
        _this.setupStickyBlocks();

        _this.bind();
    },

    bind: () => {
        $(window).on('resize', function () {
            _this.setupStickyBlocks();
            _this.updateStickyElementVariables();
            _this.updateCurrentImageVisibility();
        });
        $(window).on('scroll', _this.updateCurrentImageVisibility);
    }
}

export default _this;