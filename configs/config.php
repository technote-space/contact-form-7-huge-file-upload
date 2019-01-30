<?php
/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.0.0
 * @since 1.2.5 Changed: master > develop (update_info_file_url)
 * @since 1.3.0 Changed: ライブラリの更新 (#12)
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

return [

	// main menu title
	'main_menu_title'                => 'Contact Form 7 huge file upload',

	// db version
	'db_version'                     => '0.0.1',

	// update
	'update_info_file_url'           => 'https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/develop/update.json',

	// suppress setting help contents
	'suppress_setting_help_contents' => true,
];
