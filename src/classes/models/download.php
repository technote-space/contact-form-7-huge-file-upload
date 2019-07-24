<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace Cf7_Hfu\Classes\Models;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;
use WP_Framework_Presenter\Traits\Presenter;
use WP_Post;

if ( ! defined( 'CF7_HFU' ) ) {
	exit;
}

/**
 * Class Download
 * @package Cf7_Hfu\Classes\Models
 */
class Download implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook, \WP_Framework_Presenter\Interfaces\Presenter {

	use Singleton, Hook, Presenter, Package;

	/** @var File $file */
	private $file = null;

	/** @var bool $is_edit_post_file */
	private $is_edit_post_file = false;

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
	 * file download
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	private function file_download() {
		list( $file, $name ) = $this->get_download_file_info();

		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		@ob_end_clean();
		header( 'Content-Type: application/force-download' );
		header( 'Content-Length: ' . filesize( $file ) );
		header( 'Content-Disposition: attachment; filename*=UTF-8\'\'' . rawurlencode( $name ) );
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fopen
		$file_pointer = fopen( $file, 'rb' );
		if ( $file_pointer ) {
			while ( ! feof( $file_pointer ) && ( connection_status() === 0 ) ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fread, WordPress.Security.EscapeOutput.OutputNotEscaped
				echo fread( $file_pointer, 1024 * 4 );
				// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
				@ob_flush();
			}
			// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			@ob_end_flush();
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
			fclose( $file_pointer );
		}
		exit;
	}

	/**
	 * setup download page
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function setup_download_page() {
		/** @var Capability $capability */
		$capability = Capability::get_instance( $this->app );
		if ( in_array( $this->app->user->user_role, $capability->get_downloadable_roles(), true ) && ! in_array( $this->app->user->user_role, $capability->get_editable_roles(), true ) ) {
			$slug = $this->get_download_page_slug();
			$this->add_style_view( 'admin/style/show_file_post', [
				'selector' => '#menu-posts-' . $this->get_file()->get_file_post_type() . ' .wp-submenu-wrap',
			] );
			$this->add_script_view( 'admin/script/file_list' );
			add_submenu_page( 'edit.php?post_type=' . $this->get_file()->get_file_post_type(), 'Download', 'Download', $this->app->user->user_role, $slug, function () {
				$post_id = $this->app->input->get( 'post_id' );
				if ( empty( $post_id ) || ! $this->check_can_download( $post_id ) ) {
					$list = false;
					$post = false;
				} else {
					$post = get_post( $post_id );
					if ( empty( $post ) ) {
						$list = false;
						$post = false;
					} else {
						$list = $this->get_file_list( $post );
					}
				}
				$this->get_view( 'admin/show_file_post', [
					'list' => $list,
					'post' => $post,
				], true );
			} );
		}
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param array $columns
	 * @param string $post_type
	 *
	 * @return array
	 */
	private function delete_check_box( $columns, $post_type ) {
		if ( $post_type === $this->get_file()->get_file_post_type() ) {
			/** @var Capability $capability */
			$capability = Capability::get_instance( $this->app );
			if ( ! in_array( $this->app->user->user_role, $capability->get_editable_roles(), true ) ) {
				unset( $columns['cb'] );
			}
		}

		return $columns;
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param array $actions
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function delete_edit_links( $actions, $post ) {
		if ( $post->post_type === $this->get_file()->get_file_post_type() ) {
			$post_type = get_post_type_object( $post->post_type );
			if ( ! current_user_can( $post_type->cap->delete_posts ) ) {
				unset( $actions['inline hide-if-no-js'] );
				unset( $actions['edit'] );
				unset( $actions['trash'] );
				unset( $actions['clone'] );
				unset( $actions['edit_as_new_draft'] );
			}
		}

		return $actions;
	}

	/**
	 * redirect to download page
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	private function redirect_to_download_page() {
		global $pagenow;
		if ( 'post.php' !== $pagenow ) {
			return;
		}

		$post_id = $this->app->input->get( 'post' );
		if ( empty( $post_id ) ) {
			return;
		}

		$post = get_post( $post_id );
		if ( empty( $post ) || $post->post_type !== $this->get_file()->get_file_post_type() ) {
			return;
		}

		/** @var Capability $capability */
		$capability = Capability::get_instance( $this->app );
		if ( in_array( $this->app->user->user_role, $capability->get_downloadable_roles(), true ) && ! in_array( $this->app->user->user_role, $capability->get_editable_roles(), true ) ) {
			wp_safe_redirect( get_admin_url() . 'admin.php?page=' . $this->get_download_page_slug() . '&post_id=' . $post_id );
			exit;
		}
	}

	/**
	 * check edit post file
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 */
	private function check_edit_post_file() {
		global $post;
		if ( empty( $post ) ) {
			return;
		}

		$access_key = $this->app->post->get( 'access_key', $post->ID );
		if ( ! empty( $access_key ) ) {
			$this->is_edit_post_file = true;
		}
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param array $image_editors
	 *
	 * @return array
	 */
	private function wp_image_editors( $image_editors ) {
		if ( $this->is_edit_post_file ) {
			return [];
		}

		return $image_editors;
	}

	/**
	 * @noinspection PhpUnusedPrivateMethodInspection
	 * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
	 *
	 * @param WP_Post $post
	 */
	private function edit_form_after_title( $post ) {
		if ( $post->post_type !== $this->get_file()->get_file_post_type() ) {
			return;
		}

		$this->add_script_view( 'admin/script/file_list' );
		$this->get_view( 'admin/file_list', [
			'list' => $this->get_file_list( $post ),
		], true );
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
			return $this->can_not_download( $post_id );
		}

		$post = get_post( $post_id );
		if ( empty( $post ) ) {
			return $this->get_no_image_file_info();
		}

		$file = get_attached_file( $post_id );
		if ( empty( $file ) || ! $this->app->file->exists( $file ) ) {
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
	 * @param int $post_id
	 *
	 * @return array
	 * @SuppressWarnings(PHPMD.ExitExpression)
	 */
	private function can_not_download( $post_id ) {
		if ( ! $this->app->user->logged_in && $this->apply_filters( 'must_be_logged_in_to_download' ) ) {
			$post_id = $this->app->post->first( 'file_id', $post_id );
			if ( ! empty( $post_id ) ) {
				$redirect = $this->get_edit_post_link( $post_id );
				if ( $redirect ) {
					wp_safe_redirect( $redirect );
					exit;
				}
			}
		}

		return $this->get_no_image_file_info();
	}

	/**
	 * @return array
	 */
	private function get_no_image_file_info() {
		return [
			$this->apply_filters( 'no_image_file', $this->get_assets_path( 'img/no_img.png' ) ),
			'no_img.png',
		];
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
	 * @param $post_id
	 *
	 * @return string|false
	 */
	private function get_edit_post_link( $post_id ) {
		$post = get_post( $post_id );
		if ( ! $post ) {
			return false;
		}
		$action           = '&action=edit';
		$post_type_object = get_post_type_object( $post->post_type );
		if ( ! $post_type_object ) {
			return false;
		}

		return admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
	}

	/**
	 * @return string
	 */
	private function get_download_page_slug() {
		return $this->apply_filters( 'download_page_slug', 'download-' . $this->get_file()->get_file_post_type() );
	}


	/**
	 * @param WP_Post $post
	 *
	 * @return array
	 */
	private function get_file_list( $post ) {
		return array_map(
			function ( $file_id ) {
				$post         = get_post( $file_id );
				$access_key   = empty( $post ) ? false : $this->app->post->get( 'access_key', $file_id );
				$can_edit     = empty( $access_key ) ? false : $this->check_can_edit( $file_id );
				$can_download = empty( $access_key ) ? false : $this->check_can_download( $file_id );
				$url          = empty( $access_key ) ? false : $this->get_file()->get_access_url( $access_key );
				$edit_link    = empty( $access_key ) ? false : get_edit_post_link( $file_id, 'link' );
				$size         = empty( $access_key ) ? false : $this->get_file()->get_appropriate_size_format_callback( $this->app->post->get( 'size', $file_id ), function ( $size, $norm, $unit ) {
					return round( $size / $norm, 2 ) . $unit . 'B';
				} );
				$name         = empty( $post ) ? '' : $post->post_title;

				return [
					'can_edit'     => $can_edit,
					'can_download' => $can_download,
					'url'          => $url,
					'edit_link'    => $edit_link,
					'name'         => $name,
					'size'         => $size,
				];
			}, $this->get_file()->get_file_ids( $post->ID )
		);
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function check_can_download( $post_id ) {
		$can_download = true;
		if ( $this->apply_filters( 'must_be_logged_in_to_download' ) ) {
			if ( ! $this->app->user->logged_in ) {
				$can_download = false;
			} else {
				/** @var Capability $capability */
				$capability   = Capability::get_instance( $this->app );
				$capabilities = $capability->get_downloadable_roles();
				if ( ! in_array( 'all', $capabilities, true ) && ! in_array( $this->app->user->user_role, $capabilities, true ) ) {
					$can_download = false;
				}
			}
		}

		return $this->apply_filters( 'can_download', $can_download, $post_id, $this->app->user->user_data );
	}

	/**
	 * @param int $post_id
	 *
	 * @return bool
	 */
	private function check_can_edit( $post_id ) {
		$can_edit = $this->check_can_download( $post_id );
		if ( $can_edit ) {
			if ( ! $this->app->user->logged_in ) {
				$can_edit = false;
			} else {
				/** @var Capability $capability */
				$capability   = Capability::get_instance( $this->app );
				$capabilities = $capability->get_editable_roles();
				if ( ! in_array( 'all', $capabilities, true ) && ! in_array( $this->app->user->user_role, $capabilities, true ) ) {
					$can_edit = false;
				}
			}
		}

		return $this->apply_filters( 'can_edit', $can_edit, $post_id, $this->app->user->user_data );
	}
}
