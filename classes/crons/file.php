<?php
/**
 * @version 1.0.0.1
 * @author technote-space
 * @since 1.0.0.1
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Crons;

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	exit;
}

/**
 * Class File
 * @package Cf7_Hfu\Crons
 */
class File extends \Technote\Crons\Base {

	/**
	 * @return int
	 */
	protected function get_interval() {
		return $this->apply_filters( 'delete_file_interval' );
	}

	/**
	 * execute
	 */
	protected function execute() {
		if ( ! $this->apply_filters( 'is_valid_auto_delete' ) ) {
			return;
		}

		/** @var \Cf7_Hfu\Models\Upload $upload */
		/** @var \Cf7_Hfu\Models\File $file */
		$upload = \Cf7_Hfu\Models\Upload::get_instance( $this->app );
		$file   = \Cf7_Hfu\Models\File::get_instance( $this->app );
		$params = $upload->get_non_dynamic_upload_params();
		$file->remove_dir( $params['tmp_base_dir'], time() - $this->apply_filters( 'delete_file_threshold' ) );
	}
}
