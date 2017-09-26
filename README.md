# Google Analytics Counter
Google Analyticsをカウンタとして使うPHPスクリプトです。

## 使い方
Analytics Counterを動作するようにして、
サーバー側で認証情報を読み取れるようにしてください。

その後、POSTメソッドで呼び出してください。


引数は以下の通りです。


### id (必須)
Google AnalyticsのView のIDです。

### start_day (デフォルト: 2005-01-01)
期間指定の始端の日付です。
end_dayより過去の日付を指定してください。


### end_day (デフォルト: today)
期間指定の終端の日付です。
start_dayより未来の日付を指定してください。

### days
複数の期間をまとめて指定できます。
'start' と 'end' を含む連想配列のペアを、配列にして指定してください。

```php
$_POST['days'] = [
    ['start' => '2005-01-01', 'end' => 'today'],
    ['start' => 'today', 'end' => 'today'],
    ['start' => 'yesterday', 'end' => 'yesterday'],
];
```


### 注記：引数の日付の書式
書式の例 '2005-01-01' 7daysAgo, today

- 注意：年月日指定のとき、 2005-01-01より前は指定できません。


 

## インストール
```sh
$ git clone https://github.com/fruitriin/analytics-counter.git
$ cd analytics-counter
$ composer install
```

## 設定
### Google API CosoleでGoogle Analytics APIを有効にする
1. Google APIs へアクセスします。
https://console.developers.google.com/

1. ライブラリからAnalytics API を選択します。
 AdBlockが有効になっている場合は無効にしてください。

1. Analytics API を有効にします。

1. 認証情報がまだ作成されていなければ作成します。次の工程へ

### Google API ConsoleでGoogle APIの認証情報を作成する

1. APIを呼び出す場所に
「ウェブサーバー（node.js、Tomcatなど）」を選択します。

1. アクセスするデータの種類に「アプリケーション データ」を選択します。

1. 「Google App EngineかGoogle Compute Engineを使用していますか？」のチェックボックスは「いいえ」のほうにチェックしてください。

1. 認証情報を作成をクリックすると

___ここで表示されるメールアドレスは次の工程で使うのでコピーしておきます。___

### Google AnalyticsでAPIからのメールアドレスを登録する

GoogleAnalyticsへアクセスします。

1. 管理画面を開きます。
1. アカウントの列からユーザー管理を選択します。
1. 権限を付与するユーザーに先程のメールアドレスを入力し、表示と分析を選択します。（デフォルトで選択されています。）
1. 追加ボタンを押します。



## 認証情報の登録
### json登録

analyticsCredentials.json を projectのディレクトリに置いてください。

### 環境変数登録
認証情報の.jsonから、下記の項目を環境変数として登録してください。

- AC_CLIENT_ID
- AC_CLIENT_EMAIL
- AC_SIGHNED_KEY



