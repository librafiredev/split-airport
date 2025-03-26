"use strict";
    
/**  
 * 1. Add [ "featherlight": "1.7.14" ], to package.json
 * 2. npm install
 * 3. Import this line of code into scss file inside block that use featherlight => @import '../../../node_modules/featherlight/release/featherlight.min';
 * 4. Add require('featherlight/src/featherlight')($);
 * 5. Initialize featherlight
 * 
*/

require('featherlight/src/featherlight')($);
require('featherlight/src/featherlight.gallery')($);

module.exports = {
	
	/*-------------------------------------------------------------------------------
		# Cache dom and strings
	-------------------------------------------------------------------------------*/
	$dom: {
		lightbox: $('#lightbox'),
	},
	

	/*-------------------------------------------------------------------------------
		# Initialize
	-------------------------------------------------------------------------------*/
	init: function () {

		this.$dom.lightbox.featherlight();
		this.$dom.lightbox.featherlightGallery();
	}
};