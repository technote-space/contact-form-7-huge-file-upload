<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.6
 * @since 1.3.0 Changed: trivial change
 * @copyright Technote All Rights Reserved
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