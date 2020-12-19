<?php
/**
 * Plugin Name: Contact Form 7 huge file upload
 * Plugin URI: https://github.com/technote-space/contact-form-7-huge-file-upload
 * Description: Add function to upload huge file
 * Author: Technote
 * Version: 1.6.18
 * Author URI: https://technote.space
 * Text Domain: cf7-hfu
 * Domain Path: /languages/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( defined( 'CF7_HFU' ) ) {
	return;
}

define( 'CF7_HFU', 'CF7_HFU' );

@require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

WP_Framework::get_instance( CF7_HFU, __FILE__ );
