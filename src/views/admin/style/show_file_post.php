<?php
/**
 * @version 1.1.4
 * @author technote-space
 * @since 1.0.0.6
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'CF7_HFU' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var string $selector */
?>

<style>
    #adminmenuwrap <?php $instance->h($selector); ?> {
        display: none;
    }

    .locked-info {
        display: none;
    }
</style>