"use strict";

    
/**  
 * 1. Add [ "select2": "^4.0.8" ], to package.json
 * 2. npm install
 * 3. Import scss file in assets/scss/plugins/select2.scss => @import '../../../node_modules/select2/dist/css/select2.min';
 * 4. Add require("select2")($);
 * 5. Initialize select2
 * 
*/
require("select2")($);

module.exports = {
	
	/*-------------------------------------------------------------------------------
		# Cache dom and strings
	-------------------------------------------------------------------------------*/
	$dom: {
		select: $("#select"),
	},
	

	/*-------------------------------------------------------------------------------
		# Initialize
	-------------------------------------------------------------------------------*/
	init: function () {

		this.$dom.select.select2();

	}
};