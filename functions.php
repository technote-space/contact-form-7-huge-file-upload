<?php
/**
 * @version 1.0.1.2
 * @author technote-space
 * @since 1.0.0.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

add_action( 'cf7_hfu-app_initialized', function ( $app ) {
	/** @var \Technote $app */
	$app->setting->remove_setting( 'assets_version' );
} );

add_filter( 'cf7_hfu-get_help_contents', function ( $contents, $slug ) {
	if ( 'setting' === $slug ) {
		return [];
	}

	return $contents;
}, 10, 2 );