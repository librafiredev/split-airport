"use strict";

{/* <div class="tabs">
	<ul class='tab-nav'>
		<li><a href='#content-one'>1</a></li>
		<li><a href='#content-two'>2</a></li>
	</ul>
	<div class='tab-content'>
		<div id='content-one'>One</div>
		<div  id='content-two'>Two</div>
	</div>
</div> */}

let _this = module.exports = {

	
	/*-------------------------------------------------------------------------------
		# Cache dom and strings
	-------------------------------------------------------------------------------*/
	$dom: {
		tabsNav: $('.tabs .tab-nav a'),
		tabsContent: $('.tabs .tab-content li'),
    },

    vars: {
	},

	/*-------------------------------------------------------------------------------
		# Initialize
	-------------------------------------------------------------------------------*/
	init: function () {
		_this.prepare();
		_this.bind();
	},
	
	bind: function(){
		_this.$dom.tabsNav.on( 'click', _this.toggle );
	},

	prepare: function(){
		_this.$dom.tabsContent.not(':first').hide();
	},

	toggle: function(e){
		e.preventDefault();

        const elementSelector = $(this).attr('href');
        
        _this.$dom.tabsNav.css('pointer-events', 'none'); // prevent click on tab nav until fade is finished

        // Add active class on tab nav
        _this.$dom.tabsNav.parent().removeClass('active');
        $(this).parent().addClass('active');
		
		let $element = _this.$dom.tabsContent.siblings(elementSelector);

		if( $element.length > 0 ){

            // Hide Tabs
			_this.$dom.tabsContent.fadeOut(200);
            _this.$dom.tabsContent.removeClass('active');

            setTimeout(function() {
                // Show Active Tab
                $element.fadeIn(300);
                $element.addClass('active');

                // Enable if slick slider is in tabs
                //$('.slick-slider').slick('setPosition', 0); 
            }, 200);

            setTimeout(function() {
                _this.$dom.tabsNav.css('pointer-events', 'all'); // enable click on tab nav
            }, 500);
		}
		
	},

};