<?php
/**
 * @version 1.3.0
 * @author Technote
 * @since 1.0.0.2
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'CF7_HFU' ) ) {
	return;
}
/** @var \WP_Framework_Presenter\Interfaces\Presenter $instance */
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
