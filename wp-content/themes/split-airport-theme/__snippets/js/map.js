"use strict";


/**  
 * 1. Add [ "google-maps": "^3.3.0", ], to package.json
 * 2. npm install
 * 4. Add require('google-maps');
 * 5. Initialize google maps
 * 6. Add code to flexible template:
 * 	<?php $location = get_sub_field('location'); ?>
 * 	<div class="acf-map">
		<div class="marker" data-lat="<?php echo $location['lat']; ?>" data-lng="<?php echo $location['lng']; ?>">
		<?php echo $location['address']; ?>
		</div>
	</div>
 * 
*/
var GoogleMapsLoader = require('google-maps'); // only for common js environments
GoogleMapsLoader.KEY = theme.google_api_key;

let _this = module.exports = {

	
	/*-------------------------------------------------------------------------------
		# Cache dom and strings
	-------------------------------------------------------------------------------*/
	$dom: {
		map: $('.acf-map')
    },

    vars: {
		map: null,
		googleMapOptions: [{
			"elementType": "geometry",
			"stylers": [{
				"color": "#f5f5f5"
			}]
		}, {
			"elementType": "labels.icon",
			"stylers": [{
				"visibility": "off"
			}]
		}, {
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#616161"
			}]
		}, {
			"elementType": "labels.text.stroke",
			"stylers": [{
				"color": "#f5f5f5"
			}]
		}, {
			"featureType": "administrative.land_parcel",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#bdbdbd"
			}]
		}, {
			"featureType": "poi",
			"elementType": "geometry",
			"stylers": [{
				"color": "#eeeeee"
			}]
		}, {
			"featureType": "poi",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#757575"
			}]
		}, {
			"featureType": "poi.park",
			"elementType": "geometry",
			"stylers": [{
				"color": "#e5e5e5"
			}]
		}, {
			"featureType": "poi.park",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#9e9e9e"
			}]
		}, {
			"featureType": "road",
			"elementType": "geometry",
			"stylers": [{
				"color": "#ffffff"
			}]
		}, {
			"featureType": "road.arterial",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#757575"
			}]
		}, {
			"featureType": "road.highway",
			"elementType": "geometry",
			"stylers": [{
				"color": "#dadada"
			}]
		}, {
			"featureType": "road.highway",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#616161"
			}]
		}, {
			"featureType": "road.local",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#9e9e9e"
			}]
		}, {
			"featureType": "transit.line",
			"elementType": "geometry",
			"stylers": [{
				"color": "#e5e5e5"
			}]
		}, {
			"featureType": "transit.station",
			"elementType": "geometry",
			"stylers": [{
				"color": "#eeeeee"
			}]
		}, {
			"featureType": "water",
			"elementType": "geometry",
			"stylers": [{
				"color": "#e5e5e5"
			}]
		}, {
			"featureType": "water",
			"elementType": "labels.text.fill",
			"stylers": [{
				"color": "#9e9e9e"
			}]
		}]
	},

	/*-------------------------------------------------------------------------------
		# Initialize
	-------------------------------------------------------------------------------*/
	init: function () {
		GoogleMapsLoader.load(function(google) {

			_this.$dom.map.each(function(){

				// create map
				_this.vars.map = _this.new_map( $(this) );
		
			});

		});
	},
	
	center_map: function( map ) {

		// vars
		var bounds = new google.maps.LatLngBounds();
	
		// loop through all markers and create bounds
		$.each( map.markers, function( i, marker ){
	
			var latlng = new google.maps.LatLng( marker.position.lat(), marker.position.lng() );
	
			bounds.extend( latlng );
	
		});
	
		// only 1 marker?
		if( map.markers.length === 1 ) {
			// set center of map
			map.setCenter( bounds.getCenter() );
			map.setZoom( 16 );
		}
		else {
			// fit to bounds
			map.fitBounds( bounds );
		}
	
	},

	add_marker: function( $marker, map ) {

		// var
		var latlng = new google.maps.LatLng( $marker.attr('data-lat'), $marker.attr('data-lng') );
	
		// create marker
		var marker = new google.maps.Marker({
			position	: latlng,
			map			: map
		});
	
		// add to array
		map.markers.push( marker );
	
		// if marker contains HTML, add it to an infoWindow
		if( $marker.html() )
		{
			// create info window
			var infowindow = new google.maps.InfoWindow({
				content		: $marker.html()
			});
	
			// show info window when marker is clicked
			google.maps.event.addListener(marker, 'click', function() {
	
				infowindow.open( map, marker );
	
			});
		}
	
	},
	
	new_map: function( $el ) {
	
		// var
		var $markers = $el.find('.marker');
		
		
		// vars
		var args = {
			zoom		: 16,
			center		: new google.maps.LatLng(0, 0),
			mapTypeId	: google.maps.MapTypeId.ROADMAP,
			styles		: _this.vars.googleMapOptions
		};
		
		
		// create map	        	
		var map = new google.maps.Map( $el[0], args);
		
		
		// add a markers reference
		map.markers = [];
		
		
		// add markers
		$markers.each(function(){
			
			_this.add_marker( $(this), map );
			
		});
		
		
		// center map
		_this.center_map( map );
		
		
		// return
		return map;
		
	},


};