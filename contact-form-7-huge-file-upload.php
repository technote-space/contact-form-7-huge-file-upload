<?php
/*
Plugin Name: Contact Form 7 huge file upload
Plugin URI:
Description: Add function to upload huge file
Author: technote
Version: 1.2.2
Author URI: https://technote.space
Text Domain: cf7-hfu
Domain Path: /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

Technote::get_instance( 'CF7_HFU', __FILE__ );
