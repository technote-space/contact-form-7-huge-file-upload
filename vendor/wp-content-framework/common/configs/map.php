<?php
/**
 * WP_Framework_Common Configs Map
 *
 * @version 0.0.1
 * @author technote-space
 * @copyright technote-space All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

return [

	'define'    => '\WP_Framework_Common\Classes\Models\Define',
	'config'    => '\WP_Framework_Common\Classes\Models\Config',
	'setting'   => '\WP_Framework_Common\Classes\Models\Setting',
	'filter'    => '\WP_Framework_Common\Classes\Models\Filter',
	'uninstall' => '\WP_Framework_Common\Classes\Models\Uninstall',
	'utility'   => '\WP_Framework_Common\Classes\Models\Utility',
	'option'    => '\WP_Framework_Common\Classes\Models\Option',
	'user'      => '\WP_Framework_Common\Classes\Models\User',
	'input'     => '\WP_Framework_Common\Classes\Models\Input',

];