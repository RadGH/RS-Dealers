<?php

if ( !defined('ABSPATH') ) die('This file should not be accessed directly.');

function rst_register_dealers_post_type() {
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
		'description'           => 'Display dealers from your clients and visitors.',
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'author', 'thumbnail', 'revisions', ),
		'taxonomies'            => array(),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => '32.47004',
		'menu_icon'             => 'dashicons-groups',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => 'dealers',
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'rewrite'               => array(
			'slug' => 'dealer',
		    'with_front' => false,
		),
		'capability_type'       => 'page',
	);
	register_post_type( 'rs_dealer', $args );
}
add_action( 'init', 'rst_register_dealers_post_type' );

/**
 * Generated by the WordPress Meta Box Generator at http://goo.gl/8nwllb
 */
class rst_metabox {
	private $screens = array(
		'rs_dealer',
	);
	
	private $fields = array(
		array(
			'id' => 'email',
			'label' => 'Email',
			'type' => 'email',
		),
		array(
			'id' => 'organization-location',
			'label' => 'Organization/Location',
			'type' => 'text',
		),
	);
	
	/**
	 * Class construct method. Adds actions to their respective WordPress hooks.
	 */
	public function __construct() {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		add_action( 'save_post', array( $this, 'save_post' ) );
	}
	
	/**
	 * Hooks into WordPress' add_meta_boxes function.
	 * Goes through screens (post types) and adds the meta box.
	 */
	public function add_meta_boxes() {
		foreach ( $this->screens as $screen ) {
			add_meta_box(
				'dealer-author',
				__( 'Dealer Details', 'rational-metabox' ),
				array( $this, 'add_meta_box_callback' ),
				$screen,
				'normal',
				'high'
			);
		}
	}
	
	/**
	 * Generates the HTML for the meta box
	 *
	 * @param object $post WordPress post object
	 */
	public function add_meta_box_callback( $post ) {
		wp_nonce_field( 'dealer_author_data', 'dealer_author_nonce' );
		$this->generate_fields( $post );
	}
	
	/**
	 * Generates the field's HTML for the meta box.
	 *
	 * @param $post
	 */
	public function generate_fields( $post ) {
		$output = '';
		foreach ( $this->fields as $field ) {
			$label = '<label for="' . $field['id'] . '">' . $field['label'] . '</label>';
			$db_value = get_post_meta( $post->ID, $field['id'], true );
			switch ( $field['type'] ) {
				default:
					$input = sprintf(
						'<input %s id="%s" name="%s" type="%s" value="%s">',
						$field['type'] !== 'color' ? 'class="regular-text"' : '',
						$field['id'],
						$field['id'],
						$field['type'],
						$db_value
					);
			}
			$output .= $this->row_format( $label, $input );
		}
		echo '<table class="form-table"><tbody>' . $output . '</tbody></table>';
	}
	
	/**
	 * Generates the HTML for table rows.
	 *
	 * @param $label
	 * @param $input
	 *
	 * @return string
	 */
	public function row_format( $label, $input ) {
		return sprintf(
			'<tr><th scope="row">%s</th><td>%s</td></tr>',
			$label,
			$input
		);
	}
	
	/**
	 * Hooks into WordPress' save_post function
	 *
	 * @param $post_id
	 *
	 * @return mixed
	 */
	public function save_post( $post_id ) {
		if ( ! isset( $_POST['dealer_author_nonce'] ) )
			return $post_id;
		
		$nonce = $_POST['dealer_author_nonce'];
		if ( !wp_verify_nonce( $nonce, 'dealer_author_data' ) )
			return $post_id;
		
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return $post_id;
		
		foreach ( $this->fields as $field ) {
			if ( isset( $_POST[ $field['id'] ] ) ) {
				switch ( $field['type'] ) {
					case 'email':
						$_POST[ $field['id'] ] = sanitize_email( $_POST[ $field['id'] ] );
						break;
					case 'text':
						$_POST[ $field['id'] ] = sanitize_text_field( $_POST[ $field['id'] ] );
						break;
				}
				update_post_meta( $post_id, $field['id'], $_POST[ $field['id'] ] );
			} else if ( $field['type'] === 'checkbox' ) {
				update_post_meta( $post_id, $field['id'], '0' );
			}
		}
		
		return $post_id;
	}
}
new rst_metabox;