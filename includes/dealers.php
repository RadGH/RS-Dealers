<?php

if ( !defined('ABSPATH') ) die('This file should not be accessed directly.');

function rsd_register_dealers_post_type() {
	$labels = array(
		'name'                  => 'Dealers',
		'singular_name'         => 'Dealer',
		'menu_name'             => 'Dealers',
		'name_admin_bar'        => 'Dealer',
		'archives'              => 'Dealer Archives',
		'parent_item_colon'     => 'Parent Dealer:',
		'all_items'             => 'All Dealers',
		'add_new_item'          => 'Add New Dealer',
		'add_new'               => 'Add Dealer',
		'new_item'              => 'New Dealer',
		'edit_item'             => 'Edit Dealer',
		'update_item'           => 'Update Dealer',
		'view_item'             => 'View Dealer',
		'search_items'          => 'Search Dealer',
		'not_found'             => 'Not found',
		'not_found_in_trash'    => 'Not found in Trash',
		'featured_image'        => 'Featured Image',
		'set_featured_image'    => 'Set featured image',
		'remove_featured_image' => 'Remove featured image',
		'use_featured_image'    => 'Use as featured image',
		'insert_into_item'      => 'Add into Dealer',
		'uploaded_to_this_item' => 'Uploaded to this Dealer',
		'items_list'            => 'Dealer list',
		'items_list_navigation' => 'Dealer list navigation',
		'filter_items_list'     => 'Filter Dealer list',
	);
	
	$args = array(
		'label'                 => 'Dealer',
		'description'           => 'Dealers are displayed on a google map using the shortcode [rs_dealers].',
		'labels'                => $labels,
		'supports'              => array( 'title', 'author', 'revisions', ),
		'taxonomies'            => array(),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => '32.47002',
		'menu_icon'             => 'dashicons-location-alt',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => 'dealers',
		'exclude_from_search'   => true,
		'publicly_queryable'    => false,
		'rewrite'               => false,
		'capability_type'       => 'page',
	);
	register_post_type( 'rs_dealer', $args );
}
add_action( 'init', 'rsd_register_dealers_post_type' );

function rsd_save_lat_lng_as_separate_meta_keys( $value, $post_id, $field ) {
	if ( empty($value) ) {
		delete_post_meta( $post_id, 'lat' );
		delete_post_meta( $post_id, 'lng' );
	}else{
		update_post_meta( $post_id, 'lat', $value['lat'] );
		update_post_meta( $post_id, 'lng', $value['lng'] );
	}
	
	return $value;
}
add_action('acf/update_value/key=field_596c486d856d8', 'rsd_save_lat_lng_as_separate_meta_keys', 10, 3  );
