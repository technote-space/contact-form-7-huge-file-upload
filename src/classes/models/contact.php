<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Classes\Models;

use Exception;
use ReflectionClass;
use ReflectionException;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Nonce;
use WP_Framework_Core\Traits\Singleton;
use WPCF7_ContactForm;
use WPCF7_FormTag;
use WPCF7_Submission;
use WPCF7_Validation;

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

/**
 * Class Contact
 * @package Cf7_Hfu\Classes\Models
 */
class Contact implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Core\Interfaces\Nonce {

	use Singleton, Hook, Nonce, Package;

	/** @var File $file */
	private $file = null;

	/** @var Upload $upload */
	private $upload = null;

	/** @var array $params */
	private $params = null;

	/**
	 * @return File|Singleton
	 */
	private function get_file() {
		if ( ! isset( $this->file ) ) {
			$this->file = File::get_instance( $this->app );
		}

		return $this->file;
	}

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
	 * @return string
	 */
	public function get_nonce_slug() {
		return 'contact';
	}

	/**
	 *
	 * @param array $params
	 *
	 * @return array
	 */
	public function add_nonce_setting( $params ) {
		$params['contactNonceKey']   = $this->get_nonce_key();
		$params['contactNonceValue'] = $this->create_nonce( false );

		return $params;
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param WPCF7_Validation $result
	 * @param WPCF7_FormTag $tag
	 *
	 * @return WPCF7_Validation
	 */
	private function wpcf7_file_validation_filter( $result, $tag ) {
		$name = $tag->name;
		$file = $this->app->input->file( $name );
		if ( isset( $file ) ) {
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
		$data    = $this->find_file( $params['tmp_upload_dir'] );
		if ( false === $data ) {
			return $result;
		}
		$params['tmp_file']  = $data['path'];
		$params['base_name'] = $data['file'];
		$params['extension'] = pathinfo( $params['base_name'], PATHINFO_EXTENSION );
		$params['file_name'] = preg_replace( '/' . preg_quote( '.' . $params['extension'], '/' ) . '$/', '', $params['base_name'] );

		try {
			$params = $this->uploaded_process( $params, true );
		} catch ( Exception $e ) {
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

		$this->app->input->set_file( $name, $file );
		$this->params[ $name ] = $params;

		return $result;
	}

	/**
	 * @param $directory_path
	 *
	 * @return false|array
	 */
	private function find_file( $directory_path ) {
		foreach ( glob( $directory_path . '/*' ) as $path ) {
			if ( $this->app->file->is_file( $path ) ) {
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
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param WPCF7_ContactForm $contact_form
	 * @param bool $abort
	 * @param WPCF7_Submission $submission
	 *
	 * @return bool
	 * @throws ReflectionException
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 */
	private function wpcf7_before_send_mail(
		/** @noinspection PhpUnusedParameterInspection */
		$contact_form, $abort, $submission
	) {
		if ( $abort ) {
			return $abort;
		}

		foreach ( $this->params as $name => $param ) {
			try {
				$params = $this->uploaded_process( $param );
			} catch ( Exception $e ) {
				$this->app->log( $e );
				$this->set_contact_form_post_data( $submission, $name, null );
				continue;
			}
			$this->set_contact_form_post_data( $submission, $name, $params['access_url'] );
		}

		return $abort;
	}

	/**
	 * @param WPCF7_Submission $submission
	 * @param string $key
	 * @param mixed $value
	 *
	 * @throws ReflectionException
	 */
	private function set_contact_form_post_data( $submission, $key, $value ) {
		$reflection = new ReflectionClass( $submission );
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
	 * @param WPCF7_FormTag $tag
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
	 * @param WPCF7_FormTag $tag
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
	 * @throws Exception
	 */
	private function uploaded_process( $params, $validation = false ) {
		$params = $this->get_file()->move_file( $params, $validation );
		if ( ! $validation ) {
			try {
				$params = $this->get_file()->attach_media( $params );
				try {
					$params = $this->get_file()->insert_file_post( $params );
					$this->get_file()->remove_dir( $params['tmp_upload_dir'] );
				} catch ( Exception $e ) {
					$this->get_file()->detach_media( $params );
					throw $e;
				}
			} catch ( Exception $e ) {
				$this->app->file->delete( $params['new_file'] );
				throw $e;
			}
		}

		return $params;
	}
}
