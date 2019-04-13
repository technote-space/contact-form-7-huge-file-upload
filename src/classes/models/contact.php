<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.1
 * @since 1.1.8
 * @since 1.3.0 Changed: ライブラリの更新 (#12)
 * @since 1.3.0 Changed: nonceチェックの追加 (#13)
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Classes\Models;

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

/**
 * Class Contact
 * @package Cf7_Hfu\Classes\Models
 */
class Contact implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Core\Interfaces\Nonce {

	use \WP_Framework_Core\Traits\Singleton, \WP_Framework_Core\Traits\Hook, \WP_Framework_Core\Traits\Nonce, \WP_Framework_Common\Traits\Package;

	/** @var File $_file */
	private $_file = null;

	/** @var Upload $_upload */
	private $_upload = null;

	/** @var array $_params */
	private $_params = null;

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
	 * @return Upload|\WP_Framework_Core\Traits\Singleton
	 */
	private function get_upload() {
		if ( ! isset( $this->_upload ) ) {
			$this->_upload = Upload::get_instance( $this->app );
		}

		return $this->_upload;
	}

	/**
	 * @since 1.3.0 Added: #13
	 * @return string
	 */
	public function get_nonce_slug() {
		return 'contact';
	}

	/**
	 * @since 1.3.0 Added: #13
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function add_nonce_setting( $params ) {
		$params['contact_nonce_key']   = $this->get_nonce_key();
		$params['contact_nonce_value'] = $this->create_nonce( false );

		return $params;
	}

	/**
	 * @since 1.3.0 Changed: #13
	 *
	 * @param \WPCF7_Validation $result
	 * @param \WPCF7_FormTag $tag
	 *
	 * @return \WPCF7_Validation
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function wpcf7_file_validation_filter( $result, $tag ) {
		$name = $tag->name;
		if ( isset( $_FILES[ $name ] ) ) {
			return $result;
		}

		if ( ! $this->get_upload()->is_valid_upload( $name ) ) {
			return $result;
		}

		if ( ! $this->nonce_check( false ) ) {
			$result->invalidate( $tag, $this->translate( 'nonce check error' ) );

			return $result;
		}

		$process = $this->get_upload()->get_uploaded_file_process( $name );
		$random  = $this->get_upload()->get_uploaded_file_random_key( $name );
		$params  = $this->get_upload()->get_upload_params( $process, null, $random, $name );
		$data    = $this->get_file()->find_file( $params['tmp_upload_dir'] );
		if ( false === $data ) {
			return $result;
		}
		$params['tmp_file']  = $data['path'];
		$params['base_name'] = $data['file'];
		$params['extension'] = pathinfo( $params['base_name'], PATHINFO_EXTENSION );
		$params['file_name'] = preg_replace( '/' . preg_quote( '.' . $params['extension'], '/' ) . '$/', '', $params['base_name'] );

		try {
			$params = $this->uploaded_process( $params, true );
		} catch ( \Exception $e ) {
			$this->app->log( $e );
			$result->invalidate( $tag, $this->translate( $e->getMessage() ) );

			return $result;
		}

		$file = $this->get_dummy_file( $params );

		if ( ! $this->check_file_type_pattern( $tag, $file ) ) {
			$result->invalidate( $tag, wpcf7_get_message( 'upload_file_type_invalid' ) );

			return $result;
		}

		if ( ! $this->check_file_size( $tag, $file ) ) {
			$result->invalidate( $tag, wpcf7_get_message( 'upload_file_too_large' ) );

			return $result;
		}
		$_FILES[ $name ]        = $file;
		$this->_params[ $name ] = $params;

		return $result;
	}

	/**
	 * @param \WPCF7_ContactForm $contact_form
	 * @param bool $abort
	 * @param \WPCF7_Submission $submission
	 *
	 * @return bool
	 * @throws \ReflectionException
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function wpcf7_before_send_mail(
		/** @noinspection PhpUnusedParameterInspection */
		$contact_form, $abort, $submission
	) {
		if ( $abort ) {
			return $abort;
		}

		foreach ( $this->_params as $name => $param ) {
			try {
				$params = $this->uploaded_process( $param );
			} catch ( \Exception $e ) {
				$this->app->log( $e );
				$this->set_contact_form_post_data( $submission, $name, null );
				continue;
			}
			$this->set_contact_form_post_data( $submission, $name, $params['access_url'] );
		}

		return $abort;
	}

	/**
	 * @param \WPCF7_Submission $submission
	 * @param string $key
	 * @param mixed $value
	 *
	 * @throws \ReflectionException
	 */
	private function set_contact_form_post_data( $submission, $key, $value ) {
		$reflection = new \ReflectionClass( $submission );
		$property   = $reflection->getProperty( 'posted_data' );
		$property->setAccessible( true );
		$posted_data = $property->getValue( $submission );
		if ( isset( $value ) ) {
			$posted_data[ $key ] = $value;
		} else {
			unset( $posted_data[ $key ] );
		}
		$property->setValue( $submission, $posted_data );
		$property->setAccessible( false );
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_dummy_file( $params ) {
		return [
			'error'    => false,
			'tmp_name' => $params['new_file'],
			'name'     => $params['base_name'],
			'size'     => $params['size'],
		];
	}

	/**
	 * @param \WPCF7_FormTag $tag
	 * @param array $file
	 *
	 * @return false|int
	 */
	private function check_file_type_pattern( $tag, $file ) {
		$file_type_pattern = wpcf7_acceptable_filetypes( $tag->get_option( 'filetypes' ), 'regex' );
		$file_type_pattern = '/\.(' . $file_type_pattern . ')$/i';

		return preg_match( $file_type_pattern, $file['name'] );
	}

	/**
	 * @param \WPCF7_FormTag $tag
	 * @param array $file
	 *
	 * @return bool
	 */
	private function check_file_size( $tag, $file ) {
		$allowed_size = $this->get_file()->get_size_limit( $tag );

		return $file['size'] <= $allowed_size;
	}

	/**
	 * @param array $params
	 * @param bool $validation
	 *
	 * @return array
	 * @throws \Exception
	 */
	private function uploaded_process( $params, $validation = false ) {
		$params = $this->get_file()->move_file( $params, $validation );
		if ( ! $validation ) {
			try {
				$params = $this->get_file()->attach_media( $params );
				try {
					$params = $this->get_file()->insert_file_post( $params );
					$this->get_file()->remove_dir( $params['tmp_upload_dir'] );
				} catch ( \Exception $e ) {
					$this->get_file()->detach_media( $params );
					throw $e;
				}
			} catch ( \Exception $e ) {
				$this->app->file->delete( $params['new_file'] );
				throw $e;
			}
		}

		return $params;
	}
}
