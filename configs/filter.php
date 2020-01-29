<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

return [
	'\Cf7_Hfu\Classes\Models\Upload'     => [
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
		'${prefix}post_load_admin_page'         => [
			'remove_setting',
		],
	],
	'\Cf7_Hfu\Classes\Models\Contact'    => [
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
	'\Cf7_Hfu\Classes\Models\File'       => [
		'${prefix}changed_options' => [
			'changed_options',
		],
		'before_delete_post'      => [
			'delete_file',
		],
		'init'                    => [
			'register_file_post_type' => 9,
		],
		'image_downsize'          => [
			'image_downsize',
		],
	],
	'\Cf7_Hfu\Classes\Models\Capability' => [
		'${prefix}app_activated'   => [
			'set_capability',
		],
		'${prefix}app_deactivated' => [
			'unset_capability',
		],
		'${prefix}changed_options'  => [
			'reset_capability',
		],
		'custom_menu_order'        => [
			'filter_wp_menu_nopriv',
		],
	],
	'\Cf7_Hfu\Classes\Models\Download'   => [
		'wp_ajax_cf7hfu_file_download'        => [
			'file_download',
		],
		'wp_ajax_nopriv_cf7hfu_file_download' => [
			'file_download',
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
		'admin_head-post.php'                 => [
			'check_edit_post_file',
		],
		'wp_image_editors'                    => [
			'wp_image_editors',
		],
		'edit_form_after_title'               => [
			'edit_form_after_title',
		],
	],
];
