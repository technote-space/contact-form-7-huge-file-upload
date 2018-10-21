<?php
/**
 * @version 1.0.0.5
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
	public function get_downloadable_roles() {
		return array_unique( array_filter( explode( ',', $this->apply_filters( 'downloadable_roles' ) ) ) );
	}

	/**
	 * @return array
	 */
	public function get_editable_roles() {
		return array_intersect( $this->get_downloadable_roles(), array_unique( array_filter( explode( ',', $this->apply_filters( 'editable_roles' ) ) ) ) );
	}

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
		foreach ( $this->get_downloadable_roles() as $role ) {
			$role = get_role( $role );
			if ( $role ) {
				foreach ( $this->get_capabilities() as $capability ) {
					$role->add_cap( $capability, ! preg_match( '#^create_#', $capability ) && ! preg_match( '#^delete_#', $capability ) && strpos( $capability, '_others_' ) === false );
				}
			}
		}
		foreach ( $this->get_editable_roles() as $role ) {
			$role = get_role( $role );
			if ( $role ) {
				foreach ( $this->get_capabilities() as $capability ) {
					$role->add_cap( $capability, ! preg_match( '#^create_#', $capability ) );
				}
			}
		}
	}

	/**
	 * unset capability
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function unset_capability() {
		foreach ( $this->get_editable_roles() as $role ) {
			$role = get_role( $role );
			if ( $role ) {
				foreach ( $this->get_capabilities() as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}
		foreach ( $this->get_downloadable_roles() as $role ) {
			$role = get_role( $role );
			if ( $role ) {
				foreach ( $this->get_capabilities() as $capability ) {
					$role->remove_cap( $capability );
				}
			}
		}
	}

	/**
	 * @param string $key
	 */
	/** @noinspection PhpUnusedPrivateMethodInspection */
	private function reset_capability( $key ) {
		if ( in_array( $key, [
			$this->get_filter_prefix() . 'editable_roles',
		] ) ) {
			$this->unset_capability();
			$this->set_capability();
		}
	}
}
