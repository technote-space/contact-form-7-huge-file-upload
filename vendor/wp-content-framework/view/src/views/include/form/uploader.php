<?php
/**
 * WP_Framework_View Views Include Form Uploader
 *
 * @version 0.0.3
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

use WP_Framework_Presenter\Interfaces\Presenter;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	return;
}
/** @var Presenter $instance */
/** @var array $args */
/** @var string $target */
$args['class']                     .= ' ' . $instance->get_media_uploader_class();
$args['attributes']['data-target'] = $target;
! isset( $args['name'] ) and $args['name'] = '';
?>
<?php $instance->form( 'input/button', $args ); ?>
