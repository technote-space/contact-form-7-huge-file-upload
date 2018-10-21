<?php
/**
 * @version 1.0.0.3
 * @author technote-space
 * @since 1.0.0.1
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Models;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class File
 * @package Cf7_Hfu\Models
 */
class File implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook, \Technote\Interfaces\Presenter, \Technote\Interfaces\Uninstall {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Presenter, \Technote\Traits\Uninstall;

	/** @var Upload $_upload */
	private $_upload = null;

	/** @var bool $_is_edit_post_file */
	private $_is_edit_post_file = false;

	/**
	 * @return Upload|\Technote\Traits\Singleton
	 */
	private function get_upload() {
		if ( ! isset( $this->_upload ) ) {
			$this->_upload = Upload::get_instance( $this->app );
		}

		return $this->_upload;
	}

	/**
	 * register file post type
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function register_file_post_type() {
		register_post_type( $this->get_file_post_type(), $this->get_file_post_type_args() );
	}

	/**
	 * @param string $key
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function changed_option( $key ) {
		if ( in_array( $key, [
			$this->get_filter_prefix() . 'max_chunk_size',
			$this->get_filter_prefix() . 'max_filesize',
			$this->get_filter_prefix() . 'output_max_size_settings',
		] ) ) {
			try {
				$this->recreate_htaccess();
			} catch ( \Exception $e ) {
				$this->app->add_message( $e->getMessage(), 'option', true );
				$this->app->log( $e->getMessage() );
			}
		}
	}

	/**
	 * @param int $post_id
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
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
	 * file download
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function file_download() {

		list( $file, $name ) = $this->get_download_file_info();

		header( 'Content-Type: application/force-download' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode( $name ) );
		if ( $fp = fopen( $file, 'rb' ) ) {
			while ( ! feof( $fp ) and ( connection_status() == 0 ) ) {
				echo fread( $fp, 1024 * 4 );
				ob_flush();
			}
			ob_end_flush();
			fclose( $fp );
		}
	}

	/**
	 * @param bool $result
	 * @param int $id
	 *
	 * @return array|bool
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function image_downsize( $result, $id ) {
		$access_key = $this->app->post->get( 'access_key', $id );
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
	 * check edit post file
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function check_edit_post_file() {
		global $post;
		if ( empty( $post ) ) {
			return;
		}

		$access_key = $this->app->post->get( 'access_key', $post->ID );
		if ( ! empty( $access_key ) ) {
			$this->_is_edit_post_file = true;
		}
	}

	/**
	 * @param array $image_editors
	 *
	 * @return array
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function wp_image_editors( $image_editors ) {
		if ( $this->_is_edit_post_file ) {
			return [];
		}

		return $image_editors;
	}

	/**
	 * @param \WP_Post $post
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function edit_form_after_title( $post ) {
		if ( $post->post_type !== $this->get_file_post_type() ) {
			return;
		}
		$list = array_map( function ( $file_id ) {
			$post         = get_post( $file_id );
			$access_key   = empty( $post ) ? false : $this->app->post->get( 'access_key', $file_id );
			$can_download = empty( $access_key ) ? false : $this->check_can_download( $file_id );
			$url          = empty( $access_key ) ? false : $this->get_access_url( $access_key );
			$edit_link    = empty( $access_key ) ? false : get_edit_post_link( $file_id, 'link' );
			$size         = empty( $access_key ) ? false : $this->get_appropriate_size_format_callback( $this->app->post->get( 'size', $file_id ), function ( $size, $norm, $unit ) {
				return round( $size / $norm, 2 ) . $unit . 'B';
			} );
			$name         = empty( $post ) ? '' : $post->post_title;

			return [
				'can_download' => $can_download,
				'url'          => $url,
				'edit_link'    => $edit_link,
				'name'         => $name,
				'size'         => $size,
			];
		}, $this->get_file_ids( $post->ID ) );
		$this->add_script_view( 'admin/script/file_list' );
		$this->get_view( 'admin/file_list', [
			'list' => $list,
		], true );
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
			'rewrite'             => [ 'slug' => $this->get_file_post_slug(), 'with_front' => false ],
			'query_var'           => true,
			'menu_icon'           => $this->get_file_post_menu_icon(),
			'supports'            => $this->get_file_post_supports(),
			'menu_position'       => $this->apply_filters( 'file_post_menu_position' ),
		] );
	}

	/**
	 * @return array
	 */
	private function get_file_post_labels() {
		return $this->apply_filters( 'file_post_labels', [
			'name'          => $this->app->translate( 'Files' ),
			'singular_name' => $this->app->translate( 'File' ),
			'menu_name'     => $this->app->translate( 'Files' ),
			'all_items'     => $this->app->translate( 'All Files' ),
			'add_new'       => $this->app->translate( 'Upload Files' ),
			'edit_item'     => $this->app->translate( 'Edit File' ),
			'search_items'  => $this->app->translate( 'Search Files' ),
		] );
	}

	/**
	 * @return string|array
	 */
	public function get_file_post_capability_type() {
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
	 * @throws \Exception
	 */
	public function move_file( $params, $validation ) {
		$params['save_file_name'] = sha1_file( $params['tmp_file'] ) . '.' . $params['extension'];
		$params['save_file_name'] = wp_unique_filename( $params['upload_dir'], $params['save_file_name'] );
		$params['size']           = filesize( $params['tmp_file'] );
		$new_file                 = path_join( $params['upload_dir'], $params['save_file_name'] );
		$old_file                 = $params['tmp_file'];
		if ( ! $validation ) {
			$this->create_save_dir( $params['upload_dir'] );
			if ( false === @rename( $old_file, $new_file ) ) {
				throw new \Exception( 'Failed to move file.' );
			}
		}
		$params['new_file'] = $new_file;

		return $params;
	}

	/**
	 * @param string $upload_dir
	 *
	 * @throws \Exception
	 */
	private function create_save_dir( $upload_dir ) {
		$this->create_dir( $upload_dir );
	}

	/**
	 * @param string $base_dir
	 * @param string $tmp_base_dir
	 *
	 * @throws \Exception
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
	 * @throws \Exception
	 */
	private function create_dir( $dir ) {
		if ( ! file_exists( $dir ) ) {
			if ( false === mkdir( $dir, 0700, true ) ) {
				throw new \Exception( 'Failed to make dir.' );
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
	 * @throws \Exception
	 */
	private function create_htaccess( $dir, $contents ) {
		$htaccess = $this->get_htaccess_file_name( $dir );
		if ( ! file_exists( $htaccess ) ) {
			if ( false === @file_put_contents( $htaccess, $contents, 0644 ) ) {
				throw  new \Exception( 'Failed to create .htaccess file.' );
			}
		}
	}

	/**
	 * @throws \Exception
	 */
	private function recreate_htaccess() {
		$params   = $this->get_upload()->get_non_dynamic_upload_params();
		$htaccess = $this->get_htaccess_file_name( $params['tmp_base_dir'] );
		if ( file_exists( $htaccess ) ) {
			unlink( $htaccess );
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
			$max_chunk_size = $this->get_appropriate_size( $this->apply_filters( 'max_chunk_size' ) + 100 * 1000 ); // form data を考慮して少し大きめ
			$max_filesize   = $this->get_appropriate_size( $this->apply_filters( 'max_filesize' ) );
			$contents       .= <<< EOS

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
		$kb   = 1024;
		$mb   = $kb * $kb;
		$gb   = $mb * $kb;
		switch ( true ) {
			case $size >= $gb:
				return $callback( $size, $gb, 'G' );
			case $size >= $mb:
				return $callback( $size, $mb, 'M' );
			case $size >= $kb:
				return $callback( $size, $kb, 'K' );
			default:
				return $callback( $size, 1, '' );
		}
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function attach_media( $params ) {
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		$file_type = wp_check_filetype( $params['new_file'] );
		$ext       = $file_type['ext'];
		$type      = $file_type['type'];
		if ( empty( $ext ) || empty( $type ) ) {
			throw new \Exception( 'Not allowed file type.' );
		}
		$access_key = $this->generate_file_access_key();
		$access_url = $this->get_access_url( $access_key );
		$attach_id  = wp_insert_attachment( [
			'guid'           => $access_url,
			'post_mime_type' => $type,
			'post_title'     => $params['file_name'],
			'post_content'   => $this->apply_filters( 'upload_file_post_content', 'contact_form_7 uploaded' ),
			'post_status'    => 'inherit',
		] );
		if ( is_wp_error( $attach_id ) ) {
			throw new \Exception( $attach_id->get_error_message() );
		}
		$attach_data = wp_generate_attachment_metadata( $attach_id, $params['new_file'] );
		if ( false === wp_update_attachment_metadata( $attach_id, $attach_data ) ) {
			throw new \Exception( 'Failed to update attachment metadata.' );
		}
		if ( false === update_attached_file( $attach_id, $params['new_file'] ) ) {
			throw new \Exception( 'Failed to update attached file.' );
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
	private function get_access_url( $access_key ) {
		return admin_url( 'admin-ajax.php' ) . '?action=file_download&access_key=' . $access_key;
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 * @throws \Exception
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
				throw new \Exception( $post_id->get_error_message() );
			}
			$this->app->post->set( $post_id, 'process', $params['process'] );
		} else {
			$post = get_post( $post_id );
			if ( empty( $post ) ) {
				throw new \Exception( 'Unexpected error has occurred.' );
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
		return \Technote\Models\Utility::replace_time( $this->apply_filters( 'file_post_title' ) );
	}

	/**
	 * @return string
	 */
	private function generate_file_access_key() {
		return $this->apply_filters( 'generate_file_access_key', md5( uniqid() ) );
	}

	/**
	 * @param $access_key
	 *
	 * @return false|int
	 */
	private function get_attached_file_post( $access_key ) {
		return $this->app->post->first( 'access_key', $access_key );
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function check_can_download( $post_id ) {
		return $this->apply_filters( 'can_download', true, $post_id, $this->app->user->user_id );
	}

	/**
	 * @return array
	 */
	private function get_download_file_info() {
		$access_key = $this->app->input->get( 'access_key' );
		if ( empty( $access_key ) ) {
			return $this->get_no_image_file_info();
		}

		$post_id = $this->get_attached_file_post( $access_key );
		if ( empty( $post_id ) ) {
			return $this->get_no_image_file_info();
		}

		if ( ! $this->check_can_download( $post_id ) ) {
			return $this->get_no_image_file_info();
		}

		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			return $this->get_no_image_file_info();
		}

		$file = get_attached_file( $post_id );
		if ( empty( $file ) ) {
			return $this->get_no_image_file_info();
		}

		$name      = $post->post_title;
		$extension = $this->app->post->get( 'extension', $post_id );

		return [
			$file,
			rawurlencode( $name . '.' . $extension ),
		];
	}

	/**
	 * @return array
	 */
	private function get_no_image_file_info() {
		return [
			$this->apply_filters( 'no_image_file', $this->app->define->lib_assets_dir . DS . 'img' . DS . 'no_img.png' ),
			'no_img.png',
		];
	}

	/**
	 * @param string $directory_path
	 * @param bool|int $threshold
	 *
	 * @return bool
	 */
	public function remove_dir( $directory_path, $threshold = false ) {
		if ( ! is_dir( $directory_path ) ) {
			return false;
		}
		foreach ( glob( $directory_path . '/{*,.[!.]*,..?*}', GLOB_BRACE ) as $path ) {
			if ( is_file( $path ) ) {
				if ( preg_match( '#\.htaccess$#', $path ) ) {
					continue;
				}
				if ( false !== $threshold ) {
					if ( filemtime( $path ) >= $threshold ) {
						continue;
					}
				}
				if ( ! unlink( $path ) ) {
					continue;
				}
			} elseif ( is_dir( $path ) ) {
				if ( $this->remove_dir( $path, $threshold ) === false ) {
					continue;
				}
			}
		}

		return @rmdir( $directory_path );
	}

	/**
	 * @param $directory_path
	 *
	 * @return false|array
	 */
	public function find_file( $directory_path ) {
		foreach ( glob( $directory_path . '/*' ) as $path ) {
			if ( is_file( $path ) ) {
				return [
					'dir'  => $directory_path,
					'file' => ltrim( str_replace( $directory_path, '', $path ), DS ),
					'path' => $path,
				];
			}
		}

		return false;
	}

	/**
	 * uninstall
	 */
	public function uninstall() {

	}
}
