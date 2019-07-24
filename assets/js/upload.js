/**
 * @author Technote
 * @copyright technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */
( function( $ ) {
	$( function() {
		/**
		 * @var {object} cf7Hfu
		 * @property {string} ajaxUrl
		 * @property {string} processKey
		 * @property {string} randomKey
		 * @property {string} randomKeySlug
		 * @property {string} hugeFileClass
		 * @property {int} maxChunkSize
		 * @property {string} nonceKey
		 * @property {string} nonceValue
		 * @property {string} contactNonceKey
		 * @property {string} contactNonceValue
		 * @method {closure} getFormData
		 * @method {object} getCancelUploadFormData
		 */

		const $fileUpload = $( 'input[type="file"].' + cf7Hfu.hugeFileClass );
		const progressbars = {};
		const abortFunctions = {};
		const canceled = {};
		const random = {};
		let process = generateRand();

		/**
		 * generate rand
		 * @returns {string} rand
		 */
		function generateRand() {
			return Math.random().toString( 36 ).slice( -8 ) + Math.random().toString( 36 ).slice( -8 ); // eslint-disable-line no-magic-numbers
		}

		cf7Hfu.getFormData = function( index ) {
			return function() {
				return [
					{ name: 'action', value: 'cf7_hfu_do_file_upload' },
					{ name: cf7Hfu.nonceKey, value: cf7Hfu.nonceValue },
					{ name: cf7Hfu.processKey, value: process },
					{ name: cf7Hfu.randomKey, value: random[ index ] },
					{ name: '_wpcf7', value: $( '[name="_wpcf7"]' ).val() },
				];
			};
		};

		cf7Hfu.getCancelUploadFormData = function( random, process, paramName ) {
			const form = {};
			form[ 'action' ] = 'cf7_hfu_cancel_upload';
			form[ 'param_name' ] = paramName;
			form[ cf7Hfu.nonceKey ] = cf7Hfu.nonceValue;
			form[ cf7Hfu.processKey ] = process;
			form[ cf7Hfu.randomKey ] = random;
			form[ '_wpcf7' ] = $( '[name="_wpcf7"]' ).val();
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

		$fileUpload.each( function( index ) {
			const $this = $( this ), $wrap = $( this ).closest( '.wpcf7-form-control-wrap' );
			$wrap.attr( 'data-progressbar_index', index ).css( {
				display: 'block',
				width: '100%',
			} ).append( loading );
			progressbars[ index ] = $wrap.find( '.uploading-progress' ).progressbar( {
				value: 0,
			} );
			random[ index ] = generateRand();

			$( this ).fileupload( {
				url: cf7Hfu.ajaxUrl,
				dataType: 'json',
				autoUpload: true,
				maxChunkSize: cf7Hfu.maxChunkSize - 0, // eslint-disable-line no-magic-numbers
				formData: cf7Hfu.getFormData( index ),
			} ).on( 'fileuploadstart', function( event ) {
				const $target = $( event.currentTarget ),
					$wrap = $target.closest( '.wpcf7-form-control-wrap' ),
					progressbarIndex = $wrap.attr( 'data-progressbar_index' ),
					$progress = progressbars[ progressbarIndex ];
				// ファイル選択を消してアップロードを表示
				$target.prop( 'disabled', true ).hide();
				$wrap.find( '.file-uploading' ).show();
				$wrap.find( '.file-data' ).text( '' );
				$wrap.find( '.cancel-btn' ).val( 'キャンセル' ).show();
				$wrap.addClass( 'uploading' ).removeClass( 'uploaded' );
				$progress.progressbar( 'value', 0 ); // eslint-disable-line no-magic-numbers
				canceled[ progressbarIndex ] = false;
				$( '.wpcf7-submit' ).addClass( 'disabled' ).prop( 'disabled', true );
			} ).on( 'fileuploadadd', function( event, data ) {
				const $target = $( event.currentTarget ),
					$wrap = $target.closest( '.wpcf7-form-control-wrap' ),
					progressbarIndex = $wrap.attr( 'data-progressbar_index' );
				// アップロードにファイル名を表示
				// キャンセル用のxhrを保存
				abortFunctions[ progressbarIndex ] = data.submit();
				$wrap.find( '.file-data' ).html( '<span class="filename">' + data.files[ 0 ].name + '</span>' );
			} ).on( 'fileuploadprogressall', function( event, data ) {
				const $target = $( event.currentTarget );
				const $wrap = $target.closest( '.wpcf7-form-control-wrap' );
				const progressbarIndex = $wrap.attr( 'data-progressbar_index' );
				const $progress = progressbars[ progressbarIndex ];
				// アップロード中
				if ( canceled[ progressbarIndex ] ) {
					// キャンセルされていたら
					if ( abortFunctions[ progressbarIndex ] ) {
						// 中断処理
						const xhr = abortFunctions[ progressbarIndex ];
						abortFunctions[ progressbarIndex ] = null;
						xhr.abort();
					}
				} else {
					// アップロードを進める
					$progress.progressbar( 'value', Math.round( data.loaded / data.total * 100 ) ); // eslint-disable-line no-magic-numbers
				}
			} ).on( 'fileuploaddone', function( event, data ) {
				const $target = $( event.currentTarget );
				const $wrap = $target.closest( '.wpcf7-form-control-wrap' );
				const progressbarIndex = $wrap.attr( 'data-progressbar_index' );
				const $progress = progressbars[ progressbarIndex ];
				const name = data.paramName[ 0 ];
				const error = data.jqXHR.responseJSON[ name ][ 0 ].error;
				if ( error ) {
					// error
					$wrap.removeClass( 'uploading' );
					alert( error );
					return;
				}
				// アップロードが完了したら
				// 必要なデータをhiddenで持たせる
				const result = data.result[ name ][ 0 ];
				$progress.progressbar( 'value', 100 ); // eslint-disable-line no-magic-numbers
				$wrap.find( '.cancel-btn' ).val( '削除' );
				const html = '<input type="hidden" name="' + $target.attr( 'name' ) + '" value="' + result.process + '">' +
					'<input type="hidden" name="' + $target.attr( 'name' ) + cf7Hfu.randomKeySlug + '" value="' + result.random + '">' +
					'<input type="hidden" name="' + cf7Hfu.contactNonceKey + '" value="' + cf7Hfu.contactNonceValue + '">' +
					'<span class="filename">' + result.name + '</span>';
				$wrap.find( '.file-data' ).html( html );
				$wrap.addClass( 'uploaded' ).removeClass( 'uploading' );
			} ).on( 'fileuploadfail', function( event ) {
				const $target = $( event.currentTarget ),
					$wrap = $target.closest( '.wpcf7-form-control-wrap' );
				// 失敗（中断）の場合、アップロード中フラグを下げる
				$wrap.removeClass( 'uploading' );
				// PHP側の処理を考慮して少し遅らせて中断したファイル削除
				const rand = random[ index ];
				const proc = process;
				setTimeout( function() {
					$.post( {
						url: cf7Hfu.ajaxUrl,
						data: cf7Hfu.getCancelUploadFormData( rand, proc, $this.attr( 'name' ) ),
					} );
				}, 1000 ); // eslint-disable-line no-magic-numbers
			} ).on( 'fileuploadalways', function( event ) {
				const $target = $( event.currentTarget );
				const $wrap = $target.closest( '.wpcf7-form-control-wrap' );
				const progressbarIndex = $wrap.attr( 'data-progressbar_index' );
				// 今の状態に合わせてファイル選択とアップロード中を適切に表示
				checkFileState( $wrap );
				// アップロード先が被らないようにランダム文字列再生成
				random[ progressbarIndex ] = generateRand();
			} );
		} );

		// キャンセルボタン押下時の処理
		$( '.cancel-btn' ).on( 'click', function() {
			const $wrap = $( this ).closest( '.wpcf7-form-control-wrap' );
			const progressbarIndex = $wrap.attr( 'data-progressbar_index' );
			if ( $wrap.hasClass( 'uploading' ) ) {
				// アップロードキャンセル
				canceled[ progressbarIndex ] = true;
				$wrap.removeClass( 'uploading' );
			}
			if ( $wrap.hasClass( 'uploaded' ) ) {
				// ファイル削除
				$wrap.find( '.file-data' ).text( '' );
				$wrap.removeClass( 'uploaded' );
			}
			checkFileState( $wrap );
		} );

		/**
		 * check file state
		 * @param {*} $wrap wrap
		 */
		function checkFileState( $wrap ) {
			if ( ! $wrap.hasClass( 'uploading' ) && ! $wrap.hasClass( 'uploaded' ) ) {
				// ファイル選択を再度表示
				$wrap.find( '.fileupload' ).prop( 'disabled', false ).show();
				$wrap.find( '.file-uploading' ).hide();
			}
			checkFilesState();
		}

		// フォーム送信ボタン動作調整用
		/**
		 * check files state
		 */
		function checkFilesState() {
			let uploading = false;
			$( '.fileupload' ).each( function() {
				if ( $( this ).closest( '.wpcf7-form-control-wrap' ).hasClass( 'uploading' ) ) {
					uploading = true;
				}
			} );
			if ( ! uploading ) {
				$( '.wpcf7-submit' ).removeClass( '.disabled' ).prop( 'disabled', false );
			}
		}

		// フォーム送信動作調整用
		$( '.wpcf7' ).on( 'wpcf7:beforesubmit', function() {
			$( '.wpcf7-submit' ).addClass( '.disabled' ).prop( 'disabled', true );
		} ).on( 'wpcf7:submit', function() {
			$( '.wpcf7-submit' ).removeClass( '.disabled' ).prop( 'disabled', false );
		} ).on( 'wpcf7:mailsent', function() {
			$( '.fileupload' ).each( function() {
				const $wrap = $( this ).closest( '.wpcf7-form-control-wrap' );
				$wrap.find( '.fileupload' ).prop( 'disabled', false ).show();
				$wrap.find( '.file-uploading' ).hide();
			} );
			process = generateRand();
		} );

	} );
} )( jQuery );
