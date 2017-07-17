<?php

if ( !defined('ABSPATH') ) die('This file should not be accessed directly.');

function shortcode_rs_dealer_form( $atts, $content = '' ) {
	$search_location = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : false;
	$search_distance = isset($_REQUEST['distance']) ? stripslashes($_REQUEST['distance']) : false;
	
	$args = array(
		'post_type' => 'rs_dealer',
	    'nopaging' => true,
	);
	
	if ( $search_location !== false ) {
		$geocoded_location = rsd_geocode_address_to_coordinates($search_location);
		
		if ( $geocoded_location && !is_wp_error($geocoded_location)) {
			$args['geo_query'] = array(
				'lat' => $geocoded_location['latitude'],
				'lng' => $geocoded_location['longitude'],
				'lat_meta_key' => 'lat',
				'lng_meta_key' => 'lng',
				'radius' => $search_distance ? (int) $search_distance : 50,
				'distance_unit' => 69.0, // 69.0 = Miles, and 111.045 = Kilometers
			);
		}else{
			if ( is_wp_error($geocoded_location) ) {
				return 'Error ('. esc_html($geocoded_location->get_error_code()) .'): ' . esc_html($geocoded_location->get_error_message());
			}
		}
	}
	
	$geo_query = new WP_Query( $args );
	
	ob_start();
	
	if ( $geo_query->have_posts() ) {
		
		$map_locations = array();
		
		while( $geo_query->have_posts() ): $geo_query->the_post();
			global $post;
			
			$distance = isset($post->distance_value) ? preg_replace('/\.?0+$/', '', number_format($post->distance_value, 2)) : false;
			if ( $distance ) $distance = sprintf(_n('%s Mile', '%s Miles', $distance), $distance);
			
			$map_locations[] = array(
				'title' => get_the_title(),
			    'address' => get_field( 'address' ),
			    'lat' => get_post_meta( get_the_ID(), 'lat', true ),
			    'lng' => get_post_meta( get_the_ID(), 'lng', true ),
			    'distance' => $distance
			);
		endwhile;
		wp_reset_postdata();
		
		$map_options = array(
			'zoom' => 9,
		);
		
		global $RS_Dealers;
		$icon_url = $RS_Dealers->plugin_url . '/assets/dealer-pin.png';
		
		?>
		<div class="rs-dealers">
			<div class="rs-dealer-map">
				<div class="rsd-google-map">
					<div id="rsd-map"></div>
					<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php echo esc_attr(rsd_get_maps_api_key()); ?>&sensor=false"></script>
					<script>
						var rsd_map = {
							container: false,
							map: false,
							bounds: false,
							has_bounds: false,
							markers: [],
							infowindow: false,
							icon: {
								url: <?php echo json_encode($icon_url); ?>,
								size: new google.maps.Size( 30, 30 ),
								scaledSize: new google.maps.Size( 30, 30 ),
								origin: new google.maps.Point( 0, 0 ),
								anchor: new google.maps.Point( 15, 15 )
							},
							locations: <?php echo json_encode($map_locations); ?>,
							map_options: <?php echo json_encode($map_options); ?>
						};
					</script>
				</div>
				<div class="rsd-map-search-overlay">
					<?php __rsd_dealer_search_location_form(); ?>
				</div>
			</div>
			
			<div class="rs-dealer-list">
				<?php
				foreach( $map_locations as $loc ) {
					$title = $loc['title'];
					$address = $loc['address'];
					$distance = $loc['distance'];
					?>
					<article <?php post_class('rs-dealer-item'); ?>>
						
						<header class="loop-header">
							<h2 class="loop-title"><?php echo esc_html( $title ); ?></h2>
						</header>
						
						<?php if ( $address || $distance ) { ?>
						<div class="loop-body">
						
							<?php if ( $address ) { ?>
								<div class="loop-content">
									<?php echo nl2br(esc_html($address)); ?>
								</div>
							<?php } ?>
							
							<?php if ( $distance ) { ?>
								<div class="loop-distance">
									<?php echo $distance; ?>
								</div>
							<?php } ?>
						
						</div>
						<?php } ?>
					
					</article>
					<?php
				}
				?>
			</div>
		</div>
		<?php
		
	}else{
		
		if ( $search_location ) {
			?>
			<p><em>Sorry, no dealers were found near your location.</em></p>
			
			<?php
			__rsd_dealer_search_location_form();
		}else{
			?>
			<p><em>Sorry, no dealers are available at the moment.</em></p>
			<?php
		}
	}
	
	return ob_get_clean();
}
add_shortcode( 'rs_dealers', 'shortcode_rs_dealer_form' );

function __rsd_dealer_search_location_form() {
	$search_location = isset($_REQUEST['location']) ? stripslashes($_REQUEST['location']) : false;
	$search_distance = isset($_REQUEST['distance']) ? stripslashes($_REQUEST['distance']) : false;
	
	?>
	<form action="" method="GET" class="rsd-map-search-form">
		<label for="rs-location">Find a Dealer:</label>
		
		<div class="dealer-field location"><input type="text" name="location" id="rs-location" placeholder="City/State or Zip Code" value="<?php echo $search_location ? esc_attr($search_location) : ""; ?>"></div>
		
		<div class="dealer-field distance"><input type="text" name="distance" id="rs-distance" placeholder="Distance (Miles)" value="<?php echo $search_distance ? esc_attr($search_distance) : ""; ?>"></div>
		
		<div class="dealer-submit"><input type="submit" value="Search"></div>
	</form>
	<?php
}

function rsd_geocode_address_to_coordinates( $address ) {
	$key = rsd_get_maps_api_key();
	
	$args = array();
	$args['address'] = $address;
	if ( $key ) $args['key'] = $key;
	
	$url = 'https://maps.googleapis.com/maps/api/geocode/json';
	$geocode = wp_remote_get( add_query_arg( $args, $url ) );
	
	// If the request failed, or we're missing crucial information, return with an error
	if ( !$geocode || is_wp_error($geocode) || $geocode['response']['message'] !== 'OK' || !$geocode['body'] ) {
		return new WP_Error( 'geocoding_failed', 'The Google Geocoding lookup failed. Unable to connect to the server or unexpected response from the server.', $geocode );
	}
	
	$json = json_decode( $geocode['body'], true );
	
	// If the request was OK but the data is no good, return with an error
	if ( !$json || !isset($json['results']) || !$json['results'] || !isset($json['results'][0]['geometry']['location']) ) {
		return new WP_Error( 'geocoding_no_location', 'No results were found for the provided location.', $geocode );
	}
	
	$lat = (float) $json['results'][0]['geometry']['location']['lat'];
	$lng = (float) $json['results'][0]['geometry']['location']['lng'];
	
	return array(
		'latitude' => $lat,
		'longitude' => $lng,
		'result' => $geocode,
	);
}

function rsd_get_maps_api_key() {
	return apply_filters( 'locator_geocoding_key', 'AIzaSyDwt4-tH-EogatekFi7QuQsg95OQYl4Hd8' );
}