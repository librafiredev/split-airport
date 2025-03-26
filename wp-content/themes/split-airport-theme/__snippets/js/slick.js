"use strict";

    
/**  
 * 1. Add [ "slick-carousel": "1.8.1" ], to package.json
 * 2. npm install
 * 3. Import this line of code into scss file inside block that use slick => @import "../../../node_modules/slick-carousel/slick/slick.scss";
 * 4. Add require('slick-carousel');
 * 5. Initialize slick
 * 
*/
require('slick-carousel');

module.exports = {
	
	/*-------------------------------------------------------------------------------
		# Cache dom and strings
	-------------------------------------------------------------------------------*/
	$dom: {
		slickSlider: $(".slick"),
	},
	

	/*-------------------------------------------------------------------------------
		# Initialize
	-------------------------------------------------------------------------------*/
	init: function () {

		this.$dom.slickSlider.slick({
			slidesToScroll: 1,
			slidesToShow: 3,
			centerMode: true,
			centerPadding: 100,
			dots: true,
			arrows: true,
			prevArrow: "<button type='button' class='slick-prev pull-left'><i class='fa fa-angle-left' aria-hidden='true'></i></button>",
			nextArrow: "<button type='button' class='slick-next pull-right'><i class='fa fa-angle-right' aria-hidden='true'></i></button>",
		});

	}
};