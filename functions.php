<?php
/**
 * @version 1.0.0.3
 * @author technote-space
 * @since 1.0.0.3
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}

add_filter( 'cf7_hfu-get_settings', function ( $data, $group ) {
	if ( 'Others' === $group ) {
		foreach ( $data as $k => $v ) {
			if ( 'assets_version' === $v ) {
				unset( $data[ $k ] );
			}
		}
	}

	return $data;
}, 10, 2 );

add_filter( 'cf7_hfu-get_help_contents', function ( $contents, $slug ) {
	if ( 'setting' === $slug ) {
		return [];
	}

	return $contents;
}, 10, 2 );