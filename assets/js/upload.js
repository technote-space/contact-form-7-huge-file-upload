/**
 * @version 1.3.0
 * @author technote-space
 * @since 1.0.0
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */
(function ($) {
    $(function () {
        /**
         * @var {object} cf7_hfu
         * @property {string} ajax_url
         * @property {string} process_key
         * @property {string} random_key
         * @property {string} random_key_slug
         * @property {string} huge_file_class
         * @property {int} max_chunk_size
         * @property {string} nonce_key
         * @property {string} nonce_value
         * @property {string} contact_nonce_key
         * @property {string} contact_nonce_value
         * @method {closure} get_form_data
         * @method {object} get_cancel_upload_form_data
         */

        const $file_upload = $('input[type="file"].' + cf7_hfu.huge_file_class);
        const progressbars = {};
        const abort_functions = {};
        const canceled = {};
        const random = {};
        let process = generate_rand();

        function generate_rand() {
            return Math.random().toString(36).slice(-8) + Math.random().toString(36).slice(-8);
        }

        cf7_hfu.get_form_data = function (i) {
            return function () {
                return [
                    {name: 'action', value: 'cf7_hfu_do_file_upload'},
                    {name: cf7_hfu.nonce_key, value: cf7_hfu.nonce_value},
                    {name: cf7_hfu.process_key, value: process},
                    {name: cf7_hfu.random_key, value: random[i]},
                    {name: '_wpcf7', value: $('[name="_wpcf7"]').val()}
                ];
            }
        };

        cf7_hfu.get_cancel_upload_form_data = function (random, process, param_name) {
            let form = {};
            form.action = 'cf7_hfu_cancel_upload';
            form.param_name = param_name;
            form[cf7_hfu.nonce_key] = cf7_hfu.nonce_value;
            form[cf7_hfu.process_key] = process;
            form[cf7_hfu.random_key] = random;
            return form;
        };

        // ロード中表示用
        const loading = '<div class="file-uploading">' +
            '<div class="uploading-progress">' +
            '<div class="uploading-progress-label">' +
            '<span class="file-data-wrap">' +
            '<span class="file-data"></span><input type="button" value="キャンセル" class="cancel-btn"></div>' +
            '</span>' +
            '</div>' +
            '</div>';

        $file_upload.each(function (i) {
            const $this = $(this), $wrap = $(this).closest('.wpcf7-form-control-wrap');
            $wrap.attr('data-progressbar_index', i).css({
                display: 'block',
                width: '100%'
            }).append(loading);
            progressbars[i] = $wrap.find('.uploading-progress').progressbar({
                value: 0
            });
            random[i] = generate_rand();

            $(this).fileupload({
                url: cf7_hfu.ajax_url,
                dataType: 'json',
                autoUpload: true,
                maxChunkSize: cf7_hfu.max_chunk_size - 0,
                formData: cf7_hfu.get_form_data(i)
            }).on('fileuploadstart', function (e) {
                const $target = $(e.currentTarget),
                    $wrap = $target.closest('.wpcf7-form-control-wrap'),
                    progressbar_index = $wrap.attr('data-progressbar_index'),
                    $progress = progressbars[progressbar_index];
                // ファイル選択を消してアップロードを表示
                $target.prop('disabled', true).hide();
                $wrap.find('.file-uploading').show();
                $wrap.find('.file-data').text('');
                $wrap.find('.cancel-btn').val('キャンセル').show();
                $wrap.addClass('uploading').removeClass('uploaded');
                $progress.progressbar('value', 0);
                canceled[progressbar_index] = false;
                $('.wpcf7-submit').addClass('disabled').prop('disabled', true);
            }).on('fileuploadadd', function (e, data) {
                const $target = $(e.currentTarget),
                    $wrap = $target.closest('.wpcf7-form-control-wrap'),
                    progressbar_index = $wrap.attr('data-progressbar_index');
                // アップロードにファイル名を表示
                // キャンセル用のxhrを保存
                abort_functions[progressbar_index] = data.submit();
                $wrap.find('.file-data').html('<span class="filename">' + data.files[0].name + '</span>');
            }).on('fileuploadprogressall', function (e, data) {
                const $target = $(e.currentTarget),
                    $wrap = $target.closest('.wpcf7-form-control-wrap'),
                    progressbar_index = $wrap.attr('data-progressbar_index'),
                    $progress = progressbars[progressbar_index];
                // アップロード中
                if (canceled[progressbar_index]) {
                    // キャンセルされていたら
                    if (abort_functions[progressbar_index]) {
                        // 中断処理
                        const xhr = abort_functions[progressbar_index];
                        abort_functions[progressbar_index] = null;
                        xhr.abort();
                    }
                } else {
                    // アップロードを進める
                    $progress.progressbar('value', Math.round(data.loaded / data.total * 100));
                }
            }).on('fileuploaddone', function (e, data) {
                const $target = $(e.currentTarget),
                    $wrap = $target.closest('.wpcf7-form-control-wrap'),
                    progressbar_index = $wrap.attr('data-progressbar_index'),
                    $progress = progressbars[progressbar_index];
                const name = data.paramName[0];
                const error = data.jqXHR.responseJSON[name][0].error;
                if (error) {
                    // error
                    $wrap.removeClass('uploading');
                    alert(error);
                    return;
                }
                // アップロードが完了したら
                // 必要なデータをhiddenで持たせる
                const result = data.result[name][0];
                $progress.progressbar('value', 100);
                $wrap.find('.cancel-btn').val('削除');
                const html = '<input type="hidden" name="' + $target.attr('name') + '" value="' + result.process + '">' +
                    '<input type="hidden" name="' + $target.attr('name') + cf7_hfu.random_key_slug + '" value="' + result.random + '">' +
                    '<input type="hidden" name="' + cf7_hfu.contact_nonce_key + '" value="' + cf7_hfu.contact_nonce_value + '">' +
                    '<span class="filename">' + result.name + '</span>';
                $wrap.find('.file-data').html(html);
                $wrap.addClass('uploaded').removeClass('uploading');
            }).on('fileuploadfail', function (e) {
                const $target = $(e.currentTarget),
                    $wrap = $target.closest('.wpcf7-form-control-wrap');
                // 失敗（中断）の場合、アップロード中フラグを下げる
                $wrap.removeClass('uploading');
                // PHP側の処理を考慮して少し遅らせて中断したファイル削除
                const r = random[i];
                const p = process;
                setTimeout(function () {
                    $.post({
                        url: cf7_hfu.ajax_url,
                        data: cf7_hfu.get_cancel_upload_form_data(r, p, $this.attr('name'))
                    });
                }, 1000);
            }).on('fileuploadalways', function (e) {
                const $target = $(e.currentTarget),
                    $wrap = $target.closest('.wpcf7-form-control-wrap'),
                    progressbar_index = $wrap.attr('data-progressbar_index');
                // 今の状態に合わせてファイル選択とアップロード中を適切に表示
                check_file_state($wrap);
                // アップロード先が被らないようにランダム文字列再生成
                random[progressbar_index] = generate_rand();
            });
        });

        // キャンセルボタン押下時の処理
        $('.cancel-btn').on('click', function () {
            const $wrap = $(this).closest('.wpcf7-form-control-wrap'),
                progressbar_index = $wrap.attr('data-progressbar_index');
            if ($wrap.hasClass('uploading')) {
                // アップロードキャンセル
                canceled[progressbar_index] = true;
                $wrap.removeClass('uploading');
            }
            if ($wrap.hasClass('uploaded')) {
                // ファイル削除
                $wrap.find('.file-data').text('');
                $wrap.removeClass('uploaded');
            }
            check_file_state($wrap);
        });

        function check_file_state($wrap) {
            if (!$wrap.hasClass('uploading') && !$wrap.hasClass('uploaded')) {
                // ファイル選択を再度表示
                $wrap.find('.fileupload').prop('disabled', false).show();
                $wrap.find('.file-uploading').hide();
            }
            check_files_state();
        }

        // フォーム送信ボタン動作調整用
        function check_files_state() {
            let uploading = false;
            $('.fileupload').each(function () {
                if ($(this).closest('.wpcf7-form-control-wrap').hasClass('uploading')) {
                    uploading = true;
                }
            });
            if (!uploading) {
                $('.wpcf7-submit').removeClass('.disabled').prop('disabled', false);
            }
        }

        // フォーム送信動作調整用
        $(".wpcf7").on('wpcf7:beforesubmit', function () {
            $('.wpcf7-submit').addClass('.disabled').prop('disabled', true);
        }).on('wpcf7:submit', function () {
            $('.wpcf7-submit').removeClass('.disabled').prop('disabled', false);
        }).on('wpcf7:mailsent', function () {
            $('.fileupload').each(function () {
                const $wrap = $(this).closest('.wpcf7-form-control-wrap');
                $wrap.find('.fileupload').prop('disabled', false).show();
                $wrap.find('.file-uploading').hide();
            });
            process = generate_rand();
        });

    });
})(jQuery);