<?php
/**
 * @version 1.0.0.2
 * @author technote-space
 * @since 1.0.0.2
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'TECHNOTE_PLUGIN' ) ) {
	return;
}
/** @var \Technote\Controllers\Admin\Base $instance */
/** @var array $args */
/** @var array $list */
?>

<div class="wrap" id="<?php $instance->id(); ?>-file_list">
    <h2><?php $instance->h( 'File List', true ); ?></h2>
    <table class="widefat striped">
        <tr>
            <th><?php $instance->h( 'File Name', true ); ?></th>
            <th><?php $instance->h( 'File Size', true ); ?></th>
            <th></th>
        </tr>
		<?php if ( empty( $list ) ): ?>
            <tr>
                <td colspan="3"><?php $instance->h( 'File not found.', true ); ?></td>
            </tr>
		<?php else: ?>
			<?php foreach ( $list as $item ): ?>
				<?php if ( empty( $item['name'] ) ): ?>
                    <tr>
                        <td colspan="3"><?php $instance->h( 'File has been deleted.' ); ?></td>
                    </tr>
				<?php elseif ( ! $item['can_download'] ): ?>
                    <tr>
                        <td><?php $instance->h( $item['name'] ); ?></td>
                        <td><?php $instance->h( $item['size'] ); ?></td>
                        <td></td>
                    </tr>
				<?php else: ?>
                    <tr>
                        <td><?php $instance->url( $item['edit_link'], $item['name'], false, true ); ?></td>
                        <td><?php $instance->h( $item['size'] ); ?></td>
                        <td><?php $instance->form( 'input/button', $args, [
								'class'      => 'button-primary download-file',
								'name'       => 'download',
								'value'      => 'Download',
								'attributes' => [
									'data-url' => $item['url'],
								],
							] ); ?></td>
                    </tr>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
    </table>
</div>
