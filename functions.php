<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'CF7_HFU' ) ) {
	return;
}

add_action( 'cf7_hfu/app_initialized', function ( $app ) {
	/** @var \WP_Framework $app */
	$app->setting->remove_setting( 'assets_version' );
} );
