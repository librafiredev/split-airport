"use strict";

/**  
 * 1. Copy equalheight.js from __js_snippets to assets/js/site
 * 2. Add const equalheight = require('./site/equalheight');
 * 3. Initialize equalheight => equalheight.init();
 * 4. Add [ data-equal='{somename}' ] on each element that you want to have same height
 * 
*/

const Global = require('./global');

let	_this;

module.exports = {

	
	/*-------------------------------------------------------------------------------
		# Cache dom and strings
	-------------------------------------------------------------------------------*/
	$dom: {
		equalElements: $("[data-equal], [class*='data-equal-']"),
    },

    defaults: {
        heights: {
            'full': {}
        },
    },
    
    vars: {
        heights: {},
	},

	/*-------------------------------------------------------------------------------
		# Initialize
	-------------------------------------------------------------------------------*/
	init: function () {

		_this = this;
		_this.bind();
    },
    
    reset: function(){

        _this.vars.heights = _this.defaults.heights;
		_this.$dom.equalElements.css('height', 'auto');
		
	},
	
	calculate: function(){

		_this.$dom.equalElements.each(function() {

			const dataType = $(this).is("[class*='data-equal-']") ? 'class' : 'data';
			let refElement, size;

            if (dataType === 'data') {
                refElement = $(this).attr('data-equal');
                size = $(this).attr('data-equal-width');
            } else {

                let classes = this.className.split(/\s+/);

                for (let i = 0; i < classes.length; i++) {
                    if (classes[i].indexOf('data-equal-') > -1) {
                        refElement = classes[i].replace('data-equal-', '');
                    }
                    if (classes[i].indexOf('data-equal-width-') > -1) {
                        size = classes[i].replace('data-equal-width-', '');
                    }
                }
            }

			
            if (size === undefined)
                size = 'full';


            if (_this.vars.heights[size] === undefined) {
                _this.vars.heights[size] = {}
            }

            if (_this.vars.heights[size][refElement] === undefined) {
                _this.vars.heights[size][refElement] = 0;
            }

            if ($(this).outerHeight() > _this.vars.heights[size][refElement]) {
                _this.vars.heights[size][refElement] = $(this).outerHeight();
			}
			
		});
		
	},

	render: function(){

		for (var breakpoint in _this.vars.heights) {
			
            var elementDataValue = _this.vars.heights[breakpoint];

            for (var element in elementDataValue) {

                if (Global.device.width > parseInt(breakpoint) || breakpoint === 'full') {
                    $("[data-equal='" + element + "'], [class*='data-equal-" + element + "']").css('height', elementDataValue[element]);
                }
            }
		}
		
	},

	bind: function(){
		Global.$dom.window.on('load', _this.recalculate);
		Global.$dom.window.on('resize', Global.functions.throttle(_this.recalculate, 100));
	},

	recalculate: function(){
		_this.reset();
		_this.calculate();
		_this.render();
	}
};