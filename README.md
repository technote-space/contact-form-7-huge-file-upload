# Contact Form 7 huge file upload

[![Build Status](https://github.com/technote-space/contact-form-7-huge-file-upload/workflows/Build/badge.svg)](https://github.com/technote-space/contact-form-7-huge-file-upload/actions)
[![Build Status](https://travis-ci.com/technote-space/contact-form-7-huge-file-upload.svg?branch=master)](https://travis-ci.com/technote-space/contact-form-7-huge-file-upload)
[![CodeFactor](https://www.codefactor.io/repository/github/technote-space/contact-form-7-huge-file-upload/badge)](https://www.codefactor.io/repository/github/technote-space/contact-form-7-huge-file-upload)
[![License: GPL v2+](https://img.shields.io/badge/License-GPL%20v2%2B-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP: >=5.6](https://img.shields.io/badge/PHP-%3E%3D5.6-orange.svg)](http://php.net/)
[![WordPress: >=3.9.3](https://img.shields.io/badge/WordPress-%3E%3D3.9.3-brightgreen.svg)](https://wordpress.org/)

![バナー](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/images/assets/banner-772x250.png)

Contact Form 7 の機能を拡張するプラグインです。  
通常アップロードできないような大容量のファイルをアップロードできるようになります。

[最新バージョン](https://github.com/technote-space/contact-form-7-huge-file-upload/releases/latest/download/release.zip)

<!-- START doctoc generated TOC please keep comment here to allow auto update -->
<!-- DON'T EDIT THIS SECTION, INSTEAD RE-RUN doctoc TO UPDATE -->
**Table of Contents**

- [要件](#%E8%A6%81%E4%BB%B6)
- [スクリーンショット](#%E3%82%B9%E3%82%AF%E3%83%AA%E3%83%BC%E3%83%B3%E3%82%B7%E3%83%A7%E3%83%83%E3%83%88)
- [導入手順](#%E5%B0%8E%E5%85%A5%E6%89%8B%E9%A0%86)
- [特徴](#%E7%89%B9%E5%BE%B4)
  - [大きいファイルを分割してアップロード](#%E5%A4%A7%E3%81%8D%E3%81%84%E3%83%95%E3%82%A1%E3%82%A4%E3%83%AB%E3%82%92%E5%88%86%E5%89%B2%E3%81%97%E3%81%A6%E3%82%A2%E3%83%83%E3%83%97%E3%83%AD%E3%83%BC%E3%83%89)
  - [アップロードされたファイルをサーバで管理](#%E3%82%A2%E3%83%83%E3%83%97%E3%83%AD%E3%83%BC%E3%83%89%E3%81%95%E3%82%8C%E3%81%9F%E3%83%95%E3%82%A1%E3%82%A4%E3%83%AB%E3%82%92%E3%82%B5%E3%83%BC%E3%83%90%E3%81%A7%E7%AE%A1%E7%90%86)
  - [ファイルへのアクセス制限](#%E3%83%95%E3%82%A1%E3%82%A4%E3%83%AB%E3%81%B8%E3%81%AE%E3%82%A2%E3%82%AF%E3%82%BB%E3%82%B9%E5%88%B6%E9%99%90)
- [Apache 以外の注意点](#apache-%E4%BB%A5%E5%A4%96%E3%81%AE%E6%B3%A8%E6%84%8F%E7%82%B9)
- [Author](#author)
- [プラグイン作成用フレームワーク](#%E3%83%97%E3%83%A9%E3%82%B0%E3%82%A4%E3%83%B3%E4%BD%9C%E6%88%90%E7%94%A8%E3%83%95%E3%83%AC%E3%83%BC%E3%83%A0%E3%83%AF%E3%83%BC%E3%82%AF)

<!-- END doctoc generated TOC please keep comment here to allow auto update -->

## 要件
- PHP 5.6 以上
- WordPress 3.9.3 以上

## スクリーンショット
- アップロード中

![設定画面](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/images/assets/screenshot-1.png)

- 管理画面でファイルの管理

![処理中画面](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/images/assets/screenshot-2.png)

- メールにはダウンロード用のリンク

![有効化前](https://raw.githubusercontent.com/technote-space/contact-form-7-huge-file-upload/images/assets/screenshot-3.png)

## 導入手順
1. 最新版をGitHubからダウンロード  
[release.zip](https://github.com/technote-space/contact-form-7-huge-file-upload/releases/latest/download/release.zip)
2. 「プラグインのアップロード」からインストール
![install](https://raw.githubusercontent.com/technote-space/screenshots/master/misc/install-wp-plugin.png)
3. プラグインを有効化 
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

## Apache 以外の注意点
`.htaccess` を使用してアクセス制限やアップロードサイズ上限を設定していますが `Nginx` などではこの制御は有効ではありません。  
アップロードされるファイルの種類に関しては PHP側で制御しているため通常の利用の場合ファイルアップロード攻撃は発生しませんが、直接ファイルへのアクセスやアップロードサイズ制限などは制御できません。  
問題がある場合は `Apache` の利用や `Nginx` の設定を直接変更するなどの対応を検討してください。

## Author
[GitHub (Technote)](https://github.com/technote-space)  
[Blog](https://technote.space)

## プラグイン作成用フレームワーク
[WP Content Framework](https://github.com/wp-content-framework/core)
