<?php
/**
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Interfaces\Presenter;

if ( ! defined( 'CF7_HFU' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var array $args */
/** @var array $list */
/** @var WP_Post $post */
?>

<?php if ( false === $post ) : ?>
	<h2><?php $instance->h( 'File not found.', true ); ?></h2>
<?php else: ?>
	<h1><?php $instance->h( $post->post_title ); ?></h1>
	<?php $instance->get_view( 'admin/file_list', $args, true ); ?>
<?php endif; ?>
