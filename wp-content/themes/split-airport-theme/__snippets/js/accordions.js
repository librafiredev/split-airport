// Add some hide class to items to hide them on page init

const _this = {

    $dom: {

    },

    accordions : function(trigger = _this.isRequired(), items = _this.isRequired()) {

        trigger.on('click', _this.accordionsAction);

        _this.$dom.items = items;
        _this.$dom.triggers = trigger;

    },
    accordionsAction : function() {

        let triggered = $(this);

        if(!triggered.hasClass('open')) {

            // Close all the others
            _this.$dom.items.stop().slideUp('.4s');
            _this.$dom.triggers.removeClass('open');

            // Open Current

            triggered.next().stop().slideDown('.4s');
            triggered.addClass('open');
            
        }
        else {

            // Close Just Current

            triggered.next().stop().slideUp('.4s');
            triggered.removeClass('open');
        }
    },

    isRequired : function () {
        throw new Error('Accordion function missing parameters');
    }

}

// Export accordions function

export const accordions = _this.accordions;

export default _this;