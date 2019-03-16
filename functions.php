<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.3
 * @since 1.3.0 Changed: ライブラリの更新 (#12)
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
