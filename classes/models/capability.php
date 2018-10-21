<?php
/**
 * @version 1.0.0.1
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
 * Class Capability
 * @package Cf7_Hfu\Models
 */
class Capability implements \Technote\Interfaces\Singleton, \Technote\Interfaces\Hook {

	use \Technote\Traits\Singleton, \Technote\Traits\Hook, \Technote\Traits\Presenter;

	/**
	 * @return array
	 */
	public function get_capabilities() {
		/** @var File $file */
		$file = File::get_instance( $this->app );
		$caps = (array) get_post_type_capabilities( (object) $file->get_file_post_type_args( [] ) );
		unset( $caps['read'] );
		$caps['create_posts'] = preg_replace( '#^edit#', 'create', $caps['edit_posts'] );

		return $this->apply_filters( 'file_post_capabilities', $caps );
	}

	/**
	 * set capability
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function set_capability() {
		$role = get_role( 'administrator' );
		if ( $role ) {
			foreach ( $this->get_capabilities() as $capability ) {
				$role->add_cap( $capability, ! preg_match( '#^create_#', $capability ) );
			}
		}
	}

	/**
	 * unset capability
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function unset_capability() {
		$role = get_role( 'administrator' );
		if ( $role ) {
			foreach ( $this->get_capabilities() as $capability ) {
				$role->remove_cap( $capability );
			}
		}
	}
}
