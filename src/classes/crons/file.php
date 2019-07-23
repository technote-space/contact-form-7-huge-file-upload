<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Classes\Crons;

use Cf7_Hfu\Classes\Models\Upload;
use WP_Framework_Cron\Classes\Crons\Base;

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

/**
 * Class File
 * @package Cf7_Hfu\Classes\Crons
 */
class File extends Base {

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

		/** @var Upload $upload */
		/** @var \Cf7_Hfu\Classes\Models\File $file */
		$upload = Upload::get_instance( $this->app );
		$file   = \Cf7_Hfu\Classes\Models\File::get_instance( $this->app );
		$params = $upload->get_non_dynamic_upload_params();
		$file->remove_dir( $params['tmp_base_dir'], time() - $this->apply_filters( 'delete_file_threshold' ) );
	}
}
