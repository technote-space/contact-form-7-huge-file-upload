<?php
/**
 * @version 1.1.8
 * @author technote-space
 * @since 1.0.0.1
 * @since 1.1.8
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Classes\Models;

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

/**
 * Class Upload
 * @package Cf7_Hfu\Classes\Models
 */
class Upload implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Presenter\Interfaces\Presenter, \WP_Framework_Core\Interfaces\Nonce, \WP_Framework_Common\Interfaces\Uninstall {

	use \WP_Framework_Core\Traits\Singleton, \WP_Framework_Core\Traits\Hook, \WP_Framework_Presenter\Traits\Presenter, \WP_Framework_Core\Traits\Nonce, \WP_Framework_Common\Traits\Uninstall, \WP_Framework_Common\Traits\Package;

	/** @var File $_file */
	private $_file = null;

	/**
	 * @return File|\WP_Framework_Core\Traits\Singleton
	 */
	private function get_file() {
		if ( ! isset( $this->_file ) ) {
			$this->_file = File::get_instance( $this->app );
		}

		return $this->_file;
	}

	/**
	 * @return string
	 */
	public function get_nonce_slug() {
		return 'upload';
	}

	/**
	 * upload process
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function upload_process() {
		if ( ! $this->nonce_check() ) {
			header( 'HTTP/1.1 403 Forbidden' );
			exit;
		}
		$process  = $this->get_process();
		$random   = $this->get_random();
		$wpcf7_id = $this->get_wpcf7_id();
		if ( empty( $process ) || empty( $random ) || empty( $_FILES ) || empty( $wpcf7_id ) ) {
			header( 'HTTP/1.1 400 Bad Request' );
			exit;
		}

		$params = $this->get_upload_params( $process, $wpcf7_id, $random );
		if ( empty( $params ) ) {
			wp_send_json( [
				'message' => $this->translate( 'The requested contact form was not found.' ),
			], 404 );
			exit;
		}
		try {
			$this->get_file()->create_upload_dir( $params['base_dir'], $params['tmp_base_dir'] );
		} catch ( \Exception $e ) {
			wp_send_json( [
				'message' => $this->translate( $e->getMessage() ),
			], 500 );
			exit;
		}

		$param_name                             = $params['param_name'];
		$response                               = ( new \UploadHandler( $this->get_upload_handler_params( $params ) ) )->get_response();
		$response[ $param_name ][0]->process    = $process;
		$response[ $param_name ][0]->random     = $random;
		$response[ $param_name ][0]->param_name = $param_name;
		wp_send_json( $response );
	}

	/**
	 * cancel upload
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function cancel_upload() {
		if ( ! $this->nonce_check() ) {
			header( 'HTTP/1.1 403 Forbidden' );
			exit;
		}
		$process    = $this->get_process();
		$random     = $this->get_random();
		$wpcf7_id   = $this->get_wpcf7_id();
		$param_name = $this->app->input->post( 'param_name' );
		if ( empty( $process ) || empty( $random ) || empty( $param_name ) || empty( $wpcf7_id ) ) {
			header( 'HTTP/1.1 400 Bad Request' );
			exit;
		}
		$params = $this->get_upload_params( $process, $wpcf7_id, $random, $param_name );
		if ( empty( $params ) ) {
			return;
		}
		$this->get_file()->remove_dir( $params['tmp_upload_dir'] );
		wp_send_json_success();
	}

	/**
	 * setup assets
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function setup_assets() {
		if ( ! $this->app->post->has_shortcode( [ 'contact-form-7', 'contact-form' ] ) ) {
			return;
		}

		$assets_dir = $this->app->define->plugin_url . '/vendor/blueimp/jquery-file-upload/js/';
		wp_enqueue_script( 'cf7_hfu-fileupload-widget',
			$assets_dir . 'vendor/jquery.ui.widget.js', [
				'jquery',
			], false, true );
		wp_enqueue_script( 'cf7_hfu-fileupload-iframe',
			$assets_dir . 'jquery.iframe-transport.js', [
				'jquery',
			], false, true );
		wp_enqueue_script( 'cf7_hfu-fileupload',
			$assets_dir . 'jquery.fileupload.js', [
				'jquery',
				'cf7_hfu-fileupload-widget',
				'cf7_hfu-fileupload-iframe',
			], false, true );
		wp_enqueue_script( "cf7_hfu-upload-script", $this->app->define->plugin_assets_url . '/js/upload.js', [
			"jquery",
			"cf7_hfu-fileupload",
		] );
		wp_localize_script( 'cf7_hfu-upload-script', 'cf7_hfu', [
			'ajax_url'        => admin_url( 'admin-ajax.php' ),
			'process_key'     => $this->get_process_key(),
			'random_key'      => $this->get_random_key(),
			'random_key_slug' => $this->get_uploaded_file_random_key_slug(),
			'huge_file_class' => $this->get_huge_file_class(),
			'max_chunk_size'  => $this->get_max_chunk_size(),
			'nonce_key'       => $this->get_nonce_key(),
			'nonce_value'     => $this->create_nonce(),
		] );
		wp_enqueue_style( 'cf7_hfu-upload-style', $this->app->define->plugin_assets_url . '/css/upload.css' );

		global $wp_scripts;
		$ui = $wp_scripts->query( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_style( 'jquery-ui-progressbar', "//ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css" );
	}

	/**
	 * @return string
	 */
	private function get_process_key() {
		return $this->apply_filters( 'process_key', $this->app->plugin_name . '-process' );
	}

	/**
	 * @return string
	 */
	private function get_random_key() {
		return $this->apply_filters( 'random_key', $this->app->plugin_name . '-rand' );
	}

	/**
	 * @return string
	 */
	private function get_huge_file_class() {
		return $this->apply_filters( 'huge_file_class' );
	}

	/**
	 * @return int
	 */
	private function get_max_chunk_size() {
		return $this->get_file()->parse_filesize( $this->apply_filters( 'max_chunk_size' ), $this->get_file()->get_default_max_chunk_size() );
	}

	/**
	 * @return string
	 */
	private function get_process() {
		return $this->app->input->post( $this->get_process_key() );
	}

	/**
	 * @return string
	 */
	private function get_random() {
		return $this->app->input->post( $this->get_random_key() );
	}

	/**
	 * @return string
	 */
	private function get_wpcf7_id() {
		return $this->app->input->post( '_wpcf7' );
	}

	/**
	 * @return string
	 */
	private function get_uploaded_file_random_key_slug() {
		return $this->apply_filters( 'uploaded_flag_slug', '_cf7_hfu_uploaded' );
	}

	/**
	 * @param string $name
	 *
	 * @return string
	 */
	private function get_uploaded_file_random_key_name( $name ) {
		return $name . $this->get_uploaded_file_random_key_slug();
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get_uploaded_file_random_key( $name ) {
		return $this->app->input->post( $this->get_uploaded_file_random_key_name( $name ) );
	}

	/**
	 * @param $name
	 *
	 * @return mixed
	 */
	public function get_uploaded_file_process( $name ) {
		return $this->app->input->post( $name );
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function is_valid_upload( $name ) {
		return ! empty( $this->get_uploaded_file_process( $name ) ) && ! empty( $this->get_uploaded_file_random_key( $name ) );
	}

	/**
	 * @param string $process
	 * @param string $wpcf7_id
	 * @param string $random
	 * @param string $param_name
	 *
	 * @return array|false
	 */
	public function get_upload_params( $process, $wpcf7_id, $random = '', $param_name = null ) {
		$params          = $this->get_non_dynamic_upload_params();
		$param_name      = ! isset( $param_name ) ? array_keys( $_FILES )[0] : $param_name;
		$tmp_upload_path = md5( $process . $random . $param_name );
		$tmp_base_dir    = "{$params['base_dir']}/tmp";
		$tmp_upload_dir  = "{$tmp_base_dir}/{$tmp_upload_path}";
		/* contact form から submit時 ($wpcf7_id = null) の file type のチェックは Contact::check_file_type_pattern で行うためここではチェックさせない */
		$accept_file_types = '/.+/';
		$max_file_size     = $this->get_file()->get_default_max_filesize();
		if ( ! empty( $wpcf7_id ) ) {
			$item = wpcf7_contact_form( $wpcf7_id );
			if ( ! $item ) {
				return false;
			}
			$tags               = $item->scan_form_tags();
			$file_type_patterns = [];
			foreach ( (array) $tags as $tag ) {
				/** @var \WPCF7_FormTag $tag */
				if ( empty( $tag->name ) || $tag->name != $param_name ) {
					continue;
				}
				$classes = explode( ' ', $tag->get_class_option() );
				if ( ! in_array( $this->get_huge_file_class(), $classes ) ) {
					continue;
				}

				$file_type_pattern    = wpcf7_acceptable_filetypes( $tag->get_option( 'filetypes' ), 'regex' );
				$file_type_patterns[] = empty( $file_type_pattern ) ? 'gif|jpe?g|png' : $file_type_pattern;
				$max_file_size        = $this->get_file()->get_size_limit( $tag );

				break;
			}
			if ( ! empty( $file_type_patterns ) ) {
				$file_type_patterns = array_unique( $file_type_patterns );
				$accept_file_types  = '/\.(' . implode( ')|(', $file_type_patterns ) . ')$/i';
			}
		}

		return $this->apply_filters( 'upload_params', array_merge( $params, [
			'tmp_upload_dir'    => $tmp_upload_dir,
			'param_name'        => $param_name,
			'process'           => $process,
			'random'            => $random,
			'accept_file_types' => $accept_file_types,
			'max_file_size'     => $max_file_size,
		] ), $tmp_upload_dir, $param_name, $process, $random );
	}

	/**
	 * @return array
	 */
	public function get_non_dynamic_upload_params() {
		$wp_upload_dir = wp_upload_dir();
		$url           = content_url() . '/uploads';
		$Y             = date( 'Y' );
		$m             = date( 'm' );
		$path          = $this->apply_filters( 'upload_path', 'cf7_hfu' );
		$base_dir      = "{$wp_upload_dir['basedir']}/{$path}";
		$tmp_base_dir  = "{$base_dir}/tmp";
		$dir           = "{$Y}/{$m}";
		$upload_dir    = "{$base_dir}/{$dir}";
		$upload_url    = "{$url}/{$path}/{$dir}";

		return $this->apply_filters( 'non_dynamic_upload_params', [
			'base_dir'     => $base_dir,
			'tmp_base_dir' => $tmp_base_dir,
			'upload_dir'   => $upload_dir,
			'upload_url'   => $upload_url,
		], $base_dir, $tmp_base_dir, $upload_dir, $upload_url );
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_upload_handler_params( $params ) {
		return $this->apply_filters( 'upload_handler_params', [
			'upload_dir'        => $params['tmp_upload_dir'] . DS,
			'upload_url'        => $params['upload_url'] . '/',
			'param_name'        => $params['param_name'],
			'image_versions'    => [
				'' => [
					'auto_orient' => true,
				],
			],
			'print_response'    => false,
			'accept_file_types' => $params['accept_file_types'],
			'max_file_size'     => $params['max_file_size'],
		], $params );
	}

	/**
	 * uninstall
	 */
	public function uninstall() {
		$params = $this->get_non_dynamic_upload_params();
		$this->get_file()->remove_dir( $params['tmp_base_dir'] );
	}
}
