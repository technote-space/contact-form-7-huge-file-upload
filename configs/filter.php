<?php
/**
 * @version 1.0.0.1
 * @author technote-space
 * @since 1.0.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

return [

	'\Cf7_Hfu\Models\Upload' => [
		'template_redirect'                     => [
			'setup_assets' => [],
		],
		'wp_ajax_cf7_hfu_do_file_upload'        => [
			'upload_process' => [],
		],
		'wp_ajax_nopriv_cf7_hfu_do_file_upload' => [
			'upload_process' => [],
		],
		'wp_ajax_cf7_hfu_cancel_upload'         => [
			'cancel_upload' => [],
		],
		'wp_ajax_nopriv_cf7_hfu_cancel_upload'  => [
			'cancel_upload' => [],
		],
	],

	'\Cf7_Hfu\Models\Contact' => [
		'wpcf7_validate_file'    => [
			'wpcf7_file_validation_filter' => [ 9 ],
		],
		'wpcf7_validate_file*'   => [
			'wpcf7_file_validation_filter' => [ 9 ],
		],
		'wpcf7_before_send_mail' => [
			'wpcf7_before_send_mail' => [ 9 ],
		],
	],

	'\Cf7_Hfu\Models\File' => [
		'${prefix}changed_option'      => [
			'changed_option' => [],
		],
		'before_delete_post'           => [
			'delete_file' => [],
		],
		'init'                         => [
			'register_file_post_type' => [ 9 ],
		],
		'wp_ajax_file_download'        => [
			'file_download' => [],
		],
		'wp_ajax_nopriv_file_download' => [
			'file_download' => [],
		],
		'image_downsize'               => [
			'image_downsize' => [],
		],
		'admin_head-post.php'          => [
			'check_edit_post_file' => [],
		],
		'wp_image_editors'             => [
			'wp_image_editors' => [],
		],
	],

	'\Cf7_Hfu\Models\Capability' => [
		'${prefix}app_activated'   => [
			'set_capability' => [],
		],
		'${prefix}app_deactivated' => [
			'unset_capability' => [],
		],
	],
];