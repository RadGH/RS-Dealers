jQuery(function() {
	if ( typeof window.rsd_map === 'undefined' ) return;

	google.maps.event.addDomListener(window, 'load', rsd_initialize_google_map);
	
	console.log(rsd_map);
});

function rsd_initialize_google_map() {
	var map_center = new google.maps.LatLng( 44.044563, -123.061333 );
	
	if ( rsd_map.locations[0] ) {
		map_center = new google.maps.LatLng( rsd_map.locations[0].latitude, rsd_map.locations[0].longitude );
	}

	rsd_map.container = document.getElementById('rsd-map');
	rsd_map.map = new google.maps.Map( rsd_map.container, rsd_map.map_options );
	rsd_map.infowindow = new google.maps.InfoWindow({ disableAutoPan: false });
	rsd_map.bounds = new google.maps.LatLngBounds();

	// Add markers for each post, if any
	for ( i in rsd_map.locations ) {
		var post = rsd_map.locations[i];

		var coords = new google.maps.LatLng( post.lat, post.lng );
		rsd_map.bounds.extend(coords);
		rsd_map.has_bounds = true;

		var marker_args = {
			position: coords,
			map: rsd_map.map,
			title: post.title
		};

		// Use a custom icon?
		if ( rsd_map.icon ) {
			marker_args.icon = rsd_map.icon;
		}

		// Add the marker
		var marker = new google.maps.Marker( marker_args );

		// Attach the post so we can use it with the info window later
		marker.post = post;

		// Bind a click event for info windows.
		google.maps.event.addListener( marker, 'click', map_click_marker );

		rsd_map.markers.push( marker );
	}


	google.maps.event.addListenerOnce( rsd_map.map, 'idle', function(){
		// Reposition the map when it is ready, by marker bounds or by default coordinates
		if ( rsd_map.has_bounds ) {
			rsd_map.map.fitBounds(rsd_map.bounds);
		}else{
			rsd_map.map.setCenter( map_center );
		}

		// Force a default zoom level so we don't get too close
		if ( rsd_map.map.getZoom() > 16 ) rsd_map.map.setZoom(16);
	});

	function map_click_marker() {
		var html = '<p><strong>' + this.post.title + '</strong></p><p>' + this.post.address + '</p>';

		if ( this.post.distance ) html += '<p>Distance: ' + this.post.distance + '</p>';

		rsd_map.infowindow.close();
		rsd_map.infowindow.setContent( '<div class="loc-info">' + html + '</div>' );
		rsd_map.infowindow.setContent(rsd_map.infowindow.getContent());
		rsd_map.infowindow.open( rsd_map.map, this );

		var offset = jQuery('.loc-info').height();

		if ( this.post.icon ) {
			offset -= this.post.icon.scale[1];
		}

		locator_center_map_offset( this.getPosition(), 0, -1 * offset );
	}

	function locator_center_map_offset( position, offsetx, offsety ) {
		// http://stackoverflow.com/questions/3473367/#answer-10722973
		var point1 = rsd_map.map.getProjection().fromLatLngToPoint( position );
		var point2 = new google.maps.Point(
			( (typeof(offsetx) === 'number' ? offsetx : 0) / Math.pow(2, rsd_map.map.getZoom()) ) || 0,
			( (typeof(offsety) === 'number' ? offsety : 0) / Math.pow(2, rsd_map.map.getZoom()) ) || 0
		);

		rsd_map.map.setCenter(rsd_map.map.getProjection().fromPointToLatLng(new google.maps.Point(
			point1.x - point2.x,
			point1.y + point2.y
		)));
	}
}