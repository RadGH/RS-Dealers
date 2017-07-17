<?php

if ( !defined('ABSPATH') ) die('This file should not be accessed directly.');

function rst_enqueue_scripts() {
	global $RS_Dealers;
	wp_enqueue_script( 'rs-dealer', $RS_Dealers->plugin_url . '/assets/rs-dealer.js', array('jquery'), $RS_Dealers->version );
}
add_action( 'wp_enqueue_scripts', 'rst_enqueue_scripts' );