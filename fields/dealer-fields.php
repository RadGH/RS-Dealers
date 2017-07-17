<?php
if( function_exists('acf_add_local_field_group') ):
	
	acf_add_local_field_group(array (
		'key' => 'group_596c48656c3da',
		'title' => 'Dealer Location',
		'fields' => array (
			array (
				'key' => 'field_596c4882856d9',
				'label' => 'Address',
				'name' => 'address',
				'type' => 'textarea',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'default_value' => '',
				'placeholder' => '',
				'maxlength' => '',
				'rows' => 4,
				'new_lines' => '',
			),
			array (
				'key' => 'field_596c486d856d8',
				'label' => 'Map Location',
				'name' => 'map-location',
				'type' => 'google_map',
				'instructions' => '',
				'required' => 0,
				'conditional_logic' => 0,
				'wrapper' => array (
					'width' => '',
					'class' => '',
					'id' => '',
				),
				'center_lat' => '',
				'center_lng' => '',
				'zoom' => '',
				'height' => '',
			),
		),
		'location' => array (
			array (
				array (
					'param' => 'post_type',
					'operator' => '==',
					'value' => 'rs_dealer',
				),
			),
		),
		'menu_order' => 0,
		'position' => 'normal',
		'style' => 'default',
		'label_placement' => 'left',
		'instruction_placement' => 'label',
		'hide_on_screen' => '',
		'active' => 1,
		'description' => '',
	));

endif;