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
?>

<script>
	( function( $ ) {
		$( '#<?php $instance->id(); ?>-file_list .download-file' ).on( 'click', function() {
			const url = $( this ).data( 'url' );
			window.open( url, null );
			return false;
		} );
	} )( jQuery );
</script>
