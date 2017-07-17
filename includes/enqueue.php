<?php

if ( !defined('ABSPATH') ) die('This file should not be accessed directly.');

function rsd_enqueue_admin_scripts() {
	global $RS_Dealers;
	wp_enqueue_script( 'rs-dealer-admin', $RS_Dealers->plugin_url . '/assets/rs-dealer-admin.js', array('jquery'), $RS_Dealers->version );
}
add_action( 'admin_enqueue_scripts', 'rsd_enqueue_admin_scripts' );

function rsd_enqueue_scripts() {
	global $RS_Dealers;
	wp_enqueue_script( 'rs-dealer', $RS_Dealers->plugin_url . '/assets/rs-dealer.js', array('jquery'), $RS_Dealers->version );
}
add_action( 'wp_enqueue_scripts', 'rsd_enqueue_scripts' );