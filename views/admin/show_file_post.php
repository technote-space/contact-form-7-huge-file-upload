<?php
/**
 * @version 1.0.0.6
 * @author technote-space
 * @since 1.0.0.6
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Interfaces\Presenter $instance */
/** @var array $args */
/** @var array $list */
/** @var \WP_Post $post */
?>

<?php if ( false === $post ) : ?>
    <h2><?php $instance->h( 'File not found.', true ); ?></h2>
<?php else: ?>
    <h1><?php $instance->h( $post->post_title ); ?></h1>
	<?php $instance->get_view( 'admin/file_list', $args, true ); ?>
<?php endif; ?>
