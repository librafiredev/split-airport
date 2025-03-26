/*
Install slider with npm install slick-carousel
Import from node_modules
*/

import { slick } from "slick-carousel";

const _this = {
  $dom: {
    
  },

  vars: {},

  slider: function (selector = _this.isRequired(), params = {}) {

    if($(selector).length == 0 ) {
       return false;
    }

    const defaultParams = {
      slidesToScroll: 1,
      slidesToShow: 1,
      centerMode: true,
      infinite: true,
      centerPadding: 100,
      rows: 0,
      dots: false,
      arrows: true,
      prevArrow: $(".slider-arrow arrow--left"),
      nextArrow: $(".slider-arrow arrow--right"),
    };

    const sliderParams = $.extend(defaultParams, params); 
    selector.slick(sliderParams);
  },
  isRequired : function () {
    throw new Error('Selector is required!');
  }
};

// Export only testimonialSlider

export const slider = _this.slider;

// Export default _this object

export default _this;
