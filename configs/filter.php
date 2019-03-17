<?php
/**
 * @version 1.3.5
 * @author Technote
 * @since 1.0.0.0
 * @since 1.1.8
 * @since 1.3.0 trivial change
 * @since 1.3.5 trivial change
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

return [

	'\Cf7_Hfu\Classes\Models\Upload' => [
		'template_redirect'                     => [
			'setup_assets',
		],
		'wp_ajax_cf7_hfu_do_file_upload'        => [
			'upload_process',
		],
		'wp_ajax_nopriv_cf7_hfu_do_file_upload' => [
			'upload_process',
		],
		'wp_ajax_cf7_hfu_cancel_upload'         => [
			'cancel_upload',
		],
		'wp_ajax_nopriv_cf7_hfu_cancel_upload'  => [
			'cancel_upload',
		],
	],

	'\Cf7_Hfu\Classes\Models\Contact' => [
		'wpcf7_validate_file'    => [
			'wpcf7_file_validation_filter' => 9,
		],
		'wpcf7_validate_file*'   => [
			'wpcf7_file_validation_filter' => 9,
		],
		'wpcf7_before_send_mail' => [
			'wpcf7_before_send_mail' => 9,
		],
	],

	'\Cf7_Hfu\Classes\Models\File' => [
		'${prefix}changed_option'             => [
			'changed_option',
		],
		'before_delete_post'                  => [
			'delete_file',
		],
		'init'                                => [
			'register_file_post_type' => 9,
		],
		'wp_ajax_cf7hfu_file_download'        => [
			'file_download',
		],
		'wp_ajax_nopriv_cf7hfu_file_download' => [
			'file_download',
		],
		'image_downsize'                      => [
			'image_downsize',
		],
		'admin_head-post.php'                 => [
			'check_edit_post_file',
		],
		'wp_image_editors'                    => [
			'wp_image_editors',
		],
		'edit_form_after_title'               => [
			'edit_form_after_title',
		],
		'admin_menu'                          => [
			'setup_download_page',
		],
		'manage_posts_columns'                => [
			'delete_check_box',
		],
		'post_row_actions'                    => [
			'delete_edit_links',
		],
		'admin_init'                          => [
			'redirect_to_download_page',
		],
	],

	'\Cf7_Hfu\Classes\Models\Capability' => [
		'${prefix}app_activated'   => [
			'set_capability',
		],
		'${prefix}app_deactivated' => [
			'unset_capability',
		],
		'${prefix}changed_option'  => [
			'reset_capability',
		],
		'custom_menu_order'        => [
			'filter_wp_menu_nopriv',
		],
	],
];