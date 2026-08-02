# PinkClub-DUGA

PinkClub-FANZAを機能面の基準にした、DUGA商品情報API対応の設置型アフィリエイトサイトです。

PHP / MySQLで動作し、商品取得、商品一覧、商品詳細、検索、分類ページ、ランキング、アクセス解析、広告、固定ページ、相互リンク/RSS、cron自動更新を備えています。

## DUGA API対応

この版ではDUGAの商品検索APIを使用します。

- `version=1.2`
- `appid`
- `agentid`
- `bannerid`
- `format=json`
- `adult`
- `keyword`
- `hits`
- `offset`
- `sort`
- `target`
- `category`
- `labelid`
- `seriesid`
- `performerid`
- `openstt/openend`
- `releasestt/releaseend`

APIキーや代理店IDなどの秘密情報はGitに保存せず、管理画面から設定します。

## 女優・出演者データについて

DUGAにはFANZAのような独立した女優一覧APIはありません。

ただし、商品レスポンス内に `performer` フィールドがあるため、このツールでは商品同期時に出演者ID・名前（取得できる場合は読み仮名）を蓄積し、取得済み商品の範囲で出演者一覧・出演者別商品表示に利用します。読み仮名がない出演者は、女優一覧の「その他」に表示します。

制限として、まだ取得していない商品にしか出てこない出演者は一覧に表示できません。これはDUGA側に独立した出演者APIが無いためです。

## 画像・動画対応

- 商品カードはDUGAの見開きパッケージ候補を優先表示
- `jacket_120.jpg` や `120x90.jpg` より `package.jpg` / `jacket.jpg` 系を優先
- 画像が存在しない場合は候補URLへ自動フォールバック
- `samplemovie` の `movie` をサンプル動画として表示
- `samplemovie` の `capture` を動画poster候補として利用
- ダイジェスト画像 `thumbnail.image` をサンプル画像として利用

## 主な機能

- DUGA商品API設定
- 商品取得テストとDB保存
- cron/スケジューラによる自動取得
- 新着商品一覧
- 商品詳細ページ
- 気分で探す
- 最近見た作品
- 閲覧傾向に基づくおすすめ作品
- キーワード検索
- 出演者・ジャンル・メーカー・シリーズ等の分類データ保存
- 人気ランキング
- サンプル動画モーダル
- サンプル画像表示
- サイト基本設定
- 広告設定
- 固定ページ
- お問い合わせ・掲載削除依頼
- 相互リンク
- 相互RSS
- アクセス解析
- sitemap / robots
- 初期セットアップとDB自動構築

## ベース

- 機能・画面構成の基準: [PinkClub-FANZA](https://github.com/TaniyanR/PinkClub-FANZA)

## 注意

- DUGAのAPI仕様と利用条件を優先してください。
- APIキー、アプリケーションID、代理店ID、パスワードなどはGitへ保存しないでください。
- 本番反映前に管理画面からDUGA API設定を登録してください。
