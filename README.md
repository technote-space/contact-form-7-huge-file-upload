# Contact Form 7 huge file upload

[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

Contact Form 7 の機能を拡張するプラグインです。  
通常アップロードできないような大容量のファイルをアップロードできるようになります。

## スクリーンショット
- アップロード中

![設定画面](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/master/screenshot-1.png)

- 管理画面でファイルの管理

![処理中画面](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/master/screenshot-2.png)

- メールにはダウンロード用のリンク

![有効化前](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/master/screenshot-3.png)


## 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

## 導入手順
1. ZIPダウンロード  
2. wp-content/plugins に展開  
3. 管理画面から有効化  
4. Contact Form 7 のフォーム編集画面で対象のファイルに「fileupload」クラスを追加
5. Contact Form 7 のメール編集画面でメッセージ本文にタグを挿入（ダウンロードURLになります）

## 特徴
### 大きいファイルを分割してアップロード
[jQuery File Upload Plugin](https://github.com/blueimp/jQuery-File-Upload) を使用してブラウザから分割してアップロードを行います。  
そのため通常アップロードサイズ制限（post_max_size）を超えたファイルをアップロードすることが可能です。

### アップロードされたファイルをサーバで管理
通常 Contact Form 7 でアップロードされたファイルはメール送信後にサーバからは削除されます。  
このプラグインではファイルをサーバ上に残し、WordPressの機能を使用してファイルを管理（削除・閲覧）する事が可能です。  
またメールにはファイル添付の代わりにダウンロード用のURLが記載されます。  
そのため、ファイル容量が大きすぎてメールの送信に失敗してしまうことがなくなります。

### ファイルへのアクセス制限
WordPressの権限グループごとにダウンロード権限及び編集（削除）権限を設定することが可能です。  
ログインしていなくてもダウンロード可能な設定にすることもできます。
