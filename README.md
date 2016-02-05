# DropboxAPITest
Dropbox APIとWebhookのテスト。getDelta()を使ってデータを読み込む、ミスのない方法。

## インストール
composerを使う。

    > curl -sS https://getcomposer.org/installer | php
    > php composer.phar install

で、インストールする

## 設定
あらかじめdropbox.confに書き込む。

 * token: Dropbox APIのトークン。あらかじめこちらで書き込む。
 * webhook: PHPが実行されたときに呼び出されるWebhook。動作確認済みなのはSlackのみ。

以下のような感じのファイルを書き込んでおくこと。
```json
{
  "token": "dropbox token ......",
  "webhook": "https://hooks.slack.com/ ......"
}
```

カーソルをここに書き込むので、不安な場合はコピーを取っておくことを推奨。

### Webhook対応
このPHPファイルをHTTPサーバにアップロード後、DropboxのAPIコンソールより、Webhookアドレスとして設定する。
