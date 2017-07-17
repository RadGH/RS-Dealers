jQuery(function() {
	var $map_search = jQuery('#acf-field_596c486d856d8');
	if ( $map_search.length < 1 ) return;

	var $address = jQuery('#acf-field_596c4882856d9');

	var $map_address_field = $map_search.find('.acf-soh input.search');
	var $map_hidden_address_input = $map_search.find('.acf-hidden input.input-address');

	// If user entered an address in the separate field, fill it in on the map address.
	$map_address_field.on('focus', function() {
		if ( $map_hidden_address_input.val() === "" && $address.val() ) {
			$map_address_field.val( $address.val().split(/[\r\n]+/gm).join(', ') ).trigger('change');
		}
	});
});
