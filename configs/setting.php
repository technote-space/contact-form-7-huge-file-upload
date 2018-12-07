<?php
/**
 * @version 1.0.0.5
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

	9 => [
		'Upload'           => [
			10 => [
				'output_max_size_settings' => [
					'label'   => 'Whether to output upload file size settings to .htaccess file.',
					'type'    => 'bool',
					'default' => true,
				],
				'huge_file_class'          => [
					'label'   => 'Class name to determine whether it is target or not.',
					'default' => 'fileupload',
				],
				'max_chunk_size'           => [
					'label'   => 'Max size of post data.',
					'default' => '4M',
				],
				'max_filesize'             => [
					'label'   => 'Max size of upload file.',
					'default' => '300M',
				],
			],
		],
		'Custom Post Type' => [
			10 => [
				'file_post_title'         => [
					'label'   => 'Post title',
					'default' => 'Uploaded at ${Y}/${m}/${d} ${H}:${i}:${s}',
				],
				'file_post_menu_position' => [
					'label'   => 'Post menu position',
					'type'    => 'int',
					'default' => 5,
					'min'     => 0,
				],
			],
		],
		'File'             => [
			10 => [
				'is_valid_auto_delete'          => [
					'label'   => 'Whether to delete old tmp upload file.',
					'type'    => 'bool',
					'default' => true,
				],
				'delete_file_interval'          => [
					'label'   => 'Interval to run process to delete old tmp upload file.',
					'type'    => 'int',
					'default' => 24 * 60 * 60,
					'min'     => 60,
				],
				'delete_file_threshold'         => [
					'label'   => 'Threshold to determine the file is old or not.',
					'type'    => 'int',
					'default' => 24 * 60 * 60,
					'min'     => 60,
				],
				'must_be_logged_in_to_download' => [
					'label'   => 'User has to be logged in to download file.',
					'type'    => 'bool',
					'default' => true,
				],
				'downloadable_roles'            => [
					'label'   => 'Logged in user\'s role to download file (comma separated).',
					'default' => 'administrator,editor,author,contributor,subscriber',
				],
				'editable_roles'                => [
					'label'   => 'Logged in user\'s role to edit file (comma separated).',
					'default' => 'administrator',
				],
			],
		],
	],

];