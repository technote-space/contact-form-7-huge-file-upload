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
/** @var \Technote\Interfaces\Presenter $instance */
?>

<script>
    (function ($) {
        $('#<?php $instance->id(); ?>-file_list .download-file').on('click', function () {
            const url = $(this).data('url');
            window.open(url, null);
            return false;
        });
    })(jQuery);
</script>
