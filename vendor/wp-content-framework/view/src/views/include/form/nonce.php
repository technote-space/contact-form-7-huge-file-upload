<?php
/**
 * WP_Framework_View Views Include Form Nonce
 *
 * @version 0.0.1
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
/** @var array $args */
/** @var string $nonce_key */
/** @var string $nonce_value */
?>
<?php $instance->form( 'input/hidden', array_merge( $args, [
	'name'  => $nonce_key,
	'value' => $nonce_value,
] ) ); ?>