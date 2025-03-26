const global  = {
	
	$dom: {
		window: $(window),
		body: $('body')
	},

	device: {
		isMobile: false,
		isTablet: false,
		isPortable: false,
		width: window.innerWidth ? window.innerWidth : document.documentElement.clientWidth ? document.documentElement.clientWidth : screen.width,
		height: window.innerHeight ? window.innerHeight : document.documentElement.clientHeight ? document.documentElement.clientHeight : screen.height,
	},

	vars: {
		keys: { 37: 1, 38: 1, 39: 1, 40: 1 },
		scrollAllowed: [
			$('.main-navigation')
		],
	},

	functions: {

		escKey: function (callback) {
			$(document).on('keyup', function (e) {
				if (e.keyCode === 27) {
					callback();
				}
			});
		},

		clickOutsideContainer: function (selector, container, closeBtn, callback) {

			global.privateFunctions.convertToObject( arguments );

			$(selector).on('mouseup', function (e) {
				e.preventDefault();
				if (!container.is(e.target) && container.has(e.target).length === 0 && !$(closeBtn).is( $(e.target) ) ) {
					callback();
				}
			});
		},

		throttle: function(fn, wait) {
			
			var time = Date.now();

			return function() {
				if ((time + wait - Date.now()) < 0) {
					fn();
					time = Date.now();
				}
			}
		},
	
		disableScroll: function() {
			if (window.addEventListener) {
				window.addEventListener('DOMMouseScroll', global.privateFunctions.preventDefault, {passive: false});
			}
				
			document.addEventListener('wheel', global.privateFunctions.preventDefault, {passive: false}); // Disable scrolling in Chrome
			document.onkeydown  = global.privateFunctions.preventDefaultForScrollKeys;
	
			window.addEventListener('touchmove', global.privateFunctions.preventDefault, { passive: false });
			window.addEventListener('mousewheel', global.privateFunctions.preventDefault, { passive: false });
			document.addEventListener('mousewheel', global.privateFunctions.preventDefault, { passive: false });
			window.addEventListener('wheel', global.privateFunctions.preventDefault, { passive: false });
	
		},
	
		enableScroll: function() {
			if (window.removeEventListener) {
				window.removeEventListener('DOMMouseScroll', global.privateFunctions.preventDefault, {passive: false});
			}
				
			document.removeEventListener('wheel', global.privateFunctions.preventDefault, {passive: false}); // Enable scrolling in Chrome
	
			window.removeEventListener('touchmove', global.privateFunctions.preventDefault, { passive: false });
			window.removeEventListener('mousewheel', global.privateFunctions.preventDefault, { passive: false });
			document.removeEventListener('mousewheel', global.privateFunctions.preventDefault, { passive: false });
			window.removeEventListener('wheel', global.privateFunctions.preventDefault, { passive: false });
	
		}
		
	},

	privateFunctions: {

		init: function(){
			global.privateFunctions.setupSizes();
			global.privateFunctions.setupEvents();
		},

		setupSizes: function(){

			global.device.width = $(window).outerWidth();
			global.device.height = $(window).outerHeight();

			if( global.device.width <= 1199 && global.device.width >= 768 ){
				global.device.isTablet = true;
				global.device.isMobile = false;
				global.device.isPortable = true;
			}
			else if( global.device.width >= 1199 ){
				global.device.iTablet = false;
				global.device.isMobile = false;
				global.device.isPortable = false;
			}
			else if( global.device.width < 768 ){
				global.device.iTablet = false;
				global.device.isMobile = true;
				global.device.isPortable = true;
			}

		},
		setupEvents: function(){
			global.$dom.window.on( 'resize', global.functions.throttle( this.setupSizes, 200 ) );
		},
		convertToObject: function( items ){
			
			for( let i = 0; i < items.length; i++ ){

				if( items[i] instanceof Function ){ break }

				if( ( items[i] instanceof jQuery ) === false ){
					items[i] = $(items[i]);
				}
			}
		
		},

		preventDefault: function(e) {

			for( let allowedElement in global.vars.scrollAllowed ){
				let $element = global.vars.scrollAllowed[allowedElement];

				if( $element.is(e.target) || $element.has( $(e.target) ).length > 0 ){
					return;
				}
			}

			e = e || window.event;
			if (e.preventDefault){
				e.preventDefault();
			}
			e.returnValue = false;  
		},
	
		preventDefaultForScrollKeys: function(e) {
			if (global.vars.keys[e.keyCode]) {
				global.preventDefault(e);
				return false;
			}
		},
	}
};

global.privateFunctions.init();

export default global;
