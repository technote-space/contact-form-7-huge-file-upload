<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Classes\Models;

use Exception;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;
use WP_Framework_Presenter\Traits\Presenter;
use WPCF7_FormTag;

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

/**
 * Class File
 * @package Cf7_Hfu\Classes\Models
 */
class File implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Presenter\Interfaces\Presenter {

	use Singleton, Hook, Presenter, Package;

	/** @var Upload $upload */
	private $upload = null;

	/**
	 * @return Upload|Singleton
	 */
	private function get_upload() {
		if ( ! isset( $this->upload ) ) {
			$this->upload = Upload::get_instance( $this->app );
		}

		return $this->upload;
	}

	/**
	 * register file post type
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function register_file_post_type() {
		register_post_type( $this->get_file_post_type(), $this->get_file_post_type_args() );
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param string $key
	 */
	private function changed_option( $key ) {
		if ( in_array( $key, [
			$this->get_filter_prefix() . 'max_chunk_size',
			$this->get_filter_prefix() . 'max_filesize',
			$this->get_filter_prefix() . 'output_max_size_settings',
		], true ) ) {
			$this->delete_hook_cache( 'max_chunk_size' );
			$this->delete_hook_cache( 'max_filesize' );
			$this->delete_hook_cache( 'output_max_size_settings' );
			try {
				$this->recreate_htaccess();
			} catch ( Exception $e ) {
				$this->app->add_message( $e->getMessage(), 'option', true );
				$this->app->log( $e );
			}
		}
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param int $post_id
	 */
	private function delete_file( $post_id ) {
		$post = get_post( $post_id );
		if ( empty( $post ) || $post->post_type !== $this->get_file_post_type() ) {
			return;
		}

		foreach ( $this->get_file_ids( $post_id ) as $file_id ) {
			$this->detach_media( [
				'attach_id' => $file_id,
			] );
		}
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param bool $result
	 * @param int $post_id
	 *
	 * @return array|bool
	 */
	private function image_downsize( $result, $post_id ) {
		$access_key = $this->app->post->get( 'access_key', $post_id );
		if ( ! empty( $access_key ) ) {
			return [
				$this->get_access_url( $access_key ),
				0,
				0,
				false,
			];
		}

		return $result;
	}

	/**
	 * @return string
	 */
	public function get_file_post_type() {
		return $this->apply_filters( 'file_post_type', 'cf7_hfu' );
	}

	/**
	 * @param $capabilities
	 *
	 * @return array
	 */
	public function get_file_post_type_args( $capabilities = null ) {
		if ( ! isset( $capabilities ) ) {
			/** @var Capability $capability */
			$capability   = Capability::get_instance( $this->app );
			$capabilities = $capability->get_capabilities();
		}

		return $this->apply_filters( 'file_post_type_args', [
			'labels'              => $this->get_file_post_labels(),
			'description'         => '',
			'public'              => false,
			'show_ui'             => true,
			'has_archive'         => false,
			'show_in_menu'        => true,
			'exclude_from_search' => true,
			'capability_type'     => $this->get_file_post_capability_type(),
			'capabilities'        => $capabilities,
			'map_meta_cap'        => true,
			'hierarchical'        => false,
			'rewrite'             => [
				'slug'       => $this->get_file_post_slug(),
				'with_front' => false,
			],
			'query_var'           => true,
			'menu_icon'           => $this->get_file_post_menu_icon(),
			'supports'            => $this->get_file_post_supports(),
			'menu_position'       => $this->apply_filters( 'file_post_menu_position' ),
		], $capabilities );
	}

	/**
	 * @return array
	 */
	private function get_file_post_labels() {
		return $this->apply_filters( 'file_post_labels', [
			'name'          => $this->translate( 'Files' ),
			'singular_name' => $this->translate( 'File' ),
			'menu_name'     => $this->translate( 'Files' ),
			'all_items'     => $this->translate( 'All Files' ),
			'add_new'       => $this->translate( 'Upload Files' ),
			'edit_item'     => $this->translate( 'Edit File' ),
			'search_items'  => $this->translate( 'Search Files' ),
		] );
	}

	/**
	 * @return string|array
	 */
	private function get_file_post_capability_type() {
		return $this->apply_filters( 'capability_type', 'cf7_hfu' );
	}

	/**
	 * @return string
	 */
	private function get_file_post_slug() {
		return $this->apply_filters( 'file_post_slug', 'cf7_hfu' );
	}

	/**
	 * @return string
	 */
	private function get_file_post_menu_icon() {
		return $this->apply_filters( 'file_post_menu_icon', 'dashicons-admin-media' );
	}

	/**
	 * @return array
	 */
	private function get_file_post_supports() {
		return $this->apply_filters( 'file_post_supports', [
			'title',
		] );
	}

	/**
	 * @param array $params
	 * @param bool $validation
	 *
	 * @return array
	 * @throws Exception
	 */
	public function move_file( $params, $validation ) {
		$params['save_file_name'] = sha1_file( $params['tmp_file'] ) . '.' . $params['extension'];
		$params['save_file_name'] = wp_unique_filename( $params['upload_dir'], $params['save_file_name'] );
		$params['size']           = filesize( $params['tmp_file'] );
		$new_file                 = path_join( $params['upload_dir'], $params['save_file_name'] );
		$old_file                 = $params['tmp_file'];
		if ( ! $validation ) {
			$this->create_save_dir( $params['upload_dir'] );
			if ( false === $this->app->file->move( $old_file, $new_file, true ) ) {
				throw new Exception( 'Failed to move file.' );
			}
		}
		$params['new_file'] = $new_file;

		return $params;
	}

	/**
	 * @param string $upload_dir
	 *
	 * @throws Exception
	 */
	private function create_save_dir( $upload_dir ) {
		$this->create_dir( $upload_dir );
	}

	/**
	 * @param string $base_dir
	 * @param string $tmp_base_dir
	 *
	 * @throws Exception
	 */
	public function create_upload_dir( $base_dir, $tmp_base_dir ) {
		$this->create_dir( $base_dir );
		$this->create_dir( $tmp_base_dir );
		$this->create_htaccess( $base_dir, $this->get_htaccess_contents() );
		$this->create_htaccess( $tmp_base_dir, $this->get_upload_htaccess_contents() );
	}

	/**
	 * @param string $dir
	 *
	 * @throws Exception
	 */
	private function create_dir( $dir ) {
		if ( ! $this->app->file->exists( $dir ) ) {
			if ( false === $this->app->file->mkdir_recursive( $dir, 0700 ) ) {
				throw new Exception( 'Failed to make dir.' );
			}
		}
	}

	/**
	 * @param string $dir
	 *
	 * @return string
	 */
	private function get_htaccess_file_name( $dir ) {
		return $dir . DS . '.htaccess';
	}

	/**
	 * @param string $dir
	 * @param string $contents
	 *
	 * @throws Exception
	 */
	private function create_htaccess( $dir, $contents ) {
		$htaccess = $this->get_htaccess_file_name( $dir );
		if ( ! $this->app->file->exists( $htaccess ) ) {
			if ( false === $this->app->file->put_contents( $htaccess, $contents, 0644 ) ) {
				throw  new Exception( 'Failed to create .htaccess file.' );
			}
		}
	}

	/**
	 * @throws Exception
	 */
	private function recreate_htaccess() {
		$params   = $this->get_upload()->get_non_dynamic_upload_params();
		$htaccess = $this->get_htaccess_file_name( $params['tmp_base_dir'] );
		if ( $this->app->file->exists( $htaccess ) ) {
			$this->app->file->delete( $htaccess );
		}
		$this->create_htaccess( $params['tmp_base_dir'], $this->get_upload_htaccess_contents() );
	}

	/**
	 * @return string
	 */
	private function get_htaccess_contents() {
		$contents = <<< EOS
order deny,allow
deny from all
EOS;

		return $this->apply_filters( 'htaccess_content', $contents );
	}

	/**
	 * @return string
	 */
	private function get_upload_htaccess_contents() {
		$contents = <<< EOS
order deny,allow
deny from all
EOS;

		if ( $this->apply_filters( 'output_max_size_settings' ) ) {
			$max_chunk_size = $this->get_appropriate_size( $this->parse_filesize( $this->apply_filters( 'max_chunk_size' ), $this->get_default_max_chunk_size() ) + 100 * 1000 ); // form data を考慮して少し大きめ
			$max_filesize   = $this->get_appropriate_size( $this->parse_filesize( $this->apply_filters( 'max_filesize' ), $this->get_default_max_filesize() ) );

			$contents .= <<< EOS

php_value post_max_size {$max_chunk_size}
php_value upload_max_filesize {$max_filesize}
EOS;
		}

		return $this->apply_filters( 'htaccess_content', $contents );
	}

	/**
	 * @param string $size
	 *
	 * @return int|string
	 */
	private function get_appropriate_size( $size ) {
		return $this->get_appropriate_size_format_callback( $size, function ( $size, $norm, $unit ) {
			return ceil( $size / $norm ) . $unit;
		} );
	}

	/**
	 * @param string $size
	 * @param callable $callback
	 *
	 * @return int|string
	 */
	public function get_appropriate_size_format_callback( $size, $callback ) {
		$size = trim( $size );
		if ( '' === $size || ! ctype_digit( $size ) ) {
			return $size;
		}
		$size -= 0;

		$size_kb = 1024;
		$size_mb = $size_kb * $size_kb;
		$size_gb = $size_mb * $size_kb;
		switch ( true ) {
			case $size >= $size_gb:
				return $callback( $size, $size_gb, 'G' );
			case $size >= $size_mb:
				return $callback( $size, $size_mb, 'M' );
			case $size >= $size_kb:
				return $callback( $size, $size_kb, 'K' );
			default:
				return $callback( $size, 1, '' );
		}
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 * @throws Exception
	 */
	public function attach_media( $params ) {
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$file_type = wp_check_filetype( $params['new_file'] );
		$ext       = $file_type['ext'];
		$type      = $file_type['type'];
		if ( empty( $ext ) || empty( $type ) ) {
			throw new Exception( 'Not allowed file type.' );
		}
		$access_key = $this->generate_file_access_key( $params );
		$access_url = $this->get_access_url( $access_key );
		$attach_id  = wp_insert_attachment( [
			'guid'           => $access_url,
			'post_mime_type' => $type,
			'post_title'     => $params['file_name'],
			'post_content'   => $this->apply_filters( 'upload_file_post_content', 'contact_form_7 uploaded' ),
			'post_status'    => 'inherit',
		] );
		if ( is_wp_error( $attach_id ) ) {
			throw new Exception( $attach_id->get_error_message() );
		}
		$attach_data = wp_generate_attachment_metadata( $attach_id, $params['new_file'] );
		if ( ! empty( $attach_data ) && false === wp_update_attachment_metadata( $attach_id, $attach_data ) ) {
			throw new Exception( 'Failed to update attachment metadata.' );
		}
		if ( false === update_attached_file( $attach_id, $params['new_file'] ) ) {
			throw new Exception( 'Failed to update attached file.' );
		}
		$params['attach_id']  = $attach_id;
		$params['access_url'] = $access_url;
		$this->app->post->set( $attach_id, 'access_key', $access_key );
		$this->app->post->set( $attach_id, 'extension', $params['extension'] );
		$this->app->post->set( $attach_id, 'size', $params['size'] );

		return $params;
	}

	/**
	 * @param string $access_key
	 *
	 * @return string
	 */
	public function get_access_url( $access_key ) {
		return admin_url( 'admin-ajax.php' ) . '?action=cf7hfu_file_download&access_key=' . $access_key;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 * @throws Exception
	 */
	public function insert_file_post( $params ) {
		// 同じプロセスでアップロードしたファイルは同一の投稿に追加
		$post_id = $this->app->post->first( 'process', $params['process'] );
		if ( false === $post_id ) {
			$post_id = wp_insert_post( $this->apply_filters( 'insert_file_post_args', [
				'post_type'   => $this->get_file_post_type(),
				'post_title'  => $this->get_file_post_title(),
				'post_status' => 'publish',
			] ), true );
			if ( is_wp_error( $post_id ) ) {
				throw new Exception( $post_id->get_error_message() );
			}
			$this->app->post->set( $post_id, 'process', $params['process'] );
		} else {
			$post = get_post( $post_id );
			if ( empty( $post ) ) {
				throw new Exception( 'Unexpected error has occurred.' );
			}
		}
		$this->app->post->set( $post_id, 'file_id', $params['attach_id'], true );
		$params['post_id'] = $post_id;

		return $params;
	}

	/**
	 * @param array $params
	 */
	public function detach_media( $params ) {
		wp_delete_attachment( $params['attach_id'], true );
	}

	/**
	 * @param $post_id
	 *
	 * @return array
	 */
	public function get_file_ids( $post_id ) {
		return $this->app->post->get( 'file_id', $post_id, false, [] );
	}

	/**
	 * @return string
	 */
	private function get_file_post_title() {
		return $this->app->string->replace_time( $this->apply_filters( 'file_post_title' ) );
	}

	/**
	 * @param array $params
	 *
	 * @return string
	 */
	private function generate_file_access_key( $params ) {
		return $this->apply_filters( 'generate_file_access_key', md5( uniqid() ), $params );
	}

	/**
	 * @param string $directory_path
	 * @param bool|int $threshold
	 *
	 * @return bool
	 */
	public function remove_dir( $directory_path, $threshold = false ) {
		if ( ! $this->app->file->is_dir( $directory_path ) ) {
			return false;
		}
		foreach ( $this->scan_dir( $directory_path ) as $path ) {
			if ( $this->app->file->is_file( $path ) ) {
				if ( preg_match( '#\.htaccess$#', $path ) ) {
					continue;
				}
				if ( false !== $threshold ) {
					if ( $this->app->file->mtime( $path ) >= $threshold ) {
						continue;
					}
				}
				$this->app->file->delete( $path );
			} elseif ( $this->app->file->is_dir( $path ) ) {
				$this->remove_dir( $path, $threshold );
			}
		}

		return $this->app->file->rmdir( $directory_path );
	}

	/**
	 * @param string $dir
	 *
	 * @return array
	 */
	private function scan_dir( $dir ) {
		return glob( $dir . '/{*,.[!.]*,..?*}', GLOB_BRACE );
	}

	/**
	 *
	 * @param WPCF7_FormTag $tag
	 *
	 * @return int
	 */
	public function get_size_limit( $tag ) {
		$allowed_size = 0;
		$file_size_a  = $tag->get_option( 'limit' );
		if ( $file_size_a ) {
			foreach ( $file_size_a as $file_size ) {
				$file_size = $this->parse_filesize( $file_size );
				if ( isset( $file_size ) ) {
					$allowed_size = $file_size;
					break;
				}
			}
		}

		$default = $this->parse_filesize( $this->apply_filters( 'max_filesize' ), $this->get_default_max_filesize() );
		if ( $allowed_size > 0 ) {
			$allowed_size = min( $allowed_size, $default );
		} else {
			$allowed_size = $default;
		}

		return $allowed_size;
	}

	/**
	 *
	 * @param string $size
	 * @param int|null $default
	 *
	 * @return int|null
	 */
	public function parse_filesize( $size, $default = null ) {
		$limit_pattern = '/^([1-9][0-9]*)([kKmM][bB]?)?$/';
		$result        = $default;
		if ( preg_match( $limit_pattern, $size, $matches ) ) {
			$result = (int) $matches[1];
			if ( ! empty( $matches[2] ) ) {
				$kbmb = strtolower( $matches[2] );
				if ( in_array( $kbmb, [ 'kb', 'k' ], true ) ) {
					$result *= 1024;
				} elseif ( in_array( $kbmb, [ 'mb', 'm' ], true ) ) {
					$result *= 1024 * 1024;
				}
			}
		}

		return $result;
	}

	/**
	 * @return int
	 */
	public function get_default_max_filesize() {
		return $this->apply_filters( 'default_max_filesize', 1 * 1024 * 1024 );
	}

	/**
	 * @return int
	 */
	public function get_default_max_chunk_size() {
		return $this->apply_filters( 'default_max_chunk_size', 100 * 1024 );
	}
}
